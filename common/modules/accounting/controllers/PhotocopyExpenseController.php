<?php

namespace common\modules\accounting\controllers;

use Yii;
use common\modules\accounting\models\AccessProgram;
use common\modules\accounting\models\PhotocopyExpense;
use common\modules\accounting\models\Expense;
use common\modules\accounting\models\Season;
use common\modules\accounting\models\ArchiveSeason;
use common\modules\accounting\models\DateRestriction;
use common\modules\accounting\models\PhotocopyExpenseSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use kartik\mpdf\Pdf;
/**
 * PhotocopyExpenseController implements the CRUD actions for PhotocopyExpense model.
 */
class PhotocopyExpenseController extends Controller
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
                'only' => ['index','update', 'delete'],
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['managePhotocopyExpense'],
                    ],
                    [
                        'actions' => ['update'],
                        'allow' => true,
                        'roles' => ['updatePhotocopyExpense'],
                    ],
                    [
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['deletePhotocopyExpense'],
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
        $model = new PhotocopyExpense();
        $model->scenario = 'searchPhotocopyExpense';
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
            $seasons = $access ? $access->branch_program_id != '' ? Season::find()
                   ->select([
                    'accounting_season.id as id',
                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name'
                   ])
                   ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                   ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                   ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                   ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                   ->asArray()
                   ->orderBy(['name' => SORT_ASC])
                   ->all() : Season::find()
                   ->select([
                    'accounting_season.id as id',
                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name'
                   ])
                   ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                   ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                   ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                   ->asArray()
                   ->orderBy(['name' => SORT_ASC])
                   ->all() : Season::find()
                   ->select([
                    'accounting_season.id as id',
                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name'
                   ])
                   ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                   ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                   ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                   ->asArray()
                   ->orderBy(['name' => SORT_ASC])
                   ->all();
        }else{
            $seasons = $access ? $access->branch_program_id != '' ? Season::find()
                   ->select([
                    'accounting_season.id as id',
                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name'
                   ])
                   ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                   ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                   ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                   ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                   ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                   ->asArray()
                   ->orderBy(['name' => SORT_ASC])
                   ->all() : Season::find()
                   ->select([
                    'accounting_season.id as id',
                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name'
                   ])
                   ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                   ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                   ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                   ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                   ->asArray()
                   ->orderBy(['name' => SORT_ASC])
                   ->all() : Season::find()
                   ->select([
                    'accounting_season.id as id',
                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name'
                   ])
                   ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                   ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                   ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                   ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                   ->asArray()
                   ->orderBy(['name' => SORT_ASC])
                   ->all();
        }

        $seasons = ArrayHelper::map($seasons, 'id', 'name');

        $pages = [];

        if(Yii::$app->request->post())
        {
            $postData = Yii::$app->request->post()['PhotocopyExpense'];

            $season = Season::findOne($postData['seasons_id']);

            $limit = 1000;

            if($season)
            {
                $dates = [];

                if($postData['frequency_id']!="" && $postData['date_id']!="")
                {
                    $dates = $this->takeFrequency($postData['date_id'], $postData['frequency_id']);

                    if($postData['frequency_id'] == 'Yearly')
                    {
                        $data = $access ? $access->branch_program_id!= '' ? PhotocopyExpense::find()
                                ->select([
                                    'accounting_expense_photocopy_expense.cv_no',
                                    'accounting_expense_photocopy_expense.subject',
                                    'accounting_expense_photocopy_expense.no_of_pages',
                                    'accounting_expense_photocopy_expense.no_of_pieces',
                                    'accounting_expense_photocopy_expense.amount_per_page',
                                    'accounting_expense_photocopy_expense.total_amount',
                                    'accounting_expense_photocopy_expense.charge_to',
                                    'accounting_expense.amount_type',
                                    'accounting_expense.transaction_number',
                                    'accounting_expense.datetime',
                                ])
                                ->leftJoin('accounting_expense', 'accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                                ->leftJoin('accounting_season','accounting_season.id = accounting_expense.season_id')
                                ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                                ->andWhere(['like','accounting_expense.datetime', $dates['year']])
                                ->andWhere(['accounting_season.id' => $season->id])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->limit($limit)
                                ->offset($postData['page_id'])
                                ->asArray()
                                ->all() : PhotocopyExpense::find()
                                ->select([
                                    'accounting_expense_photocopy_expense.cv_no',
                                    'accounting_expense_photocopy_expense.subject',
                                    'accounting_expense_photocopy_expense.no_of_pages',
                                    'accounting_expense_photocopy_expense.no_of_pieces',
                                    'accounting_expense_photocopy_expense.amount_per_page',
                                    'accounting_expense_photocopy_expense.total_amount',
                                    'accounting_expense_photocopy_expense.charge_to',
                                    'accounting_expense.amount_type',
                                    'accounting_expense.transaction_number',
                                    'accounting_expense.datetime',
                                ])
                                ->leftJoin('accounting_expense', 'accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                                ->leftJoin('accounting_season','accounting_season.id = accounting_expense.season_id')
                                ->andWhere(['like','accounting_expense.datetime', $dates['year']])
                                ->andWhere(['accounting_season.id' => $season->id])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->limit($limit)
                                ->offset($postData['page_id'])
                                ->asArray()
                                ->all() : PhotocopyExpense::find()
                                ->select([
                                    'accounting_expense_photocopy_expense.cv_no',
                                    'accounting_expense_photocopy_expense.subject',
                                    'accounting_expense_photocopy_expense.no_of_pages',
                                    'accounting_expense_photocopy_expense.no_of_pieces',
                                    'accounting_expense_photocopy_expense.amount_per_page',
                                    'accounting_expense_photocopy_expense.total_amount',
                                    'accounting_expense_photocopy_expense.charge_to',
                                    'accounting_expense.amount_type',
                                    'accounting_expense.transaction_number',
                                    'accounting_expense.datetime',
                                ])
                                ->leftJoin('accounting_expense', 'accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                                ->leftJoin('accounting_season','accounting_season.id = accounting_expense.season_id')
                                ->andWhere(['like','accounting_expense.datetime', $dates['year']])
                                ->andWhere(['accounting_season.id' => $season->id])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->limit($limit)
                                ->offset($postData['page_id'])
                                ->asArray()
                                ->all();
                    }else if($postData['frequency_id'] == 'Monthly')
                    {
                        $data = $access ? $access->branch_program_id!= '' ? PhotocopyExpense::find()
                                ->select([
                                    'accounting_expense_photocopy_expense.cv_no',
                                    'accounting_expense_photocopy_expense.subject',
                                    'accounting_expense_photocopy_expense.no_of_pages',
                                    'accounting_expense_photocopy_expense.no_of_pieces',
                                    'accounting_expense_photocopy_expense.amount_per_page',
                                    'accounting_expense_photocopy_expense.total_amount',
                                    'accounting_expense_photocopy_expense.charge_to',
                                    'accounting_expense.amount_type',
                                    'accounting_expense.transaction_number',
                                    'accounting_expense.datetime',
                                ])
                                ->leftJoin('accounting_expense', 'accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                                ->leftJoin('accounting_season','accounting_season.id = accounting_expense.season_id')
                                ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                                ->andWhere(['like', 'accounting_expense.datetime', $dates['year'].'-'.$dates['month']])
                                ->andWhere(['accounting_season.id' => $season->id])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->limit($limit)
                                ->offset($postData['page_id'])
                                ->asArray()
                                ->all() : PhotocopyExpense::find()
                                ->select([
                                    'accounting_expense_photocopy_expense.cv_no',
                                    'accounting_expense_photocopy_expense.subject',
                                    'accounting_expense_photocopy_expense.no_of_pages',
                                    'accounting_expense_photocopy_expense.no_of_pieces',
                                    'accounting_expense_photocopy_expense.amount_per_page',
                                    'accounting_expense_photocopy_expense.total_amount',
                                    'accounting_expense_photocopy_expense.charge_to',
                                    'accounting_expense.amount_type',
                                    'accounting_expense.transaction_number',
                                    'accounting_expense.datetime',
                                ])
                                ->leftJoin('accounting_expense', 'accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                                ->leftJoin('accounting_season','accounting_season.id = accounting_expense.season_id')
                                ->andWhere(['like', 'accounting_expense.datetime', $dates['year'].'-'.$dates['month']])
                                ->andWhere(['accounting_season.id' => $season->id])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->limit($limit)
                                ->offset($postData['page_id'])
                                ->asArray()
                                ->all() : PhotocopyExpense::find()
                                ->select([
                                    'accounting_expense_photocopy_expense.cv_no',
                                    'accounting_expense_photocopy_expense.subject',
                                    'accounting_expense_photocopy_expense.no_of_pages',
                                    'accounting_expense_photocopy_expense.no_of_pieces',
                                    'accounting_expense_photocopy_expense.amount_per_page',
                                    'accounting_expense_photocopy_expense.total_amount',
                                    'accounting_expense_photocopy_expense.charge_to',
                                    'accounting_expense.amount_type',
                                    'accounting_expense.transaction_number',
                                    'accounting_expense.datetime',
                                ])
                                ->leftJoin('accounting_expense', 'accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                                ->leftJoin('accounting_season','accounting_season.id = accounting_expense.season_id')
                                ->andWhere(['like', 'accounting_expense.datetime', $dates['year'].'-'.$dates['month']])
                                ->andWhere(['accounting_season.id' => $season->id])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->limit($limit)
                                ->offset($postData['page_id'])
                                ->asArray()
                                ->all();
                    }else if($postData['frequency_id'] == 'Cut Off')
                    {
                        $data = $access ? $access->branch_program_id!= '' ? PhotocopyExpense::find()
                                ->select([
                                    'accounting_expense_photocopy_expense.cv_no',
                                    'accounting_expense_photocopy_expense.subject',
                                    'accounting_expense_photocopy_expense.no_of_pages',
                                    'accounting_expense_photocopy_expense.no_of_pieces',
                                    'accounting_expense_photocopy_expense.amount_per_page',
                                    'accounting_expense_photocopy_expense.total_amount',
                                    'accounting_expense_photocopy_expense.charge_to',
                                    'accounting_expense.amount_type',
                                    'accounting_expense.transaction_number',
                                    'accounting_expense.datetime',
                                ])
                                ->leftJoin('accounting_expense', 'accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                                ->leftJoin('accounting_season','accounting_season.id = accounting_expense.season_id')
                                ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                                ->andWhere(['between', 'accounting_expense.datetime', $dates['start'].' 00:00:00', $dates['end'].' 23:59:59'])
                                ->andWhere(['accounting_season.id' => $season->id])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->limit($limit)
                                ->offset($postData['page_id'])
                                ->asArray()
                                ->all() : PhotocopyExpense::find()
                                ->select([
                                    'accounting_expense_photocopy_expense.cv_no',
                                    'accounting_expense_photocopy_expense.subject',
                                    'accounting_expense_photocopy_expense.no_of_pages',
                                    'accounting_expense_photocopy_expense.no_of_pieces',
                                    'accounting_expense_photocopy_expense.amount_per_page',
                                    'accounting_expense_photocopy_expense.total_amount',
                                    'accounting_expense_photocopy_expense.charge_to',
                                    'accounting_expense.amount_type',
                                    'accounting_expense.transaction_number',
                                    'accounting_expense.datetime',
                                ])
                                ->leftJoin('accounting_expense', 'accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                                ->leftJoin('accounting_season','accounting_season.id = accounting_expense.season_id')
                                ->andWhere(['between', 'accounting_expense.datetime', $dates['start'].' 00:00:00', $dates['end'].' 23:59:59'])
                                ->andWhere(['accounting_season.id' => $season->id])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->limit($limit)
                                ->offset($postData['page_id'])
                                ->asArray()
                                ->all() : PhotocopyExpense::find()
                                ->select([
                                    'accounting_expense_photocopy_expense.cv_no',
                                    'accounting_expense_photocopy_expense.subject',
                                    'accounting_expense_photocopy_expense.no_of_pages',
                                    'accounting_expense_photocopy_expense.no_of_pieces',
                                    'accounting_expense_photocopy_expense.amount_per_page',
                                    'accounting_expense_photocopy_expense.total_amount',
                                    'accounting_expense_photocopy_expense.charge_to',
                                    'accounting_expense.amount_type',
                                    'accounting_expense.transaction_number',
                                    'accounting_expense.datetime',
                                ])
                                ->leftJoin('accounting_expense', 'accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                                ->leftJoin('accounting_season','accounting_season.id = accounting_expense.season_id')
                                ->andWhere(['between', 'accounting_expense.datetime', $dates['start'].' 00:00:00', $dates['end'].' 23:59:59'])
                                ->andWhere(['accounting_season.id' => $season->id])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->limit($limit)
                                ->offset($postData['page_id'])
                                ->asArray()
                                ->all();
                    }else if($postData['frequency_id'] == 'Daily')
                    {
                        $data = $access ? $access->branch_program_id!= '' ? PhotocopyExpense::find()
                                ->select([
                                    'accounting_expense_photocopy_expense.cv_no',
                                    'accounting_expense_photocopy_expense.subject',
                                    'accounting_expense_photocopy_expense.no_of_pages',
                                    'accounting_expense_photocopy_expense.no_of_pieces',
                                    'accounting_expense_photocopy_expense.amount_per_page',
                                    'accounting_expense_photocopy_expense.total_amount',
                                    'accounting_expense_photocopy_expense.charge_to',
                                    'accounting_expense.amount_type',
                                    'accounting_expense.transaction_number',
                                    'accounting_expense.datetime',
                                ])
                                ->leftJoin('accounting_expense', 'accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                                ->leftJoin('accounting_season','accounting_season.id = accounting_expense.season_id')
                                ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                                ->andWhere(['like','accounting_expense.datetime', $dates['year'].'-'.$dates['month'].'-'.$dates['day']])
                                ->andWhere(['accounting_season.id' => $season->id])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->limit($limit)
                                ->offset($postData['page_id'])
                                ->asArray()
                                ->all() : PhotocopyExpense::find()
                                ->select([
                                    'accounting_expense_photocopy_expense.cv_no',
                                    'accounting_expense_photocopy_expense.subject',
                                    'accounting_expense_photocopy_expense.no_of_pages',
                                    'accounting_expense_photocopy_expense.no_of_pieces',
                                    'accounting_expense_photocopy_expense.amount_per_page',
                                    'accounting_expense_photocopy_expense.total_amount',
                                    'accounting_expense_photocopy_expense.charge_to',
                                    'accounting_expense.amount_type',
                                    'accounting_expense.transaction_number',
                                    'accounting_expense.datetime',
                                ])
                                ->leftJoin('accounting_expense', 'accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                                ->leftJoin('accounting_season','accounting_season.id = accounting_expense.season_id')
                                ->andWhere(['like','accounting_expense.datetime', $dates['year'].'-'.$dates['month'].'-'.$dates['day']])
                                ->andWhere(['accounting_season.id' => $season->id])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->limit($limit)
                                ->offset($postData['page_id'])
                                ->asArray()
                                ->all() : PhotocopyExpense::find()
                                ->select([
                                    'accounting_expense_photocopy_expense.cv_no',
                                    'accounting_expense_photocopy_expense.subject',
                                    'accounting_expense_photocopy_expense.no_of_pages',
                                    'accounting_expense_photocopy_expense.no_of_pieces',
                                    'accounting_expense_photocopy_expense.amount_per_page',
                                    'accounting_expense_photocopy_expense.total_amount',
                                    'accounting_expense_photocopy_expense.charge_to',
                                    'accounting_expense.amount_type',
                                    'accounting_expense.transaction_number',
                                    'accounting_expense.datetime',
                                ])
                                ->leftJoin('accounting_expense', 'accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                                ->leftJoin('accounting_season','accounting_season.id = accounting_expense.season_id')
                                ->andWhere(['like','accounting_expense.datetime', $dates['year'].'-'.$dates['month'].'-'.$dates['day']])
                                ->andWhere(['accounting_season.id' => $season->id])
                                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                                ->limit($limit)
                                ->offset($postData['page_id'])
                                ->asArray()
                                ->all();
                    }

                    $pages = ceil(count($data) / $limit);

                    $content = $this->renderPartial('_photocopy-expense-report', [
                        'data' => $data,
                        'season' => $season,
                        'postData' => $postData,
                        'dates' => $dates,
                    ]);

                    $pdf = new Pdf([
                    'mode' => Pdf::MODE_CORE,
                    'format' => Pdf::FORMAT_LEGAL, 
                    'orientation' => Pdf::ORIENT_LANDSCAPE, 
                    'destination' => Pdf::DEST_DOWNLOAD, 
                    'filename' => $postData['frequency_id'].' Report: Photocopy Expense - '.$season->seasonName.' - Page '.($postData['page_id']+1).' of '.$pages.'.pdf',
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
                    'options' => ['title' => 'Photocopy Expense'],
                    'methods' => [ 
                        'SetHeader'=>[$postData['frequency_id'].' Report: Photocopy Expense - '.$season->seasonName.' - Page '.($postData['page_id']+1).' of '.$pages], 
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

        return $this->renderAjax('_search-photocopy-expense',[
            'model' => $model,
            'seasons' => $seasons,
            'frequencies' => $frequencies,
            'pages' => $pages,
        ]);
    }

    public function actionPageList($season_id = '', $frequency_id = '', $date_id = '') {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $user_info = Yii::$app->user->identity->userinfo;
        $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
        $rolenames =  ArrayHelper::map($roles, 'name','name');

        $season = Season::findOne($season_id);
        $reportPages = [];

        $access = AccessProgram::findOne(['user_id' => Yii::$app->user->identity->userinfo->user_id]);
        $count = 0;

        if($season)
        {
            $dates = [];

            if($frequency_id != '' && $date_id != '')
            {
                $dates = $this->takeFrequency($date_id, $frequency_id);

                if($frequency_id == 'Yearly')
                {
                    $count = $access ? $access->branch_program_id!= '' ? PhotocopyExpense::find()
                            ->leftJoin('accounting_expense', 'accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                            ->leftJoin('accounting_season','accounting_season.id = accounting_expense.season_id')
                            ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                            ->andWhere(['like','accounting_expense.datetime', $dates['year']])
                            ->andWhere(['accounting_season.id' => $season->id])
                            ->asArray()
                            ->count() : PhotocopyExpense::find()
                            ->leftJoin('accounting_expense', 'accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                            ->leftJoin('accounting_season','accounting_season.id = accounting_expense.season_id')
                            ->andWhere(['like','accounting_expense.datetime', $dates['year']])
                            ->andWhere(['accounting_season.id' => $season->id])
                            ->asArray()
                            ->count() : PhotocopyExpense::find()
                            ->leftJoin('accounting_expense', 'accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                            ->leftJoin('accounting_season','accounting_season.id = accounting_expense.season_id')
                            ->andWhere(['like','accounting_expense.datetime', $dates['year']])
                            ->andWhere(['accounting_season.id' => $season->id])
                            ->asArray()
                            ->count();
                }else if($frequency_id == 'Monthly')
                {
                    $count = $access ? $access->branch_program_id!= '' ? PhotocopyExpense::find()
                            ->leftJoin('accounting_expense', 'accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                            ->leftJoin('accounting_season','accounting_season.id = accounting_expense.season_id')
                            ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                            ->andWhere(['like', 'accounting_expense.datetime', $dates['year'].'-'.$dates['month']])
                            ->andWhere(['accounting_season.id' => $season->id])
                            ->asArray()
                            ->count() : PhotocopyExpense::find()
                            ->leftJoin('accounting_expense', 'accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                            ->leftJoin('accounting_season','accounting_season.id = accounting_expense.season_id')
                            ->andWhere(['like', 'accounting_expense.datetime', $dates['year'].'-'.$dates['month']])
                            ->andWhere(['accounting_season.id' => $season->id])
                            ->asArray()
                            ->count() : PhotocopyExpense::find()
                            ->leftJoin('accounting_expense', 'accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                            ->leftJoin('accounting_season','accounting_season.id = accounting_expense.season_id')
                            ->andWhere(['like', 'accounting_expense.datetime', $dates['year'].'-'.$dates['month']])
                            ->andWhere(['accounting_season.id' => $season->id])
                            ->asArray()
                            ->count();
                }else if($frequency_id == 'Cut Off')
                {
                    $count = $access ? $access->branch_program_id!= '' ? PhotocopyExpense::find()
                            ->leftJoin('accounting_expense', 'accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                            ->leftJoin('accounting_season','accounting_season.id = accounting_expense.season_id')
                            ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                            ->andWhere(['between', 'accounting_expense.datetime', $dates['start'].' 00:00:00', $dates['end'].' 23:59:59'])
                            ->andWhere(['accounting_season.id' => $season->id])
                            ->asArray()
                            ->count() : PhotocopyExpense::find()
                            ->leftJoin('accounting_expense', 'accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                            ->leftJoin('accounting_season','accounting_season.id = accounting_expense.season_id')
                            ->andWhere(['between', 'accounting_expense.datetime', $dates['start'].' 00:00:00', $dates['end'].' 23:59:59'])
                            ->andWhere(['accounting_season.id' => $season->id])
                            ->asArray()
                            ->count() : PhotocopyExpense::find()
                            ->leftJoin('accounting_expense', 'accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                            ->leftJoin('accounting_season','accounting_season.id = accounting_expense.season_id')
                            ->andWhere(['between', 'accounting_expense.datetime', $dates['start'].' 00:00:00', $dates['end'].' 23:59:59'])
                            ->andWhere(['accounting_season.id' => $season->id])
                            ->asArray()
                            ->count();
                }else if($frequency_id == 'Daily')
                {
                    $count = $access ? $access->branch_program_id!= '' ? PhotocopyExpense::find()
                            ->leftJoin('accounting_expense', 'accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                            ->leftJoin('accounting_season','accounting_season.id = accounting_expense.season_id')
                            ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                            ->andWhere(['like','accounting_expense.datetime', $dates['year'].'-'.$dates['month'].'-'.$dates['day']])
                            ->andWhere(['accounting_season.id' => $season->id])
                            ->asArray()
                            ->count() : PhotocopyExpense::find()
                            ->leftJoin('accounting_expense', 'accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                            ->leftJoin('accounting_season','accounting_season.id = accounting_expense.season_id')
                            ->andWhere(['like','accounting_expense.datetime', $dates['year'].'-'.$dates['month'].'-'.$dates['day']])
                            ->andWhere(['accounting_season.id' => $season->id])
                            ->asArray()
                            ->count() : PhotocopyExpense::find()
                            ->leftJoin('accounting_expense', 'accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                            ->leftJoin('accounting_season','accounting_season.id = accounting_expense.season_id')
                            ->andWhere(['like','accounting_expense.datetime', $dates['year'].'-'.$dates['month'].'-'.$dates['day']])
                            ->andWhere(['accounting_season.id' => $season->id])
                            ->asArray()
                            ->count();
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
     * Lists all PhotocopyExpense models.
     * @return mixed
     */
    public function actionIndex()
    {
        $model = new PhotocopyExpense();
        $expenseModel = new Expense();
        $searchModel = new PhotocopyExpenseSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $user_info = Yii::$app->user->identity->userinfo;
        $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
        $rolenames =  ArrayHelper::map($roles, 'name','name');

        $access = AccessProgram::findOne(['user_id' => Yii::$app->user->identity->userinfo->user_id]);

        if(in_array('TopManagement',$rolenames)){
            $overall = $access ? $access->branch_program_id!= '' ? PhotocopyExpense::find()
                        ->select(['sum(total_amount) as total'])
                        ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                        ->leftJoin('accounting_season','accounting_expense.season_id = accounting_season.id')
                        ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                        ->asArray()
                        ->all() : PhotocopyExpense::find()
                        ->select(['sum(total_amount) as total'])
                        ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                        ->asArray()
                        ->all() : PhotocopyExpense::find()
                        ->select(['sum(total_amount) as total'])
                        ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                        ->asArray()
                        ->all();

            $overallCash = $access ? $access->branch_program_id!= '' ? PhotocopyExpense::find()
                        ->select(['sum(total_amount) as total'])
                        ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                        ->leftJoin('accounting_season','accounting_expense.season_id = accounting_season.id')
                        ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                        ->andWhere(['accounting_expense.amount_type' => 'Cash'])
                        ->asArray()
                        ->all() : PhotocopyExpense::find()
                        ->select(['sum(total_amount) as total'])
                        ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                        ->andWhere(['accounting_expense.amount_type' => 'Cash'])
                        ->asArray()
                        ->all() : PhotocopyExpense::find()
                        ->select(['sum(total_amount) as total'])
                        ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                        ->andWhere(['accounting_expense.amount_type' => 'Cash'])
                        ->asArray()
                        ->all();

            $overallCheck = $access ? $access->branch_program_id!= '' ? PhotocopyExpense::find()
                        ->select(['sum(total_amount) as total'])
                        ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                        ->leftJoin('accounting_season','accounting_expense.season_id = accounting_season.id')
                        ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                        ->andWhere(['<>', 'accounting_expense.amount_type', 'Cash'])
                        ->asArray()
                        ->all() : PhotocopyExpense::find()
                        ->select(['sum(total_amount) as total'])
                        ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                        ->andWhere(['<>', 'accounting_expense.amount_type', 'Cash'])
                        ->asArray()
                        ->all() : PhotocopyExpense::find()
                        ->select(['sum(total_amount) as total'])
                        ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                        ->andWhere(['<>', 'accounting_expense.amount_type', 'Cash'])
                        ->asArray()
                        ->all();

            $overallToday = $access ? $access->branch_program_id!= '' ? PhotocopyExpense::find()
                        ->select(['sum(total_amount) as total'])
                        ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                        ->leftJoin('accounting_season','accounting_expense.season_id = accounting_season.id')
                        ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                        ->andWhere(['like', 'accounting_expense.datetime', date("Y-m-d")])
                        ->asArray()
                        ->all() : PhotocopyExpense::find()
                        ->select(['sum(total_amount) as total'])
                        ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                        ->andWhere(['like', 'accounting_expense.datetime', date("Y-m-d")])
                        ->asArray()
                        ->all() : PhotocopyExpense::find()
                        ->select(['sum(total_amount) as total'])
                        ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                        ->andWhere(['like', 'accounting_expense.datetime', date("Y-m-d")])
                        ->asArray()
                        ->all();

            $overallCashToday = $access ? $access->branch_program_id!= '' ? PhotocopyExpense::find()
                        ->select(['sum(total_amount) as total'])
                        ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                        ->leftJoin('accounting_season','accounting_expense.season_id = accounting_season.id')
                        ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                        ->andWhere(['accounting_expense.amount_type' => 'Cash'])
                        ->andWhere(['like', 'accounting_expense.datetime', date("Y-m-d")])
                        ->asArray()
                        ->all() : PhotocopyExpense::find()
                        ->select(['sum(total_amount) as total'])
                        ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                        ->where(['accounting_expense.amount_type' => 'Cash'])
                        ->andWhere(['like', 'accounting_expense.datetime', date("Y-m-d")])
                        ->asArray()
                        ->all() : PhotocopyExpense::find()
                        ->select(['sum(total_amount) as total'])
                        ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                        ->where(['accounting_expense.amount_type' => 'Cash'])
                        ->andWhere(['like', 'accounting_expense.datetime', date("Y-m-d")])
                        ->asArray()
                        ->all();

            $overallCheckToday = $access ? $access->branch_program_id!= '' ? PhotocopyExpense::find()
                        ->select(['sum(total_amount) as total'])
                        ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                        ->leftJoin('accounting_season','accounting_expense.season_id = accounting_season.id')
                        ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                        ->andWhere(['<>', 'accounting_expense.amount_type', 'Cash'])
                        ->andWhere(['like', 'accounting_expense.datetime', date("Y-m-d")])
                        ->asArray()
                        ->all() : PhotocopyExpense::find()
                        ->select(['sum(total_amount) as total'])
                        ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                        ->andWhere(['<>', 'accounting_expense.amount_type', 'Cash'])
                        ->andWhere(['like', 'accounting_expense.datetime', date("Y-m-d")])
                        ->asArray()
                        ->all() : PhotocopyExpense::find()
                        ->select(['sum(total_amount) as total'])
                        ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                        ->andWhere(['<>', 'accounting_expense.amount_type', 'Cash'])
                        ->andWhere(['like', 'accounting_expense.datetime', date("Y-m-d")])
                        ->asArray()
                        ->all();

            return $this->render('index', [
                'model' => $model,
                'expenseModel' => $expenseModel,
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'overall' => $overall,
                'overallCash' => $overallCash,
                'overallCheck' => $overallCheck,
                'overallToday' => $overallToday,
                'overallCashToday' => $overallCashToday,
                'overallCheckToday' => $overallCheckToday,
            ]);
        }else{
            $archivedSeasons = ArchiveSeason::find()->select(['season_id as id'])->asArray()->all();
            $archivedSeasons = ArrayHelper::map($archivedSeasons, 'id', 'id');

            $seasons = $access ? $access->branch_program_id!= '' ? Season::find()
                   ->select([
                    'accounting_season.id as id',
                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name'
                   ])
                   ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                   ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                   ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                   ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                   ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                   ->andWhere(['not in', 'accounting_season.id', $archivedSeasons])
                   ->asArray()
                   ->orderBy(['accounting_season.end_date' => SORT_DESC, 'name' => SORT_ASC])
                   ->all() : Season::find()
                   ->select([
                    'accounting_season.id as id',
                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name'
                   ])
                   ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                   ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                   ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                   ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                   ->andWhere(['not in', 'accounting_season.id', $archivedSeasons])
                   ->asArray()
                   ->orderBy(['accounting_season.end_date' => SORT_DESC, 'name' => SORT_ASC])
                   ->all() : Season::find()
                   ->select([
                    'accounting_season.id as id',
                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name'
                   ])
                   ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                   ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                   ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                   ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                   ->andWhere(['not in', 'accounting_season.id', $archivedSeasons])
                   ->asArray()
                   ->orderBy(['accounting_season.end_date' => SORT_DESC, 'name' => SORT_ASC])
                   ->all();

            $seasons = ArrayHelper::map($seasons, 'id', 'name');

            $dateRestriction = DateRestriction::findOne(['branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C]);

            $overall = $access ? $access->branch_program_id!= '' ? PhotocopyExpense::find()
                        ->select(['sum(total_amount) as total'])
                        ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                        ->leftJoin('accounting_season','accounting_expense.season_id = accounting_season.id')
                        ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                        ->andWhere(['accounting_expense.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                        ->asArray()
                        ->all() : PhotocopyExpense::find()
                        ->select(['sum(total_amount) as total'])
                        ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                        ->andWhere(['accounting_expense.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                        ->asArray()
                        ->all() : PhotocopyExpense::find()
                        ->select(['sum(total_amount) as total'])
                        ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                        ->andWhere(['accounting_expense.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                        ->asArray()
                        ->all();

            $overallCash = $access ? $access->branch_program_id!= '' ? PhotocopyExpense::find()
                        ->select(['sum(total_amount) as total'])
                        ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                        ->leftJoin('accounting_season','accounting_expense.season_id = accounting_season.id')
                        ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                        ->andWhere(['accounting_expense.amount_type' => 'Cash'])
                        ->andWhere(['accounting_expense.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                        ->asArray()
                        ->all() : PhotocopyExpense::find()
                        ->select(['sum(total_amount) as total'])
                        ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                        ->andWhere(['accounting_expense.amount_type' => 'Cash'])
                        ->andWhere(['accounting_expense.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                        ->asArray()
                        ->all() : PhotocopyExpense::find()
                        ->select(['sum(total_amount) as total'])
                        ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                        ->andWhere(['accounting_expense.amount_type' => 'Cash'])
                        ->andWhere(['accounting_expense.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                        ->asArray()
                        ->all();

            $overallCheck = $access ? $access->branch_program_id!= '' ? PhotocopyExpense::find()
                        ->select(['sum(total_amount) as total'])
                        ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                        ->leftJoin('accounting_season','accounting_expense.season_id = accounting_season.id')
                        ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                        ->andWhere(['<>', 'accounting_expense.amount_type', 'Cash'])
                        ->andWhere(['accounting_expense.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                        ->asArray()
                        ->all() : PhotocopyExpense::find()
                        ->select(['sum(total_amount) as total'])
                        ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                        ->andWhere(['<>', 'accounting_expense.amount_type', 'Cash'])
                        ->andWhere(['accounting_expense.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                        ->asArray()
                        ->all() : PhotocopyExpense::find()
                        ->select(['sum(total_amount) as total'])
                        ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                        ->andWhere(['<>', 'accounting_expense.amount_type', 'Cash'])
                        ->andWhere(['accounting_expense.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                        ->asArray()
                        ->all();

            $overallToday = $access ? $access->branch_program_id!= '' ? PhotocopyExpense::find()
                        ->select(['sum(total_amount) as total'])
                        ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                        ->leftJoin('accounting_season','accounting_expense.season_id = accounting_season.id')
                        ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                        ->andWhere(['like', 'accounting_expense.datetime', date("Y-m-d")])
                        ->andWhere(['accounting_expense.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                        ->asArray()
                        ->all() : PhotocopyExpense::find()
                        ->select(['sum(total_amount) as total'])
                        ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                        ->andWhere(['like', 'accounting_expense.datetime', date("Y-m-d")])
                        ->andWhere(['accounting_expense.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                        ->asArray()
                        ->all() : PhotocopyExpense::find()
                        ->select(['sum(total_amount) as total'])
                        ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                        ->andWhere(['like', 'accounting_expense.datetime', date("Y-m-d")])
                        ->andWhere(['accounting_expense.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                        ->asArray()
                        ->all();

            $overallCashToday = $access ? $access->branch_program_id!= '' ? PhotocopyExpense::find()
                        ->select(['sum(total_amount) as total'])
                        ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                        ->leftJoin('accounting_season','accounting_expense.season_id = accounting_season.id')
                        ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                        ->andWhere(['accounting_expense.amount_type' => 'Cash'])
                        ->andWhere(['like', 'accounting_expense.datetime', date("Y-m-d")])
                        ->andWhere(['accounting_expense.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                        ->asArray()
                        ->all() : PhotocopyExpense::find()
                        ->select(['sum(total_amount) as total'])
                        ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                        ->where(['accounting_expense.amount_type' => 'Cash'])
                        ->andWhere(['like', 'accounting_expense.datetime', date("Y-m-d")])
                        ->andWhere(['accounting_expense.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                        ->asArray()
                        ->all() : PhotocopyExpense::find()
                        ->select(['sum(total_amount) as total'])
                        ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                        ->where(['accounting_expense.amount_type' => 'Cash'])
                        ->andWhere(['like', 'accounting_expense.datetime', date("Y-m-d")])
                        ->andWhere(['accounting_expense.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                        ->asArray()
                        ->all();

            $overallCheckToday = $access ? $access->branch_program_id!= '' ? PhotocopyExpense::find()
                        ->select(['sum(total_amount) as total'])
                        ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                        ->leftJoin('accounting_season','accounting_expense.season_id = accounting_season.id')
                        ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                        ->andWhere(['<>', 'accounting_expense.amount_type', 'Cash'])
                        ->andWhere(['like', 'accounting_expense.datetime', date("Y-m-d")])
                        ->andWhere(['accounting_expense.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                        ->asArray()
                        ->all() : PhotocopyExpense::find()
                        ->select(['sum(total_amount) as total'])
                        ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                        ->andWhere(['<>', 'accounting_expense.amount_type', 'Cash'])
                        ->andWhere(['like', 'accounting_expense.datetime', date("Y-m-d")])
                        ->andWhere(['accounting_expense.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                        ->asArray()
                        ->all() : PhotocopyExpense::find()
                        ->select(['sum(total_amount) as total'])
                        ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                        ->andWhere(['<>', 'accounting_expense.amount_type', 'Cash'])
                        ->andWhere(['like', 'accounting_expense.datetime', date("Y-m-d")])
                        ->andWhere(['accounting_expense.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                        ->asArray()
                        ->all();

            if ($model->load(Yii::$app->request->post())) {
                $postData = Yii::$app->request->post();
                if($model->save(false))
                {   
                    $selectedSeason = Season::findOne($postData['Expense']['season_id']);
                   if($selectedSeason)
                   {
                        $expenseModel->expense_type_id = 2; 
                        $expenseModel->branch_id = Yii::$app->user->identity->userinfo->BRANCH_C;  
                        $expenseModel->program_id = $selectedSeason->branchProgram->program_id;  
                        $expenseModel->season_id = $selectedSeason->id;  
                        $expenseModel->expense_id = $model->id; 
                        $expenseModel->amount_type = $postData['Expense']['amount_type'];
                        $expenseModel->transaction_number = $postData['Expense']['transaction_number'];
                        if($dateRestriction){
                            if($dateRestriction->allow == "Yes" && $dateRestriction->open_type == "Expenses"){ 
                                if(!isset($postData['Expense']['dateNow']))
                                {
                                    if($postData['Expense']['datetime']!=""){
                                        $expenseModel->datetime = $postData['Expense']['datetime'].' '.date("H:i:s");
                                    }
                                }
                            }
                        }
                        $expenseModel->save(false);

                        \Yii::$app->getSession()->setFlash('success', 'Photocopy expense has been saved.');
                        return $this->redirect(['index']);
                   }
                }
            }

            return $this->render('index', [
                'model' => $model,
                'seasons' => $seasons,
                'expenseModel' => $expenseModel,
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'dateRestriction' => $dateRestriction,
                'overall' => $overall,
                'overallCash' => $overallCash,
                'overallCheck' => $overallCheck,
                'overallToday' => $overallToday,
                'overallCashToday' => $overallCashToday,
                'overallCheckToday' => $overallCheckToday,
            ]);
        }
    }

    /**
     * Updates an existing PhotocopyExpense model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $expenseModel = Expense::findOne(['expense_id' => $model->id, 'expense_type_id' => '2']);
        $dateRestriction = DateRestriction::findOne(['branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C]);
        $expenseModel->datetime = (date("Y-m-d", strtotime($expenseModel->datetime)) >= $dateRestriction->start_date) && (date("Y-m-d", strtotime($expenseModel->datetime)) <= $dateRestriction->end_date) ? date('Y-m-d', strtotime($expenseModel->datetime)) : '';
        $searchModel = new PhotocopyExpenseSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $access = AccessProgram::findOne(['user_id' => Yii::$app->user->identity->userinfo->user_id]);

        $archivedSeasons = ArchiveSeason::find()->select(['season_id as id'])->asArray()->all();
        $archivedSeasons = ArrayHelper::map($archivedSeasons, 'id', 'id');

        $seasons = $access ? $access->branch_program_id!= '' ? Season::find()
                   ->select([
                    'accounting_season.id as id',
                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name'
                   ])
                   ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                   ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                   ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                   ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                   ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                   ->andWhere(['not in', 'accounting_season.id', $archivedSeasons])
                   ->asArray()
                   ->orderBy(['accounting_season.end_date' => SORT_DESC, 'name' => SORT_ASC])
                   ->all() : Season::find()
                   ->select([
                    'accounting_season.id as id',
                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name'
                   ])
                   ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                   ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                   ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                   ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                   ->andWhere(['not in', 'accounting_season.id', $archivedSeasons])
                   ->asArray()
                   ->orderBy(['accounting_season.end_date' => SORT_DESC, 'name' => SORT_ASC])
                   ->all() : Season::find()
                   ->select([
                    'accounting_season.id as id',
                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name'
                   ])
                   ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                   ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                   ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                   ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                   ->andWhere(['not in', 'accounting_season.id', $archivedSeasons])
                   ->asArray()
                   ->orderBy(['accounting_season.end_date' => SORT_DESC, 'name' => SORT_ASC])
                   ->all();

        $seasons = ArrayHelper::map($seasons, 'id', 'name');

        $overall = $access ? $access->branch_program_id!= '' ? PhotocopyExpense::find()
                        ->select(['sum(total_amount) as total'])
                        ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                        ->leftJoin('accounting_season','accounting_expense.season_id = accounting_season.id')
                        ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                        ->andWhere(['accounting_expense.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                        ->asArray()
                        ->all() : PhotocopyExpense::find()
                        ->select(['sum(total_amount) as total'])
                        ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                        ->andWhere(['accounting_expense.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                        ->asArray()
                        ->all() : PhotocopyExpense::find()
                        ->select(['sum(total_amount) as total'])
                        ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                        ->andWhere(['accounting_expense.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                        ->asArray()
                        ->all();

            $overallCash = $access ? $access->branch_program_id!= '' ? PhotocopyExpense::find()
                        ->select(['sum(total_amount) as total'])
                        ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                        ->leftJoin('accounting_season','accounting_expense.season_id = accounting_season.id')
                        ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                        ->andWhere(['accounting_expense.amount_type' => 'Cash'])
                        ->andWhere(['accounting_expense.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                        ->asArray()
                        ->all() : PhotocopyExpense::find()
                        ->select(['sum(total_amount) as total'])
                        ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                        ->andWhere(['accounting_expense.amount_type' => 'Cash'])
                        ->andWhere(['accounting_expense.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                        ->asArray()
                        ->all() : PhotocopyExpense::find()
                        ->select(['sum(total_amount) as total'])
                        ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                        ->andWhere(['accounting_expense.amount_type' => 'Cash'])
                        ->andWhere(['accounting_expense.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                        ->asArray()
                        ->all();

            $overallCheck = $access ? $access->branch_program_id!= '' ? PhotocopyExpense::find()
                        ->select(['sum(total_amount) as total'])
                        ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                        ->leftJoin('accounting_season','accounting_expense.season_id = accounting_season.id')
                        ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                        ->andWhere(['<>', 'accounting_expense.amount_type', 'Cash'])
                        ->andWhere(['accounting_expense.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                        ->asArray()
                        ->all() : PhotocopyExpense::find()
                        ->select(['sum(total_amount) as total'])
                        ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                        ->andWhere(['<>', 'accounting_expense.amount_type', 'Cash'])
                        ->andWhere(['accounting_expense.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                        ->asArray()
                        ->all() : PhotocopyExpense::find()
                        ->select(['sum(total_amount) as total'])
                        ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                        ->andWhere(['<>', 'accounting_expense.amount_type', 'Cash'])
                        ->andWhere(['accounting_expense.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                        ->asArray()
                        ->all();

            $overallToday = $access ? $access->branch_program_id!= '' ? PhotocopyExpense::find()
                        ->select(['sum(total_amount) as total'])
                        ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                        ->leftJoin('accounting_season','accounting_expense.season_id = accounting_season.id')
                        ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                        ->andWhere(['like', 'accounting_expense.datetime', date("Y-m-d")])
                        ->andWhere(['accounting_expense.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                        ->asArray()
                        ->all() : PhotocopyExpense::find()
                        ->select(['sum(total_amount) as total'])
                        ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                        ->andWhere(['like', 'accounting_expense.datetime', date("Y-m-d")])
                        ->andWhere(['accounting_expense.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                        ->asArray()
                        ->all() : PhotocopyExpense::find()
                        ->select(['sum(total_amount) as total'])
                        ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                        ->andWhere(['like', 'accounting_expense.datetime', date("Y-m-d")])
                        ->andWhere(['accounting_expense.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                        ->asArray()
                        ->all();

            $overallCashToday = $access ? $access->branch_program_id!= '' ? PhotocopyExpense::find()
                        ->select(['sum(total_amount) as total'])
                        ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                        ->leftJoin('accounting_season','accounting_expense.season_id = accounting_season.id')
                        ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                        ->andWhere(['accounting_expense.amount_type' => 'Cash'])
                        ->andWhere(['like', 'accounting_expense.datetime', date("Y-m-d")])
                        ->andWhere(['accounting_expense.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                        ->asArray()
                        ->all() : PhotocopyExpense::find()
                        ->select(['sum(total_amount) as total'])
                        ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                        ->where(['accounting_expense.amount_type' => 'Cash'])
                        ->andWhere(['like', 'accounting_expense.datetime', date("Y-m-d")])
                        ->andWhere(['accounting_expense.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                        ->asArray()
                        ->all() : PhotocopyExpense::find()
                        ->select(['sum(total_amount) as total'])
                        ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                        ->where(['accounting_expense.amount_type' => 'Cash'])
                        ->andWhere(['like', 'accounting_expense.datetime', date("Y-m-d")])
                        ->andWhere(['accounting_expense.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                        ->asArray()
                        ->all();

            $overallCheckToday = $access ? $access->branch_program_id!= '' ? PhotocopyExpense::find()
                        ->select(['sum(total_amount) as total'])
                        ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                        ->leftJoin('accounting_season','accounting_expense.season_id = accounting_season.id')
                        ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                        ->andWhere(['<>', 'accounting_expense.amount_type', 'Cash'])
                        ->andWhere(['like', 'accounting_expense.datetime', date("Y-m-d")])
                        ->andWhere(['accounting_expense.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                        ->asArray()
                        ->all() : PhotocopyExpense::find()
                        ->select(['sum(total_amount) as total'])
                        ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                        ->andWhere(['<>', 'accounting_expense.amount_type', 'Cash'])
                        ->andWhere(['like', 'accounting_expense.datetime', date("Y-m-d")])
                        ->andWhere(['accounting_expense.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                        ->asArray()
                        ->all() : PhotocopyExpense::find()
                        ->select(['sum(total_amount) as total'])
                        ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                        ->andWhere(['<>', 'accounting_expense.amount_type', 'Cash'])
                        ->andWhere(['like', 'accounting_expense.datetime', date("Y-m-d")])
                        ->andWhere(['accounting_expense.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                        ->asArray()
                        ->all();

        if ($model->load(Yii::$app->request->post())) {
            $postData = Yii::$app->request->post();
            if($model->save())
            {   
                $selectedSeason = Season::findOne($postData['Expense']['season_id']);
               if($selectedSeason)
               {
                    $expenseModel->expense_type_id = 2; 
                    $expenseModel->branch_id = Yii::$app->user->identity->userinfo->BRANCH_C;  
                    $expenseModel->program_id = $selectedSeason->branchProgram->program_id;  
                    $expenseModel->season_id = $selectedSeason->id;  
                    $expenseModel->expense_id = $model->id; 
                    $expenseModel->amount_type = $postData['Expense']['amount_type'];
                    $expenseModel->transaction_number = $postData['Expense']['transaction_number'];
                    if($dateRestriction){
                        if($dateRestriction->allow == "Yes" && $dateRestriction->open_type == "Expenses"){ 
                            if(!isset($postData['Expense']['dateNow']))
                            {
                                if($postData['Expense']['datetime']!=""){
                                    $expenseModel->datetime = $postData['Expense']['datetime'].' '.date("H:i:s");
                                }
                            }else{
                              $expenseModel->datetime = date("Y-m-d H:i:s");
                          }
                        }else{
                            $expenseModel->datetime = date("Y-m-d H:i:s");
                        }
                    }else{
                        $expenseModel->datetime = date("Y-m-d H:i:s");
                    }
                    
                    $expenseModel->save(false);

                    \Yii::$app->getSession()->setFlash('success', 'Photocopy expense has been updated.');
                    return $this->redirect(['index']);
               }
            }
        }
        
        return $this->render('index', [
            'model' => $model,
            'seasons' => $seasons,
            'expenseModel' => $expenseModel,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'dateRestriction' => $dateRestriction,
            'overall' => $overall,
            'overallCash' => $overallCash,
            'overallCheck' => $overallCheck,
            'overallToday' => $overallToday,
            'overallCashToday' => $overallCashToday,
            'overallCheckToday' => $overallCheckToday
        ]);
    }

    /**
     * Deletes an existing PhotocopyExpense model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $expenseModel = Expense::findOne(['expense_id' => $model->id, 'expense_type_id' => '2']);

        if($model->delete())
        {
            $expenseModel->delete();
        }

        \Yii::$app->getSession()->setFlash('success', 'Petty expense has been deleted.');
        return $this->redirect(['index']);
    }

    /**
     * Finds the PhotocopyExpense model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return PhotocopyExpense the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PhotocopyExpense::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
