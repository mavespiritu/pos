<?php

namespace common\modules\accounting\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use common\modules\accounting\models\AccessProgram;
use common\modules\accounting\models\Audit;
use common\modules\accounting\models\Season;
use common\modules\accounting\models\Student;
use common\modules\accounting\models\StudentBranchProgram;
use common\modules\accounting\models\Branch;
use common\modules\accounting\models\BranchProgram;
use common\modules\accounting\models\Income;
use common\modules\accounting\models\Expense;
use common\modules\accounting\models\PettyExpense;
use common\modules\accounting\models\PhotocopyExpense;
use common\modules\accounting\models\OtherExpense;
use common\modules\accounting\models\BankDeposit;
use common\modules\accounting\models\OperatingExpense;
use common\modules\accounting\models\BranchTransfer;
use common\modules\accounting\models\IncomeEnrolment;
use common\modules\accounting\models\FreebieAndIcon;
use common\modules\accounting\models\BudgetProposal;
use common\modules\accounting\models\BeginningCoh;
use common\modules\accounting\models\IncomeCode;
use common\modules\accounting\models\Denomination;
use kartik\mpdf\Pdf;

class ReportController extends \yii\web\Controller
{
	/**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['income-generation', 'expense-generation'],
                'rules' => [
                    [
                        'actions' => ['income-generation', 'expense-generation', 'monthly-summary', 'daily-audit'],
                        'allow' => true,
                        'roles' => ['TopManagement','AreaManager','AccountingStaff'],
                    ],
                ],
            ],
        ];
    }

    function explodeDate($date)
    {
        $dates = [];
        $dates = explode(" - ", $date);

        return $dates;
    }

    function check_in_range($start_date, $end_date, $date_from_user)
    {
      // Convert to timestamp
      $start_ts = strtotime($start_date);
      $end_ts = strtotime($end_date);
      $user_ts = strtotime($date_from_user);

      // Check that user date is between start & end
      return (($user_ts >= $start_ts) && ($user_ts <= $end_ts));
    }

    function dateRange( $first, $last, $step = '+1 day', $format = 'Y-m-d' ) {
        $dates = [];
        $current = strtotime( $first );
        $last = strtotime( $last );

        while( $current <= $last ) {

            $dates[] = date( $format, $current );
            $current = strtotime( $step, $current );
        }

        return $dates;
    }

    function check_in_cutoff($date)
    {
        $dates = [];
        $month = date("m", strtotime($date));
        $year = date("Y", strtotime($date));
        $nth_month = date('n', strtotime($date));
        $cutoff_check_start_date = $year.'-'.$month.'-11'; 
        $cutoff_check_end_date = $year.'-'.$month.'-25';



        if($this->check_in_range($cutoff_check_start_date, $cutoff_check_end_date, $date) == true)
        {
            $dates['start'] = $cutoff_check_start_date;
            $dates['end'] = $cutoff_check_end_date;
        }else{
            if(strtotime($date) < strtotime($cutoff_check_start_date))
            {
                $last_month = date("m", strtotime("first day of last month", strtotime($date)));
                $no_of_days_in_a_month = cal_days_in_month(CAL_GREGORIAN,$last_month,$year);
                if($nth_month == 1)
                {
                    $dates['start'] = ($year-1).'-12-26';
                    $dates['end'] = $year.'-'.$month.'-10';
                }else if(($nth_month > 1) && ($nth_month < 12)){
                    $dates['start'] = $year.'-'.$last_month.'-26';
                    $dates['end'] = $year.'-'.$month.'-10';
                }else if($nth_month == 12){
                    $dates['start'] = $year.'-'.$last_month.'-26';
                    $dates['end'] = $year.'-'.$month.'-10';
                }
            }else if(strtotime($date) > strtotime($cutoff_check_start_date))
            {
                $next_month = date("m", strtotime('first day of +1 month', strtotime($date)));
                $no_of_days_in_a_month = cal_days_in_month(CAL_GREGORIAN,$next_month,$year);
                if($nth_month < 12){
                    $dates['start'] = $year.'-'.$month.'-26';
                    $dates['end'] = $year.'-'.$next_month.'-10';
                }else if($nth_month == 12){
                    $dates['start'] = $year.'-'.$month.'-26';
                    $dates['end'] = ($year+1).'-01-10';
                }
            }
        } 

        return $dates;
    }

    function check_in_cutoff_key($date)
    {
        $dates = [];
        $month = date("m", strtotime($date));
        $year = date("Y", strtotime($date));
        $nth_month = date('n', strtotime($date));
        $cutoff_check_start_date = $year.'-'.$month.'-11'; 
        $cutoff_check_end_date = $year.'-'.$month.'-25';



        if($this->check_in_range($cutoff_check_start_date, $cutoff_check_end_date, $date) == true)
        {
            $dates['start'] = $cutoff_check_start_date;
            $dates['end'] = $cutoff_check_end_date;
        }else{
            if(strtotime($date) < strtotime($cutoff_check_start_date))
            {
                $last_month = date("m", strtotime("first day of last month", strtotime($date)));
                $no_of_days_in_a_month = cal_days_in_month(CAL_GREGORIAN,$last_month,$year);
                if($nth_month == 1)
                {
                    $dates['start'] = ($year-1).'-12-26';
                    $dates['end'] = $year.'-'.$month.'-10';
                }else if(($nth_month > 1) && ($nth_month < 12)){
                    $dates['start'] = $year.'-'.$last_month.'-26';
                    $dates['end'] = $year.'-'.$month.'-10';
                }else if($nth_month == 12){
                    $dates['start'] = $year.'-'.$last_month.'-26';
                    $dates['end'] = $year.'-'.$month.'-10';
                }
            }else if(strtotime($date) > strtotime($cutoff_check_start_date))
            {
                $next_month = date("m", strtotime('first day of +1 month', strtotime($date)));
                $no_of_days_in_a_month = cal_days_in_month(CAL_GREGORIAN,$next_month,$year);
                if($nth_month < 12){
                    $dates['start'] = $year.'-'.$month.'-26';
                    $dates['end'] = $year.'-'.$next_month.'-10';
                }else if($nth_month == 12){
                    $dates['start'] = $year.'-'.$month.'-26';
                    $dates['end'] = ($year+1).'-01-10';
                }
            }
        } 

        return $dates['start'].' - '.$dates['end'];
    }

    function date_difference($start,$end)
    {
        $datediff = strtotime($end) - strtotime($start);
        return floor($datediff / (60 * 60 * 24));
    }

    function produceCutoffs($start_date, $end_date)
    {
        $cutoffs = [];
        $final_cutoff = [];
        for($i=0; $i<$this->date_difference($start_date, $end_date); $i+=16)
        {
            $repeat = date("Y-m-d",strtotime("+".$i." day",strtotime($start_date)));
            $cutoffs[] = $this->check_in_cutoff($repeat);
        }

        $cutoffs[] = $this->check_in_cutoff($end_date);

        $final_cutoff = array_unique($cutoffs, SORT_REGULAR);

        return $final_cutoff;
    }

    public function actionIncomeGeneration()
    {
        $model = new Audit();

        $user_info = Yii::$app->user->identity->userinfo;
        $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
        $rolenames =  ArrayHelper::map($roles, 'name','name');

        $access = AccessProgram::findOne(['user_id' => Yii::$app->user->identity->userinfo->user_id]);

        if(in_array('TopManagement',$rolenames)){
            $branchPrograms = $access ? $access->branch_program_id!= '' ? BranchProgram::find()
                                ->select(['accounting_branch_program.id as id', 'concat(accounting_branch.name," - ",accounting_program.name) as branchProgramName'])
                                ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->asArray()
                                ->orderBy(['branchProgramName' => SORT_ASC])
                                ->all() : BranchProgram::find()
                                ->select(['accounting_branch_program.id as id', 'concat(accounting_branch.name," - ",accounting_program.name) as branchProgramName'])
                                ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                                ->asArray()
                                ->orderBy(['branchProgramName' => SORT_ASC])
                                ->all() : BranchProgram::find()
                                ->select(['accounting_branch_program.id as id', 'concat(accounting_branch.name," - ",accounting_program.name) as branchProgramName'])
                                ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                                ->asArray()
                                ->orderBy(['branchProgramName' => SORT_ASC])
                                ->all();

            $branchPrograms = ArrayHelper::map($branchPrograms, 'id', 'branchProgramName');
        }else{
            $branchPrograms = $access ? $access->branch_program_id!= '' ? BranchProgram::find()
                                ->select(['accounting_branch_program.id as id', 'concat(accounting_branch.name," - ",accounting_program.name) as branchProgramName'])
                                ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->asArray()
                                ->orderBy(['branchProgramName' => SORT_ASC])
                                ->all() : BranchProgram::find()
                                ->select(['accounting_branch_program.id as id', 'concat(accounting_branch.name," - ",accounting_program.name) as branchProgramName'])
                                ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['branchProgramName' => SORT_ASC])
                                ->all() : BranchProgram::find()
                                ->select(['accounting_branch_program.id as id', 'concat(accounting_branch.name," - ",accounting_program.name) as branchProgramName'])
                                ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['branchProgramName' => SORT_ASC])
                                ->all();
            $branchPrograms = ArrayHelper::map($branchPrograms, 'id', 'branchProgramName');
        }

        $seasons = [];

        return $this->render('income-generation',[
            'model' => $model,
            'branchPrograms' => $branchPrograms,
            'seasons' => $seasons,
        ]);
    }

    public function actionExpenseGeneration()
    {
        $model = new Audit();

        $user_info = Yii::$app->user->identity->userinfo;
        $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
        $rolenames =  ArrayHelper::map($roles, 'name','name');

        $access = AccessProgram::findOne(['user_id' => Yii::$app->user->identity->userinfo->user_id]);

        if(in_array('TopManagement',$rolenames)){
            $branchPrograms = $access ? $access->branch_program_id!= '' ? BranchProgram::find()
                                ->select(['accounting_branch_program.id as id', 'concat(accounting_branch.name," - ",accounting_program.name) as branchProgramName'])
                                ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->asArray()
                                ->orderBy(['branchProgramName' => SORT_ASC])
                                ->all() : BranchProgram::find()
                                ->select(['accounting_branch_program.id as id', 'concat(accounting_branch.name," - ",accounting_program.name) as branchProgramName'])
                                ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                                ->asArray()
                                ->orderBy(['branchProgramName' => SORT_ASC])
                                ->all() : BranchProgram::find()
                                ->select(['accounting_branch_program.id as id', 'concat(accounting_branch.name," - ",accounting_program.name) as branchProgramName'])
                                ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                                ->asArray()
                                ->orderBy(['branchProgramName' => SORT_ASC])
                                ->all();

            $branchPrograms = ArrayHelper::map($branchPrograms, 'id', 'branchProgramName');
        }else{
            $branchPrograms = $access ? $access->branch_program_id!= '' ? BranchProgram::find()
                                ->select(['accounting_branch_program.id as id', 'concat(accounting_branch.name," - ",accounting_program.name) as branchProgramName'])
                                ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->asArray()
                                ->orderBy(['branchProgramName' => SORT_ASC])
                                ->all() : BranchProgram::find()
                                ->select(['accounting_branch_program.id as id', 'concat(accounting_branch.name," - ",accounting_program.name) as branchProgramName'])
                                ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['branchProgramName' => SORT_ASC])
                                ->all() : BranchProgram::find()
                                ->select(['accounting_branch_program.id as id', 'concat(accounting_branch.name," - ",accounting_program.name) as branchProgramName'])
                                ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['branchProgramName' => SORT_ASC])
                                ->all();
            $branchPrograms = ArrayHelper::map($branchPrograms, 'id', 'branchProgramName');
        }

        $seasons = [];

        return $this->render('expense-generation',[
            'model' => $model,
            'branchPrograms' => $branchPrograms,
            'seasons' => $seasons,
        ]);
    }

    public function actionMonthlySummary()
    {
        $model = new Audit();

        $user_info = Yii::$app->user->identity->userinfo;
        $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
        $rolenames =  ArrayHelper::map($roles, 'name','name');

        $access = AccessProgram::findOne(['user_id' => Yii::$app->user->identity->userinfo->user_id]);

        if(in_array('TopManagement',$rolenames)){
            $branchPrograms = $access ? $access->branch_program_id!= '' ? BranchProgram::find()
                                ->select(['accounting_branch_program.id as id', 'concat(accounting_branch.name," - ",accounting_program.name) as branchProgramName'])
                                ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->asArray()
                                ->orderBy(['branchProgramName' => SORT_ASC])
                                ->all() : BranchProgram::find()
                                ->select(['accounting_branch_program.id as id', 'concat(accounting_branch.name," - ",accounting_program.name) as branchProgramName'])
                                ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                                ->asArray()
                                ->orderBy(['branchProgramName' => SORT_ASC])
                                ->all() : BranchProgram::find()
                                ->select(['accounting_branch_program.id as id', 'concat(accounting_branch.name," - ",accounting_program.name) as branchProgramName'])
                                ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                                ->asArray()
                                ->orderBy(['branchProgramName' => SORT_ASC])
                                ->all();

            $branchPrograms = ArrayHelper::map($branchPrograms, 'id', 'branchProgramName');
        }else{
            $branchPrograms = $access ? $access->branch_program_id!= '' ? BranchProgram::find()
                                ->select(['accounting_branch_program.id as id', 'concat(accounting_branch.name," - ",accounting_program.name) as branchProgramName'])
                                ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->asArray()
                                ->orderBy(['branchProgramName' => SORT_ASC])
                                ->all() : BranchProgram::find()
                                ->select(['accounting_branch_program.id as id', 'concat(accounting_branch.name," - ",accounting_program.name) as branchProgramName'])
                                ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['branchProgramName' => SORT_ASC])
                                ->all() : BranchProgram::find()
                                ->select(['accounting_branch_program.id as id', 'concat(accounting_branch.name," - ",accounting_program.name) as branchProgramName'])
                                ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['branchProgramName' => SORT_ASC])
                                ->all();
            $branchPrograms = ArrayHelper::map($branchPrograms, 'id', 'branchProgramName');
        }

        $seasons = [];

        return $this->render('monthly-summary',[
            'model' => $model,
            'branchPrograms' => $branchPrograms,
            'seasons' => $seasons,
        ]);
    }

    public function actionDailyAudit()
    {
        $model = new Audit();

        $user_info = Yii::$app->user->identity->userinfo;
        $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
        $rolenames =  ArrayHelper::map($roles, 'name','name');

        $access = AccessProgram::findOne(['user_id' => Yii::$app->user->identity->userinfo->user_id]);

        if(in_array('TopManagement',$rolenames)){
            $branchPrograms = $access ? $access->branch_program_id!= '' ? BranchProgram::find()
                                ->select(['accounting_branch_program.id as id', 'concat(accounting_branch.name," - ",accounting_program.name) as branchProgramName'])
                                ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->asArray()
                                ->orderBy(['branchProgramName' => SORT_ASC])
                                ->all() : BranchProgram::find()
                                ->select(['accounting_branch_program.id as id', 'concat(accounting_branch.name," - ",accounting_program.name) as branchProgramName'])
                                ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                                ->asArray()
                                ->orderBy(['branchProgramName' => SORT_ASC])
                                ->all() : BranchProgram::find()
                                ->select(['accounting_branch_program.id as id', 'concat(accounting_branch.name," - ",accounting_program.name) as branchProgramName'])
                                ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                                ->asArray()
                                ->orderBy(['branchProgramName' => SORT_ASC])
                                ->all();

            $branchPrograms = ArrayHelper::map($branchPrograms, 'id', 'branchProgramName');
        }else{
            $branchPrograms = $access ? $access->branch_program_id!= '' ? BranchProgram::find()
                                ->select(['accounting_branch_program.id as id', 'concat(accounting_branch.name," - ",accounting_program.name) as branchProgramName'])
                                ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->asArray()
                                ->orderBy(['branchProgramName' => SORT_ASC])
                                ->all() : BranchProgram::find()
                                ->select(['accounting_branch_program.id as id', 'concat(accounting_branch.name," - ",accounting_program.name) as branchProgramName'])
                                ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['branchProgramName' => SORT_ASC])
                                ->all() : BranchProgram::find()
                                ->select(['accounting_branch_program.id as id', 'concat(accounting_branch.name," - ",accounting_program.name) as branchProgramName'])
                                ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['branchProgramName' => SORT_ASC])
                                ->all();
            $branchPrograms = ArrayHelper::map($branchPrograms, 'id', 'branchProgramName');
        }

        $seasons = [];

        return $this->render('daily-audit',[
            'model' => $model,
            'branchPrograms' => $branchPrograms,
            'seasons' => $seasons,
        ]);
    }

    public function actionGenerateIncomeGeneration($id, $branchProgram = '', $season = '')
    {
        if($id!="" && $season!="")
        {
            $selectedSeason = Season::findOne($season);
            $selectedBranchProgram = BranchProgram::find()
                                    ->select([
                                        'accounting_branch_program.id as id',
                                        'concat(accounting_branch.name," - ",accounting_program.name) as name'
                                    ])
                                    ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                    ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                    ->andWhere(['accounting_branch_program.id' => $branchProgram])
                                    ->asArray()
                                    ->one();

            $cutoff = $this->check_in_cutoff($id);
            $dates = $this->dateRange($cutoff['start'], $cutoff['end']);

            $incomeCodes = IncomeCode::find()->select(['name','description'])->asArray()->all();

            $beginningCoh = BeginningCoh::find()->where(['date(datetime)' => $cutoff['start'], 'branch_program_id' => $selectedSeason->branchProgram->id])->orderBy(['datetime' => SORT_DESC])->one();

            $incomeEnrolments = IncomeEnrolment::find()
                                ->select([
                                    'DATE(accounting_income.datetime) as date',
                                    'accounting_income_enrolment.or_no',
                                    'accounting_income_code.name as codeName',
                                    'concat(accounting_student.first_name," ",accounting_student.middle_name," ",accounting_student.last_name," ",accounting_student.extension_name) as studentName',
                                    'accounting_income_enrolment.amount',
                                    'accounting_income.amount_type as amountType',
                                ])
                                ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_enrolment.id and accounting_income.income_type_id = 1')
                                ->leftJoin('accounting_income_code','accounting_income_code.id = accounting_income_enrolment.code_id')
                                ->leftJoin('accounting_student','accounting_student.id = accounting_income_enrolment.student_id')
                                ->andWhere(['accounting_income_enrolment.season_id' => $selectedSeason->id])
                                ->andWhere(['between', 'accounting_income.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
                                ->asArray()
                                ->all();

            $freebies = FreebieAndIcon::find()
                                ->select([
                                    'DATE(accounting_income.datetime) as date',
                                    'accounting_income_freebies_and_icons.pr',
                                    'accounting_income_code.name as codeName',
                                    'concat(accounting_student.first_name," ",accounting_student.middle_name," ",accounting_student.last_name," ",accounting_student.extension_name) as studentName',
                                    'accounting_income_freebies_and_icons.amount',
                                    'accounting_income.amount_type as amountType',
                                ])
                                ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                                ->leftJoin('accounting_income_code','accounting_income_code.id = accounting_income_freebies_and_icons.code_id')
                                ->leftJoin('accounting_student','accounting_student.id = accounting_income_freebies_and_icons.student_id')
                                ->andWhere(['accounting_income_freebies_and_icons.season_id' => $selectedSeason->id])
                                ->andWhere(['between', 'accounting_income.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
                                ->asArray()
                                ->all();

            $budgetProposals = BudgetProposal::find()
                                ->select([
                                    'DATE(accounting_income.datetime) as date',
                                    'accounting_income_code.name as codeName',
                                    'IF(accounting_income_budget_proposal.budget_proposal_type_id = 18, accounting_income_budget_proposal.other_type, accounting_budget_proposal_type.name) as detail',
                                    'sum(COALESCE(particulars.total, 0)) as total',
                                    'accounting_income.amount_type as amountType',
                                ])
                                ->leftJoin(['particulars' => '(
                                    (
                                        SELECT
                                            accounting_budget_proposal_particular.budget_proposal_id,
                                            sum(accounting_budget_proposal_particular.amount) as total
                                        from accounting_budget_proposal_particular
                                        LEFT JOIN accounting_income_budget_proposal on accounting_income_budget_proposal.id = accounting_budget_proposal_particular.budget_proposal_id
                                        WHERE accounting_budget_proposal_particular.approval_status = "Approved"
                                        GROUP BY accounting_budget_proposal_particular.budget_proposal_id
                                    )
                                )'], 'particulars.budget_proposal_id = accounting_income_budget_proposal.id')
                                ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_budget_proposal.id and accounting_income.income_type_id = 3')
                                ->leftJoin('accounting_budget_proposal_type','accounting_budget_proposal_type.id = accounting_income_budget_proposal.budget_proposal_type_id')
                                ->leftJoin('accounting_income_code','accounting_income_code.id = accounting_income_budget_proposal.code_id')
                                ->andWhere(['accounting_income_budget_proposal.approval_status' => 'Approved'])
                                ->andWhere(['accounting_income_budget_proposal.season_id' => $selectedSeason->id])
                                ->andWhere(['between', 'accounting_income.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
                                ->asArray()
                                ->all();

            return $this->renderAjax('_generate_income_generation', [
                'id' => $id,
                'season' => $season,
                'branchProgram' => $branchProgram,
                'selectedSeason' => $selectedSeason,
                'selectedBranchProgram' => $selectedBranchProgram,
                'cutoff' => $cutoff,
                'dates' => $dates,
                'beginningCoh' => $beginningCoh,
                'incomeEnrolments' => $incomeEnrolments,
                'freebies' => $freebies,
                'budgetProposals' => $budgetProposals,
                'incomeCodes' => $incomeCodes,
            ]);
        }     
    }

    public function actionExtractIncomeGeneration($id, $branchProgram, $season)
    {
        if($id!="" && $season!="")
        {
            $selectedSeason = Season::findOne($season);
            $selectedBranchProgram = BranchProgram::find()
                                    ->select([
                                        'accounting_branch_program.id as id',
                                        'concat(accounting_branch.name," - ",accounting_program.name) as name'
                                    ])
                                    ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                    ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                    ->andWhere(['accounting_branch_program.id' => $branchProgram])
                                    ->asArray()
                                    ->one();

            $cutoff = $this->check_in_cutoff($id);
            $dates = $this->dateRange($cutoff['start'], $cutoff['end']);

            $incomeCodes = IncomeCode::find()->select(['name','description'])->asArray()->all();

            $beginningCoh = BeginningCoh::find()->where(['date(datetime)' => $cutoff['start'], 'branch_program_id' => $selectedSeason->branchProgram->id])->orderBy(['datetime' => SORT_DESC])->one();

            $incomeEnrolments = IncomeEnrolment::find()
                                ->select([
                                    'DATE(accounting_income.datetime) as date',
                                    'accounting_income_enrolment.or_no',
                                    'accounting_income_code.name as codeName',
                                    'concat(accounting_student.first_name," ",accounting_student.middle_name," ",accounting_student.last_name," ",accounting_student.extension_name) as studentName',
                                    'accounting_income_enrolment.amount',
                                    'accounting_income.amount_type as amountType',
                                ])
                                ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_enrolment.id and accounting_income.income_type_id = 1')
                                ->leftJoin('accounting_income_code','accounting_income_code.id = accounting_income_enrolment.code_id')
                                ->leftJoin('accounting_student','accounting_student.id = accounting_income_enrolment.student_id')
                                ->andWhere(['accounting_income_enrolment.season_id' => $selectedSeason->id])
                                ->andWhere(['between', 'accounting_income.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
                                ->asArray()
                                ->all();

            $freebies = FreebieAndIcon::find()
                                ->select([
                                    'DATE(accounting_income.datetime) as date',
                                    'accounting_income_freebies_and_icons.pr',
                                    'accounting_income_code.name as codeName',
                                    'concat(accounting_student.first_name," ",accounting_student.middle_name," ",accounting_student.last_name," ",accounting_student.extension_name) as studentName',
                                    'accounting_income_freebies_and_icons.amount',
                                    'accounting_income.amount_type as amountType',
                                ])
                                ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                                ->leftJoin('accounting_income_code','accounting_income_code.id = accounting_income_freebies_and_icons.code_id')
                                ->leftJoin('accounting_student','accounting_student.id = accounting_income_freebies_and_icons.student_id')
                                ->andWhere(['accounting_income_freebies_and_icons.season_id' => $selectedSeason->id])
                                ->andWhere(['between', 'accounting_income.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
                                ->asArray()
                                ->all();

            $budgetProposals = BudgetProposal::find()
                                ->select([
                                    'DATE(accounting_income.datetime) as date',
                                    'accounting_income_code.name as codeName',
                                    'IF(accounting_income_budget_proposal.budget_proposal_type_id = 18, accounting_income_budget_proposal.other_type, accounting_budget_proposal_type.name) as detail',
                                    'sum(COALESCE(particulars.total, 0)) as total',
                                    'accounting_income.amount_type as amountType',
                                ])
                                ->leftJoin(['particulars' => '(
                                    (
                                        SELECT
                                            accounting_budget_proposal_particular.budget_proposal_id,
                                            sum(accounting_budget_proposal_particular.amount) as total
                                        from accounting_budget_proposal_particular
                                        LEFT JOIN accounting_income_budget_proposal on accounting_income_budget_proposal.id = accounting_budget_proposal_particular.budget_proposal_id
                                        WHERE accounting_budget_proposal_particular.approval_status = "Approved"
                                        GROUP BY accounting_budget_proposal_particular.budget_proposal_id
                                    )
                                )'], 'particulars.budget_proposal_id = accounting_income_budget_proposal.id')
                                ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_budget_proposal.id and accounting_income.income_type_id = 3')
                                ->leftJoin('accounting_budget_proposal_type','accounting_budget_proposal_type.id = accounting_income_budget_proposal.budget_proposal_type_id')
                                ->leftJoin('accounting_income_code','accounting_income_code.id = accounting_income_budget_proposal.code_id')
                                ->andWhere(['accounting_income_budget_proposal.approval_status' => 'Approved'])
                                ->andWhere(['accounting_income_budget_proposal.season_id' => $selectedSeason->id])
                                ->andWhere(['between', 'accounting_income.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
                                ->asArray()
                                ->all();

            $content = $this->renderPartial('_report_income_generation', [
                'id' => $id,
                'season' => $season,
                'branchProgram' => $branchProgram,
                'selectedSeason' => $selectedSeason,
                'selectedBranchProgram' => $selectedBranchProgram,
                'cutoff' => $cutoff,
                'dates' => $dates,
                'beginningCoh' => $beginningCoh,
                'incomeEnrolments' => $incomeEnrolments,
                'freebies' => $freebies,
                'budgetProposals' => $budgetProposals,
                'incomeCodes' => $incomeCodes,
            ]);

            $title = 'Daily Income: '.$selectedBranchProgram['name'].' - SEASON '.$selectedSeason->name.' - ('.$cutoff['start'].' - '.$cutoff['end'].')';

            $pdf = new Pdf([
            'mode' => Pdf::MODE_CORE,
            'format' => Pdf::FORMAT_LEGAL, 
            'orientation' => Pdf::ORIENT_LANDSCAPE, 
            'destination' => Pdf::DEST_DOWNLOAD, 
            'filename' => $title.'.pdf',
            'content' => $content,  
            'marginLeft' => 11.4,
            'marginRight' => 11.4,
            'cssInline' => 'th { text-align:center; 
                                      padding:6px 6px 6px 6px;
                                      font-size:12px;
                                      }
                            .title {
                                      font-size:16px;
                                      font-weight:bold;
                                      }
                            td { padding:8px 4px 4px 4px; font-size:12px;
                          }', 
            'options' => ['title' => $title],
            'methods' => [ 
                'SetHeader'=>[$title], 
                'SetFooter'=>['Page {PAGENO}'],
            ]
            ]);
            $response = Yii::$app->response;
            $response->format = \yii\web\Response::FORMAT_RAW;
            $headers = Yii::$app->response->headers;
            $headers->add('Content-Type', 'application/pdf');
            return $pdf->render();   
        }     
    }

    public function actionGenerateExpenseGeneration($id, $branchProgram = '', $season = '')
    {
        if($id!="" && $season!="")
        {
            $selectedSeason = Season::findOne($season);
            $selectedBranchProgram = BranchProgram::find()
                                    ->select([
                                        'concat(accounting_branch.name," - ",accounting_program.name) as name'
                                    ])
                                    ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                    ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                    ->andWhere(['accounting_branch_program.id' => $branchProgram])
                                    ->asArray()
                                    ->one();

            $cutoff = $this->check_in_cutoff($id);
            $dates = $this->dateRange($cutoff['start'], $cutoff['end']);

            $pettyExpenses = PettyExpense::find()
                                ->select([
                                    'DATE(accounting_expense.datetime) as date',
                                    'accounting_expense_petty_expense.pcv_no',
                                    'accounting_expense_petty_expense.particulars',
                                    'accounting_expense_petty_expense.food',
                                    'accounting_expense_petty_expense.supplies',
                                    'accounting_expense_petty_expense.load',
                                    'accounting_expense_petty_expense.fare',
                                    'accounting_expense_petty_expense.charge_to',
                                    'accounting_expense.amount_type as amountType',
                                ])
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_petty_expense.id and accounting_expense.expense_type_id = 1')
                                ->andWhere(['accounting_expense.season_id' => $season])
                                ->andWhere(['between', 'accounting_expense.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
                                ->asArray()
                                ->all();

            $photocopyExpenses = PhotocopyExpense::find()
                                ->select([
                                    'DATE(accounting_expense.datetime) as date',
                                    'accounting_expense_photocopy_expense.cv_no',
                                    'accounting_expense_photocopy_expense.subject',
                                    'accounting_expense_photocopy_expense.no_of_pages',
                                    'accounting_expense_photocopy_expense.no_of_pieces',
                                    'accounting_expense_photocopy_expense.amount_per_page',
                                    'accounting_expense_photocopy_expense.total_amount',
                                    'accounting_expense_photocopy_expense.charge_to',
                                    'accounting_expense.amount_type as amountType',
                                ])
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                                ->andWhere(['accounting_expense.season_id' => $season])
                                ->andWhere(['between', 'accounting_expense.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
                                ->asArray()
                                ->all();

            $otherExpenses = OtherExpense::find()
                                ->select([
                                    'DATE(accounting_expense.datetime) as date',
                                    'accounting_expense_other_expense.cv_no',
                                    'accounting_expense_other_expense.particulars',
                                    'accounting_expense_other_expense.amount',
                                    'accounting_expense_other_expense.charge_to',
                                    'accounting_expense.amount_type as amountType',
                                ])
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_other_expense.id and accounting_expense.expense_type_id = 3')
                                ->andWhere(['accounting_expense.season_id' => $season])
                                ->andWhere(['between', 'accounting_expense.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
                                ->asArray()
                                ->all();

            $bankDeposits = BankDeposit::find()
                                ->select([
                                    'DATE(accounting_expense.datetime) as date',
                                    'accounting_expense_bank_deposit.bank',
                                    'accounting_expense_bank_deposit.account_no',
                                    'accounting_expense_bank_deposit.transaction_no',
                                    'accounting_expense_bank_deposit.deposited_by',
                                    'accounting_expense_bank_deposit.remarks',
                                    'accounting_expense_bank_deposit.amount',
                                    'accounting_expense.amount_type as amountType',
                                ])
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_bank_deposit.id and accounting_expense.expense_type_id = 4')
                                ->andWhere(['accounting_expense.season_id' => $season])
                                ->andWhere(['between', 'accounting_expense.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
                                ->asArray()
                                ->all();

            $operatingExpenses = OperatingExpense::find()
                                ->select([
                                    'DATE(accounting_expense.datetime) as date',
                                    'accounting_expense_operating_expense.cv_no',
                                    'accounting_expense_operating_expense.particulars',
                                    'accounting_expense_operating_expense.staff_salary',
                                    'accounting_expense_operating_expense.cash_pf',
                                    'accounting_expense_operating_expense.rent',
                                    'accounting_expense_operating_expense.utilities',
                                    'accounting_expense_operating_expense.equipment_and_labor',
                                    'accounting_expense_operating_expense.bir_and_docs',
                                    'accounting_expense_operating_expense.marketing',
                                    'accounting_expense_operating_expense.charge_to',
                                    'accounting_expense.amount_type as amountType',
                                ])
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_operating_expense.id and accounting_expense.expense_type_id = 5')
                                ->andWhere(['accounting_expense.season_id' => $season])
                                ->andWhere(['between', 'accounting_expense.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
                                ->asArray()
                                ->all();

            return $this->renderAjax('_generate_expense_generation', [
                'id' => $id,
                'season' => $season,
                'branchProgram' => $branchProgram,
                'selectedSeason' => $selectedSeason,
                'selectedBranchProgram' => $selectedBranchProgram,
                'cutoff' => $cutoff,
                'dates' => $dates,
                'pettyExpenses' => $pettyExpenses,
                'photocopyExpenses' => $photocopyExpenses,
                'otherExpenses' => $otherExpenses,
                'bankDeposits' => $bankDeposits,
                'operatingExpenses' => $operatingExpenses,
            ]);
        }    
    }

    public function actionExtractExpenseGeneration($id, $branchProgram = '', $season = '')
    {
        if($id!="" && $season!="")
        {
            $selectedSeason = Season::findOne($season);
            $selectedBranchProgram = BranchProgram::find()
                                    ->select([
                                        'concat(accounting_branch.name," - ",accounting_program.name) as name'
                                    ])
                                    ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                    ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                    ->andWhere(['accounting_branch_program.id' => $branchProgram])
                                    ->asArray()
                                    ->one();

            $cutoff = $this->check_in_cutoff($id);
            $dates = $this->dateRange($cutoff['start'], $cutoff['end']);

            $pettyExpenses = PettyExpense::find()
                                ->select([
                                    'DATE(accounting_expense.datetime) as date',
                                    'accounting_expense_petty_expense.pcv_no',
                                    'accounting_expense_petty_expense.particulars',
                                    'accounting_expense_petty_expense.food',
                                    'accounting_expense_petty_expense.supplies',
                                    'accounting_expense_petty_expense.load',
                                    'accounting_expense_petty_expense.fare',
                                    'accounting_expense_petty_expense.charge_to',
                                    'accounting_expense.amount_type as amountType',
                                ])
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_petty_expense.id and accounting_expense.expense_type_id = 1')
                                ->andWhere(['accounting_expense.season_id' => $season])
                                ->andWhere(['between', 'accounting_expense.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
                                ->asArray()
                                ->all();

            $photocopyExpenses = PhotocopyExpense::find()
                                ->select([
                                    'DATE(accounting_expense.datetime) as date',
                                    'accounting_expense_photocopy_expense.cv_no',
                                    'accounting_expense_photocopy_expense.subject',
                                    'accounting_expense_photocopy_expense.no_of_pages',
                                    'accounting_expense_photocopy_expense.no_of_pieces',
                                    'accounting_expense_photocopy_expense.amount_per_page',
                                    'accounting_expense_photocopy_expense.total_amount',
                                    'accounting_expense_photocopy_expense.charge_to',
                                    'accounting_expense.amount_type as amountType',
                                ])
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                                ->andWhere(['accounting_expense.season_id' => $season])
                                ->andWhere(['between', 'accounting_expense.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
                                ->asArray()
                                ->all();

            $otherExpenses = OtherExpense::find()
                                ->select([
                                    'DATE(accounting_expense.datetime) as date',
                                    'accounting_expense_other_expense.cv_no',
                                    'accounting_expense_other_expense.particulars',
                                    'accounting_expense_other_expense.amount',
                                    'accounting_expense_other_expense.charge_to',
                                    'accounting_expense.amount_type as amountType',
                                ])
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_other_expense.id and accounting_expense.expense_type_id = 3')
                                ->andWhere(['accounting_expense.season_id' => $season])
                                ->andWhere(['between', 'accounting_expense.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
                                ->asArray()
                                ->all();

            $bankDeposits = BankDeposit::find()
                                ->select([
                                    'DATE(accounting_expense.datetime) as date',
                                    'accounting_expense_bank_deposit.bank',
                                    'accounting_expense_bank_deposit.account_no',
                                    'accounting_expense_bank_deposit.transaction_no',
                                    'accounting_expense_bank_deposit.deposited_by',
                                    'accounting_expense_bank_deposit.remarks',
                                    'accounting_expense_bank_deposit.amount',
                                    'accounting_expense.amount_type as amountType',
                                ])
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_bank_deposit.id and accounting_expense.expense_type_id = 4')
                                ->andWhere(['accounting_expense.season_id' => $season])
                                ->andWhere(['between', 'accounting_expense.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
                                ->asArray()
                                ->all();

            $operatingExpenses = OperatingExpense::find()
                                ->select([
                                    'DATE(accounting_expense.datetime) as date',
                                    'accounting_expense_operating_expense.cv_no',
                                    'accounting_expense_operating_expense.particulars',
                                    'accounting_expense_operating_expense.staff_salary',
                                    'accounting_expense_operating_expense.cash_pf',
                                    'accounting_expense_operating_expense.rent',
                                    'accounting_expense_operating_expense.utilities',
                                    'accounting_expense_operating_expense.equipment_and_labor',
                                    'accounting_expense_operating_expense.bir_and_docs',
                                    'accounting_expense_operating_expense.marketing',
                                    'accounting_expense_operating_expense.charge_to',
                                    'accounting_expense.amount_type as amountType',
                                ])
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_operating_expense.id and accounting_expense.expense_type_id = 5')
                                ->andWhere(['accounting_expense.season_id' => $season])
                                ->andWhere(['between', 'accounting_expense.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
                                ->asArray()
                                ->all();

            $content = $this->renderPartial('_report_expense_generation', [
                'id' => $id,
                'season' => $season,
                'branchProgram' => $branchProgram,
                'selectedSeason' => $selectedSeason,
                'selectedBranchProgram' => $selectedBranchProgram,
                'cutoff' => $cutoff,
                'dates' => $dates,
                'pettyExpenses' => $pettyExpenses,
                'photocopyExpenses' => $photocopyExpenses,
                'otherExpenses' => $otherExpenses,
                'bankDeposits' => $bankDeposits,
                'operatingExpenses' => $operatingExpenses,
            ]);

            $title = 'Daily Expense: '.$selectedBranchProgram['name'].' - SEASON '.$selectedSeason->name.' - ('.$cutoff['start'].' - '.$cutoff['end'].')';

            $pdf = new Pdf([
            'mode' => Pdf::MODE_CORE,
            'format' => Pdf::FORMAT_LEGAL, 
            'orientation' => Pdf::ORIENT_LANDSCAPE, 
            'destination' => Pdf::DEST_DOWNLOAD, 
            'filename' => $title.'.pdf',
            'content' => $content,  
            'marginLeft' => 11.4,
            'marginRight' => 11.4,
            'cssInline' => 'th { text-align:center; 
                                      padding:6px 6px 6px 6px;
                                      font-size:12px;
                                      }
                            .title {
                                      font-size:16px;
                                      font-weight:bold;
                                      }
                            td { padding:8px 4px 4px 4px; font-size:12px;
                          }', 
            'options' => ['title' => $title],
            'methods' => [ 
                'SetHeader'=>[$title], 
                'SetFooter'=>['Page {PAGENO}'],
            ]
            ]);
            $response = Yii::$app->response;
            $response->format = \yii\web\Response::FORMAT_RAW;
            $headers = Yii::$app->response->headers;
            $headers->add('Content-Type', 'application/pdf');
            return $pdf->render();   
        }    
    }

    public function actionGenerateDailyAudit($id, $branchProgram = '')
    {
        if($id!="" && $branchProgram!="")
        {
            $data = [];
            $selectedBranchProgram = BranchProgram::find()
                                    ->andWhere(['id' => $branchProgram])
                                    ->one();

            $cutoff = $this->check_in_cutoff($id);
            $dates = $this->dateRange($cutoff['start'], $cutoff['end']);

            $beginningCoh = BeginningCoh::find()->where(['date(datetime)' => $cutoff['start'], 'branch_program_id' => $selectedBranchProgram->id])->orderBy(['datetime' => SORT_DESC])->one();

            $denominations = Audit::find()
                                ->select([
                                    'accounting_denomination.denomination as denomination',
                                    'accounting_audit.total as value'
                                ])
                                ->leftJoin('accounting_denomination','accounting_denomination.id = accounting_audit.denomination_id')
                                ->andWhere(['between', 'accounting_audit.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
                                ->andWhere(['accounting_audit.branch_program_id' => $selectedBranchProgram->id])
                                ->asArray()
                                ->all();

            $incomeEnrolments = IncomeEnrolment::find()
                                ->select([
                                    'DATE(accounting_income.datetime) as date',
                                    'sum(COALESCE(accounting_income_enrolment.amount,0)) as total',
                                    'accounting_income.amount_type as amountType',
                                ])
                                ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_enrolment.id and accounting_income.income_type_id = 1')
                                ->leftJoin('accounting_income_code','accounting_income_code.id = accounting_income_enrolment.code_id')
                                ->leftJoin('accounting_student','accounting_student.id = accounting_income_enrolment.student_id')
                                ->andWhere(['accounting_income.branch_id' => $selectedBranchProgram->branch_id, 'accounting_income.program_id' => $selectedBranchProgram->program_id])
                                ->andWhere(['between', 'accounting_income.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
                                ->groupBy(['DATE(accounting_income.datetime)', 'amountType'])
                                ->asArray()
                                ->all();

            if(!empty($incomeEnrolments))
            {
                foreach($incomeEnrolments as $enrolment)
                {
                    if($enrolment['amountType'] == 'Cash')
                    {
                        $data['incomeEnrolments'][$enrolment['amountType']][$enrolment['date']] = $enrolment;
                    }else{
                        $data['incomeEnrolments']['Non-Cash'][$enrolment['date']] = $enrolment;
                    }
                }
            }

            $freebies = FreebieAndIcon::find()
                                ->select([
                                    'DATE(accounting_income.datetime) as date',
                                    'sum(COALESCE(accounting_income_freebies_and_icons.amount,0)) as total',
                                    'accounting_income.amount_type as amountType',
                                ])
                                ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                                ->leftJoin('accounting_income_code','accounting_income_code.id = accounting_income_freebies_and_icons.code_id')
                                ->leftJoin('accounting_student','accounting_student.id = accounting_income_freebies_and_icons.student_id')
                                ->andWhere(['accounting_income.branch_id' => $selectedBranchProgram->branch_id, 'accounting_income.program_id' => $selectedBranchProgram->program_id])
                                ->andWhere(['between', 'accounting_income.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
                                ->groupBy(['DATE(accounting_income.datetime)', 'amountType'])
                                ->asArray()
                                ->all();

            if(!empty($freebies))
            {
                foreach($freebies as $freebie)
                {
                    if($freebie['amountType'] == 'Cash')
                    {
                        $data['freebies'][$freebie['amountType']][$freebie['date']] = $freebie;
                    }else{
                        $data['freebies']['Non-Cash'][$freebie['date']] = $freebie;
                    }
                }
            }

            $budgetProposals = BudgetProposal::find()
                                ->select([
                                    'DATE(accounting_income.datetime) as date',
                                    'sum(COALESCE(particulars.total, 0)) as total',
                                    'accounting_income.amount_type as amountType',
                                ])
                                ->leftJoin(['particulars' => '(
                                    (
                                        SELECT
                                            accounting_budget_proposal_particular.budget_proposal_id,
                                            sum(accounting_budget_proposal_particular.amount) as total
                                        from accounting_budget_proposal_particular
                                        LEFT JOIN accounting_income_budget_proposal on accounting_income_budget_proposal.id = accounting_budget_proposal_particular.budget_proposal_id
                                        WHERE accounting_budget_proposal_particular.approval_status = "Approved"
                                        GROUP BY accounting_budget_proposal_particular.budget_proposal_id
                                    )
                                )'], 'particulars.budget_proposal_id = accounting_income_budget_proposal.id')
                                ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_budget_proposal.id and accounting_income.income_type_id = 3')
                                ->leftJoin('accounting_budget_proposal_type','accounting_budget_proposal_type.id = accounting_income_budget_proposal.budget_proposal_type_id')
                                ->leftJoin('accounting_income_code','accounting_income_code.id = accounting_income_budget_proposal.code_id')
                                ->andWhere(['accounting_income_budget_proposal.approval_status' => 'Approved'])
                                ->andWhere(['between', 'accounting_income.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
                                ->andWhere(['accounting_income.branch_id' => $selectedBranchProgram->branch_id, 'accounting_income.program_id' => $selectedBranchProgram->program_id])
                                ->groupBy(['DATE(accounting_income.datetime)', 'amountType'])
                                ->asArray()
                                ->all();

            if(!empty($budgetProposals))
            {
                foreach($budgetProposals as $budgetProposal)
                {
                    if($budgetProposal['amountType'] == 'Cash')
                    {
                        $data['budgetProposals'][$budgetProposal['amountType']][$budgetProposal['date']] = $budgetProposal;
                    }else{
                        $data['budgetProposals']['Non-Cash'][$budgetProposal['date']] = $budgetProposal;
                    }
                }
            }

            $pettyExpenses = PettyExpense::find()
                                ->select([
                                    'DATE(accounting_expense.datetime) as date',
                                    'sum(COALESCE(accounting_expense_petty_expense.food,0) + COALESCE(accounting_expense_petty_expense.supplies,0) + COALESCE(accounting_expense_petty_expense.load,0) + COALESCE(accounting_expense_petty_expense.fare,0)) as total',
                                    'accounting_expense.amount_type as amountType',
                                ])
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_petty_expense.id and accounting_expense.expense_type_id = 1')
                                ->andWhere(['accounting_expense.branch_id' => $selectedBranchProgram->branch_id, 'accounting_expense.program_id' => $selectedBranchProgram->program_id])
                                ->andWhere(['between', 'accounting_expense.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
                                ->groupBy(['DATE(accounting_expense.datetime)', 'amountType'])
                                ->asArray()
                                ->all();

            if(!empty($pettyExpenses))
            {
                foreach($pettyExpenses as $pettyExpense)
                {
                    if($pettyExpense['amountType'] == 'Cash')
                    {
                        $data['pettyExpenses'][$pettyExpense['amountType']][$pettyExpense['date']] = $pettyExpense;
                    }else{
                        $data['pettyExpenses']['Non-Cash'][$pettyExpense['date']] = $pettyExpense;
                    }
                }
            }

            $photocopyExpenses = PhotocopyExpense::find()
                                ->select([
                                    'DATE(accounting_expense.datetime) as date',
                                    'sum(COALESCE(accounting_expense_photocopy_expense.total_amount,0)) as total',
                                    'accounting_expense.amount_type as amountType',
                                ])
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                                ->andWhere(['accounting_expense.branch_id' => $selectedBranchProgram->branch_id, 'accounting_expense.program_id' => $selectedBranchProgram->program_id])
                                ->andWhere(['between', 'accounting_expense.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
                                ->groupBy(['DATE(accounting_expense.datetime)', 'amountType'])
                                ->asArray()
                                ->all();

            if(!empty($photocopyExpenses))
            {
                foreach($photocopyExpenses as $photocopyExpense)
                {
                    if($photocopyExpense['amountType'] == 'Cash')
                    {
                        $data['photocopyExpenses'][$photocopyExpense['amountType']][$photocopyExpense['date']] = $photocopyExpense;
                    }else{
                        $data['photocopyExpenses']['Non-Cash'][$photocopyExpense['date']] = $photocopyExpense;
                    }
                }
            }

            $otherExpenses = OtherExpense::find()
                                ->select([
                                    'DATE(accounting_expense.datetime) as date',
                                    'sum(COALESCE(accounting_expense_other_expense.amount,0)) as total',
                                    'accounting_expense.amount_type as amountType',
                                ])
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_other_expense.id and accounting_expense.expense_type_id = 3')
                                ->andWhere(['accounting_expense.branch_id' => $selectedBranchProgram->branch_id, 'accounting_expense.program_id' => $selectedBranchProgram->program_id])
                                ->andWhere(['between', 'accounting_expense.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
                                ->groupBy(['DATE(accounting_expense.datetime)', 'amountType'])
                                ->asArray()
                                ->all();

            if(!empty($otherExpenses))
            {
                foreach($otherExpenses as $otherExpense)
                {
                    if($otherExpense['amountType'] == 'Cash')
                    {
                        $data['otherExpenses'][$otherExpense['amountType']][$otherExpense['date']] = $otherExpense;
                    }else{
                        $data['otherExpenses']['Non-Cash'][$otherExpense['date']] = $otherExpense;
                    }
                }
            }

            $bankDeposits = BankDeposit::find()
                                ->select([
                                    'DATE(accounting_expense.datetime) as date',
                                    'sum(COALESCE(accounting_expense_bank_deposit.amount,0)) as total',
                                    'accounting_expense.amount_type as amountType',
                                ])
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_bank_deposit.id and accounting_expense.expense_type_id = 4')
                                ->andWhere(['accounting_expense.branch_id' => $selectedBranchProgram->branch_id, 'accounting_expense.program_id' => $selectedBranchProgram->program_id])
                                ->andWhere(['between', 'accounting_expense.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
                                ->groupBy(['DATE(accounting_expense.datetime)', 'amountType'])
                                ->asArray()
                                ->all();

            if(!empty($bankDeposits))
            {
                foreach($bankDeposits as $bankDeposit)
                {
                    if($bankDeposit['amountType'] == 'Cash')
                    {
                        $data['bankDeposits'][$bankDeposit['amountType']][$bankDeposit['date']] = $bankDeposit;
                    }else{
                        $data['bankDeposits']['Non-Cash'][$bankDeposit['date']] = $bankDeposit;
                    }
                }
            }

            $operatingExpenses = OperatingExpense::find()
                                ->select([
                                    'DATE(accounting_expense.datetime) as date',
                                    'sum(
                                        COALESCE(accounting_expense_operating_expense.staff_salary,0) + 
                                        COALESCE(accounting_expense_operating_expense.cash_pf,0) + 
                                        COALESCE(accounting_expense_operating_expense.rent,0) + 
                                        COALESCE(accounting_expense_operating_expense.utilities,0) + 
                                        COALESCE(accounting_expense_operating_expense.equipment_and_labor,0) + 
                                        COALESCE(accounting_expense_operating_expense.bir_and_docs,0) + 
                                        COALESCE(accounting_expense_operating_expense.marketing,0)
                                    ) as total',
                                    'accounting_expense.amount_type as amountType',
                                ])
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_operating_expense.id and accounting_expense.expense_type_id = 5')
                                ->andWhere(['accounting_expense.branch_id' => $selectedBranchProgram->branch_id, 'accounting_expense.program_id' => $selectedBranchProgram->program_id])
                                ->andWhere(['between', 'accounting_expense.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
                                ->groupBy(['DATE(accounting_expense.datetime)', 'amountType'])
                                ->asArray()
                                ->all();

            if(!empty($operatingExpenses))
            {
                foreach($operatingExpenses as $operatingExpense)
                {
                    if($operatingExpense['amountType'] == 'Cash')
                    {
                        $data['operatingExpenses'][$operatingExpense['amountType']][$operatingExpense['date']] = $operatingExpense;
                    }else{
                        $data['operatingExpenses']['Non-Cash'][$operatingExpense['date']] = $operatingExpense;
                    }
                }
            }

            $branchTransfers = BranchTransfer::find()
                                ->select([
                                    'DATE(accounting_expense.datetime) as date',
                                    'sum(COALESCE(particulars.total, 0)) as total',
                                    'accounting_expense.amount_type as amountType',
                                ])
                                ->leftJoin(['particulars' => '(
                                    (
                                        SELECT
                                            accounting_budget_proposal_particular.budget_proposal_id,
                                            sum(accounting_budget_proposal_particular.amount) as total
                                        from accounting_budget_proposal_particular
                                        LEFT JOIN accounting_income_budget_proposal on accounting_income_budget_proposal.id = accounting_budget_proposal_particular.budget_proposal_id
                                        WHERE accounting_budget_proposal_particular.approval_status = "Approved"
                                        GROUP BY accounting_budget_proposal_particular.budget_proposal_id
                                    )
                                )'], 'particulars.budget_proposal_id = accounting_expense_branch_transfer.budget_proposal_id')
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6')
                                ->andWhere(['between', 'accounting_expense.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
                                ->andWhere(['accounting_expense.branch_id' => $selectedBranchProgram->branch_id, 'accounting_expense.program_id' => $selectedBranchProgram->program_id])
                                ->andWhere(['accounting_expense_branch_transfer.amount_source' => 'Cash On Hand'])
                                ->groupBy(['DATE(accounting_expense.datetime)', 'amountType'])
                                ->asArray()
                                ->all();

            if(!empty($branchTransfers))
            {
                foreach($branchTransfers as $branchTransfer)
                {
                    if($branchTransfer['amountType'] == 'Cash')
                    {
                        $data['branchTransfers'][$branchTransfer['amountType']][$branchTransfer['date']] = $branchTransfer;
                    }else{
                        $data['branchTransfers']['Non-Cash'][$branchTransfer['date']] = $branchTransfer;
                    }
                }
            }

            return $this->renderAjax('_generate_daily_audit', [
                'id' => $id,
                'branchProgram' => $branchProgram,
                'denominations' => $denominations,
                'selectedBranchProgram' => $selectedBranchProgram,
                'cutoff' => $cutoff,
                'dates' => $dates,
                'beginningCoh' => $beginningCoh,
                'data' => $data
            ]);
        }     
    }

    public function actionExtractDailyAudit($id, $branchProgram = '')
    {
        if($id!="" && $branchProgram!="")
        {
            $data = [];
            $selectedBranchProgram = BranchProgram::find()
                                    ->andWhere(['id' => $branchProgram])
                                    ->one();

            $cutoff = $this->check_in_cutoff($id);
            $dates = $this->dateRange($cutoff['start'], $cutoff['end']);

            $beginningCoh = BeginningCoh::find()->where(['date(datetime)' => $cutoff['start'], 'branch_program_id' => $selectedBranchProgram->id])->orderBy(['datetime' => SORT_DESC])->one();

            $denominations = Audit::find()
                                ->select([
                                    'accounting_denomination.denomination as denomination',
                                    'accounting_audit.total as value'
                                ])
                                ->leftJoin('accounting_denomination','accounting_denomination.id = accounting_audit.denomination_id')
                                ->andWhere(['between', 'accounting_audit.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
                                ->andWhere(['accounting_audit.branch_program_id' => $selectedBranchProgram->id])
                                ->asArray()
                                ->all();

            $incomeEnrolments = IncomeEnrolment::find()
                                ->select([
                                    'DATE(accounting_income.datetime) as date',
                                    'sum(COALESCE(accounting_income_enrolment.amount,0)) as total',
                                    'accounting_income.amount_type as amountType',
                                ])
                                ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_enrolment.id and accounting_income.income_type_id = 1')
                                ->leftJoin('accounting_income_code','accounting_income_code.id = accounting_income_enrolment.code_id')
                                ->leftJoin('accounting_student','accounting_student.id = accounting_income_enrolment.student_id')
                                ->andWhere(['accounting_income.branch_id' => $selectedBranchProgram->branch_id, 'accounting_income.program_id' => $selectedBranchProgram->program_id])
                                ->andWhere(['between', 'accounting_income.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
                                ->groupBy(['DATE(accounting_income.datetime)', 'amountType'])
                                ->asArray()
                                ->all();

            if(!empty($incomeEnrolments))
            {
                foreach($incomeEnrolments as $enrolment)
                {
                    if($enrolment['amountType'] == 'Cash')
                    {
                        $data['incomeEnrolments'][$enrolment['amountType']][$enrolment['date']] = $enrolment;
                    }else{
                        $data['incomeEnrolments']['Non-Cash'][$enrolment['date']] = $enrolment;
                    }
                }
            }

            $freebies = FreebieAndIcon::find()
                                ->select([
                                    'DATE(accounting_income.datetime) as date',
                                    'sum(COALESCE(accounting_income_freebies_and_icons.amount,0)) as total',
                                    'accounting_income.amount_type as amountType',
                                ])
                                ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                                ->leftJoin('accounting_income_code','accounting_income_code.id = accounting_income_freebies_and_icons.code_id')
                                ->leftJoin('accounting_student','accounting_student.id = accounting_income_freebies_and_icons.student_id')
                                ->andWhere(['accounting_income.branch_id' => $selectedBranchProgram->branch_id, 'accounting_income.program_id' => $selectedBranchProgram->program_id])
                                ->andWhere(['between', 'accounting_income.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
                                ->groupBy(['DATE(accounting_income.datetime)', 'amountType'])
                                ->asArray()
                                ->all();

            if(!empty($freebies))
            {
                foreach($freebies as $freebie)
                {
                    if($freebie['amountType'] == 'Cash')
                    {
                        $data['freebies'][$freebie['amountType']][$freebie['date']] = $freebie;
                    }else{
                        $data['freebies']['Non-Cash'][$freebie['date']] = $freebie;
                    }
                }
            }

            $budgetProposals = BudgetProposal::find()
                                ->select([
                                    'DATE(accounting_income.datetime) as date',
                                    'sum(COALESCE(particulars.total, 0)) as total',
                                    'accounting_income.amount_type as amountType',
                                ])
                                ->leftJoin(['particulars' => '(
                                    (
                                        SELECT
                                            accounting_budget_proposal_particular.budget_proposal_id,
                                            sum(accounting_budget_proposal_particular.amount) as total
                                        from accounting_budget_proposal_particular
                                        LEFT JOIN accounting_income_budget_proposal on accounting_income_budget_proposal.id = accounting_budget_proposal_particular.budget_proposal_id
                                        WHERE accounting_budget_proposal_particular.approval_status = "Approved"
                                        GROUP BY accounting_budget_proposal_particular.budget_proposal_id
                                    )
                                )'], 'particulars.budget_proposal_id = accounting_income_budget_proposal.id')
                                ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_budget_proposal.id and accounting_income.income_type_id = 3')
                                ->leftJoin('accounting_budget_proposal_type','accounting_budget_proposal_type.id = accounting_income_budget_proposal.budget_proposal_type_id')
                                ->leftJoin('accounting_income_code','accounting_income_code.id = accounting_income_budget_proposal.code_id')
                                ->andWhere(['accounting_income_budget_proposal.approval_status' => 'Approved'])
                                ->andWhere(['between', 'accounting_income.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
                                ->andWhere(['accounting_income.branch_id' => $selectedBranchProgram->branch_id, 'accounting_income.program_id' => $selectedBranchProgram->program_id])
                                ->groupBy(['DATE(accounting_income.datetime)', 'amountType'])
                                ->asArray()
                                ->all();

            if(!empty($budgetProposals))
            {
                foreach($budgetProposals as $budgetProposal)
                {
                    if($budgetProposal['amountType'] == 'Cash')
                    {
                        $data['budgetProposals'][$budgetProposal['amountType']][$budgetProposal['date']] = $budgetProposal;
                    }else{
                        $data['budgetProposals']['Non-Cash'][$budgetProposal['date']] = $budgetProposal;
                    }
                }
            }

            $pettyExpenses = PettyExpense::find()
                                ->select([
                                    'DATE(accounting_expense.datetime) as date',
                                    'sum(COALESCE(accounting_expense_petty_expense.food,0) + COALESCE(accounting_expense_petty_expense.supplies,0) + COALESCE(accounting_expense_petty_expense.load,0) + COALESCE(accounting_expense_petty_expense.fare,0)) as total',
                                    'accounting_expense.amount_type as amountType',
                                ])
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_petty_expense.id and accounting_expense.expense_type_id = 1')
                                ->andWhere(['accounting_expense.branch_id' => $selectedBranchProgram->branch_id, 'accounting_expense.program_id' => $selectedBranchProgram->program_id])
                                ->andWhere(['between', 'accounting_expense.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
                                ->groupBy(['DATE(accounting_expense.datetime)', 'amountType'])
                                ->asArray()
                                ->all();

            if(!empty($pettyExpenses))
            {
                foreach($pettyExpenses as $pettyExpense)
                {
                    if($pettyExpense['amountType'] == 'Cash')
                    {
                        $data['pettyExpenses'][$pettyExpense['amountType']][$pettyExpense['date']] = $pettyExpense;
                    }else{
                        $data['pettyExpenses']['Non-Cash'][$pettyExpense['date']] = $pettyExpense;
                    }
                }
            }

            $photocopyExpenses = PhotocopyExpense::find()
                                ->select([
                                    'DATE(accounting_expense.datetime) as date',
                                    'sum(COALESCE(accounting_expense_photocopy_expense.total_amount,0)) as total',
                                    'accounting_expense.amount_type as amountType',
                                ])
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                                ->andWhere(['accounting_expense.branch_id' => $selectedBranchProgram->branch_id, 'accounting_expense.program_id' => $selectedBranchProgram->program_id])
                                ->andWhere(['between', 'accounting_expense.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
                                ->groupBy(['DATE(accounting_expense.datetime)', 'amountType'])
                                ->asArray()
                                ->all();

            if(!empty($photocopyExpenses))
            {
                foreach($photocopyExpenses as $photocopyExpense)
                {
                    if($photocopyExpense['amountType'] == 'Cash')
                    {
                        $data['photocopyExpenses'][$photocopyExpense['amountType']][$photocopyExpense['date']] = $photocopyExpense;
                    }else{
                        $data['photocopyExpenses']['Non-Cash'][$photocopyExpense['date']] = $photocopyExpense;
                    }
                }
            }

            $otherExpenses = OtherExpense::find()
                                ->select([
                                    'DATE(accounting_expense.datetime) as date',
                                    'sum(COALESCE(accounting_expense_other_expense.amount,0)) as total',
                                    'accounting_expense.amount_type as amountType',
                                ])
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_other_expense.id and accounting_expense.expense_type_id = 3')
                                ->andWhere(['accounting_expense.branch_id' => $selectedBranchProgram->branch_id, 'accounting_expense.program_id' => $selectedBranchProgram->program_id])
                                ->andWhere(['between', 'accounting_expense.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
                                ->groupBy(['DATE(accounting_expense.datetime)', 'amountType'])
                                ->asArray()
                                ->all();

            if(!empty($otherExpenses))
            {
                foreach($otherExpenses as $otherExpense)
                {
                    if($otherExpense['amountType'] == 'Cash')
                    {
                        $data['otherExpenses'][$otherExpense['amountType']][$otherExpense['date']] = $otherExpense;
                    }else{
                        $data['otherExpenses']['Non-Cash'][$otherExpense['date']] = $otherExpense;
                    }
                }
            }

            $bankDeposits = BankDeposit::find()
                                ->select([
                                    'DATE(accounting_expense.datetime) as date',
                                    'sum(COALESCE(accounting_expense_bank_deposit.amount,0)) as total',
                                    'accounting_expense.amount_type as amountType',
                                ])
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_bank_deposit.id and accounting_expense.expense_type_id = 4')
                                ->andWhere(['accounting_expense.branch_id' => $selectedBranchProgram->branch_id, 'accounting_expense.program_id' => $selectedBranchProgram->program_id])
                                ->andWhere(['between', 'accounting_expense.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
                                ->groupBy(['DATE(accounting_expense.datetime)', 'amountType'])
                                ->asArray()
                                ->all();

            if(!empty($bankDeposits))
            {
                foreach($bankDeposits as $bankDeposit)
                {
                    if($bankDeposit['amountType'] == 'Cash')
                    {
                        $data['bankDeposits'][$bankDeposit['amountType']][$bankDeposit['date']] = $bankDeposit;
                    }else{
                        $data['bankDeposits']['Non-Cash'][$bankDeposit['date']] = $bankDeposit;
                    }
                }
            }

            $operatingExpenses = OperatingExpense::find()
                                ->select([
                                    'DATE(accounting_expense.datetime) as date',
                                    'sum(
                                        COALESCE(accounting_expense_operating_expense.staff_salary,0) + 
                                        COALESCE(accounting_expense_operating_expense.cash_pf,0) + 
                                        COALESCE(accounting_expense_operating_expense.rent,0) + 
                                        COALESCE(accounting_expense_operating_expense.utilities,0) + 
                                        COALESCE(accounting_expense_operating_expense.equipment_and_labor,0) + 
                                        COALESCE(accounting_expense_operating_expense.bir_and_docs,0) + 
                                        COALESCE(accounting_expense_operating_expense.marketing,0)
                                    ) as total',
                                    'accounting_expense.amount_type as amountType',
                                ])
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_operating_expense.id and accounting_expense.expense_type_id = 5')
                                ->andWhere(['accounting_expense.branch_id' => $selectedBranchProgram->branch_id, 'accounting_expense.program_id' => $selectedBranchProgram->program_id])
                                ->andWhere(['between', 'accounting_expense.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
                                ->groupBy(['DATE(accounting_expense.datetime)', 'amountType'])
                                ->asArray()
                                ->all();

            if(!empty($operatingExpenses))
            {
                foreach($operatingExpenses as $operatingExpense)
                {
                    if($operatingExpense['amountType'] == 'Cash')
                    {
                        $data['operatingExpenses'][$operatingExpense['amountType']][$operatingExpense['date']] = $operatingExpense;
                    }else{
                        $data['operatingExpenses']['Non-Cash'][$operatingExpense['date']] = $operatingExpense;
                    }
                }
            }

            $branchTransfers = BranchTransfer::find()
                                ->select([
                                    'DATE(accounting_expense.datetime) as date',
                                    'sum(COALESCE(particulars.total, 0)) as total',
                                    'accounting_expense.amount_type as amountType',
                                ])
                                ->leftJoin(['particulars' => '(
                                    (
                                        SELECT
                                            accounting_budget_proposal_particular.budget_proposal_id,
                                            sum(accounting_budget_proposal_particular.amount) as total
                                        from accounting_budget_proposal_particular
                                        LEFT JOIN accounting_income_budget_proposal on accounting_income_budget_proposal.id = accounting_budget_proposal_particular.budget_proposal_id
                                        WHERE accounting_budget_proposal_particular.approval_status = "Approved"
                                        GROUP BY accounting_budget_proposal_particular.budget_proposal_id
                                    )
                                )'], 'particulars.budget_proposal_id = accounting_expense_branch_transfer.budget_proposal_id')
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6')
                                ->andWhere(['between', 'accounting_expense.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
                                ->andWhere(['accounting_expense.branch_id' => $selectedBranchProgram->branch_id, 'accounting_expense.program_id' => $selectedBranchProgram->program_id])
                                ->andWhere(['accounting_expense_branch_transfer.amount_source' => 'Cash On Hand'])
                                ->groupBy(['DATE(accounting_expense.datetime)', 'amountType'])
                                ->asArray()
                                ->all();

            if(!empty($branchTransfers))
            {
                foreach($branchTransfers as $branchTransfer)
                {
                    if($branchTransfer['amountType'] == 'Cash')
                    {
                        $data['branchTransfers'][$branchTransfer['amountType']][$branchTransfer['date']] = $branchTransfer;
                    }else{
                        $data['branchTransfers']['Non-Cash'][$branchTransfer['date']] = $branchTransfer;
                    }
                }
            }

            $content = $this->renderPartial('_report_daily_audit', [
                'id' => $id,
                'branchProgram' => $branchProgram,
                'denominations' => $denominations,
                'selectedBranchProgram' => $selectedBranchProgram,
                'cutoff' => $cutoff,
                'dates' => $dates,
                'beginningCoh' => $beginningCoh,
                'data' => $data
            ]);

            $title = 'Daily Audit: '.$selectedBranchProgram->branchProgramName.' - ('.$cutoff['start'].' - '.$cutoff['end'].')';

            $pdf = new Pdf([
            'mode' => Pdf::MODE_CORE,
            'format' => Pdf::FORMAT_LEGAL, 
            'orientation' => Pdf::ORIENT_LANDSCAPE, 
            'destination' => Pdf::DEST_DOWNLOAD, 
            'filename' => $title.'.pdf',
            'content' => $content,  
            'marginLeft' => 11.4,
            'marginRight' => 11.4,
            'cssInline' => 'th { text-align:center; 
                                      padding:6px 6px 6px 6px;
                                      font-size:12px;
                                      }
                            .title {
                                      font-size:16px;
                                      font-weight:bold;
                                      }
                            td { padding:8px 4px 4px 4px; font-size:12px;
                          }', 
            'options' => ['title' => $title],
            'methods' => [ 
                'SetHeader'=>[$title], 
                'SetFooter'=>['Page {PAGENO}'],
            ]
            ]);
            $response = Yii::$app->response;
            $response->format = \yii\web\Response::FORMAT_RAW;
            $headers = Yii::$app->response->headers;
            $headers->add('Content-Type', 'application/pdf');
            return $pdf->render();   
        }     
    }

    public function actionGenerateMonthlySummary($id, $branchProgram = '', $season = '')
    {
        if($id!="" && $season!="")
        {
            $selectedSeason = Season::findOne($season);
            $selectedBranchProgram = BranchProgram::find()
                                    ->select([
                                        'concat(accounting_branch.name," - ",accounting_program.name) as name'
                                    ])
                                    ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                    ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                    ->andWhere(['accounting_branch_program.id' => $branchProgram])
                                    ->asArray()
                                    ->one();

            $dates = explode(" - ", $id);
            $cutoffs = $this->produceCutoffs($dates[0], $dates[1]);
            $incomeEnrolmentQuery = '';
            $freebiesQuery = '';
            $pettyQuery = '';
            $photocopyQuery = '';
            $otherQuery = '';
            $bankQuery = '';
            $operatingQuery = '';
            $connection = Yii::$app->getDb();
            $incomeEnrolments = [];
            $freebies = [];
            $pettyExpenses = [];
            $photocopyExpenses = [];
            $otherExpenses = [];
            $bankDeposits = [];
            $operatingExpenses = [];

            $beginningCash = BeginningCoh::find()
                            ->select([
                                'COALESCE(sum(cash_on_hand), 0) as beginning_coh',
                                'COALESCE(sum(cash_on_bank), 0) as beginning_cob',
                            ])
                            ->andWhere(['between', 'datetime', $dates[0].' 00:00:00',$dates[1].' 23:59:59'])
                            ->asArray()
                            ->one();

            if(!empty($cutoffs))
            {
                foreach($cutoffs as $i => $cutoff)
                {
                    if($i < (count($cutoffs) - 1))
                    {
                        $incomeEnrolmentQuery.= IncomeEnrolment::find()
                                                ->select([
                                                    'date(min(accounting_income.datetime)) as cutoff',
                                                    'COALESCE(sum(accounting_income_enrolment.amount),0) as total',
                                                    'accounting_income.amount_type'
                                                ])
                                                ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_enrolment.id and accounting_income.income_type_id = 1')
                                                //->leftJoin('accounting_package_student', 'accounting_package_student.season_id = accounting_income_enrolment.season_id and accounting_package_student.student_id = accounting_income_enrolment.student_id')
                                                //->leftJoin('accounting_package', 'accounting_package.id = accounting_package_student.package_id')
                                                //->leftJoin('accounting_package_type', 'accounting_package_type.id = accounting_package.package_type_id')
                                                ->andWhere(['between', 'accounting_income.datetime',$cutoff['start'], $cutoff['end']])
                                                ->andWhere(['accounting_income_enrolment.season_id' => $season])
                                                //->andWhere(['not in', 'accounting_package_type.id', ['5','7']])
                                                ->groupBy(['accounting_income.amount_type'])
                                                ->createCommand()
                                                ->getRawSql();

                        $freebiesQuery.= FreebieAndIcon::find()
                                                ->select([
                                                    'date(min(accounting_income.datetime)) as cutoff',
                                                    'COALESCE(sum(accounting_income_freebies_and_icons.amount),0) as total',
                                                    'accounting_income.amount_type'
                                                ])
                                                ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                                                ->andWhere(['between', 'accounting_income.datetime',$cutoff['start'], $cutoff['end']])
                                                //->andWhere(['<>', 'code_id', '9'])
                                                ->andWhere(['accounting_income_freebies_and_icons.season_id' => $season])
                                                ->groupBy(['accounting_income.amount_type'])
                                                ->createCommand()
                                                ->getRawSql();

                        $pettyQuery.= PettyExpense::find()
                                                ->select([
                                                    'date(min(accounting_expense.datetime)) as cutoff',
                                                    'COALESCE(sum(accounting_expense_petty_expense.food),0) as foodTotal',
                                                    'COALESCE(sum(accounting_expense_petty_expense.supplies),0) as suppliesTotal',
                                                    'COALESCE(sum(accounting_expense_petty_expense.load),0) as loadTotal',
                                                    'COALESCE(sum(accounting_expense_petty_expense.fare),0) as fareTotal',
                                                    'accounting_expense.amount_type'
                                                ])
                                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_petty_expense.id and accounting_expense.expense_type_id = 1')
                                                ->andWhere(['between', 'accounting_expense.datetime',$cutoff['start'], $cutoff['end']])
                                                ->andWhere(['accounting_expense.season_id' => $season])
                                                //->andWhere(['<>', 'charge_to', 'Icon'])
                                                ->groupBy(['accounting_expense.amount_type'])
                                                ->createCommand()
                                                ->getRawSql();

                        $photocopyQuery.= PhotocopyExpense::find()
                                                ->select([
                                                    'date(min(accounting_expense.datetime)) as cutoff',
                                                    'COALESCE(sum(accounting_expense_photocopy_expense.total_amount),0) as total',
                                                    'accounting_expense.amount_type'
                                                ])
                                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                                                ->andWhere(['between', 'accounting_expense.datetime',$cutoff['start'], $cutoff['end']])
                                                ->andWhere(['accounting_expense.season_id' => $season])
                                                //->andWhere(['<>', 'charge_to', 'Icon'])
                                                ->groupBy(['accounting_expense.amount_type'])
                                                ->createCommand()
                                                ->getRawSql();

                        $otherQuery.= OtherExpense::find()
                                                ->select([
                                                    'date(min(accounting_expense.datetime)) as cutoff',
                                                    'COALESCE(sum(accounting_expense_other_expense.amount),0) as total',
                                                    'accounting_expense.amount_type'
                                                ])
                                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_other_expense.id and accounting_expense.expense_type_id = 3')
                                                ->andWhere(['between', 'accounting_expense.datetime',$cutoff['start'], $cutoff['end']])
                                                ->andWhere(['accounting_expense.season_id' => $season])
                                                //->andWhere(['<>', 'charge_to', 'Icon'])
                                                ->groupBy(['accounting_expense.amount_type'])
                                                ->createCommand()
                                                ->getRawSql();

                        $bankQuery.= BankDeposit::find()
                                                ->select([
                                                    'date(min(accounting_expense.datetime)) as cutoff',
                                                    'COALESCE(sum(accounting_expense_bank_deposit.amount),0) as total',
                                                    'accounting_expense.amount_type'
                                                ])
                                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_bank_deposit.id and accounting_expense.expense_type_id = 4')
                                                ->andWhere(['between', 'accounting_expense.datetime',$cutoff['start'], $cutoff['end']])
                                                ->andWhere(['accounting_expense.season_id' => $season])
                                                ->groupBy(['accounting_expense.amount_type'])
                                                ->createCommand()
                                                ->getRawSql();

                        $operatingQuery.= OperatingExpense::find()
                                                ->select([
                                                    'date(min(accounting_expense.datetime)) as cutoff',
                                                    'COALESCE(sum(accounting_expense_operating_expense.staff_salary),0) as staffSalaryTotal',
                                                    'COALESCE(sum(accounting_expense_operating_expense.cash_pf),0) as cashPfTotal',
                                                    'COALESCE(sum(accounting_expense_operating_expense.rent),0) as rentTotal',
                                                    'COALESCE(sum(accounting_expense_operating_expense.utilities),0) as utilitiesTotal',
                                                    'COALESCE(sum(accounting_expense_operating_expense.equipment_and_labor),0) as equipmentAndLaborTotal',
                                                    'COALESCE(sum(accounting_expense_operating_expense.bir_and_docs),0) as birAndDocsTotal',
                                                    'COALESCE(sum(accounting_expense_operating_expense.marketing),0) as marketingTotal',
                                                    'accounting_expense.amount_type'
                                                ])
                                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_operating_expense.id and accounting_expense.expense_type_id = 5')
                                                ->andWhere(['between', 'accounting_expense.datetime',$cutoff['start'], $cutoff['end']])
                                                ->andWhere(['accounting_expense.season_id' => $season])
                                                //->andWhere(['<>', 'charge_to', 'Icon'])
                                                ->groupBy(['accounting_expense.amount_type'])
                                                ->createCommand()
                                                ->getRawSql();

                        $incomeEnrolmentQuery.=' UNION ';
                        $freebiesQuery.=' UNION ';
                        $pettyQuery.=' UNION ';
                        $photocopyQuery.=' UNION ';
                        $otherQuery.=' UNION ';
                        $bankQuery.=' UNION ';
                        $operatingQuery.=' UNION ';
                    }else{
                        $incomeEnrolmentQuery.= IncomeEnrolment::find()
                                                ->select([
                                                    'date(min(accounting_income.datetime)) as cutoff',
                                                    'COALESCE(sum(accounting_income_enrolment.amount),0) as total',
                                                    'accounting_income.amount_type'
                                                ])
                                                ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_enrolment.id and accounting_income.income_type_id = 1')
                                                //->leftJoin('accounting_package_student', 'accounting_package_student.season_id = accounting_income_enrolment.season_id and accounting_package_student.student_id = accounting_income_enrolment.student_id')
                                                //->leftJoin('accounting_package', 'accounting_package.id = accounting_package_student.package_id')
                                                //->leftJoin('accounting_package_type', 'accounting_package_type.id = accounting_package.package_type_id')
                                                ->andWhere(['between', 'accounting_income.datetime',$cutoff['start'], $cutoff['end']])
                                                ->andWhere(['accounting_income_enrolment.season_id' => $season])
                                                //->andWhere(['not in', 'accounting_package_type.id', ['5','7']])
                                                ->groupBy(['accounting_income.amount_type'])
                                                ->createCommand()
                                                ->getRawSql();

                        $freebiesQuery.= FreebieAndIcon::find()
                                                ->select([
                                                    'date(min(accounting_income.datetime)) as cutoff',
                                                    'COALESCE(sum(accounting_income_freebies_and_icons.amount),0) as total',
                                                    'accounting_income.amount_type'
                                                ])
                                                ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                                                ->andWhere(['between', 'accounting_income.datetime',$cutoff['start'], $cutoff['end']])
                                                //->andWhere(['<>', 'code_id', '9'])
                                                ->andWhere(['accounting_income_freebies_and_icons.season_id' => $season])
                                                ->groupBy(['accounting_income.amount_type'])
                                                ->createCommand()
                                                ->getRawSql();

                        $pettyQuery.= PettyExpense::find()
                                                ->select([
                                                    'date(min(accounting_expense.datetime)) as cutoff',
                                                    'COALESCE(sum(accounting_expense_petty_expense.food),0) as foodTotal',
                                                    'COALESCE(sum(accounting_expense_petty_expense.supplies),0) as suppliesTotal',
                                                    'COALESCE(sum(accounting_expense_petty_expense.load),0) as loadTotal',
                                                    'COALESCE(sum(accounting_expense_petty_expense.fare),0) as fareTotal',
                                                    'accounting_expense.amount_type'
                                                ])
                                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_petty_expense.id and accounting_expense.expense_type_id = 1')
                                                ->andWhere(['between', 'accounting_expense.datetime',$cutoff['start'], $cutoff['end']])
                                                ->andWhere(['accounting_expense.season_id' => $season])
                                                //->andWhere(['<>', 'charge_to', 'Icon'])
                                                ->groupBy(['accounting_expense.amount_type'])
                                                ->createCommand()
                                                ->getRawSql();

                        $photocopyQuery.= PhotocopyExpense::find()
                                                ->select([
                                                    'date(min(accounting_expense.datetime)) as cutoff',
                                                    'COALESCE(sum(accounting_expense_photocopy_expense.total_amount),0) as total',
                                                    'accounting_expense.amount_type'
                                                ])
                                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                                                ->andWhere(['between', 'accounting_expense.datetime',$cutoff['start'], $cutoff['end']])
                                                ->andWhere(['accounting_expense.season_id' => $season])
                                                //->andWhere(['<>', 'charge_to', 'Icon'])
                                                ->groupBy(['accounting_expense.amount_type'])
                                                ->createCommand()
                                                ->getRawSql();

                        $otherQuery.= OtherExpense::find()
                                                ->select([
                                                    'date(min(accounting_expense.datetime)) as cutoff',
                                                    'COALESCE(sum(accounting_expense_other_expense.amount),0) as total',
                                                    'accounting_expense.amount_type'
                                                ])
                                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_other_expense.id and accounting_expense.expense_type_id = 3')
                                                ->andWhere(['between', 'accounting_expense.datetime',$cutoff['start'], $cutoff['end']])
                                                ->andWhere(['accounting_expense.season_id' => $season])
                                                //->andWhere(['<>', 'charge_to', 'Icon'])
                                                ->groupBy(['accounting_expense.amount_type'])
                                                ->createCommand()
                                                ->getRawSql();

                        $bankQuery.= BankDeposit::find()
                                                ->select([
                                                    'date(min(accounting_expense.datetime)) as cutoff',
                                                    'COALESCE(sum(accounting_expense_bank_deposit.amount),0) as total',
                                                    'accounting_expense.amount_type'
                                                ])
                                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_bank_deposit.id and accounting_expense.expense_type_id = 4')
                                                ->andWhere(['between', 'accounting_expense.datetime',$cutoff['start'], $cutoff['end']])
                                                ->andWhere(['accounting_expense.season_id' => $season])
                                                ->groupBy(['accounting_expense.amount_type'])
                                                ->createCommand()
                                                ->getRawSql();

                        $operatingQuery.= OperatingExpense::find()
                                                ->select([
                                                    'date(min(accounting_expense.datetime)) as cutoff',
                                                    'COALESCE(sum(accounting_expense_operating_expense.staff_salary),0) as staffSalaryTotal',
                                                    'COALESCE(sum(accounting_expense_operating_expense.cash_pf),0) as cashPfTotal',
                                                    'COALESCE(sum(accounting_expense_operating_expense.rent),0) as rentTotal',
                                                    'COALESCE(sum(accounting_expense_operating_expense.utilities),0) as utilitiesTotal',
                                                    'COALESCE(sum(accounting_expense_operating_expense.equipment_and_labor),0) as equipmentAndLaborTotal',
                                                    'COALESCE(sum(accounting_expense_operating_expense.bir_and_docs),0) as birAndDocsTotal',
                                                    'COALESCE(sum(accounting_expense_operating_expense.marketing),0) as marketingTotal',
                                                    'accounting_expense.amount_type'
                                                ])
                                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_operating_expense.id and accounting_expense.expense_type_id = 5')
                                                ->andWhere(['between', 'accounting_expense.datetime',$cutoff['start'], $cutoff['end']])
                                                ->andWhere(['accounting_expense.season_id' => $season])
                                                //->andWhere(['<>', 'charge_to', 'Icon'])
                                                ->groupBy(['accounting_expense.amount_type'])
                                                ->createCommand()
                                                ->getRawSql();
                    }
                }
            }

            if($incomeEnrolmentQuery!='')
            {
                $incomeEnrolmentCommand = $connection->createCommand($incomeEnrolmentQuery);
                $incomeEnrolments = $incomeEnrolmentCommand->queryAll();
            }

            if($freebiesQuery!='')
            {
                $freebiesCommand = $connection->createCommand($freebiesQuery);
                $freebies = $freebiesCommand->queryAll();
            }

            if($pettyQuery!='')
            {
                $pettyCommand = $connection->createCommand($pettyQuery);
                $pettyExpenses = $pettyCommand->queryAll();
            }

            if($photocopyQuery!='')
            {
                $photocopyCommand = $connection->createCommand($photocopyQuery);
                $photocopyExpenses = $photocopyCommand->queryAll();
            }

            if($otherQuery!='')
            {
                $otherCommand = $connection->createCommand($otherQuery);
                $otherExpenses = $otherCommand->queryAll();
            }

            if($bankQuery!='')
            {
                $bankCommand = $connection->createCommand($bankQuery);
                $bankDeposits = $bankCommand->queryAll();
            }

            if($operatingQuery!='')
            {
                $operatingCommand = $connection->createCommand($operatingQuery);
                $operatingExpenses = $operatingCommand->queryAll();
            }

            $cashData = [];
            $nonCashData = []; 
            $totals  = [];

            if(!empty($incomeEnrolments))
            {
                foreach($incomeEnrolments as $enrolment)
                {
                    if($enrolment['cutoff'] != '')
                    {
                        if($enrolment['amount_type'] == 'Cash')
                        {
                            $cashData['incomeEnrolments'][$this->check_in_cutoff_key($enrolment['cutoff'])] = $enrolment['total'];
                        }else{
                            $nonCashData['incomeEnrolments'][$this->check_in_cutoff_key($enrolment['cutoff'])] = $enrolment['total'];
                        }
                    }
                }
            }

            if(!empty($freebies))
            {
                foreach($freebies as $freebie)
                {
                    if($freebie['cutoff'] != '')
                    {
                        if($freebie['amount_type'] == 'Cash')
                        {
                            $cashData['freebies'][$this->check_in_cutoff_key($freebie['cutoff'])] = $freebie['total'];
                        }else{
                            $nonCashData['freebies'][$this->check_in_cutoff_key($freebie['cutoff'])] = $freebie['total'];
                        }
                    }
                }
            }

            if(!empty($pettyExpenses))
            {
                foreach($pettyExpenses as $petty)
                {
                    if($petty['cutoff'] != '')
                    {
                        if($petty['amount_type'] == 'Cash')
                        {
                            $cashData['pettyExpenses'][$this->check_in_cutoff_key($petty['cutoff'])]['food'] = $petty['foodTotal'];
                            $cashData['pettyExpenses'][$this->check_in_cutoff_key($petty['cutoff'])]['supplies'] = $petty['suppliesTotal'];
                            $cashData['pettyExpenses'][$this->check_in_cutoff_key($petty['cutoff'])]['load'] = $petty['loadTotal'];
                            $cashData['pettyExpenses'][$this->check_in_cutoff_key($petty['cutoff'])]['fare'] = $petty['fareTotal'];
                        }else{
                            $nonCashData['pettyExpenses'][$this->check_in_cutoff_key($petty['cutoff'])]['food'] = $petty['foodTotal'];
                            $nonCashData['pettyExpenses'][$this->check_in_cutoff_key($petty['cutoff'])]['supplies'] = $petty['suppliesTotal'];
                            $nonCashData['pettyExpenses'][$this->check_in_cutoff_key($petty['cutoff'])]['load'] = $petty['loadTotal'];
                            $nonCashData['pettyExpenses'][$this->check_in_cutoff_key($petty['cutoff'])]['fare'] = $petty['fareTotal'];
                        }
                    }
                }
            }

            if(!empty($photocopyExpenses))
            {
                foreach($photocopyExpenses as $photocopy)
                {
                    if($photocopy['cutoff'] != '')
                    {
                        if($photocopy['amount_type'] == 'Cash')
                        {
                            $cashData['photocopyExpenses'][$this->check_in_cutoff_key($photocopy['cutoff'])] = $photocopy['total'];
                        }else{
                            $nonCashData['photocopyExpenses'][$this->check_in_cutoff_key($photocopy['cutoff'])] = $photocopy['total'];
                        }
                    }
                }
            }

            if(!empty($otherExpenses))
            {
                foreach($otherExpenses as $other)
                {
                    if($other['cutoff'] != '')
                    {
                        if($other['amount_type'] == 'Cash')
                        {
                            $cashData['otherExpenses'][$this->check_in_cutoff_key($other['cutoff'])] = $other['total'];
                        }else{
                            $nonCashData['otherExpenses'][$this->check_in_cutoff_key($other['cutoff'])] = $other['total'];
                        }
                    }
                }
            }

            if(!empty($bankDeposits))
            {
                foreach($bankDeposits as $bank)
                {
                    if($bank['cutoff'] != '')
                    {
                        if($bank['amount_type'] == 'Cash')
                        {
                            $cashData['bankDeposits'][$this->check_in_cutoff_key($bank['cutoff'])] = $bank['total'];
                        }else{
                            $nonCashData['bankDeposits'][$this->check_in_cutoff_key($bank['cutoff'])] = $bank['total'];
                        }
                    }
                }
            }

            if(!empty($operatingExpenses))
            {
                foreach($operatingExpenses as $operating)
                {
                    if($operating['cutoff'] != '')
                    {
                        $data['operatingExpenses'][$this->check_in_cutoff_key($operating['cutoff'])]['staffSalary'] = $operating['staffSalaryTotal'];
                        $data['operatingExpenses'][$this->check_in_cutoff_key($operating['cutoff'])]['cashPf'] = $operating['cashPfTotal'];
                        $data['operatingExpenses'][$this->check_in_cutoff_key($operating['cutoff'])]['rent'] = $operating['rentTotal'];
                        $data['operatingExpenses'][$this->check_in_cutoff_key($operating['cutoff'])]['utilities'] = $operating['utilitiesTotal'];
                        $data['operatingExpenses'][$this->check_in_cutoff_key($operating['cutoff'])]['equipmentAndLabor'] = $operating['equipmentAndLaborTotal'];
                        $data['operatingExpenses'][$this->check_in_cutoff_key($operating['cutoff'])]['birAndDocs'] = $operating['birAndDocsTotal'];
                        $data['operatingExpenses'][$this->check_in_cutoff_key($operating['cutoff'])]['marketing'] = $operating['marketingTotal'];
                    }

                    if($operating['cutoff'] != '')
                    {
                        if($operating['amount_type'] == 'Cash')
                        {
                            $cashData['operatingExpenses'][$this->check_in_cutoff_key($operating['cutoff'])]['staffSalary'] = $operating['staffSalaryTotal'];
                            $cashData['operatingExpenses'][$this->check_in_cutoff_key($operating['cutoff'])]['cashPf'] = $operating['cashPfTotal'];
                            $cashData['operatingExpenses'][$this->check_in_cutoff_key($operating['cutoff'])]['rent'] = $operating['rentTotal'];
                            $cashData['operatingExpenses'][$this->check_in_cutoff_key($operating['cutoff'])]['utilities'] = $operating['utilitiesTotal'];
                            $cashData['operatingExpenses'][$this->check_in_cutoff_key($operating['cutoff'])]['equipmentAndLabor'] = $operating['equipmentAndLaborTotal'];
                            $cashData['operatingExpenses'][$this->check_in_cutoff_key($operating['cutoff'])]['birAndDocs'] = $operating['birAndDocsTotal'];
                            $cashData['operatingExpenses'][$this->check_in_cutoff_key($operating['cutoff'])]['marketing'] = $operating['marketingTotal'];
                        }else{
                            $nonCashData['operatingExpenses'][$this->check_in_cutoff_key($operating['cutoff'])]['staffSalary'] = $operating['staffSalaryTotal'];
                            $nonCashData['operatingExpenses'][$this->check_in_cutoff_key($operating['cutoff'])]['cashPf'] = $operating['cashPfTotal'];
                            $nonCashData['operatingExpenses'][$this->check_in_cutoff_key($operating['cutoff'])]['rent'] = $operating['rentTotal'];
                            $nonCashData['operatingExpenses'][$this->check_in_cutoff_key($operating['cutoff'])]['utilities'] = $operating['utilitiesTotal'];
                            $nonCashData['operatingExpenses'][$this->check_in_cutoff_key($operating['cutoff'])]['equipmentAndLabor'] = $operating['equipmentAndLaborTotal'];
                            $nonCashData['operatingExpenses'][$this->check_in_cutoff_key($operating['cutoff'])]['birAndDocs'] = $operating['birAndDocsTotal'];
                            $nonCashData['operatingExpenses'][$this->check_in_cutoff_key($operating['cutoff'])]['marketing'] = $operating['marketingTotal'];
                        }
                    }
                }
            }

            return $this->renderAjax('_generate_monthly_summary', [
                'id' => $id,
                'season' => $season,
                'branchProgram' => $branchProgram,
                'dates' => $dates,
                'selectedSeason' => $selectedSeason,
                'selectedBranchProgram' => $selectedBranchProgram,
                'cutoffs' => $cutoffs,
                'cashData' => $cashData,
                'nonCashData' => $nonCashData,
                'beginningCash' => $beginningCash,
            ]);
        }
    }

    public function actionExtractMonthlySummary($id, $branchProgram = '', $season = '')
    {
        if($id!="" && $season!="")
        {
            $selectedSeason = Season::findOne($season);
            $selectedBranchProgram = BranchProgram::find()
                                    ->select([
                                        'concat(accounting_branch.name," - ",accounting_program.name) as name'
                                    ])
                                    ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                    ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                    ->andWhere(['accounting_branch_program.id' => $branchProgram])
                                    ->asArray()
                                    ->one();

            $dates = explode(" - ", $id);
            $cutoffs = $this->produceCutoffs($dates[0], $dates[1]);
            $incomeEnrolmentQuery = '';
            $freebiesQuery = '';
            $pettyQuery = '';
            $photocopyQuery = '';
            $otherQuery = '';
            $bankQuery = '';
            $operatingQuery = '';
            $connection = Yii::$app->getDb();
            $incomeEnrolments = [];
            $freebies = [];
            $pettyExpenses = [];
            $photocopyExpenses = [];
            $otherExpenses = [];
            $bankDeposits = [];
            $operatingExpenses = [];

            $beginningCash = BeginningCoh::find()
                            ->select([
                                'COALESCE(sum(cash_on_hand), 0) as beginning_coh',
                                'COALESCE(sum(cash_on_bank), 0) as beginning_cob',
                            ])
                            ->andWhere(['between', 'datetime', $dates[0].' 00:00:00',$dates[1].' 23:59:59'])
                            ->asArray()
                            ->one();

            if(!empty($cutoffs))
            {
                foreach($cutoffs as $i => $cutoff)
                {
                    if($i < (count($cutoffs) - 1))
                    {
                        $incomeEnrolmentQuery.= IncomeEnrolment::find()
                                                ->select([
                                                    'date(min(accounting_income.datetime)) as cutoff',
                                                    'COALESCE(sum(accounting_income_enrolment.amount),0) as total',
                                                    'accounting_income.amount_type'
                                                ])
                                                ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_enrolment.id and accounting_income.income_type_id = 1')
                                                //->leftJoin('accounting_package_student', 'accounting_package_student.season_id = accounting_income_enrolment.season_id and accounting_package_student.student_id = accounting_income_enrolment.student_id')
                                                //->leftJoin('accounting_package', 'accounting_package.id = accounting_package_student.package_id')
                                                //->leftJoin('accounting_package_type', 'accounting_package_type.id = accounting_package.package_type_id')
                                                ->andWhere(['between', 'accounting_income.datetime',$cutoff['start'], $cutoff['end']])
                                                ->andWhere(['accounting_income_enrolment.season_id' => $season])
                                                //->andWhere(['not in', 'accounting_package_type.id', ['5','7']])
                                                ->groupBy(['accounting_income.amount_type'])
                                                ->createCommand()
                                                ->getRawSql();

                        $freebiesQuery.= FreebieAndIcon::find()
                                                ->select([
                                                    'date(min(accounting_income.datetime)) as cutoff',
                                                    'COALESCE(sum(accounting_income_freebies_and_icons.amount),0) as total',
                                                    'accounting_income.amount_type'
                                                ])
                                                ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                                                ->andWhere(['between', 'accounting_income.datetime',$cutoff['start'], $cutoff['end']])
                                                //->andWhere(['<>', 'code_id', '9'])
                                                ->andWhere(['accounting_income_freebies_and_icons.season_id' => $season])
                                                ->groupBy(['accounting_income.amount_type'])
                                                ->createCommand()
                                                ->getRawSql();

                        $pettyQuery.= PettyExpense::find()
                                                ->select([
                                                    'date(min(accounting_expense.datetime)) as cutoff',
                                                    'COALESCE(sum(accounting_expense_petty_expense.food),0) as foodTotal',
                                                    'COALESCE(sum(accounting_expense_petty_expense.supplies),0) as suppliesTotal',
                                                    'COALESCE(sum(accounting_expense_petty_expense.load),0) as loadTotal',
                                                    'COALESCE(sum(accounting_expense_petty_expense.fare),0) as fareTotal',
                                                    'accounting_expense.amount_type'
                                                ])
                                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_petty_expense.id and accounting_expense.expense_type_id = 1')
                                                ->andWhere(['between', 'accounting_expense.datetime',$cutoff['start'], $cutoff['end']])
                                                ->andWhere(['accounting_expense.season_id' => $season])
                                                //->andWhere(['<>', 'charge_to', 'Icon'])
                                                ->groupBy(['accounting_expense.amount_type'])
                                                ->createCommand()
                                                ->getRawSql();

                        $photocopyQuery.= PhotocopyExpense::find()
                                                ->select([
                                                    'date(min(accounting_expense.datetime)) as cutoff',
                                                    'COALESCE(sum(accounting_expense_photocopy_expense.total_amount),0) as total',
                                                    'accounting_expense.amount_type'
                                                ])
                                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                                                ->andWhere(['between', 'accounting_expense.datetime',$cutoff['start'], $cutoff['end']])
                                                ->andWhere(['accounting_expense.season_id' => $season])
                                                //->andWhere(['<>', 'charge_to', 'Icon'])
                                                ->groupBy(['accounting_expense.amount_type'])
                                                ->createCommand()
                                                ->getRawSql();

                        $otherQuery.= OtherExpense::find()
                                                ->select([
                                                    'date(min(accounting_expense.datetime)) as cutoff',
                                                    'COALESCE(sum(accounting_expense_other_expense.amount),0) as total',
                                                    'accounting_expense.amount_type'
                                                ])
                                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_other_expense.id and accounting_expense.expense_type_id = 3')
                                                ->andWhere(['between', 'accounting_expense.datetime',$cutoff['start'], $cutoff['end']])
                                                ->andWhere(['accounting_expense.season_id' => $season])
                                                //->andWhere(['<>', 'charge_to', 'Icon'])
                                                ->groupBy(['accounting_expense.amount_type'])
                                                ->createCommand()
                                                ->getRawSql();

                        $bankQuery.= BankDeposit::find()
                                                ->select([
                                                    'date(min(accounting_expense.datetime)) as cutoff',
                                                    'COALESCE(sum(accounting_expense_bank_deposit.amount),0) as total',
                                                    'accounting_expense.amount_type'
                                                ])
                                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_bank_deposit.id and accounting_expense.expense_type_id = 4')
                                                ->andWhere(['between', 'accounting_expense.datetime',$cutoff['start'], $cutoff['end']])
                                                ->andWhere(['accounting_expense.season_id' => $season])
                                                ->groupBy(['accounting_expense.amount_type'])
                                                ->createCommand()
                                                ->getRawSql();

                        $operatingQuery.= OperatingExpense::find()
                                                ->select([
                                                    'date(min(accounting_expense.datetime)) as cutoff',
                                                    'COALESCE(sum(accounting_expense_operating_expense.staff_salary),0) as staffSalaryTotal',
                                                    'COALESCE(sum(accounting_expense_operating_expense.cash_pf),0) as cashPfTotal',
                                                    'COALESCE(sum(accounting_expense_operating_expense.rent),0) as rentTotal',
                                                    'COALESCE(sum(accounting_expense_operating_expense.utilities),0) as utilitiesTotal',
                                                    'COALESCE(sum(accounting_expense_operating_expense.equipment_and_labor),0) as equipmentAndLaborTotal',
                                                    'COALESCE(sum(accounting_expense_operating_expense.bir_and_docs),0) as birAndDocsTotal',
                                                    'COALESCE(sum(accounting_expense_operating_expense.marketing),0) as marketingTotal',
                                                    'accounting_expense.amount_type'
                                                ])
                                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_operating_expense.id and accounting_expense.expense_type_id = 5')
                                                ->andWhere(['between', 'accounting_expense.datetime',$cutoff['start'], $cutoff['end']])
                                                ->andWhere(['accounting_expense.season_id' => $season])
                                                //->andWhere(['<>', 'charge_to', 'Icon'])
                                                ->groupBy(['accounting_expense.amount_type'])
                                                ->createCommand()
                                                ->getRawSql();

                        $incomeEnrolmentQuery.=' UNION ';
                        $freebiesQuery.=' UNION ';
                        $pettyQuery.=' UNION ';
                        $photocopyQuery.=' UNION ';
                        $otherQuery.=' UNION ';
                        $bankQuery.=' UNION ';
                        $operatingQuery.=' UNION ';
                    }else{
                        $incomeEnrolmentQuery.= IncomeEnrolment::find()
                                                ->select([
                                                    'date(min(accounting_income.datetime)) as cutoff',
                                                    'COALESCE(sum(accounting_income_enrolment.amount),0) as total',
                                                    'accounting_income.amount_type'
                                                ])
                                                ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_enrolment.id and accounting_income.income_type_id = 1')
                                                //->leftJoin('accounting_package_student', 'accounting_package_student.season_id = accounting_income_enrolment.season_id and accounting_package_student.student_id = accounting_income_enrolment.student_id')
                                                //->leftJoin('accounting_package', 'accounting_package.id = accounting_package_student.package_id')
                                                //->leftJoin('accounting_package_type', 'accounting_package_type.id = accounting_package.package_type_id')
                                                ->andWhere(['between', 'accounting_income.datetime',$cutoff['start'], $cutoff['end']])
                                                ->andWhere(['accounting_income_enrolment.season_id' => $season])
                                                //->andWhere(['not in', 'accounting_package_type.id', ['5','7']])
                                                ->groupBy(['accounting_income.amount_type'])
                                                ->createCommand()
                                                ->getRawSql();

                        $freebiesQuery.= FreebieAndIcon::find()
                                                ->select([
                                                    'date(min(accounting_income.datetime)) as cutoff',
                                                    'COALESCE(sum(accounting_income_freebies_and_icons.amount),0) as total',
                                                    'accounting_income.amount_type'
                                                ])
                                                ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                                                ->andWhere(['between', 'accounting_income.datetime',$cutoff['start'], $cutoff['end']])
                                                //->andWhere(['<>', 'code_id', '9'])
                                                ->andWhere(['accounting_income_freebies_and_icons.season_id' => $season])
                                                ->groupBy(['accounting_income.amount_type'])
                                                ->createCommand()
                                                ->getRawSql();

                        $pettyQuery.= PettyExpense::find()
                                                ->select([
                                                    'date(min(accounting_expense.datetime)) as cutoff',
                                                    'COALESCE(sum(accounting_expense_petty_expense.food),0) as foodTotal',
                                                    'COALESCE(sum(accounting_expense_petty_expense.supplies),0) as suppliesTotal',
                                                    'COALESCE(sum(accounting_expense_petty_expense.load),0) as loadTotal',
                                                    'COALESCE(sum(accounting_expense_petty_expense.fare),0) as fareTotal',
                                                    'accounting_expense.amount_type'
                                                ])
                                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_petty_expense.id and accounting_expense.expense_type_id = 1')
                                                ->andWhere(['between', 'accounting_expense.datetime',$cutoff['start'], $cutoff['end']])
                                                ->andWhere(['accounting_expense.season_id' => $season])
                                                //->andWhere(['<>', 'charge_to', 'Icon'])
                                                ->groupBy(['accounting_expense.amount_type'])
                                                ->createCommand()
                                                ->getRawSql();

                        $photocopyQuery.= PhotocopyExpense::find()
                                                ->select([
                                                    'date(min(accounting_expense.datetime)) as cutoff',
                                                    'COALESCE(sum(accounting_expense_photocopy_expense.total_amount),0) as total',
                                                    'accounting_expense.amount_type'
                                                ])
                                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                                                ->andWhere(['between', 'accounting_expense.datetime',$cutoff['start'], $cutoff['end']])
                                                ->andWhere(['accounting_expense.season_id' => $season])
                                                //->andWhere(['<>', 'charge_to', 'Icon'])
                                                ->groupBy(['accounting_expense.amount_type'])
                                                ->createCommand()
                                                ->getRawSql();

                        $otherQuery.= OtherExpense::find()
                                                ->select([
                                                    'date(min(accounting_expense.datetime)) as cutoff',
                                                    'COALESCE(sum(accounting_expense_other_expense.amount),0) as total',
                                                    'accounting_expense.amount_type'
                                                ])
                                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_other_expense.id and accounting_expense.expense_type_id = 3')
                                                ->andWhere(['between', 'accounting_expense.datetime',$cutoff['start'], $cutoff['end']])
                                                ->andWhere(['accounting_expense.season_id' => $season])
                                                //->andWhere(['<>', 'charge_to', 'Icon'])
                                                ->groupBy(['accounting_expense.amount_type'])
                                                ->createCommand()
                                                ->getRawSql();

                        $bankQuery.= BankDeposit::find()
                                                ->select([
                                                    'date(min(accounting_expense.datetime)) as cutoff',
                                                    'COALESCE(sum(accounting_expense_bank_deposit.amount),0) as total',
                                                    'accounting_expense.amount_type'
                                                ])
                                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_bank_deposit.id and accounting_expense.expense_type_id = 4')
                                                ->andWhere(['between', 'accounting_expense.datetime',$cutoff['start'], $cutoff['end']])
                                                ->andWhere(['accounting_expense.season_id' => $season])
                                                ->groupBy(['accounting_expense.amount_type'])
                                                ->createCommand()
                                                ->getRawSql();

                        $operatingQuery.= OperatingExpense::find()
                                                ->select([
                                                    'date(min(accounting_expense.datetime)) as cutoff',
                                                    'COALESCE(sum(accounting_expense_operating_expense.staff_salary),0) as staffSalaryTotal',
                                                    'COALESCE(sum(accounting_expense_operating_expense.cash_pf),0) as cashPfTotal',
                                                    'COALESCE(sum(accounting_expense_operating_expense.rent),0) as rentTotal',
                                                    'COALESCE(sum(accounting_expense_operating_expense.utilities),0) as utilitiesTotal',
                                                    'COALESCE(sum(accounting_expense_operating_expense.equipment_and_labor),0) as equipmentAndLaborTotal',
                                                    'COALESCE(sum(accounting_expense_operating_expense.bir_and_docs),0) as birAndDocsTotal',
                                                    'COALESCE(sum(accounting_expense_operating_expense.marketing),0) as marketingTotal',
                                                    'accounting_expense.amount_type'
                                                ])
                                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_operating_expense.id and accounting_expense.expense_type_id = 5')
                                                ->andWhere(['between', 'accounting_expense.datetime',$cutoff['start'], $cutoff['end']])
                                                ->andWhere(['accounting_expense.season_id' => $season])
                                                //->andWhere(['<>', 'charge_to', 'Icon'])
                                                ->groupBy(['accounting_expense.amount_type'])
                                                ->createCommand()
                                                ->getRawSql();
                    }
                }
            }

            if($incomeEnrolmentQuery!='')
            {
                $incomeEnrolmentCommand = $connection->createCommand($incomeEnrolmentQuery);
                $incomeEnrolments = $incomeEnrolmentCommand->queryAll();
            }

            if($freebiesQuery!='')
            {
                $freebiesCommand = $connection->createCommand($freebiesQuery);
                $freebies = $freebiesCommand->queryAll();
            }

            if($pettyQuery!='')
            {
                $pettyCommand = $connection->createCommand($pettyQuery);
                $pettyExpenses = $pettyCommand->queryAll();
            }

            if($photocopyQuery!='')
            {
                $photocopyCommand = $connection->createCommand($photocopyQuery);
                $photocopyExpenses = $photocopyCommand->queryAll();
            }

            if($otherQuery!='')
            {
                $otherCommand = $connection->createCommand($otherQuery);
                $otherExpenses = $otherCommand->queryAll();
            }

            if($bankQuery!='')
            {
                $bankCommand = $connection->createCommand($bankQuery);
                $bankDeposits = $bankCommand->queryAll();
            }

            if($operatingQuery!='')
            {
                $operatingCommand = $connection->createCommand($operatingQuery);
                $operatingExpenses = $operatingCommand->queryAll();
            }

            $cashData = [];
            $nonCashData = []; 
            $totals  = [];

            if(!empty($incomeEnrolments))
            {
                foreach($incomeEnrolments as $enrolment)
                {
                    if($enrolment['cutoff'] != '')
                    {
                        if($enrolment['amount_type'] == 'Cash')
                        {
                            $cashData['incomeEnrolments'][$this->check_in_cutoff_key($enrolment['cutoff'])] = $enrolment['total'];
                        }else{
                            $nonCashData['incomeEnrolments'][$this->check_in_cutoff_key($enrolment['cutoff'])] = $enrolment['total'];
                        }
                    }
                }
            }

            if(!empty($freebies))
            {
                foreach($freebies as $freebie)
                {
                    if($freebie['cutoff'] != '')
                    {
                        if($freebie['amount_type'] == 'Cash')
                        {
                            $cashData['freebies'][$this->check_in_cutoff_key($freebie['cutoff'])] = $freebie['total'];
                        }else{
                            $nonCashData['freebies'][$this->check_in_cutoff_key($freebie['cutoff'])] = $freebie['total'];
                        }
                    }
                }
            }

            if(!empty($pettyExpenses))
            {
                foreach($pettyExpenses as $petty)
                {
                    if($petty['cutoff'] != '')
                    {
                        if($petty['amount_type'] == 'Cash')
                        {
                            $cashData['pettyExpenses'][$this->check_in_cutoff_key($petty['cutoff'])]['food'] = $petty['foodTotal'];
                            $cashData['pettyExpenses'][$this->check_in_cutoff_key($petty['cutoff'])]['supplies'] = $petty['suppliesTotal'];
                            $cashData['pettyExpenses'][$this->check_in_cutoff_key($petty['cutoff'])]['load'] = $petty['loadTotal'];
                            $cashData['pettyExpenses'][$this->check_in_cutoff_key($petty['cutoff'])]['fare'] = $petty['fareTotal'];
                        }else{
                            $nonCashData['pettyExpenses'][$this->check_in_cutoff_key($petty['cutoff'])]['food'] = $petty['foodTotal'];
                            $nonCashData['pettyExpenses'][$this->check_in_cutoff_key($petty['cutoff'])]['supplies'] = $petty['suppliesTotal'];
                            $nonCashData['pettyExpenses'][$this->check_in_cutoff_key($petty['cutoff'])]['load'] = $petty['loadTotal'];
                            $nonCashData['pettyExpenses'][$this->check_in_cutoff_key($petty['cutoff'])]['fare'] = $petty['fareTotal'];
                        }
                    }
                }
            }

            if(!empty($photocopyExpenses))
            {
                foreach($photocopyExpenses as $photocopy)
                {
                    if($photocopy['cutoff'] != '')
                    {
                        if($photocopy['amount_type'] == 'Cash')
                        {
                            $cashData['photocopyExpenses'][$this->check_in_cutoff_key($photocopy['cutoff'])] = $photocopy['total'];
                        }else{
                            $nonCashData['photocopyExpenses'][$this->check_in_cutoff_key($photocopy['cutoff'])] = $photocopy['total'];
                        }
                    }
                }
            }

            if(!empty($otherExpenses))
            {
                foreach($otherExpenses as $other)
                {
                    if($other['cutoff'] != '')
                    {
                        if($other['amount_type'] == 'Cash')
                        {
                            $cashData['otherExpenses'][$this->check_in_cutoff_key($other['cutoff'])] = $other['total'];
                        }else{
                            $nonCashData['otherExpenses'][$this->check_in_cutoff_key($other['cutoff'])] = $other['total'];
                        }
                    }
                }
            }

            if(!empty($bankDeposits))
            {
                foreach($bankDeposits as $bank)
                {
                    if($bank['cutoff'] != '')
                    {
                        if($bank['amount_type'] == 'Cash')
                        {
                            $cashData['bankDeposits'][$this->check_in_cutoff_key($bank['cutoff'])] = $bank['total'];
                        }else{
                            $nonCashData['bankDeposits'][$this->check_in_cutoff_key($bank['cutoff'])] = $bank['total'];
                        }
                    }
                }
            }

            if(!empty($operatingExpenses))
            {
                foreach($operatingExpenses as $operating)
                {
                    if($operating['cutoff'] != '')
                    {
                        $data['operatingExpenses'][$this->check_in_cutoff_key($operating['cutoff'])]['staffSalary'] = $operating['staffSalaryTotal'];
                        $data['operatingExpenses'][$this->check_in_cutoff_key($operating['cutoff'])]['cashPf'] = $operating['cashPfTotal'];
                        $data['operatingExpenses'][$this->check_in_cutoff_key($operating['cutoff'])]['rent'] = $operating['rentTotal'];
                        $data['operatingExpenses'][$this->check_in_cutoff_key($operating['cutoff'])]['utilities'] = $operating['utilitiesTotal'];
                        $data['operatingExpenses'][$this->check_in_cutoff_key($operating['cutoff'])]['equipmentAndLabor'] = $operating['equipmentAndLaborTotal'];
                        $data['operatingExpenses'][$this->check_in_cutoff_key($operating['cutoff'])]['birAndDocs'] = $operating['birAndDocsTotal'];
                        $data['operatingExpenses'][$this->check_in_cutoff_key($operating['cutoff'])]['marketing'] = $operating['marketingTotal'];
                    }

                    if($operating['cutoff'] != '')
                    {
                        if($operating['amount_type'] == 'Cash')
                        {
                            $cashData['operatingExpenses'][$this->check_in_cutoff_key($operating['cutoff'])]['staffSalary'] = $operating['staffSalaryTotal'];
                            $cashData['operatingExpenses'][$this->check_in_cutoff_key($operating['cutoff'])]['cashPf'] = $operating['cashPfTotal'];
                            $cashData['operatingExpenses'][$this->check_in_cutoff_key($operating['cutoff'])]['rent'] = $operating['rentTotal'];
                            $cashData['operatingExpenses'][$this->check_in_cutoff_key($operating['cutoff'])]['utilities'] = $operating['utilitiesTotal'];
                            $cashData['operatingExpenses'][$this->check_in_cutoff_key($operating['cutoff'])]['equipmentAndLabor'] = $operating['equipmentAndLaborTotal'];
                            $cashData['operatingExpenses'][$this->check_in_cutoff_key($operating['cutoff'])]['birAndDocs'] = $operating['birAndDocsTotal'];
                            $cashData['operatingExpenses'][$this->check_in_cutoff_key($operating['cutoff'])]['marketing'] = $operating['marketingTotal'];
                        }else{
                            $nonCashData['operatingExpenses'][$this->check_in_cutoff_key($operating['cutoff'])]['staffSalary'] = $operating['staffSalaryTotal'];
                            $nonCashData['operatingExpenses'][$this->check_in_cutoff_key($operating['cutoff'])]['cashPf'] = $operating['cashPfTotal'];
                            $nonCashData['operatingExpenses'][$this->check_in_cutoff_key($operating['cutoff'])]['rent'] = $operating['rentTotal'];
                            $nonCashData['operatingExpenses'][$this->check_in_cutoff_key($operating['cutoff'])]['utilities'] = $operating['utilitiesTotal'];
                            $nonCashData['operatingExpenses'][$this->check_in_cutoff_key($operating['cutoff'])]['equipmentAndLabor'] = $operating['equipmentAndLaborTotal'];
                            $nonCashData['operatingExpenses'][$this->check_in_cutoff_key($operating['cutoff'])]['birAndDocs'] = $operating['birAndDocsTotal'];
                            $nonCashData['operatingExpenses'][$this->check_in_cutoff_key($operating['cutoff'])]['marketing'] = $operating['marketingTotal'];
                        }
                    }
                }
            }

            $content = $this->renderPartial('_report_monthly_summary', [
                'dates' => $dates,
                'selectedSeason' => $selectedSeason,
                'selectedBranchProgram' => $selectedBranchProgram,
                'cutoffs' => $cutoffs,
                'cashData' => $cashData,
                'nonCashData' => $nonCashData,
                'beginningCash' => $beginningCash,
            ]);

            $title = 'Monthly Summary: '.$selectedBranchProgram['name'].' - SEASON '.$selectedSeason->name.' - ('.$cutoff['start'].' - '.$cutoff['end'].')';

            $pdf = new Pdf([
            'mode' => Pdf::MODE_CORE,
            'format' => Pdf::FORMAT_LEGAL, 
            'orientation' => Pdf::ORIENT_LANDSCAPE, 
            'destination' => Pdf::DEST_DOWNLOAD, 
            'filename' => $title.'.pdf',
            'content' => $content,  
            'marginLeft' => 11.4,
            'marginRight' => 11.4,
            'cssInline' => 'th { text-align:center; 
                                      padding:6px 6px 6px 6px;
                                      font-size:12px;
                                      }
                            .title {
                                      font-size:16px;
                                      font-weight:bold;
                                      }
                            td { padding:8px 4px 4px 4px; font-size:12px;
                          }', 
            'options' => ['title' => $title],
            'methods' => [ 
                'SetHeader'=>[$title], 
                'SetFooter'=>['Page {PAGENO}'],
            ]
            ]);
            $response = Yii::$app->response;
            $response->format = \yii\web\Response::FORMAT_RAW;
            $headers = Yii::$app->response->headers;
            $headers->add('Content-Type', 'application/pdf');
            return $pdf->render();   
        }
    }
}
