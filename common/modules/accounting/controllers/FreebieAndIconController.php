<?php

namespace common\modules\accounting\controllers;

use Yii;
use common\modules\accounting\models\AccessProgram;
use common\modules\accounting\models\Income;
use common\modules\accounting\models\FreebieAndIcon;
use common\modules\accounting\models\Season;
use common\modules\accounting\models\ArchiveSeason;
use common\modules\accounting\models\SeasonOr;
use common\modules\accounting\models\SeasonOrList;
use common\modules\accounting\models\IncomeCode;
use common\modules\accounting\models\Student;
use common\modules\accounting\models\DateRestriction;
use common\modules\accounting\models\FreebieAndIconSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\filters\AccessControl;
use yii\helpers\Url;
use kartik\mpdf\Pdf;
/**
 * FreebieAndIconController implements the CRUD actions for FreebieAndIcon model.
 */
class FreebieAndIconController extends Controller
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
                        'roles' => ['manageFreebieAndIcon'],
                    ],
                    [
                        'actions' => ['update'],
                        'allow' => true,
                        'roles' => ['updateFreebieAndIcon'],
                    ],
                    [
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['deleteFreebieAndIcon'],
                    ],
                ],
            ],
        ];
    }

    function takeOr($id)
    {
        $orList = SeasonOrList::find()
                ->select([
                    'accounting_season_or_list.id as id',
                    'IF(count(accounting_season_or.id)<accounting_season_or_list.no_of_pieces,"Yes","No") as availability'
                ])
                ->leftJoin('accounting_season_or','accounting_season_or.season_or_list_id = accounting_season_or_list.id')
                ->where(['accounting_season_or_list.season_id' => $id])
                ->orderBy(['accounting_season_or_list.id' => SORT_ASC])
                ->groupBy(['accounting_season_or_list.id'])
                ->having(['availability' => 'Yes'])
                ->asArray()
                ->one();

        if(!empty($orList))
        {
            $or = SeasonOr::find()->where(['season_or_list_id' => $orList['id']])->orderBy(['id' => SORT_DESC])->one();
            $orCount = SeasonOr::find()->where(['season_or_list_id' => $orList['id']])->count();
            $season = SeasonOrList::findOne($orList['id']);

            if($orCount < $season->no_of_pieces)
            {
                if($or)
                {
                    $no_of_places = strlen($or->or_no);
                    $current_or = intval($or->or_no);
                    $current_or = str_pad($current_or + 1, $no_of_places, 0, STR_PAD_LEFT);

                    return $current_or;
                }else{
                    $season = SeasonOrList::findOne($orList['id']);

                    return $season->or_start;
                }
            }else{
                return 'No Available OR';
            }
        }else{
            return 'No Available OR';
        }
    } 

    function takeOrList($id)
    {
        $orList = SeasonOrList::find()
                ->select([
                    'accounting_season_or_list.id as id',
                    'IF(count(accounting_season_or.id)<accounting_season_or_list.no_of_pieces,"Yes","No") as availability'
                ])
                ->leftJoin('accounting_season_or','accounting_season_or.season_or_list_id = accounting_season_or_list.id')
                ->where(['accounting_season_or_list.season_id' => $id])
                ->orderBy(['accounting_season_or_list.id' => SORT_ASC])
                ->groupBy(['accounting_season_or_list.id'])
                ->having(['availability' => 'Yes'])
                ->asArray()
                ->one();

        return !empty($orList) ? $orList['id'] : '';
    }

    function takeOrStatus($id)
    {
        $orList = SeasonOrList::find()
                ->select([
                    'accounting_season_or_list.id as id',
                    'IF(count(accounting_season_or.id)<accounting_season_or_list.no_of_pieces,"Yes","No") as availability'
                ])
                ->leftJoin('accounting_season_or','accounting_season_or.season_or_list_id = accounting_season_or_list.id')
                ->where(['accounting_season_or_list.season_id' => $id])
                ->orderBy(['accounting_season_or_list.id' => SORT_ASC])
                ->groupBy(['accounting_season_or_list.id'])
                ->having(['availability' => 'Yes'])
                ->asArray()
                ->one();

        if(!empty($orList))
        {
            $or = SeasonOr::find()->where(['season_or_list_id' => $orList['id']])->orderBy(['id' => SORT_DESC])->one();
            $orCount = SeasonOr::find()->where(['season_or_list_id' => $orList['id']])->count();
            $season = SeasonOrList::findOne($orList['id']);

            if($orCount < $season->no_of_pieces)
            {
                return 1;
            }else{
                return 0;
            }
        }else{
            return 0;
        }
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
        $model = new FreebieAndIcon();
        $model->scenario = 'searchFreebieAndIcon';
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
            $postData = Yii::$app->request->post()['FreebieAndIcon'];

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
                        $data = $access ? $access->branch_program_id!= '' ? FreebieAndIcon::find()
                                ->select([
                                    'accounting_student.id_number',
                                    'accounting_student.last_name',
                                    'accounting_student.extension_name',
                                    'accounting_student.first_name',
                                    'accounting_student.middle_name',
                                    'accounting_income_freebies_and_icons.pr',
                                    'accounting_income_freebies_and_icons.ar_no',
                                    'concat(accounting_income_code.name," - ",accounting_income_code.description) as codeName',
                                    'accounting_income_freebies_and_icons.amount',
                                    'accounting_income.amount_type',
                                    'accounting_income.transaction_number',
                                    'accounting_income.datetime',
                                ])
                                ->leftJoin('accounting_income', 'accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                                ->leftJoin('accounting_season','accounting_season.id = accounting_income_freebies_and_icons.season_id')
                                ->leftJoin('accounting_student','accounting_student.id = accounting_income_freebies_and_icons.student_id')
                                ->leftJoin('accounting_income_code','accounting_income_code.id = accounting_income_freebies_and_icons.code_id')
                                ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                                ->andWhere(['like','accounting_income.datetime', $dates['year']])
                                ->andWhere(['accounting_season.id' => $season->id])
                                ->orderBy(['accounting_income.datetime' => SORT_DESC])
                                ->limit($limit)
                                ->offset($postData['page_id'])
                                ->asArray()
                                ->all() : FreebieAndIcon::find()
                                ->select([
                                    'accounting_student.id_number',
                                    'accounting_student.last_name',
                                    'accounting_student.extension_name',
                                    'accounting_student.first_name',
                                    'accounting_student.middle_name',
                                    'accounting_income_freebies_and_icons.pr',
                                    'accounting_income_freebies_and_icons.ar_no',
                                    'concat(accounting_income_code.name," - ",accounting_income_code.description) as codeName',
                                    'accounting_income_freebies_and_icons.amount',
                                    'accounting_income.amount_type',
                                    'accounting_income.transaction_number',
                                    'accounting_income.datetime',
                                ])
                                ->leftJoin('accounting_income', 'accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                                ->leftJoin('accounting_season','accounting_season.id = accounting_income_freebies_and_icons.season_id')
                                ->leftJoin('accounting_student','accounting_student.id = accounting_income_freebies_and_icons.student_id')
                                ->leftJoin('accounting_income_code','accounting_income_code.id = accounting_income_freebies_and_icons.code_id')
                                ->andWhere(['like','accounting_income.datetime', $dates['year']])
                                ->andWhere(['accounting_season.id' => $season->id])
                                ->orderBy(['accounting_income.datetime' => SORT_DESC])
                                ->limit($limit)
                                ->offset($postData['page_id'])
                                ->asArray()
                                ->all() : FreebieAndIcon::find()
                                ->select([
                                    'accounting_student.id_number',
                                    'accounting_student.last_name',
                                    'accounting_student.extension_name',
                                    'accounting_student.first_name',
                                    'accounting_student.middle_name',
                                    'accounting_income_freebies_and_icons.pr',
                                    'accounting_income_freebies_and_icons.ar_no',
                                    'concat(accounting_income_code.name," - ",accounting_income_code.description) as codeName',
                                    'accounting_income_freebies_and_icons.amount',
                                    'accounting_income.amount_type',
                                    'accounting_income.transaction_number',
                                    'accounting_income.datetime',
                                ])
                                ->leftJoin('accounting_income', 'accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                                ->leftJoin('accounting_season','accounting_season.id = accounting_income_freebies_and_icons.season_id')
                                ->leftJoin('accounting_student','accounting_student.id = accounting_income_freebies_and_icons.student_id')
                                ->leftJoin('accounting_income_code','accounting_income_code.id = accounting_income_freebies_and_icons.code_id')
                                ->andWhere(['like','accounting_income.datetime', $dates['year']])
                                ->andWhere(['accounting_season.id' => $season->id])
                                ->orderBy(['accounting_income.datetime' => SORT_DESC])
                                ->limit($limit)
                                ->offset($postData['page_id'])
                                ->asArray()
                                ->all();
                    }else if($postData['frequency_id'] == 'Monthly')
                    {
                        $data = $access ? $access->branch_program_id!= '' ? FreebieAndIcon::find()
                                ->select([
                                    'accounting_student.id_number',
                                    'accounting_student.last_name',
                                    'accounting_student.extension_name',
                                    'accounting_student.first_name',
                                    'accounting_student.middle_name',
                                    'accounting_income_freebies_and_icons.pr',
                                    'accounting_income_freebies_and_icons.ar_no',
                                    'concat(accounting_income_code.name," - ",accounting_income_code.description) as codeName',
                                    'accounting_income_freebies_and_icons.amount',
                                    'accounting_income.amount_type',
                                    'accounting_income.transaction_number',
                                    'accounting_income.datetime',
                                ])
                                ->leftJoin('accounting_income', 'accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                                ->leftJoin('accounting_season','accounting_season.id = accounting_income_freebies_and_icons.season_id')
                                ->leftJoin('accounting_student','accounting_student.id = accounting_income_freebies_and_icons.student_id')
                                ->leftJoin('accounting_income_code','accounting_income_code.id = accounting_income_freebies_and_icons.code_id')
                                ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                                ->andWhere(['like', 'accounting_income.datetime', $dates['year'].'-'.$dates['month']])
                                ->andWhere(['accounting_season.id' => $season->id])
                                ->orderBy(['accounting_income.datetime' => SORT_DESC])
                                ->limit($limit)
                                ->offset($postData['page_id'])
                                ->asArray()
                                ->all() : FreebieAndIcon::find()
                                ->select([
                                    'accounting_student.id_number',
                                    'accounting_student.last_name',
                                    'accounting_student.extension_name',
                                    'accounting_student.first_name',
                                    'accounting_student.middle_name',
                                    'accounting_income_freebies_and_icons.pr',
                                    'accounting_income_freebies_and_icons.ar_no',
                                    'concat(accounting_income_code.name," - ",accounting_income_code.description) as codeName',
                                    'accounting_income_freebies_and_icons.amount',
                                    'accounting_income.amount_type',
                                    'accounting_income.transaction_number',
                                    'accounting_income.datetime',
                                ])
                                ->leftJoin('accounting_income', 'accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                                ->leftJoin('accounting_season','accounting_season.id = accounting_income_freebies_and_icons.season_id')
                                ->leftJoin('accounting_student','accounting_student.id = accounting_income_freebies_and_icons.student_id')
                                ->leftJoin('accounting_income_code','accounting_income_code.id = accounting_income_freebies_and_icons.code_id')
                                ->andWhere(['like', 'accounting_income.datetime', $dates['year'].'-'.$dates['month']])
                                ->andWhere(['accounting_season.id' => $season->id])
                                ->orderBy(['accounting_income.datetime' => SORT_DESC])
                                ->limit($limit)
                                ->offset($postData['page_id'])
                                ->asArray()
                                ->all() : FreebieAndIcon::find()
                                ->select([
                                    'accounting_student.id_number',
                                    'accounting_student.last_name',
                                    'accounting_student.extension_name',
                                    'accounting_student.first_name',
                                    'accounting_student.middle_name',
                                    'accounting_income_freebies_and_icons.pr',
                                    'accounting_income_freebies_and_icons.ar_no',
                                    'concat(accounting_income_code.name," - ",accounting_income_code.description) as codeName',
                                    'accounting_income_freebies_and_icons.amount',
                                    'accounting_income.amount_type',
                                    'accounting_income.transaction_number',
                                    'accounting_income.datetime',
                                ])
                                ->leftJoin('accounting_income', 'accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                                ->leftJoin('accounting_season','accounting_season.id = accounting_income_freebies_and_icons.season_id')
                                ->leftJoin('accounting_student','accounting_student.id = accounting_income_freebies_and_icons.student_id')
                                ->leftJoin('accounting_income_code','accounting_income_code.id = accounting_income_freebies_and_icons.code_id')
                                ->andWhere(['like', 'accounting_income.datetime', $dates['year'].'-'.$dates['month']])
                                ->andWhere(['accounting_season.id' => $season->id])
                                ->orderBy(['accounting_income.datetime' => SORT_DESC])
                                ->limit($limit)
                                ->offset($postData['page_id'])
                                ->asArray()
                                ->all();
                    }else if($postData['frequency_id'] == 'Cut Off')
                    {
                        $data = $access ? $access->branch_program_id!= '' ? FreebieAndIcon::find()
                                ->select([
                                    'accounting_student.id_number',
                                    'accounting_student.last_name',
                                    'accounting_student.extension_name',
                                    'accounting_student.first_name',
                                    'accounting_student.middle_name',
                                    'accounting_income_freebies_and_icons.pr',
                                    'accounting_income_freebies_and_icons.ar_no',
                                    'concat(accounting_income_code.name," - ",accounting_income_code.description) as codeName',
                                    'accounting_income_freebies_and_icons.amount',
                                    'accounting_income.amount_type',
                                    'accounting_income.transaction_number',
                                    'accounting_income.datetime',
                                ])
                                ->leftJoin('accounting_income', 'accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                                ->leftJoin('accounting_season','accounting_season.id = accounting_income_freebies_and_icons.season_id')
                                ->leftJoin('accounting_student','accounting_student.id = accounting_income_freebies_and_icons.student_id')
                                ->leftJoin('accounting_income_code','accounting_income_code.id = accounting_income_freebies_and_icons.code_id')
                                ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                                ->andWhere(['between', 'accounting_income.datetime', $dates['start'].' 00:00:00', $dates['end'].' 23:59:59'])
                                ->andWhere(['accounting_season.id' => $season->id])
                                ->orderBy(['accounting_income.datetime' => SORT_DESC])
                                ->limit($limit)
                                ->offset($postData['page_id'])
                                ->asArray()
                                ->all() : FreebieAndIcon::find()
                                ->select([
                                    'accounting_student.id_number',
                                    'accounting_student.last_name',
                                    'accounting_student.extension_name',
                                    'accounting_student.first_name',
                                    'accounting_student.middle_name',
                                    'accounting_income_freebies_and_icons.pr',
                                    'accounting_income_freebies_and_icons.ar_no',
                                    'concat(accounting_income_code.name," - ",accounting_income_code.description) as codeName',
                                    'accounting_income_freebies_and_icons.amount',
                                    'accounting_income.amount_type',
                                    'accounting_income.transaction_number',
                                    'accounting_income.datetime',
                                ])
                                ->leftJoin('accounting_income', 'accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                                ->leftJoin('accounting_season','accounting_season.id = accounting_income_freebies_and_icons.season_id')
                                ->leftJoin('accounting_student','accounting_student.id = accounting_income_freebies_and_icons.student_id')
                                ->leftJoin('accounting_income_code','accounting_income_code.id = accounting_income_freebies_and_icons.code_id')
                                ->andWhere(['between', 'accounting_income.datetime', $dates['start'].' 00:00:00', $dates['end'].' 23:59:59'])
                                ->andWhere(['accounting_season.id' => $season->id])
                                ->orderBy(['accounting_income.datetime' => SORT_DESC])
                                ->limit($limit)
                                ->offset($postData['page_id'])
                                ->asArray()
                                ->all() : FreebieAndIcon::find()
                                ->select([
                                    'accounting_student.id_number',
                                    'accounting_student.last_name',
                                    'accounting_student.extension_name',
                                    'accounting_student.first_name',
                                    'accounting_student.middle_name',
                                    'accounting_income_freebies_and_icons.pr',
                                    'accounting_income_freebies_and_icons.ar_no',
                                    'concat(accounting_income_code.name," - ",accounting_income_code.description) as codeName',
                                    'accounting_income_freebies_and_icons.amount',
                                    'accounting_income.amount_type',
                                    'accounting_income.transaction_number',
                                    'accounting_income.datetime',
                                ])
                                ->leftJoin('accounting_income', 'accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                                ->leftJoin('accounting_season','accounting_season.id = accounting_income_freebies_and_icons.season_id')
                                ->leftJoin('accounting_student','accounting_student.id = accounting_income_freebies_and_icons.student_id')
                                ->leftJoin('accounting_income_code','accounting_income_code.id = accounting_income_freebies_and_icons.code_id')
                                ->andWhere(['between', 'accounting_income.datetime', $dates['start'].' 00:00:00', $dates['end'].' 23:59:59'])
                                ->andWhere(['accounting_season.id' => $season->id])
                                ->orderBy(['accounting_income.datetime' => SORT_DESC])
                                ->limit($limit)
                                ->offset($postData['page_id'])
                                ->asArray()
                                ->all();
                    }else if($postData['frequency_id'] == 'Daily')
                    {
                        $data = $access ? $access->branch_program_id!= '' ? FreebieAndIcon::find()
                                ->select([
                                    'accounting_student.id_number',
                                    'accounting_student.last_name',
                                    'accounting_student.extension_name',
                                    'accounting_student.first_name',
                                    'accounting_student.middle_name',
                                    'accounting_income_freebies_and_icons.pr',
                                    'accounting_income_freebies_and_icons.ar_no',
                                    'concat(accounting_income_code.name," - ",accounting_income_code.description) as codeName',
                                    'accounting_income_freebies_and_icons.amount',
                                    'accounting_income.amount_type',
                                    'accounting_income.transaction_number',
                                    'accounting_income.datetime',
                                ])
                                ->leftJoin('accounting_income', 'accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                                ->leftJoin('accounting_season','accounting_season.id = accounting_income_freebies_and_icons.season_id')
                                ->leftJoin('accounting_student','accounting_student.id = accounting_income_freebies_and_icons.student_id')
                                ->leftJoin('accounting_income_code','accounting_income_code.id = accounting_income_freebies_and_icons.code_id')
                                ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                                ->andWhere(['like','accounting_income.datetime', $dates['year'].'-'.$dates['month'].'-'.$dates['day']])
                                ->andWhere(['accounting_season.id' => $season->id])
                                ->orderBy(['accounting_income.datetime' => SORT_DESC])
                                ->limit($limit)
                                ->offset($postData['page_id'])
                                ->asArray()
                                ->all() : FreebieAndIcon::find()
                                ->select([
                                    'accounting_student.id_number',
                                    'accounting_student.last_name',
                                    'accounting_student.extension_name',
                                    'accounting_student.first_name',
                                    'accounting_student.middle_name',
                                    'accounting_income_freebies_and_icons.pr',
                                    'accounting_income_freebies_and_icons.ar_no',
                                    'concat(accounting_income_code.name," - ",accounting_income_code.description) as codeName',
                                    'accounting_income_freebies_and_icons.amount',
                                    'accounting_income.amount_type',
                                    'accounting_income.transaction_number',
                                    'accounting_income.datetime',
                                ])
                                ->leftJoin('accounting_income', 'accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                                ->leftJoin('accounting_season','accounting_season.id = accounting_income_freebies_and_icons.season_id')
                                ->leftJoin('accounting_student','accounting_student.id = accounting_income_freebies_and_icons.student_id')
                                ->leftJoin('accounting_income_code','accounting_income_code.id = accounting_income_freebies_and_icons.code_id')
                                ->andWhere(['like','accounting_income.datetime', $dates['year'].'-'.$dates['month'].'-'.$dates['day']])
                                ->andWhere(['accounting_season.id' => $season->id])
                                ->orderBy(['accounting_income.datetime' => SORT_DESC])
                                ->limit($limit)
                                ->offset($postData['page_id'])
                                ->asArray()
                                ->all() : FreebieAndIcon::find()
                                ->select([
                                    'accounting_student.id_number',
                                    'accounting_student.last_name',
                                    'accounting_student.extension_name',
                                    'accounting_student.first_name',
                                    'accounting_student.middle_name',
                                    'accounting_income_freebies_and_icons.pr',
                                    'accounting_income_freebies_and_icons.ar_no',
                                    'concat(accounting_income_code.name," - ",accounting_income_code.description) as codeName',
                                    'accounting_income_freebies_and_icons.amount',
                                    'accounting_income.amount_type',
                                    'accounting_income.transaction_number',
                                    'accounting_income.datetime',
                                ])
                                ->leftJoin('accounting_income', 'accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                                ->leftJoin('accounting_season','accounting_season.id = accounting_income_freebies_and_icons.season_id')
                                ->leftJoin('accounting_student','accounting_student.id = accounting_income_freebies_and_icons.student_id')
                                ->leftJoin('accounting_income_code','accounting_income_code.id = accounting_income_freebies_and_icons.code_id')
                                ->andWhere(['like','accounting_income.datetime', $dates['year'].'-'.$dates['month'].'-'.$dates['day']])
                                ->andWhere(['accounting_season.id' => $season->id])
                                ->orderBy(['accounting_income.datetime' => SORT_DESC])
                                ->limit($limit)
                                ->offset($postData['page_id'])
                                ->asArray()
                                ->all();
                    }

                    $pages = ceil(count($data) / $limit);

                    $content = $this->renderPartial('_freebies-and-icons-report', [
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
                    'filename' => $postData['frequency_id'].' Report: Freebies and Icons Income - '.$season->seasonName.' - Page '.($postData['page_id']+1).' of '.$pages.'.pdf',
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
                    'options' => ['title' => 'Freebies And Icons Income'],
                    'methods' => [ 
                        'SetHeader'=>[$postData['frequency_id'].' Report: Freebies and Icons Income - '.$season->seasonName.' - Page '.($postData['page_id']+1).' of '.$pages], 
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

        return $this->renderAjax('_search-freebies-and-icons',[
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
                    $count = $access ? $access->branch_program_id!= '' ? FreebieAndIcon::find()
                            ->leftJoin('accounting_income', 'accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                            ->leftJoin('accounting_season','accounting_season.id = accounting_income_freebies_and_icons.season_id')
                            ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                            ->andWhere(['like','accounting_income.datetime', $dates['year']])
                            ->andWhere(['accounting_season.id' => $season->id])
                            ->asArray()
                            ->count() : FreebieAndIcon::find()
                            ->leftJoin('accounting_income', 'accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                            ->leftJoin('accounting_season','accounting_season.id = accounting_income_freebies_and_icons.season_id')
                            ->andWhere(['like','accounting_income.datetime', $dates['year']])
                            ->andWhere(['accounting_season.id' => $season->id])
                            ->asArray()
                            ->count() : FreebieAndIcon::find()
                            ->leftJoin('accounting_income', 'accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                            ->leftJoin('accounting_season','accounting_season.id = accounting_income_freebies_and_icons.season_id')
                            ->andWhere(['like','accounting_income.datetime', $dates['year']])
                            ->andWhere(['accounting_season.id' => $season->id])
                            ->asArray()
                            ->count();
                }else if($frequency_id == 'Monthly')
                {
                    $count = $access ? $access->branch_program_id!= '' ? FreebieAndIcon::find()
                            ->leftJoin('accounting_income', 'accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                            ->leftJoin('accounting_season','accounting_season.id = accounting_income_freebies_and_icons.season_id')
                            ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                            ->andWhere(['like', 'accounting_income.datetime', $dates['year'].'-'.$dates['month']])
                            ->andWhere(['accounting_season.id' => $season->id])
                            ->asArray()
                            ->count() : FreebieAndIcon::find()
                            ->leftJoin('accounting_income', 'accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                            ->leftJoin('accounting_season','accounting_season.id = accounting_income_freebies_and_icons.season_id')
                            ->andWhere(['like', 'accounting_income.datetime', $dates['year'].'-'.$dates['month']])
                            ->andWhere(['accounting_season.id' => $season->id])
                            ->asArray()
                            ->count() : FreebieAndIcon::find()
                            ->leftJoin('accounting_income', 'accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                            ->leftJoin('accounting_season','accounting_season.id = accounting_income_freebies_and_icons.season_id')
                            ->andWhere(['like', 'accounting_income.datetime', $dates['year'].'-'.$dates['month']])
                            ->andWhere(['accounting_season.id' => $season->id])
                            ->asArray()
                            ->count();
                }else if($frequency_id == 'Cut Off')
                {
                    $count = $access ? $access->branch_program_id!= '' ? FreebieAndIcon::find()
                            ->leftJoin('accounting_income', 'accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                            ->leftJoin('accounting_season','accounting_season.id = accounting_income_freebies_and_icons.season_id')
                            ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                            ->andWhere(['between', 'accounting_income.datetime', $dates['start'].' 00:00:00', $dates['end'].' 23:59:59'])
                            ->andWhere(['accounting_season.id' => $season->id])
                            ->asArray()
                            ->count() : FreebieAndIcon::find()
                            ->leftJoin('accounting_income', 'accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                            ->leftJoin('accounting_season','accounting_season.id = accounting_income_freebies_and_icons.season_id')
                            ->andWhere(['between', 'accounting_income.datetime', $dates['start'].' 00:00:00', $dates['end'].' 23:59:59'])
                            ->andWhere(['accounting_season.id' => $season->id])
                            ->asArray()
                            ->count() : FreebieAndIcon::find()
                            ->leftJoin('accounting_income', 'accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                            ->leftJoin('accounting_season','accounting_season.id = accounting_income_freebies_and_icons.season_id')
                            ->andWhere(['between', 'accounting_income.datetime', $dates['start'].' 00:00:00', $dates['end'].' 23:59:59'])
                            ->andWhere(['accounting_season.id' => $season->id])
                            ->asArray()
                            ->count();
                }else if($frequency_id == 'Daily')
                {
                    $count = $access ? $access->branch_program_id!= '' ? FreebieAndIcon::find()
                            ->leftJoin('accounting_income', 'accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                            ->leftJoin('accounting_season','accounting_season.id = accounting_income_freebies_and_icons.season_id')
                            ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                            ->andWhere(['like','accounting_income.datetime', $dates['year'].'-'.$dates['month'].'-'.$dates['day']])
                            ->andWhere(['accounting_season.id' => $season->id])
                            ->asArray()
                            ->count() : FreebieAndIcon::find()
                            ->leftJoin('accounting_income', 'accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                            ->leftJoin('accounting_season','accounting_season.id = accounting_income_freebies_and_icons.season_id')
                            ->andWhere(['like','accounting_income.datetime', $dates['year'].'-'.$dates['month'].'-'.$dates['day']])
                            ->andWhere(['accounting_season.id' => $season->id])
                            ->asArray()
                            ->count() : FreebieAndIcon::find()
                            ->leftJoin('accounting_income', 'accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                            ->leftJoin('accounting_season','accounting_season.id = accounting_income_freebies_and_icons.season_id')
                            ->andWhere(['like','accounting_income.datetime', $dates['year'].'-'.$dates['month'].'-'.$dates['day']])
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

    public function actionStudentList($q = null, $id = null) {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $user_info = Yii::$app->user->identity->userinfo;
        $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
        $rolenames =  ArrayHelper::map($roles, 'name','name');

        $access = AccessProgram::findOne(['user_id' => Yii::$app->user->identity->userinfo->user_id]);

        $out = ['results' => ['id' => '', 'name' => '']];
        if (!is_null($q)) {
            if(in_array('TopManagement',$rolenames)){
                $names = $access ? $access->branch_program_id!= '' ? Student::find()
                    ->select(['accounting_student.id', 'concat(id_number," - ",first_name," ",middle_name," ",last_name," ",extension_name) as name'])
                    ->leftJoin('accounting_student_branch_program','accounting_student_branch_program.student_id = accounting_student.id')
                    ->leftJoin('accounting_branch_program','accounting_student_branch_program.branch_id = accounting_branch_program.branch_id and accounting_student_branch_program.program_id = accounting_branch_program.program_id')
                    ->where(['like','id_number', $q])
                    ->orWhere(['like','first_name', $q])
                    ->orWhere(['like','middle_name', $q])
                    ->orWhere(['like','last_name', $q])
                    ->orWhere(['like','extension_name', $q])
                    ->andWhere(['status' => 'Active'])
                    ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                    ->limit(20)
                    ->asArray()
                    ->all() : Student::find()
                    ->select(['accounting_student.id', 'concat(id_number," - ",first_name," ",middle_name," ",last_name," ",extension_name) as name'])
                    ->leftJoin('accounting_student_branch_program','accounting_student_branch_program.student_id = accounting_student.id')
                    ->leftJoin('accounting_branch_program','accounting_student_branch_program.branch_id = accounting_branch_program.branch_id and accounting_student_branch_program.program_id = accounting_branch_program.program_id')
                    ->where(['like','id_number', $q])
                    ->orWhere(['like','first_name', $q])
                    ->orWhere(['like','middle_name', $q])
                    ->orWhere(['like','last_name', $q])
                    ->orWhere(['like','extension_name', $q])
                    ->andWhere(['status' => 'Active'])
                    ->limit(20)
                    ->asArray()
                    ->all() : Student::find()
                    ->select(['accounting_student.id', 'concat(id_number," - ",first_name," ",middle_name," ",last_name," ",extension_name) as name'])
                    ->leftJoin('accounting_student_branch_program','accounting_student_branch_program.student_id = accounting_student.id')
                    ->leftJoin('accounting_branch_program','accounting_student_branch_program.branch_id = accounting_branch_program.branch_id and accounting_student_branch_program.program_id = accounting_branch_program.program_id')
                    ->where(['like','id_number', $q])
                    ->orWhere(['like','first_name', $q])
                    ->orWhere(['like','middle_name', $q])
                    ->orWhere(['like','last_name', $q])
                    ->orWhere(['like','extension_name', $q])
                    ->andWhere(['status' => 'Active'])
                    ->limit(20)
                    ->asArray()
                    ->all();
            }else{
                $names = $access ? $access->branch_program_id!= '' ? Student::find()
                    ->select(['accounting_student.id', 'concat(id_number," - ",first_name," ",middle_name," ",last_name," ",extension_name) as name'])
                    ->leftJoin('accounting_student_branch_program','accounting_student_branch_program.student_id = accounting_student.id')
                    ->leftJoin('accounting_branch_program','accounting_student_branch_program.branch_id = accounting_branch_program.branch_id and accounting_student_branch_program.program_id = accounting_branch_program.program_id')
                    ->where(['like','id_number', $q])
                    ->orWhere(['like','first_name', $q])
                    ->orWhere(['like','middle_name', $q])
                    ->orWhere(['like','last_name', $q])
                    ->orWhere(['like','extension_name', $q])
                    ->andWhere(['status' => 'Active'])
                    ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                    ->andWhere(['accounting_student_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                    ->limit(20)
                    ->asArray()
                    ->all() : Student::find()
                    ->select(['accounting_student.id', 'concat(id_number," - ",first_name," ",middle_name," ",last_name," ",extension_name) as name'])
                    ->leftJoin('accounting_student_branch_program','accounting_student_branch_program.student_id = accounting_student.id')
                    ->leftJoin('accounting_branch_program','accounting_student_branch_program.branch_id = accounting_branch_program.branch_id and accounting_student_branch_program.program_id = accounting_branch_program.program_id')
                    ->where(['like','id_number', $q])
                    ->orWhere(['like','first_name', $q])
                    ->orWhere(['like','middle_name', $q])
                    ->orWhere(['like','last_name', $q])
                    ->orWhere(['like','extension_name', $q])
                    ->andWhere(['status' => 'Active'])
                    ->andWhere(['accounting_student_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                    ->limit(20)
                    ->asArray()
                    ->all() : Student::find()
                    ->select(['accounting_student.id', 'concat(id_number," - ",first_name," ",middle_name," ",last_name," ",extension_name) as name'])
                    ->leftJoin('accounting_student_branch_program','accounting_student_branch_program.student_id = accounting_student.id')
                    ->leftJoin('accounting_branch_program','accounting_student_branch_program.branch_id = accounting_branch_program.branch_id and accounting_student_branch_program.program_id = accounting_branch_program.program_id')
                    ->where(['like','id_number', $q])
                    ->orWhere(['like','first_name', $q])
                    ->orWhere(['like','middle_name', $q])
                    ->orWhere(['like','last_name', $q])
                    ->orWhere(['like','extension_name', $q])
                    ->andWhere(['status' => 'Active'])
                    ->andWhere(['accounting_student_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                    ->limit(20)
                    ->asArray()
                    ->all();
            }
            $out['results'] = array_values($names);
        }
        elseif ($id > 0) {
            $out['results'] = ['id' => $id, 'name' => Student::find($id)->first_name.' '.Student::find($id)->middle_name.' '.Student::find($id)->last_name];
        }
        return $out;
    }

    /**
     * Lists all FreebieAndIcon models.
     * @return mixed
     */
    public function actionIndex()
    {
        $model = new FreebieAndIcon();
        $incomeModel = new Income();
        $searchModel = new FreebieAndIconSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $user_info = Yii::$app->user->identity->userinfo;
        $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
        $rolenames =  ArrayHelper::map($roles, 'name','name');

        $access = AccessProgram::findOne(['user_id' => Yii::$app->user->identity->userinfo->user_id]);

        if(in_array('TopManagement',$rolenames)){
            $overall = $access ? $access->branch_program_id!= '' ? FreebieAndIcon::find()
                    ->select(['sum(amount) as total'])
                    ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                    ->leftJoin('accounting_season', 'accounting_season.id = accounting_income_freebies_and_icons.season_id')
                    ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                    ->asArray()
                    ->all() : FreebieAndIcon::find()
                    ->select(['sum(amount) as total'])
                    ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                    ->asArray()
                    ->all() : FreebieAndIcon::find()
                    ->select(['sum(amount) as total'])
                    ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                    ->asArray()
                    ->all();

            $overallCash = $access ? $access->branch_program_id!= '' ? FreebieAndIcon::find()
                        ->select(['sum(amount) as total'])
                        ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                        ->leftJoin('accounting_season', 'accounting_season.id = accounting_income_freebies_and_icons.season_id')
                        ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                        ->andWhere(['accounting_income.amount_type' => 'Cash'])
                        ->asArray()
                        ->all() : FreebieAndIcon::find()
                        ->select(['sum(amount) as total'])
                        ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                        ->where(['accounting_income.amount_type' => 'Cash'])
                        ->asArray()
                        ->all() : FreebieAndIcon::find()
                        ->select(['sum(amount) as total'])
                        ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                        ->where(['accounting_income.amount_type' => 'Cash'])
                        ->asArray()
                        ->all();

            $overallCheck = $access ? $access->branch_program_id!= '' ? FreebieAndIcon::find()
                        ->select(['sum(amount) as total'])
                        ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                        ->leftJoin('accounting_season', 'accounting_season.id = accounting_income_freebies_and_icons.season_id')
                        ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                        ->andWhere(['<>', 'accounting_income.amount_type', 'Cash'])
                        ->asArray()
                        ->all() : FreebieAndIcon::find()
                        ->select(['sum(amount) as total'])
                        ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                        ->andWhere(['<>', 'accounting_income.amount_type', 'Cash'])
                        ->asArray()
                        ->all() : FreebieAndIcon::find()
                        ->select(['sum(amount) as total'])
                        ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                        ->andWhere(['<>', 'accounting_income.amount_type', 'Cash'])
                        ->asArray()
                        ->all();

            $overallToday = $access ? $access->branch_program_id!= '' ? FreebieAndIcon::find()
                        ->select(['sum(amount) as total'])
                        ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                        ->leftJoin('accounting_season', 'accounting_season.id = accounting_income_freebies_and_icons.season_id')
                        ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                        ->andWhere(['like', 'accounting_income.datetime', date("Y-m-d")])
                        ->asArray()
                        ->all() : FreebieAndIcon::find()
                        ->select(['sum(amount) as total'])
                        ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                        ->andWhere(['like', 'accounting_income.datetime', date("Y-m-d")])
                        ->asArray()
                        ->all() : FreebieAndIcon::find()
                        ->select(['sum(amount) as total'])
                        ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                        ->andWhere(['like', 'accounting_income.datetime', date("Y-m-d")])
                        ->asArray()
                        ->all();

            $overallCashToday = $access ? $access->branch_program_id!= '' ? FreebieAndIcon::find()
                        ->select(['sum(amount) as total'])
                        ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                        ->leftJoin('accounting_season', 'accounting_season.id = accounting_income_freebies_and_icons.season_id')
                        ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                        ->andWhere(['accounting_income.amount_type' => 'Cash'])
                        ->andWhere(['like', 'accounting_income.datetime', date("Y-m-d")])
                        ->asArray()
                        ->all() : FreebieAndIcon::find()
                        ->select(['sum(amount) as total'])
                        ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                        ->where(['accounting_income.amount_type' => 'Cash'])
                        ->andWhere(['like', 'accounting_income.datetime', date("Y-m-d")])
                        ->asArray()
                        ->all() : FreebieAndIcon::find()
                        ->select(['sum(amount) as total'])
                        ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                        ->where(['accounting_income.amount_type' => 'Cash'])
                        ->andWhere(['like', 'accounting_income.datetime', date("Y-m-d")])
                        ->asArray()
                        ->all();

            $overallCheckToday = $access ? $access->branch_program_id!= '' ? FreebieAndIcon::find()
                        ->select(['sum(amount) as total'])
                        ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                        ->leftJoin('accounting_season', 'accounting_season.id = accounting_income_freebies_and_icons.season_id')
                        ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                        ->andWhere(['<>', 'accounting_income.amount_type', 'Cash'])
                        ->andWhere(['like', 'accounting_income.datetime', date("Y-m-d")])
                        ->asArray()
                        ->all() : FreebieAndIcon::find()
                        ->select(['sum(amount) as total'])
                        ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                        ->andWhere(['<>', 'accounting_income.amount_type', 'Cash'])
                        ->andWhere(['like', 'accounting_income.datetime', date("Y-m-d")])
                        ->asArray()
                        ->all() : FreebieAndIcon::find()
                        ->select(['sum(amount) as total'])
                        ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                        ->andWhere(['<>', 'accounting_income.amount_type', 'Cash'])
                        ->andWhere(['like', 'accounting_income.datetime', date("Y-m-d")])
                        ->asArray()
                        ->all();

            return $this->render('index', [
                'model' => $model,
                'incomeModel' => $incomeModel,
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
            $overall = $access ? $access->branch_program_id!= '' ? FreebieAndIcon::find()
                    ->select(['sum(amount) as total'])
                    ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                    ->leftJoin('accounting_season', 'accounting_season.id = accounting_income_freebies_and_icons.season_id')
                    ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                    ->andWhere(['accounting_income.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                    ->asArray()
                    ->all() : FreebieAndIcon::find()
                    ->select(['sum(amount) as total'])
                    ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                    ->andWhere(['accounting_income.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                    ->asArray()
                    ->all() : FreebieAndIcon::find()
                    ->select(['sum(amount) as total'])
                    ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                    ->andWhere(['accounting_income.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                    ->asArray()
                    ->all();

            $overallCash = $access ? $access->branch_program_id!= '' ? FreebieAndIcon::find()
                        ->select(['sum(amount) as total'])
                        ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                        ->leftJoin('accounting_season', 'accounting_season.id = accounting_income_freebies_and_icons.season_id')
                        ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                        ->andWhere(['accounting_income.amount_type' => 'Cash'])
                        ->andWhere(['accounting_income.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                        ->asArray()
                        ->all() : FreebieAndIcon::find()
                        ->select(['sum(amount) as total'])
                        ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                        ->where(['accounting_income.amount_type' => 'Cash'])
                        ->andWhere(['accounting_income.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                        ->asArray()
                        ->all() : FreebieAndIcon::find()
                        ->select(['sum(amount) as total'])
                        ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                        ->where(['accounting_income.amount_type' => 'Cash'])
                        ->andWhere(['accounting_income.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                        ->asArray()
                        ->all();

            $overallCheck = $access ? $access->branch_program_id!= '' ? FreebieAndIcon::find()
                        ->select(['sum(amount) as total'])
                        ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                        ->leftJoin('accounting_season', 'accounting_season.id = accounting_income_freebies_and_icons.season_id')
                        ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                        ->andWhere(['<>', 'accounting_income.amount_type', 'Cash'])
                        ->andWhere(['accounting_income.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                        ->asArray()
                        ->all() : FreebieAndIcon::find()
                        ->select(['sum(amount) as total'])
                        ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                        ->andWhere(['<>', 'accounting_income.amount_type', 'Cash'])
                        ->andWhere(['accounting_income.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                        ->asArray()
                        ->all() : FreebieAndIcon::find()
                        ->select(['sum(amount) as total'])
                        ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                        ->andWhere(['<>', 'accounting_income.amount_type', 'Cash'])
                        ->andWhere(['accounting_income.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                        ->asArray()
                        ->all();

            $overallToday = $access ? $access->branch_program_id!= '' ? FreebieAndIcon::find()
                        ->select(['sum(amount) as total'])
                        ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                        ->leftJoin('accounting_season', 'accounting_season.id = accounting_income_freebies_and_icons.season_id')
                        ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                        ->andWhere(['like', 'accounting_income.datetime', date("Y-m-d")])
                        ->andWhere(['accounting_income.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                        ->asArray()
                        ->all() : FreebieAndIcon::find()
                        ->select(['sum(amount) as total'])
                        ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                        ->andWhere(['like', 'accounting_income.datetime', date("Y-m-d")])
                        ->andWhere(['accounting_income.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                        ->asArray()
                        ->all() : FreebieAndIcon::find()
                        ->select(['sum(amount) as total'])
                        ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                        ->andWhere(['like', 'accounting_income.datetime', date("Y-m-d")])
                        ->andWhere(['accounting_income.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                        ->asArray()
                        ->all();

            $overallCashToday = $access ? $access->branch_program_id!= '' ? FreebieAndIcon::find()
                        ->select(['sum(amount) as total'])
                        ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                        ->leftJoin('accounting_season', 'accounting_season.id = accounting_income_freebies_and_icons.season_id')
                        ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                        ->andWhere(['accounting_income.amount_type' => 'Cash'])
                        ->andWhere(['like', 'accounting_income.datetime', date("Y-m-d")])
                        ->andWhere(['accounting_income.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                        ->asArray()
                        ->all() : FreebieAndIcon::find()
                        ->select(['sum(amount) as total'])
                        ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                        ->where(['accounting_income.amount_type' => 'Cash'])
                        ->andWhere(['like', 'accounting_income.datetime', date("Y-m-d")])
                        ->andWhere(['accounting_income.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                        ->asArray()
                        ->all() : FreebieAndIcon::find()
                        ->select(['sum(amount) as total'])
                        ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                        ->where(['accounting_income.amount_type' => 'Cash'])
                        ->andWhere(['like', 'accounting_income.datetime', date("Y-m-d")])
                        ->andWhere(['accounting_income.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                        ->asArray()
                        ->all();

            $overallCheckToday = $access ? $access->branch_program_id!= '' ? FreebieAndIcon::find()
                        ->select(['sum(amount) as total'])
                        ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                        ->leftJoin('accounting_season', 'accounting_season.id = accounting_income_freebies_and_icons.season_id')
                        ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                        ->andWhere(['<>', 'accounting_income.amount_type', 'Cash'])
                        ->andWhere(['like', 'accounting_income.datetime', date("Y-m-d")])
                        ->andWhere(['accounting_income.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                        ->asArray()
                        ->all() : FreebieAndIcon::find()
                        ->select(['sum(amount) as total'])
                        ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                        ->andWhere(['<>', 'accounting_income.amount_type', 'Cash'])
                        ->andWhere(['like', 'accounting_income.datetime', date("Y-m-d")])
                        ->andWhere(['accounting_income.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                        ->asArray()
                        ->all() : FreebieAndIcon::find()
                        ->select(['sum(amount) as total'])
                        ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                        ->andWhere(['<>', 'accounting_income.amount_type', 'Cash'])
                        ->andWhere(['like', 'accounting_income.datetime', date("Y-m-d")])
                        ->andWhere(['accounting_income.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                        ->asArray()
                        ->all();

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

            $incomeCodes = IncomeCode::find()->select(['id, concat(name," - ",description) as name'])->where(['income_type_id' => '2'])->all();
            $incomeCodes = ArrayHelper::map($incomeCodes, 'id', 'name');

            $dateRestriction = DateRestriction::findOne(['branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C]);

            if ($model->load(Yii::$app->request->post())) {
                $postData = Yii::$app->request->post();
                $model->pr = $this->takeOr($model->season_id);
                if($model->pr == 'No Available OR')
                {
                    \Yii::$app->getSession()->setFlash('danger', ' No Available OR. Please request additional ORs to the management to save payments.');
                }else{
                    if($model->save(false))
                    {
                        $selectedSeason = Season::findOne($model->season_id);
                        if($selectedSeason)
                        {
                            $incomeModel->income_type_id = 2; 
                            $incomeModel->branch_id = $selectedSeason->branchProgram->branch_id; 
                            $incomeModel->program_id = $selectedSeason->branchProgram->program_id; 
                            $incomeModel->income_id = $model->id; 
                            $incomeModel->amount_type = $postData['Income']['amount_type'];
                            $incomeModel->transaction_number = $postData['Income']['transaction_number'];
                            if($dateRestriction){
                                if($dateRestriction->allow == "Yes" && $dateRestriction->open_type == "Income"){ 
                                    if(!isset($postData['Income']['dateNow']))
                                    {
                                        if($postData['Income']['datetime']!=""){
                                            $incomeModel->datetime = $postData['Income']['datetime'].' '.date("H:i:s");
                                        }
                                    }
                                }
                            }
                            $incomeModel->save(false);

                            $or = new SeasonOr();
                            $or->season_id = $model->season_id;
                            $or->season_or_list_id = $this->takeOrList($model->season_id);
                            $or->or_no = $this->takeOr($model->season_id);
                            $or->save();

                            \Yii::$app->getSession()->setFlash('success', 'Payment has been saved.');
                        }
                    }
                }
                
                return $this->redirect(['index']);
            }

            return $this->render('index', [
                'model' => $model,
                'incomeModel' => $incomeModel,
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'seasons' => $seasons,
                'incomeCodes' => $incomeCodes,
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
     * Updates an existing FreebieAndIcon model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $incomeModel = Income::findOne(['income_id' => $model->id, 'income_type_id' => '2']);
        $dateRestriction = DateRestriction::findOne(['branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C]);

        $incomeModel->datetime = (date("Y-m-d", strtotime($incomeModel->datetime)) >= $dateRestriction->start_date) && (date("Y-m-d", strtotime($incomeModel->datetime)) <= $dateRestriction->end_date) ? date('Y-m-d', strtotime($incomeModel->datetime)) : '';
        
        $searchModel = new FreebieAndIconSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $access = AccessProgram::findOne(['user_id' => Yii::$app->user->identity->userinfo->user_id]);

        $overall = $access ? $access->branch_program_id!= '' ? FreebieAndIcon::find()
                    ->select(['sum(amount) as total'])
                    ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                    ->leftJoin('accounting_season', 'accounting_season.id = accounting_income_freebies_and_icons.season_id')
                    ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                    ->andWhere(['accounting_income.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                    ->asArray()
                    ->all() : FreebieAndIcon::find()
                    ->select(['sum(amount) as total'])
                    ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                    ->andWhere(['accounting_income.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                    ->asArray()
                    ->all() : FreebieAndIcon::find()
                    ->select(['sum(amount) as total'])
                    ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                    ->andWhere(['accounting_income.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                    ->asArray()
                    ->all();

        $overallCash = $access ? $access->branch_program_id!= '' ? FreebieAndIcon::find()
                    ->select(['sum(amount) as total'])
                    ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                    ->leftJoin('accounting_season', 'accounting_season.id = accounting_income_freebies_and_icons.season_id')
                    ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                    ->andWhere(['accounting_income.amount_type' => 'Cash'])
                    ->andWhere(['accounting_income.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                    ->asArray()
                    ->all() : FreebieAndIcon::find()
                    ->select(['sum(amount) as total'])
                    ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                    ->where(['accounting_income.amount_type' => 'Cash'])
                    ->andWhere(['accounting_income.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                    ->asArray()
                    ->all() : FreebieAndIcon::find()
                    ->select(['sum(amount) as total'])
                    ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                    ->where(['accounting_income.amount_type' => 'Cash'])
                    ->andWhere(['accounting_income.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                    ->asArray()
                    ->all();

        $overallCheck = $access ? $access->branch_program_id!= '' ? FreebieAndIcon::find()
                    ->select(['sum(amount) as total'])
                    ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                    ->leftJoin('accounting_season', 'accounting_season.id = accounting_income_freebies_and_icons.season_id')
                    ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                    ->andWhere(['<>', 'accounting_income.amount_type', 'Cash'])
                    ->andWhere(['accounting_income.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                    ->asArray()
                    ->all() : FreebieAndIcon::find()
                    ->select(['sum(amount) as total'])
                    ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                    ->andWhere(['<>', 'accounting_income.amount_type', 'Cash'])
                    ->andWhere(['accounting_income.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                    ->asArray()
                    ->all() : FreebieAndIcon::find()
                    ->select(['sum(amount) as total'])
                    ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                    ->andWhere(['<>', 'accounting_income.amount_type', 'Cash'])
                    ->andWhere(['accounting_income.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                    ->asArray()
                    ->all();

        $overallToday = $access ? $access->branch_program_id!= '' ? FreebieAndIcon::find()
                    ->select(['sum(amount) as total'])
                    ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                    ->leftJoin('accounting_season', 'accounting_season.id = accounting_income_freebies_and_icons.season_id')
                    ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                    ->andWhere(['like', 'accounting_income.datetime', date("Y-m-d")])
                    ->andWhere(['accounting_income.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                    ->asArray()
                    ->all() : FreebieAndIcon::find()
                    ->select(['sum(amount) as total'])
                    ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                    ->andWhere(['like', 'accounting_income.datetime', date("Y-m-d")])
                    ->andWhere(['accounting_income.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                    ->asArray()
                    ->all() : FreebieAndIcon::find()
                    ->select(['sum(amount) as total'])
                    ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                    ->andWhere(['like', 'accounting_income.datetime', date("Y-m-d")])
                    ->andWhere(['accounting_income.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                    ->asArray()
                    ->all();

        $overallCashToday = $access ? $access->branch_program_id!= '' ? FreebieAndIcon::find()
                    ->select(['sum(amount) as total'])
                    ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                    ->leftJoin('accounting_season', 'accounting_season.id = accounting_income_freebies_and_icons.season_id')
                    ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                    ->andWhere(['accounting_income.amount_type' => 'Cash'])
                    ->andWhere(['like', 'accounting_income.datetime', date("Y-m-d")])
                    ->andWhere(['accounting_income.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                    ->asArray()
                    ->all() : FreebieAndIcon::find()
                    ->select(['sum(amount) as total'])
                    ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                    ->where(['accounting_income.amount_type' => 'Cash'])
                    ->andWhere(['like', 'accounting_income.datetime', date("Y-m-d")])
                    ->andWhere(['accounting_income.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                    ->asArray()
                    ->all() : FreebieAndIcon::find()
                    ->select(['sum(amount) as total'])
                    ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                    ->where(['accounting_income.amount_type' => 'Cash'])
                    ->andWhere(['like', 'accounting_income.datetime', date("Y-m-d")])
                    ->andWhere(['accounting_income.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                    ->asArray()
                    ->all();

        $overallCheckToday = $access ? $access->branch_program_id!= '' ? FreebieAndIcon::find()
                    ->select(['sum(amount) as total'])
                    ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                    ->leftJoin('accounting_season', 'accounting_season.id = accounting_income_freebies_and_icons.season_id')
                    ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                    ->andWhere(['<>', 'accounting_income.amount_type', 'Cash'])
                    ->andWhere(['like', 'accounting_income.datetime', date("Y-m-d")])
                    ->andWhere(['accounting_income.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                    ->asArray()
                    ->all() : FreebieAndIcon::find()
                    ->select(['sum(amount) as total'])
                    ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                    ->andWhere(['<>', 'accounting_income.amount_type', 'Cash'])
                    ->andWhere(['like', 'accounting_income.datetime', date("Y-m-d")])
                    ->andWhere(['accounting_income.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                    ->asArray()
                    ->all() : FreebieAndIcon::find()
                    ->select(['sum(amount) as total'])
                    ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                    ->andWhere(['<>', 'accounting_income.amount_type', 'Cash'])
                    ->andWhere(['like', 'accounting_income.datetime', date("Y-m-d")])
                    ->andWhere(['accounting_income.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                    ->asArray()
                    ->all();

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

        $incomeCodes = IncomeCode::find()->select(['id, concat(name," - ",description) as name'])->where(['income_type_id' => '2'])->all();
        $incomeCodes = ArrayHelper::map($incomeCodes, 'id', 'name');

        $dateRestriction = DateRestriction::findOne(['branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C]);

        if ($model->load(Yii::$app->request->post())) {
            $postData = Yii::$app->request->post();
            if($model->save(false))
            {
                $selectedSeason = Season::findOne($model->season_id);
                if($selectedSeason)
                {
                    $incomeModel->income_type_id = 2; 
                    $incomeModel->branch_id = $selectedSeason->branchProgram->branch_id; 
                    $incomeModel->program_id = $selectedSeason->branchProgram->program_id;  
                    $incomeModel->income_id = $model->id; 
                    $incomeModel->amount_type = $postData['Income']['amount_type'];
                    $incomeModel->transaction_number = $postData['Income']['transaction_number'];
                    if($dateRestriction){
                        if($dateRestriction->allow == "Yes" && $dateRestriction->open_type == "Income"){ 
                            if(!isset($postData['Income']['dateNow']))
                            {
                                if($postData['Income']['datetime']!=""){
                                    $incomeModel->datetime = $postData['Income']['datetime'].' '.date("H:i:s");
                                }
                            }else{
                              $incomeModel->datetime = date("Y-m-d H:i:s");
                            }
                        }else{
                            $incomeModel->datetime = date("Y-m-d H:i:s");
                        }
                    }else{
                        $incomeModel->datetime = date("Y-m-d H:i:s");
                    }
                    
                    $incomeModel->save(false);
                    \Yii::$app->getSession()->setFlash('success', 'Payment has been updated.');
                    return $this->redirect(['index']);
                }
            }
        }

        return $this->render('index', [
            'model' => $model,
            'incomeModel' => $incomeModel,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'seasons' => $seasons,
            'incomeCodes' => $incomeCodes,
            'dateRestriction' => $dateRestriction,
            'overall' => $overall,
            'overallCash' => $overallCash,
            'overallCheck' => $overallCheck,
            'overallToday' => $overallToday,
            'overallCashToday' => $overallCashToday,
            'overallCheckToday' => $overallCheckToday,
        ]);
    }

    /**
     * Deletes an existing FreebieAndIcon model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $incomeModel = Income::findOne(['income_id' => $model->id, 'income_type_id' => '2']);
        $seasonOrModel = SeasonOr::findOne(['or_no' => $model->pr]);

        if($model->delete())
        {
            $incomeModel->delete();
            $seasonOrModel->delete();
        }

        \Yii::$app->getSession()->setFlash('success', 'Payment has been deleted.');
        return $this->redirect(['index']);
    }

    /**
     * Finds the FreebieAndIcon model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return FreebieAndIcon the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = FreebieAndIcon::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
