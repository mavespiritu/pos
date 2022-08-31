<?php

namespace common\modules\accounting\controllers;

use Yii;
use common\modules\accounting\models\AccessProgram;
use common\modules\accounting\models\BranchTransfer;
use common\modules\accounting\models\BranchProgram;
use common\modules\accounting\models\BranchTransferSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use kartik\mpdf\Pdf;
use yii\helpers\ArrayHelper;
/**
 * BranchTransferController implements the CRUD actions for BranchTransfer model.
 */
class BranchTransferController extends Controller
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
                'only' => ['index', 'view'],
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['manageBranchTransfer'],
                    ],
                    [
                        'actions' => ['view'],
                        'allow' => true,
                        'roles' => ['viewBranchTransfer'],
                    ],
                ],
            ],
        ];
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
                    if($no_of_days_in_a_month > 30)
                    {
                        $dates['start'] = ($year-1).'-12-26';
                        $dates['end'] = $year.'-'.$month.'-10';
                    }else{
                        $dates['start'] = ($year-1).'-12-26';
                        $dates['end'] = $year.'-'.$month.'-10';
                    }
                    
                }else if(($nth_month > 1) && ($nth_month < 12)){
                    if($no_of_days_in_a_month > 30)
                    {
                        
                        $dates['start'] = $year.'-'.($last_month).'-26';
                        $dates['end'] = $year.'-'.$month.'-10';
                    }else{
                        $dates['start'] = $year.'-'.$last_month.'-26';
                        $dates['end'] = $year.'-'.$month.'-10';
                    }
                }else if($nth_month == 12){
                    if($no_of_days_in_a_month > 30)
                    {
                        $dates['start'] = $year.'-'.$last_month.'-26';
                        $dates['end'] = $year.'-'.$month.'-10';
                    }else{
                        $dates['start'] = $year.'-'.$last_month.'-26';
                        $dates['end'] = $year.'-'.$month.'-10';
                    }
                }
            }else if(strtotime($date) > strtotime($cutoff_check_start_date))
            {
                $next_month = date("m", strtotime('first day of +1 month', strtotime($date)));
                $no_of_days_in_a_month = cal_days_in_month(CAL_GREGORIAN,$next_month,$year);
                if(($nth_month > 1) && ($nth_month < 12)){
                    if($no_of_days_in_a_month > 30)
                    {
                        $dates['start'] = $year.'-'.$month.'-26';
                        $dates['end'] = $year.'-'.$next_month.'-10';
                    }else{
                        $dates['start'] = $year.'-'.$month.'-26';
                        $dates['end'] = $year.'-'.$next_month.'-10';
                    }
                }else if($nth_month == 12){
                    if($no_of_days_in_a_month > 30)
                    {
                        $dates['start'] = $year.'-'.$month.'-26';
                        $dates['end'] = ($year+1).'-01-10';
                    }else{
                        $dates['start'] = $year.'-'.$month.'-26';
                        $dates['end'] = ($year+1).'-01-10';
                    }
                }else if($nth_month == 1){
                    if($no_of_days_in_a_month > 30)
                    {
                        $dates['start'] = $year.'-'.$month.'-26';
                        $dates['end'] = ($year).'-02-10';
                    }else{
                        $dates['start'] = $year.'-'.$month.'-26';
                        $dates['end'] = ($year).'-02-10';
                    }
                }
            }
        } 

        return $dates;
    }

    function takeFrequency($date, $frequency)
    {
        $out = [];
        if($frequency == 'Yearly')
        {
            $out['year'] = date("Y", strtotime($date));
        }else if($frequency == 'Monthly')
        {
            $out['year'] = date("Y", strtotime($date));
            $out['month'] = date("m", strtotime($date));
        }else if($frequency == 'Daily')
        {
            $out['year'] = date("Y", strtotime($date));
            $out['month'] = date("m", strtotime($date));
            $out['day'] = date("d", strtotime($date));
        }
        else if($frequency == 'Cut Off')
        {
            $data = $this->check_in_cutoff($date);
            $out['start'] = $data['start'];
            $out['end'] = $data['end'];
        }

        return $out;
    }

    public function actionSearch()
    {
        $model = new BranchTransfer();
        $model->scenario = 'searchBranchTransfer';
        $user_info = Yii::$app->user->identity->userinfo;
        $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
        $rolenames =  ArrayHelper::map($roles, 'name','name');

        $access = AccessProgram::findOne(['user_id' => Yii::$app->user->identity->userinfo->user_id]);

        $frequencies = [
            'Yearly' => 'Yearly', 
            'Cut Off' => 'Cut Off', 
            'Monthly' => 'Monthly', 
            'Daily' => 'Daily'
        ];

        if(in_array('TopManagement',$rolenames)){
            $branchPrograms = $access ? $access->branch_program_id != '' ? BranchProgram::find()
                    ->select([
                        'accounting_branch_program.id as id',
                        'concat(accounting_branch.name," - ",accounting_program.name) as name',
                    ])
                    ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                    ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                    ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                    ->orderBy(['name' => SORT_ASC])
                    ->asArray()
                    ->all() : BranchProgram::find()
                    ->select([
                        'accounting_branch_program.id as id',
                        'concat(accounting_branch.name," - ",accounting_program.name) as name',
                    ])
                    ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                    ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                    ->orderBy(['name' => SORT_ASC])
                    ->asArray()
                    ->all() : BranchProgram::find()
                    ->select([
                        'accounting_branch_program.id as id',
                        'concat(accounting_branch.name," - ",accounting_program.name) as name',
                    ])
                    ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                    ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                    ->orderBy(['name' => SORT_ASC])
                    ->asArray()
                    ->all();
        }else{
            $branchPrograms = $access ? $access->branch_program_id != '' ? BranchProgram::find()
                    ->select([
                        'accounting_branch_program.id as id',
                        'concat(accounting_branch.name," - ",accounting_program.name) as name',
                    ])
                    ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                    ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                    ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                    ->where(['accounting_branch.id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                    ->orderBy(['name' => SORT_ASC])
                    ->asArray()
                    ->all() : BranchProgram::find()
                    ->select([
                        'accounting_branch_program.id as id',
                        'concat(accounting_branch.name," - ",accounting_program.name) as name',
                    ])
                    ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                    ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                    ->where(['accounting_branch.id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                    ->orderBy(['name' => SORT_ASC])
                    ->asArray()
                    ->all() : BranchProgram::find()
                    ->select([
                        'accounting_branch_program.id as id',
                        'concat(accounting_branch.name," - ",accounting_program.name) as name',
                    ])
                    ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                    ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                    ->where(['accounting_branch.id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                    ->orderBy(['name' => SORT_ASC])
                    ->asArray()
                    ->all();
        }

        $branchPrograms = ArrayHelper::map($branchPrograms, 'id', 'name');

        $pages = [];

        if(Yii::$app->request->post())
        {
            $postData = Yii::$app->request->post()['BranchTransfer'];

            $branchProgram = BranchProgram::findOne($postData['branch_programs_id']);

            $limit = 1000;

            if($branchProgram)
            {
                $dates = [];

                if($postData['frequency_id']!="" && $postData['date_id']!="")
                {
                    $dates = $this->takeFrequency($postData['date_id'], $postData['frequency_id']);

                    if($postData['frequency_id'] == 'Yearly')
                    {
                        if(in_array('TopManagement',$rolenames)){
                            $data = $access ? $access->branch_program_id!= '' ? BranchTransfer::find()
                                ->select([
                                    'IF(accounting_branch.id, accounting_branch.name, concat(accounting_branch.name," - ",accounting_program.name)) as charged_to',
                                    'sum(COALESCE(particulars.total, 0)) as amount',
                                    'accounting_expense_branch_transfer.amount_source',
                                    'accounting_expense.datetime',
                                ])
                                ->leftJoin(['particulars' => '(
                                    (
                                        SELECT
                                            accounting_budget_proposal_particular.budget_proposal_id,
                                            sum(accounting_budget_proposal_particular.amount) as total
                                        from accounting_budget_proposal_particular
                                        LEFT JOIN accounting_income_budget_proposal on accounting_income_budget_proposal.id = accounting_budget_proposal_particular.budget_proposal_id
                                        WHERE accounting_budget_proposal_particular.approval_status = "Approved"
                                    )
                                )'], 'particulars.budget_proposal_id = accounting_expense_branch_transfer.budget_proposal_id')
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_expense_branch_transfer.branch_id')
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_expense_branch_transfer.branch_program_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->andWhere(['like','accounting_expense.datetime', $dates['year']])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->limit($limit)
                                ->offset($postData['page_id'])
                                ->asArray()
                                ->all() : BranchTransfer::find()
                                ->select([
                                    'IF(accounting_branch.id, accounting_branch.name, concat(accounting_branch.name," - ",accounting_program.name)) as charged_to',
                                    'sum(COALESCE(particulars.total, 0)) as amount',
                                    'accounting_expense_branch_transfer.amount_source',
                                    'accounting_expense.datetime',
                                ])
                                ->leftJoin(['particulars' => '(
                                    (
                                        SELECT
                                            accounting_budget_proposal_particular.budget_proposal_id,
                                            sum(accounting_budget_proposal_particular.amount) as total
                                        from accounting_budget_proposal_particular
                                        LEFT JOIN accounting_income_budget_proposal on accounting_income_budget_proposal.id = accounting_budget_proposal_particular.budget_proposal_id
                                        WHERE accounting_budget_proposal_particular.approval_status = "Approved"
                                    )
                                )'], 'particulars.budget_proposal_id = accounting_expense_branch_transfer.budget_proposal_id')
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_expense_branch_transfer.branch_id')
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_expense_branch_transfer.branch_program_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['like','accounting_expense.datetime', $dates['year']])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->limit($limit)
                                ->offset($postData['page_id'])
                                ->asArray()
                                ->all() : BranchTransfer::find()
                                ->select([
                                    'IF(accounting_branch.id, accounting_branch.name, concat(accounting_branch.name," - ",accounting_program.name)) as charged_to',
                                    'sum(COALESCE(particulars.total, 0)) as amount',
                                    'accounting_expense_branch_transfer.amount_source',
                                    'accounting_expense.datetime',
                                ])
                                ->leftJoin(['particulars' => '(
                                    (
                                        SELECT
                                            accounting_budget_proposal_particular.budget_proposal_id,
                                            sum(accounting_budget_proposal_particular.amount) as total
                                        from accounting_budget_proposal_particular
                                        LEFT JOIN accounting_income_budget_proposal on accounting_income_budget_proposal.id = accounting_budget_proposal_particular.budget_proposal_id
                                        WHERE accounting_budget_proposal_particular.approval_status = "Approved"
                                    )
                                )'], 'particulars.budget_proposal_id = accounting_expense_branch_transfer.budget_proposal_id')
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_expense_branch_transfer.branch_id')
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_expense_branch_transfer.branch_program_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['like','accounting_expense.datetime', $dates['year']])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->limit($limit)
                                ->offset($postData['page_id'])
                                ->asArray()
                                ->all();
                        }else{
                            $data = $access ? $access->branch_program_id!= '' ? BranchTransfer::find()
                                ->select([
                                    'IF(accounting_branch.id, accounting_branch.name, concat(accounting_branch.name," - ",accounting_program.name)) as charged_to',
                                    'sum(COALESCE(particulars.total, 0)) as amount',
                                    'accounting_expense_branch_transfer.amount_source',
                                    'accounting_expense.datetime',
                                ])
                                ->leftJoin(['particulars' => '(
                                    (
                                        SELECT
                                            accounting_budget_proposal_particular.budget_proposal_id,
                                            sum(accounting_budget_proposal_particular.amount) as total
                                        from accounting_budget_proposal_particular
                                        LEFT JOIN accounting_income_budget_proposal on accounting_income_budget_proposal.id = accounting_budget_proposal_particular.budget_proposal_id
                                        WHERE accounting_budget_proposal_particular.approval_status = "Approved"
                                    )
                                )'], 'particulars.budget_proposal_id = accounting_expense_branch_transfer.budget_proposal_id')
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_expense_branch_transfer.branch_id')
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_expense_branch_transfer.branch_program_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->orWhere(['accounting_branch.id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->orWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->andWhere(['like','accounting_expense.datetime', $dates['year']])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->limit($limit)
                                ->offset($postData['page_id'])
                                ->asArray()
                                ->all() : BranchTransfer::find()
                                ->select([
                                    'IF(accounting_branch.id, accounting_branch.name, concat(accounting_branch.name," - ",accounting_program.name)) as charged_to',
                                    'sum(COALESCE(particulars.total, 0)) as amount',
                                    'accounting_expense_branch_transfer.amount_source',
                                    'accounting_expense.datetime',
                                ])
                                ->leftJoin(['particulars' => '(
                                    (
                                        SELECT
                                            accounting_budget_proposal_particular.budget_proposal_id,
                                            sum(accounting_budget_proposal_particular.amount) as total
                                        from accounting_budget_proposal_particular
                                        LEFT JOIN accounting_income_budget_proposal on accounting_income_budget_proposal.id = accounting_budget_proposal_particular.budget_proposal_id
                                        WHERE accounting_budget_proposal_particular.approval_status = "Approved"
                                    )
                                )'], 'particulars.budget_proposal_id = accounting_expense_branch_transfer.budget_proposal_id')
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_expense_branch_transfer.branch_id')
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_expense_branch_transfer.branch_program_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->orWhere(['accounting_branch.id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->orWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->andWhere(['like','accounting_expense.datetime', $dates['year']])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->limit($limit)
                                ->offset($postData['page_id'])
                                ->asArray()
                                ->all() : BranchTransfer::find()
                                ->select([
                                    'IF(accounting_branch.id, accounting_branch.name, concat(accounting_branch.name," - ",accounting_program.name)) as charged_to',
                                    'sum(COALESCE(particulars.total, 0)) as amount',
                                    'accounting_expense_branch_transfer.amount_source',
                                    'accounting_expense.datetime',
                                ])
                                ->leftJoin(['particulars' => '(
                                    (
                                        SELECT
                                            accounting_budget_proposal_particular.budget_proposal_id,
                                            sum(accounting_budget_proposal_particular.amount) as total
                                        from accounting_budget_proposal_particular
                                        LEFT JOIN accounting_income_budget_proposal on accounting_income_budget_proposal.id = accounting_budget_proposal_particular.budget_proposal_id
                                        WHERE accounting_budget_proposal_particular.approval_status = "Approved"
                                    )
                                )'], 'particulars.budget_proposal_id = accounting_expense_branch_transfer.budget_proposal_id')
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_expense_branch_transfer.branch_id')
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_expense_branch_transfer.branch_program_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->orWhere(['accounting_branch.id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->orWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->andWhere(['like','accounting_expense.datetime', $dates['year']])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->limit($limit)
                                ->offset($postData['page_id'])
                                ->asArray()
                                ->all();
                        }
                    }else if($postData['frequency_id'] == 'Monthly')
                    {
                        if(in_array('TopManagement',$rolenames)){
                            $data = $access ? $access->branch_program_id!= '' ? BranchTransfer::find()
                                ->select([
                                    'IF(accounting_branch.id, accounting_branch.name, concat(accounting_branch.name," - ",accounting_program.name)) as charged_to',
                                    'sum(COALESCE(particulars.total, 0)) as amount',
                                    'accounting_expense_branch_transfer.amount_source',
                                    'accounting_expense.datetime',
                                ])
                                ->leftJoin(['particulars' => '(
                                    (
                                        SELECT
                                            accounting_budget_proposal_particular.budget_proposal_id,
                                            sum(accounting_budget_proposal_particular.amount) as total
                                        from accounting_budget_proposal_particular
                                        LEFT JOIN accounting_income_budget_proposal on accounting_income_budget_proposal.id = accounting_budget_proposal_particular.budget_proposal_id
                                        WHERE accounting_budget_proposal_particular.approval_status = "Approved"
                                    )
                                )'], 'particulars.budget_proposal_id = accounting_expense_branch_transfer.budget_proposal_id')
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_expense_branch_transfer.branch_id')
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_expense_branch_transfer.branch_program_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->andWhere(['like', 'accounting_expense.datetime', $dates['year'].'-'.$dates['month']])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->limit($limit)
                                ->offset($postData['page_id'])
                                ->asArray()
                                ->all() : BranchTransfer::find()
                                ->select([
                                    'IF(accounting_branch.id, accounting_branch.name, concat(accounting_branch.name," - ",accounting_program.name)) as charged_to',
                                    'sum(COALESCE(particulars.total, 0)) as amount',
                                    'accounting_expense_branch_transfer.amount_source',
                                    'accounting_expense.datetime',
                                ])
                                ->leftJoin(['particulars' => '(
                                    (
                                        SELECT
                                            accounting_budget_proposal_particular.budget_proposal_id,
                                            sum(accounting_budget_proposal_particular.amount) as total
                                        from accounting_budget_proposal_particular
                                        LEFT JOIN accounting_income_budget_proposal on accounting_income_budget_proposal.id = accounting_budget_proposal_particular.budget_proposal_id
                                        WHERE accounting_budget_proposal_particular.approval_status = "Approved"
                                    )
                                )'], 'particulars.budget_proposal_id = accounting_expense_branch_transfer.budget_proposal_id')
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_expense_branch_transfer.branch_id')
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_expense_branch_transfer.branch_program_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['like', 'accounting_expense.datetime', $dates['year'].'-'.$dates['month']])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->limit($limit)
                                ->offset($postData['page_id'])
                                ->asArray()
                                ->all() : BranchTransfer::find()
                                ->select([
                                    'IF(accounting_branch.id, accounting_branch.name, concat(accounting_branch.name," - ",accounting_program.name)) as charged_to',
                                    'sum(COALESCE(particulars.total, 0)) as amount',
                                    'accounting_expense_branch_transfer.amount_source',
                                    'accounting_expense.datetime',
                                ])
                                ->leftJoin(['particulars' => '(
                                    (
                                        SELECT
                                            accounting_budget_proposal_particular.budget_proposal_id,
                                            sum(accounting_budget_proposal_particular.amount) as total
                                        from accounting_budget_proposal_particular
                                        LEFT JOIN accounting_income_budget_proposal on accounting_income_budget_proposal.id = accounting_budget_proposal_particular.budget_proposal_id
                                        WHERE accounting_budget_proposal_particular.approval_status = "Approved"
                                    )
                                )'], 'particulars.budget_proposal_id = accounting_expense_branch_transfer.budget_proposal_id')
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_expense_branch_transfer.branch_id')
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_expense_branch_transfer.branch_program_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['like', 'accounting_expense.datetime', $dates['year'].'-'.$dates['month']])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->limit($limit)
                                ->offset($postData['page_id'])
                                ->asArray()
                                ->all();
                        }else{
                            $data = $access ? $access->branch_program_id!= '' ? BranchTransfer::find()
                                ->select([
                                    'IF(accounting_branch.id, accounting_branch.name, concat(accounting_branch.name," - ",accounting_program.name)) as charged_to',
                                    'sum(COALESCE(particulars.total, 0)) as amount',
                                    'accounting_expense_branch_transfer.amount_source',
                                    'accounting_expense.datetime',
                                ])
                                ->leftJoin(['particulars' => '(
                                    (
                                        SELECT
                                            accounting_budget_proposal_particular.budget_proposal_id,
                                            sum(accounting_budget_proposal_particular.amount) as total
                                        from accounting_budget_proposal_particular
                                        LEFT JOIN accounting_income_budget_proposal on accounting_income_budget_proposal.id = accounting_budget_proposal_particular.budget_proposal_id
                                        WHERE accounting_budget_proposal_particular.approval_status = "Approved"
                                    )
                                )'], 'particulars.budget_proposal_id = accounting_expense_branch_transfer.budget_proposal_id')
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_expense_branch_transfer.branch_id')
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_expense_branch_transfer.branch_program_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->orWhere(['accounting_branch.id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->orWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->andWhere(['like', 'accounting_expense.datetime', $dates['year'].'-'.$dates['month']])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->limit($limit)
                                ->offset($postData['page_id'])
                                ->asArray()
                                ->all() : BranchTransfer::find()
                                ->select([
                                    'IF(accounting_branch.id, accounting_branch.name, concat(accounting_branch.name," - ",accounting_program.name)) as charged_to',
                                    'sum(COALESCE(particulars.total, 0)) as amount',
                                    'accounting_expense_branch_transfer.amount_source',
                                    'accounting_expense.datetime',
                                ])
                                ->leftJoin(['particulars' => '(
                                    (
                                        SELECT
                                            accounting_budget_proposal_particular.budget_proposal_id,
                                            sum(accounting_budget_proposal_particular.amount) as total
                                        from accounting_budget_proposal_particular
                                        LEFT JOIN accounting_income_budget_proposal on accounting_income_budget_proposal.id = accounting_budget_proposal_particular.budget_proposal_id
                                        WHERE accounting_budget_proposal_particular.approval_status = "Approved"
                                    )
                                )'], 'particulars.budget_proposal_id = accounting_expense_branch_transfer.budget_proposal_id')
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_expense_branch_transfer.branch_id')
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_expense_branch_transfer.branch_program_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->orWhere(['accounting_branch.id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->orWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->andWhere(['like', 'accounting_expense.datetime', $dates['year'].'-'.$dates['month']])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->limit($limit)
                                ->offset($postData['page_id'])
                                ->asArray()
                                ->all() : BranchTransfer::find()
                                ->select([
                                    'IF(accounting_branch.id, accounting_branch.name, concat(accounting_branch.name," - ",accounting_program.name)) as charged_to',
                                    'sum(COALESCE(particulars.total, 0)) as amount',
                                    'accounting_expense_branch_transfer.amount_source',
                                    'accounting_expense.datetime',
                                ])
                                ->leftJoin(['particulars' => '(
                                    (
                                        SELECT
                                            accounting_budget_proposal_particular.budget_proposal_id,
                                            sum(accounting_budget_proposal_particular.amount) as total
                                        from accounting_budget_proposal_particular
                                        LEFT JOIN accounting_income_budget_proposal on accounting_income_budget_proposal.id = accounting_budget_proposal_particular.budget_proposal_id
                                        WHERE accounting_budget_proposal_particular.approval_status = "Approved"
                                    )
                                )'], 'particulars.budget_proposal_id = accounting_expense_branch_transfer.budget_proposal_id')
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_expense_branch_transfer.branch_id')
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_expense_branch_transfer.branch_program_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->orWhere(['accounting_branch.id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->orWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->andWhere(['like', 'accounting_expense.datetime', $dates['year'].'-'.$dates['month']])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->limit($limit)
                                ->offset($postData['page_id'])
                                ->asArray()
                                ->all();
                        }
                    }else if($postData['frequency_id'] == 'Cut Off')
                    {
                        if(in_array('TopManagement',$rolenames)){
                            $data = $access ? $access->branch_program_id!= '' ? BranchTransfer::find()
                                ->select([
                                    'IF(accounting_branch.id, accounting_branch.name, concat(accounting_branch.name," - ",accounting_program.name)) as charged_to',
                                    'sum(COALESCE(particulars.total, 0)) as amount',
                                    'accounting_expense_branch_transfer.amount_source',
                                    'accounting_expense.datetime',
                                ])
                                ->leftJoin(['particulars' => '(
                                    (
                                        SELECT
                                            accounting_budget_proposal_particular.budget_proposal_id,
                                            sum(accounting_budget_proposal_particular.amount) as total
                                        from accounting_budget_proposal_particular
                                        LEFT JOIN accounting_income_budget_proposal on accounting_income_budget_proposal.id = accounting_budget_proposal_particular.budget_proposal_id
                                        WHERE accounting_budget_proposal_particular.approval_status = "Approved"
                                    )
                                )'], 'particulars.budget_proposal_id = accounting_expense_branch_transfer.budget_proposal_id')
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_expense_branch_transfer.branch_id')
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_expense_branch_transfer.branch_program_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->andWhere(['between', 'accounting_expense.datetime', $dates['start'].' 00:00:00', $dates['end'].' 23:59:59'])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->limit($limit)
                                ->offset($postData['page_id'])
                                ->asArray()
                                ->all() : BranchTransfer::find()
                                ->select([
                                    'IF(accounting_branch.id, accounting_branch.name, concat(accounting_branch.name," - ",accounting_program.name)) as charged_to',
                                    'sum(COALESCE(particulars.total, 0)) as amount',
                                    'accounting_expense_branch_transfer.amount_source',
                                    'accounting_expense.datetime',
                                ])
                                ->leftJoin(['particulars' => '(
                                    (
                                        SELECT
                                            accounting_budget_proposal_particular.budget_proposal_id,
                                            sum(accounting_budget_proposal_particular.amount) as total
                                        from accounting_budget_proposal_particular
                                        LEFT JOIN accounting_income_budget_proposal on accounting_income_budget_proposal.id = accounting_budget_proposal_particular.budget_proposal_id
                                        WHERE accounting_budget_proposal_particular.approval_status = "Approved"
                                    )
                                )'], 'particulars.budget_proposal_id = accounting_expense_branch_transfer.budget_proposal_id')
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_expense_branch_transfer.branch_id')
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_expense_branch_transfer.branch_program_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['between', 'accounting_expense.datetime', $dates['start'].' 00:00:00', $dates['end'].' 23:59:59'])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->limit($limit)
                                ->offset($postData['page_id'])
                                ->asArray()
                                ->all() : BranchTransfer::find()
                                ->select([
                                    'IF(accounting_branch.id, accounting_branch.name, concat(accounting_branch.name," - ",accounting_program.name)) as charged_to',
                                    'sum(COALESCE(particulars.total, 0)) as amount',
                                    'accounting_expense_branch_transfer.amount_source',
                                    'accounting_expense.datetime',
                                ])
                                ->leftJoin(['particulars' => '(
                                    (
                                        SELECT
                                            accounting_budget_proposal_particular.budget_proposal_id,
                                            sum(accounting_budget_proposal_particular.amount) as total
                                        from accounting_budget_proposal_particular
                                        LEFT JOIN accounting_income_budget_proposal on accounting_income_budget_proposal.id = accounting_budget_proposal_particular.budget_proposal_id
                                        WHERE accounting_budget_proposal_particular.approval_status = "Approved"
                                    )
                                )'], 'particulars.budget_proposal_id = accounting_expense_branch_transfer.budget_proposal_id')
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_expense_branch_transfer.branch_id')
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_expense_branch_transfer.branch_program_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['between', 'accounting_expense.datetime', $dates['start'].' 00:00:00', $dates['end'].' 23:59:59'])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->limit($limit)
                                ->offset($postData['page_id'])
                                ->asArray()
                                ->all();
                        }else{
                            $data = $access ? $access->branch_program_id!= '' ? BranchTransfer::find()
                                ->select([
                                    'IF(accounting_branch.id, accounting_branch.name, concat(accounting_branch.name," - ",accounting_program.name)) as charged_to',
                                    'sum(COALESCE(particulars.total, 0)) as amount',
                                    'accounting_expense_branch_transfer.amount_source',
                                    'accounting_expense.datetime',
                                ])
                                ->leftJoin(['particulars' => '(
                                    (
                                        SELECT
                                            accounting_budget_proposal_particular.budget_proposal_id,
                                            sum(accounting_budget_proposal_particular.amount) as total
                                        from accounting_budget_proposal_particular
                                        LEFT JOIN accounting_income_budget_proposal on accounting_income_budget_proposal.id = accounting_budget_proposal_particular.budget_proposal_id
                                        WHERE accounting_budget_proposal_particular.approval_status = "Approved"
                                    )
                                )'], 'particulars.budget_proposal_id = accounting_expense_branch_transfer.budget_proposal_id')
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_expense_branch_transfer.branch_id')
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_expense_branch_transfer.branch_program_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->orWhere(['accounting_branch.id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->orWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->andWhere(['between', 'accounting_expense.datetime', $dates['start'].' 00:00:00', $dates['end'].' 23:59:59'])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->limit($limit)
                                ->offset($postData['page_id'])
                                ->asArray()
                                ->all() : BranchTransfer::find()
                                ->select([
                                    'IF(accounting_branch.id, accounting_branch.name, concat(accounting_branch.name," - ",accounting_program.name)) as charged_to',
                                    'sum(COALESCE(particulars.total, 0)) as amount',
                                    'accounting_expense_branch_transfer.amount_source',
                                    'accounting_expense.datetime',
                                ])
                                ->leftJoin(['particulars' => '(
                                    (
                                        SELECT
                                            accounting_budget_proposal_particular.budget_proposal_id,
                                            sum(accounting_budget_proposal_particular.amount) as total
                                        from accounting_budget_proposal_particular
                                        LEFT JOIN accounting_income_budget_proposal on accounting_income_budget_proposal.id = accounting_budget_proposal_particular.budget_proposal_id
                                        WHERE accounting_budget_proposal_particular.approval_status = "Approved"
                                    )
                                )'], 'particulars.budget_proposal_id = accounting_expense_branch_transfer.budget_proposal_id')
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_expense_branch_transfer.branch_id')
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_expense_branch_transfer.branch_program_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->orWhere(['accounting_branch.id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->orWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->andWhere(['between', 'accounting_expense.datetime', $dates['start'].' 00:00:00', $dates['end'].' 23:59:59'])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->limit($limit)
                                ->offset($postData['page_id'])
                                ->asArray()
                                ->all() : BranchTransfer::find()
                                ->select([
                                    'IF(accounting_branch.id, accounting_branch.name, concat(accounting_branch.name," - ",accounting_program.name)) as charged_to',
                                    'sum(COALESCE(particulars.total, 0)) as amount',
                                    'accounting_expense_branch_transfer.amount_source',
                                    'accounting_expense.datetime',
                                ])
                                ->leftJoin(['particulars' => '(
                                    (
                                        SELECT
                                            accounting_budget_proposal_particular.budget_proposal_id,
                                            sum(accounting_budget_proposal_particular.amount) as total
                                        from accounting_budget_proposal_particular
                                        LEFT JOIN accounting_income_budget_proposal on accounting_income_budget_proposal.id = accounting_budget_proposal_particular.budget_proposal_id
                                        WHERE accounting_budget_proposal_particular.approval_status = "Approved"
                                    )
                                )'], 'particulars.budget_proposal_id = accounting_expense_branch_transfer.budget_proposal_id')
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_expense_branch_transfer.branch_id')
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_expense_branch_transfer.branch_program_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->orWhere(['accounting_branch.id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->orWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->andWhere(['between', 'accounting_expense.datetime', $dates['start'].' 00:00:00', $dates['end'].' 23:59:59'])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->limit($limit)
                                ->offset($postData['page_id'])
                                ->asArray()
                                ->all();
                        }
                    }else if($postData['frequency_id'] == 'Daily')
                    {
                        if(in_array('TopManagement',$rolenames)){
                            $data = $access ? $access->branch_program_id!= '' ? BranchTransfer::find()
                                ->select([
                                    'IF(accounting_branch.id, accounting_branch.name, concat(accounting_branch.name," - ",accounting_program.name)) as charged_to',
                                    'sum(COALESCE(particulars.total, 0)) as amount',
                                    'accounting_expense_branch_transfer.amount_source',
                                    'accounting_expense.datetime',
                                ])
                                ->leftJoin(['particulars' => '(
                                    (
                                        SELECT
                                            accounting_budget_proposal_particular.budget_proposal_id,
                                            sum(accounting_budget_proposal_particular.amount) as total
                                        from accounting_budget_proposal_particular
                                        LEFT JOIN accounting_income_budget_proposal on accounting_income_budget_proposal.id = accounting_budget_proposal_particular.budget_proposal_id
                                        WHERE accounting_budget_proposal_particular.approval_status = "Approved"
                                    )
                                )'], 'particulars.budget_proposal_id = accounting_expense_branch_transfer.budget_proposal_id')
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_expense_branch_transfer.branch_id')
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_expense_branch_transfer.branch_program_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->andWhere(['like','accounting_expense.datetime', $dates['year'].'-'.$dates['month'].'-'.$dates['day']])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->limit($limit)
                                ->offset($postData['page_id'])
                                ->asArray()
                                ->all() : BranchTransfer::find()
                                ->select([
                                    'IF(accounting_branch.id, accounting_branch.name, concat(accounting_branch.name," - ",accounting_program.name)) as charged_to',
                                    'sum(COALESCE(particulars.total, 0)) as amount',
                                    'accounting_expense_branch_transfer.amount_source',
                                    'accounting_expense.datetime',
                                ])
                                ->leftJoin(['particulars' => '(
                                    (
                                        SELECT
                                            accounting_budget_proposal_particular.budget_proposal_id,
                                            sum(accounting_budget_proposal_particular.amount) as total
                                        from accounting_budget_proposal_particular
                                        LEFT JOIN accounting_income_budget_proposal on accounting_income_budget_proposal.id = accounting_budget_proposal_particular.budget_proposal_id
                                        WHERE accounting_budget_proposal_particular.approval_status = "Approved"
                                    )
                                )'], 'particulars.budget_proposal_id = accounting_expense_branch_transfer.budget_proposal_id')
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_expense_branch_transfer.branch_id')
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_expense_branch_transfer.branch_program_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['like','accounting_expense.datetime', $dates['year'].'-'.$dates['month'].'-'.$dates['day']])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->limit($limit)
                                ->offset($postData['page_id'])
                                ->asArray()
                                ->all() : BranchTransfer::find()
                                ->select([
                                    'IF(accounting_branch.id, accounting_branch.name, concat(accounting_branch.name," - ",accounting_program.name)) as charged_to',
                                    'sum(COALESCE(particulars.total, 0)) as amount',
                                    'accounting_expense_branch_transfer.amount_source',
                                    'accounting_expense.datetime',
                                ])
                                ->leftJoin(['particulars' => '(
                                    (
                                        SELECT
                                            accounting_budget_proposal_particular.budget_proposal_id,
                                            sum(accounting_budget_proposal_particular.amount) as total
                                        from accounting_budget_proposal_particular
                                        LEFT JOIN accounting_income_budget_proposal on accounting_income_budget_proposal.id = accounting_budget_proposal_particular.budget_proposal_id
                                        WHERE accounting_budget_proposal_particular.approval_status = "Approved"
                                    )
                                )'], 'particulars.budget_proposal_id = accounting_expense_branch_transfer.budget_proposal_id')
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_expense_branch_transfer.branch_id')
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_expense_branch_transfer.branch_program_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['like','accounting_expense.datetime', $dates['year'].'-'.$dates['month'].'-'.$dates['day']])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->limit($limit)
                                ->offset($postData['page_id'])
                                ->asArray()
                                ->all();
                        }else{
                            $data = $access ? $access->branch_program_id!= '' ? BranchTransfer::find()
                                ->select([
                                    'IF(accounting_branch.id, accounting_branch.name, concat(accounting_branch.name," - ",accounting_program.name)) as charged_to',
                                    'sum(COALESCE(particulars.total, 0)) as amount',
                                    'accounting_expense_branch_transfer.amount_source',
                                    'accounting_expense.datetime',
                                ])
                                ->leftJoin(['particulars' => '(
                                    (
                                        SELECT
                                            accounting_budget_proposal_particular.budget_proposal_id,
                                            sum(accounting_budget_proposal_particular.amount) as total
                                        from accounting_budget_proposal_particular
                                        LEFT JOIN accounting_income_budget_proposal on accounting_income_budget_proposal.id = accounting_budget_proposal_particular.budget_proposal_id
                                        WHERE accounting_budget_proposal_particular.approval_status = "Approved"
                                    )
                                )'], 'particulars.budget_proposal_id = accounting_expense_branch_transfer.budget_proposal_id')
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_expense_branch_transfer.branch_id')
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_expense_branch_transfer.branch_program_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->orWhere(['accounting_branch.id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->orWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->andWhere(['like','accounting_expense.datetime', $dates['year'].'-'.$dates['month'].'-'.$dates['day']])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->limit($limit)
                                ->offset($postData['page_id'])
                                ->asArray()
                                ->all() : BranchTransfer::find()
                                ->select([
                                    'IF(accounting_branch.id, accounting_branch.name, concat(accounting_branch.name," - ",accounting_program.name)) as charged_to',
                                    'sum(COALESCE(particulars.total, 0)) as amount',
                                    'accounting_expense_branch_transfer.amount_source',
                                    'accounting_expense.datetime',
                                ])
                                ->leftJoin(['particulars' => '(
                                    (
                                        SELECT
                                            accounting_budget_proposal_particular.budget_proposal_id,
                                            sum(accounting_budget_proposal_particular.amount) as total
                                        from accounting_budget_proposal_particular
                                        LEFT JOIN accounting_income_budget_proposal on accounting_income_budget_proposal.id = accounting_budget_proposal_particular.budget_proposal_id
                                        WHERE accounting_budget_proposal_particular.approval_status = "Approved"
                                    )
                                )'], 'particulars.budget_proposal_id = accounting_expense_branch_transfer.budget_proposal_id')
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_expense_branch_transfer.branch_id')
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_expense_branch_transfer.branch_program_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->orWhere(['accounting_branch.id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->orWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->andWhere(['like','accounting_expense.datetime', $dates['year'].'-'.$dates['month'].'-'.$dates['day']])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->limit($limit)
                                ->offset($postData['page_id'])
                                ->asArray()
                                ->all() : BranchTransfer::find()
                                ->select([
                                    'IF(accounting_branch.id, accounting_branch.name, concat(accounting_branch.name," - ",accounting_program.name)) as charged_to',
                                    'sum(COALESCE(particulars.total, 0)) as amount',
                                    'accounting_expense_branch_transfer.amount_source',
                                    'accounting_expense.datetime',
                                ])
                                ->leftJoin(['particulars' => '(
                                    (
                                        SELECT
                                            accounting_budget_proposal_particular.budget_proposal_id,
                                            sum(accounting_budget_proposal_particular.amount) as total
                                        from accounting_budget_proposal_particular
                                        LEFT JOIN accounting_income_budget_proposal on accounting_income_budget_proposal.id = accounting_budget_proposal_particular.budget_proposal_id
                                        WHERE accounting_budget_proposal_particular.approval_status = "Approved"
                                    )
                                )'], 'particulars.budget_proposal_id = accounting_expense_branch_transfer.budget_proposal_id')
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_expense_branch_transfer.branch_id')
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_expense_branch_transfer.branch_program_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->orWhere(['accounting_branch.id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->orWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->andWhere(['like','accounting_expense.datetime', $dates['year'].'-'.$dates['month'].'-'.$dates['day']])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->limit($limit)
                                ->offset($postData['page_id'])
                                ->asArray()
                                ->all();
                        }
                    }

                    $pages = ceil(count($data) / $limit);

                    $content = $this->renderPartial('_branch-transfer-report', [
                        'data' => $data,
                        'branchProgram' => $branchProgram,
                        'postData' => $postData,
                        'dates' => $dates,
                    ]);

                    $pdf = new Pdf([
                    'mode' => Pdf::MODE_CORE,
                    'format' => Pdf::FORMAT_LEGAL, 
                    'orientation' => Pdf::ORIENT_LANDSCAPE, 
                    'destination' => Pdf::DEST_DOWNLOAD, 
                    'filename' => $postData['frequency_id'].' Report: Branch Transfers - '.$season->seasonName.' - Page '.($postData['page_id']+1).' of '.$pages.'.pdf',
                    'content' => $content,  
                    'marginLeft' => 11.4,
                    'marginRight' => 11.4,
                    //'cssFile' => '@vendor/kartik-v/yii2-mpdf/assets/kv-mpdf-bootstrap.min.css',
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
                    'options' => ['title' => 'Branch Transfers'],
                    'methods' => [ 
                        'SetHeader'=>[$postData['frequency_id'].' Report: Branch Transfers - '.$season->seasonName.' - Page '.($postData['page_id']+1).' of '.$pages], 
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

        return $this->renderAjax('_search-branch-transfer',[
            'model' => $model,
            'branchPrograms' => $branchPrograms,
            'frequencies' => $frequencies,
            'pages' => $pages,
        ]);
    }

    public function actionPageList($branch_programs_id = '', $frequency_id = '', $date_id = '') {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $user_info = Yii::$app->user->identity->userinfo;
        $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
        $rolenames =  ArrayHelper::map($roles, 'name','name');

        $branchProgram = BranchProgram::findOne($branch_programs_id);
        $reportPages = [];

        $access = AccessProgram::findOne(['user_id' => Yii::$app->user->identity->userinfo->user_id]);
        $count = 0;

        if($branchProgram)
        {
            $dates = [];

            if($frequency_id != '' && $date_id != '')
            {
                $dates = $this->takeFrequency($date_id, $frequency_id);

                if($postData['frequency_id'] == 'Yearly')
                    {
                        if(in_array('TopManagement',$rolenames)){
                            $count = $access ? $access->branch_program_id!= '' ? BranchTransfer::find()
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_expense_branch_transfer.branch_id')
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_expense_branch_transfer.branch_program_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->andWhere(['like','accounting_expense.datetime', $dates['year']])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->count() : BranchTransfer::find()
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_expense_branch_transfer.branch_id')
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_expense_branch_transfer.branch_program_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['like','accounting_expense.datetime', $dates['year']])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->count() : BranchTransfer::find()
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_expense_branch_transfer.branch_id')
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_expense_branch_transfer.branch_program_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['like','accounting_expense.datetime', $dates['year']])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->count();
                        }else{
                            $count = $access ? $access->branch_program_id!= '' ? BranchTransfer::find()
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_expense_branch_transfer.branch_id')
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_expense_branch_transfer.branch_program_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->andWhere(['like','accounting_expense.datetime', $dates['year']])
                                ->orWhere(['accounting_branch.id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->orWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->count() : BranchTransfer::find()
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_expense_branch_transfer.branch_id')
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_expense_branch_transfer.branch_program_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['like','accounting_expense.datetime', $dates['year']])
                                ->orWhere(['accounting_branch.id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->orWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->count() : BranchTransfer::find()
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_expense_branch_transfer.branch_id')
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_expense_branch_transfer.branch_program_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['like','accounting_expense.datetime', $dates['year']])
                                ->orWhere(['accounting_branch.id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->orWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->count();
                        }
                    }else if($postData['frequency_id'] == 'Monthly')
                    {
                        if(in_array('TopManagement',$rolenames)){
                            $count = $access ? $access->branch_program_id!= '' ? BranchTransfer::find()
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_expense_branch_transfer.branch_id')
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_expense_branch_transfer.branch_program_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->andWhere(['like', 'accounting_expense.datetime', $dates['year'].'-'.$dates['month']])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->count() : BranchTransfer::find()
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_expense_branch_transfer.branch_id')
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_expense_branch_transfer.branch_program_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['like', 'accounting_expense.datetime', $dates['year'].'-'.$dates['month']])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->count() : BranchTransfer::find()
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_expense_branch_transfer.branch_id')
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_expense_branch_transfer.branch_program_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['like', 'accounting_expense.datetime', $dates['year'].'-'.$dates['month']])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->count();
                        }else{
                            $count = $access ? $access->branch_program_id!= '' ? BranchTransfer::find()
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_expense_branch_transfer.branch_id')
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_expense_branch_transfer.branch_program_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->andWhere(['like', 'accounting_expense.datetime', $dates['year'].'-'.$dates['month']])
                                ->orWhere(['accounting_branch.id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->orWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->count() : BranchTransfer::find()
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_expense_branch_transfer.branch_id')
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_expense_branch_transfer.branch_program_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['like', 'accounting_expense.datetime', $dates['year'].'-'.$dates['month']])
                                ->orWhere(['accounting_branch.id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->orWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->count() : BranchTransfer::find()
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_expense_branch_transfer.branch_id')
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_expense_branch_transfer.branch_program_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['like', 'accounting_expense.datetime', $dates['year'].'-'.$dates['month']])
                                ->orWhere(['accounting_branch.id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->orWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->count();
                        }

                    }else if($postData['frequency_id'] == 'Cut Off')
                    {
                        if(in_array('TopManagement',$rolenames)){
                            $count = $access ? $access->branch_program_id!= '' ? BranchTransfer::find()
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_expense_branch_transfer.branch_id')
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_expense_branch_transfer.branch_program_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->andWhere(['between', 'accounting_expense.datetime', $dates['start'].' 00:00:00', $dates['end'].' 23:59:59'])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->count() : BranchTransfer::find()
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_expense_branch_transfer.branch_id')
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_expense_branch_transfer.branch_program_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['between', 'accounting_expense.datetime', $dates['start'].' 00:00:00', $dates['end'].' 23:59:59'])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->count() : BranchTransfer::find()
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_expense_branch_transfer.branch_id')
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_expense_branch_transfer.branch_program_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['between', 'accounting_expense.datetime', $dates['start'].' 00:00:00', $dates['end'].' 23:59:59'])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->count();
                        }else{
                            $count = $access ? $access->branch_program_id!= '' ? BranchTransfer::find()
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_expense_branch_transfer.branch_id')
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_expense_branch_transfer.branch_program_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->andWhere(['between', 'accounting_expense.datetime', $dates['start'].' 00:00:00', $dates['end'].' 23:59:59'])
                                ->orWhere(['accounting_branch.id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->orWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->count() : BranchTransfer::find()
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_expense_branch_transfer.branch_id')
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_expense_branch_transfer.branch_program_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['between', 'accounting_expense.datetime', $dates['start'].' 00:00:00', $dates['end'].' 23:59:59'])
                                ->orWhere(['accounting_branch.id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->orWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->count() : BranchTransfer::find()
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_expense_branch_transfer.branch_id')
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_expense_branch_transfer.branch_program_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['between', 'accounting_expense.datetime', $dates['start'].' 00:00:00', $dates['end'].' 23:59:59'])
                                ->orWhere(['accounting_branch.id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->orWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->count();
                        }
                    }else if($postData['frequency_id'] == 'Daily')
                    {
                        if(in_array('TopManagement',$rolenames)){
                            $count = $access ? $access->branch_program_id!= '' ? BranchTransfer::find()
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_expense_branch_transfer.branch_id')
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_expense_branch_transfer.branch_program_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->andWhere(['like','accounting_expense.datetime', $dates['year'].'-'.$dates['month'].'-'.$dates['day']])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->count() : BranchTransfer::find()
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_expense_branch_transfer.branch_id')
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_expense_branch_transfer.branch_program_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['like','accounting_expense.datetime', $dates['year'].'-'.$dates['month'].'-'.$dates['day']])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->count() : BranchTransfer::find()
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_expense_branch_transfer.branch_id')
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_expense_branch_transfer.branch_program_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['like','accounting_expense.datetime', $dates['year'].'-'.$dates['month'].'-'.$dates['day']])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->count();
                        }else{
                            $count = $access ? $access->branch_program_id!= '' ? BranchTransfer::find()
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_expense_branch_transfer.branch_id')
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_expense_branch_transfer.branch_program_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->andWhere(['like','accounting_expense.datetime', $dates['year'].'-'.$dates['month'].'-'.$dates['day']])
                                ->orWhere(['accounting_branch.id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->orWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->count() : BranchTransfer::find()
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_expense_branch_transfer.branch_id')
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_expense_branch_transfer.branch_program_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['like','accounting_expense.datetime', $dates['year'].'-'.$dates['month'].'-'.$dates['day']])
                                ->orWhere(['accounting_branch.id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->orWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->count() : BranchTransfer::find()
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_expense_branch_transfer.branch_id')
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_expense_branch_transfer.branch_program_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['like','accounting_expense.datetime', $dates['year'].'-'.$dates['month'].'-'.$dates['day']])
                                ->orWhere(['accounting_branch.id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->orWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->count();
                        }
                    }

                $limit = 1000;
                $pages = ceil($count / $limit);
                $offset = 0;
                $rangeValue = 0;

                for($x=0;$x<$pages;$x++)
                {
                    $offset += $rangeValue;
                    $reportPages[] = ['id' => $offset, 'text' => ($x+1).' of '.$pages];
                    $rangeValue = 1000;
                }
                $reportPages[] = ['id' => '', 'text' => 'Select One'];
            }else{
                $reportPages = [];
            }
        }

        return $reportPages;
    }

    /**
     * Lists all BranchTransfer models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new BranchTransferSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single BranchTransfer model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $budgetProposal = $model->budgetProposal;
        return $this->render('view', [
            'model' => $model,
            'budgetProposal' => $budgetProposal,
        ]);
    }

    /**
     * Deletes an existing BranchTransfer model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the BranchTransfer model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return BranchTransfer the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = BranchTransfer::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
