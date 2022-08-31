<?php

namespace common\modules\accounting\controllers;

use Yii;
use common\modules\accounting\models\ArchiveSeason;
use common\modules\accounting\models\AccessProgram;
use common\modules\accounting\models\Audit;
use common\modules\accounting\models\Notification;
use common\modules\accounting\models\NotificationSearch;
use common\modules\accounting\models\Student;
use common\modules\accounting\models\Dropout;
use common\modules\accounting\models\Transferee;
use common\modules\accounting\models\Income;
use common\modules\accounting\models\Branch;
use common\modules\accounting\models\BranchProgram;
use common\modules\accounting\models\beginningCoh;
use common\modules\accounting\models\BranchProgramSearch;
use common\modules\accounting\models\IncomeEnrolment;
use common\modules\accounting\models\FreebieAndIcon;
use common\modules\accounting\models\Season;
use common\modules\accounting\models\SeasonOr;
use common\modules\accounting\models\SeasonOrList;
use common\modules\accounting\models\StudentTuition;
use common\modules\accounting\models\IncomeCode;
use common\modules\accounting\models\DateRestriction;
use common\modules\accounting\models\TargetEnrolee;
use common\modules\accounting\models\TargetExpense;
use common\modules\accounting\models\TargetAcademic;
use common\modules\accounting\models\TargetEmergencyFund;
use common\modules\accounting\models\TargetFood;
use common\modules\accounting\models\TargetFreebie;
use common\modules\accounting\models\TargetIncome;
use common\modules\accounting\models\TargetProgram;
use common\modules\accounting\models\TargetRebate;
use common\modules\accounting\models\TargetReview;
use common\modules\accounting\models\TargetRoyaltyFee;
use common\modules\accounting\models\TargetStaffSalary;
use common\modules\accounting\models\TargetTax;
use common\modules\accounting\models\TargetTransportation;
use common\modules\accounting\models\TargetUtility;
use common\modules\accounting\models\TargetVenueRental;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use kartik\mpdf\Pdf;

class HomeController extends \yii\web\Controller
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
                'only' => ['index'],
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ]
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

    function getTargetAcademicByBranchProgram()
    {
        $sql = BranchProgram::find()
                ->select([
                    'accounting_branch_program.id',
                    'sum(COALESCE(targets.total, 0)) as total'
                ])
                ->leftJoin(['targets' => '(
                    SELECT 
                        accounting_branch_program.id,
                        sum(COALESCE(quantity*unit_price, 0)) as total
                    from accounting_target_academic
                    LEFT JOIN accounting_season on accounting_season.id = accounting_target_academic.season_id
                    LEFT JOIN accounting_branch_program on accounting_branch_program.id = accounting_season.branch_program_id
                    GROUP BY accounting_branch_program.id
                )'], 'targets.id = accounting_branch_program.id')
                ->groupBy(['accounting_branch_program.id'])
                ->createCommand()
                ->getRawSql();

        return $sql;
    }

    function getTargetAcademicBySeason()
    {
        $sql = Season::find()
                ->select([
                    'accounting_season.branch_program_id',
                    'accounting_season.id',
                    'sum(COALESCE(targets.total, 0)) as total'
                ])
                ->leftJoin(['targets' => '(
                    SELECT 
                        accounting_season.id,
                        sum(COALESCE(quantity*unit_price, 0)) as total
                    from accounting_target_academic
                    LEFT JOIN accounting_season on accounting_season.id = accounting_target_academic.season_id
                    GROUP BY accounting_season.id
                )'], 'targets.id = accounting_season.id')
                ->groupBy(['accounting_season.id'])
                ->createCommand()
                ->getRawSql();

        return $sql;
    }

    function getTargetEmergencyFundByBranchProgram()
    {
        $sql = BranchProgram::find()
                ->select([
                    'accounting_branch_program.id',
                    'sum(COALESCE(targets.total, 0)) as total'
                ])
                ->leftJoin(['targets' => '(
                    SELECT 
                        accounting_branch_program.id,
                        sum(COALESCE(quantity*unit_price, 0)) as total
                    from accounting_target_emergency_fund
                    LEFT JOIN accounting_season on accounting_season.id = accounting_target_emergency_fund.season_id
                    LEFT JOIN accounting_branch_program on accounting_branch_program.id = accounting_season.branch_program_id
                    GROUP BY accounting_branch_program.id
                )'], 'targets.id = accounting_branch_program.id')
                ->groupBy(['accounting_branch_program.id'])
                ->createCommand()
                ->getRawSql();

        return $sql;
    }

    function getTargetEmergencyFundBySeason()
    {
        $sql = Season::find()
                ->select([
                    'accounting_season.branch_program_id',
                    'accounting_season.id',
                    'sum(COALESCE(targets.total, 0)) as total'
                ])
                ->leftJoin(['targets' => '(
                    SELECT 
                        accounting_season.id,
                        sum(COALESCE(quantity*unit_price, 0)) as total
                    from accounting_target_emergency_fund
                    LEFT JOIN accounting_season on accounting_season.id = accounting_target_emergency_fund.season_id
                    GROUP BY accounting_season.id
                )'], 'targets.id = accounting_season.id')
                ->groupBy(['accounting_season.id'])
                ->createCommand()
                ->getRawSql();

        return $sql;
    }

    function getTargetFoodByBranchProgram()
    {
        $sql = BranchProgram::find()
                ->select([
                    'accounting_branch_program.id',
                    'sum(COALESCE(targets.total, 0)) as total'
                ])
                ->leftJoin(['targets' => '(
                    SELECT 
                        accounting_branch_program.id,
                        sum(COALESCE(quantity*unit_price, 0)) as total
                    from accounting_target_food
                    LEFT JOIN accounting_season on accounting_season.id = accounting_target_food.season_id
                    LEFT JOIN accounting_branch_program on accounting_branch_program.id = accounting_season.branch_program_id
                    GROUP BY accounting_branch_program.id
                )'], 'targets.id = accounting_branch_program.id')
                ->groupBy(['accounting_branch_program.id'])
                ->createCommand()
                ->getRawSql();

        return $sql;
    }

    function getTargetFoodBySeason()
    {
        $sql = Season::find()
                ->select([
                    'accounting_season.branch_program_id',
                    'accounting_season.id',
                    'sum(COALESCE(targets.total, 0)) as total'
                ])
                ->leftJoin(['targets' => '(
                    SELECT 
                        accounting_season.id,
                        sum(COALESCE(quantity*unit_price, 0)) as total
                    from accounting_target_food
                    LEFT JOIN accounting_season on accounting_season.id = accounting_target_food.season_id
                    GROUP BY accounting_season.id
                )'], 'targets.id = accounting_season.id')
                ->groupBy(['accounting_season.id'])
                ->createCommand()
                ->getRawSql();

        return $sql;
    }

    function getTargetFreebieByBranchProgram()
    {
        $sql = BranchProgram::find()
                ->select([
                    'accounting_branch_program.id',
                    'sum(COALESCE(targets.total, 0)) as total'
                ])
                ->leftJoin(['targets' => '(
                    SELECT 
                        accounting_branch_program.id,
                        sum(COALESCE(quantity*unit_price, 0)) as total
                    from accounting_target_freebie
                    LEFT JOIN accounting_season on accounting_season.id = accounting_target_freebie.season_id
                    LEFT JOIN accounting_branch_program on accounting_branch_program.id = accounting_season.branch_program_id
                    GROUP BY accounting_branch_program.id
                )'], 'targets.id = accounting_branch_program.id')
                ->groupBy(['accounting_branch_program.id'])
                ->createCommand()
                ->getRawSql();

        return $sql;
    }

    function getTargetFreebieBySeason()
    {
        $sql = Season::find()
                ->select([
                    'accounting_season.branch_program_id',
                    'accounting_season.id',
                    'sum(COALESCE(targets.total, 0)) as total'
                ])
                ->leftJoin(['targets' => '(
                    SELECT 
                        accounting_season.id,
                        sum(COALESCE(quantity*unit_price, 0)) as total
                    from accounting_target_freebie
                    LEFT JOIN accounting_season on accounting_season.id = accounting_target_freebie.season_id
                    GROUP BY accounting_season.id
                )'], 'targets.id = accounting_season.id')
                ->groupBy(['accounting_season.id'])
                ->createCommand()
                ->getRawSql();

        return $sql;
    }

    function getTargetIncomeByBranchProgram()
    {
        $sql = BranchProgram::find()
                ->select([
                    'accounting_branch_program.id',
                    'sum(COALESCE(targets.total, 0)) as total'
                ])
                ->leftJoin(['targets' => '(
                    SELECT 
                        accounting_branch_program.id,
                        sum(COALESCE(quantity*unit_price, 0)) as total
                    from accounting_target_income
                    LEFT JOIN accounting_season on accounting_season.id = accounting_target_income.season_id
                    LEFT JOIN accounting_branch_program on accounting_branch_program.id = accounting_season.branch_program_id
                    GROUP BY accounting_branch_program.id
                )'], 'targets.id = accounting_branch_program.id')
                ->groupBy(['accounting_branch_program.id'])
                ->createCommand()
                ->getRawSql();

        return $sql;
    }

    function getTargetIncomeBySeason()
    {
        $sql = Season::find()
                ->select([
                    'accounting_season.branch_program_id',
                    'accounting_season.id',
                    'sum(COALESCE(targets.total, 0)) as total'
                ])
                ->leftJoin(['targets' => '(
                    SELECT 
                        accounting_season.id,
                        sum(COALESCE(quantity*unit_price, 0)) as total
                    from accounting_target_income
                    LEFT JOIN accounting_season on accounting_season.id = accounting_target_income.season_id
                    GROUP BY accounting_season.id
                )'], 'targets.id = accounting_season.id')
                ->groupBy(['accounting_season.id'])
                ->createCommand()
                ->getRawSql();

        return $sql;
    }

    function getTargetProgramByBranchProgram()
    {
        $sql = BranchProgram::find()
                ->select([
                    'accounting_branch_program.id',
                    'sum(COALESCE(targets.total, 0)) as total'
                ])
                ->leftJoin(['targets' => '(
                    SELECT 
                        accounting_branch_program.id,
                        sum(COALESCE(quantity*unit_price, 0)) as total
                    from accounting_target_program
                    LEFT JOIN accounting_season on accounting_season.id = accounting_target_program.season_id
                    LEFT JOIN accounting_branch_program on accounting_branch_program.id = accounting_season.branch_program_id
                    GROUP BY accounting_branch_program.id
                )'], 'targets.id = accounting_branch_program.id')
                ->groupBy(['accounting_branch_program.id'])
                ->createCommand()
                ->getRawSql();

        return $sql;
    }

    function getTargetProgramBySeason()
    {
        $sql = Season::find()
                ->select([
                    'accounting_season.branch_program_id',
                    'accounting_season.id',
                    'sum(COALESCE(targets.total, 0)) as total'
                ])
                ->leftJoin(['targets' => '(
                    SELECT 
                        accounting_season.id,
                        sum(COALESCE(quantity*unit_price, 0)) as total
                    from accounting_target_program
                    LEFT JOIN accounting_season on accounting_season.id = accounting_target_program.season_id
                    GROUP BY accounting_season.id
                )'], 'targets.id = accounting_season.id')
                ->groupBy(['accounting_season.id'])
                ->createCommand()
                ->getRawSql();

        return $sql;
    }

    function getTargetRebateByBranchProgram()
    {
        $sql = BranchProgram::find()
                ->select([
                    'accounting_branch_program.id',
                    'sum(COALESCE(targets.total, 0)) as total'
                ])
                ->leftJoin(['targets' => '(
                    SELECT 
                        accounting_branch_program.id,
                        sum(COALESCE(quantity*unit_price, 0)) as total
                    from accounting_target_rebate
                    LEFT JOIN accounting_season on accounting_season.id = accounting_target_rebate.season_id
                    LEFT JOIN accounting_branch_program on accounting_branch_program.id = accounting_season.branch_program_id
                    GROUP BY accounting_branch_program.id
                )'], 'targets.id = accounting_branch_program.id')
                ->groupBy(['accounting_branch_program.id'])
                ->createCommand()
                ->getRawSql();

        return $sql;
    }

    function getTargetRebateBySeason()
    {
        $sql = Season::find()
                ->select([
                    'accounting_season.branch_program_id',
                    'accounting_season.id',
                    'sum(COALESCE(targets.total, 0)) as total'
                ])
                ->leftJoin(['targets' => '(
                    SELECT 
                        accounting_season.id,
                        sum(COALESCE(quantity*unit_price, 0)) as total
                    from accounting_target_rebate
                    LEFT JOIN accounting_season on accounting_season.id = accounting_target_rebate.season_id
                    GROUP BY accounting_season.id
                )'], 'targets.id = accounting_season.id')
                ->groupBy(['accounting_season.id'])
                ->createCommand()
                ->getRawSql();

        return $sql;
    }

    function getTargetReviewByBranchProgram()
    {
        $sql = BranchProgram::find()
                ->select([
                    'accounting_branch_program.id',
                    'sum(COALESCE(targets.total, 0)) as total'
                ])
                ->leftJoin(['targets' => '(
                    SELECT 
                        accounting_branch_program.id,
                        sum(COALESCE(quantity*unit_price, 0)) as total
                    from accounting_target_review
                    LEFT JOIN accounting_season on accounting_season.id = accounting_target_review.season_id
                    LEFT JOIN accounting_branch_program on accounting_branch_program.id = accounting_season.branch_program_id
                    GROUP BY accounting_branch_program.id
                )'], 'targets.id = accounting_branch_program.id')
                ->groupBy(['accounting_branch_program.id'])
                ->createCommand()
                ->getRawSql();

        return $sql;
    }

    function getTargetReviewBySeason()
    {
        $sql = Season::find()
                ->select([
                    'accounting_season.branch_program_id',
                    'accounting_season.id',
                    'sum(COALESCE(targets.total, 0)) as total'
                ])
                ->leftJoin(['targets' => '(
                    SELECT 
                        accounting_season.id,
                        sum(COALESCE(quantity*unit_price, 0)) as total
                    from accounting_target_review
                    LEFT JOIN accounting_season on accounting_season.id = accounting_target_review.season_id
                    GROUP BY accounting_season.id
                )'], 'targets.id = accounting_season.id')
                ->groupBy(['accounting_season.id'])
                ->createCommand()
                ->getRawSql();

        return $sql;
    }

    function getTargetStaffSalaryByBranchProgram()
    {
        $sql = BranchProgram::find()
                ->select([
                    'accounting_branch_program.id',
                    'sum(COALESCE(targets.total, 0)) as total'
                ])
                ->leftJoin(['targets' => '(
                    SELECT 
                        accounting_branch_program.id,
                        sum(COALESCE(quantity*unit_price, 0)) as total
                    from accounting_target_staff_salary
                    LEFT JOIN accounting_season on accounting_season.id = accounting_target_staff_salary.season_id
                    LEFT JOIN accounting_branch_program on accounting_branch_program.id = accounting_season.branch_program_id
                    GROUP BY accounting_branch_program.id
                )'], 'targets.id = accounting_branch_program.id')
                ->groupBy(['accounting_branch_program.id'])
                ->createCommand()
                ->getRawSql();

        return $sql;
    }

    function getTargetStaffSalaryBySeason()
    {
        $sql = Season::find()
                ->select([
                    'accounting_season.branch_program_id',
                    'accounting_season.id',
                    'sum(COALESCE(targets.total, 0)) as total'
                ])
                ->leftJoin(['targets' => '(
                    SELECT 
                        accounting_season.id,
                        sum(COALESCE(quantity*unit_price, 0)) as total
                    from accounting_target_staff_salary
                    LEFT JOIN accounting_season on accounting_season.id = accounting_target_staff_salary.season_id
                    GROUP BY accounting_season.id
                )'], 'targets.id = accounting_season.id')
                ->groupBy(['accounting_season.id'])
                ->createCommand()
                ->getRawSql();

        return $sql;
    }

    function getTargetTransportationByBranchProgram()
    {
        $sql = BranchProgram::find()
                ->select([
                    'accounting_branch_program.id',
                    'sum(COALESCE(targets.total, 0)) as total'
                ])
                ->leftJoin(['targets' => '(
                    SELECT 
                        accounting_branch_program.id,
                        sum(COALESCE(quantity*unit_price, 0)) as total
                    from accounting_target_transportation
                    LEFT JOIN accounting_season on accounting_season.id = accounting_target_transportation.season_id
                    LEFT JOIN accounting_branch_program on accounting_branch_program.id = accounting_season.branch_program_id
                    GROUP BY accounting_branch_program.id
                )'], 'targets.id = accounting_branch_program.id')
                ->groupBy(['accounting_branch_program.id'])
                ->createCommand()
                ->getRawSql();

        return $sql;
    }

    function getTargetTransportationBySeason()
    {
        $sql = Season::find()
                ->select([
                    'accounting_season.branch_program_id',
                    'accounting_season.id',
                    'sum(COALESCE(targets.total, 0)) as total'
                ])
                ->leftJoin(['targets' => '(
                    SELECT 
                        accounting_season.id,
                        sum(COALESCE(quantity*unit_price, 0)) as total
                    from accounting_target_transportation
                    LEFT JOIN accounting_season on accounting_season.id = accounting_target_transportation.season_id
                    GROUP BY accounting_season.id
                )'], 'targets.id = accounting_season.id')
                ->groupBy(['accounting_season.id'])
                ->createCommand()
                ->getRawSql();

        return $sql;
    }

    function getTargetUtilityByBranchProgram()
    {
        $sql = BranchProgram::find()
                ->select([
                    'accounting_branch_program.id',
                    'sum(COALESCE(targets.total, 0)) as total'
                ])
                ->leftJoin(['targets' => '(
                    SELECT 
                        accounting_branch_program.id,
                        sum(COALESCE(quantity*unit_price, 0)) as total
                    from accounting_target_utility
                    LEFT JOIN accounting_season on accounting_season.id = accounting_target_utility.season_id
                    LEFT JOIN accounting_branch_program on accounting_branch_program.id = accounting_season.branch_program_id
                    GROUP BY accounting_branch_program.id
                )'], 'targets.id = accounting_branch_program.id')
                ->groupBy(['accounting_branch_program.id'])
                ->createCommand()
                ->getRawSql();

        return $sql;
    }

    function getTargetUtilityBySeason()
    {
        $sql = Season::find()
                ->select([
                    'accounting_season.branch_program_id',
                    'accounting_season.id',
                    'sum(COALESCE(targets.total, 0)) as total'
                ])
                ->leftJoin(['targets' => '(
                    SELECT 
                        accounting_season.id,
                        sum(COALESCE(quantity*unit_price, 0)) as total
                    from accounting_target_utility
                    LEFT JOIN accounting_season on accounting_season.id = accounting_target_utility.season_id
                    GROUP BY accounting_season.id
                )'], 'targets.id = accounting_season.id')
                ->groupBy(['accounting_season.id'])
                ->createCommand()
                ->getRawSql();

        return $sql;
    }

    function getTargetVenueRentalByBranchProgram()
    {
        $sql = BranchProgram::find()
                ->select([
                    'accounting_branch_program.id',
                    'sum(COALESCE(targets.total, 0)) as total'
                ])
                ->leftJoin(['targets' => '(
                    SELECT 
                        accounting_branch_program.id,
                        sum(COALESCE(quantity*unit_price, 0)) as total
                    from accounting_target_venue_rental
                    LEFT JOIN accounting_season on accounting_season.id = accounting_target_venue_rental.season_id
                    LEFT JOIN accounting_branch_program on accounting_branch_program.id = accounting_season.branch_program_id
                    GROUP BY accounting_branch_program.id
                )'], 'targets.id = accounting_branch_program.id')
                ->groupBy(['accounting_branch_program.id'])
                ->createCommand()
                ->getRawSql();

        return $sql;
    }

    function getTargetVenueRentalBySeason()
    {
        $sql = Season::find()
                ->select([
                    'accounting_season.branch_program_id',
                    'accounting_season.id',
                    'sum(COALESCE(targets.total, 0)) as total'
                ])
                ->leftJoin(['targets' => '(
                    SELECT 
                        accounting_season.id,
                        sum(COALESCE(quantity*unit_price, 0)) as total
                    from accounting_target_venue_rental
                    LEFT JOIN accounting_season on accounting_season.id = accounting_target_venue_rental.season_id
                    GROUP BY accounting_season.id
                )'], 'targets.id = accounting_season.id')
                ->groupBy(['accounting_season.id'])
                ->createCommand()
                ->getRawSql();

        return $sql;
    }

    function getOverallIncomeEnrolmentByBranchProgramRawSql($start = null, $end = null)
    {
        if(!is_null($start) && !is_null($end))
        {
            $sql = BranchProgram::find()
            ->select([
                'accounting_branch_program.id',
                'sum(COALESCE(incomeEnrolments.total, 0)) as total'
            ])
            ->leftJoin(['incomeEnrolments' => '(
                SELECT 
                    accounting_branch_program.id,
                    sum(COALESCE(accounting_income_enrolment.amount, 0)) as total,
                    DATE(accounting_income.datetime) as newDate,
                    accounting_income.amount_type as amountType
                from accounting_income_enrolment
                LEFT JOIN accounting_income on accounting_income.income_id = accounting_income_enrolment.id and accounting_income.income_type_id = 1
                LEFT JOIN accounting_season on accounting_season.id = accounting_income_enrolment.season_id
                LEFT JOIN accounting_branch_program on accounting_branch_program.id = accounting_season.branch_program_id
                WHERE 
                    accounting_income.datetime between "'.$start.' 00:00:00" and "'.$end.' 23:59:59"
                GROUP BY newDate, accounting_branch_program.id
            )'], 'incomeEnrolments.id = accounting_branch_program.id')
            ->groupBy(['accounting_branch_program.id'])
            ->createCommand()
            ->getRawSql();
        }else{
            $sql = BranchProgram::find()
            ->select([
                'accounting_branch_program.id',
                'sum(COALESCE(incomeEnrolments.total, 0)) as total'
            ])
            ->leftJoin(['incomeEnrolments' => '(
                SELECT 
                    accounting_branch_program.id,
                    sum(COALESCE(accounting_income_enrolment.amount, 0)) as total,
                    DATE(accounting_income.datetime) as newDate,
                    accounting_income.amount_type as amountType
                from accounting_income_enrolment
                LEFT JOIN accounting_income on accounting_income.income_id = accounting_income_enrolment.id and accounting_income.income_type_id = 1
                LEFT JOIN accounting_season on accounting_season.id = accounting_income_enrolment.season_id
                LEFT JOIN accounting_branch_program on accounting_branch_program.id = accounting_season.branch_program_id
                GROUP BY newDate, accounting_branch_program.id
            )'], 'incomeEnrolments.id = accounting_branch_program.id')
            ->groupBy(['accounting_branch_program.id'])
            ->createCommand()
            ->getRawSql();
        }

        return $sql;
    }

    function getOverallIncomeEnrolmentBySeasonRawSql($start = null, $end = null)
    {
        if(!is_null($start) && !is_null($end))
        {
            $sql = Season::find()
                ->select([
                    'accounting_season.branch_program_id',
                    'accounting_season.id',
                    'sum(COALESCE(incomeEnrolments.total, 0)) as total'
                ])
                ->leftJoin(['incomeEnrolments' => '(
                    SELECT 
                        accounting_season.id,
                        sum(COALESCE(accounting_income_enrolment.amount, 0)) as total,
                        DATE(accounting_income.datetime) as newDate,
                        accounting_income.amount_type as amountType
                    from accounting_income_enrolment
                    LEFT JOIN accounting_income on accounting_income.income_id = accounting_income_enrolment.id and accounting_income.income_type_id = 1
                    LEFT JOIN accounting_season on accounting_season.id = accounting_income_enrolment.season_id
                    WHERE 
                        accounting_income.datetime between "'.$start.' 00:00:00" and "'.$end.' 23:59:59"
                    GROUP BY newDate, accounting_season.id
                )'], 'incomeEnrolments.id = accounting_season.id')
                ->groupBy(['accounting_season.id'])
                ->createCommand()
                ->getRawSql();
        }else{
            $sql = Season::find()
                ->select([
                    'accounting_season.branch_program_id',
                    'accounting_season.id',
                    'sum(COALESCE(incomeEnrolments.total, 0)) as total'
                ])
                ->leftJoin(['incomeEnrolments' => '(
                    SELECT 
                        accounting_season.id,
                        sum(COALESCE(accounting_income_enrolment.amount, 0)) as total,
                        DATE(accounting_income.datetime) as newDate,
                        accounting_income.amount_type as amountType
                    from accounting_income_enrolment
                    LEFT JOIN accounting_income on accounting_income.income_id = accounting_income_enrolment.id and accounting_income.income_type_id = 1
                    LEFT JOIN accounting_season on accounting_season.id = accounting_income_enrolment.season_id
                    GROUP BY newDate, accounting_season.id
                )'], 'incomeEnrolments.id = accounting_season.id')
                ->groupBy(['accounting_season.id'])
                ->createCommand()
                ->getRawSql();
        }

        return $sql;
    }

    function getOverallIncomeFreebieByBranchProgramRawSql($start = null, $end = null)
    {
        if(!is_null($start) && !is_null($end))
        {
            $sql = BranchProgram::find()
                ->select([
                    'accounting_branch_program.id',
                    'sum(COALESCE(freebies.total, 0)) as total'
                ])
                ->leftJoin(['freebies' => '(
                    SELECT 
                        accounting_branch_program.id,
                        sum(COALESCE(accounting_income_freebies_and_icons.amount, 0)) as total,
                        DATE(accounting_income.datetime) as newDate,
                        accounting_income.amount_type as amountType
                    from accounting_income_freebies_and_icons
                    LEFT JOIN accounting_income on accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2
                    LEFT JOIN accounting_season on accounting_season.id = accounting_income_freebies_and_icons.season_id
                    LEFT JOIN accounting_branch_program on accounting_branch_program.id = accounting_season.branch_program_id
                    WHERE 
                        accounting_income.datetime between "'.$start.' 00:00:00" and "'.$end.' 23:59:59"
                    GROUP BY newDate, accounting_branch_program.id
                )'], 'freebies.id = accounting_branch_program.id')
                ->groupBy(['accounting_branch_program.id'])
                ->createCommand()
                ->getRawSql();
        }else{
            $sql = BranchProgram::find()
                ->select([
                    'accounting_branch_program.id',
                    'sum(COALESCE(freebies.total, 0)) as total'
                ])
                ->leftJoin(['freebies' => '(
                    SELECT 
                        accounting_income.branch_id,
                        accounting_income.program_id,
                        sum(COALESCE(accounting_income_freebies_and_icons.amount, 0)) as total,
                        DATE(accounting_income.datetime) as newDate,
                        accounting_income.amount_type as amountType
                    from accounting_income_freebies_and_icons
                    LEFT JOIN accounting_income on accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2
                    LEFT JOIN accounting_season on accounting_season.id = accounting_income_freebies_and_icons.season_id
                    LEFT JOIN accounting_branch_program on accounting_branch_program.id = accounting_season.branch_program_id
                    GROUP BY newDate, accounting_branch_program.id
                )'], 'freebies.id = accounting_branch_program.id')
                ->groupBy(['accounting_branch_program.id'])
                ->createCommand()
                ->getRawSql();
        }

        return $sql;
    }

    function getOverallIncomeFreebieBySeasonRawSql($start = null, $end = null)
    {
        if(!is_null($start) && !is_null($end))
        {
            $sql = Season::find()
                ->select([
                    'accounting_season.branch_program_id',
                    'accounting_season.id',
                    'sum(COALESCE(freebies.total, 0)) as total'
                ])
                ->leftJoin(['freebies' => '(
                    SELECT 
                        accounting_season.id,
                        sum(COALESCE(accounting_income_freebies_and_icons.amount, 0)) as total,
                        DATE(accounting_income.datetime) as newDate,
                        accounting_income.amount_type as amountType
                    from accounting_income_freebies_and_icons
                    LEFT JOIN accounting_income on accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2
                    LEFT JOIN accounting_season on accounting_season.id = accounting_income_freebies_and_icons.season_id
                    WHERE 
                        accounting_income.datetime between "'.$start.' 00:00:00" and "'.$end.' 23:59:59"
                    GROUP BY newDate, accounting_season.id
                )'], 'freebies.id = accounting_season.id')
                ->groupBy(['accounting_season.id'])
                ->createCommand()
                ->getRawSql();
        }else{
            $sql = Season::find()
                ->select([
                    'accounting_season.branch_program_id',
                    'accounting_season.id',
                    'sum(COALESCE(freebies.total, 0)) as total'
                ])
                ->leftJoin(['freebies' => '(
                    SELECT 
                        accounting_season.id,
                        sum(COALESCE(accounting_income_freebies_and_icons.amount, 0)) as total,
                        DATE(accounting_income.datetime) as newDate,
                        accounting_income.amount_type as amountType
                    from accounting_income_freebies_and_icons
                    LEFT JOIN accounting_income on accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2
                    LEFT JOIN accounting_season on accounting_season.id = accounting_income_freebies_and_icons.season_id
                    GROUP BY newDate, accounting_season.id
                )'], 'freebies.id = accounting_season.id')
                ->groupBy(['accounting_season.id'])
                ->createCommand()
                ->getRawSql();
        }

        return $sql;        
    }

    function getOverallPettyExpenseByBranchProgramRawSql($start = null, $end = null)
    {
        if(!is_null($start) && !is_null($end))
        {
            $sql = BranchProgram::find()
                ->select([
                    'accounting_branch_program.id',
                    'sum(COALESCE(pettyExpense.total, 0)) as total'
                ])
                ->leftJoin(['pettyExpense' => '(
                    SELECT 
                        accounting_branch_program.id,
                        sum(COALESCE(accounting_expense_petty_expense.food, 0)) as foodTotal,
                        sum(COALESCE(accounting_expense_petty_expense.supplies, 0)) as supplyTotal,
                        sum(COALESCE(accounting_expense_petty_expense.load, 0)) as loadTotal,
                        sum(COALESCE(accounting_expense_petty_expense.fare, 0)) as fareTotal,
                        sum(accounting_expense_petty_expense.food + accounting_expense_petty_expense.supplies + accounting_expense_petty_expense.load + accounting_expense_petty_expense.fare) as total,
                        DATE(accounting_expense.datetime) as newDate,
                        accounting_expense.amount_type as amountType
                    from accounting_expense_petty_expense
                    LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_petty_expense.id and accounting_expense.expense_type_id = 1
                    LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                    LEFT JOIN accounting_branch_program on accounting_branch_program.id = accounting_season.branch_program_id
                    WHERE 
                        accounting_expense.datetime between "'.$start.' 00:00:00" and "'.$end.' 23:59:59"
                    GROUP BY newDate, accounting_branch_program.id
                )'], 'pettyExpense.id = accounting_branch_program.id')
                ->groupBy(['accounting_branch_program.id'])
                ->createCommand()
                ->getRawSql();
        }else{
            $sql = BranchProgram::find()
                ->select([
                    'accounting_branch_program.id',
                    'sum(COALESCE(pettyExpense.total, 0)) as total'
                ])
                ->leftJoin(['pettyExpense' => '(
                    SELECT 
                        accounting_branch_program.id,
                        sum(COALESCE(accounting_expense_petty_expense.food, 0)) as foodTotal,
                        sum(COALESCE(accounting_expense_petty_expense.supplies, 0)) as supplyTotal,
                        sum(COALESCE(accounting_expense_petty_expense.load, 0)) as loadTotal,
                        sum(COALESCE(accounting_expense_petty_expense.fare, 0)) as fareTotal,
                        sum(accounting_expense_petty_expense.food + accounting_expense_petty_expense.supplies + accounting_expense_petty_expense.load + accounting_expense_petty_expense.fare) as total,
                        DATE(accounting_expense.datetime) as newDate,
                        accounting_expense.amount_type as amountType
                    from accounting_expense_petty_expense
                    LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_petty_expense.id and accounting_expense.expense_type_id = 1
                    LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                    LEFT JOIN accounting_branch_program on accounting_branch_program.id = accounting_season.branch_program_id
                    GROUP BY newDate, accounting_branch_program.id
                )'], 'pettyExpense.id = accounting_branch_program.id')
                ->groupBy(['accounting_branch_program.id'])
                ->createCommand()
                ->getRawSql();
        }

        return $sql;
    }

    function getOverallPettyExpenseBySeasonRawSql($start = null, $end = null)
    {
        if(!is_null($start) && !is_null($end))
        {
            $sql = Season::find()
                ->select([
                    'accounting_season.branch_program_id',
                    'accounting_season.id',
                    'sum(COALESCE(pettyExpense.total, 0)) as total'
                ])
                ->leftJoin(['pettyExpense' => '(
                    SELECT 
                        accounting_season.id,
                        sum(COALESCE(accounting_expense_petty_expense.food, 0)) as foodTotal,
                        sum(COALESCE(accounting_expense_petty_expense.supplies, 0)) as supplyTotal,
                        sum(COALESCE(accounting_expense_petty_expense.load, 0)) as loadTotal,
                        sum(COALESCE(accounting_expense_petty_expense.fare, 0)) as fareTotal,
                        sum(accounting_expense_petty_expense.food + accounting_expense_petty_expense.supplies + accounting_expense_petty_expense.load + accounting_expense_petty_expense.fare) as total,
                        DATE(accounting_expense.datetime) as newDate,
                        accounting_expense.amount_type as amountType
                    from accounting_expense_petty_expense
                    LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_petty_expense.id and accounting_expense.expense_type_id = 1
                    LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                    WHERE 
                        accounting_expense.datetime between "'.$start.' 00:00:00" and "'.$end.' 23:59:59"
                    GROUP BY newDate, accounting_season.id
                )'], 'pettyExpense.id = accounting_season.id')
                ->groupBy(['accounting_season.id'])
                ->createCommand()
                ->getRawSql();
        }else{
            $sql = Season::find()
                ->select([
                    'accounting_season.branch_program_id',
                    'accounting_season.id',
                    'sum(COALESCE(pettyExpense.total, 0)) as total'
                ])
                ->leftJoin(['pettyExpense' => '(
                    SELECT 
                        accounting_season.id,
                        sum(COALESCE(accounting_expense_petty_expense.food, 0)) as foodTotal,
                        sum(COALESCE(accounting_expense_petty_expense.supplies, 0)) as supplyTotal,
                        sum(COALESCE(accounting_expense_petty_expense.load, 0)) as loadTotal,
                        sum(COALESCE(accounting_expense_petty_expense.fare, 0)) as fareTotal,
                        sum(accounting_expense_petty_expense.food + accounting_expense_petty_expense.supplies + accounting_expense_petty_expense.load + accounting_expense_petty_expense.fare) as total,
                        DATE(accounting_expense.datetime) as newDate,
                        accounting_expense.amount_type as amountType
                    from accounting_expense_petty_expense
                    LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_petty_expense.id and accounting_expense.expense_type_id = 1
                    LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                    GROUP BY newDate, accounting_season.id
                )'], 'pettyExpense.id = accounting_season.id')
                ->groupBy(['accounting_season.id'])
                ->createCommand()
                ->getRawSql();
        }

        return $sql;
    }

    function getOverallPhotocopyExpenseByBranchProgramRawSql($start = null, $end = null)
    {
        if(!is_null($start) && !is_null($end))
        {
            $sql = BranchProgram::find()
                    ->select([
                        'accounting_branch_program.id',
                        'sum(COALESCE(photocopyExpense.total, 0)) as total'
                    ])
                    ->leftJoin(['photocopyExpense' => '(
                        SELECT 
                            accounting_branch_program.id,
                            sum(COALESCE(accounting_expense_photocopy_expense.total_amount, 0)) as total,
                            DATE(accounting_expense.datetime) as newDate,
                            accounting_expense.amount_type as amountType
                        from accounting_expense_photocopy_expense
                        LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2
                        LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                        LEFT JOIN accounting_branch_program on accounting_branch_program.id = accounting_season.branch_program_id
                        WHERE 
                            accounting_expense.datetime between "'.$start.' 00:00:00" and "'.$end.' 23:59:59"
                        GROUP BY newDate, accounting_branch_program.id
                    )'], 'photocopyExpense.id = accounting_branch_program.id')
                    ->groupBy(['accounting_branch_program.id'])
                    ->createCommand()
                    ->getRawSql();
        }else{
            $sql = BranchProgram::find()
                    ->select([
                        'accounting_branch_program.id',
                        'sum(COALESCE(photocopyExpense.total, 0)) as total'
                    ])
                    ->leftJoin(['photocopyExpense' => '(
                        SELECT 
                            accounting_branch_program.id,
                            sum(COALESCE(accounting_expense_photocopy_expense.total_amount, 0)) as total,
                            DATE(accounting_expense.datetime) as newDate,
                            accounting_expense.amount_type as amountType
                        from accounting_expense_photocopy_expense
                        LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2
                        LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                        LEFT JOIN accounting_branch_program on accounting_branch_program.id = accounting_season.branch_program_id
                        GROUP BY newDate, accounting_branch_program.id
                    )'], 'photocopyExpense.id = accounting_branch_program.id')
                    ->groupBy(['accounting_branch_program.id'])
                    ->createCommand()
                    ->getRawSql();
        }

        return $sql;
    }

    function getOverallPhotocopyExpenseBySeasonRawSql($start = null, $end = null)
    {
        if(!is_null($start) && !is_null($end))
        {
            $sql = Season::find()
                    ->select([
                        'accounting_season.branch_program_id',
                        'accounting_season.id',
                        'sum(COALESCE(photocopyExpense.total, 0)) as total'
                    ])
                    ->leftJoin(['photocopyExpense' => '(
                        SELECT 
                            accounting_season.id,
                            sum(COALESCE(accounting_expense_photocopy_expense.total_amount, 0)) as total,
                            DATE(accounting_expense.datetime) as newDate,
                            accounting_expense.amount_type as amountType
                        from accounting_expense_photocopy_expense
                        LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2
                        LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                        WHERE 
                            accounting_expense.datetime between "'.$start.' 00:00:00" and "'.$end.' 23:59:59"
                        GROUP BY newDate, accounting_season.id
                    )'], 'photocopyExpense.id = accounting_season.id')
                    ->groupBy(['accounting_season.id'])
                    ->createCommand()
                    ->getRawSql();

        }else{
            $sql = Season::find()
                    ->select([
                        'accounting_season.id',
                        'sum(COALESCE(photocopyExpense.total, 0)) as total'
                    ])
                    ->leftJoin(['photocopyExpense' => '(
                        SELECT 
                            accounting_season.id,
                            sum(COALESCE(accounting_expense_photocopy_expense.total_amount, 0)) as total,
                            DATE(accounting_expense.datetime) as newDate,
                            accounting_expense.amount_type as amountType
                        from accounting_expense_photocopy_expense
                        LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2
                        LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                        GROUP BY newDate, accounting_season.id
                    )'], 'photocopyExpense.id = accounting_season.id')
                    ->groupBy(['accounting_season.id'])
                    ->createCommand()
                    ->getRawSql();
        }

        return $sql;
    }

    function getOverallOtherExpenseByBranchProgramRawSql($start = null, $end = null)
    {
        if(!is_null($start) && !is_null($end))
        {
            $sql = BranchProgram::find()
                ->select([
                    'accounting_branch_program.id',
                    'sum(COALESCE(otherExpense.total, 0)) as total'
                ])
                ->leftJoin(['otherExpense' => '(
                    SELECT 
                        accounting_branch_program.id,
                        sum(COALESCE(accounting_expense_other_expense.amount, 0)) as total,
                        DATE(accounting_expense.datetime) as newDate,
                        accounting_expense.amount_type as amountType
                    from accounting_expense_other_expense
                    LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_other_expense.id and accounting_expense.expense_type_id = 3
                    LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                    LEFT JOIN accounting_branch_program on accounting_branch_program.id = accounting_season.branch_program_id
                    WHERE 
                        accounting_expense.datetime between "'.$start.' 00:00:00" and "'.$end.' 23:59:59"
                    GROUP BY newDate, accounting_branch_program.id
                )'], 'otherExpense.id = accounting_branch_program.id')
                ->groupBy(['accounting_branch_program.id'])
                ->createCommand()
                ->getRawSql();
        }else{
            $sql = BranchProgram::find()
                ->select([
                    'accounting_branch_program.id',
                    'sum(COALESCE(otherExpense.total, 0)) as total'
                ])
                ->leftJoin(['otherExpense' => '(
                    SELECT 
                        accounting_branch_program.id,
                        sum(COALESCE(accounting_expense_other_expense.amount, 0)) as total,
                        DATE(accounting_expense.datetime) as newDate,
                        accounting_expense.amount_type as amountType
                    from accounting_expense_other_expense
                    LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_other_expense.id and accounting_expense.expense_type_id = 3
                    LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                    LEFT JOIN accounting_branch_program on accounting_branch_program.id = accounting_season.branch_program_id
                    GROUP BY newDate, accounting_branch_program.id
                )'], 'otherExpense.id = accounting_branch_program.id')
                ->groupBy(['accounting_branch_program.id'])
                ->createCommand()
                ->getRawSql();
        }

        return $sql;
    }

    function getOverallOtherExpenseBySeasonRawSql($start = null, $end = null)
    {
        if(!is_null($start) && !is_null($end))
        {
            $sql = Season::find()
                ->select([
                    'accounting_season.branch_program_id',
                    'accounting_season.id',
                    'sum(COALESCE(otherExpense.total, 0)) as total'
                ])
                ->leftJoin(['otherExpense' => '(
                    SELECT 
                        accounting_season.id,
                        sum(COALESCE(accounting_expense_other_expense.amount, 0)) as total,
                        DATE(accounting_expense.datetime) as newDate,
                        accounting_expense.amount_type as amountType
                    from accounting_expense_other_expense
                    LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_other_expense.id and accounting_expense.expense_type_id = 3
                    LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                    WHERE 
                        accounting_expense.datetime between "'.$start.' 00:00:00" and "'.$end.' 23:59:59"
                    GROUP BY newDate, accounting_season.id
                )'], 'otherExpense.id = accounting_season.id')
                ->groupBy(['accounting_season.id'])
                ->createCommand()
                ->getRawSql();
        }else{
            $sql = Season::find()
                ->select([
                    'accounting_season.id',
                    'sum(COALESCE(otherExpense.total, 0)) as total'
                ])
                ->leftJoin(['otherExpense' => '(
                    SELECT 
                        accounting_season.id,
                        sum(COALESCE(accounting_expense_other_expense.amount, 0)) as total,
                        DATE(accounting_expense.datetime) as newDate,
                        accounting_expense.amount_type as amountType
                    from accounting_expense_other_expense
                    LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_other_expense.id and accounting_expense.expense_type_id = 3
                    LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                    GROUP BY newDate, accounting_season.id
                )'], 'otherExpense.id = accounting_season.id')
                ->groupBy(['accounting_season.id'])
                ->createCommand()
                ->getRawSql();
        }

        return $sql;
    }

    function getOverallBankDepositByBranchProgramRawSql($start = null, $end = null)
    {
        if(!is_null($start) && !is_null($end))
        {
            $sql = BranchProgram::find()
                ->select([
                    'accounting_branch_program.id',
                    'sum(COALESCE(bankDeposits.total, 0)) as total'
                ])
                ->leftJoin(['bankDeposits' => '(
                    SELECT 
                        accounting_branch_program.id,
                        sum(COALESCE(accounting_expense_bank_deposit.amount, 0)) as total,
                        DATE(accounting_expense.datetime) as newDate,
                        accounting_expense.amount_type as amountType
                    from accounting_expense_bank_deposit
                    LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_bank_deposit.id and accounting_expense.expense_type_id = 4
                    LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                    LEFT JOIN accounting_branch_program on accounting_branch_program.id = accounting_season.branch_program_id
                    WHERE 
                        accounting_expense.datetime between "'.$start.' 00:00:00" and "'.$end.' 23:59:59"
                    GROUP BY newDate, accounting_branch_program.id
                )'], 'bankDeposits.id = accounting_branch_program.id')
                ->groupBy(['accounting_branch_program.id'])
                ->createCommand()
                ->getRawSql();
        }else{
            $sql = BranchProgram::find()
                ->select([
                    'accounting_branch_program.id',
                    'sum(COALESCE(bankDeposits.total, 0)) as total'
                ])
                ->leftJoin(['bankDeposits' => '(
                    SELECT 
                        accounting_branch_program.id,
                        sum(COALESCE(accounting_expense_bank_deposit.amount, 0)) as total,
                        DATE(accounting_expense.datetime) as newDate,
                        accounting_expense.amount_type as amountType
                    from accounting_expense_bank_deposit
                    LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_bank_deposit.id and accounting_expense.expense_type_id = 4
                    LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                    LEFT JOIN accounting_branch_program on accounting_branch_program.id = accounting_season.branch_program_id
                    GROUP BY newDate, accounting_branch_program.id
                )'], 'bankDeposits.id = accounting_branch_program.id')
                ->groupBy(['accounting_branch_program.id'])
                ->createCommand()
                ->getRawSql();
        }

        return $sql;
    }

    function getOverallBankDepositBySeasonRawSql($start = null, $end = null)
    {
        if(!is_null($start) && !is_null($end))
        {
            $sql = Season::find()
                ->select([
                    'accounting_season.branch_program_id',
                    'accounting_season.id',
                    'sum(COALESCE(bankDeposits.total, 0)) as total'
                ])
                ->leftJoin(['bankDeposits' => '(
                    SELECT 
                        accounting_season.id,
                        sum(COALESCE(accounting_expense_bank_deposit.amount, 0)) as total,
                        DATE(accounting_expense.datetime) as newDate,
                        accounting_expense.amount_type as amountType
                    from accounting_expense_bank_deposit
                    LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_bank_deposit.id and accounting_expense.expense_type_id = 4
                    LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                    WHERE 
                        accounting_expense.datetime between "'.$start.' 00:00:00" and "'.$end.' 23:59:59"
                    GROUP BY newDate, accounting_season.id
                )'], 'bankDeposits.id = accounting_season.id')
                ->groupBy(['accounting_season.id'])
                ->createCommand()
                ->getRawSql();
        }else{
            $sql = Season::find()
                ->select([
                    'accounting_season.id',
                    'sum(COALESCE(bankDeposits.total, 0)) as total'
                ])
                ->leftJoin(['bankDeposits' => '(
                    SELECT 
                        accounting_season.id,
                        sum(COALESCE(accounting_expense_bank_deposit.amount, 0)) as total,
                        DATE(accounting_expense.datetime) as newDate,
                        accounting_expense.amount_type as amountType
                    from accounting_expense_bank_deposit
                    LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_bank_deposit.id and accounting_expense.expense_type_id = 4
                    LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                    GROUP BY newDate, accounting_season.id
                )'], 'bankDeposits.id = accounting_season.id')
                ->groupBy(['accounting_season.id'])
                ->createCommand()
                ->getRawSql();
        }

        return $sql;
    }

    function getOverallOperatingExpenseByBranchProgramRawSql($start = null, $end = null)
    {
        if(!is_null($start) && !is_null($end))
        {
            $sql = BranchProgram::find()
                ->select([
                    'accounting_branch_program.id',
                    'sum(COALESCE(operatingExpenses.total, 0)) as total'
                ])
                ->leftJoin(['operatingExpenses' => '(
                    SELECT 
                        accounting_branch_program.id,
                        sum(COALESCE(accounting_expense_operating_expense.staff_salary, 0)) as staffSalaryTotal,
                        sum(COALESCE(accounting_expense_operating_expense.cash_pf, 0)) as cashPfTotal,
                        sum(COALESCE(accounting_expense_operating_expense.rent, 0)) as rentTotal,
                        sum(COALESCE(accounting_expense_operating_expense.utilities, 0)) as utilitiesTotal,
                        sum(COALESCE(accounting_expense_operating_expense.equipment_and_labor, 0)) as equipmentAndLaborTotal,
                        sum(COALESCE(accounting_expense_operating_expense.bir_and_docs, 0)) as bir_and_docsTotal,
                        sum(COALESCE(accounting_expense_operating_expense.marketing, 0)) as marketingTotal,
                        sum(
                            accounting_expense_operating_expense.staff_salary + 
                            accounting_expense_operating_expense.cash_pf + 
                            accounting_expense_operating_expense.rent + 
                            accounting_expense_operating_expense.utilities + 
                            accounting_expense_operating_expense.equipment_and_labor + 
                            accounting_expense_operating_expense.bir_and_docs + 
                            accounting_expense_operating_expense.marketing
                        ) as total,
                        DATE(accounting_expense.datetime) as newDate,
                        accounting_expense.amount_type as amountType
                    from accounting_expense_operating_expense
                    LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_operating_expense.id and accounting_expense.expense_type_id = 5
                    LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                    LEFT JOIN accounting_branch_program on accounting_branch_program.id = accounting_season.branch_program_id
                    WHERE 
                        accounting_expense.datetime between "'.$start.' 00:00:00" and "'.$end.' 23:59:59"
                    GROUP BY newDate, accounting_branch_program.id
                )'], 'operatingExpenses.id = accounting_branch_program.id')
                ->groupBy(['accounting_branch_program.id'])
                ->createCommand()
                ->getRawSql();
        }else{
            $sql = BranchProgram::find()
                ->select([
                    'accounting_branch_program.id',
                    'sum(COALESCE(operatingExpenses.total, 0)) as total'
                ])
                ->leftJoin(['operatingExpenses' => '(
                    SELECT 
                        accounting_branch_program.id,
                        sum(COALESCE(accounting_expense_operating_expense.staff_salary, 0)) as staffSalaryTotal,
                        sum(COALESCE(accounting_expense_operating_expense.cash_pf, 0)) as cashPfTotal,
                        sum(COALESCE(accounting_expense_operating_expense.rent, 0)) as rentTotal,
                        sum(COALESCE(accounting_expense_operating_expense.utilities, 0)) as utilitiesTotal,
                        sum(COALESCE(accounting_expense_operating_expense.equipment_and_labor, 0)) as equipmentAndLaborTotal,
                        sum(COALESCE(accounting_expense_operating_expense.bir_and_docs, 0)) as bir_and_docsTotal,
                        sum(COALESCE(accounting_expense_operating_expense.marketing, 0)) as marketingTotal,
                        sum(
                            accounting_expense_operating_expense.staff_salary + 
                            accounting_expense_operating_expense.cash_pf + 
                            accounting_expense_operating_expense.rent + 
                            accounting_expense_operating_expense.utilities + 
                            accounting_expense_operating_expense.equipment_and_labor + 
                            accounting_expense_operating_expense.bir_and_docs + 
                            accounting_expense_operating_expense.marketing
                        ) as total,
                        DATE(accounting_expense.datetime) as newDate,
                        accounting_expense.amount_type as amountType
                    from accounting_expense_operating_expense
                    LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_operating_expense.id and accounting_expense.expense_type_id = 5
                    LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                    LEFT JOIN accounting_branch_program on accounting_branch_program.id = accounting_season.branch_program_id
                    GROUP BY newDate, accounting_branch_program.id
                )'], 'operatingExpenses.id = accounting_branch_program.id')
                ->groupBy(['accounting_branch_program.id'])
                ->createCommand()
                ->getRawSql();
        }

        return $sql;
    }

    function getOverallOperatingExpenseBySeasonRawSql($start = null, $end = null)
    {
        if(!is_null($start) && !is_null($end))
        {
            $sql = Season::find()
                ->select([
                    'accounting_season.branch_program_id',
                    'accounting_season.id',
                    'sum(COALESCE(operatingExpenses.total, 0)) as total'
                ])
                ->leftJoin(['operatingExpenses' => '(
                    SELECT 
                        accounting_season.id,
                        sum(COALESCE(accounting_expense_operating_expense.staff_salary, 0)) as staffSalaryTotal,
                        sum(COALESCE(accounting_expense_operating_expense.cash_pf, 0)) as cashPfTotal,
                        sum(COALESCE(accounting_expense_operating_expense.rent, 0)) as rentTotal,
                        sum(COALESCE(accounting_expense_operating_expense.utilities, 0)) as utilitiesTotal,
                        sum(COALESCE(accounting_expense_operating_expense.equipment_and_labor, 0)) as equipmentAndLaborTotal,
                        sum(COALESCE(accounting_expense_operating_expense.bir_and_docs, 0)) as bir_and_docsTotal,
                        sum(COALESCE(accounting_expense_operating_expense.marketing, 0)) as marketingTotal,
                        sum(
                            accounting_expense_operating_expense.staff_salary + 
                            accounting_expense_operating_expense.cash_pf + 
                            accounting_expense_operating_expense.rent + 
                            accounting_expense_operating_expense.utilities + 
                            accounting_expense_operating_expense.equipment_and_labor + 
                            accounting_expense_operating_expense.bir_and_docs + 
                            accounting_expense_operating_expense.marketing
                        ) as total,
                        DATE(accounting_expense.datetime) as newDate,
                        accounting_expense.amount_type as amountType
                    from accounting_expense_operating_expense
                    LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_operating_expense.id and accounting_expense.expense_type_id = 5
                    LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                    WHERE 
                        accounting_expense.datetime between "'.$start.' 00:00:00" and "'.$end.' 23:59:59"
                    GROUP BY newDate, accounting_season.id
                )'], 'operatingExpenses.id = accounting_season.id')
                ->groupBy(['accounting_season.id'])
                ->createCommand()
                ->getRawSql();
        }else{
            $sql = Season::find()
                ->select([
                    'accounting_season.id',
                    'sum(COALESCE(operatingExpenses.total, 0)) as total'
                ])
                ->leftJoin(['operatingExpenses' => '(
                    SELECT 
                        accounting_season.id,
                        sum(COALESCE(accounting_expense_operating_expense.staff_salary, 0)) as staffSalaryTotal,
                        sum(COALESCE(accounting_expense_operating_expense.cash_pf, 0)) as cashPfTotal,
                        sum(COALESCE(accounting_expense_operating_expense.rent, 0)) as rentTotal,
                        sum(COALESCE(accounting_expense_operating_expense.utilities, 0)) as utilitiesTotal,
                        sum(COALESCE(accounting_expense_operating_expense.equipment_and_labor, 0)) as equipmentAndLaborTotal,
                        sum(COALESCE(accounting_expense_operating_expense.bir_and_docs, 0)) as bir_and_docsTotal,
                        sum(COALESCE(accounting_expense_operating_expense.marketing, 0)) as marketingTotal,
                        sum(
                            accounting_expense_operating_expense.staff_salary + 
                            accounting_expense_operating_expense.cash_pf + 
                            accounting_expense_operating_expense.rent + 
                            accounting_expense_operating_expense.utilities + 
                            accounting_expense_operating_expense.equipment_and_labor + 
                            accounting_expense_operating_expense.bir_and_docs + 
                            accounting_expense_operating_expense.marketing
                        ) as total,
                        DATE(accounting_expense.datetime) as newDate,
                        accounting_expense.amount_type as amountType
                    from accounting_expense_operating_expense
                    LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_operating_expense.id and accounting_expense.expense_type_id = 5
                    LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                    GROUP BY newDate, accounting_season.id
                )'], 'operatingExpenses.id = accounting_season.id')
                ->groupBy(['accounting_season.id'])
                ->createCommand()
                ->getRawSql();
        }

        return $sql;
    }

    function getOverallBudgetProposalByBranchProgramRawSql($start = null, $end = null)
    {
        if(!is_null($start) && !is_null($end))
        {
            $sql = BranchProgram::find()
                ->select([
                    'accounting_branch_program.id',
                    'sum(COALESCE(budgetProposals.total, 0)) as total'
                ])
                ->leftJoin(['budgetProposals' => '(
                    SELECT 
                        accounting_branch_program.id,
                        sum(COALESCE(particulars.total, 0)) as total,
                        DATE(accounting_income.datetime) as newDate,
                        accounting_income.amount_type as amountType
                    from accounting_income_budget_proposal
                    LEFT JOIN (
                        SELECT
                            accounting_budget_proposal_particular.budget_proposal_id,
                            sum(accounting_budget_proposal_particular.amount) as total
                        from accounting_budget_proposal_particular
                        LEFT JOIN accounting_income_budget_proposal on accounting_income_budget_proposal.id = accounting_budget_proposal_particular.budget_proposal_id
                        WHERE accounting_budget_proposal_particular.approval_status = "Approved"
                        GROUP BY accounting_budget_proposal_particular.budget_proposal_id
                    ) particulars on particulars.budget_proposal_id = accounting_income_budget_proposal.id
                    LEFT JOIN accounting_income on accounting_income.income_id = accounting_income_budget_proposal.id and accounting_income.income_type_id = 3
                    LEFT JOIN accounting_season on accounting_season.id = accounting_income_budget_proposal.season_id
                    LEFT JOIN accounting_branch_program on accounting_branch_program.id = accounting_season.branch_program_id
                    WHERE 
                        accounting_income.datetime between "'.$start.' 00:00:00" and "'.$end.' 23:59:59" and 
                        accounting_income_budget_proposal.approval_status = "Approved"
                    GROUP BY newDate, accounting_branch_program.id
                )'], 'budgetProposals.id = accounting_branch_program.id')
                ->groupBy(['accounting_branch_program.id'])
                ->createCommand()
                ->getRawSql();
        }else{
            $sql = BranchProgram::find()
                ->select([
                    'accounting_branch_program.id',
                    'sum(COALESCE(budgetProposals.total, 0)) as total'
                ])
                ->leftJoin(['budgetProposals' => '(
                    SELECT 
                        accounting_branch_program.id,
                        sum(COALESCE(particulars.total, 0)) as total,
                        DATE(accounting_income.datetime) as newDate,
                        accounting_income.amount_type as amountType
                    from accounting_income_budget_proposal
                    LEFT JOIN (
                        SELECT
                            accounting_budget_proposal_particular.budget_proposal_id,
                            sum(accounting_budget_proposal_particular.amount) as total
                        from accounting_budget_proposal_particular
                        LEFT JOIN accounting_income_budget_proposal on accounting_income_budget_proposal.id = accounting_budget_proposal_particular.budget_proposal_id
                        WHERE accounting_budget_proposal_particular.approval_status = "Approved"
                        GROUP BY accounting_budget_proposal_particular.budget_proposal_id
                    ) particulars on particulars.budget_proposal_id = accounting_income_budget_proposal.id
                    LEFT JOIN accounting_income on accounting_income.income_id = accounting_income_budget_proposal.id and accounting_income.income_type_id = 3
                    LEFT JOIN accounting_branch_program on accounting_branch_program.branch_id = accounting_income.branch_id and accounting_branch_program.program_id = accounting_income.program_id
                    WHERE 
                        accounting_income_budget_proposal.approval_status = "Approved"
                    GROUP BY newDate, accounting_branch_program.id
                )'], 'budgetProposals.id = accounting_branch_program.id')
                ->groupBy(['accounting_branch_program.id'])
                ->createCommand()
                ->getRawSql();
        }

        return $sql;
    }

    function getOverallBudgetProposalBySeasonRawSql($start = null, $end = null)
    {
        if(!is_null($start) && !is_null($end))
        {
            $sql = Season::find()
                ->select([
                    'accounting_season.branch_program_id',
                    'accounting_season.id',
                    'sum(COALESCE(budgetProposals.total, 0)) as total'
                ])
                ->leftJoin(['budgetProposals' => '(
                    SELECT 
                        accounting_season.id,
                        sum(COALESCE(particulars.total, 0)) as total,
                        DATE(accounting_income.datetime) as newDate,
                        accounting_income.amount_type as amountType
                    from accounting_income_budget_proposal
                    LEFT JOIN (
                        SELECT
                            accounting_budget_proposal_particular.budget_proposal_id,
                            sum(accounting_budget_proposal_particular.amount) as total
                        from accounting_budget_proposal_particular
                        LEFT JOIN accounting_income_budget_proposal on accounting_income_budget_proposal.id = accounting_budget_proposal_particular.budget_proposal_id
                        WHERE accounting_budget_proposal_particular.approval_status = "Approved"
                        GROUP BY accounting_budget_proposal_particular.budget_proposal_id
                    ) particulars on particulars.budget_proposal_id = accounting_income_budget_proposal.id
                    LEFT JOIN accounting_income on accounting_income.income_id = accounting_income_budget_proposal.id and accounting_income.income_type_id = 3
                    LEFT JOIN accounting_season on accounting_season.id = accounting_income_budget_proposal.season_id
                    WHERE 
                        accounting_income.datetime between "'.$start.' 00:00:00" and "'.$end.' 23:59:59" and 
                        accounting_income_budget_proposal.approval_status = "Approved"
                    GROUP BY newDate, accounting_season.id
                )'], 'budgetProposals.id = accounting_season.id')
                ->groupBy(['accounting_season.id'])
                ->createCommand()
                ->getRawSql();
        }else{
            $sql = Season::find()
                ->select([
                    'accounting_season.id',
                    'sum(COALESCE(budgetProposals.total, 0)) as total'
                ])
                ->leftJoin(['budgetProposals' => '(
                    SELECT 
                        accounting_season.id,
                        sum(COALESCE(particulars.total, 0)) as total,
                        DATE(accounting_income.datetime) as newDate,
                        accounting_income.amount_type as amountType
                    from accounting_income_budget_proposal
                    LEFT JOIN (
                        SELECT
                            accounting_budget_proposal_particular.budget_proposal_id,
                            sum(accounting_budget_proposal_particular.amount) as total
                        from accounting_budget_proposal_particular
                        LEFT JOIN accounting_income_budget_proposal on accounting_income_budget_proposal.id = accounting_budget_proposal_particular.budget_proposal_id
                        WHERE accounting_budget_proposal_particular.approval_status = "Approved"
                        GROUP BY accounting_budget_proposal_particular.budget_proposal_id
                    ) particulars on particulars.budget_proposal_id = accounting_income_budget_proposal.id
                    LEFT JOIN accounting_income on accounting_income.income_id = accounting_income_budget_proposal.id and accounting_income.income_type_id = 3
                    LEFT JOIN accounting_season on accounting_season.id = accounting_income_budget_proposal.season_id
                    WHERE 
                        accounting_income_budget_proposal.approval_status = "Approved"
                    GROUP BY newDate, accounting_season.id
                )'], 'budgetProposals.id = accounting_season.id')
                ->groupBy(['accounting_season.id'])
                ->createCommand()
                ->getRawSql();
        }

        return $sql;
    }

    function getOverallBranchTransferByBranchProgramRawSql($start = null, $end = null)
    {
        if(!is_null($start) && !is_null($end))
        {
            $sql = BranchProgram::find()
                ->select([
                    'accounting_branch_program.id',
                    'sum(COALESCE(branchTransfers.total, 0)) as total'
                ])
                ->leftJoin(['branchTransfers' => '(
                    SELECT 
                        accounting_branch_program.id,
                        sum(COALESCE(particulars.total, 0)) as total,
                        DATE(accounting_expense.datetime) as newDate,
                        accounting_expense.amount_type as amountType
                    from accounting_expense_branch_transfer
                    LEFT JOIN (
                        SELECT
                            accounting_budget_proposal_particular.budget_proposal_id,
                            sum(accounting_budget_proposal_particular.amount) as total
                        from accounting_budget_proposal_particular
                        LEFT JOIN accounting_income_budget_proposal on accounting_income_budget_proposal.id = accounting_budget_proposal_particular.budget_proposal_id
                        WHERE accounting_budget_proposal_particular.approval_status = "Approved"
                        GROUP BY accounting_budget_proposal_particular.budget_proposal_id
                    ) particulars on particulars.budget_proposal_id = accounting_expense_branch_transfer.budget_proposal_id
                    LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6
                    LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                    LEFT JOIN accounting_branch_program on accounting_branch_program.id = accounting_season.branch_program_id
                    WHERE 
                        accounting_expense.datetime between "'.$start.' 00:00:00" and "'.$end.' 23:59:59"
                    GROUP BY newDate, accounting_branch_program.id
                )'], 'branchTransfers.id = accounting_branch_program.id')
                ->groupBy(['accounting_branch_program.id'])
                ->createCommand()
                ->getRawSql();
        }else{
            $sql = BranchProgram::find()
                ->select([
                    'accounting_branch_program.id',
                    'sum(COALESCE(branchTransfers.total, 0)) as total'
                ])
                ->leftJoin(['branchTransfers' => '(
                    SELECT 
                        accounting_branch_program.id,
                        sum(COALESCE(particulars.total, 0)) as total,
                        DATE(accounting_expense.datetime) as newDate,
                        accounting_expense.amount_type as amountType
                    from accounting_expense_branch_transfer
                    LEFT JOIN (
                        SELECT
                            accounting_budget_proposal_particular.budget_proposal_id,
                            sum(accounting_budget_proposal_particular.amount) as total
                        from accounting_budget_proposal_particular
                        LEFT JOIN accounting_income_budget_proposal on accounting_income_budget_proposal.id = accounting_budget_proposal_particular.budget_proposal_id
                        WHERE accounting_budget_proposal_particular.approval_status = "Approved"
                        GROUP BY accounting_budget_proposal_particular.budget_proposal_id
                    ) particulars on particulars.budget_proposal_id = accounting_expense_branch_transfer.budget_proposal_id
                    LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6
                    LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                    LEFT JOIN accounting_branch_program on accounting_branch_program.id = accounting_season.branch_program_id
                    GROUP BY newDate, accounting_branch_program.id
                )'], 'branchTransfers.id = accounting_branch_program.id')
                ->groupBy(['accounting_branch_program.id'])
                ->createCommand()
                ->getRawSql();
        }

        return $sql;
    }

    function getOverallBranchTransferBySeasonRawSql($start = null, $end = null)
    {
        if(!is_null($start) && !is_null($end))
        {
            $sql = Season::find()
                ->select([
                    'accounting_season.branch_program_id',
                    'accounting_season.id',
                    'sum(COALESCE(branchTransfers.total, 0)) as total'
                ])
                ->leftJoin(['branchTransfers' => '(
                    SELECT 
                        accounting_season.id,
                        sum(COALESCE(particulars.total, 0)) as total,
                        DATE(accounting_expense.datetime) as newDate,
                        accounting_expense.amount_type as amountType
                    from accounting_expense_branch_transfer
                    LEFT JOIN (
                        SELECT
                            accounting_budget_proposal_particular.budget_proposal_id,
                            sum(accounting_budget_proposal_particular.amount) as total
                        from accounting_budget_proposal_particular
                        LEFT JOIN accounting_income_budget_proposal on accounting_income_budget_proposal.id = accounting_budget_proposal_particular.budget_proposal_id
                        WHERE accounting_budget_proposal_particular.approval_status = "Approved"
                        GROUP BY accounting_budget_proposal_particular.budget_proposal_id
                    ) particulars on particulars.budget_proposal_id = accounting_expense_branch_transfer.budget_proposal_id
                    LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6
                    LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                    WHERE 
                        accounting_expense.datetime between "'.$start.' 00:00:00" and "'.$end.' 23:59:59"
                    GROUP BY newDate, accounting_season.id
                )'], 'branchTransfers.id = accounting_season.id')
                ->groupBy(['accounting_season.id'])
                ->createCommand()
                ->getRawSql();
        }else{
            $sql = Season::find()
                ->select([
                    'accounting_season.id',
                    'sum(COALESCE(branchTransfers.total, 0)) as total'
                ])
                ->leftJoin(['branchTransfers' => '(
                    SELECT 
                        accounting_season.id,
                        sum(COALESCE(particulars.total, 0)) as total,
                        DATE(accounting_expense.datetime) as newDate,
                        accounting_expense.amount_type as amountType
                    from accounting_expense_branch_transfer
                    LEFT JOIN (
                        SELECT
                            accounting_budget_proposal_particular.budget_proposal_id,
                            sum(accounting_budget_proposal_particular.amount) as total
                        from accounting_budget_proposal_particular
                        LEFT JOIN accounting_income_budget_proposal on accounting_income_budget_proposal.id = accounting_budget_proposal_particular.budget_proposal_id
                        WHERE accounting_budget_proposal_particular.approval_status = "Approved"
                        GROUP BY accounting_budget_proposal_particular.budget_proposal_id
                    ) particulars on particulars.budget_proposal_id = accounting_expense_branch_transfer.budget_proposal_id
                    LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6
                    LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                    GROUP BY newDate, accounting_season.id
                )'], 'branchTransfers.id = accounting_season.id')
                ->groupBy(['accounting_season.id'])
                ->createCommand()
                ->getRawSql();
        }

        return $sql;
    }

    function getIncomeEnrolmentByBranchProgramRawSql($start = null, $end = null, $amountType)
    {
        $con = $amountType == "Cash" ? '=' : '<>';
        if(!is_null($start) && !is_null($end))
        {
            $sql = BranchProgram::find()
            ->select([
                'accounting_branch_program.id',
                'sum(COALESCE(incomeEnrolments.total, 0)) as total'
            ])
            ->leftJoin(['incomeEnrolments' => '(
                SELECT 
                    accounting_branch_program.id,
                    sum(COALESCE(accounting_income_enrolment.amount, 0)) as total,
                    DATE(accounting_income.datetime) as newDate,
                    accounting_income.amount_type as amountType
                from accounting_income_enrolment
                LEFT JOIN accounting_income on accounting_income.income_id = accounting_income_enrolment.id and accounting_income.income_type_id = 1
                LEFT JOIN accounting_season on accounting_season.id = accounting_income_enrolment.season_id
                LEFT JOIN accounting_branch_program on accounting_branch_program.id = accounting_season.branch_program_id
                WHERE 
                    accounting_income.datetime between "'.$start.' 00:00:00" and "'.$end.' 23:59:59" and 
                    accounting_income.amount_type '.$con.' "Cash"
                GROUP BY newDate, accounting_branch_program.id
            )'], 'incomeEnrolments.id = accounting_branch_program.id')
            ->groupBy(['accounting_branch_program.id'])
            ->createCommand()
            ->getRawSql();

        }else{
            $sql = BranchProgram::find()
            ->select([
                'accounting_branch_program.id',
                'sum(COALESCE(incomeEnrolments.total, 0)) as total'
            ])
            ->leftJoin(['incomeEnrolments' => '(
                SELECT 
                    accounting_branch_program.id,
                    sum(COALESCE(accounting_income_enrolment.amount, 0)) as total,
                    DATE(accounting_income.datetime) as newDate,
                    accounting_income.amount_type as amountType
                from accounting_income_enrolment
                LEFT JOIN accounting_income on accounting_income.income_id = accounting_income_enrolment.id and accounting_income.income_type_id = 1
                LEFT JOIN accounting_season on accounting_season.id = accounting_income_enrolment.season_id
                LEFT JOIN accounting_branch_program on accounting_branch_program.id = accounting_season.branch_program_id
                WHERE 
                    accounting_income.amount_type '.$con.' "Cash"
                GROUP BY newDate, accounting_branch_program.id
            )'], 'incomeEnrolments.id = accounting_branch_program.id')
            ->groupBy(['accounting_branch_program.id'])
            ->createCommand()
            ->getRawSql();
        }

        return $sql;
    }

    function getIncomeEnrolmentBySeasonRawSql($start = null, $end = null, $amountType)
    {
        $con = $amountType == "Cash" ? '=' : '<>';
        if(!is_null($start) && !is_null($end))
        {
            $sql = Season::find()
                ->select([
                    'accounting_season.branch_program_id',
                    'accounting_season.id',
                    'sum(COALESCE(incomeEnrolments.total, 0)) as total'
                ])
                ->leftJoin(['incomeEnrolments' => '(
                    SELECT 
                        accounting_season.id,
                        sum(COALESCE(accounting_income_enrolment.amount, 0)) as total,
                        DATE(accounting_income.datetime) as newDate,
                        accounting_income.amount_type as amountType
                    from accounting_income_enrolment
                    LEFT JOIN accounting_income on accounting_income.income_id = accounting_income_enrolment.id and accounting_income.income_type_id = 1
                    LEFT JOIN accounting_season on accounting_season.id = accounting_income_enrolment.season_id
                    WHERE 
                        accounting_income.datetime between "'.$start.' 00:00:00" and "'.$end.' 23:59:59" and 
                        accounting_income.amount_type '.$con.' "Cash"
                    GROUP BY newDate, accounting_season.id
                )'], 'incomeEnrolments.id = accounting_season.id')
                ->groupBy(['accounting_season.id'])
                ->createCommand()
                ->getRawSql();
        }else{
            $sql = Season::find()
                ->select([
                    'accounting_season.branch_program_id',
                    'accounting_season.id',
                    'sum(COALESCE(incomeEnrolments.total, 0)) as total'
                ])
                ->leftJoin(['incomeEnrolments' => '(
                    SELECT 
                        accounting_season.id,
                        sum(COALESCE(accounting_income_enrolment.amount, 0)) as total,
                        DATE(accounting_income.datetime) as newDate,
                        accounting_income.amount_type as amountType
                    from accounting_income_enrolment
                    LEFT JOIN accounting_income on accounting_income.income_id = accounting_income_enrolment.id and accounting_income.income_type_id = 1
                    LEFT JOIN accounting_season on accounting_season.id = accounting_income_enrolment.season_id
                    WHERE 
                        accounting_income.amount_type '.$con.' "Cash"
                    GROUP BY newDate, accounting_season.id
                )'], 'incomeEnrolments.id = accounting_season.id')
                ->groupBy(['accounting_season.id'])
                ->createCommand()
                ->getRawSql();
        }

        return $sql;
    }

    function getIncomeFreebieByBranchProgramRawSql($start = null, $end = null, $amountType)
    {
        $con = $amountType == "Cash" ? '=' : '<>';
        if(!is_null($start) && !is_null($end))
        {
            $sql = BranchProgram::find()
                ->select([
                    'accounting_branch_program.id',
                    'sum(COALESCE(freebies.total, 0)) as total'
                ])
                ->leftJoin(['freebies' => '(
                    SELECT 
                        accounting_branch_program.id,
                        sum(COALESCE(accounting_income_freebies_and_icons.amount, 0)) as total,
                        DATE(accounting_income.datetime) as newDate,
                        accounting_income.amount_type as amountType
                    from accounting_income_freebies_and_icons
                    LEFT JOIN accounting_income on accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2
                    LEFT JOIN accounting_season on accounting_season.id = accounting_income_freebies_and_icons.season_id
                    LEFT JOIN accounting_branch_program on accounting_branch_program.id = accounting_season.branch_program_id
                    WHERE 
                        accounting_income.datetime between "'.$start.' 00:00:00" and "'.$end.' 23:59:59" and 
                        accounting_income.amount_type '.$con.' "Cash"
                    GROUP BY newDate, accounting_branch_program.id
                )'], 'freebies.id = accounting_branch_program.id')
                ->groupBy(['accounting_branch_program.id'])
                ->createCommand()
                ->getRawSql();
        }else{
            $sql = BranchProgram::find()
                ->select([
                    'accounting_branch_program.id',
                    'sum(COALESCE(freebies.total, 0)) as total'
                ])
                ->leftJoin(['freebies' => '(
                    SELECT 
                        accounting_branch_program.id,
                        sum(COALESCE(accounting_income_freebies_and_icons.amount, 0)) as total,
                        DATE(accounting_income.datetime) as newDate,
                        accounting_income.amount_type as amountType
                    from accounting_income_freebies_and_icons
                    LEFT JOIN accounting_income on accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2
                    LEFT JOIN accounting_season on accounting_season.id = accounting_income_freebies_and_icons.season_id
                    LEFT JOIN accounting_branch_program on accounting_branch_program.id = accounting_season.branch_program_id
                    WHERE 
                        accounting_income.amount_type '.$con.' "Cash"
                    GROUP BY newDate, accounting_branch_program.id
                )'], 'freebies.id = accounting_branch_program.id')
                ->groupBy(['accounting_branch_program.id'])
                ->createCommand()
                ->getRawSql();
        }

        return $sql;
    }

    function getIncomeFreebieBySeasonRawSql($start = null, $end = null, $amountType)
    {
        $con = $amountType == "Cash" ? '=' : '<>';
        if(!is_null($start) && !is_null($end))
        {
            $sql = Season::find()
                ->select([
                    'accounting_season.branch_program_id',
                    'accounting_season.id',
                    'sum(COALESCE(freebies.total, 0)) as total'
                ])
                ->leftJoin(['freebies' => '(
                    SELECT 
                        accounting_season.id,
                        sum(COALESCE(accounting_income_freebies_and_icons.amount, 0)) as total,
                        DATE(accounting_income.datetime) as newDate,
                        accounting_income.amount_type as amountType
                    from accounting_income_freebies_and_icons
                    LEFT JOIN accounting_income on accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2
                    LEFT JOIN accounting_season on accounting_season.id = accounting_income_freebies_and_icons.season_id
                    WHERE 
                        accounting_income.datetime between "'.$start.' 00:00:00" and "'.$end.' 23:59:59" and 
                        accounting_income.amount_type '.$con.' "Cash"
                    GROUP BY newDate, accounting_season.id
                )'], 'freebies.id = accounting_season.id')
                ->groupBy(['accounting_season.id'])
                ->createCommand()
                ->getRawSql();
        }else{
            $sql = Season::find()
                ->select([
                    'accounting_season.branch_program_id',
                    'accounting_season.id',
                    'sum(COALESCE(freebies.total, 0)) as total'
                ])
                ->leftJoin(['freebies' => '(
                    SELECT 
                        accounting_season.id,
                        sum(COALESCE(accounting_income_freebies_and_icons.amount, 0)) as total,
                        DATE(accounting_income.datetime) as newDate,
                        accounting_income.amount_type as amountType
                    from accounting_income_freebies_and_icons
                    LEFT JOIN accounting_income on accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2
                    LEFT JOIN accounting_season on accounting_season.id = accounting_income_freebies_and_icons.season_id
                    WHERE
                        accounting_income.amount_type '.$con.' "Cash"
                    GROUP BY newDate, accounting_season.id
                )'], 'freebies.id = accounting_season.id')
                ->groupBy(['accounting_season.id'])
                ->createCommand()
                ->getRawSql();
        }

        return $sql;        
    }

    function getPettyExpenseByBranchProgramRawSql($start = null, $end = null, $amountType)
    {
        $con = $amountType == "Cash" ? '=' : '<>';
        if(!is_null($start) && !is_null($end))
        {
            $sql = BranchProgram::find()
                ->select([
                    'accounting_branch_program.id',
                    'sum(COALESCE(pettyExpense.total, 0)) as total'
                ])
                ->leftJoin(['pettyExpense' => '(
                    SELECT 
                        accounting_branch_program.id,
                        sum(COALESCE(accounting_expense_petty_expense.food, 0)) as foodTotal,
                        sum(COALESCE(accounting_expense_petty_expense.supplies, 0)) as supplyTotal,
                        sum(COALESCE(accounting_expense_petty_expense.load, 0)) as loadTotal,
                        sum(COALESCE(accounting_expense_petty_expense.fare, 0)) as fareTotal,
                        sum(accounting_expense_petty_expense.food + accounting_expense_petty_expense.supplies + accounting_expense_petty_expense.load + accounting_expense_petty_expense.fare) as total,
                        DATE(accounting_expense.datetime) as newDate,
                        accounting_expense.amount_type as amountType
                    from accounting_expense_petty_expense
                    LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_petty_expense.id and accounting_expense.expense_type_id = 1
                    LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                    LEFT JOIN accounting_branch_program on accounting_branch_program.id = accounting_season.branch_program_id
                    WHERE 
                        accounting_expense.datetime between "'.$start.' 00:00:00" and "'.$end.' 23:59:59" and 
                        accounting_expense.amount_type '.$con.' "Cash"
                    GROUP BY newDate, accounting_branch_program.id
                )'], 'pettyExpense.id = accounting_branch_program.id')
                ->groupBy(['accounting_branch_program.id'])
                ->createCommand()
                ->getRawSql();
        }else{
            $sql = BranchProgram::find()
                ->select([
                    'accounting_branch_program.id',
                    'sum(COALESCE(pettyExpense.total, 0)) as total'
                ])
                ->leftJoin(['pettyExpense' => '(
                    SELECT 
                        accounting_branch_program.id,
                        sum(COALESCE(accounting_expense_petty_expense.food, 0)) as foodTotal,
                        sum(COALESCE(accounting_expense_petty_expense.supplies, 0)) as supplyTotal,
                        sum(COALESCE(accounting_expense_petty_expense.load, 0)) as loadTotal,
                        sum(COALESCE(accounting_expense_petty_expense.fare, 0)) as fareTotal,
                        sum(accounting_expense_petty_expense.food + accounting_expense_petty_expense.supplies + accounting_expense_petty_expense.load + accounting_expense_petty_expense.fare) as total,
                        DATE(accounting_expense.datetime) as newDate,
                        accounting_expense.amount_type as amountType
                    from accounting_expense_petty_expense
                    LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_petty_expense.id and accounting_expense.expense_type_id = 1
                    LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                    LEFT JOIN accounting_branch_program on accounting_branch_program.id = accounting_season.branch_program_id
                    WHERE 
                        accounting_expense.amount_type '.$con.' "Cash"
                    GROUP BY newDate, accounting_branch_program.id
                )'], 'pettyExpense.id = accounting_branch_program.id')
                ->groupBy(['accounting_branch_program.id'])
                ->createCommand()
                ->getRawSql();
        }

        return $sql;
    }

    function getPettyExpenseBySeasonRawSql($start = null, $end = null, $amountType)
    {
        $con = $amountType == "Cash" ? '=' : '<>';
        if(!is_null($start) && !is_null($end))
        {
            $sql = Season::find()
                ->select([
                    'accounting_season.branch_program_id',
                    'accounting_season.id',
                    'sum(COALESCE(pettyExpense.total, 0)) as total'
                ])
                ->leftJoin(['pettyExpense' => '(
                    SELECT 
                        accounting_season.id,
                        sum(COALESCE(accounting_expense_petty_expense.food, 0)) as foodTotal,
                        sum(COALESCE(accounting_expense_petty_expense.supplies, 0)) as supplyTotal,
                        sum(COALESCE(accounting_expense_petty_expense.load, 0)) as loadTotal,
                        sum(COALESCE(accounting_expense_petty_expense.fare, 0)) as fareTotal,
                        sum(accounting_expense_petty_expense.food + accounting_expense_petty_expense.supplies + accounting_expense_petty_expense.load + accounting_expense_petty_expense.fare) as total,
                        DATE(accounting_expense.datetime) as newDate,
                        accounting_expense.amount_type as amountType
                    from accounting_expense_petty_expense
                    LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_petty_expense.id and accounting_expense.expense_type_id = 1
                    LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                    WHERE 
                        accounting_expense.datetime between "'.$start.' 00:00:00" and "'.$end.' 23:59:59" and 
                        accounting_expense.amount_type '.$con.' "Cash"
                    GROUP BY newDate, accounting_season.id
                )'], 'pettyExpense.id = accounting_season.id')
                ->groupBy(['accounting_season.id'])
                ->createCommand()
                ->getRawSql();
        }else{
           $sql = Season::find()
                ->select([
                    'accounting_season.branch_program_id',
                    'accounting_season.id',
                    'sum(COALESCE(pettyExpense.total, 0)) as total'
                ])
                ->leftJoin(['pettyExpense' => '(
                    SELECT 
                        accounting_season.id,
                        sum(COALESCE(accounting_expense_petty_expense.food, 0)) as foodTotal,
                        sum(COALESCE(accounting_expense_petty_expense.supplies, 0)) as supplyTotal,
                        sum(COALESCE(accounting_expense_petty_expense.load, 0)) as loadTotal,
                        sum(COALESCE(accounting_expense_petty_expense.fare, 0)) as fareTotal,
                        sum(accounting_expense_petty_expense.food + accounting_expense_petty_expense.supplies + accounting_expense_petty_expense.load + accounting_expense_petty_expense.fare) as total,
                        DATE(accounting_expense.datetime) as newDate,
                        accounting_expense.amount_type as amountType
                    from accounting_expense_petty_expense
                    LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_petty_expense.id and accounting_expense.expense_type_id = 1
                    LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                    WHERE 
                        accounting_expense.amount_type '.$con.' "Cash"
                    GROUP BY newDate, accounting_season.id
                )'], 'pettyExpense.id = accounting_season.id')
                ->groupBy(['accounting_season.id'])
                ->createCommand()
                ->getRawSql();
        }

        return $sql;
    }

    function getPhotocopyExpenseByBranchProgramRawSql($start = null, $end = null, $amountType)
    {
        $con = $amountType == "Cash" ? '=' : '<>';
        if(!is_null($start) && !is_null($end))
        {
            $sql = BranchProgram::find()
                    ->select([
                        'accounting_branch_program.id',
                        'sum(COALESCE(photocopyExpense.total, 0)) as total'
                    ])
                    ->leftJoin(['photocopyExpense' => '(
                        SELECT 
                            accounting_branch_program.id,
                            sum(COALESCE(accounting_expense_photocopy_expense.total_amount, 0)) as total,
                            DATE(accounting_expense.datetime) as newDate,
                            accounting_expense.amount_type as amountType
                        from accounting_expense_photocopy_expense
                        LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2
                        LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                        LEFT JOIN accounting_branch_program on accounting_branch_program.id = accounting_season.branch_program_id
                        WHERE 
                            accounting_expense.datetime between "'.$start.' 00:00:00" and "'.$end.' 23:59:59" and 
                            accounting_expense.amount_type '.$con.' "Cash"
                        GROUP BY newDate, accounting_branch_program.id
                    )'], 'photocopyExpense.id = accounting_branch_program.id')
                    ->groupBy(['accounting_branch_program.id'])
                    ->createCommand()
                    ->getRawSql();
        }else{
            $sql = BranchProgram::find()
                    ->select([
                        'accounting_branch_program.id',
                        'sum(COALESCE(photocopyExpense.total, 0)) as total'
                    ])
                    ->leftJoin(['photocopyExpense' => '(
                        SELECT 
                            accounting_branch_program.id,
                            sum(COALESCE(accounting_expense_photocopy_expense.total_amount, 0)) as total,
                            DATE(accounting_expense.datetime) as newDate,
                            accounting_expense.amount_type as amountType
                        from accounting_expense_photocopy_expense
                        LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2
                        LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                        LEFT JOIN accounting_branch_program on accounting_branch_program.id = accounting_season.branch_program_id
                        WHERE 
                            accounting_expense.amount_type '.$con.' "Cash"
                        GROUP BY newDate, accounting_branch_program.id
                    )'], 'photocopyExpense.id = accounting_branch_program.id')
                    ->groupBy(['accounting_branch_program.id'])
                    ->createCommand()
                    ->getRawSql();
        }

        return $sql;
    }

    function getPhotocopyExpenseBySeasonRawSql($start = null, $end = null, $amountType)
    {
        $con = $amountType == "Cash" ? '=' : '<>';
        if(!is_null($start) && !is_null($end))
        {
            $sql = Season::find()
                    ->select([
                        'accounting_season.branch_program_id',
                        'accounting_season.id',
                        'sum(COALESCE(photocopyExpense.total, 0)) as total'
                    ])
                    ->leftJoin(['photocopyExpense' => '(
                        SELECT 
                            accounting_season.id,
                            sum(COALESCE(accounting_expense_photocopy_expense.total_amount, 0)) as total,
                            DATE(accounting_expense.datetime) as newDate,
                            accounting_expense.amount_type as amountType
                        from accounting_expense_photocopy_expense
                        LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2
                        LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                        WHERE 
                            accounting_expense.datetime between "'.$start.' 00:00:00" and "'.$end.' 23:59:59" and 
                            accounting_expense.amount_type '.$con.' "Cash"
                        GROUP BY newDate, accounting_season.id
                    )'], 'photocopyExpense.id = accounting_season.id')
                    ->groupBy(['accounting_season.id'])
                    ->createCommand()
                    ->getRawSql();
        }else{
            $sql = Season::find()
                    ->select([
                        'accounting_season.branch_program_id',
                        'accounting_season.id',
                        'sum(COALESCE(photocopyExpense.total, 0)) as total'
                    ])
                    ->leftJoin(['photocopyExpense' => '(
                        SELECT 
                            accounting_season.id,
                            sum(COALESCE(accounting_expense_photocopy_expense.total_amount, 0)) as total,
                            DATE(accounting_expense.datetime) as newDate,
                            accounting_expense.amount_type as amountType
                        from accounting_expense_photocopy_expense
                        LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2
                        LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                        WHERE 
                            accounting_expense.amount_type '.$con.' "Cash"
                        GROUP BY newDate, accounting_season.id
                    )'], 'photocopyExpense.id = accounting_season.id')
                    ->groupBy(['accounting_season.id'])
                    ->createCommand()
                    ->getRawSql();
        }

        return $sql;
    }

    function getOtherExpenseByBranchProgramRawSql($start = null, $end = null, $amountType)
    {
        $con = $amountType == "Cash" ? '=' : '<>';
        if(!is_null($start) && !is_null($end))
        {
            $sql = BranchProgram::find()
                ->select([
                    'accounting_branch_program.id',
                    'sum(COALESCE(otherExpense.total, 0)) as total'
                ])
                ->leftJoin(['otherExpense' => '(
                    SELECT 
                        accounting_branch_program.id,
                        sum(COALESCE(accounting_expense_other_expense.amount, 0)) as total,
                        DATE(accounting_expense.datetime) as newDate,
                        accounting_expense.amount_type as amountType
                    from accounting_expense_other_expense
                    LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_other_expense.id and accounting_expense.expense_type_id = 3
                    LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                    LEFT JOIN accounting_branch_program on accounting_branch_program.id = accounting_season.branch_program_id
                    WHERE 
                        accounting_expense.datetime between "'.$start.' 00:00:00" and "'.$end.' 23:59:59" and 
                        accounting_expense.amount_type '.$con.' "Cash"
                    GROUP BY newDate, accounting_branch_program.id
                )'], 'otherExpense.id = accounting_branch_program.id')
                ->groupBy(['accounting_branch_program.id'])
                ->createCommand()
                ->getRawSql();
        }else{
            $sql = BranchProgram::find()
                ->select([
                    'accounting_branch_program.id',
                    'sum(COALESCE(otherExpense.total, 0)) as total'
                ])
                ->leftJoin(['otherExpense' => '(
                    SELECT 
                        accounting_branch_program.id,
                        sum(COALESCE(accounting_expense_other_expense.amount, 0)) as total,
                        DATE(accounting_expense.datetime) as newDate,
                        accounting_expense.amount_type as amountType
                    from accounting_expense_other_expense
                    LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_other_expense.id and accounting_expense.expense_type_id = 3
                    LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                    LEFT JOIN accounting_branch_program on accounting_branch_program.id = accounting_season.branch_program_id
                    WHERE 
                        accounting_expense.amount_type '.$con.' "Cash"
                    GROUP BY newDate, accounting_branch_program.id
                )'], 'otherExpense.id = accounting_branch_program.id')
                ->groupBy(['accounting_branch_program.id'])
                ->createCommand()
                ->getRawSql();
        }

        return $sql;
    }

    function getOtherExpenseBySeasonRawSql($start = null, $end = null, $amountType)
    {
        $con = $amountType == "Cash" ? '=' : '<>';
        if(!is_null($start) && !is_null($end))
        {
            $sql = Season::find()
                ->select([
                    'accounting_season.branch_program_id',
                    'accounting_season.id',
                    'sum(COALESCE(otherExpense.total, 0)) as total'
                ])
                ->leftJoin(['otherExpense' => '(
                    SELECT 
                        accounting_season.id,
                        sum(COALESCE(accounting_expense_other_expense.amount, 0)) as total,
                        DATE(accounting_expense.datetime) as newDate,
                        accounting_expense.amount_type as amountType
                    from accounting_expense_other_expense
                    LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_other_expense.id and accounting_expense.expense_type_id = 3
                    LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                    WHERE 
                        accounting_expense.datetime between "'.$start.' 00:00:00" and "'.$end.' 23:59:59" and
                        accounting_expense.amount_type '.$con.' "Cash"
                    GROUP BY newDate, accounting_season.id
                )'], 'otherExpense.id = accounting_season.id')
                ->groupBy(['accounting_season.id'])
                ->createCommand()
                ->getRawSql();
        }else{
           $sql = Season::find()
                ->select([
                    'accounting_season.branch_program_id',
                    'accounting_season.id',
                    'sum(COALESCE(otherExpense.total, 0)) as total'
                ])
                ->leftJoin(['otherExpense' => '(
                    SELECT 
                        accounting_season.id,
                        sum(COALESCE(accounting_expense_other_expense.amount, 0)) as total,
                        DATE(accounting_expense.datetime) as newDate,
                        accounting_expense.amount_type as amountType
                    from accounting_expense_other_expense
                    LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_other_expense.id and accounting_expense.expense_type_id = 3
                    LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                    WHERE
                        accounting_expense.amount_type '.$con.' "Cash"
                    GROUP BY newDate, accounting_season.id
                )'], 'otherExpense.id = accounting_season.id')
                ->groupBy(['accounting_season.id'])
                ->createCommand()
                ->getRawSql();
        }

        return $sql;
    }

    function getBankDepositByBranchProgramRawSql($start = null, $end = null, $amountType)
    {
        $con = $amountType == "Cash" ? '=' : '<>';
        if(!is_null($start) && !is_null($end))
        {
            $sql = BranchProgram::find()
                ->select([
                    'accounting_branch_program.id',
                    'sum(COALESCE(bankDeposits.total, 0)) as total'
                ])
                ->leftJoin(['bankDeposits' => '(
                    SELECT 
                        accounting_branch_program.id,
                        sum(COALESCE(accounting_expense_bank_deposit.amount, 0)) as total,
                        DATE(accounting_expense.datetime) as newDate,
                        accounting_expense.amount_type as amountType
                    from accounting_expense_bank_deposit
                    LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_bank_deposit.id and accounting_expense.expense_type_id = 4
                    LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                    LEFT JOIN accounting_branch_program on accounting_branch_program.id = accounting_season.branch_program_id
                    WHERE 
                        accounting_expense.datetime between "'.$start.' 00:00:00" and "'.$end.' 23:59:59" and
                        accounting_expense.amount_type '.$con.' "Cash"
                    GROUP BY newDate, accounting_branch_program.id
                )'], 'bankDeposits.id = accounting_branch_program.id')
                ->groupBy(['accounting_branch_program.id'])
                ->createCommand()
                ->getRawSql();
        }else{
           $sql = BranchProgram::find()
                ->select([
                    'accounting_branch_program.id',
                    'sum(COALESCE(bankDeposits.total, 0)) as total'
                ])
                ->leftJoin(['bankDeposits' => '(
                    SELECT 
                        accounting_branch_program.id,
                        sum(COALESCE(accounting_expense_bank_deposit.amount, 0)) as total,
                        DATE(accounting_expense.datetime) as newDate,
                        accounting_expense.amount_type as amountType
                    from accounting_expense_bank_deposit
                    LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_bank_deposit.id and accounting_expense.expense_type_id = 4
                    LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                    LEFT JOIN accounting_branch_program on accounting_branch_program.id = accounting_season.branch_program_id
                    WHERE 
                        accounting_expense.amount_type '.$con.' "Cash"
                    GROUP BY newDate, accounting_branch_program.id
                )'], 'bankDeposits.id = accounting_branch_program.id')
                ->groupBy(['accounting_branch_program.id'])
                ->createCommand()
                ->getRawSql();
        }

        return $sql;
    }

    function getBankDepositBySeasonRawSql($start = null, $end = null, $amountType)
    {
        $con = $amountType == "Cash" ? '=' : '<>';
        if(!is_null($start) && !is_null($end))
        {
            $sql = Season::find()
                ->select([
                    'accounting_season.branch_program_id',
                    'accounting_season.id',
                    'sum(COALESCE(bankDeposits.total, 0)) as total'
                ])
                ->leftJoin(['bankDeposits' => '(
                    SELECT 
                        accounting_season.id,
                        sum(COALESCE(accounting_expense_bank_deposit.amount, 0)) as total,
                        DATE(accounting_expense.datetime) as newDate,
                        accounting_expense.amount_type as amountType
                    from accounting_expense_bank_deposit
                    LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_bank_deposit.id and accounting_expense.expense_type_id = 4
                    LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                    WHERE 
                        accounting_expense.datetime between "'.$start.' 00:00:00" and "'.$end.' 23:59:59" and
                        accounting_expense.amount_type '.$con.' "Cash"
                    GROUP BY newDate, accounting_season.id
                )'], 'bankDeposits.id = accounting_season.id')
                ->groupBy(['accounting_season.id'])
                ->createCommand()
                ->getRawSql();
        }else{
            $sql = Season::find()
                ->select([
                    'accounting_season.branch_program_id',
                    'accounting_season.id',
                    'sum(COALESCE(bankDeposits.total, 0)) as total'
                ])
                ->leftJoin(['bankDeposits' => '(
                    SELECT 
                        accounting_season.id,
                        sum(COALESCE(accounting_expense_bank_deposit.amount, 0)) as total,
                        DATE(accounting_expense.datetime) as newDate,
                        accounting_expense.amount_type as amountType
                    from accounting_expense_bank_deposit
                    LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_bank_deposit.id and accounting_expense.expense_type_id = 4
                    LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                    WHERE 
                        accounting_expense.amount_type '.$con.' "Cash"
                    GROUP BY newDate, accounting_season.id
                )'], 'bankDeposits.id = accounting_season.id')
                ->groupBy(['accounting_season.id'])
                ->createCommand()
                ->getRawSql();
        }

        return $sql;
    }

    function getOperatingExpenseByBranchProgramRawSql($start = null, $end = null, $amountType)
    {
        $con = $amountType == "Cash" ? '=' : '<>';
        if(!is_null($start) && !is_null($end))
        {
            $sql = BranchProgram::find()
                ->select([
                    'accounting_branch_program.id',
                    'sum(COALESCE(operatingExpenses.total, 0)) as total'
                ])
                ->leftJoin(['operatingExpenses' => '(
                    SELECT 
                        accounting_branch_program.id,
                        sum(COALESCE(accounting_expense_operating_expense.staff_salary, 0)) as staffSalaryTotal,
                        sum(COALESCE(accounting_expense_operating_expense.cash_pf, 0)) as cashPfTotal,
                        sum(COALESCE(accounting_expense_operating_expense.rent, 0)) as rentTotal,
                        sum(COALESCE(accounting_expense_operating_expense.utilities, 0)) as utilitiesTotal,
                        sum(COALESCE(accounting_expense_operating_expense.equipment_and_labor, 0)) as equipmentAndLaborTotal,
                        sum(COALESCE(accounting_expense_operating_expense.bir_and_docs, 0)) as bir_and_docsTotal,
                        sum(COALESCE(accounting_expense_operating_expense.marketing, 0)) as marketingTotal,
                        sum(
                            accounting_expense_operating_expense.staff_salary + 
                            accounting_expense_operating_expense.cash_pf + 
                            accounting_expense_operating_expense.rent + 
                            accounting_expense_operating_expense.utilities + 
                            accounting_expense_operating_expense.equipment_and_labor + 
                            accounting_expense_operating_expense.bir_and_docs + 
                            accounting_expense_operating_expense.marketing
                        ) as total,
                        DATE(accounting_expense.datetime) as newDate,
                        accounting_expense.amount_type as amountType
                    from accounting_expense_operating_expense
                    LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_operating_expense.id and accounting_expense.expense_type_id = 5
                    LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                    LEFT JOIN accounting_branch_program on accounting_branch_program.id = accounting_season.branch_program_id
                    WHERE 
                        accounting_expense.datetime between "'.$start.' 00:00:00" and "'.$end.' 23:59:59" and
                        accounting_expense.amount_type '.$con.' "Cash"
                    GROUP BY newDate, accounting_branch_program.id
                )'], 'operatingExpenses.id = accounting_branch_program.id')
                ->groupBy(['accounting_branch_program.id'])
                ->createCommand()
                ->getRawSql();
        }else{
            $sql = BranchProgram::find()
                ->select([
                    'accounting_branch_program.id',
                    'sum(COALESCE(operatingExpenses.total, 0)) as total'
                ])
                ->leftJoin(['operatingExpenses' => '(
                    SELECT 
                        accounting_branch_program.id,
                        sum(COALESCE(accounting_expense_operating_expense.staff_salary, 0)) as staffSalaryTotal,
                        sum(COALESCE(accounting_expense_operating_expense.cash_pf, 0)) as cashPfTotal,
                        sum(COALESCE(accounting_expense_operating_expense.rent, 0)) as rentTotal,
                        sum(COALESCE(accounting_expense_operating_expense.utilities, 0)) as utilitiesTotal,
                        sum(COALESCE(accounting_expense_operating_expense.equipment_and_labor, 0)) as equipmentAndLaborTotal,
                        sum(COALESCE(accounting_expense_operating_expense.bir_and_docs, 0)) as bir_and_docsTotal,
                        sum(COALESCE(accounting_expense_operating_expense.marketing, 0)) as marketingTotal,
                        sum(
                            accounting_expense_operating_expense.staff_salary + 
                            accounting_expense_operating_expense.cash_pf + 
                            accounting_expense_operating_expense.rent + 
                            accounting_expense_operating_expense.utilities + 
                            accounting_expense_operating_expense.equipment_and_labor + 
                            accounting_expense_operating_expense.bir_and_docs + 
                            accounting_expense_operating_expense.marketing
                        ) as total,
                        DATE(accounting_expense.datetime) as newDate,
                        accounting_expense.amount_type as amountType
                    from accounting_expense_operating_expense
                    LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_operating_expense.id and accounting_expense.expense_type_id = 5
                    LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                    LEFT JOIN accounting_branch_program on accounting_branch_program.id = accounting_season.branch_program_id
                    WHERE
                        accounting_expense.amount_type '.$con.' "Cash"
                    GROUP BY newDate, accounting_branch_program.id
                )'], 'operatingExpenses.id = accounting_branch_program.id')
                ->groupBy(['accounting_branch_program.id'])
                ->createCommand()
                ->getRawSql();   
        }
        return $sql;
    }

    function getOperatingExpenseBySeasonRawSql($start = null, $end = null, $amountType)
    {
        $con = $amountType == "Cash" ? '=' : '<>';
        if(!is_null($start) && !is_null($end))
        {
            $sql = Season::find()
                ->select([
                    'accounting_season.branch_program_id',
                    'accounting_season.id',
                    'sum(COALESCE(operatingExpenses.total, 0)) as total'
                ])
                ->leftJoin(['operatingExpenses' => '(
                    SELECT 
                        accounting_season.id,
                        sum(COALESCE(accounting_expense_operating_expense.staff_salary, 0)) as staffSalaryTotal,
                        sum(COALESCE(accounting_expense_operating_expense.cash_pf, 0)) as cashPfTotal,
                        sum(COALESCE(accounting_expense_operating_expense.rent, 0)) as rentTotal,
                        sum(COALESCE(accounting_expense_operating_expense.utilities, 0)) as utilitiesTotal,
                        sum(COALESCE(accounting_expense_operating_expense.equipment_and_labor, 0)) as equipmentAndLaborTotal,
                        sum(COALESCE(accounting_expense_operating_expense.bir_and_docs, 0)) as bir_and_docsTotal,
                        sum(COALESCE(accounting_expense_operating_expense.marketing, 0)) as marketingTotal,
                        sum(
                            accounting_expense_operating_expense.staff_salary + 
                            accounting_expense_operating_expense.cash_pf + 
                            accounting_expense_operating_expense.rent + 
                            accounting_expense_operating_expense.utilities + 
                            accounting_expense_operating_expense.equipment_and_labor + 
                            accounting_expense_operating_expense.bir_and_docs + 
                            accounting_expense_operating_expense.marketing
                        ) as total,
                        DATE(accounting_expense.datetime) as newDate,
                        accounting_expense.amount_type as amountType
                    from accounting_expense_operating_expense
                    LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_operating_expense.id and accounting_expense.expense_type_id = 5
                    LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                    WHERE 
                        accounting_expense.datetime between "'.$start.' 00:00:00" and "'.$end.' 23:59:59" and 
                        accounting_expense.amount_type '.$con.' "Cash"
                    GROUP BY newDate, accounting_season.id
                )'], 'operatingExpenses.id = accounting_season.id')
                ->groupBy(['accounting_season.id'])
                ->createCommand()
                ->getRawSql();
        }else{
            $sql = Season::find()
                ->select([
                    'accounting_season.branch_program_id',
                    'accounting_season.id',
                    'sum(COALESCE(operatingExpenses.total, 0)) as total'
                ])
                ->leftJoin(['operatingExpenses' => '(
                    SELECT 
                        accounting_season.id,
                        sum(COALESCE(accounting_expense_operating_expense.staff_salary, 0)) as staffSalaryTotal,
                        sum(COALESCE(accounting_expense_operating_expense.cash_pf, 0)) as cashPfTotal,
                        sum(COALESCE(accounting_expense_operating_expense.rent, 0)) as rentTotal,
                        sum(COALESCE(accounting_expense_operating_expense.utilities, 0)) as utilitiesTotal,
                        sum(COALESCE(accounting_expense_operating_expense.equipment_and_labor, 0)) as equipmentAndLaborTotal,
                        sum(COALESCE(accounting_expense_operating_expense.bir_and_docs, 0)) as bir_and_docsTotal,
                        sum(COALESCE(accounting_expense_operating_expense.marketing, 0)) as marketingTotal,
                        sum(
                            accounting_expense_operating_expense.staff_salary + 
                            accounting_expense_operating_expense.cash_pf + 
                            accounting_expense_operating_expense.rent + 
                            accounting_expense_operating_expense.utilities + 
                            accounting_expense_operating_expense.equipment_and_labor + 
                            accounting_expense_operating_expense.bir_and_docs + 
                            accounting_expense_operating_expense.marketing
                        ) as total,
                        DATE(accounting_expense.datetime) as newDate,
                        accounting_expense.amount_type as amountType
                    from accounting_expense_operating_expense
                    LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_operating_expense.id and accounting_expense.expense_type_id = 5
                    LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                    WHERE
                        accounting_expense.amount_type '.$con.' "Cash"
                    GROUP BY newDate, accounting_season.id
                )'], 'operatingExpenses.id = accounting_season.id')
                ->groupBy(['accounting_season.id'])
                ->createCommand()
                ->getRawSql();
        }

        return $sql;
    }

    function getBudgetProposalByBranchProgramRawSql($start = null, $end = null, $amountType)
    {
        $con = $amountType == "Cash" ? '=' : '<>';
        if(!is_null($start) && !is_null($end))
        {
            $sql = BranchProgram::find()
                ->select([
                    'accounting_branch_program.id',
                    'sum(COALESCE(budgetProposals.total, 0)) as total'
                ])
                ->leftJoin(['budgetProposals' => '(
                    SELECT 
                        accounting_branch_program.id,
                        sum(COALESCE(particulars.total, 0)) as total,
                        DATE(accounting_income.datetime) as newDate,
                        accounting_income.amount_type as amountType
                    from accounting_income_budget_proposal
                    LEFT JOIN (
                        SELECT
                            accounting_budget_proposal_particular.budget_proposal_id,
                            sum(accounting_budget_proposal_particular.amount) as total
                        from accounting_budget_proposal_particular
                        LEFT JOIN accounting_income_budget_proposal on accounting_income_budget_proposal.id = accounting_budget_proposal_particular.budget_proposal_id
                        WHERE accounting_budget_proposal_particular.approval_status = "Approved"
                        GROUP BY accounting_budget_proposal_particular.budget_proposal_id
                    ) particulars on particulars.budget_proposal_id = accounting_income_budget_proposal.id
                    LEFT JOIN accounting_income on accounting_income.income_id = accounting_income_budget_proposal.id and accounting_income.income_type_id = 3
                    LEFT JOIN accounting_season on accounting_season.id = accounting_income_budget_proposal.season_id
                    LEFT JOIN accounting_branch_program on accounting_branch_program.id = accounting_season.branch_program_id
                    WHERE 
                        accounting_income.datetime between "'.$start.' 00:00:00" and "'.$end.' 23:59:59" and 
                        accounting_income.amount_type '.$con.' "Cash" and
                        accounting_income_budget_proposal.approval_status = "Approved"
                    GROUP BY newDate, accounting_branch_program.id
                )'], 'budgetProposals.id = accounting_branch_program.id')
                ->groupBy(['accounting_branch_program.id'])
                ->createCommand()
                ->getRawSql();
        }else{
            $sql = BranchProgram::find()
                ->select([
                    'accounting_branch_program.id',
                    'sum(COALESCE(budgetProposals.total, 0)) as total'
                ])
                ->leftJoin(['budgetProposals' => '(
                    SELECT 
                        accounting_branch_program.id,
                        sum(COALESCE(particulars.total, 0)) as total,
                        DATE(accounting_income.datetime) as newDate,
                        accounting_income.amount_type as amountType
                    from accounting_income_budget_proposal
                    LEFT JOIN (
                        SELECT
                            accounting_budget_proposal_particular.budget_proposal_id,
                            sum(accounting_budget_proposal_particular.amount) as total
                        from accounting_budget_proposal_particular
                        LEFT JOIN accounting_income_budget_proposal on accounting_income_budget_proposal.id = accounting_budget_proposal_particular.budget_proposal_id
                        WHERE accounting_budget_proposal_particular.approval_status = "Approved"
                        GROUP BY accounting_budget_proposal_particular.budget_proposal_id
                    ) particulars on particulars.budget_proposal_id = accounting_income_budget_proposal.id
                    LEFT JOIN accounting_income on accounting_income.income_id = accounting_income_budget_proposal.id and accounting_income.income_type_id = 3
                    LEFT JOIN accounting_season on accounting_season.id = accounting_income_budget_proposal.season_id
                    LEFT JOIN accounting_branch_program on accounting_branch_program.id = accounting_season.branch_program_id
                    WHERE
                        accounting_income.amount_type '.$con.' "Cash" and
                        accounting_income_budget_proposal.approval_status = "Approved"
                    GROUP BY newDate, accounting_branch_program.id
                )'], 'budgetProposals.id = accounting_branch_program.id')
                ->groupBy(['accounting_branch_program.id'])
                ->createCommand()
                ->getRawSql();
        }

        return $sql;
    }

    function getBudgetProposalBySeasonRawSql($start = null, $end = null, $amountType)
    {
        $con = $amountType == "Cash" ? '=' : '<>';
        if(!is_null($start) && !is_null($end))
        {
            $sql = Season::find()
                ->select([
                    'accounting_season.branch_program_id',
                    'accounting_season.id',
                    'sum(COALESCE(budgetProposals.total, 0)) as total'
                ])
                ->leftJoin(['budgetProposals' => '(
                    SELECT 
                        accounting_season.id,
                        sum(COALESCE(particulars.total, 0)) as total,
                        DATE(accounting_income.datetime) as newDate,
                        accounting_income.amount_type as amountType
                    from accounting_income_budget_proposal
                    LEFT JOIN (
                        SELECT
                            accounting_budget_proposal_particular.budget_proposal_id,
                            sum(accounting_budget_proposal_particular.amount) as total
                        from accounting_budget_proposal_particular
                        LEFT JOIN accounting_income_budget_proposal on accounting_income_budget_proposal.id = accounting_budget_proposal_particular.budget_proposal_id
                        WHERE accounting_budget_proposal_particular.approval_status = "Approved"
                        GROUP BY accounting_budget_proposal_particular.budget_proposal_id
                    ) particulars on particulars.budget_proposal_id = accounting_income_budget_proposal.id
                    LEFT JOIN accounting_income on accounting_income.income_id = accounting_income_budget_proposal.id and accounting_income.income_type_id = 3
                    LEFT JOIN accounting_season on accounting_season.id = accounting_income_budget_proposal.season_id
                    WHERE 
                        accounting_income.datetime between "'.$start.' 00:00:00" and "'.$end.' 23:59:59" and 
                        accounting_income.amount_type '.$con.' "Cash" and
                        accounting_income_budget_proposal.approval_status = "Approved"
                    GROUP BY newDate, accounting_season.id
                )'], 'budgetProposals.id = accounting_season.id')
                ->groupBy(['accounting_season.id'])
                ->createCommand()
                ->getRawSql();
        }else{
            $sql = Season::find()
                ->select([
                    'accounting_season.branch_program_id',
                    'accounting_season.id',
                    'sum(COALESCE(budgetProposals.total, 0)) as total'
                ])
                ->leftJoin(['budgetProposals' => '(
                    SELECT 
                        accounting_season.id,
                        sum(COALESCE(particulars.total, 0)) as total,
                        DATE(accounting_income.datetime) as newDate,
                        accounting_income.amount_type as amountType
                    from accounting_income_budget_proposal
                    LEFT JOIN (
                        SELECT
                            accounting_budget_proposal_particular.budget_proposal_id,
                            sum(accounting_budget_proposal_particular.amount) as total
                        from accounting_budget_proposal_particular
                        LEFT JOIN accounting_income_budget_proposal on accounting_income_budget_proposal.id = accounting_budget_proposal_particular.budget_proposal_id
                        WHERE accounting_budget_proposal_particular.approval_status = "Approved"
                        GROUP BY accounting_budget_proposal_particular.budget_proposal_id
                    ) particulars on particulars.budget_proposal_id = accounting_income_budget_proposal.id
                    LEFT JOIN accounting_income on accounting_income.income_id = accounting_income_budget_proposal.id and accounting_income.income_type_id = 3
                    LEFT JOIN accounting_season on accounting_season.id = accounting_income_budget_proposal.season_id
                    WHERE 
                        accounting_income.amount_type '.$con.' "Cash" and
                        accounting_income_budget_proposal.approval_status = "Approved"
                    GROUP BY newDate, accounting_season.id
                )'], 'budgetProposals.id = accounting_season.id')
                ->groupBy(['accounting_season.id'])
                ->createCommand()
                ->getRawSql();
        }

        return $sql;
    }

    function getBranchTransferByBranchProgramRawSql($start = null, $end = null, $amountType)
    {
        $con = $amountType == "Cash" ? '=' : '<>';
        if(!is_null($start) && !is_null($end))
        {
            $sql = BranchProgram::find()
                ->select([
                    'accounting_branch_program.id',
                    'sum(COALESCE(branchTransfers.total, 0)) as total'
                ])
                ->leftJoin(['branchTransfers' => '(
                    SELECT 
                        accounting_branch_program.id,
                        sum(COALESCE(particulars.total, 0)) as total,
                        DATE(accounting_expense.datetime) as newDate,
                        accounting_expense.amount_type as amountType
                    from accounting_expense_branch_transfer
                    LEFT JOIN (
                        SELECT
                            accounting_budget_proposal_particular.budget_proposal_id,
                            sum(accounting_budget_proposal_particular.amount) as total
                        from accounting_budget_proposal_particular
                        LEFT JOIN accounting_income_budget_proposal on accounting_income_budget_proposal.id = accounting_budget_proposal_particular.budget_proposal_id
                        WHERE accounting_budget_proposal_particular.approval_status = "Approved"
                        GROUP BY accounting_budget_proposal_particular.budget_proposal_id
                    ) particulars on particulars.budget_proposal_id = accounting_expense_branch_transfer.budget_proposal_id
                    LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6
                    LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                    LEFT JOIN accounting_branch_program on accounting_branch_program.id = accounting_season.branch_program_id
                    WHERE 
                        accounting_expense.datetime between "'.$start.' 00:00:00" and "'.$end.' 23:59:59" and 
                        accounting_expense_branch_transfer.amount_source '.$con.' "Cash On Hand" and
                        accounting_expense.amount_type '.$con.' "Cash"
                    GROUP BY newDate, accounting_branch_program.id
                )'], 'branchTransfers.id = accounting_branch_program.id')
                ->groupBy(['accounting_branch_program.id'])
                ->createCommand()
                ->getRawSql();
        }else{
            $sql = BranchProgram::find()
                ->select([
                    'accounting_branch_program.id',
                    'sum(COALESCE(branchTransfers.total, 0)) as total'
                ])
                ->leftJoin(['branchTransfers' => '(
                    SELECT 
                        accounting_branch_program.id,
                        sum(COALESCE(particulars.total, 0)) as total,
                        DATE(accounting_expense.datetime) as newDate,
                        accounting_expense.amount_type as amountType
                    from accounting_expense_branch_transfer
                    LEFT JOIN (
                        SELECT
                            accounting_budget_proposal_particular.budget_proposal_id,
                            sum(accounting_budget_proposal_particular.amount) as total
                        from accounting_budget_proposal_particular
                        LEFT JOIN accounting_income_budget_proposal on accounting_income_budget_proposal.id = accounting_budget_proposal_particular.budget_proposal_id
                        WHERE accounting_budget_proposal_particular.approval_status = "Approved"
                        GROUP BY accounting_budget_proposal_particular.budget_proposal_id
                    ) particulars on particulars.budget_proposal_id = accounting_expense_branch_transfer.budget_proposal_id
                    LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6
                    LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                    LEFT JOIN accounting_branch_program on accounting_branch_program.id = accounting_season.branch_program_id
                    WHERE
                        accounting_expense_branch_transfer.amount_source '.$con.' "Cash On Hand" and
                        accounting_expense.amount_type '.$con.' "Cash"
                    GROUP BY newDate, accounting_branch_program.id
                )'], 'branchTransfers.id = accounting_branch_program.id')
                ->groupBy(['accounting_branch_program.id'])
                ->createCommand()
                ->getRawSql();
        }

        return $sql;
    }

    function getBranchTransferBySeasonRawSql($start = null, $end = null, $amountType)
    {
        $con = $amountType == "Cash" ? '=' : '<>';
        if(!is_null($start) && !is_null($end))
        {
            $sql = Season::find()
                ->select([
                    'accounting_season.branch_program_id',
                    'accounting_season.id',
                    'sum(COALESCE(branchTransfers.total, 0)) as total'
                ])
                ->leftJoin(['branchTransfers' => '(
                    SELECT 
                        accounting_season.id,
                        sum(COALESCE(particulars.total, 0)) as total,
                        DATE(accounting_expense.datetime) as newDate,
                        accounting_expense.amount_type as amountType
                    from accounting_expense_branch_transfer
                    LEFT JOIN (
                        SELECT
                            accounting_budget_proposal_particular.budget_proposal_id,
                            sum(accounting_budget_proposal_particular.amount) as total
                        from accounting_budget_proposal_particular
                        LEFT JOIN accounting_income_budget_proposal on accounting_income_budget_proposal.id = accounting_budget_proposal_particular.budget_proposal_id
                        WHERE accounting_budget_proposal_particular.approval_status = "Approved"
                        GROUP BY accounting_budget_proposal_particular.budget_proposal_id
                    ) particulars on particulars.budget_proposal_id = accounting_expense_branch_transfer.budget_proposal_id
                    LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6
                    LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                    WHERE 
                        accounting_expense.datetime between "'.$start.' 00:00:00" and "'.$end.' 23:59:59" and 
                        accounting_expense_branch_transfer.amount_source '.$con.' "Cash On Hand" and
                        accounting_expense.amount_type '.$con.' "Cash"
                    GROUP BY newDate, accounting_season.id
                )'], 'branchTransfers.id = accounting_season.id')
                ->groupBy(['accounting_season.id'])
                ->createCommand()
                ->getRawSql();
        }else{
            $sql = Season::find()
                ->select([
                    'accounting_season.branch_program_id',
                    'accounting_season.id',
                    'sum(COALESCE(branchTransfers.total, 0)) as total'
                ])
                ->leftJoin(['branchTransfers' => '(
                    SELECT 
                        accounting_season.id,
                        sum(COALESCE(particulars.total, 0)) as total,
                        DATE(accounting_expense.datetime) as newDate,
                        accounting_expense.amount_type as amountType
                    from accounting_expense_branch_transfer
                    LEFT JOIN (
                        SELECT
                            accounting_budget_proposal_particular.budget_proposal_id,
                            sum(accounting_budget_proposal_particular.amount) as total
                        from accounting_budget_proposal_particular
                        LEFT JOIN accounting_income_budget_proposal on accounting_income_budget_proposal.id = accounting_budget_proposal_particular.budget_proposal_id
                        WHERE accounting_budget_proposal_particular.approval_status = "Approved"
                        GROUP BY accounting_budget_proposal_particular.budget_proposal_id
                    ) particulars on particulars.budget_proposal_id = accounting_expense_branch_transfer.budget_proposal_id
                    LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_branch_transfer.id and accounting_expense.expense_type_id = 6
                    LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                    WHERE 
                        accounting_expense_branch_transfer.amount_source '.$con.' "Cash On Hand" and
                        accounting_expense.amount_type '.$con.' "Cash"
                    GROUP BY newDate, accounting_season.id
                )'], 'branchTransfers.id = accounting_season.id')
                ->groupBy(['accounting_season.id'])
                ->createCommand()
                ->getRawSql();
        }

        return $sql;
    }

    function getBeginningCashByBranchProgram($start = null, $end = null)
    {
        if(!is_null($start) && !is_null($end))
        {
            $sql = BranchProgram::find()
            ->select([
                'accounting_branch_program.id',
                'COALESCE(beginningCoh.cash_on_hand, 0) as coh',
                'COALESCE(beginningCoh.cash_on_bank, 0) as cob',
            ])
            ->leftJoin(['beginningCoh' => '(
                SELECT 
                    accounting_audit_beginning_coh.branch_program_id,
                    sum(COALESCE(accounting_audit_beginning_coh.cash_on_hand,0)) as cash_on_hand,
                    sum(COALESCE(accounting_audit_beginning_coh.cash_on_bank,0)) as cash_on_bank
                from accounting_audit_beginning_coh
                WHERE 
                    datetime between "'.$start.' 00:00:00" and "'.$end.' 23:59:59"
                group by accounting_audit_beginning_coh.branch_program_id
            )'], 'beginningCoh.branch_program_id = accounting_branch_program.id')
            ->createCommand()
            ->getRawSql();
        }else{
            $sql = BranchProgram::find()
            ->select([
                'accounting_branch_program.id',
                'COALESCE(beginningCoh.cash_on_hand, 0) as coh',
                'COALESCE(beginningCoh.cash_on_bank, 0) as cob',
            ])
            ->leftJoin(['beginningCoh' => '(
                SELECT 
                    accounting_audit_beginning_coh.branch_program_id,
                    sum(COALESCE(accounting_audit_beginning_coh.cash_on_hand,0)) as cash_on_hand,
                    sum(COALESCE(accounting_audit_beginning_coh.cash_on_bank,0)) as cash_on_bank
                from accounting_audit_beginning_coh
                group by accounting_audit_beginning_coh.branch_program_id
            )'], 'beginningCoh.branch_program_id = accounting_branch_program.id')
            ->createCommand()
            ->getRawSql();
        }

        return $sql;
    }

    function getBeginningCashBySeason($start = null, $end = null)
    {
        if(!is_null($start) && !is_null($end))
        {
            $sql = Season::find()
            ->select([
                'accounting_season.branch_program_id',
                'accounting_season.id',
                'COALESCE(beginningCoh.cash_on_hand, 0) as coh',
                'COALESCE(beginningCoh.cash_on_bank, 0) as cob',
            ])
            ->leftJoin(['beginningCoh' => '(
                SELECT 
                    accounting_audit_beginning_coh.season_id,
                    sum(COALESCE(accounting_audit_beginning_coh.cash_on_hand,0)) as cash_on_hand,
                    sum(COALESCE(accounting_audit_beginning_coh.cash_on_bank,0)) as cash_on_bank
                from accounting_audit_beginning_coh
                WHERE 
                    datetime between "'.$start.' 00:00:00" and "'.$end.' 23:59:59"
                group by accounting_audit_beginning_coh.season_id
            )'], 'beginningCoh.season_id = accounting_season.id')
            ->createCommand()
            ->getRawSql();
        }else{
            $sql = Season::find()
            ->select([
                'accounting_season.branch_program_id',
                'COALESCE(beginningCoh.cash_on_hand, 0) as coh',
                'COALESCE(beginningCoh.cash_on_bank, 0) as cob',
            ])
            ->leftJoin(['beginningCoh' => '(
                SELECT 
                    accounting_audit_beginning_coh.season_id,
                    sum(COALESCE(accounting_audit_beginning_coh.cash_on_hand,0)) as cash_on_hand,
                    sum(COALESCE(accounting_audit_beginning_coh.cash_on_bank,0)) as cash_on_bank
                from accounting_audit_beginning_coh
                group by accounting_audit_beginning_coh.season_id
            )'], 'beginningCoh.season_id = accounting_season.id')
            ->createCommand()
            ->getRawSql();
        }

        return $sql;
    }

    public function actionIndex()
    {
        $searchModel = new NotificationSearch();

        $user_info = Yii::$app->user->identity->userinfo;
        $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
        $rolenames =  ArrayHelper::map($roles, 'name','name');

        $access = AccessProgram::findOne(['user_id' => Yii::$app->user->identity->userinfo->user_id]) ? AccessProgram::findOne(['user_id' => Yii::$app->user->identity->userinfo->user_id]) : new AccessProgram();

        $archive = new ArchiveSeason();

        $currentDates = $this->check_in_cutoff(date('Y-m-d'));
        $cutoffDates = $this->dateRange($currentDates['start'], $currentDates['end']);

        if(in_array('TopManagement',$rolenames)){
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
            $dataProvider->query->orderBy(['datetime' => SORT_DESC]);

            $branchCount = Branch::find()->count();
            $targetEnrolee = TargetEnrolee::find()->where(['month' => date('F').' '.date('Y')])->count();

            if($branchCount > 0){ 
                $enroleeMessage = ($branchCount - $targetEnrolee) > 1 ? ($branchCount - $targetEnrolee).' branches has no target no. of enrolees for this month.' : ($branchCount - $targetEnrolee).' branch has no target no. of enrolees for this month.'; 

                if($targetEnrolee != $branchCount)
                {
                    $enroleeNotification = Notification::findOne(['model' => 'TargetEnrolee']) ? Notification::findOne(['model' => 'TargetEnrolee']) : new Notification(); 
            
                    $enroleeNotification->model = 'TargetEnrolee';
                    $enroleeNotification->message = $enroleeMessage;
                    $enroleeNotification->save();
                }
            }

            $targetExpense = TargetExpense::find()->where(['month' => date('F').' '.date('Y')])->count();

            if($branchCount > 0){ 
                $expenseMessage = ($branchCount - $targetExpense) > 1 ? ($branchCount - $targetExpense).' branches has no target expense amount for this month.' : ($branchCount - $targetExpense).' branch has no target expense amount for this month.'; 

                if($targetExpense != $branchCount)
                {
                    $expenseNotification = Notification::findOne(['model' => 'TargetExpense']) ? Notification::findOne(['model' => 'TargetExpense']) : new Notification(); 
            
                    $expenseNotification->model = 'TargetExpense';
                    $expenseNotification->message = $expenseMessage;
                    $expenseNotification->save();
                }
            }

            $branchPrograms = BranchProgram::find()
                            ->select([
                                'accounting_branch_program.id as id',
                                'concat(accounting_branch.name," - ",accounting_program.name) as name',
                            ])
                            ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                            ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                            ->orderBy(['name' => SORT_ASC])
                            ->asArray()
                            ->all();

            $date = date("Y-m-d");
            $cutoff = $this->check_in_cutoff(date("Y-m-d"));
            $dates = $this->dateRange($cutoff['start'], $cutoff['end']);

            if($access->load(Yii::$app->request->post()))
            {
                $postData = Yii::$app->request->post();

                $access->user_id = Yii::$app->user->identity->userinfo->user_id; 
                $access->branch_program_id = $postData['AccessProgram']['branch_program_id']; 
                $access->save();

                \Yii::$app->getSession()->setFlash('success', 'You have successfully selected a program to access');
                return $this->redirect(['index']);
            }

            if($archive->load(Yii::$app->request->post()))
            {
                $postData = Yii::$app->request->post();

                $archive->user_id = Yii::$app->user->identity->userinfo->user_id; 
                $archive->season_id = $postData['ArchiveSeason']['season_id']; 
                $archive->save();

                \Yii::$app->getSession()->setFlash('success', 'You have successfully archived a season');
                return $this->redirect(['index']);
            }

            return $this->render('index',[
                'access' => $access,
                'archive' => $archive,
                'cutoff' => $cutoff,
                'branchPrograms' => $branchPrograms, 
                'seasons' => [], 
                'dataProvider' => $dataProvider,
                'cutoffDates' => $cutoffDates,
            ]);

        }else if(in_array('AreaManager',$rolenames)){
            $searchModel->branch_id = Yii::$app->user->identity->userinfo->BRANCH_C;

            $audit = Audit::find()
                    ->leftJoin('accounting_branch_program', 'accounting_branch_program.id = accounting_audit.branch_program_id')
                    ->where(['like', 'datetime', date('Y-m-d')])
                    ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                    ->count();

            if($audit == 0)
            {
                $auditNotification = Notification::find()->where(['model' => 'Audit', 'branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])->one() ? Notification::find()->where(['model' => 'Audit', 'branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])->one() : new Notification();
                $auditNotification->branch_id = Yii::$app->user->identity->userinfo->BRANCH_C;
                $auditNotification->model = 'Audit';
                $auditNotification->message = 'Daily audit must be done before the closing time of operations.';
                $auditNotification->save();
            }

            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
            $dataProvider->query->orderBy(['datetime' => SORT_DESC]);

            $branchPrograms = BranchProgram::find()
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

            $date = date("Y-m-d");
            $cutoff = $this->check_in_cutoff(date("Y-m-d"));
            $dates = $this->dateRange($cutoff['start'], $cutoff['end']);

            if($access->load(Yii::$app->request->post()))
            {
                $postData = Yii::$app->request->post();

                $access->user_id = Yii::$app->user->identity->userinfo->user_id; 
                $access->branch_program_id = $postData['AccessProgram']['branch_program_id']; 
                $access->save();

                \Yii::$app->getSession()->setFlash('success', 'You have successfully selected a program to access');
                return $this->redirect(['index']);
            }

            if($archive->load(Yii::$app->request->post()))
            {
                $postData = Yii::$app->request->post();

                $archive->user_id = Yii::$app->user->identity->userinfo->user_id; 
                $archive->season_id = $postData['ArchiveSeason']['season_id']; 
                $archive->save();

                \Yii::$app->getSession()->setFlash('success', 'You have successfully archived a season');
                return $this->redirect(['index']);
            }

            return $this->render('index',[
                'access' => $access,
                'archive' => $archive,
                'seasons' => [],
                'branchPrograms' => $branchPrograms,
                'cutoff' => $cutoff,
                'dataProvider' => $dataProvider,
                'cutoffDates' => $cutoffDates,
            ]);

        }else if(in_array('EnrolmentStaff',$rolenames)){
            $searchModel->branch_id = Yii::$app->user->identity->userinfo->BRANCH_C;

            $audit = Audit::find()
                    ->leftJoin('accounting_branch_program', 'accounting_branch_program.id = accounting_audit.branch_program_id')
                    ->where(['like', 'datetime', date('Y-m-d')])
                    ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                    ->count();

            if($audit == 0)
            {
                $auditNotification = Notification::find()->where(['model' => 'Audit', 'branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])->one() ? Notification::find()->where(['model' => 'Audit', 'branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])->one() : new Notification();
                $auditNotification->branch_id = Yii::$app->user->identity->userinfo->BRANCH_C;
                $auditNotification->model = 'Audit';
                $auditNotification->message = 'Daily audit must be done before the closing time of operations.';
                $auditNotification->save();
            }

            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
            $dataProvider->query->orderBy(['datetime' => SORT_DESC]);

            $branchPrograms = BranchProgram::find()
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

            if($access->load(Yii::$app->request->post()))
            {
                $postData = Yii::$app->request->post();

                $access->user_id = Yii::$app->user->identity->userinfo->user_id; 
                $access->branch_program_id = $postData['AccessProgram']['branch_program_id']; 
                $access->save();

                \Yii::$app->getSession()->setFlash('success', 'You have successfully selected a program to access');
                return $this->redirect(['index']);
            }

            if($archive->load(Yii::$app->request->post()))
            {
                $postData = Yii::$app->request->post();

                $archive->user_id = Yii::$app->user->identity->userinfo->user_id; 
                $archive->season_id = $postData['ArchiveSeason']['season_id']; 
                $archive->save();

                \Yii::$app->getSession()->setFlash('success', 'You have successfully archived a season');
                return $this->redirect(['index']);
            }

            return $this->render('index',[
                'access' => $access,
                'archive' => $archive,
                'seasons' => [],
                'branchPrograms' => $branchPrograms,
                'dataProvider' => $dataProvider,
                'cutoffDates' => $cutoffDates,
            ]);

        }else if(in_array('AccountingStaff',$rolenames)){
            $searchModel->branch_id = Yii::$app->user->identity->userinfo->BRANCH_C;

            $audit = Audit::find()
                    ->leftJoin('accounting_branch_program', 'accounting_branch_program.id = accounting_audit.branch_program_id')
                    ->where(['like', 'datetime', date('Y-m-d')])
                    ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                    ->count();

            if($audit == 0)
            {
                $auditNotification = Notification::find()->where(['model' => 'Audit', 'branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])->one() ? Notification::find()->where(['model' => 'Audit', 'branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])->one() : new Notification();
                $auditNotification->branch_id = Yii::$app->user->identity->userinfo->BRANCH_C;
                $auditNotification->model = 'Audit';
                $auditNotification->message = 'Daily audit must be done before the closing time of operations.';
                $auditNotification->save();
            }

            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
            $dataProvider->query->orderBy(['datetime' => SORT_DESC]);

            $branchPrograms = BranchProgram::find()
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

            $date = date("Y-m-d");
            $cutoff = $this->check_in_cutoff(date("Y-m-d"));
            $dates = $this->dateRange($cutoff['start'], $cutoff['end']);

            if($access->load(Yii::$app->request->post()))
            {
                $postData = Yii::$app->request->post();

                $access->user_id = Yii::$app->user->identity->userinfo->user_id; 
                $access->branch_program_id = $postData['AccessProgram']['branch_program_id']; 
                $access->save();

                \Yii::$app->getSession()->setFlash('success', 'You have successfully selected a program to access');
                return $this->redirect(['index']);
            }

            if($archive->load(Yii::$app->request->post()))
            {
                $postData = Yii::$app->request->post();

                $archive->user_id = Yii::$app->user->identity->userinfo->user_id; 
                $archive->season_id = $postData['ArchiveSeason']['season_id']; 
                $archive->save();

                \Yii::$app->getSession()->setFlash('success', 'You have successfully archived a season');
                return $this->redirect(['index']);
            }

            return $this->render('index',[
                'access' => $access,
                'archive' => $archive,
                'seasons' => [],
                'branchPrograms' => $branchPrograms,
                'cutoff' => $cutoff,
                'dataProvider' => $dataProvider,
                'cutoffDates' => $cutoffDates,
            ]);

        }else if(in_array('SchoolBased',$rolenames)){
            $searchModel->branch_id = Yii::$app->user->identity->userinfo->BRANCH_C;
            $searchModel->model = 'Transferee';

            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
            $dataProvider->query->orderBy(['datetime' => SORT_DESC]);

            $branchPrograms = BranchProgram::find()
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

            if($access->load(Yii::$app->request->post()))
            {
                $postData = Yii::$app->request->post();

                $access->user_id = Yii::$app->user->identity->userinfo->user_id; 
                $access->branch_program_id = $postData['AccessProgram']['branch_program_id']; 
                $access->save();

                \Yii::$app->getSession()->setFlash('success', 'You have successfully selected a program to access');
                return $this->redirect(['index']);
            }

            if($archive->load(Yii::$app->request->post()))
            {
                $postData = Yii::$app->request->post();

                $archive->user_id = Yii::$app->user->identity->userinfo->user_id; 
                $archive->season_id = $postData['ArchiveSeason']['season_id']; 
                $archive->save();

                \Yii::$app->getSession()->setFlash('success', 'You have successfully archived a season');
                return $this->redirect(['index']);
            }

            return $this->render('index',[
                'access' => $access,
                'archive' => $archive,
                'seasons' => [],
                'branchPrograms' => $branchPrograms,
                'dataProvider' => $dataProvider,
                'cutoffDates' => $cutoffDates,
            ]);

        }else if(in_array('Student',$rolenames)){

            $model = $this->findStudent(Yii::$app->user->identity->userinfo->STUDENT_C);
            $enrolments = StudentTuition::find()
                          ->select([
                            'accounting_student_enrolee_type.id as id',
                            'accounting_student_tuition.season_id as season_id',
                            'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as seasonName',
                            'accounting_package_student.amount as packageAmount',
                            'accounting_package.code as packageName',
                            'accounting_enrolee_type.name as enroleeTypeName',
                            'accounting_branch.name as branchName',
                            'accounting_program.name as programName',
                            'accounting_discount.amount as discountAmount',
                            'accounting_discount_type.name as discountType',
                            'accounting_enhancement.amount as enhancementAmount',
                            'accounting_coaching.amount as coachingAmount',
                            '((COALESCE(accounting_package_student.amount,0) + COALESCE(accounting_enhancement.amount,0) + COALESCE(accounting_coaching.amount,0)) - COALESCE(accounting_discount.amount,0)) as finalTuitionFee',
                            '((COALESCE(accounting_package_student.amount,0) + COALESCE(accounting_enhancement.amount,0) + COALESCE(accounting_coaching.amount,0)) - COALESCE(accounting_discount.amount,0)) - (COALESCE(incomeEnrolments.totalAmount, 0) + COALESCE(freebies.totalAmount, 0)) as balanceAmount',
                            'IF(((COALESCE(accounting_package_student.amount,0) + COALESCE(accounting_enhancement.amount,0) + COALESCE(accounting_coaching.amount,0)) - COALESCE(accounting_discount.amount,0)) - (COALESCE(incomeEnrolments.totalAmount, 0) + COALESCE(freebies.totalAmount, 0)) > 0, "With Balance", "Cleared") as balanceStatus'
                          ])
                          ->leftJoin('accounting_package_student', 'accounting_package_student.id = accounting_student_tuition.package_student_id')
                          ->leftJoin('accounting_package', 'accounting_package.id = accounting_package_student.package_id')
                          ->leftJoin('accounting_season', 'accounting_season.id = accounting_student_tuition.season_id')
                          ->leftJoin('accounting_coaching', 'accounting_coaching.season_id = accounting_season.id and accounting_coaching.student_id = accounting_student_tuition.student_id')
                          ->leftJoin('accounting_student_enrolee_type', 'accounting_student_enrolee_type.season_id = accounting_season.id and accounting_student_enrolee_type.student_id = accounting_student_tuition.student_id')
                          ->leftJoin('accounting_enhancement', 'accounting_enhancement.season_id = accounting_season.id and accounting_enhancement.student_id = accounting_student_tuition.student_id')
                          ->leftJoin('accounting_discount', 'accounting_discount.season_id = accounting_season.id and accounting_discount.student_id = accounting_student_tuition.student_id')
                          ->leftJoin('accounting_discount_type', 'accounting_discount_type.id = accounting_discount.discount_type_id')
                          ->leftJoin('accounting_enrolee_type', 'accounting_enrolee_type.id = accounting_student_enrolee_type.enrolee_type_id')
                          ->leftJoin('accounting_branch_program', 'accounting_branch_program.id = accounting_season.branch_program_id')
                          ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                          ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                          ->leftJoin(['incomeEnrolments' => '(
                            SELECT 
                                season_id,
                                student_id,
                                sum(amount) as totalAmount
                            from accounting_income_enrolment
                            group by season_id, student_id
                            )'], 'incomeEnrolments.season_id = accounting_season.id and incomeEnrolments.student_id = accounting_student_tuition.student_id')
                          ->leftJoin(['freebies' => '(
                            SELECT 
                                season_id,
                                student_id,
                                sum(amount) as totalAmount
                            from accounting_income_freebies_and_icons
                            group by season_id, student_id
                            )'], 'freebies.season_id = accounting_season.id and freebies.student_id = accounting_student_tuition.student_id')
                          ->where(['accounting_student_tuition.student_id' => $model->id])
                          ->orderBy(['accounting_student_tuition.id' => SORT_DESC])
                          ->asArray()
                          ->all();

            return $this->render('student-view', [
                'model' => $model,
                'enrolments' => $enrolments,
            ]);
        
        }else if(in_array('Professional',$rolenames)){
            return $this->redirect(['/accounting/professional-request']);
        }

    }

    public function actionAuditSummary($params)
    {
        $access = AccessProgram::findOne(['user_id' => Yii::$app->user->identity->userinfo->user_id]) ? AccessProgram::findOne(['user_id' => Yii::$app->user->identity->userinfo->user_id]) : new AccessProgram();

        $params = json_decode($params, true);
        $date = [];

        if(!empty($params))
        {
            foreach($params as $param)
            {
                if($param['name'] == 'date')
                {
                    if(!empty($param['value']))
                    {
                        $date[] = explode(" - ", $param['value']);
                    }else{
                        $cutoff = $this->check_in_cutoff(date("Y-m-d"));
                        $date[0][0] = $cutoff['start'];
                        $date[0][1] = $cutoff['end'];
                    }
                }
            }
        }else{
            $cutoff = $this->check_in_cutoff(date("Y-m-d"));
            $date[0][0] = $cutoff['start'];
            $date[0][1] = $cutoff['end'];
        }

        $user_info = Yii::$app->user->identity->userinfo;
        $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
        $rolenames =  ArrayHelper::map($roles, 'name','name');

        $targetAcademic = $this->getTargetAcademicByBranchProgram();

        $targetAcademicSeason = $this->getTargetAcademicBySeason();

        $targetEmergencyFund = $this->getTargetEmergencyFundByBranchProgram();

        $targetEmergencyFundSeason = $this->getTargetEmergencyFundBySeason();

        $targetFood = $this->getTargetFoodByBranchProgram();

        $targetFoodSeason = $this->getTargetFoodBySeason();

        $targetFreebie = $this->getTargetFreebieByBranchProgram();

        $targetFreebieSeason = $this->getTargetFreebieBySeason();

        $targetIncome = $this->getTargetIncomeByBranchProgram();

        $targetIncomeSeason = $this->getTargetIncomeBySeason();

        $targetProgram = $this->getTargetProgramByBranchProgram();

        $targetProgramSeason = $this->getTargetProgramBySeason();

        $targetRebate = $this->getTargetRebateByBranchProgram();

        $targetRebateSeason = $this->getTargetRebateBySeason();

        $targetReview = $this->getTargetReviewByBranchProgram();

        $targetReviewSeason = $this->getTargetReviewBySeason();

        $targetStaffSalary = $this->getTargetStaffSalaryByBranchProgram();

        $targetStaffSalarySeason = $this->getTargetStaffSalaryBySeason();

        $targetTransportation = $this->getTargetTransportationByBranchProgram();

        $targetTransportationSeason = $this->getTargetTransportationBySeason();

        $targetUtility = $this->getTargetUtilityByBranchProgram();

        $targetUtilitySeason = $this->getTargetUtilityBySeason();

        $targetVenueRental = $this->getTargetVenueRentalByBranchProgram();

        $targetVenueRentalSeason = $this->getTargetVenueRentalBySeason();

        if(in_array('TopManagement',$rolenames)){

            if(!empty($date))
            {
                $incomeEnrolments = $this->getOverallIncomeEnrolmentByBranchProgramRawSql($date[0][0], $date[0][1]);

                $incomeEnrolmentsSeason = $this->getOverallIncomeEnrolmentBySeasonRawSql($date[0][0], $date[0][1]);

                $incomeFreebies = $this->getOverallIncomeFreebieByBranchProgramRawSql($date[0][0], $date[0][1]);

                $incomeFreebiesSeason = $this->getOverallIncomeFreebieBySeasonRawSql($date[0][0], $date[0][1]);

                $pettyExpenses = $this->getOverallPettyExpenseByBranchProgramRawSql($date[0][0], $date[0][1]);

                $pettyExpensesSeason = $this->getOverallPettyExpenseBySeasonRawSql($date[0][0], $date[0][1]);

                $photocopyExpenses = $this->getOverallPhotocopyExpenseByBranchProgramRawSql($date[0][0], $date[0][1]);

                $photocopyExpensesSeason = $this->getOverallPhotocopyExpenseBySeasonRawSql($date[0][0], $date[0][1]);

                $otherExpenses = $this->getOverallOtherExpenseByBranchProgramRawSql($date[0][0], $date[0][1]);

                $otherExpensesSeason = $this->getOverallOtherExpenseBySeasonRawSql($date[0][0], $date[0][1]);

                $bankDeposits = $this->getOverallBankDepositByBranchProgramRawSql($date[0][0], $date[0][1]);

                $bankDepositsSeason = $this->getOverallBankDepositBySeasonRawSql($date[0][0], $date[0][1]);

                $operatingExpenses = $this->getOverallOperatingExpenseByBranchProgramRawSql($date[0][0], $date[0][1]);

                $operatingExpensesSeason = $this->getOverallOperatingExpenseBySeasonRawSql($date[0][0], $date[0][1]);

                $budgetProposals = $this->getOverallBudgetProposalByBranchProgramRawSql($date[0][0], $date[0][1]);

                $budgetProposalsSeason = $this->getOverallBudgetProposalBySeasonRawSql($date[0][0], $date[0][1]);

                $branchTransfers = $this->getOverallBranchTransferByBranchProgramRawSql($date[0][0], $date[0][1]);

                $branchTransfersSeason = $this->getOverallBranchTransferBySeasonRawSql($date[0][0], $date[0][1]);

                $beginningCoh = $this->getBeginningCashByBranchProgram($date[0][0], $date[0][1]);

                $beginningCohSeason = $this->getBeginningCashBySeason($date[0][0], $date[0][1]);

                $auditTargetSeason = Season::find()
                            ->select([
                                'accounting_season.id as id',
                                'accounting_season.branch_program_id as branch_program_id',
                                'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                'COALESCE(targetIncome.total, 0) as totalGross',
                                'COALESCE((targetIncome.total - ((targetIncome.total/1.12)*0.12)), 0) as totalGrossIncome',
                                'COALESCE(targetProgram.total + 
                                          targetVenueRental.total + 
                                          targetFreebie.total +
                                          targetReview.total +
                                          targetFood.total +
                                          targetTransportation.total +
                                          targetStaffSalary.total + 
                                          targetRebate.total +
                                          targetUtility.total +
                                          targetAcademic.total +
                                          targetEmergencyFund.total
                                          , 0) as totalExpenses',
                                'COALESCE((targetIncome.total - ((targetIncome.total/1.12)*0.12)) - (targetProgram.total + 
                                          targetVenueRental.total + 
                                          targetFreebie.total +
                                          targetReview.total +
                                          targetFood.total +
                                          targetTransportation.total +
                                          targetStaffSalary.total + 
                                          targetRebate.total +
                                          targetUtility.total +
                                          targetAcademic.total +
                                          targetEmergencyFund.total), 0) as expectedIncome',
                                'COALESCE(((targetIncome.total - ((targetIncome.total/1.12)*0.12)) - (targetProgram.total + 
                                          targetVenueRental.total + 
                                          targetFreebie.total +
                                          targetReview.total +
                                          targetFood.total +
                                          targetTransportation.total +
                                          targetStaffSalary.total + 
                                          targetRebate.total +
                                          targetUtility.total +
                                          targetAcademic.total +
                                          targetEmergencyFund.total) - ((targetIncome.total - ((targetIncome.total/1.12)*0.12)) - (targetProgram.total + 
                                          targetVenueRental.total + 
                                          targetFreebie.total +
                                          targetReview.total +
                                          targetFood.total +
                                          targetTransportation.total +
                                          targetStaffSalary.total + 
                                          targetRebate.total +
                                          targetUtility.total +
                                          targetAcademic.total +
                                          targetEmergencyFund.total)) * accounting_target_royalty_fee.percentage), 0) as netIncome'
                            ])

                            ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                            ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                            ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                            ->leftJoin('accounting_target_royalty_fee','accounting_target_royalty_fee.season_id = accounting_season.id')
                            ->leftJoin(['targetAcademic' => '('.$targetAcademicSeason.')'],'targetAcademic.id = accounting_season.id')
                            ->leftJoin(['targetEmergencyFund' => '('.$targetEmergencyFundSeason.')'],'targetEmergencyFund.id = accounting_season.id')
                            ->leftJoin(['targetFood' => '('.$targetFoodSeason.')'],'targetFood.id = accounting_season.id')
                            ->leftJoin(['targetFreebie' => '('.$targetFreebieSeason.')'],'targetFreebie.id = accounting_season.id')
                            ->leftJoin(['targetIncome' => '('.$targetIncomeSeason.')'],'targetIncome.id = accounting_season.id')
                            ->leftJoin(['targetProgram' => '('.$targetProgramSeason.')'],'targetProgram.id = accounting_season.id')
                            ->leftJoin(['targetRebate' => '('.$targetRebateSeason.')'],'targetRebate.id = accounting_season.id')
                            ->leftJoin(['targetReview' => '('.$targetReviewSeason.')'],'targetReview.id = accounting_season.id')
                            ->leftJoin(['targetStaffSalary' => '('.$targetStaffSalarySeason.')'],'targetStaffSalary.id = accounting_season.id')
                            ->leftJoin(['targetTransportation' => '('.$targetTransportationSeason.')'],'targetTransportation.id = accounting_season.id')
                            ->leftJoin(['targetUtility' => '('.$targetUtilitySeason.')'],'targetUtility.id = accounting_season.id')
                            ->leftJoin(['targetVenueRental' => '('.$targetVenueRentalSeason.')'],'targetVenueRental.id = accounting_season.id')
                            ->createCommand()
                            ->getRawSql();

                $auditTargetBranch = BranchProgram::find()
                                ->select([
                                    'accounting_branch_program.id as id',
                                    'sum(COALESCE(targetSeason.totalGross, 0)) as totalGross',
                                    'sum(COALESCE(targetSeason.totalGrossIncome, 0)) as totalGrossIncome',
                                    'sum(COALESCE(targetSeason.totalExpenses, 0)) as totalExpenses',
                                    'sum(COALESCE(targetSeason.expectedIncome, 0)) as expectedIncome',
                                    'sum(COALESCE(targetSeason.netIncome, 0)) as netIncome',
                                ])
                                ->leftJoin(['targetSeason' => '('.$auditTargetSeason.')'],'targetSeason.branch_program_id = accounting_branch_program.id')
                                ->groupBy(['targetSeason.branch_program_id'])
                                ->createCommand()                              
                                ->getRawSql();

                $incomeEnrolmentsCash = $this->getIncomeEnrolmentByBranchProgramRawSql($date[0][0], $date[0][1], 'Cash');

                $incomeEnrolmentsCashSeason = $this->getIncomeEnrolmentBySeasonRawSql($date[0][0], $date[0][1], 'Cash');

                $incomeFreebiesCash = $this->getIncomeFreebieByBranchProgramRawSql($date[0][0], $date[0][1], 'Cash');

                $incomeFreebiesCashSeason = $this->getIncomeFreebieBySeasonRawSql($date[0][0], $date[0][1], 'Cash');

                $pettyExpensesCash = $this->getPettyExpenseByBranchProgramRawSql($date[0][0], $date[0][1], 'Cash');

                $pettyExpensesCashSeason = $this->getPettyExpenseBySeasonRawSql($date[0][0], $date[0][1], 'Cash');

                $photocopyExpensesCash = $this->getPhotocopyExpenseByBranchProgramRawSql($date[0][0], $date[0][1], 'Cash');

                $photocopyExpensesCashSeason = $this->getPhotocopyExpenseBySeasonRawSql($date[0][0], $date[0][1], 'Cash');

                $otherExpensesCash = $this->getOtherExpenseByBranchProgramRawSql($date[0][0], $date[0][1], 'Cash');

                $otherExpensesCashSeason = $this->getOtherExpenseBySeasonRawSql($date[0][0], $date[0][1], 'Cash');

                $bankDepositsCash = $this->getBankDepositByBranchProgramRawSql($date[0][0], $date[0][1], 'Cash');

                $bankDepositsCashSeason = $this->getBankDepositBySeasonRawSql($date[0][0], $date[0][1], 'Cash');

                $operatingExpensesCash = $this->getOperatingExpenseByBranchProgramRawSql($date[0][0], $date[0][1], 'Cash');

                $operatingExpensesCashSeason = $this->getOperatingExpenseBySeasonRawSql($date[0][0], $date[0][1], 'Cash');

                $budgetProposalsCash = $this->getBudgetProposalByBranchProgramRawSql($date[0][0], $date[0][1], 'Cash');

                $budgetProposalsCashSeason = $this->getBudgetProposalBySeasonRawSql($date[0][0], $date[0][1], 'Cash');

                $branchTransfersCash = $this->getBranchTransferByBranchProgramRawSql($date[0][0], $date[0][1], 'Cash');

                $branchTransfersCashSeason = $this->getBranchTransferBySeasonRawSql($date[0][0], $date[0][1], 'Cash');

                $auditSummary = $access ? $access->branch_program_id != '' ? BranchProgram::find()
                            ->select([
                                'accounting_branch_program.id as id',
                                'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                'finalBeginningCoh.coh as beginningCoh',
                                'finalBeginningCoh.cob as beginningCob',
                                'finalBankDeposits.total as bankDepositsTotal',
                                '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                '(
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0) +
                                    COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBankDeposits.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0) +
                                    COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal',
                                'targetBranch.totalGross as targetGross',
                                'targetBranch.totalGrossIncome as targetGrossIncome',
                                'targetBranch.totalExpenses as targetExpenses',
                                'targetBranch.expectedIncome as targetExpectedIncome',
                                'targetBranch.netIncome as targetNetIncome',
                            ])
                            ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                            ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                            ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolments.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                            ->leftJoin(['finalFreebies' => '('.$incomeFreebies.')'],'finalFreebies.id = accounting_branch_program.id')
                            ->leftJoin(['finalBudgetProposals' => '('.$budgetProposals.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                            ->leftJoin(['finalPettyExpense' => '('.$pettyExpenses.')'],'finalPettyExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpenses.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalOtherExpense' => '('.$otherExpenses.')'],'finalOtherExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalBankDeposits' => '('.$bankDeposits.')'],'finalBankDeposits.id = accounting_branch_program.id')
                            ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpenses.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                            ->leftJoin(['finalBranchTransfers' => '('.$branchTransfers.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                            ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                            ->leftJoin(['targetBranch' => '('.$auditTargetBranch.')'],'targetBranch.id = accounting_branch_program.id')
                            ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                            ->asArray()
                            ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                            ->all() : BranchProgram::find()
                            ->select([
                                'accounting_branch_program.id as id',
                                'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                'finalBeginningCoh.coh as beginningCoh',
                                'finalBeginningCoh.cob as beginningCob',
                                'finalBankDeposits.total as bankDepositsTotal',
                                '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                '(
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0) +
                                    COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBankDeposits.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0) +
                                    COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal',
                                'targetBranch.totalGross as targetGross',
                                'targetBranch.totalGrossIncome as targetGrossIncome',
                                'targetBranch.totalExpenses as targetExpenses',
                                'targetBranch.expectedIncome as targetExpectedIncome',
                                'targetBranch.netIncome as targetNetIncome',
                            ])
                            ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                            ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                            ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolments.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                            ->leftJoin(['finalFreebies' => '('.$incomeFreebies.')'],'finalFreebies.id = accounting_branch_program.id')
                            ->leftJoin(['finalBudgetProposals' => '('.$budgetProposals.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                            ->leftJoin(['finalPettyExpense' => '('.$pettyExpenses.')'],'finalPettyExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpenses.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalOtherExpense' => '('.$otherExpenses.')'],'finalOtherExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalBankDeposits' => '('.$bankDeposits.')'],'finalBankDeposits.id = accounting_branch_program.id')
                            ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpenses.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                            ->leftJoin(['finalBranchTransfers' => '('.$branchTransfers.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                            ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                            ->leftJoin(['targetBranch' => '('.$auditTargetBranch.')'],'targetBranch.id = accounting_branch_program.id')
                            ->asArray()
                            ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                            ->all() : BranchProgram::find()
                            ->select([
                                'accounting_branch_program.id as id',
                                'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                'finalBeginningCoh.coh as beginningCoh',
                                'finalBeginningCoh.cob as beginningCob',
                                'finalBankDeposits.total as bankDepositsTotal',
                                '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                '(
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0) +
                                    COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBankDeposits.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0) +
                                    COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal',
                                'targetBranch.totalGross as targetGross',
                                'targetBranch.totalGrossIncome as targetGrossIncome',
                                'targetBranch.totalExpenses as targetExpenses',
                                'targetBranch.expectedIncome as targetExpectedIncome',
                                'targetBranch.netIncome as targetNetIncome',
                            ])
                            ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                            ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                            ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolments.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                            ->leftJoin(['finalFreebies' => '('.$incomeFreebies.')'],'finalFreebies.id = accounting_branch_program.id')
                            ->leftJoin(['finalBudgetProposals' => '('.$budgetProposals.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                            ->leftJoin(['finalPettyExpense' => '('.$pettyExpenses.')'],'finalPettyExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpenses.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalOtherExpense' => '('.$otherExpenses.')'],'finalOtherExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalBankDeposits' => '('.$bankDeposits.')'],'finalBankDeposits.id = accounting_branch_program.id')
                            ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpenses.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                            ->leftJoin(['finalBranchTransfers' => '('.$branchTransfers.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                            ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                            ->leftJoin(['targetBranch' => '('.$auditTargetBranch.')'],'targetBranch.id = accounting_branch_program.id')
                            ->asArray()
                            ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                            ->all();

                $auditSummarySeason = $access ? $access->branch_program_id != '' ? Season::find()
                            ->select([
                                'accounting_season.id as id',
                                'accounting_season.branch_program_id as branch_program_id',
                                'finalBeginningCoh.coh as beginningCoh',
                                'finalBeginningCoh.cob as beginningCob',
                                'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                'finalBankDeposits.total as bankDepositsTotal',
                                '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                '(
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBranchTransfers.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBankDeposits.total, 0) + 
                                    COALESCE(finalBranchTransfers.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal',
                                'COALESCE(targets.totalGross, 0) as targetGross',
                                'COALESCE(targets.totalGrossIncome, 0) as targetGrossIncome',
                                'COALESCE(targets.totalExpenses, 0) as targetExpenses',
                                'COALESCE(targets.expectedIncome, 0) as targetExpectedIncome',
                                'COALESCE(targets.netIncome, 0) as targetNetIncome',
                            ])
                            ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                            ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                            ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                            ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                            ->leftJoin(['finalFreebies' => '('.$incomeFreebiesSeason.')'],'finalFreebies.id = accounting_season.id')
                            ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesSeason.')'],'finalPettyExpense.id = accounting_season.id')
                            ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                            ->leftJoin(['finalOtherExpense' => '('.$otherExpensesSeason.')'],'finalOtherExpense.id = accounting_season.id')
                            ->leftJoin(['finalBankDeposits' => '('.$bankDepositsSeason.')'],'finalBankDeposits.id = accounting_season.id')
                            ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                            ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                            ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                            ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                            ->leftJoin(['targets' => '('.$auditTargetSeason.')'],'targets.id = accounting_season.id')
                            ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                            ->asArray()
                            ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                            ->all() : Season::find()
                            ->select([
                                'accounting_season.id as id',
                                'accounting_season.branch_program_id as branch_program_id',
                                'finalBeginningCoh.coh as beginningCoh',
                                'finalBeginningCoh.cob as beginningCob',
                                'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                'finalBankDeposits.total as bankDepositsTotal',
                                '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                '(
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBranchTransfers.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBankDeposits.total, 0) + 
                                    COALESCE(finalBranchTransfers.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal',
                                'COALESCE(targets.totalGross, 0) as targetGross',
                                'COALESCE(targets.totalGrossIncome, 0) as targetGrossIncome',
                                'COALESCE(targets.totalExpenses, 0) as targetExpenses',
                                'COALESCE(targets.expectedIncome, 0) as targetExpectedIncome',
                                'COALESCE(targets.netIncome, 0) as targetNetIncome',
                            ])
                            ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                            ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                            ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                            ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                            ->leftJoin(['finalFreebies' => '('.$incomeFreebiesSeason.')'],'finalFreebies.id = accounting_season.id')
                            ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesSeason.')'],'finalPettyExpense.id = accounting_season.id')
                            ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                            ->leftJoin(['finalOtherExpense' => '('.$otherExpensesSeason.')'],'finalOtherExpense.id = accounting_season.id')
                            ->leftJoin(['finalBankDeposits' => '('.$bankDepositsSeason.')'],'finalBankDeposits.id = accounting_season.id')
                            ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                            ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                            ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                            ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                            ->leftJoin(['targets' => '('.$auditTargetSeason.')'],'targets.id = accounting_season.id')
                            ->asArray()
                            ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                            ->all() : Season::find()
                            ->select([
                                'accounting_season.id as id',
                                'accounting_season.branch_program_id as branch_program_id',
                                'finalBeginningCoh.coh as beginningCoh',
                                'finalBeginningCoh.cob as beginningCob',
                                'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                'finalBankDeposits.total as bankDepositsTotal',
                                '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                '(
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBranchTransfers.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBankDeposits.total, 0) + 
                                    COALESCE(finalBranchTransfers.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal',
                                'COALESCE(targets.totalGross, 0) as targetGross',
                                'COALESCE(targets.totalGrossIncome, 0) as targetGrossIncome',
                                'COALESCE(targets.totalExpenses, 0) as targetExpenses',
                                'COALESCE(targets.expectedIncome, 0) as targetExpectedIncome',
                                'COALESCE(targets.netIncome, 0) as targetNetIncome',
                            ])
                            ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                            ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                            ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                            ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                            ->leftJoin(['finalFreebies' => '('.$incomeFreebiesSeason.')'],'finalFreebies.id = accounting_season.id')
                            ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesSeason.')'],'finalPettyExpense.id = accounting_season.id')
                            ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                            ->leftJoin(['finalOtherExpense' => '('.$otherExpensesSeason.')'],'finalOtherExpense.id = accounting_season.id')
                            ->leftJoin(['finalBankDeposits' => '('.$bankDepositsSeason.')'],'finalBankDeposits.id = accounting_season.id')
                            ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                            ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                            ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                            ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                            ->leftJoin(['targets' => '('.$auditTargetSeason.')'],'targets.id = accounting_season.id')
                            ->asArray()
                            ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                            ->all();

                $auditSummaryCash = $access ? $access->branch_program_id != '' ? BranchProgram::find()
                                ->select([
                                    'accounting_branch_program.id as id',
                                    'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsCash.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesCash.')'],'finalFreebies.id = accounting_branch_program.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsCash.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesCash.')'],'finalPettyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesCash.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesCash.')'],'finalOtherExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsCash.')'],'finalBankDeposits.id = accounting_branch_program.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesCash.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersCash.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : BranchProgram::find()
                                ->select([
                                    'accounting_branch_program.id as id',
                                    'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsCash.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesCash.')'],'finalFreebies.id = accounting_branch_program.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsCash.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesCash.')'],'finalPettyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesCash.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesCash.')'],'finalOtherExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsCash.')'],'finalBankDeposits.id = accounting_branch_program.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesCash.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersCash.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : BranchProgram::find()
                                ->select([
                                    'accounting_branch_program.id as id',
                                    'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsCash.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesCash.')'],'finalFreebies.id = accounting_branch_program.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsCash.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesCash.')'],'finalPettyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesCash.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesCash.')'],'finalOtherExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsCash.')'],'finalBankDeposits.id = accounting_branch_program.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesCash.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersCash.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all();

                $auditSummaryCashSeason = $access ? $access->branch_program_id != '' ? Season::find()
                                ->select([
                                    'accounting_season.id as id',
                                    'accounting_season.branch_program_id as branch_program_id',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsCashSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesCashSeason.')'],'finalFreebies.id = accounting_season.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesCashSeason.')'],'finalPettyExpense.id = accounting_season.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesCashSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesCashSeason.')'],'finalOtherExpense.id = accounting_season.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsCashSeason.')'],'finalBankDeposits.id = accounting_season.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesCashSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsCashSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersCashSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : Season::find()
                                ->select([
                                    'accounting_season.id as id',
                                    'accounting_season.branch_program_id as branch_program_id',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsCashSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesCashSeason.')'],'finalFreebies.id = accounting_season.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesCashSeason.')'],'finalPettyExpense.id = accounting_season.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesCashSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesCashSeason.')'],'finalOtherExpense.id = accounting_season.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsCashSeason.')'],'finalBankDeposits.id = accounting_season.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesCashSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsCashSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersCashSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : Season::find()
                                ->select([
                                    'accounting_season.id as id',
                                    'accounting_season.branch_program_id as branch_program_id',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsCashSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesCashSeason.')'],'finalFreebies.id = accounting_season.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesCashSeason.')'],'finalPettyExpense.id = accounting_season.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesCashSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesCashSeason.')'],'finalOtherExpense.id = accounting_season.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsCashSeason.')'],'finalBankDeposits.id = accounting_season.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesCashSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsCashSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersCashSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all();

                $incomeEnrolmentsNonCash = $this->getIncomeEnrolmentByBranchProgramRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $incomeEnrolmentsNonCashSeason = $this->getIncomeEnrolmentBySeasonRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $incomeFreebiesNonCash = $this->getIncomeFreebieByBranchProgramRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $incomeFreebiesNonCashSeason = $this->getIncomeFreebieBySeasonRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $pettyExpensesNonCash = $this->getPettyExpenseByBranchProgramRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $pettyExpensesNonCashSeason = $this->getPettyExpenseBySeasonRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $photocopyExpensesNonCash = $this->getPhotocopyExpenseByBranchProgramRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $photocopyExpensesNonCashSeason = $this->getPhotocopyExpenseBySeasonRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $otherExpensesNonCash = $this->getOtherExpenseByBranchProgramRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $otherExpensesNonCashSeason = $this->getOtherExpenseBySeasonRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $bankDepositsNonCash = $this->getBankDepositByBranchProgramRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $bankDepositsNonCashSeason = $this->getBankDepositBySeasonRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $operatingExpensesNonCash = $this->getOperatingExpenseByBranchProgramRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $operatingExpensesNonCashSeason = $this->getOperatingExpenseBySeasonRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $budgetProposalsNonCash = $this->getBudgetProposalByBranchProgramRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $budgetProposalsNonCashSeason = $this->getBudgetProposalBySeasonRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $branchTransfersNonCash = $this->getBranchTransferByBranchProgramRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $branchTransfersNonCashSeason = $this->getBranchTransferBySeasonRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $auditSummaryNonCash = $access ? $access->branch_program_id != '' ? BranchProgram::find()
                            ->select([
                                'accounting_branch_program.id as id',
                                'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                'finalBeginningCoh.coh as beginningCoh',
                                'finalBeginningCoh.cob as beginningCob',
                                'finalBankDeposits.total as bankDepositsTotal',
                                '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                '(
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0) +
                                    COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                '((COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBankDeposits.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0) +
                                    COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal'
                            ])
                            ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                            ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                            ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsNonCash.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                            ->leftJoin(['finalFreebies' => '('.$incomeFreebiesNonCash.')'],'finalFreebies.id = accounting_branch_program.id')
                            ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsNonCash.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                            ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesNonCash.')'],'finalPettyExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesNonCash.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalOtherExpense' => '('.$otherExpensesNonCash.')'],'finalOtherExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalBankDeposits' => '('.$bankDepositsNonCash.')'],'finalBankDeposits.id = accounting_branch_program.id')
                            ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesNonCash.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                            ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersNonCash.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                            ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                            ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                            ->asArray()
                            ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                            ->all() : BranchProgram::find()
                            ->select([
                                'accounting_branch_program.id as id',
                                'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                'finalBeginningCoh.coh as beginningCoh',
                                'finalBeginningCoh.cob as beginningCob',
                                'finalBankDeposits.total as bankDepositsTotal',
                                '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                '(
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0) +
                                    COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                '((COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBankDeposits.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0) +
                                    COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal'
                            ])
                            ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                            ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                            ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsNonCash.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                            ->leftJoin(['finalFreebies' => '('.$incomeFreebiesNonCash.')'],'finalFreebies.id = accounting_branch_program.id')
                            ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsNonCash.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                            ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesNonCash.')'],'finalPettyExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesNonCash.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalOtherExpense' => '('.$otherExpensesNonCash.')'],'finalOtherExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalBankDeposits' => '('.$bankDepositsNonCash.')'],'finalBankDeposits.id = accounting_branch_program.id')
                            ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesNonCash.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                            ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersNonCash.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                            ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                            ->asArray()
                            ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                            ->all() : BranchProgram::find()
                            ->select([
                                'accounting_branch_program.id as id',
                                'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                'finalBeginningCoh.coh as beginningCoh',
                                'finalBeginningCoh.cob as beginningCob',
                                'finalBankDeposits.total as bankDepositsTotal',
                                '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                '(
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0) +
                                    COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                '((COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBankDeposits.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0) +
                                    COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal'
                            ])
                            ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                            ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                            ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsNonCash.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                            ->leftJoin(['finalFreebies' => '('.$incomeFreebiesNonCash.')'],'finalFreebies.id = accounting_branch_program.id')
                            ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsNonCash.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                            ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesNonCash.')'],'finalPettyExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesNonCash.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalOtherExpense' => '('.$otherExpensesNonCash.')'],'finalOtherExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalBankDeposits' => '('.$bankDepositsNonCash.')'],'finalBankDeposits.id = accounting_branch_program.id')
                            ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesNonCash.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                            ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersNonCash.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                            ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                            ->asArray()
                            ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                            ->all();

                $auditSummaryNonCashSeason = $access ? $access->branch_program_id != '' ? Season::find()
                            ->select([
                                'accounting_season.id as id',
                                'accounting_season.branch_program_id as branch_program_id',
                                'finalBeginningCoh.coh as beginningCoh',
                                'finalBeginningCoh.cob as beginningCob',
                                'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                'finalBankDeposits.total as bankDepositsTotal',
                                '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                '(
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBranchTransfers.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                '((COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBankDeposits.total, 0) + 
                                    COALESCE(finalBranchTransfers.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal'
                            ])
                            ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                            ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                            ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                            ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsNonCashSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                            ->leftJoin(['finalFreebies' => '('.$incomeFreebiesNonCashSeason.')'],'finalFreebies.id = accounting_season.id')
                            ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesNonCashSeason.')'],'finalPettyExpense.id = accounting_season.id')
                            ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesNonCashSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                            ->leftJoin(['finalOtherExpense' => '('.$otherExpensesNonCashSeason.')'],'finalOtherExpense.id = accounting_season.id')
                            ->leftJoin(['finalBankDeposits' => '('.$bankDepositsNonCashSeason.')'],'finalBankDeposits.id = accounting_season.id')
                            ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesNonCashSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                            ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsNonCashSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                            ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersNonCashSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                            ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                            ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                            ->asArray()
                            ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                            ->all() : Season::find()
                            ->select([
                                'accounting_season.id as id',
                                'accounting_season.branch_program_id as branch_program_id',
                                'finalBeginningCoh.coh as beginningCoh',
                                'finalBeginningCoh.cob as beginningCob',
                                'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                'finalBankDeposits.total as bankDepositsTotal',
                                '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                '(
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBranchTransfers.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                '((COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBankDeposits.total, 0) + 
                                    COALESCE(finalBranchTransfers.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal'
                            ])
                            ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                            ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                            ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                            ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsNonCashSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                            ->leftJoin(['finalFreebies' => '('.$incomeFreebiesNonCashSeason.')'],'finalFreebies.id = accounting_season.id')
                            ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesNonCashSeason.')'],'finalPettyExpense.id = accounting_season.id')
                            ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesNonCashSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                            ->leftJoin(['finalOtherExpense' => '('.$otherExpensesNonCashSeason.')'],'finalOtherExpense.id = accounting_season.id')
                            ->leftJoin(['finalBankDeposits' => '('.$bankDepositsNonCashSeason.')'],'finalBankDeposits.id = accounting_season.id')
                            ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesNonCashSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                            ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsNonCashSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                            ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersNonCashSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                            ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                            ->asArray()
                            ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                            ->all() : Season::find()
                            ->select([
                                'accounting_season.id as id',
                                'accounting_season.branch_program_id as branch_program_id',
                                'finalBeginningCoh.coh as beginningCoh',
                                'finalBeginningCoh.cob as beginningCob',
                                'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                'finalBankDeposits.total as bankDepositsTotal',
                                '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                '(
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBranchTransfers.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                '((COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBankDeposits.total, 0) + 
                                    COALESCE(finalBranchTransfers.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal'
                            ])
                            ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                            ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                            ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                            ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsNonCashSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                            ->leftJoin(['finalFreebies' => '('.$incomeFreebiesNonCashSeason.')'],'finalFreebies.id = accounting_season.id')
                            ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesNonCashSeason.')'],'finalPettyExpense.id = accounting_season.id')
                            ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesNonCashSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                            ->leftJoin(['finalOtherExpense' => '('.$otherExpensesNonCashSeason.')'],'finalOtherExpense.id = accounting_season.id')
                            ->leftJoin(['finalBankDeposits' => '('.$bankDepositsNonCashSeason.')'],'finalBankDeposits.id = accounting_season.id')
                            ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesNonCashSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                            ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsNonCashSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                            ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersNonCashSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                            ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                            ->asArray()
                            ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                            ->all();
            }else{
                $incomeEnrolments = $this->getOverallIncomeEnrolmentByBranchProgramRawSql();

                $incomeEnrolmentsSeason = $this->getOverallIncomeEnrolmentBySeasonRawSql();

                $incomeFreebies = $this->getOverallIncomeFreebieByBranchProgramRawSql();

                $incomeFreebiesSeason = $this->getOverallIncomeFreebieBySeasonRawSql();

                $pettyExpenses = $this->getOverallPettyExpenseByBranchProgramRawSql();

                $pettyExpensesSeason = $this->getOverallPettyExpenseBySeasonRawSql();

                $photocopyExpenses = $this->getOverallPhotocopyExpenseByBranchProgramRawSql();

                $photocopyExpensesSeason = $this->getOverallPhotocopyExpenseBySeasonRawSql();

                $otherExpenses = $this->getOverallOtherExpenseByBranchProgramRawSql();

                $otherExpensesSeason = $this->getOverallOtherExpenseBySeasonRawSql();

                $bankDeposits = $this->getOverallBankDepositByBranchProgramRawSql();

                $bankDepositsSeason = $this->getOverallBankDepositBySeasonRawSql();

                $operatingExpenses = $this->getOverallOperatingExpenseByBranchProgramRawSql();

                $operatingExpensesSeason = $this->getOverallOperatingExpenseBySeasonRawSql();

                $budgetProposals = $this->getOverallBudgetProposalByBranchProgramRawSql();

                $budgetProposalsSeason = $this->getOverallBudgetProposalBySeasonRawSql();

                $branchTransfers = $this->getOverallBranchTransferByBranchProgramRawSql();

                $branchTransfersSeason = $this->getOverallBranchTransferBySeasonRawSql();

                $beginningCoh = $this->getBeginningCashByBranchProgram();

                $beginningCohSeason = $this->getBeginningCashBySeason();

                $auditTargetSeason = Season::find()
                            ->select([
                                'accounting_season.id as id',
                                'accounting_season.branch_program_id as branch_program_id',
                                'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                'COALESCE(targetIncome.total, 0) as totalGross',
                                'COALESCE((targetIncome.total - ((targetIncome.total/1.12)*0.12)), 0) as totalGrossIncome',
                                'COALESCE(targetProgram.total + 
                                          targetVenueRental.total + 
                                          targetFreebie.total +
                                          targetReview.total +
                                          targetFood.total +
                                          targetTransportation.total +
                                          targetStaffSalary.total + 
                                          targetRebate.total +
                                          targetUtility.total +
                                          targetAcademic.total +
                                          targetEmergencyFund.total
                                          , 0) as totalExpenses',
                                'COALESCE((targetIncome.total - ((targetIncome.total/1.12)*0.12)) - (targetProgram.total + 
                                          targetVenueRental.total + 
                                          targetFreebie.total +
                                          targetReview.total +
                                          targetFood.total +
                                          targetTransportation.total +
                                          targetStaffSalary.total + 
                                          targetRebate.total +
                                          targetUtility.total +
                                          targetAcademic.total +
                                          targetEmergencyFund.total), 0) as expectedIncome',
                                'COALESCE(((targetIncome.total - ((targetIncome.total/1.12)*0.12)) - (targetProgram.total + 
                                          targetVenueRental.total + 
                                          targetFreebie.total +
                                          targetReview.total +
                                          targetFood.total +
                                          targetTransportation.total +
                                          targetStaffSalary.total + 
                                          targetRebate.total +
                                          targetUtility.total +
                                          targetAcademic.total +
                                          targetEmergencyFund.total) - ((targetIncome.total - ((targetIncome.total/1.12)*0.12)) - (targetProgram.total + 
                                          targetVenueRental.total + 
                                          targetFreebie.total +
                                          targetReview.total +
                                          targetFood.total +
                                          targetTransportation.total +
                                          targetStaffSalary.total + 
                                          targetRebate.total +
                                          targetUtility.total +
                                          targetAcademic.total +
                                          targetEmergencyFund.total)) * accounting_target_royalty_fee.percentage), 0) as netIncome'
                            ])

                            ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                            ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                            ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                            ->leftJoin('accounting_target_royalty_fee','accounting_target_royalty_fee.season_id = accounting_season.id')
                            ->leftJoin(['targetAcademic' => '('.$targetAcademicSeason.')'],'targetAcademic.id = accounting_season.id')
                            ->leftJoin(['targetEmergencyFund' => '('.$targetEmergencyFundSeason.')'],'targetEmergencyFund.id = accounting_season.id')
                            ->leftJoin(['targetFood' => '('.$targetFoodSeason.')'],'targetFood.id = accounting_season.id')
                            ->leftJoin(['targetFreebie' => '('.$targetFreebieSeason.')'],'targetFreebie.id = accounting_season.id')
                            ->leftJoin(['targetIncome' => '('.$targetIncomeSeason.')'],'targetIncome.id = accounting_season.id')
                            ->leftJoin(['targetProgram' => '('.$targetProgramSeason.')'],'targetProgram.id = accounting_season.id')
                            ->leftJoin(['targetRebate' => '('.$targetRebateSeason.')'],'targetRebate.id = accounting_season.id')
                            ->leftJoin(['targetReview' => '('.$targetReviewSeason.')'],'targetReview.id = accounting_season.id')
                            ->leftJoin(['targetStaffSalary' => '('.$targetStaffSalarySeason.')'],'targetStaffSalary.id = accounting_season.id')
                            ->leftJoin(['targetTransportation' => '('.$targetTransportationSeason.')'],'targetTransportation.id = accounting_season.id')
                            ->leftJoin(['targetUtility' => '('.$targetUtilitySeason.')'],'targetUtility.id = accounting_season.id')
                            ->leftJoin(['targetVenueRental' => '('.$targetVenueRentalSeason.')'],'targetVenueRental.id = accounting_season.id')
                            ->createCommand()
                            ->getRawSql();

                $auditTargetBranch = BranchProgram::find()
                                ->select([
                                    'accounting_branch_program.id as id',
                                    'sum(COALESCE(targetSeason.totalGross, 0)) as totalGross',
                                    'sum(COALESCE(targetSeason.totalGrossIncome, 0)) as totalGrossIncome',
                                    'sum(COALESCE(targetSeason.totalExpenses, 0)) as totalExpenses',
                                    'sum(COALESCE(targetSeason.expectedIncome, 0)) as expectedIncome',
                                    'sum(COALESCE(targetSeason.netIncome, 0)) as netIncome',
                                ])
                                ->leftJoin(['targetSeason' => '('.$auditTargetSeason.')'],'targetSeason.branch_program_id = accounting_branch_program.id')
                                ->groupBy(['targetSeason.branch_program_id'])
                                ->createCommand()                              
                                ->getRawSql();

                $incomeEnrolmentsCash = $this->getIncomeEnrolmentByBranchProgramRawSql(null, null, 'Cash');

                $incomeEnrolmentsCashSeason = $this->getIncomeEnrolmentBySeasonRawSql(null, null, 'Cash');

                $incomeFreebiesCash = $this->getIncomeFreebieByBranchProgramRawSql(null, null, 'Cash');

                $incomeFreebiesCashSeason = $this->getIncomeFreebieBySeasonRawSql(null, null, 'Cash');

                $pettyExpensesCash = $this->getPettyExpenseByBranchProgramRawSql(null, null, 'Cash');

                $pettyExpensesCashSeason = $this->getPettyExpenseBySeasonRawSql(null, null, 'Cash');

                $photocopyExpensesCash = $this->getPhotocopyExpenseByBranchProgramRawSql(null, null, 'Cash');

                $photocopyExpensesCashSeason = $this->getPhotocopyExpenseBySeasonRawSql(null, null, 'Cash');

                $otherExpensesCash = $this->getOtherExpenseByBranchProgramRawSql(null, null, 'Cash');

                $otherExpensesCashSeason = $this->getOtherExpenseBySeasonRawSql(null, null, 'Cash');

                $bankDepositsCash = $this->getBankDepositByBranchProgramRawSql(null, null, 'Cash');

                $bankDepositsCashSeason = $this->getBankDepositBySeasonRawSql(null, null, 'Cash');

                $operatingExpensesCash = $this->getOperatingExpenseByBranchProgramRawSql(null, null, 'Cash');

                $operatingExpensesCashSeason = $this->getOperatingExpenseBySeasonRawSql(null, null, 'Cash');

                $budgetProposalsCash = $this->getBudgetProposalByBranchProgramRawSql(null, null, 'Cash');

                $budgetProposalsCashSeason = $this->getBudgetProposalBySeasonRawSql(null, null, 'Cash');

                $branchTransfersCash = $this->getBranchTransferByBranchProgramRawSql(null, null, 'Cash');

                $branchTransfersCashSeason = $this->getBranchTransferBySeasonRawSql(null, null, 'Cash');

                $auditSummary = $access ? $access->branch_program_id != '' ? BranchProgram::find()
                            ->select([
                                'accounting_branch_program.id as id',
                                'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                'finalBeginningCoh.coh as beginningCoh',
                                'finalBeginningCoh.cob as beginningCob',
                                'finalBankDeposits.total as bankDepositsTotal',
                                '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                '(
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0) +
                                    COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBankDeposits.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0) +
                                    COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal',
                                'targetBranch.totalGross as targetGross',
                                'targetBranch.totalGrossIncome as targetGrossIncome',
                                'targetBranch.totalExpenses as targetExpenses',
                                'targetBranch.expectedIncome as targetExpectedIncome',
                                'targetBranch.netIncome as targetNetIncome',
                            ])
                            ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                            ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                            ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolments.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                            ->leftJoin(['finalFreebies' => '('.$incomeFreebies.')'],'finalFreebies.id = accounting_branch_program.id')
                            ->leftJoin(['finalBudgetProposals' => '('.$budgetProposals.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                            ->leftJoin(['finalPettyExpense' => '('.$pettyExpenses.')'],'finalPettyExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpenses.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalOtherExpense' => '('.$otherExpenses.')'],'finalOtherExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalBankDeposits' => '('.$bankDeposits.')'],'finalBankDeposits.id = accounting_branch_program.id')
                            ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpenses.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                            ->leftJoin(['finalBranchTransfers' => '('.$branchTransfers.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                            ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                            ->leftJoin(['targetBranch' => '('.$auditTargetBranch.')'],'targetBranch.id = accounting_branch_program.id')
                            ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                            ->asArray()
                            ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                            ->all() : BranchProgram::find()
                            ->select([
                                'accounting_branch_program.id as id',
                                'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                'finalBeginningCoh.coh as beginningCoh',
                                'finalBeginningCoh.cob as beginningCob',
                                'finalBankDeposits.total as bankDepositsTotal',
                                '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                '(
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0) +
                                    COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBankDeposits.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0) +
                                    COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal',
                                'targetBranch.totalGross as targetGross',
                                'targetBranch.totalGrossIncome as targetGrossIncome',
                                'targetBranch.totalExpenses as targetExpenses',
                                'targetBranch.expectedIncome as targetExpectedIncome',
                                'targetBranch.netIncome as targetNetIncome',
                            ])
                            ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                            ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                            ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolments.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                            ->leftJoin(['finalFreebies' => '('.$incomeFreebies.')'],'finalFreebies.id = accounting_branch_program.id')
                            ->leftJoin(['finalBudgetProposals' => '('.$budgetProposals.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                            ->leftJoin(['finalPettyExpense' => '('.$pettyExpenses.')'],'finalPettyExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpenses.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalOtherExpense' => '('.$otherExpenses.')'],'finalOtherExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalBankDeposits' => '('.$bankDeposits.')'],'finalBankDeposits.id = accounting_branch_program.id')
                            ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpenses.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                            ->leftJoin(['finalBranchTransfers' => '('.$branchTransfers.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                            ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                            ->leftJoin(['targetBranch' => '('.$auditTargetBranch.')'],'targetBranch.id = accounting_branch_program.id')
                            ->asArray()
                            ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                            ->all() : BranchProgram::find()
                            ->select([
                                'accounting_branch_program.id as id',
                                'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                'finalBeginningCoh.coh as beginningCoh',
                                'finalBeginningCoh.cob as beginningCob',
                                'finalBankDeposits.total as bankDepositsTotal',
                                '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                '(
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0) +
                                    COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBankDeposits.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0) +
                                    COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal',
                                'targetBranch.totalGross as targetGross',
                                'targetBranch.totalGrossIncome as targetGrossIncome',
                                'targetBranch.totalExpenses as targetExpenses',
                                'targetBranch.expectedIncome as targetExpectedIncome',
                                'targetBranch.netIncome as targetNetIncome',
                            ])
                            ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                            ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                            ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolments.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                            ->leftJoin(['finalFreebies' => '('.$incomeFreebies.')'],'finalFreebies.id = accounting_branch_program.id')
                            ->leftJoin(['finalBudgetProposals' => '('.$budgetProposals.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                            ->leftJoin(['finalPettyExpense' => '('.$pettyExpenses.')'],'finalPettyExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpenses.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalOtherExpense' => '('.$otherExpenses.')'],'finalOtherExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalBankDeposits' => '('.$bankDeposits.')'],'finalBankDeposits.id = accounting_branch_program.id')
                            ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpenses.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                            ->leftJoin(['finalBranchTransfers' => '('.$branchTransfers.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                            ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                            ->leftJoin(['targetBranch' => '('.$auditTargetBranch.')'],'targetBranch.id = accounting_branch_program.id')
                            ->asArray()
                            ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                            ->all();

                $auditSummarySeason = $access ? $access->branch_program_id != '' ? Season::find()
                            ->select([
                                'accounting_season.id as id',
                                'accounting_season.branch_program_id as branch_program_id',
                                'finalBeginningCoh.coh as beginningCoh',
                                'finalBeginningCoh.cob as beginningCob',
                                'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                'finalBankDeposits.total as bankDepositsTotal',
                                '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                '(
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBranchTransfers.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBranchTransfers.total, 0) + 
                                    COALESCE(finalBankDeposits.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal',
                                'COALESCE(targets.totalGross, 0) as targetGross',
                                'COALESCE(targets.totalGrossIncome, 0) as targetGrossIncome',
                                'COALESCE(targets.totalExpenses, 0) as targetExpenses',
                                'COALESCE(targets.expectedIncome, 0) as targetExpectedIncome',
                                'COALESCE(targets.netIncome, 0) as targetNetIncome',
                            ])
                            ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                            ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                            ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                            ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                            ->leftJoin(['finalFreebies' => '('.$incomeFreebiesSeason.')'],'finalFreebies.id = accounting_season.id')
                            ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesSeason.')'],'finalPettyExpense.id = accounting_season.id')
                            ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                            ->leftJoin(['finalOtherExpense' => '('.$otherExpensesSeason.')'],'finalOtherExpense.id = accounting_season.id')
                            ->leftJoin(['finalBankDeposits' => '('.$bankDepositsSeason.')'],'finalBankDeposits.id = accounting_season.id')
                            ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                            ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                            ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                            ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                            ->leftJoin(['targets' => '('.$auditTargetSeason.')'],'targets.id = accounting_season.id')
                            ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                            ->asArray()
                            ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                            ->all() : Season::find()
                            ->select([
                                'accounting_season.id as id',
                                'accounting_season.branch_program_id as branch_program_id',
                                'finalBeginningCoh.coh as beginningCoh',
                                'finalBeginningCoh.cob as beginningCob',
                                'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                'finalBankDeposits.total as bankDepositsTotal',
                                '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                '(
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBranchTransfers.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBranchTransfers.total, 0) + 
                                    COALESCE(finalBankDeposits.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal',
                                'COALESCE(targets.totalGross, 0) as targetGross',
                                'COALESCE(targets.totalGrossIncome, 0) as targetGrossIncome',
                                'COALESCE(targets.totalExpenses, 0) as targetExpenses',
                                'COALESCE(targets.expectedIncome, 0) as targetExpectedIncome',
                                'COALESCE(targets.netIncome, 0) as targetNetIncome',
                            ])
                            ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                            ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                            ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                            ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                            ->leftJoin(['finalFreebies' => '('.$incomeFreebiesSeason.')'],'finalFreebies.id = accounting_season.id')
                            ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesSeason.')'],'finalPettyExpense.id = accounting_season.id')
                            ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                            ->leftJoin(['finalOtherExpense' => '('.$otherExpensesSeason.')'],'finalOtherExpense.id = accounting_season.id')
                            ->leftJoin(['finalBankDeposits' => '('.$bankDepositsSeason.')'],'finalBankDeposits.id = accounting_season.id')
                            ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                            ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                            ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                            ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                            ->leftJoin(['targets' => '('.$auditTargetSeason.')'],'targets.id = accounting_season.id')
                            ->asArray()
                            ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                            ->all() : Season::find()
                            ->select([
                                'accounting_season.id as id',
                                'accounting_season.branch_program_id as branch_program_id',
                                'finalBeginningCoh.coh as beginningCoh',
                                'finalBeginningCoh.cob as beginningCob',
                                'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                'finalBankDeposits.total as bankDepositsTotal',
                                '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                '(
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBranchTransfers.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBranchTransfers.total, 0) + 
                                    COALESCE(finalBankDeposits.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal',
                                'COALESCE(targets.totalGross, 0) as targetGross',
                                'COALESCE(targets.totalGrossIncome, 0) as targetGrossIncome',
                                'COALESCE(targets.totalExpenses, 0) as targetExpenses',
                                'COALESCE(targets.expectedIncome, 0) as targetExpectedIncome',
                                'COALESCE(targets.netIncome, 0) as targetNetIncome',
                            ])
                            ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                            ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                            ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                            ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                            ->leftJoin(['finalFreebies' => '('.$incomeFreebiesSeason.')'],'finalFreebies.id = accounting_season.id')
                            ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesSeason.')'],'finalPettyExpense.id = accounting_season.id')
                            ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                            ->leftJoin(['finalOtherExpense' => '('.$otherExpensesSeason.')'],'finalOtherExpense.id = accounting_season.id')
                            ->leftJoin(['finalBankDeposits' => '('.$bankDepositsSeason.')'],'finalBankDeposits.id = accounting_season.id')
                            ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                            ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                            ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                            ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                            ->leftJoin(['targets' => '('.$auditTargetSeason.')'],'targets.id = accounting_season.id')
                            ->asArray()
                            ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                            ->all();

                $auditSummaryCash = $access ? $access->branch_program_id != '' ? BranchProgram::find()
                                ->select([
                                    'accounting_branch_program.id as id',
                                    'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh,0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsCash.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesCash.')'],'finalFreebies.id = accounting_branch_program.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsCash.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesCash.')'],'finalPettyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesCash.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesCash.')'],'finalOtherExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsCash.')'],'finalBankDeposits.id = accounting_branch_program.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesCash.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersCash.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : BranchProgram::find()
                                ->select([
                                    'accounting_branch_program.id as id',
                                    'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh,0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsCash.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesCash.')'],'finalFreebies.id = accounting_branch_program.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsCash.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesCash.')'],'finalPettyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesCash.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesCash.')'],'finalOtherExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsCash.')'],'finalBankDeposits.id = accounting_branch_program.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesCash.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersCash.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : BranchProgram::find()
                                ->select([
                                    'accounting_branch_program.id as id',
                                    'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh,0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsCash.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesCash.')'],'finalFreebies.id = accounting_branch_program.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsCash.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesCash.')'],'finalPettyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesCash.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesCash.')'],'finalOtherExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsCash.')'],'finalBankDeposits.id = accounting_branch_program.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesCash.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersCash.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all();

                $auditSummaryCashSeason = $access ? $access->branch_program_id != '' ? Season::find()
                                ->select([
                                    'accounting_season.id as id',
                                    'accounting_season.branch_program_id as branch_program_id',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh,0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsCashSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesCashSeason.')'],'finalFreebies.id = accounting_season.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesCashSeason.')'],'finalPettyExpense.id = accounting_season.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesCashSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesCashSeason.')'],'finalOtherExpense.id = accounting_season.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsCashSeason.')'],'finalBankDeposits.id = accounting_season.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesCashSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsCashSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersCashSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : Season::find()
                                ->select([
                                    'accounting_season.id as id',
                                    'accounting_season.branch_program_id as branch_program_id',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh,0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsCashSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesCashSeason.')'],'finalFreebies.id = accounting_season.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesCashSeason.')'],'finalPettyExpense.id = accounting_season.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesCashSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesCashSeason.')'],'finalOtherExpense.id = accounting_season.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsCashSeason.')'],'finalBankDeposits.id = accounting_season.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesCashSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsCashSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersCashSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : Season::find()
                                ->select([
                                    'accounting_season.id as id',
                                    'accounting_season.branch_program_id as branch_program_id',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh,0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsCashSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesCashSeason.')'],'finalFreebies.id = accounting_season.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesCashSeason.')'],'finalPettyExpense.id = accounting_season.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesCashSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesCashSeason.')'],'finalOtherExpense.id = accounting_season.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsCashSeason.')'],'finalBankDeposits.id = accounting_season.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesCashSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsCashSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersCashSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all();

                $incomeEnrolmentsNonCash = $this->getIncomeEnrolmentByBranchProgramRawSql(null, null, 'Non-Cash');

                $incomeEnrolmentsNonCashSeason = $this->getIncomeEnrolmentBySeasonRawSql(null, null, 'Non-Cash');

                $incomeFreebiesNonCash = $this->getIncomeFreebieByBranchProgramRawSql(null, null, 'Non-Cash');

                $incomeFreebiesNonCashSeason = $this->getIncomeFreebieBySeasonRawSql(null, null, 'Non-Cash');

                $pettyExpensesNonCash = $this->getPettyExpenseByBranchProgramRawSql(null, null, 'Non-Cash');

                $pettyExpensesNonCashSeason = $this->getPettyExpenseBySeasonRawSql(null, null, 'Non-Cash');

                $photocopyExpensesNonCash = $this->getPhotocopyExpenseByBranchProgramRawSql(null, null, 'Non-Cash');

                $photocopyExpensesNonCashSeason = $this->getPhotocopyExpenseBySeasonRawSql(null, null, 'Non-Cash');

                $otherExpensesNonCash = $this->getOtherExpenseByBranchProgramRawSql(null, null, 'Non-Cash');

                $otherExpensesNonCashSeason = $this->getOtherExpenseBySeasonRawSql(null, null, 'Non-Cash');

                $bankDepositsNonCash = $this->getBankDepositByBranchProgramRawSql(null, null, 'Non-Cash');

                $bankDepositsNonCashSeason = $this->getBankDepositBySeasonRawSql(null, null, 'Non-Cash');

                $operatingExpensesNonCash = $this->getOperatingExpenseByBranchProgramRawSql(null, null, 'Non-Cash');

                $operatingExpensesNonCashSeason = $this->getOperatingExpenseBySeasonRawSql(null, null, 'Non-Cash');

                $budgetProposalsNonCash = $this->getBudgetProposalByBranchProgramRawSql(null, null, 'Non-Cash');

                $budgetProposalsNonCashSeason = $this->getBudgetProposalBySeasonRawSql(null, null, 'Non-Cash');

                $branchTransfersNonCash = $this->getBranchTransferByBranchProgramRawSql(null, null, 'Non-Cash');

                $branchTransfersNonCashSeason = $this->getBranchTransferBySeasonRawSql(null, null, 'Non-Cash');

                $auditSummaryNonCash = $access ? $access->branch_program_id != '' ? BranchProgram::find()
                            ->select([
                                'accounting_branch_program.id as id',
                                'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                'finalBeginningCoh.coh as beginningCoh',
                                'finalBeginningCoh.cob as beginningCob',
                                'finalBankDeposits.total as bankDepositsTotal',
                                '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                '(
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0) +
                                    COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                '((COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBankDeposits.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0) +
                                    COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal'
                            ])
                            ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                            ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                            ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsNonCash.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                            ->leftJoin(['finalFreebies' => '('.$incomeFreebiesNonCash.')'],'finalFreebies.id = accounting_branch_program.id')
                            ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsNonCash.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                            ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesNonCash.')'],'finalPettyExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesNonCash.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalOtherExpense' => '('.$otherExpensesNonCash.')'],'finalOtherExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalBankDeposits' => '('.$bankDepositsNonCash.')'],'finalBankDeposits.id = accounting_branch_program.id')
                            ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesNonCash.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                            ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersNonCash.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                            ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                            ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                            ->asArray()
                            ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                            ->all() : BranchProgram::find()
                            ->select([
                                'accounting_branch_program.id as id',
                                'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                'finalBeginningCoh.coh as beginningCoh',
                                'finalBeginningCoh.cob as beginningCob',
                                'finalBankDeposits.total as bankDepositsTotal',
                                '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                '(
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0) +
                                    COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                '((COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBankDeposits.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0) +
                                    COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal'
                            ])
                            ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                            ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                            ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsNonCash.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                            ->leftJoin(['finalFreebies' => '('.$incomeFreebiesNonCash.')'],'finalFreebies.id = accounting_branch_program.id')
                            ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsNonCash.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                            ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesNonCash.')'],'finalPettyExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesNonCash.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalOtherExpense' => '('.$otherExpensesNonCash.')'],'finalOtherExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalBankDeposits' => '('.$bankDepositsNonCash.')'],'finalBankDeposits.id = accounting_branch_program.id')
                            ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesNonCash.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                            ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersNonCash.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                            ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                            ->asArray()
                            ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                            ->all() : BranchProgram::find()
                            ->select([
                                'accounting_branch_program.id as id',
                                'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                'finalBeginningCoh.coh as beginningCoh',
                                'finalBeginningCoh.cob as beginningCob',
                                'finalBankDeposits.total as bankDepositsTotal',
                                '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                '(
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0) +
                                    COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                '((COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBankDeposits.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0) +
                                    COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal'
                            ])
                            ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                            ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                            ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsNonCash.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                            ->leftJoin(['finalFreebies' => '('.$incomeFreebiesNonCash.')'],'finalFreebies.id = accounting_branch_program.id')
                            ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsNonCash.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                            ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesNonCash.')'],'finalPettyExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesNonCash.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalOtherExpense' => '('.$otherExpensesNonCash.')'],'finalOtherExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalBankDeposits' => '('.$bankDepositsNonCash.')'],'finalBankDeposits.id = accounting_branch_program.id')
                            ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesNonCash.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                            ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersNonCash.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                            ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                            ->asArray()
                            ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                            ->all();

                $auditSummaryNonCashSeason = $access ? $access->branch_program_id != '' ? Season::find()
                            ->select([
                                'accounting_season.id as id',
                                'accounting_season.branch_program_id as branch_program_id',
                                'finalBeginningCoh.coh as beginningCoh',
                                'finalBeginningCoh.cob as beginningCob',
                                'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                'finalBankDeposits.total as bankDepositsTotal',
                                '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                '(
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBranchTransfers.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                '((COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBankDeposits.total, 0) + 
                                    COALESCE(finalBranchTransfers.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal'
                            ])
                            ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                            ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                            ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                            ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsNonCashSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                            ->leftJoin(['finalFreebies' => '('.$incomeFreebiesNonCashSeason.')'],'finalFreebies.id = accounting_season.id')
                            ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesNonCashSeason.')'],'finalPettyExpense.id = accounting_season.id')
                            ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesNonCashSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                            ->leftJoin(['finalOtherExpense' => '('.$otherExpensesNonCashSeason.')'],'finalOtherExpense.id = accounting_season.id')
                            ->leftJoin(['finalBankDeposits' => '('.$bankDepositsNonCashSeason.')'],'finalBankDeposits.id = accounting_season.id')
                            ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesNonCashSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                            ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsNonCashSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                            ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersNonCashSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                            ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                            ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                            ->asArray()
                            ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                            ->all() : Season::find()
                            ->select([
                                'accounting_season.id as id',
                                'accounting_season.branch_program_id as branch_program_id',
                                'finalBeginningCoh.coh as beginningCoh',
                                'finalBeginningCoh.cob as beginningCob',
                                'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                'finalBankDeposits.total as bankDepositsTotal',
                                '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                '(
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBranchTransfers.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                '((COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBankDeposits.total, 0) + 
                                    COALESCE(finalBranchTransfers.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal'
                            ])
                            ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                            ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                            ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                            ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsNonCashSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                            ->leftJoin(['finalFreebies' => '('.$incomeFreebiesNonCashSeason.')'],'finalFreebies.id = accounting_season.id')
                            ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesNonCashSeason.')'],'finalPettyExpense.id = accounting_season.id')
                            ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesNonCashSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                            ->leftJoin(['finalOtherExpense' => '('.$otherExpensesNonCashSeason.')'],'finalOtherExpense.id = accounting_season.id')
                            ->leftJoin(['finalBankDeposits' => '('.$bankDepositsNonCashSeason.')'],'finalBankDeposits.id = accounting_season.id')
                            ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesNonCashSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                            ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsNonCashSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                            ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersNonCashSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                            ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                            ->asArray()
                            ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                            ->all() : Season::find()
                            ->select([
                                'accounting_season.id as id',
                                'accounting_season.branch_program_id as branch_program_id',
                                'finalBeginningCoh.coh as beginningCoh',
                                'finalBeginningCoh.cob as beginningCob',
                                'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                'finalBankDeposits.total as bankDepositsTotal',
                                '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                '(
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBranchTransfers.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                '((COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBankDeposits.total, 0) + 
                                    COALESCE(finalBranchTransfers.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal'
                            ])
                            ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                            ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                            ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                            ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsNonCashSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                            ->leftJoin(['finalFreebies' => '('.$incomeFreebiesNonCashSeason.')'],'finalFreebies.id = accounting_season.id')
                            ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesNonCashSeason.')'],'finalPettyExpense.id = accounting_season.id')
                            ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesNonCashSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                            ->leftJoin(['finalOtherExpense' => '('.$otherExpensesNonCashSeason.')'],'finalOtherExpense.id = accounting_season.id')
                            ->leftJoin(['finalBankDeposits' => '('.$bankDepositsNonCashSeason.')'],'finalBankDeposits.id = accounting_season.id')
                            ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesNonCashSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                            ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsNonCashSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                            ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersNonCashSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                            ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                            ->asArray()
                            ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                            ->all();
            } 
        }else{
            if(!empty($date))
            {
                $incomeEnrolments = $this->getOverallIncomeEnrolmentByBranchProgramRawSql($date[0][0], $date[0][1]);

                $incomeEnrolmentsSeason = $this->getOverallIncomeEnrolmentBySeasonRawSql($date[0][0], $date[0][1]);

                $incomeFreebies = $this->getOverallIncomeFreebieByBranchProgramRawSql($date[0][0], $date[0][1]);

                $incomeFreebiesSeason = $this->getOverallIncomeFreebieBySeasonRawSql($date[0][0], $date[0][1]);

                $pettyExpenses = $this->getOverallPettyExpenseByBranchProgramRawSql($date[0][0], $date[0][1]);

                $pettyExpensesSeason = $this->getOverallPettyExpenseBySeasonRawSql($date[0][0], $date[0][1]);

                $photocopyExpenses = $this->getOverallPhotocopyExpenseByBranchProgramRawSql($date[0][0], $date[0][1]);

                $photocopyExpensesSeason = $this->getOverallPhotocopyExpenseBySeasonRawSql($date[0][0], $date[0][1]);

                $otherExpenses = $this->getOverallOtherExpenseByBranchProgramRawSql($date[0][0], $date[0][1]);

                $otherExpensesSeason = $this->getOverallOtherExpenseBySeasonRawSql($date[0][0], $date[0][1]);

                $bankDeposits = $this->getOverallBankDepositByBranchProgramRawSql($date[0][0], $date[0][1]);

                $bankDepositsSeason = $this->getOverallBankDepositBySeasonRawSql($date[0][0], $date[0][1]);

                $operatingExpenses = $this->getOverallOperatingExpenseByBranchProgramRawSql($date[0][0], $date[0][1]);

                $operatingExpensesSeason = $this->getOverallOperatingExpenseBySeasonRawSql($date[0][0], $date[0][1]);

                $budgetProposals = $this->getOverallBudgetProposalByBranchProgramRawSql($date[0][0], $date[0][1]);

                $budgetProposalsSeason = $this->getOverallBudgetProposalBySeasonRawSql($date[0][0], $date[0][1]);

                $branchTransfers = $this->getOverallBranchTransferByBranchProgramRawSql($date[0][0], $date[0][1]);

                $branchTransfersSeason = $this->getOverallBranchTransferBySeasonRawSql($date[0][0], $date[0][1]);

                $beginningCoh = $this->getBeginningCashByBranchProgram($date[0][0], $date[0][1]);

                $beginningCohSeason = $this->getBeginningCashBySeason($date[0][0], $date[0][1]);

                $auditTargetSeason = Season::find()
                                ->select([
                                    'accounting_season.id as id',
                                    'accounting_season.branch_program_id as branch_program_id',
                                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                    'COALESCE(targetIncome.total, 0) as totalGross',
                                    'COALESCE((targetIncome.total - ((targetIncome.total/1.12)*0.12)), 0) as totalGrossIncome',
                                    'COALESCE(targetProgram.total + 
                                              targetVenueRental.total + 
                                              targetFreebie.total +
                                              targetReview.total +
                                              targetFood.total +
                                              targetTransportation.total +
                                              targetStaffSalary.total + 
                                              targetRebate.total +
                                              targetUtility.total +
                                              targetAcademic.total +
                                              targetEmergencyFund.total
                                              , 0) as totalExpenses',
                                    'COALESCE((targetIncome.total - ((targetIncome.total/1.12)*0.12)) - (targetProgram.total + 
                                              targetVenueRental.total + 
                                              targetFreebie.total +
                                              targetReview.total +
                                              targetFood.total +
                                              targetTransportation.total +
                                              targetStaffSalary.total + 
                                              targetRebate.total +
                                              targetUtility.total +
                                              targetAcademic.total +
                                              targetEmergencyFund.total), 0) as expectedIncome',
                                    'COALESCE(((targetIncome.total - ((targetIncome.total/1.12)*0.12)) - (targetProgram.total + 
                                              targetVenueRental.total + 
                                              targetFreebie.total +
                                              targetReview.total +
                                              targetFood.total +
                                              targetTransportation.total +
                                              targetStaffSalary.total + 
                                              targetRebate.total +
                                              targetUtility.total +
                                              targetAcademic.total +
                                              targetEmergencyFund.total) - ((targetIncome.total - ((targetIncome.total/1.12)*0.12)) - (targetProgram.total + 
                                              targetVenueRental.total + 
                                              targetFreebie.total +
                                              targetReview.total +
                                              targetFood.total +
                                              targetTransportation.total +
                                              targetStaffSalary.total + 
                                              targetRebate.total +
                                              targetUtility.total +
                                              targetAcademic.total +
                                              targetEmergencyFund.total)) * accounting_target_royalty_fee.percentage), 0) as netIncome'
                                ])

                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin('accounting_target_royalty_fee','accounting_target_royalty_fee.season_id = accounting_season.id')
                                ->leftJoin(['targetAcademic' => '('.$targetAcademicSeason.')'],'targetAcademic.id = accounting_season.id')
                                ->leftJoin(['targetEmergencyFund' => '('.$targetEmergencyFundSeason.')'],'targetEmergencyFund.id = accounting_season.id')
                                ->leftJoin(['targetFood' => '('.$targetFoodSeason.')'],'targetFood.id = accounting_season.id')
                                ->leftJoin(['targetFreebie' => '('.$targetFreebieSeason.')'],'targetFreebie.id = accounting_season.id')
                                ->leftJoin(['targetIncome' => '('.$targetIncomeSeason.')'],'targetIncome.id = accounting_season.id')
                                ->leftJoin(['targetProgram' => '('.$targetProgramSeason.')'],'targetProgram.id = accounting_season.id')
                                ->leftJoin(['targetRebate' => '('.$targetRebateSeason.')'],'targetRebate.id = accounting_season.id')
                                ->leftJoin(['targetReview' => '('.$targetReviewSeason.')'],'targetReview.id = accounting_season.id')
                                ->leftJoin(['targetStaffSalary' => '('.$targetStaffSalarySeason.')'],'targetStaffSalary.id = accounting_season.id')
                                ->leftJoin(['targetTransportation' => '('.$targetTransportationSeason.')'],'targetTransportation.id = accounting_season.id')
                                ->leftJoin(['targetUtility' => '('.$targetUtilitySeason.')'],'targetUtility.id = accounting_season.id')
                                ->leftJoin(['targetVenueRental' => '('.$targetVenueRentalSeason.')'],'targetVenueRental.id = accounting_season.id')
                                ->createCommand()
                                ->getRawSql();

                $auditTargetBranch = BranchProgram::find()
                                    ->select([
                                        'accounting_branch_program.id as id',
                                        'sum(COALESCE(targetSeason.totalGross, 0)) as totalGross',
                                        'sum(COALESCE(targetSeason.totalGrossIncome, 0)) as totalGrossIncome',
                                        'sum(COALESCE(targetSeason.totalExpenses, 0)) as totalExpenses',
                                        'sum(COALESCE(targetSeason.expectedIncome, 0)) as expectedIncome',
                                        'sum(COALESCE(targetSeason.netIncome, 0)) as netIncome',
                                    ])
                                    ->leftJoin(['targetSeason' => '('.$auditTargetSeason.')'],'targetSeason.branch_program_id = accounting_branch_program.id')
                                    ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                    ->groupBy(['targetSeason.branch_program_id'])
                                    ->createCommand()                              
                                    ->getRawSql();

                $incomeEnrolmentsCash = $this->getIncomeEnrolmentByBranchProgramRawSql($date[0][0], $date[0][1], 'Cash');

                $incomeEnrolmentsCashSeason = $this->getIncomeEnrolmentBySeasonRawSql($date[0][0], $date[0][1], 'Cash');

                $incomeFreebiesCash = $this->getIncomeFreebieByBranchProgramRawSql($date[0][0], $date[0][1], 'Cash');

                $incomeFreebiesCashSeason = $this->getIncomeFreebieBySeasonRawSql($date[0][0], $date[0][1], 'Cash');

                $pettyExpensesCash = $this->getPettyExpenseByBranchProgramRawSql($date[0][0], $date[0][1], 'Cash');

                $pettyExpensesCashSeason = $this->getPettyExpenseBySeasonRawSql($date[0][0], $date[0][1], 'Cash');

                $photocopyExpensesCash = $this->getPhotocopyExpenseByBranchProgramRawSql($date[0][0], $date[0][1], 'Cash');

                $photocopyExpensesCashSeason = $this->getPhotocopyExpenseBySeasonRawSql($date[0][0], $date[0][1], 'Cash');

                $otherExpensesCash = $this->getOtherExpenseByBranchProgramRawSql($date[0][0], $date[0][1], 'Cash');

                $otherExpensesCashSeason = $this->getOtherExpenseBySeasonRawSql($date[0][0], $date[0][1], 'Cash');

                $bankDepositsCash = $this->getBankDepositByBranchProgramRawSql($date[0][0], $date[0][1], 'Cash');

                $bankDepositsCashSeason = $this->getBankDepositBySeasonRawSql($date[0][0], $date[0][1], 'Cash');

                $operatingExpensesCash = $this->getOperatingExpenseByBranchProgramRawSql($date[0][0], $date[0][1], 'Cash');

                $operatingExpensesCashSeason = $this->getOperatingExpenseBySeasonRawSql($date[0][0], $date[0][1], 'Cash');

                $budgetProposalsCash = $this->getBudgetProposalByBranchProgramRawSql($date[0][0], $date[0][1], 'Cash');

                $budgetProposalsCashSeason = $this->getBudgetProposalBySeasonRawSql($date[0][0], $date[0][1], 'Cash');

                $branchTransfersCash = $this->getBranchTransferByBranchProgramRawSql($date[0][0], $date[0][1], 'Cash');

                $branchTransfersCashSeason = $this->getBranchTransferBySeasonRawSql($date[0][0], $date[0][1], 'Cash');

                $auditSummary = $access ? $access->branch_program_id != '' ? BranchProgram::find()
                                ->select([
                                    'accounting_branch_program.id as id',
                                    'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal',
                                    'targetBranch.totalGross as targetGross',
                                    'targetBranch.totalGrossIncome as targetGrossIncome',
                                    'targetBranch.totalExpenses as targetExpenses',
                                    'targetBranch.expectedIncome as targetExpectedIncome',
                                    'targetBranch.netIncome as targetNetIncome',
                                ])
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolments.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebies.')'],'finalFreebies.id = accounting_branch_program.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposals.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpenses.')'],'finalPettyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpenses.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpenses.')'],'finalOtherExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDeposits.')'],'finalBankDeposits.id = accounting_branch_program.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpenses.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfers.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                                ->leftJoin(['targetBranch' => '('.$auditTargetBranch.')'],'targetBranch.id = accounting_branch_program.id')
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : BranchProgram::find()
                                ->select([
                                    'accounting_branch_program.id as id',
                                    'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal',
                                    'targetBranch.totalGross as targetGross',
                                    'targetBranch.totalGrossIncome as targetGrossIncome',
                                    'targetBranch.totalExpenses as targetExpenses',
                                    'targetBranch.expectedIncome as targetExpectedIncome',
                                    'targetBranch.netIncome as targetNetIncome',
                                ])
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolments.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebies.')'],'finalFreebies.id = accounting_branch_program.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposals.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpenses.')'],'finalPettyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpenses.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpenses.')'],'finalOtherExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDeposits.')'],'finalBankDeposits.id = accounting_branch_program.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpenses.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfers.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                                ->leftJoin(['targetBranch' => '('.$auditTargetBranch.')'],'targetBranch.id = accounting_branch_program.id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : BranchProgram::find()
                                ->select([
                                    'accounting_branch_program.id as id',
                                    'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal',
                                    'targetBranch.totalGross as targetGross',
                                    'targetBranch.totalGrossIncome as targetGrossIncome',
                                    'targetBranch.totalExpenses as targetExpenses',
                                    'targetBranch.expectedIncome as targetExpectedIncome',
                                    'targetBranch.netIncome as targetNetIncome',
                                ])
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolments.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebies.')'],'finalFreebies.id = accounting_branch_program.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposals.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpenses.')'],'finalPettyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpenses.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpenses.')'],'finalOtherExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDeposits.')'],'finalBankDeposits.id = accounting_branch_program.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpenses.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfers.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                                ->leftJoin(['targetBranch' => '('.$auditTargetBranch.')'],'targetBranch.id = accounting_branch_program.id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all();

                $auditSummarySeason = $access ? $access->branch_program_id != '' ? Season::find()
                                ->select([
                                    'accounting_season.id as id',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'accounting_season.branch_program_id as branch_program_id',
                                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                    '(
                                        (
                                        COALESCE(finalBeginningCoh.coh, 0) + 
                                        COALESCE(finalBeginningCoh.cob, 0) + 
                                        COALESCE(finalIncomeEnrolments.total, 0) + 
                                        COALESCE(finalFreebies.total, 0) + 
                                        COALESCE(finalBudgetProposals.total, 0)
                                        ) - 
                                        (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0)
                                        )
                                    ) as netIncomeTotal',
                                    'COALESCE(targets.totalGross, 0) as targetGross',
                                    'COALESCE(targets.totalGrossIncome, 0) as targetGrossIncome',
                                    'COALESCE(targets.totalExpenses, 0) as targetExpenses',
                                    'COALESCE(targets.expectedIncome, 0) as targetExpectedIncome',
                                    'COALESCE(targets.netIncome, 0) as targetNetIncome',
                                ])
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesSeason.')'],'finalFreebies.id = accounting_season.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesSeason.')'],'finalPettyExpense.id = accounting_season.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesSeason.')'],'finalOtherExpense.id = accounting_season.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsSeason.')'],'finalBankDeposits.id = accounting_season.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                                ->leftJoin(['targets' => '('.$auditTargetSeason.')'],'targets.id = accounting_season.id')
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : Season::find()
                                ->select([
                                    'accounting_season.id as id',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'accounting_season.branch_program_id as branch_program_id',
                                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                    '(
                                        (
                                        COALESCE(finalBeginningCoh.coh, 0) + 
                                        COALESCE(finalBeginningCoh.cob, 0) + 
                                        COALESCE(finalIncomeEnrolments.total, 0) + 
                                        COALESCE(finalFreebies.total, 0) + 
                                        COALESCE(finalBudgetProposals.total, 0)
                                        ) - 
                                        (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0)
                                        )
                                    ) as netIncomeTotal',
                                    'COALESCE(targets.totalGross, 0) as targetGross',
                                    'COALESCE(targets.totalGrossIncome, 0) as targetGrossIncome',
                                    'COALESCE(targets.totalExpenses, 0) as targetExpenses',
                                    'COALESCE(targets.expectedIncome, 0) as targetExpectedIncome',
                                    'COALESCE(targets.netIncome, 0) as targetNetIncome',
                                ])
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesSeason.')'],'finalFreebies.id = accounting_season.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesSeason.')'],'finalPettyExpense.id = accounting_season.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesSeason.')'],'finalOtherExpense.id = accounting_season.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsSeason.')'],'finalBankDeposits.id = accounting_season.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                                ->leftJoin(['targets' => '('.$auditTargetSeason.')'],'targets.id = accounting_season.id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : Season::find()
                                ->select([
                                    'accounting_season.id as id',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'accounting_season.branch_program_id as branch_program_id',
                                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                    '(
                                        (
                                        COALESCE(finalBeginningCoh.coh, 0) + 
                                        COALESCE(finalBeginningCoh.cob, 0) + 
                                        COALESCE(finalIncomeEnrolments.total, 0) + 
                                        COALESCE(finalFreebies.total, 0) + 
                                        COALESCE(finalBudgetProposals.total, 0)
                                        ) - 
                                        (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0)
                                        )
                                    ) as netIncomeTotal',
                                    'COALESCE(targets.totalGross, 0) as targetGross',
                                    'COALESCE(targets.totalGrossIncome, 0) as targetGrossIncome',
                                    'COALESCE(targets.totalExpenses, 0) as targetExpenses',
                                    'COALESCE(targets.expectedIncome, 0) as targetExpectedIncome',
                                    'COALESCE(targets.netIncome, 0) as targetNetIncome',
                                ])
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesSeason.')'],'finalFreebies.id = accounting_season.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesSeason.')'],'finalPettyExpense.id = accounting_season.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesSeason.')'],'finalOtherExpense.id = accounting_season.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsSeason.')'],'finalBankDeposits.id = accounting_season.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                                ->leftJoin(['targets' => '('.$auditTargetSeason.')'],'targets.id = accounting_season.id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all();

                $auditSummaryCash = $access ? $access->branch_program_id != '' ? BranchProgram::find()
                                ->select([
                                    'accounting_branch_program.id as id',
                                    'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                    '(  
                                        (
                                        COALESCE(finalBeginningCoh.coh, 0) + 
                                        COALESCE(finalIncomeEnrolments.total, 0) + 
                                        COALESCE(finalFreebies.total, 0) + 
                                        COALESCE(finalBudgetProposals.total, 0)
                                        ) - 
                                        (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0)
                                        )
                                    ) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsCash.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesCash.')'],'finalFreebies.id = accounting_branch_program.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsCash.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesCash.')'],'finalPettyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesCash.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesCash.')'],'finalOtherExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsCash.')'],'finalBankDeposits.id = accounting_branch_program.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesCash.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersCash.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : BranchProgram::find()
                                ->select([
                                    'accounting_branch_program.id as id',
                                    'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                    '(
                                        (
                                        COALESCE(finalBeginningCoh.coh, 0) + 
                                        COALESCE(finalIncomeEnrolments.total, 0) + 
                                        COALESCE(finalFreebies.total, 0) + 
                                        COALESCE(finalBudgetProposals.total, 0)
                                        ) - 
                                        (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0)
                                        )
                                    ) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsCash.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesCash.')'],'finalFreebies.id = accounting_branch_program.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsCash.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesCash.')'],'finalPettyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesCash.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesCash.')'],'finalOtherExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsCash.')'],'finalBankDeposits.id = accounting_branch_program.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesCash.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersCash.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : BranchProgram::find()
                                ->select([
                                    'accounting_branch_program.id as id',
                                    'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                    '(
                                        (
                                        COALESCE(finalBeginningCoh.coh, 0) + 
                                        COALESCE(finalIncomeEnrolments.total, 0) + 
                                        COALESCE(finalFreebies.total, 0) + 
                                        COALESCE(finalBudgetProposals.total, 0)
                                        ) - 
                                        (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0)
                                        )
                                    ) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsCash.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesCash.')'],'finalFreebies.id = accounting_branch_program.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsCash.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesCash.')'],'finalPettyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesCash.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesCash.')'],'finalOtherExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsCash.')'],'finalBankDeposits.id = accounting_branch_program.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesCash.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersCash.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all();

                $auditSummaryCashSeason = $access ? $access->branch_program_id != '' ? Season::find()
                                ->select([
                                    'accounting_season.id as id',
                                    'accounting_season.branch_program_id as branch_program_id',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                    '(
                                        (
                                        COALESCE(finalBeginningCoh.coh, 0) +  
                                        COALESCE(finalIncomeEnrolments.total, 0) + 
                                        COALESCE(finalFreebies.total, 0) + 
                                        COALESCE(finalBudgetProposals.total, 0)
                                        ) - 
                                        (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0)
                                        )
                                    ) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsCashSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesCashSeason.')'],'finalFreebies.id = accounting_season.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesCashSeason.')'],'finalPettyExpense.id = accounting_season.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesCashSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesCashSeason.')'],'finalOtherExpense.id = accounting_season.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsCashSeason.')'],'finalBankDeposits.id = accounting_season.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesCashSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsCashSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersCashSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : Season::find()
                                ->select([
                                    'accounting_season.id as id',
                                    'accounting_season.branch_program_id as branch_program_id',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                    '(((COALESCE(finalBeginningCoh.coh, 0) +  COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsCashSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesCashSeason.')'],'finalFreebies.id = accounting_season.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesCashSeason.')'],'finalPettyExpense.id = accounting_season.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesCashSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesCashSeason.')'],'finalOtherExpense.id = accounting_season.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsCashSeason.')'],'finalBankDeposits.id = accounting_season.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesCashSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsCashSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersCashSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : Season::find()
                                ->select([
                                    'accounting_season.id as id',
                                    'accounting_season.branch_program_id as branch_program_id',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                    '(((COALESCE(finalBeginningCoh.coh, 0) +  COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsCashSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesCashSeason.')'],'finalFreebies.id = accounting_season.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesCashSeason.')'],'finalPettyExpense.id = accounting_season.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesCashSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesCashSeason.')'],'finalOtherExpense.id = accounting_season.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsCashSeason.')'],'finalBankDeposits.id = accounting_season.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesCashSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsCashSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersCashSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all();

                $incomeEnrolmentsNonCash = $this->getIncomeEnrolmentByBranchProgramRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $incomeEnrolmentsNonCashSeason = $this->getIncomeEnrolmentBySeasonRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $incomeFreebiesNonCash = $this->getIncomeFreebieByBranchProgramRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $incomeFreebiesNonCashSeason = $this->getIncomeFreebieBySeasonRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $pettyExpensesNonCash = $this->getPettyExpenseByBranchProgramRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $pettyExpensesNonCashSeason = $this->getPettyExpenseBySeasonRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $photocopyExpensesNonCash = $this->getPhotocopyExpenseByBranchProgramRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $photocopyExpensesNonCashSeason = $this->getPhotocopyExpenseBySeasonRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $otherExpensesNonCash = $this->getOtherExpenseByBranchProgramRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $otherExpensesNonCashSeason = $this->getOtherExpenseBySeasonRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $bankDepositsNonCash = $this->getBankDepositByBranchProgramRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $bankDepositsNonCashSeason = $this->getBankDepositBySeasonRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $operatingExpensesNonCash = $this->getOperatingExpenseByBranchProgramRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $operatingExpensesNonCashSeason = $this->getOperatingExpenseBySeasonRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $budgetProposalsNonCash = $this->getBudgetProposalByBranchProgramRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $budgetProposalsNonCashSeason = $this->getBudgetProposalByBranchProgramRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $branchTransfersNonCash = $this->getBranchTransferByBranchProgramRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $branchTransfersNonCashSeason = $this->getBranchTransferByBranchProgramRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $auditSummaryNonCash = $access ? $access->branch_program_id != '' ? BranchProgram::find()
                                ->select([
                                    'accounting_branch_program.id as id',
                                    'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsNonCash.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesNonCash.')'],'finalFreebies.id = accounting_branch_program.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsNonCash.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesNonCash.')'],'finalPettyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesNonCash.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesNonCash.')'],'finalOtherExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsNonCash.')'],'finalBankDeposits.id = accounting_branch_program.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesNonCash.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersNonCash.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : BranchProgram::find()
                                ->select([
                                    'accounting_branch_program.id as id',
                                    'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsNonCash.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesNonCash.')'],'finalFreebies.id = accounting_branch_program.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsNonCash.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesNonCash.')'],'finalPettyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesNonCash.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesNonCash.')'],'finalOtherExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsNonCash.')'],'finalBankDeposits.id = accounting_branch_program.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesNonCash.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersNonCash.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : BranchProgram::find()
                                ->select([
                                    'accounting_branch_program.id as id',
                                    'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsNonCash.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesNonCash.')'],'finalFreebies.id = accounting_branch_program.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsNonCash.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesNonCash.')'],'finalPettyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesNonCash.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesNonCash.')'],'finalOtherExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsNonCash.')'],'finalBankDeposits.id = accounting_branch_program.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesNonCash.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersNonCash.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all();

                $auditSummaryNonCashSeason = $access ? $access->branch_program_id != '' ? Season::find()
                                ->select([
                                    'accounting_season.id as id',
                                    'accounting_season.branch_program_id as branch_program_id',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsNonCashSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesNonCashSeason.')'],'finalFreebies.id = accounting_season.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesNonCashSeason.')'],'finalPettyExpense.id = accounting_season.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesNonCashSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesNonCashSeason.')'],'finalOtherExpense.id = accounting_season.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsNonCashSeason.')'],'finalBankDeposits.id = accounting_season.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesNonCashSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsNonCashSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersNonCashSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : Season::find()
                                ->select([
                                    'accounting_season.id as id',
                                    'accounting_season.branch_program_id as branch_program_id',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsNonCashSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesNonCashSeason.')'],'finalFreebies.id = accounting_season.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesNonCashSeason.')'],'finalPettyExpense.id = accounting_season.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesNonCashSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesNonCashSeason.')'],'finalOtherExpense.id = accounting_season.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsNonCashSeason.')'],'finalBankDeposits.id = accounting_season.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesNonCashSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsNonCashSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersNonCashSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : Season::find()
                                ->select([
                                    'accounting_season.id as id',
                                    'accounting_season.branch_program_id as branch_program_id',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsNonCashSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesNonCashSeason.')'],'finalFreebies.id = accounting_season.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesNonCashSeason.')'],'finalPettyExpense.id = accounting_season.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesNonCashSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesNonCashSeason.')'],'finalOtherExpense.id = accounting_season.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsNonCashSeason.')'],'finalBankDeposits.id = accounting_season.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesNonCashSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsNonCashSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersNonCashSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all();
            }else{
                $incomeEnrolments = $this->getOverallIncomeEnrolmentByBranchProgramRawSql();

                $incomeEnrolmentsSeason = $this->getOverallIncomeEnrolmentBySeasonRawSql();

                $incomeFreebies = $this->getOverallIncomeFreebieByBranchProgramRawSql();

                $incomeFreebiesSeason = $this->getOverallIncomeFreebieBySeasonRawSql();

                $pettyExpenses = $this->getOverallPettyExpenseByBranchProgramRawSql();

                $pettyExpensesSeason = $this->getOverallPettyExpenseBySeasonRawSql();

                $photocopyExpenses = $this->getOverallPhotocopyExpenseByBranchProgramRawSql();

                $photocopyExpensesSeason = $this->getOverallPhotocopyExpenseBySeasonRawSql();

                $otherExpenses = $this->getOverallOtherExpenseByBranchProgramRawSql();

                $otherExpensesSeason = $this->getOverallOtherExpenseBySeasonRawSql();

                $bankDeposits = $this->getOverallBankDepositByBranchProgramRawSql();

                $bankDepositsSeason = $this->getOverallBankDepositBySeasonRawSql();

                $operatingExpenses = $this->getOverallOperatingExpenseByBranchProgramRawSql();

                $operatingExpensesSeason = $this->getOverallOperatingExpenseBySeasonRawSql();

                $budgetProposals = $this->getOverallBudgetProposalByBranchProgramRawSql();

                $budgetProposalsSeason = $this->getOverallBudgetProposalBySeasonRawSql();

                $branchTransfers = $this->getOverallBranchTransferByBranchProgramRawSql();

                $branchTransfersSeason = $this->getOverallBranchTransferBySeasonRawSql();

                $beginningCoh = $this->getBeginningCashByBranchProgram();

                $beginningCohSeason = $this->getBeginningCashBySeason();

                $auditTargetSeason = Season::find()
                                ->select([
                                    'accounting_season.id as id',
                                    'accounting_season.branch_program_id as branch_program_id',
                                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                    'COALESCE(targetIncome.total, 0) as totalGross',
                                    'COALESCE((targetIncome.total - ((targetIncome.total/1.12)*0.12)), 0) as totalGrossIncome',
                                    'COALESCE(targetProgram.total + 
                                              targetVenueRental.total + 
                                              targetFreebie.total +
                                              targetReview.total +
                                              targetFood.total +
                                              targetTransportation.total +
                                              targetStaffSalary.total + 
                                              targetRebate.total +
                                              targetUtility.total +
                                              targetAcademic.total +
                                              targetEmergencyFund.total
                                              , 0) as totalExpenses',
                                    'COALESCE((targetIncome.total - ((targetIncome.total/1.12)*0.12)) - (targetProgram.total + 
                                              targetVenueRental.total + 
                                              targetFreebie.total +
                                              targetReview.total +
                                              targetFood.total +
                                              targetTransportation.total +
                                              targetStaffSalary.total + 
                                              targetRebate.total +
                                              targetUtility.total +
                                              targetAcademic.total +
                                              targetEmergencyFund.total), 0) as expectedIncome',
                                    'COALESCE(((targetIncome.total - ((targetIncome.total/1.12)*0.12)) - (targetProgram.total + 
                                              targetVenueRental.total + 
                                              targetFreebie.total +
                                              targetReview.total +
                                              targetFood.total +
                                              targetTransportation.total +
                                              targetStaffSalary.total + 
                                              targetRebate.total +
                                              targetUtility.total +
                                              targetAcademic.total +
                                              targetEmergencyFund.total) - ((targetIncome.total - ((targetIncome.total/1.12)*0.12)) - (targetProgram.total + 
                                              targetVenueRental.total + 
                                              targetFreebie.total +
                                              targetReview.total +
                                              targetFood.total +
                                              targetTransportation.total +
                                              targetStaffSalary.total + 
                                              targetRebate.total +
                                              targetUtility.total +
                                              targetAcademic.total +
                                              targetEmergencyFund.total)) * accounting_target_royalty_fee.percentage), 0) as netIncome'
                                ])

                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin('accounting_target_royalty_fee','accounting_target_royalty_fee.season_id = accounting_season.id')
                                ->leftJoin(['targetAcademic' => '('.$targetAcademicSeason.')'],'targetAcademic.id = accounting_season.id')
                                ->leftJoin(['targetEmergencyFund' => '('.$targetEmergencyFundSeason.')'],'targetEmergencyFund.id = accounting_season.id')
                                ->leftJoin(['targetFood' => '('.$targetFoodSeason.')'],'targetFood.id = accounting_season.id')
                                ->leftJoin(['targetFreebie' => '('.$targetFreebieSeason.')'],'targetFreebie.id = accounting_season.id')
                                ->leftJoin(['targetIncome' => '('.$targetIncomeSeason.')'],'targetIncome.id = accounting_season.id')
                                ->leftJoin(['targetProgram' => '('.$targetProgramSeason.')'],'targetProgram.id = accounting_season.id')
                                ->leftJoin(['targetRebate' => '('.$targetRebateSeason.')'],'targetRebate.id = accounting_season.id')
                                ->leftJoin(['targetReview' => '('.$targetReviewSeason.')'],'targetReview.id = accounting_season.id')
                                ->leftJoin(['targetStaffSalary' => '('.$targetStaffSalarySeason.')'],'targetStaffSalary.id = accounting_season.id')
                                ->leftJoin(['targetTransportation' => '('.$targetTransportationSeason.')'],'targetTransportation.id = accounting_season.id')
                                ->leftJoin(['targetUtility' => '('.$targetUtilitySeason.')'],'targetUtility.id = accounting_season.id')
                                ->leftJoin(['targetVenueRental' => '('.$targetVenueRentalSeason.')'],'targetVenueRental.id = accounting_season.id')
                                ->createCommand()
                                ->getRawSql();

                $auditTargetBranch = BranchProgram::find()
                                    ->select([
                                        'accounting_branch_program.id as id',
                                        'sum(COALESCE(targetSeason.totalGross, 0)) as totalGross',
                                        'sum(COALESCE(targetSeason.totalGrossIncome, 0)) as totalGrossIncome',
                                        'sum(COALESCE(targetSeason.totalExpenses, 0)) as totalExpenses',
                                        'sum(COALESCE(targetSeason.expectedIncome, 0)) as expectedIncome',
                                        'sum(COALESCE(targetSeason.netIncome, 0)) as netIncome',
                                    ])
                                    ->leftJoin(['targetSeason' => '('.$auditTargetSeason.')'],'targetSeason.branch_program_id = accounting_branch_program.id')
                                    ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                    ->groupBy(['targetSeason.branch_program_id'])
                                    ->createCommand()                              
                                    ->getRawSql();

                $incomeEnrolmentsCash = $this->getIncomeEnrolmentByBranchProgramRawSql(null, null, 'Cash');

                $incomeEnrolmentsCashSeason = $this->getIncomeEnrolmentBySeasonRawSql(null, null, 'Cash');

                $incomeFreebiesCash = $this->getIncomeFreebieByBranchProgramRawSql(null, null, 'Cash');

                $incomeFreebiesCashSeason = $this->getIncomeFreebieBySeasonRawSql(null, null, 'Cash');

                $pettyExpensesCash = $this->getPettyExpenseByBranchProgramRawSql(null, null, 'Cash');

                $pettyExpensesCashSeason = $this->getPettyExpenseBySeasonRawSql(null, null, 'Cash');

                $photocopyExpensesCash = $this->getPhotocopyExpenseByBranchProgramRawSql(null, null, 'Cash');

                $photocopyExpensesCashSeason = $this->getPhotocopyExpenseBySeasonRawSql(null, null, 'Cash');

                $otherExpensesCash = $this->getOtherExpenseByBranchProgramRawSql(null, null, 'Cash');

                $otherExpensesCashSeason = $this->getOtherExpenseBySeasonRawSql(null, null, 'Cash');

                $bankDepositsCash = $this->getBankDepositByBranchProgramRawSql(null, null, 'Cash');

                $bankDepositsCashSeason = $this->getBankDepositBySeasonRawSql(null, null, 'Cash');

                $operatingExpensesCash = $this->getOperatingExpenseByBranchProgramRawSql(null, null, 'Cash');

                $operatingExpensesCashSeason = $this->getOperatingExpenseBySeasonRawSql(null, null, 'Cash');

                $budgetProposalsCash = $this->getBudgetProposalByBranchProgramRawSql(null, null, 'Cash');

                $budgetProposalsCashSeason = $this->getBudgetProposalBySeasonRawSql(null, null, 'Cash');

                $branchTransfersCash = $this->getBranchTransferByBranchProgramRawSql(null, null, 'Cash');

                $branchTransfersCashSeason = $this->getBranchTransferBySeasonRawSql(null, null, 'Cash');

                $auditSummary = $access ? $access->branch_program_id != '' ? BranchProgram::find()
                                ->select([
                                    'accounting_branch_program.id as id',
                                    'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal',
                                    'targetBranch.totalGross as targetGross',
                                    'targetBranch.totalGrossIncome as targetGrossIncome',
                                    'targetBranch.totalExpenses as targetExpenses',
                                    'targetBranch.expectedIncome as targetExpectedIncome',
                                    'targetBranch.netIncome as targetNetIncome',
                                ])
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolments.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebies.')'],'finalFreebies.id = accounting_branch_program.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposals.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpenses.')'],'finalPettyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpenses.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpenses.')'],'finalOtherExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDeposits.')'],'finalBankDeposits.id = accounting_branch_program.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpenses.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfers.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                                ->leftJoin(['targetBranch' => '('.$auditTargetBranch.')'],'targetBranch.id = accounting_branch_program.id')
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : BranchProgram::find()
                                ->select([
                                    'accounting_branch_program.id as id',
                                    'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal',
                                    'targetBranch.totalGross as targetGross',
                                    'targetBranch.totalGrossIncome as targetGrossIncome',
                                    'targetBranch.totalExpenses as targetExpenses',
                                    'targetBranch.expectedIncome as targetExpectedIncome',
                                    'targetBranch.netIncome as targetNetIncome',
                                ])
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolments.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebies.')'],'finalFreebies.id = accounting_branch_program.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposals.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpenses.')'],'finalPettyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpenses.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpenses.')'],'finalOtherExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDeposits.')'],'finalBankDeposits.id = accounting_branch_program.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpenses.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfers.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                                ->leftJoin(['targetBranch' => '('.$auditTargetBranch.')'],'targetBranch.id = accounting_branch_program.id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : BranchProgram::find()
                                ->select([
                                    'accounting_branch_program.id as id',
                                    'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal',
                                    'targetBranch.totalGross as targetGross',
                                    'targetBranch.totalGrossIncome as targetGrossIncome',
                                    'targetBranch.totalExpenses as targetExpenses',
                                    'targetBranch.expectedIncome as targetExpectedIncome',
                                    'targetBranch.netIncome as targetNetIncome',
                                ])
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolments.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebies.')'],'finalFreebies.id = accounting_branch_program.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposals.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpenses.')'],'finalPettyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpenses.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpenses.')'],'finalOtherExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDeposits.')'],'finalBankDeposits.id = accounting_branch_program.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpenses.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfers.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                                ->leftJoin(['targetBranch' => '('.$auditTargetBranch.')'],'targetBranch.id = accounting_branch_program.id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all();

                $auditSummarySeason = $access ? $access->branch_program_id != '' ? Season::find()
                                ->select([
                                    'accounting_season.id as id',
                                    'accounting_season.branch_program_id as branch_program_id',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal',
                                    'COALESCE(targets.totalGross, 0) as targetGross',
                                    'COALESCE(targets.totalGrossIncome, 0) as targetGrossIncome',
                                    'COALESCE(targets.totalExpenses, 0) as targetExpenses',
                                    'COALESCE(targets.expectedIncome, 0) as targetExpectedIncome',
                                    'COALESCE(targets.netIncome, 0) as targetNetIncome',
                                ])
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesSeason.')'],'finalFreebies.id = accounting_season.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesSeason.')'],'finalPettyExpense.id = accounting_season.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesSeason.')'],'finalOtherExpense.id = accounting_season.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsSeason.')'],'finalBankDeposits.id = accounting_season.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                                ->leftJoin(['targets' => '('.$auditTargetSeason.')'],'targets.id = accounting_season.id')
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : Season::find()
                                ->select([
                                    'accounting_season.id as id',
                                    'accounting_season.branch_program_id as branch_program_id',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal',
                                    'COALESCE(targets.totalGross, 0) as targetGross',
                                    'COALESCE(targets.totalGrossIncome, 0) as targetGrossIncome',
                                    'COALESCE(targets.totalExpenses, 0) as targetExpenses',
                                    'COALESCE(targets.expectedIncome, 0) as targetExpectedIncome',
                                    'COALESCE(targets.netIncome, 0) as targetNetIncome',
                                ])
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesSeason.')'],'finalFreebies.id = accounting_season.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesSeason.')'],'finalPettyExpense.id = accounting_season.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesSeason.')'],'finalOtherExpense.id = accounting_season.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsSeason.')'],'finalBankDeposits.id = accounting_season.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                                ->leftJoin(['targets' => '('.$auditTargetSeason.')'],'targets.id = accounting_season.id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : Season::find()
                                ->select([
                                    'accounting_season.id as id',
                                    'accounting_season.branch_program_id as branch_program_id',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal',
                                    'COALESCE(targets.totalGross, 0) as targetGross',
                                    'COALESCE(targets.totalGrossIncome, 0) as targetGrossIncome',
                                    'COALESCE(targets.totalExpenses, 0) as targetExpenses',
                                    'COALESCE(targets.expectedIncome, 0) as targetExpectedIncome',
                                    'COALESCE(targets.netIncome, 0) as targetNetIncome',
                                ])
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesSeason.')'],'finalFreebies.id = accounting_season.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesSeason.')'],'finalPettyExpense.id = accounting_season.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesSeason.')'],'finalOtherExpense.id = accounting_season.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsSeason.')'],'finalBankDeposits.id = accounting_season.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                                ->leftJoin(['targets' => '('.$auditTargetSeason.')'],'targets.id = accounting_season.id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all();

                $auditSummaryCash = $access ? $access->branch_program_id != '' ? BranchProgram::find()
                                ->select([
                                    'accounting_branch_program.id as id',
                                    'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsCash.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesCash.')'],'finalFreebies.id = accounting_branch_program.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsCash.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesCash.')'],'finalPettyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesCash.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesCash.')'],'finalOtherExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsCash.')'],'finalBankDeposits.id = accounting_branch_program.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesCash.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersCash.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : BranchProgram::find()
                                ->select([
                                    'accounting_branch_program.id as id',
                                    'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsCash.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesCash.')'],'finalFreebies.id = accounting_branch_program.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsCash.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesCash.')'],'finalPettyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesCash.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesCash.')'],'finalOtherExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsCash.')'],'finalBankDeposits.id = accounting_branch_program.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesCash.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersCash.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : BranchProgram::find()
                                ->select([
                                    'accounting_branch_program.id as id',
                                    'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsCash.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesCash.')'],'finalFreebies.id = accounting_branch_program.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsCash.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesCash.')'],'finalPettyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesCash.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesCash.')'],'finalOtherExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsCash.')'],'finalBankDeposits.id = accounting_branch_program.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesCash.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersCash.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all();

                $auditSummaryCashSeason = $access ? $access->branch_program_id != '' ? Season::find()
                                ->select([
                                    'accounting_season.id as id',
                                    'accounting_season.branch_program_id as branch_program_id',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsCashSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesCashSeason.')'],'finalFreebies.id = accounting_season.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesCashSeason.')'],'finalPettyExpense.id = accounting_season.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesCashSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesCashSeason.')'],'finalOtherExpense.id = accounting_season.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsCashSeason.')'],'finalBankDeposits.id = accounting_season.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesCashSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsCashSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersCashSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : Season::find()
                                ->select([
                                    'accounting_season.id as id',
                                    'accounting_season.branch_program_id as branch_program_id',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsCashSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesCashSeason.')'],'finalFreebies.id = accounting_season.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesCashSeason.')'],'finalPettyExpense.id = accounting_season.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesCashSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesCashSeason.')'],'finalOtherExpense.id = accounting_season.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsCashSeason.')'],'finalBankDeposits.id = accounting_season.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesCashSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsCashSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersCashSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : Season::find()
                                ->select([
                                    'accounting_season.id as id',
                                    'accounting_season.branch_program_id as branch_program_id',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsCashSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesCashSeason.')'],'finalFreebies.id = accounting_season.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesCashSeason.')'],'finalPettyExpense.id = accounting_season.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesCashSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesCashSeason.')'],'finalOtherExpense.id = accounting_season.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsCashSeason.')'],'finalBankDeposits.id = accounting_season.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesCashSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsCashSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersCashSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all();

                $incomeEnrolmentsNonCash = $this->getIncomeEnrolmentByBranchProgramRawSql(null, null, 'Non-Cash');

                $incomeEnrolmentsNonCashSeason = $this->getIncomeEnrolmentBySeasonRawSql(null, null, 'Non-Cash');

                $incomeFreebiesNonCash = $this->getIncomeFreebieByBranchProgramRawSql(null, null, 'Non-Cash');

                $incomeFreebiesNonCashSeason = $this->getIncomeFreebieBySeasonRawSql(null, null, 'Non-Cash');

                $pettyExpensesNonCash = $this->getPettyExpenseByBranchProgramRawSql(null, null, 'Non-Cash');

                $pettyExpensesNonCashSeason = $this->getPettyExpenseBySeasonRawSql(null, null, 'Non-Cash');

                $photocopyExpensesNonCash = $this->getPhotocopyExpenseByBranchProgramRawSql(null, null, 'Non-Cash');

                $photocopyExpensesNonCashSeason = $this->getPhotocopyExpenseBySeasonRawSql(null, null, 'Non-Cash');

                $otherExpensesNonCash = $this->getOtherExpenseByBranchProgramRawSql(null, null, 'Non-Cash');

                $otherExpensesNonCashSeason = $this->getOtherExpenseBySeasonRawSql(null, null, 'Non-Cash');

                $bankDepositsNonCash = $this->getBankDepositByBranchProgramRawSql(null, null, 'Non-Cash');

                $bankDepositsNonCashSeason = $this->getBankDepositBySeasonRawSql(null, null, 'Non-Cash');

                $operatingExpensesNonCash = $this->getOperatingExpenseByBranchProgramRawSql(null, null, 'Non-Cash');

                $operatingExpensesNonCashSeason = $this->getOperatingExpenseBySeasonRawSql(null, null, 'Non-Cash');

                $budgetProposalsNonCash = $this->getBudgetProposalByBranchProgramRawSql(null, null, 'Non-Cash');

                $budgetProposalsNonCashSeason = $this->getBudgetProposalBySeasonRawSql(null, null, 'Non-Cash');

                $branchTransfersNonCash = $this->getBranchTransferByBranchProgramRawSql(null, null, 'Non-Cash');

                $branchTransfersNonCashSeason = $this->getBranchTransferBySeasonRawSql(null, null, 'Non-Cash');

                $auditSummaryNonCash = $access ? $access->branch_program_id != '' ? BranchProgram::find()
                                ->select([
                                    'accounting_branch_program.id as id',
                                    'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsNonCash.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesNonCash.')'],'finalFreebies.id = accounting_branch_program.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsNonCash.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesNonCash.')'],'finalPettyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesNonCash.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesNonCash.')'],'finalOtherExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsNonCash.')'],'finalBankDeposits.id = accounting_branch_program.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesNonCash.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersNonCash.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : BranchProgram::find()
                                ->select([
                                    'accounting_branch_program.id as id',
                                    'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsNonCash.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesNonCash.')'],'finalFreebies.id = accounting_branch_program.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsNonCash.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesNonCash.')'],'finalPettyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesNonCash.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesNonCash.')'],'finalOtherExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsNonCash.')'],'finalBankDeposits.id = accounting_branch_program.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesNonCash.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersNonCash.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : BranchProgram::find()
                                ->select([
                                    'accounting_branch_program.id as id',
                                    'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsNonCash.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesNonCash.')'],'finalFreebies.id = accounting_branch_program.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsNonCash.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesNonCash.')'],'finalPettyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesNonCash.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesNonCash.')'],'finalOtherExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsNonCash.')'],'finalBankDeposits.id = accounting_branch_program.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesNonCash.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersNonCash.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all();

                $auditSummaryNonCashSeason = $access ? $access->branch_program_id != '' ? Season::find()
                                ->select([
                                    'accounting_season.id as id',
                                    'accounting_season.branch_program_id as branch_program_id',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsNonCashSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesNonCashSeason.')'],'finalFreebies.id = accounting_season.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesNonCashSeason.')'],'finalPettyExpense.id = accounting_season.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesNonCashSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesNonCashSeason.')'],'finalOtherExpense.id = accounting_season.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsNonCashSeason.')'],'finalBankDeposits.id = accounting_season.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesNonCashSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsNonCashSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersNonCashSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : Season::find()
                                ->select([
                                    'accounting_season.id as id',
                                    'accounting_season.branch_program_id as branch_program_id',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsNonCashSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesNonCashSeason.')'],'finalFreebies.id = accounting_season.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesNonCashSeason.')'],'finalPettyExpense.id = accounting_season.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesNonCashSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesNonCashSeason.')'],'finalOtherExpense.id = accounting_season.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsNonCashSeason.')'],'finalBankDeposits.id = accounting_season.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesNonCashSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsNonCashSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersNonCashSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : Season::find()
                                ->select([
                                    'accounting_season.id as id',
                                    'accounting_season.branch_program_id as branch_program_id',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsNonCashSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesNonCashSeason.')'],'finalFreebies.id = accounting_season.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesNonCashSeason.')'],'finalPettyExpense.id = accounting_season.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesNonCashSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesNonCashSeason.')'],'finalOtherExpense.id = accounting_season.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsNonCashSeason.')'],'finalBankDeposits.id = accounting_season.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesNonCashSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsNonCashSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersNonCashSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all();
            }            
        }

        return $this->renderAjax('_audit_summary',[
                'date' => $date,
                'access' => $access,
                'auditSummary' => $auditSummary,
                'auditSummarySeason' => $auditSummarySeason,
                'auditSummaryCash' => $auditSummaryCash,
                'auditSummaryCashSeason' => $auditSummaryCashSeason,
                'auditSummaryNonCash' => $auditSummaryNonCash,
                'auditSummaryNonCashSeason' => $auditSummaryNonCashSeason,
            ]);
    }

    public function actionGenerateOverall($date = null)
    {
        $date = json_decode($date, true);

        $user_info = Yii::$app->user->identity->userinfo;
        $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
        $rolenames =  ArrayHelper::map($roles, 'name','name');

        $access = AccessProgram::findOne(['user_id' => Yii::$app->user->identity->userinfo->user_id]) ? AccessProgram::findOne(['user_id' => Yii::$app->user->identity->userinfo->user_id]) : new AccessProgram();

        $targetAcademic = $this->getTargetAcademicByBranchProgram();

        $targetAcademicSeason = $this->getTargetAcademicBySeason();

        $targetEmergencyFund = $this->getTargetEmergencyFundByBranchProgram();

        $targetEmergencyFundSeason = $this->getTargetEmergencyFundBySeason();

        $targetFood = $this->getTargetFoodByBranchProgram();

        $targetFoodSeason = $this->getTargetFoodBySeason();

        $targetFreebie = $this->getTargetFreebieByBranchProgram();

        $targetFreebieSeason = $this->getTargetFreebieBySeason();

        $targetIncome = $this->getTargetIncomeByBranchProgram();

        $targetIncomeSeason = $this->getTargetIncomeBySeason();

        $targetProgram = $this->getTargetProgramByBranchProgram();

        $targetProgramSeason = $this->getTargetProgramBySeason();

        $targetRebate = $this->getTargetRebateByBranchProgram();

        $targetRebateSeason = $this->getTargetRebateBySeason();

        $targetReview = $this->getTargetReviewByBranchProgram();

        $targetReviewSeason = $this->getTargetReviewBySeason();

        $targetStaffSalary = $this->getTargetStaffSalaryByBranchProgram();

        $targetStaffSalarySeason = $this->getTargetStaffSalaryBySeason();

        $targetTransportation = $this->getTargetTransportationByBranchProgram();

        $targetTransportationSeason = $this->getTargetTransportationBySeason();

        $targetUtility = $this->getTargetUtilityByBranchProgram();

        $targetUtilitySeason = $this->getTargetUtilityBySeason();

        $targetVenueRental = $this->getTargetVenueRentalByBranchProgram();

        $targetVenueRentalSeason = $this->getTargetVenueRentalBySeason();

        if(in_array('TopManagement',$rolenames)){

            if(!empty($date))
            {
                $incomeEnrolments = $this->getOverallIncomeEnrolmentByBranchProgramRawSql($date[0][0], $date[0][1]);

                $incomeEnrolmentsSeason = $this->getOverallIncomeEnrolmentBySeasonRawSql($date[0][0], $date[0][1]);

                $incomeFreebies = $this->getOverallIncomeFreebieByBranchProgramRawSql($date[0][0], $date[0][1]);

                $incomeFreebiesSeason = $this->getOverallIncomeFreebieBySeasonRawSql($date[0][0], $date[0][1]);

                $pettyExpenses = $this->getOverallPettyExpenseByBranchProgramRawSql($date[0][0], $date[0][1]);

                $pettyExpensesSeason = $this->getOverallPettyExpenseBySeasonRawSql($date[0][0], $date[0][1]);

                $photocopyExpenses = $this->getOverallPhotocopyExpenseByBranchProgramRawSql($date[0][0], $date[0][1]);

                $photocopyExpensesSeason = $this->getOverallPhotocopyExpenseBySeasonRawSql($date[0][0], $date[0][1]);

                $otherExpenses = $this->getOverallOtherExpenseByBranchProgramRawSql($date[0][0], $date[0][1]);

                $otherExpensesSeason = $this->getOverallOtherExpenseBySeasonRawSql($date[0][0], $date[0][1]);

                $bankDeposits = $this->getOverallBankDepositByBranchProgramRawSql($date[0][0], $date[0][1]);

                $bankDepositsSeason = $this->getOverallBankDepositBySeasonRawSql($date[0][0], $date[0][1]);

                $operatingExpenses = $this->getOverallOperatingExpenseByBranchProgramRawSql($date[0][0], $date[0][1]);

                $operatingExpensesSeason = $this->getOverallOperatingExpenseBySeasonRawSql($date[0][0], $date[0][1]);

                $budgetProposals = $this->getOverallBudgetProposalByBranchProgramRawSql($date[0][0], $date[0][1]);

                $budgetProposalsSeason = $this->getOverallBudgetProposalBySeasonRawSql($date[0][0], $date[0][1]);

                $branchTransfers = $this->getOverallBranchTransferByBranchProgramRawSql($date[0][0], $date[0][1]);

                $branchTransfersSeason = $this->getOverallBranchTransferBySeasonRawSql($date[0][0], $date[0][1]);

                $beginningCoh = $this->getBeginningCashByBranchProgram($date[0][0], $date[0][1]);

                $beginningCohSeason = $this->getBeginningCashBySeason($date[0][0], $date[0][1]);

                $auditTargetSeason = Season::find()
                            ->select([
                                'accounting_season.id as id',
                                'accounting_season.branch_program_id as branch_program_id',
                                'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                'COALESCE(targetIncome.total, 0) as totalGross',
                                'COALESCE((targetIncome.total - ((targetIncome.total/1.12)*0.12)), 0) as totalGrossIncome',
                                'COALESCE(targetProgram.total + 
                                          targetVenueRental.total + 
                                          targetFreebie.total +
                                          targetReview.total +
                                          targetFood.total +
                                          targetTransportation.total +
                                          targetStaffSalary.total + 
                                          targetRebate.total +
                                          targetUtility.total +
                                          targetAcademic.total +
                                          targetEmergencyFund.total
                                          , 0) as totalExpenses',
                                'COALESCE((targetIncome.total - ((targetIncome.total/1.12)*0.12)) - (targetProgram.total + 
                                          targetVenueRental.total + 
                                          targetFreebie.total +
                                          targetReview.total +
                                          targetFood.total +
                                          targetTransportation.total +
                                          targetStaffSalary.total + 
                                          targetRebate.total +
                                          targetUtility.total +
                                          targetAcademic.total +
                                          targetEmergencyFund.total), 0) as expectedIncome',
                                'COALESCE(((targetIncome.total - ((targetIncome.total/1.12)*0.12)) - (targetProgram.total + 
                                          targetVenueRental.total + 
                                          targetFreebie.total +
                                          targetReview.total +
                                          targetFood.total +
                                          targetTransportation.total +
                                          targetStaffSalary.total + 
                                          targetRebate.total +
                                          targetUtility.total +
                                          targetAcademic.total +
                                          targetEmergencyFund.total) - ((targetIncome.total - ((targetIncome.total/1.12)*0.12)) - (targetProgram.total + 
                                          targetVenueRental.total + 
                                          targetFreebie.total +
                                          targetReview.total +
                                          targetFood.total +
                                          targetTransportation.total +
                                          targetStaffSalary.total + 
                                          targetRebate.total +
                                          targetUtility.total +
                                          targetAcademic.total +
                                          targetEmergencyFund.total)) * accounting_target_royalty_fee.percentage), 0) as netIncome'
                            ])

                            ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                            ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                            ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                            ->leftJoin('accounting_target_royalty_fee','accounting_target_royalty_fee.season_id = accounting_season.id')
                            ->leftJoin(['targetAcademic' => '('.$targetAcademicSeason.')'],'targetAcademic.id = accounting_season.id')
                            ->leftJoin(['targetEmergencyFund' => '('.$targetEmergencyFundSeason.')'],'targetEmergencyFund.id = accounting_season.id')
                            ->leftJoin(['targetFood' => '('.$targetFoodSeason.')'],'targetFood.id = accounting_season.id')
                            ->leftJoin(['targetFreebie' => '('.$targetFreebieSeason.')'],'targetFreebie.id = accounting_season.id')
                            ->leftJoin(['targetIncome' => '('.$targetIncomeSeason.')'],'targetIncome.id = accounting_season.id')
                            ->leftJoin(['targetProgram' => '('.$targetProgramSeason.')'],'targetProgram.id = accounting_season.id')
                            ->leftJoin(['targetRebate' => '('.$targetRebateSeason.')'],'targetRebate.id = accounting_season.id')
                            ->leftJoin(['targetReview' => '('.$targetReviewSeason.')'],'targetReview.id = accounting_season.id')
                            ->leftJoin(['targetStaffSalary' => '('.$targetStaffSalarySeason.')'],'targetStaffSalary.id = accounting_season.id')
                            ->leftJoin(['targetTransportation' => '('.$targetTransportationSeason.')'],'targetTransportation.id = accounting_season.id')
                            ->leftJoin(['targetUtility' => '('.$targetUtilitySeason.')'],'targetUtility.id = accounting_season.id')
                            ->leftJoin(['targetVenueRental' => '('.$targetVenueRentalSeason.')'],'targetVenueRental.id = accounting_season.id')
                            ->createCommand()
                            ->getRawSql();

                $auditTargetBranch = BranchProgram::find()
                                ->select([
                                    'accounting_branch_program.id as id',
                                    'sum(COALESCE(targetSeason.totalGross, 0)) as totalGross',
                                    'sum(COALESCE(targetSeason.totalGrossIncome, 0)) as totalGrossIncome',
                                    'sum(COALESCE(targetSeason.totalExpenses, 0)) as totalExpenses',
                                    'sum(COALESCE(targetSeason.expectedIncome, 0)) as expectedIncome',
                                    'sum(COALESCE(targetSeason.netIncome, 0)) as netIncome',
                                ])
                                ->leftJoin(['targetSeason' => '('.$auditTargetSeason.')'],'targetSeason.branch_program_id = accounting_branch_program.id')
                                ->groupBy(['targetSeason.branch_program_id'])
                                ->createCommand()                              
                                ->getRawSql();

                $auditSummary = $access ? $access->branch_program_id != '' ? BranchProgram::find()
                            ->select([
                                'accounting_branch_program.id as id',
                                'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                'finalBeginningCoh.coh as beginningCoh',
                                'finalBeginningCoh.cob as beginningCob',
                                'finalBankDeposits.total as bankDepositsTotal',
                                '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                '(
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0) +
                                    COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBankDeposits.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0) +
                                    COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal',
                                'targetBranch.totalGross as targetGross',
                                'targetBranch.totalGrossIncome as targetGrossIncome',
                                'targetBranch.totalExpenses as targetExpenses',
                                'targetBranch.expectedIncome as targetExpectedIncome',
                                'targetBranch.netIncome as targetNetIncome',
                            ])
                            ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                            ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                            ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolments.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                            ->leftJoin(['finalFreebies' => '('.$incomeFreebies.')'],'finalFreebies.id = accounting_branch_program.id')
                            ->leftJoin(['finalBudgetProposals' => '('.$budgetProposals.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                            ->leftJoin(['finalPettyExpense' => '('.$pettyExpenses.')'],'finalPettyExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpenses.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalOtherExpense' => '('.$otherExpenses.')'],'finalOtherExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalBankDeposits' => '('.$bankDeposits.')'],'finalBankDeposits.id = accounting_branch_program.id')
                            ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpenses.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                            ->leftJoin(['finalBranchTransfers' => '('.$branchTransfers.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                            ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                            ->leftJoin(['targetBranch' => '('.$auditTargetBranch.')'],'targetBranch.id = accounting_branch_program.id')
                            ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                            ->asArray()
                            ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                            ->all() : BranchProgram::find()
                            ->select([
                                'accounting_branch_program.id as id',
                                'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                'finalBeginningCoh.coh as beginningCoh',
                                'finalBeginningCoh.cob as beginningCob',
                                'finalBankDeposits.total as bankDepositsTotal',
                                '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                '(
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0) +
                                    COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBankDeposits.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0) +
                                    COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal',
                                'targetBranch.totalGross as targetGross',
                                'targetBranch.totalGrossIncome as targetGrossIncome',
                                'targetBranch.totalExpenses as targetExpenses',
                                'targetBranch.expectedIncome as targetExpectedIncome',
                                'targetBranch.netIncome as targetNetIncome',
                            ])
                            ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                            ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                            ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolments.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                            ->leftJoin(['finalFreebies' => '('.$incomeFreebies.')'],'finalFreebies.id = accounting_branch_program.id')
                            ->leftJoin(['finalBudgetProposals' => '('.$budgetProposals.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                            ->leftJoin(['finalPettyExpense' => '('.$pettyExpenses.')'],'finalPettyExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpenses.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalOtherExpense' => '('.$otherExpenses.')'],'finalOtherExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalBankDeposits' => '('.$bankDeposits.')'],'finalBankDeposits.id = accounting_branch_program.id')
                            ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpenses.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                            ->leftJoin(['finalBranchTransfers' => '('.$branchTransfers.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                            ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                            ->leftJoin(['targetBranch' => '('.$auditTargetBranch.')'],'targetBranch.id = accounting_branch_program.id')
                            ->asArray()
                            ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                            ->all() : BranchProgram::find()
                            ->select([
                                'accounting_branch_program.id as id',
                                'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                'finalBeginningCoh.coh as beginningCoh',
                                'finalBeginningCoh.cob as beginningCob',
                                'finalBankDeposits.total as bankDepositsTotal',
                                '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                '(
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0) +
                                    COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBankDeposits.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0) +
                                    COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal',
                                'targetBranch.totalGross as targetGross',
                                'targetBranch.totalGrossIncome as targetGrossIncome',
                                'targetBranch.totalExpenses as targetExpenses',
                                'targetBranch.expectedIncome as targetExpectedIncome',
                                'targetBranch.netIncome as targetNetIncome',
                            ])
                            ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                            ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                            ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolments.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                            ->leftJoin(['finalFreebies' => '('.$incomeFreebies.')'],'finalFreebies.id = accounting_branch_program.id')
                            ->leftJoin(['finalBudgetProposals' => '('.$budgetProposals.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                            ->leftJoin(['finalPettyExpense' => '('.$pettyExpenses.')'],'finalPettyExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpenses.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalOtherExpense' => '('.$otherExpenses.')'],'finalOtherExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalBankDeposits' => '('.$bankDeposits.')'],'finalBankDeposits.id = accounting_branch_program.id')
                            ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpenses.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                            ->leftJoin(['finalBranchTransfers' => '('.$branchTransfers.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                            ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                            ->leftJoin(['targetBranch' => '('.$auditTargetBranch.')'],'targetBranch.id = accounting_branch_program.id')
                            ->asArray()
                            ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                            ->all();

                $auditSummarySeason = $access ? $access->branch_program_id != '' ? Season::find()
                            ->select([
                                'accounting_season.id as id',
                                'accounting_season.branch_program_id as branch_program_id',
                                'finalBeginningCoh.coh as beginningCoh',
                                'finalBeginningCoh.cob as beginningCob',
                                'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                'finalBankDeposits.total as bankDepositsTotal',
                                '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                '(
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBranchTransfers.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBankDeposits.total, 0) + 
                                    COALESCE(finalBranchTransfers.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal',
                                'COALESCE(targets.totalGross, 0) as targetGross',
                                'COALESCE(targets.totalGrossIncome, 0) as targetGrossIncome',
                                'COALESCE(targets.totalExpenses, 0) as targetExpenses',
                                'COALESCE(targets.expectedIncome, 0) as targetExpectedIncome',
                                'COALESCE(targets.netIncome, 0) as targetNetIncome',
                            ])
                            ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                            ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                            ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                            ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                            ->leftJoin(['finalFreebies' => '('.$incomeFreebiesSeason.')'],'finalFreebies.id = accounting_season.id')
                            ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesSeason.')'],'finalPettyExpense.id = accounting_season.id')
                            ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                            ->leftJoin(['finalOtherExpense' => '('.$otherExpensesSeason.')'],'finalOtherExpense.id = accounting_season.id')
                            ->leftJoin(['finalBankDeposits' => '('.$bankDepositsSeason.')'],'finalBankDeposits.id = accounting_season.id')
                            ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                            ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                            ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                            ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                            ->leftJoin(['targets' => '('.$auditTargetSeason.')'],'targets.id = accounting_season.id')
                            ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                            ->asArray()
                            ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                            ->all() : Season::find()
                            ->select([
                                'accounting_season.id as id',
                                'accounting_season.branch_program_id as branch_program_id',
                                'finalBeginningCoh.coh as beginningCoh',
                                'finalBeginningCoh.cob as beginningCob',
                                'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                'finalBankDeposits.total as bankDepositsTotal',
                                '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                '(
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBranchTransfers.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBankDeposits.total, 0) + 
                                    COALESCE(finalBranchTransfers.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal',
                                'COALESCE(targets.totalGross, 0) as targetGross',
                                'COALESCE(targets.totalGrossIncome, 0) as targetGrossIncome',
                                'COALESCE(targets.totalExpenses, 0) as targetExpenses',
                                'COALESCE(targets.expectedIncome, 0) as targetExpectedIncome',
                                'COALESCE(targets.netIncome, 0) as targetNetIncome',
                            ])
                            ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                            ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                            ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                            ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                            ->leftJoin(['finalFreebies' => '('.$incomeFreebiesSeason.')'],'finalFreebies.id = accounting_season.id')
                            ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesSeason.')'],'finalPettyExpense.id = accounting_season.id')
                            ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                            ->leftJoin(['finalOtherExpense' => '('.$otherExpensesSeason.')'],'finalOtherExpense.id = accounting_season.id')
                            ->leftJoin(['finalBankDeposits' => '('.$bankDepositsSeason.')'],'finalBankDeposits.id = accounting_season.id')
                            ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                            ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                            ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                            ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                            ->leftJoin(['targets' => '('.$auditTargetSeason.')'],'targets.id = accounting_season.id')
                            ->asArray()
                            ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                            ->all() : Season::find()
                            ->select([
                                'accounting_season.id as id',
                                'accounting_season.branch_program_id as branch_program_id',
                                'finalBeginningCoh.coh as beginningCoh',
                                'finalBeginningCoh.cob as beginningCob',
                                'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                'finalBankDeposits.total as bankDepositsTotal',
                                '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                '(
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBranchTransfers.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBankDeposits.total, 0) + 
                                    COALESCE(finalBranchTransfers.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal',
                                'COALESCE(targets.totalGross, 0) as targetGross',
                                'COALESCE(targets.totalGrossIncome, 0) as targetGrossIncome',
                                'COALESCE(targets.totalExpenses, 0) as targetExpenses',
                                'COALESCE(targets.expectedIncome, 0) as targetExpectedIncome',
                                'COALESCE(targets.netIncome, 0) as targetNetIncome',
                            ])
                            ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                            ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                            ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                            ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                            ->leftJoin(['finalFreebies' => '('.$incomeFreebiesSeason.')'],'finalFreebies.id = accounting_season.id')
                            ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesSeason.')'],'finalPettyExpense.id = accounting_season.id')
                            ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                            ->leftJoin(['finalOtherExpense' => '('.$otherExpensesSeason.')'],'finalOtherExpense.id = accounting_season.id')
                            ->leftJoin(['finalBankDeposits' => '('.$bankDepositsSeason.')'],'finalBankDeposits.id = accounting_season.id')
                            ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                            ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                            ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                            ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                            ->leftJoin(['targets' => '('.$auditTargetSeason.')'],'targets.id = accounting_season.id')
                            ->asArray()
                            ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                            ->all();
            }else{
                $incomeEnrolments = $this->getOverallIncomeEnrolmentByBranchProgramRawSql();

                $incomeEnrolmentsSeason = $this->getOverallIncomeEnrolmentBySeasonRawSql();

                $incomeFreebies = $this->getOverallIncomeFreebieByBranchProgramRawSql();

                $incomeFreebiesSeason = $this->getOverallIncomeFreebieBySeasonRawSql();

                $pettyExpenses = $this->getOverallPettyExpenseByBranchProgramRawSql();

                $pettyExpensesSeason = $this->getOverallPettyExpenseBySeasonRawSql();

                $photocopyExpenses = $this->getOverallPhotocopyExpenseByBranchProgramRawSql();

                $photocopyExpensesSeason = $this->getOverallPhotocopyExpenseBySeasonRawSql();

                $otherExpenses = $this->getOverallOtherExpenseByBranchProgramRawSql();

                $otherExpensesSeason = $this->getOverallOtherExpenseBySeasonRawSql();

                $bankDeposits = $this->getOverallBankDepositByBranchProgramRawSql();

                $bankDepositsSeason = $this->getOverallBankDepositBySeasonRawSql();

                $operatingExpenses = $this->getOverallOperatingExpenseByBranchProgramRawSql();

                $operatingExpensesSeason = $this->getOverallOperatingExpenseBySeasonRawSql();

                $budgetProposals = $this->getOverallBudgetProposalByBranchProgramRawSql();

                $budgetProposalsSeason = $this->getOverallBudgetProposalBySeasonRawSql();

                $branchTransfers = $this->getOverallBranchTransferByBranchProgramRawSql();

                $branchTransfersSeason = $this->getOverallBranchTransferBySeasonRawSql();

                $beginningCoh = $this->getBeginningCash();

                $auditTargetSeason = Season::find()
                            ->select([
                                'accounting_season.id as id',
                                'accounting_season.branch_program_id as branch_program_id',
                                'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                'COALESCE(targetIncome.total, 0) as totalGross',
                                'COALESCE((targetIncome.total - ((targetIncome.total/1.12)*0.12)), 0) as totalGrossIncome',
                                'COALESCE(targetProgram.total + 
                                          targetVenueRental.total + 
                                          targetFreebie.total +
                                          targetReview.total +
                                          targetFood.total +
                                          targetTransportation.total +
                                          targetStaffSalary.total + 
                                          targetRebate.total +
                                          targetUtility.total +
                                          targetAcademic.total +
                                          targetEmergencyFund.total
                                          , 0) as totalExpenses',
                                'COALESCE((targetIncome.total - ((targetIncome.total/1.12)*0.12)) - (targetProgram.total + 
                                          targetVenueRental.total + 
                                          targetFreebie.total +
                                          targetReview.total +
                                          targetFood.total +
                                          targetTransportation.total +
                                          targetStaffSalary.total + 
                                          targetRebate.total +
                                          targetUtility.total +
                                          targetAcademic.total +
                                          targetEmergencyFund.total), 0) as expectedIncome',
                                'COALESCE(((targetIncome.total - ((targetIncome.total/1.12)*0.12)) - (targetProgram.total + 
                                          targetVenueRental.total + 
                                          targetFreebie.total +
                                          targetReview.total +
                                          targetFood.total +
                                          targetTransportation.total +
                                          targetStaffSalary.total + 
                                          targetRebate.total +
                                          targetUtility.total +
                                          targetAcademic.total +
                                          targetEmergencyFund.total) - ((targetIncome.total - ((targetIncome.total/1.12)*0.12)) - (targetProgram.total + 
                                          targetVenueRental.total + 
                                          targetFreebie.total +
                                          targetReview.total +
                                          targetFood.total +
                                          targetTransportation.total +
                                          targetStaffSalary.total + 
                                          targetRebate.total +
                                          targetUtility.total +
                                          targetAcademic.total +
                                          targetEmergencyFund.total)) * accounting_target_royalty_fee.percentage), 0) as netIncome'
                            ])

                            ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                            ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                            ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                            ->leftJoin('accounting_target_royalty_fee','accounting_target_royalty_fee.season_id = accounting_season.id')
                            ->leftJoin(['targetAcademic' => '('.$targetAcademicSeason.')'],'targetAcademic.id = accounting_season.id')
                            ->leftJoin(['targetEmergencyFund' => '('.$targetEmergencyFundSeason.')'],'targetEmergencyFund.id = accounting_season.id')
                            ->leftJoin(['targetFood' => '('.$targetFoodSeason.')'],'targetFood.id = accounting_season.id')
                            ->leftJoin(['targetFreebie' => '('.$targetFreebieSeason.')'],'targetFreebie.id = accounting_season.id')
                            ->leftJoin(['targetIncome' => '('.$targetIncomeSeason.')'],'targetIncome.id = accounting_season.id')
                            ->leftJoin(['targetProgram' => '('.$targetProgramSeason.')'],'targetProgram.id = accounting_season.id')
                            ->leftJoin(['targetRebate' => '('.$targetRebateSeason.')'],'targetRebate.id = accounting_season.id')
                            ->leftJoin(['targetReview' => '('.$targetReviewSeason.')'],'targetReview.id = accounting_season.id')
                            ->leftJoin(['targetStaffSalary' => '('.$targetStaffSalarySeason.')'],'targetStaffSalary.id = accounting_season.id')
                            ->leftJoin(['targetTransportation' => '('.$targetTransportationSeason.')'],'targetTransportation.id = accounting_season.id')
                            ->leftJoin(['targetUtility' => '('.$targetUtilitySeason.')'],'targetUtility.id = accounting_season.id')
                            ->leftJoin(['targetVenueRental' => '('.$targetVenueRentalSeason.')'],'targetVenueRental.id = accounting_season.id')
                            ->createCommand()
                            ->getRawSql();

                $auditTargetBranch = BranchProgram::find()
                                ->select([
                                    'accounting_branch_program.id as id',
                                    'sum(COALESCE(targetSeason.totalGross, 0)) as totalGross',
                                    'sum(COALESCE(targetSeason.totalGrossIncome, 0)) as totalGrossIncome',
                                    'sum(COALESCE(targetSeason.totalExpenses, 0)) as totalExpenses',
                                    'sum(COALESCE(targetSeason.expectedIncome, 0)) as expectedIncome',
                                    'sum(COALESCE(targetSeason.netIncome, 0)) as netIncome',
                                ])
                                ->leftJoin(['targetSeason' => '('.$auditTargetSeason.')'],'targetSeason.branch_program_id = accounting_branch_program.id')
                                ->groupBy(['targetSeason.branch_program_id'])
                                ->createCommand()                              
                                ->getRawSql();

                $auditSummary = $access ? $access->branch_program_id != '' ? BranchProgram::find()
                            ->select([
                                'accounting_branch_program.id as id',
                                'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                'finalBeginningCoh.coh as beginningCoh',
                                'finalBeginningCoh.cob as beginningCob',
                                'finalBankDeposits.total as bankDepositsTotal',
                                '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                '(
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0) +
                                    COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                '((COALESCE(finalBeginningCoh.coh,0) + COALESCE(finalBeginningCoh.cob,0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBankDeposits.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0) +
                                    COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal',
                                'targetBranch.totalGross as targetGross',
                                'targetBranch.totalGrossIncome as targetGrossIncome',
                                'targetBranch.totalExpenses as targetExpenses',
                                'targetBranch.expectedIncome as targetExpectedIncome',
                                'targetBranch.netIncome as targetNetIncome',
                            ])
                            ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                            ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                            ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolments.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                            ->leftJoin(['finalFreebies' => '('.$incomeFreebies.')'],'finalFreebies.id = accounting_branch_program.id')
                            ->leftJoin(['finalBudgetProposals' => '('.$budgetProposals.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                            ->leftJoin(['finalPettyExpense' => '('.$pettyExpenses.')'],'finalPettyExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpenses.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalOtherExpense' => '('.$otherExpenses.')'],'finalOtherExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalBankDeposits' => '('.$bankDeposits.')'],'finalBankDeposits.id = accounting_branch_program.id')
                            ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpenses.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                            ->leftJoin(['finalBranchTransfers' => '('.$branchTransfers.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                            ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                            ->leftJoin(['targetBranch' => '('.$auditTargetBranch.')'],'targetBranch.id = accounting_branch_program.id')
                            ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                            ->asArray()
                            ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                            ->all() : BranchProgram::find()
                            ->select([
                                'accounting_branch_program.id as id',
                                'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                'finalBeginningCoh.coh as beginningCoh',
                                'finalBeginningCoh.cob as beginningCob',
                                'finalBankDeposits.total as bankDepositsTotal',
                                '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                '(
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0) +
                                    COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                '((COALESCE(finalBeginningCoh.coh,0) + COALESCE(finalBeginningCoh.cob,0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBankDeposits.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0) +
                                    COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal',
                                'targetBranch.totalGross as targetGross',
                                'targetBranch.totalGrossIncome as targetGrossIncome',
                                'targetBranch.totalExpenses as targetExpenses',
                                'targetBranch.expectedIncome as targetExpectedIncome',
                                'targetBranch.netIncome as targetNetIncome',
                            ])
                            ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                            ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                            ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolments.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                            ->leftJoin(['finalFreebies' => '('.$incomeFreebies.')'],'finalFreebies.id = accounting_branch_program.id')
                            ->leftJoin(['finalBudgetProposals' => '('.$budgetProposals.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                            ->leftJoin(['finalPettyExpense' => '('.$pettyExpenses.')'],'finalPettyExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpenses.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalOtherExpense' => '('.$otherExpenses.')'],'finalOtherExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalBankDeposits' => '('.$bankDeposits.')'],'finalBankDeposits.id = accounting_branch_program.id')
                            ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpenses.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                            ->leftJoin(['finalBranchTransfers' => '('.$branchTransfers.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                            ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                            ->leftJoin(['targetBranch' => '('.$auditTargetBranch.')'],'targetBranch.id = accounting_branch_program.id')
                            ->asArray()
                            ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                            ->all() : BranchProgram::find()
                            ->select([
                                'accounting_branch_program.id as id',
                                'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                'finalBeginningCoh.coh as beginningCoh',
                                'finalBeginningCoh.cob as beginningCob',
                                'finalBankDeposits.total as bankDepositsTotal',
                                '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                '(
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0) +
                                    COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                '((COALESCE(finalBeginningCoh.coh,0) + COALESCE(finalBeginningCoh.cob,0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBankDeposits.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0) +
                                    COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal',
                                'targetBranch.totalGross as targetGross',
                                'targetBranch.totalGrossIncome as targetGrossIncome',
                                'targetBranch.totalExpenses as targetExpenses',
                                'targetBranch.expectedIncome as targetExpectedIncome',
                                'targetBranch.netIncome as targetNetIncome',
                            ])
                            ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                            ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                            ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolments.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                            ->leftJoin(['finalFreebies' => '('.$incomeFreebies.')'],'finalFreebies.id = accounting_branch_program.id')
                            ->leftJoin(['finalBudgetProposals' => '('.$budgetProposals.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                            ->leftJoin(['finalPettyExpense' => '('.$pettyExpenses.')'],'finalPettyExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpenses.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalOtherExpense' => '('.$otherExpenses.')'],'finalOtherExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalBankDeposits' => '('.$bankDeposits.')'],'finalBankDeposits.id = accounting_branch_program.id')
                            ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpenses.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                            ->leftJoin(['finalBranchTransfers' => '('.$branchTransfers.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                            ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                            ->leftJoin(['targetBranch' => '('.$auditTargetBranch.')'],'targetBranch.id = accounting_branch_program.id')
                            ->asArray()
                            ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                            ->all();

                $auditSummarySeason = $access ? $access->branch_program_id != '' ? Season::find()
                            ->select([
                                'accounting_season.id as id',
                                'accounting_season.branch_program_id as branch_program_id',
                                'finalBeginningCoh.coh as beginningCoh',
                                'finalBeginningCoh.cob as beginningCob',
                                'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                'finalBankDeposits.total as bankDepositsTotal',
                                '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                '(
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBranchTransfers.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                '((COALESCE(finalBeginningCoh.coh,0) + COALESCE(finalBeginningCoh.cob,0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBankDeposits.total, 0) + 
                                    COALESCE(finalBranchTransfers.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal',
                                'COALESCE(targets.totalGross, 0) as targetGross',
                                'COALESCE(targets.totalGrossIncome, 0) as targetGrossIncome',
                                'COALESCE(targets.totalExpenses, 0) as targetExpenses',
                                'COALESCE(targets.expectedIncome, 0) as targetExpectedIncome',
                                'COALESCE(targets.netIncome, 0) as targetNetIncome',
                            ])
                            ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                            ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                            ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                            ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                            ->leftJoin(['finalFreebies' => '('.$incomeFreebiesSeason.')'],'finalFreebies.id = accounting_season.id')
                            ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesSeason.')'],'finalPettyExpense.id = accounting_season.id')
                            ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                            ->leftJoin(['finalOtherExpense' => '('.$otherExpensesSeason.')'],'finalOtherExpense.id = accounting_season.id')
                            ->leftJoin(['finalBankDeposits' => '('.$bankDepositsSeason.')'],'finalBankDeposits.id = accounting_season.id')
                            ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                            ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                            ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                            ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                            ->leftJoin(['targets' => '('.$auditTargetSeason.')'],'targets.id = accounting_season.id')
                            ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                            ->asArray()
                            ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                            ->all() : Season::find()
                            ->select([
                                'accounting_season.id as id',
                                'accounting_season.branch_program_id as branch_program_id',
                                'finalBeginningCoh.coh as beginningCoh',
                                'finalBeginningCoh.cob as beginningCob',
                                'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                'finalBankDeposits.total as bankDepositsTotal',
                                '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                '(
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBranchTransfers.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                '((COALESCE(finalBeginningCoh.coh,0) + COALESCE(finalBeginningCoh.cob,0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBankDeposits.total, 0) + 
                                    COALESCE(finalBranchTransfers.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal',
                                'COALESCE(targets.totalGross, 0) as targetGross',
                                'COALESCE(targets.totalGrossIncome, 0) as targetGrossIncome',
                                'COALESCE(targets.totalExpenses, 0) as targetExpenses',
                                'COALESCE(targets.expectedIncome, 0) as targetExpectedIncome',
                                'COALESCE(targets.netIncome, 0) as targetNetIncome',
                            ])
                            ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                            ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                            ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                            ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                            ->leftJoin(['finalFreebies' => '('.$incomeFreebiesSeason.')'],'finalFreebies.id = accounting_season.id')
                            ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesSeason.')'],'finalPettyExpense.id = accounting_season.id')
                            ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                            ->leftJoin(['finalOtherExpense' => '('.$otherExpensesSeason.')'],'finalOtherExpense.id = accounting_season.id')
                            ->leftJoin(['finalBankDeposits' => '('.$bankDepositsSeason.')'],'finalBankDeposits.id = accounting_season.id')
                            ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                            ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                            ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                            ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                            ->leftJoin(['targets' => '('.$auditTargetSeason.')'],'targets.id = accounting_season.id')
                            ->asArray()
                            ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                            ->all() : Season::find()
                            ->select([
                                'accounting_season.id as id',
                                'accounting_season.branch_program_id as branch_program_id',
                                'finalBeginningCoh.coh as beginningCoh',
                                'finalBeginningCoh.cob as beginningCob',
                                'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                'finalBankDeposits.total as bankDepositsTotal',
                                '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                '(
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBranchTransfers.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                '((COALESCE(finalBeginningCoh.coh,0) + COALESCE(finalBeginningCoh.cob,0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBankDeposits.total, 0) + 
                                    COALESCE(finalBranchTransfers.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal',
                                'COALESCE(targets.totalGross, 0) as targetGross',
                                'COALESCE(targets.totalGrossIncome, 0) as targetGrossIncome',
                                'COALESCE(targets.totalExpenses, 0) as targetExpenses',
                                'COALESCE(targets.expectedIncome, 0) as targetExpectedIncome',
                                'COALESCE(targets.netIncome, 0) as targetNetIncome',
                            ])
                            ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                            ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                            ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                            ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                            ->leftJoin(['finalFreebies' => '('.$incomeFreebiesSeason.')'],'finalFreebies.id = accounting_season.id')
                            ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesSeason.')'],'finalPettyExpense.id = accounting_season.id')
                            ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                            ->leftJoin(['finalOtherExpense' => '('.$otherExpensesSeason.')'],'finalOtherExpense.id = accounting_season.id')
                            ->leftJoin(['finalBankDeposits' => '('.$bankDepositsSeason.')'],'finalBankDeposits.id = accounting_season.id')
                            ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                            ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                            ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                            ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                            ->leftJoin(['targets' => '('.$auditTargetSeason.')'],'targets.id = accounting_season.id')
                            ->asArray()
                            ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                            ->all();
            } 
        }else{
            if(!empty($date))
            {
                $incomeEnrolments = $this->getOverallIncomeEnrolmentByBranchProgramRawSql($date[0][0], $date[0][1]);

                $incomeEnrolmentsSeason = $this->getOverallIncomeEnrolmentBySeasonRawSql($date[0][0], $date[0][1]);

                $incomeFreebies = $this->getOverallIncomeFreebieByBranchProgramRawSql($date[0][0], $date[0][1]);

                $incomeFreebiesSeason = $this->getOverallIncomeFreebieBySeasonRawSql($date[0][0], $date[0][1]);

                $pettyExpenses = $this->getOverallPettyExpenseByBranchProgramRawSql($date[0][0], $date[0][1]);

                $pettyExpensesSeason = $this->getOverallPettyExpenseBySeasonRawSql($date[0][0], $date[0][1]);

                $photocopyExpenses = $this->getOverallPhotocopyExpenseByBranchProgramRawSql($date[0][0], $date[0][1]);

                $photocopyExpensesSeason = $this->getOverallPhotocopyExpenseBySeasonRawSql($date[0][0], $date[0][1]);

                $otherExpenses = $this->getOverallOtherExpenseByBranchProgramRawSql($date[0][0], $date[0][1]);

                $otherExpensesSeason = $this->getOverallOtherExpenseBySeasonRawSql($date[0][0], $date[0][1]);

                $bankDeposits = $this->getOverallBankDepositByBranchProgramRawSql($date[0][0], $date[0][1]);

                $bankDepositsSeason = $this->getOverallBankDepositBySeasonRawSql($date[0][0], $date[0][1]);

                $operatingExpenses = $this->getOverallOperatingExpenseByBranchProgramRawSql($date[0][0], $date[0][1]);

                $operatingExpensesSeason = $this->getOverallOperatingExpenseBySeasonRawSql($date[0][0], $date[0][1]);

                $budgetProposals = $this->getOverallBudgetProposalByBranchProgramRawSql($date[0][0], $date[0][1]);

                $budgetProposalsSeason = $this->getOverallBudgetProposalBySeasonRawSql($date[0][0], $date[0][1]);

                $branchTransfers = $this->getOverallBranchTransferByBranchProgramRawSql($date[0][0], $date[0][1]);

                $branchTransfersSeason = $this->getOverallBranchTransferBySeasonRawSql($date[0][0], $date[0][1]);

                $beginningCoh = $this->getBeginningCashByBranchProgram($date[0][0], $date[0][1]);

                $beginningCohSeason = $this->getBeginningCashBySeason($date[0][0], $date[0][1]);

                $auditTargetSeason = Season::find()
                                ->select([
                                    'accounting_season.id as id',
                                    'accounting_season.branch_program_id as branch_program_id',
                                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                    'COALESCE(targetIncome.total, 0) as totalGross',
                                    'COALESCE((targetIncome.total - ((targetIncome.total/1.12)*0.12)), 0) as totalGrossIncome',
                                    'COALESCE(targetProgram.total + 
                                              targetVenueRental.total + 
                                              targetFreebie.total +
                                              targetReview.total +
                                              targetFood.total +
                                              targetTransportation.total +
                                              targetStaffSalary.total + 
                                              targetRebate.total +
                                              targetUtility.total +
                                              targetAcademic.total +
                                              targetEmergencyFund.total
                                              , 0) as totalExpenses',
                                    'COALESCE((targetIncome.total - ((targetIncome.total/1.12)*0.12)) - (targetProgram.total + 
                                              targetVenueRental.total + 
                                              targetFreebie.total +
                                              targetReview.total +
                                              targetFood.total +
                                              targetTransportation.total +
                                              targetStaffSalary.total + 
                                              targetRebate.total +
                                              targetUtility.total +
                                              targetAcademic.total +
                                              targetEmergencyFund.total), 0) as expectedIncome',
                                    'COALESCE(((targetIncome.total - ((targetIncome.total/1.12)*0.12)) - (targetProgram.total + 
                                              targetVenueRental.total + 
                                              targetFreebie.total +
                                              targetReview.total +
                                              targetFood.total +
                                              targetTransportation.total +
                                              targetStaffSalary.total + 
                                              targetRebate.total +
                                              targetUtility.total +
                                              targetAcademic.total +
                                              targetEmergencyFund.total) - ((targetIncome.total - ((targetIncome.total/1.12)*0.12)) - (targetProgram.total + 
                                              targetVenueRental.total + 
                                              targetFreebie.total +
                                              targetReview.total +
                                              targetFood.total +
                                              targetTransportation.total +
                                              targetStaffSalary.total + 
                                              targetRebate.total +
                                              targetUtility.total +
                                              targetAcademic.total +
                                              targetEmergencyFund.total)) * accounting_target_royalty_fee.percentage), 0) as netIncome'
                                ])

                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin('accounting_target_royalty_fee','accounting_target_royalty_fee.season_id = accounting_season.id')
                                ->leftJoin(['targetAcademic' => '('.$targetAcademicSeason.')'],'targetAcademic.id = accounting_season.id')
                                ->leftJoin(['targetEmergencyFund' => '('.$targetEmergencyFundSeason.')'],'targetEmergencyFund.id = accounting_season.id')
                                ->leftJoin(['targetFood' => '('.$targetFoodSeason.')'],'targetFood.id = accounting_season.id')
                                ->leftJoin(['targetFreebie' => '('.$targetFreebieSeason.')'],'targetFreebie.id = accounting_season.id')
                                ->leftJoin(['targetIncome' => '('.$targetIncomeSeason.')'],'targetIncome.id = accounting_season.id')
                                ->leftJoin(['targetProgram' => '('.$targetProgramSeason.')'],'targetProgram.id = accounting_season.id')
                                ->leftJoin(['targetRebate' => '('.$targetRebateSeason.')'],'targetRebate.id = accounting_season.id')
                                ->leftJoin(['targetReview' => '('.$targetReviewSeason.')'],'targetReview.id = accounting_season.id')
                                ->leftJoin(['targetStaffSalary' => '('.$targetStaffSalarySeason.')'],'targetStaffSalary.id = accounting_season.id')
                                ->leftJoin(['targetTransportation' => '('.$targetTransportationSeason.')'],'targetTransportation.id = accounting_season.id')
                                ->leftJoin(['targetUtility' => '('.$targetUtilitySeason.')'],'targetUtility.id = accounting_season.id')
                                ->leftJoin(['targetVenueRental' => '('.$targetVenueRentalSeason.')'],'targetVenueRental.id = accounting_season.id')
                                ->createCommand()
                                ->getRawSql();

                $auditTargetBranch = BranchProgram::find()
                                    ->select([
                                        'accounting_branch_program.id as id',
                                        'sum(COALESCE(targetSeason.totalGross, 0)) as totalGross',
                                        'sum(COALESCE(targetSeason.totalGrossIncome, 0)) as totalGrossIncome',
                                        'sum(COALESCE(targetSeason.totalExpenses, 0)) as totalExpenses',
                                        'sum(COALESCE(targetSeason.expectedIncome, 0)) as expectedIncome',
                                        'sum(COALESCE(targetSeason.netIncome, 0)) as netIncome',
                                    ])
                                    ->leftJoin(['targetSeason' => '('.$auditTargetSeason.')'],'targetSeason.branch_program_id = accounting_branch_program.id')
                                    ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                    ->groupBy(['targetSeason.branch_program_id'])
                                    ->createCommand()                              
                                    ->getRawSql();

                $auditSummary = $access ? $access->branch_program_id != '' ? BranchProgram::find()
                                ->select([
                                    'accounting_branch_program.id as id',
                                    'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal',
                                    'targetBranch.totalGross as targetGross',
                                    'targetBranch.totalGrossIncome as targetGrossIncome',
                                    'targetBranch.totalExpenses as targetExpenses',
                                    'targetBranch.expectedIncome as targetExpectedIncome',
                                    'targetBranch.netIncome as targetNetIncome',
                                ])
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolments.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebies.')'],'finalFreebies.id = accounting_branch_program.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposals.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpenses.')'],'finalPettyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpenses.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpenses.')'],'finalOtherExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDeposits.')'],'finalBankDeposits.id = accounting_branch_program.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpenses.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfers.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                                ->leftJoin(['targetBranch' => '('.$auditTargetBranch.')'],'targetBranch.id = accounting_branch_program.id')
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : BranchProgram::find()
                                ->select([
                                    'accounting_branch_program.id as id',
                                    'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal',
                                    'targetBranch.totalGross as targetGross',
                                    'targetBranch.totalGrossIncome as targetGrossIncome',
                                    'targetBranch.totalExpenses as targetExpenses',
                                    'targetBranch.expectedIncome as targetExpectedIncome',
                                    'targetBranch.netIncome as targetNetIncome',
                                ])
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolments.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebies.')'],'finalFreebies.id = accounting_branch_program.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposals.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpenses.')'],'finalPettyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpenses.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpenses.')'],'finalOtherExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDeposits.')'],'finalBankDeposits.id = accounting_branch_program.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpenses.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfers.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                                ->leftJoin(['targetBranch' => '('.$auditTargetBranch.')'],'targetBranch.id = accounting_branch_program.id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : BranchProgram::find()
                                ->select([
                                    'accounting_branch_program.id as id',
                                    'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal',
                                    'targetBranch.totalGross as targetGross',
                                    'targetBranch.totalGrossIncome as targetGrossIncome',
                                    'targetBranch.totalExpenses as targetExpenses',
                                    'targetBranch.expectedIncome as targetExpectedIncome',
                                    'targetBranch.netIncome as targetNetIncome',
                                ])
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolments.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebies.')'],'finalFreebies.id = accounting_branch_program.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposals.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpenses.')'],'finalPettyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpenses.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpenses.')'],'finalOtherExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDeposits.')'],'finalBankDeposits.id = accounting_branch_program.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpenses.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfers.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                                ->leftJoin(['targetBranch' => '('.$auditTargetBranch.')'],'targetBranch.id = accounting_branch_program.id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all();

                $auditSummarySeason = $access ? $access->branch_program_id != '' ? Season::find()
                                ->select([
                                    'accounting_season.id as id',
                                    'accounting_season.branch_program_id as branch_program_id',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal',
                                    'COALESCE(targets.totalGross, 0) as targetGross',
                                    'COALESCE(targets.totalGrossIncome, 0) as targetGrossIncome',
                                    'COALESCE(targets.totalExpenses, 0) as targetExpenses',
                                    'COALESCE(targets.expectedIncome, 0) as targetExpectedIncome',
                                    'COALESCE(targets.netIncome, 0) as targetNetIncome',
                                ])
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesSeason.')'],'finalFreebies.id = accounting_season.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesSeason.')'],'finalPettyExpense.id = accounting_season.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesSeason.')'],'finalOtherExpense.id = accounting_season.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsSeason.')'],'finalBankDeposits.id = accounting_season.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                                ->leftJoin(['targets' => '('.$auditTargetSeason.')'],'targets.id = accounting_season.id')
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : Season::find()
                                ->select([
                                    'accounting_season.id as id',
                                    'accounting_season.branch_program_id as branch_program_id',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal',
                                    'COALESCE(targets.totalGross, 0) as targetGross',
                                    'COALESCE(targets.totalGrossIncome, 0) as targetGrossIncome',
                                    'COALESCE(targets.totalExpenses, 0) as targetExpenses',
                                    'COALESCE(targets.expectedIncome, 0) as targetExpectedIncome',
                                    'COALESCE(targets.netIncome, 0) as targetNetIncome',
                                ])
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesSeason.')'],'finalFreebies.id = accounting_season.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesSeason.')'],'finalPettyExpense.id = accounting_season.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesSeason.')'],'finalOtherExpense.id = accounting_season.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsSeason.')'],'finalBankDeposits.id = accounting_season.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                                ->leftJoin(['targets' => '('.$auditTargetSeason.')'],'targets.id = accounting_season.id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : Season::find()
                                ->select([
                                    'accounting_season.id as id',
                                    'accounting_season.branch_program_id as branch_program_id',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal',
                                    'COALESCE(targets.totalGross, 0) as targetGross',
                                    'COALESCE(targets.totalGrossIncome, 0) as targetGrossIncome',
                                    'COALESCE(targets.totalExpenses, 0) as targetExpenses',
                                    'COALESCE(targets.expectedIncome, 0) as targetExpectedIncome',
                                    'COALESCE(targets.netIncome, 0) as targetNetIncome',
                                ])
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesSeason.')'],'finalFreebies.id = accounting_season.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesSeason.')'],'finalPettyExpense.id = accounting_season.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesSeason.')'],'finalOtherExpense.id = accounting_season.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsSeason.')'],'finalBankDeposits.id = accounting_season.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                                ->leftJoin(['targets' => '('.$auditTargetSeason.')'],'targets.id = accounting_season.id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all();
            }else{
                $incomeEnrolments = $this->getOverallIncomeEnrolmentByBranchProgramRawSql();

                $incomeEnrolmentsSeason = $this->getOverallIncomeEnrolmentBySeasonRawSql();

                $incomeFreebies = $this->getOverallIncomeFreebieByBranchProgramRawSql();

                $incomeFreebiesSeason = $this->getOverallIncomeFreebieBySeasonRawSql();

                $pettyExpenses = $this->getOverallPettyExpenseByBranchProgramRawSql();

                $pettyExpensesSeason = $this->getOverallPettyExpenseBySeasonRawSql();

                $photocopyExpenses = $this->getOverallPhotocopyExpenseByBranchProgramRawSql();

                $photocopyExpensesSeason = $this->getOverallPhotocopyExpenseBySeasonRawSql();

                $otherExpenses = $this->getOverallOtherExpenseByBranchProgramRawSql();

                $otherExpensesSeason = $this->getOverallOtherExpenseBySeasonRawSql();

                $bankDeposits = $this->getOverallBankDepositByBranchProgramRawSql();

                $bankDepositsSeason = $this->getOverallBankDepositBySeasonRawSql();

                $operatingExpenses = $this->getOverallOperatingExpenseByBranchProgramRawSql();

                $operatingExpensesSeason = $this->getOverallOperatingExpenseBySeasonRawSql();

                $budgetProposals = $this->getOverallBudgetProposalByBranchProgramRawSql();

                $budgetProposalsSeason = $this->getOverallBudgetProposalBySeasonRawSql();

                $branchTransfers = $this->getOverallBranchTransferByBranchProgramRawSql();

                $branchTransfersSeason = $this->getOverallBranchTransferBySeasonRawSql();

                $beginningCoh = $this->getBeginningCashByBranchProgram();

                $beginningCohSeason = $this->getBeginningCashBySeason();

                $auditTargetSeason = Season::find()
                                ->select([
                                    'accounting_season.id as id',
                                    'accounting_season.branch_program_id as branch_program_id',
                                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                    'COALESCE(targetIncome.total, 0) as totalGross',
                                    'COALESCE((targetIncome.total - ((targetIncome.total/1.12)*0.12)), 0) as totalGrossIncome',
                                    'COALESCE(targetProgram.total + 
                                              targetVenueRental.total + 
                                              targetFreebie.total +
                                              targetReview.total +
                                              targetFood.total +
                                              targetTransportation.total +
                                              targetStaffSalary.total + 
                                              targetRebate.total +
                                              targetUtility.total +
                                              targetAcademic.total +
                                              targetEmergencyFund.total
                                              , 0) as totalExpenses',
                                    'COALESCE((targetIncome.total - ((targetIncome.total/1.12)*0.12)) - (targetProgram.total + 
                                              targetVenueRental.total + 
                                              targetFreebie.total +
                                              targetReview.total +
                                              targetFood.total +
                                              targetTransportation.total +
                                              targetStaffSalary.total + 
                                              targetRebate.total +
                                              targetUtility.total +
                                              targetAcademic.total +
                                              targetEmergencyFund.total), 0) as expectedIncome',
                                    'COALESCE(((targetIncome.total - ((targetIncome.total/1.12)*0.12)) - (targetProgram.total + 
                                              targetVenueRental.total + 
                                              targetFreebie.total +
                                              targetReview.total +
                                              targetFood.total +
                                              targetTransportation.total +
                                              targetStaffSalary.total + 
                                              targetRebate.total +
                                              targetUtility.total +
                                              targetAcademic.total +
                                              targetEmergencyFund.total) - ((targetIncome.total - ((targetIncome.total/1.12)*0.12)) - (targetProgram.total + 
                                              targetVenueRental.total + 
                                              targetFreebie.total +
                                              targetReview.total +
                                              targetFood.total +
                                              targetTransportation.total +
                                              targetStaffSalary.total + 
                                              targetRebate.total +
                                              targetUtility.total +
                                              targetAcademic.total +
                                              targetEmergencyFund.total)) * accounting_target_royalty_fee.percentage), 0) as netIncome'
                                ])

                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin('accounting_target_royalty_fee','accounting_target_royalty_fee.season_id = accounting_season.id')
                                ->leftJoin(['targetAcademic' => '('.$targetAcademicSeason.')'],'targetAcademic.id = accounting_season.id')
                                ->leftJoin(['targetEmergencyFund' => '('.$targetEmergencyFundSeason.')'],'targetEmergencyFund.id = accounting_season.id')
                                ->leftJoin(['targetFood' => '('.$targetFoodSeason.')'],'targetFood.id = accounting_season.id')
                                ->leftJoin(['targetFreebie' => '('.$targetFreebieSeason.')'],'targetFreebie.id = accounting_season.id')
                                ->leftJoin(['targetIncome' => '('.$targetIncomeSeason.')'],'targetIncome.id = accounting_season.id')
                                ->leftJoin(['targetProgram' => '('.$targetProgramSeason.')'],'targetProgram.id = accounting_season.id')
                                ->leftJoin(['targetRebate' => '('.$targetRebateSeason.')'],'targetRebate.id = accounting_season.id')
                                ->leftJoin(['targetReview' => '('.$targetReviewSeason.')'],'targetReview.id = accounting_season.id')
                                ->leftJoin(['targetStaffSalary' => '('.$targetStaffSalarySeason.')'],'targetStaffSalary.id = accounting_season.id')
                                ->leftJoin(['targetTransportation' => '('.$targetTransportationSeason.')'],'targetTransportation.id = accounting_season.id')
                                ->leftJoin(['targetUtility' => '('.$targetUtilitySeason.')'],'targetUtility.id = accounting_season.id')
                                ->leftJoin(['targetVenueRental' => '('.$targetVenueRentalSeason.')'],'targetVenueRental.id = accounting_season.id')
                                ->createCommand()
                                ->getRawSql();

                $auditTargetBranch = BranchProgram::find()
                                    ->select([
                                        'accounting_branch_program.id as id',
                                        'sum(COALESCE(targetSeason.totalGross, 0)) as totalGross',
                                        'sum(COALESCE(targetSeason.totalGrossIncome, 0)) as totalGrossIncome',
                                        'sum(COALESCE(targetSeason.totalExpenses, 0)) as totalExpenses',
                                        'sum(COALESCE(targetSeason.expectedIncome, 0)) as expectedIncome',
                                        'sum(COALESCE(targetSeason.netIncome, 0)) as netIncome',
                                    ])
                                    ->leftJoin(['targetSeason' => '('.$auditTargetSeason.')'],'targetSeason.branch_program_id = accounting_branch_program.id')
                                    ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                    ->groupBy(['targetSeason.branch_program_id'])
                                    ->createCommand()                              
                                    ->getRawSql();

                $auditSummary = $access ? $access->branch_program_id != '' ? BranchProgram::find()
                                ->select([
                                    'accounting_branch_program.id as id',
                                    'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal',
                                    'targetBranch.totalGross as targetGross',
                                    'targetBranch.totalGrossIncome as targetGrossIncome',
                                    'targetBranch.totalExpenses as targetExpenses',
                                    'targetBranch.expectedIncome as targetExpectedIncome',
                                    'targetBranch.netIncome as targetNetIncome',
                                ])
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolments.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebies.')'],'finalFreebies.id = accounting_branch_program.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposals.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpenses.')'],'finalPettyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpenses.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpenses.')'],'finalOtherExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDeposits.')'],'finalBankDeposits.id = accounting_branch_program.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpenses.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfers.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                                ->leftJoin(['targetBranch' => '('.$auditTargetBranch.')'],'targetBranch.id = accounting_branch_program.id')
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : BranchProgram::find()
                                ->select([
                                    'accounting_branch_program.id as id',
                                    'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal',
                                    'targetBranch.totalGross as targetGross',
                                    'targetBranch.totalGrossIncome as targetGrossIncome',
                                    'targetBranch.totalExpenses as targetExpenses',
                                    'targetBranch.expectedIncome as targetExpectedIncome',
                                    'targetBranch.netIncome as targetNetIncome',
                                ])
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolments.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebies.')'],'finalFreebies.id = accounting_branch_program.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposals.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpenses.')'],'finalPettyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpenses.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpenses.')'],'finalOtherExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDeposits.')'],'finalBankDeposits.id = accounting_branch_program.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpenses.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfers.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                                ->leftJoin(['targetBranch' => '('.$auditTargetBranch.')'],'targetBranch.id = accounting_branch_program.id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : BranchProgram::find()
                                ->select([
                                    'accounting_branch_program.id as id',
                                    'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal',
                                    'targetBranch.totalGross as targetGross',
                                    'targetBranch.totalGrossIncome as targetGrossIncome',
                                    'targetBranch.totalExpenses as targetExpenses',
                                    'targetBranch.expectedIncome as targetExpectedIncome',
                                    'targetBranch.netIncome as targetNetIncome',
                                ])
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolments.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebies.')'],'finalFreebies.id = accounting_branch_program.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposals.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpenses.')'],'finalPettyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpenses.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpenses.')'],'finalOtherExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDeposits.')'],'finalBankDeposits.id = accounting_branch_program.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpenses.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfers.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                                ->leftJoin(['targetBranch' => '('.$auditTargetBranch.')'],'targetBranch.id = accounting_branch_program.id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all();

                $auditSummarySeason = $access ? $access->branch_program_id != '' ? Season::find()
                                ->select([
                                    'accounting_season.id as id',
                                    'accounting_season.branch_program_id as branch_program_id',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal',
                                    'COALESCE(targets.totalGross, 0) as targetGross',
                                    'COALESCE(targets.totalGrossIncome, 0) as targetGrossIncome',
                                    'COALESCE(targets.totalExpenses, 0) as targetExpenses',
                                    'COALESCE(targets.expectedIncome, 0) as targetExpectedIncome',
                                    'COALESCE(targets.netIncome, 0) as targetNetIncome',
                                ])
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesSeason.')'],'finalFreebies.id = accounting_season.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesSeason.')'],'finalPettyExpense.id = accounting_season.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesSeason.')'],'finalOtherExpense.id = accounting_season.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsSeason.')'],'finalBankDeposits.id = accounting_season.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                                ->leftJoin(['targets' => '('.$auditTargetSeason.')'],'targets.id = accounting_season.id')
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : Season::find()
                                ->select([
                                    'accounting_season.id as id',
                                    'accounting_season.branch_program_id as branch_program_id',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal',
                                    'COALESCE(targets.totalGross, 0) as targetGross',
                                    'COALESCE(targets.totalGrossIncome, 0) as targetGrossIncome',
                                    'COALESCE(targets.totalExpenses, 0) as targetExpenses',
                                    'COALESCE(targets.expectedIncome, 0) as targetExpectedIncome',
                                    'COALESCE(targets.netIncome, 0) as targetNetIncome',
                                ])
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesSeason.')'],'finalFreebies.id = accounting_season.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesSeason.')'],'finalPettyExpense.id = accounting_season.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesSeason.')'],'finalOtherExpense.id = accounting_season.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsSeason.')'],'finalBankDeposits.id = accounting_season.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                                ->leftJoin(['targets' => '('.$auditTargetSeason.')'],'targets.id = accounting_season.id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : Season::find()
                                ->select([
                                    'accounting_season.id as id',
                                    'accounting_season.branch_program_id as branch_program_id',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal',
                                    'COALESCE(targets.totalGross, 0) as targetGross',
                                    'COALESCE(targets.totalGrossIncome, 0) as targetGrossIncome',
                                    'COALESCE(targets.totalExpenses, 0) as targetExpenses',
                                    'COALESCE(targets.expectedIncome, 0) as targetExpectedIncome',
                                    'COALESCE(targets.netIncome, 0) as targetNetIncome',
                                ])
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesSeason.')'],'finalFreebies.id = accounting_season.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesSeason.')'],'finalPettyExpense.id = accounting_season.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesSeason.')'],'finalOtherExpense.id = accounting_season.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsSeason.')'],'finalBankDeposits.id = accounting_season.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                                ->leftJoin(['targets' => '('.$auditTargetSeason.')'],'targets.id = accounting_season.id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all();
            }            
        }

        $content = $this->renderPartial('_audit_summary_overall', [
                        'date' => $date,
                        'auditSummary' => $auditSummary,
                        'auditSummarySeason' => $auditSummarySeason,
                    ]);

        $title = empty($date) ? 'Audit Summary Report: Overall' : 'Audit Summary Report: '.$date[0][0].' - '.$date[0][1].' - Overall';

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
            'SetHeader'=> [$title], 
            'SetFooter'=>['Page {PAGENO}'],
        ]
        ]);
        $response = Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_RAW;
        $headers = Yii::$app->response->headers;
        $headers->add('Content-Type', 'application/pdf');
        return $pdf->render();
    }

    public function actionGenerateAuditCash($date = null)
    {
        $date = json_decode($date, true);

        $user_info = Yii::$app->user->identity->userinfo;
        $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
        $rolenames =  ArrayHelper::map($roles, 'name','name');

        $access = AccessProgram::findOne(['user_id' => Yii::$app->user->identity->userinfo->user_id]) ? AccessProgram::findOne(['user_id' => Yii::$app->user->identity->userinfo->user_id]) : new AccessProgram();

        if(in_array('TopManagement',$rolenames)){

            if(!empty($date))
            {
                $beginningCoh = $this->getBeginningCash($date[0][0], $date[0][1]);

                $incomeEnrolmentsCash = $this->getIncomeEnrolmentByBranchProgramRawSql($date[0][0], $date[0][1], 'Cash');

                $incomeEnrolmentsCashSeason = $this->getIncomeEnrolmentBySeasonRawSql($date[0][0], $date[0][1], 'Cash');

                $incomeFreebiesCash = $this->getIncomeFreebieByBranchProgramRawSql($date[0][0], $date[0][1], 'Cash');

                $incomeFreebiesCashSeason = $this->getIncomeFreebieBySeasonRawSql($date[0][0], $date[0][1], 'Cash');

                $pettyExpensesCash = $this->getPettyExpenseByBranchProgramRawSql($date[0][0], $date[0][1], 'Cash');

                $pettyExpensesCashSeason = $this->getPettyExpenseBySeasonRawSql($date[0][0], $date[0][1], 'Cash');

                $photocopyExpensesCash = $this->getPhotocopyExpenseByBranchProgramRawSql($date[0][0], $date[0][1], 'Cash');

                $photocopyExpensesCashSeason = $this->getPhotocopyExpenseBySeasonRawSql($date[0][0], $date[0][1], 'Cash');

                $otherExpensesCash = $this->getOtherExpenseByBranchProgramRawSql($date[0][0], $date[0][1], 'Cash');

                $otherExpensesCashSeason = $this->getOtherExpenseBySeasonRawSql($date[0][0], $date[0][1], 'Cash');

                $bankDepositsCash = $this->getBankDepositByBranchProgramRawSql($date[0][0], $date[0][1], 'Cash');

                $bankDepositsCashSeason = $this->getBankDepositBySeasonRawSql($date[0][0], $date[0][1], 'Cash');

                $operatingExpensesCash = $this->getOperatingExpenseByBranchProgramRawSql($date[0][0], $date[0][1], 'Cash');

                $operatingExpensesCashSeason = $this->getOperatingExpenseBySeasonRawSql($date[0][0], $date[0][1], 'Cash');

                $budgetProposalsCash = $this->getBudgetProposalByBranchProgramRawSql($date[0][0], $date[0][1], 'Cash');

                $budgetProposalsCashSeason = $this->getBudgetProposalBySeasonRawSql($date[0][0], $date[0][1], 'Cash');

                $branchTransfersCash = $this->getBranchTransferByBranchProgramRawSql($date[0][0], $date[0][1], 'Cash');

                $branchTransfersCashSeason = $this->getBranchTransferBySeasonRawSql($date[0][0], $date[0][1], 'Cash');

                $auditSummaryCash = $access ? $access->branch_program_id != '' ? BranchProgram::find()
                                ->select([
                                    'accounting_branch_program.id as id',
                                    'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsCash.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesCash.')'],'finalFreebies.id = accounting_branch_program.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsCash.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesCash.')'],'finalPettyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesCash.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesCash.')'],'finalOtherExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsCash.')'],'finalBankDeposits.id = accounting_branch_program.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesCash.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersCash.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : BranchProgram::find()
                                ->select([
                                    'accounting_branch_program.id as id',
                                    'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsCash.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesCash.')'],'finalFreebies.id = accounting_branch_program.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsCash.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesCash.')'],'finalPettyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesCash.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesCash.')'],'finalOtherExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsCash.')'],'finalBankDeposits.id = accounting_branch_program.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesCash.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersCash.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : BranchProgram::find()
                                ->select([
                                    'accounting_branch_program.id as id',
                                    'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsCash.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesCash.')'],'finalFreebies.id = accounting_branch_program.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsCash.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesCash.')'],'finalPettyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesCash.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesCash.')'],'finalOtherExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsCash.')'],'finalBankDeposits.id = accounting_branch_program.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesCash.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersCash.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all();

                $auditSummaryCashSeason = $access ? $access->branch_program_id != '' ? Season::find()
                                ->select([
                                    'accounting_season.id as id',
                                    'accounting_season.branch_program_id as branch_program_id',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsCashSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesCashSeason.')'],'finalFreebies.id = accounting_season.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesCashSeason.')'],'finalPettyExpense.id = accounting_season.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesCashSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesCashSeason.')'],'finalOtherExpense.id = accounting_season.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsCashSeason.')'],'finalBankDeposits.id = accounting_season.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesCashSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsCashSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersCashSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : Season::find()
                                ->select([
                                    'accounting_season.id as id',
                                    'accounting_season.branch_program_id as branch_program_id',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsCashSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesCashSeason.')'],'finalFreebies.id = accounting_season.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesCashSeason.')'],'finalPettyExpense.id = accounting_season.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesCashSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesCashSeason.')'],'finalOtherExpense.id = accounting_season.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsCashSeason.')'],'finalBankDeposits.id = accounting_season.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesCashSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsCashSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersCashSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : Season::find()
                                ->select([
                                    'accounting_season.id as id',
                                    'accounting_season.branch_program_id as branch_program_id',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsCashSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesCashSeason.')'],'finalFreebies.id = accounting_season.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesCashSeason.')'],'finalPettyExpense.id = accounting_season.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesCashSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesCashSeason.')'],'finalOtherExpense.id = accounting_season.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsCashSeason.')'],'finalBankDeposits.id = accounting_season.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesCashSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsCashSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersCashSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all();

            }else{
                $beginningCoh = $this->getBeginningCash();

                $incomeEnrolmentsCash = $this->getIncomeEnrolmentByBranchProgramRawSql(null, null, 'Cash');

                $incomeEnrolmentsCashSeason = $this->getIncomeEnrolmentBySeasonRawSql(null, null, 'Cash');

                $incomeFreebiesCash = $this->getIncomeFreebieByBranchProgramRawSql(null, null, 'Cash');

                $incomeFreebiesCashSeason = $this->getIncomeFreebieBySeasonRawSql(null, null, 'Cash');

                $pettyExpensesCash = $this->getPettyExpenseByBranchProgramRawSql(null, null, 'Cash');

                $pettyExpensesCashSeason = $this->getPettyExpenseBySeasonRawSql(null, null, 'Cash');

                $photocopyExpensesCash = $this->getPhotocopyExpenseByBranchProgramRawSql(null, null, 'Cash');

                $photocopyExpensesCashSeason = $this->getPhotocopyExpenseBySeasonRawSql(null, null, 'Cash');

                $otherExpensesCash = $this->getOtherExpenseByBranchProgramRawSql(null, null, 'Cash');

                $otherExpensesCashSeason = $this->getOtherExpenseBySeasonRawSql(null, null, 'Cash');

                $bankDepositsCash = $this->getBankDepositByBranchProgramRawSql(null, null, 'Cash');

                $bankDepositsCashSeason = $this->getBankDepositBySeasonRawSql(null, null, 'Cash');

                $operatingExpensesCash = $this->getOperatingExpenseByBranchProgramRawSql(null, null, 'Cash');

                $operatingExpensesCashSeason = $this->getOperatingExpenseBySeasonRawSql(null, null, 'Cash');

                $budgetProposalsCash = $this->getBudgetProposalByBranchProgramRawSql(null, null, 'Cash');

                $budgetProposalsCashSeason = $this->getBudgetProposalBySeasonRawSql(null, null, 'Cash');

                $branchTransfersCash = $this->getBranchTransferByBranchProgramRawSql(null, null, 'Cash');

                $branchTransfersCashSeason = $this->getBranchTransferBySeasonRawSql(null, null, 'Cash');

                $auditSummaryCash = $access ? $access->branch_program_id != '' ? BranchProgram::find()
                                ->select([
                                    'accounting_branch_program.id as id',
                                    'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsCash.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesCash.')'],'finalFreebies.id = accounting_branch_program.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsCash.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesCash.')'],'finalPettyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesCash.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesCash.')'],'finalOtherExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsCash.')'],'finalBankDeposits.id = accounting_branch_program.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesCash.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersCash.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : BranchProgram::find()
                                ->select([
                                    'accounting_branch_program.id as id',
                                    'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsCash.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesCash.')'],'finalFreebies.id = accounting_branch_program.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsCash.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesCash.')'],'finalPettyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesCash.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesCash.')'],'finalOtherExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsCash.')'],'finalBankDeposits.id = accounting_branch_program.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesCash.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersCash.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : BranchProgram::find()
                                ->select([
                                    'accounting_branch_program.id as id',
                                    'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsCash.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesCash.')'],'finalFreebies.id = accounting_branch_program.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsCash.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesCash.')'],'finalPettyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesCash.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesCash.')'],'finalOtherExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsCash.')'],'finalBankDeposits.id = accounting_branch_program.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesCash.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersCash.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all();

                $auditSummaryCashSeason = $access ? $access->branch_program_id != '' ? Season::find()
                                ->select([
                                    'accounting_season.id as id',
                                    'accounting_season.branch_program_id as branch_program_id',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsCashSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesCashSeason.')'],'finalFreebies.id = accounting_season.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesCashSeason.')'],'finalPettyExpense.id = accounting_season.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesCashSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesCashSeason.')'],'finalOtherExpense.id = accounting_season.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsCashSeason.')'],'finalBankDeposits.id = accounting_season.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesCashSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsCashSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersCashSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : Season::find()
                                ->select([
                                    'accounting_season.id as id',
                                    'accounting_season.branch_program_id as branch_program_id',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsCashSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesCashSeason.')'],'finalFreebies.id = accounting_season.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesCashSeason.')'],'finalPettyExpense.id = accounting_season.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesCashSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesCashSeason.')'],'finalOtherExpense.id = accounting_season.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsCashSeason.')'],'finalBankDeposits.id = accounting_season.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesCashSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsCashSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersCashSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : Season::find()
                                ->select([
                                    'accounting_season.id as id',
                                    'accounting_season.branch_program_id as branch_program_id',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsCashSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesCashSeason.')'],'finalFreebies.id = accounting_season.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesCashSeason.')'],'finalPettyExpense.id = accounting_season.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesCashSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesCashSeason.')'],'finalOtherExpense.id = accounting_season.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsCashSeason.')'],'finalBankDeposits.id = accounting_season.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesCashSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsCashSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersCashSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all();
            } 
        }else{
            if(!empty($date))
            {
                $beginningCoh = $this->getBeginningCash($date[0][0], $date[0][1]);

                $incomeEnrolmentsCash = $this->getIncomeEnrolmentByBranchProgramRawSql($date[0][0], $date[0][1], 'Cash');

                $incomeEnrolmentsCashSeason = $this->getIncomeEnrolmentBySeasonRawSql($date[0][0], $date[0][1], 'Cash');

                $incomeFreebiesCash = $this->getIncomeFreebieByBranchProgramRawSql($date[0][0], $date[0][1], 'Cash');

                $incomeFreebiesCashSeason = $this->getIncomeFreebieBySeasonRawSql($date[0][0], $date[0][1], 'Cash');

                $pettyExpensesCash = $this->getPettyExpenseByBranchProgramRawSql($date[0][0], $date[0][1], 'Cash');

                $pettyExpensesCashSeason = $this->getPettyExpenseBySeasonRawSql($date[0][0], $date[0][1], 'Cash');

                $photocopyExpensesCash = $this->getPhotocopyExpenseByBranchProgramRawSql($date[0][0], $date[0][1], 'Cash');

                $photocopyExpensesCashSeason = $this->getPhotocopyExpenseBySeasonRawSql($date[0][0], $date[0][1], 'Cash');

                $otherExpensesCash = $this->getOtherExpenseByBranchProgramRawSql($date[0][0], $date[0][1], 'Cash');

                $otherExpensesCashSeason = $this->getOtherExpenseBySeasonRawSql($date[0][0], $date[0][1], 'Cash');

                $bankDepositsCash = $this->getBankDepositByBranchProgramRawSql($date[0][0], $date[0][1], 'Cash');

                $bankDepositsCashSeason = $this->getBankDepositBySeasonRawSql($date[0][0], $date[0][1], 'Cash');

                $operatingExpensesCash = $this->getOperatingExpenseByBranchProgramRawSql($date[0][0], $date[0][1], 'Cash');

                $operatingExpensesCashSeason = $this->getOperatingExpenseBySeasonRawSql($date[0][0], $date[0][1], 'Cash');

                $budgetProposalsCash = $this->getBudgetProposalByBranchProgramRawSql($date[0][0], $date[0][1], 'Cash');

                $budgetProposalsCashSeason = $this->getBudgetProposalBySeasonRawSql($date[0][0], $date[0][1], 'Cash');

                $branchTransfersCash = $this->getBranchTransferByBranchProgramRawSql($date[0][0], $date[0][1], 'Cash');

                $branchTransfersCashSeason = $this->getBranchTransferBySeasonRawSql($date[0][0], $date[0][1], 'Cash');

                $auditSummaryCash = $access ? $access->branch_program_id != '' ? BranchProgram::find()
                                ->select([
                                    'accounting_branch_program.id as id',
                                    'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsCash.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesCash.')'],'finalFreebies.id = accounting_branch_program.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsCash.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesCash.')'],'finalPettyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesCash.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesCash.')'],'finalOtherExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsCash.')'],'finalBankDeposits.id = accounting_branch_program.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesCash.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersCash.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : BranchProgram::find()
                                ->select([
                                    'accounting_branch_program.id as id',
                                    'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsCash.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesCash.')'],'finalFreebies.id = accounting_branch_program.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsCash.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesCash.')'],'finalPettyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesCash.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesCash.')'],'finalOtherExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsCash.')'],'finalBankDeposits.id = accounting_branch_program.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesCash.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersCash.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : BranchProgram::find()
                                ->select([
                                    'accounting_branch_program.id as id',
                                    'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsCash.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesCash.')'],'finalFreebies.id = accounting_branch_program.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsCash.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesCash.')'],'finalPettyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesCash.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesCash.')'],'finalOtherExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsCash.')'],'finalBankDeposits.id = accounting_branch_program.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesCash.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersCash.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all();

                $auditSummaryCashSeason = $access ? $access->branch_program_id != '' ? Season::find()
                                ->select([
                                    'accounting_season.id as id',
                                    'accounting_season.branch_program_id as branch_program_id',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsCashSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesCashSeason.')'],'finalFreebies.id = accounting_season.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesCashSeason.')'],'finalPettyExpense.id = accounting_season.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesCashSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesCashSeason.')'],'finalOtherExpense.id = accounting_season.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsCashSeason.')'],'finalBankDeposits.id = accounting_season.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesCashSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsCashSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersCashSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : Season::find()
                                ->select([
                                    'accounting_season.id as id',
                                    'accounting_season.branch_program_id as branch_program_id',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsCashSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesCashSeason.')'],'finalFreebies.id = accounting_season.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesCashSeason.')'],'finalPettyExpense.id = accounting_season.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesCashSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesCashSeason.')'],'finalOtherExpense.id = accounting_season.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsCashSeason.')'],'finalBankDeposits.id = accounting_season.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesCashSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsCashSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersCashSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : Season::find()
                                ->select([
                                    'accounting_season.id as id',
                                    'accounting_season.branch_program_id as branch_program_id',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsCashSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesCashSeason.')'],'finalFreebies.id = accounting_season.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesCashSeason.')'],'finalPettyExpense.id = accounting_season.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesCashSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesCashSeason.')'],'finalOtherExpense.id = accounting_season.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsCashSeason.')'],'finalBankDeposits.id = accounting_season.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesCashSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsCashSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersCashSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all();

            }else{
                $beginningCoh = $this->getBeginningCash();

                $incomeEnrolmentsCash = $this->getIncomeEnrolmentByBranchProgramRawSql(null, null, 'Cash');

                $incomeEnrolmentsCashSeason = $this->getIncomeEnrolmentBySeasonRawSql(null, null, 'Cash');

                $incomeFreebiesCash = $this->getIncomeFreebieByBranchProgramRawSql(null, null, 'Cash');

                $incomeFreebiesCashSeason = $this->getIncomeFreebieBySeasonRawSql(null, null, 'Cash');

                $pettyExpensesCash = $this->getPettyExpenseByBranchProgramRawSql(null, null, 'Cash');

                $pettyExpensesCashSeason = $this->getPettyExpenseBySeasonRawSql(null, null, 'Cash');

                $photocopyExpensesCash = $this->getPhotocopyExpenseByBranchProgramRawSql(null, null, 'Cash');

                $photocopyExpensesCashSeason = $this->getPhotocopyExpenseBySeasonRawSql(null, null, 'Cash');

                $otherExpensesCash = $this->getOtherExpenseByBranchProgramRawSql(null, null, 'Cash');

                $otherExpensesCashSeason = $this->getOtherExpenseBySeasonRawSql(null, null, 'Cash');

                $bankDepositsCash = $this->getBankDepositByBranchProgramRawSql(null, null, 'Cash');

                $bankDepositsCashSeason = $this->getBankDepositBySeasonRawSql(null, null, 'Cash');

                $operatingExpensesCash = $this->getOperatingExpenseByBranchProgramRawSql(null, null, 'Cash');

                $operatingExpensesCashSeason = $this->getOperatingExpenseBySeasonRawSql(null, null, 'Cash');

                $budgetProposalsCash = $this->getBudgetProposalByBranchProgramRawSql(null, null, 'Cash');

                $budgetProposalsCashSeason = $this->getBudgetProposalBySeasonRawSql(null, null, 'Cash');

                $branchTransfersCash = $this->getBranchTransferByBranchProgramRawSql(null, null, 'Cash');

                $branchTransfersCashSeason = $this->getBranchTransferBySeasonRawSql(null, null, 'Cash');

                $auditSummaryCash = $access ? $access->branch_program_id != '' ? BranchProgram::find()
                                ->select([
                                    'accounting_branch_program.id as id',
                                    'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsCash.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesCash.')'],'finalFreebies.id = accounting_branch_program.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsCash.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesCash.')'],'finalPettyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesCash.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesCash.')'],'finalOtherExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsCash.')'],'finalBankDeposits.id = accounting_branch_program.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesCash.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersCash.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : BranchProgram::find()
                                ->select([
                                    'accounting_branch_program.id as id',
                                    'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsCash.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesCash.')'],'finalFreebies.id = accounting_branch_program.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsCash.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesCash.')'],'finalPettyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesCash.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesCash.')'],'finalOtherExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsCash.')'],'finalBankDeposits.id = accounting_branch_program.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesCash.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersCash.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : BranchProgram::find()
                                ->select([
                                    'accounting_branch_program.id as id',
                                    'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsCash.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesCash.')'],'finalFreebies.id = accounting_branch_program.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsCash.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesCash.')'],'finalPettyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesCash.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesCash.')'],'finalOtherExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsCash.')'],'finalBankDeposits.id = accounting_branch_program.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesCash.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersCash.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all();

                $auditSummaryCashSeason = $access ? $access->branch_program_id != '' ? Season::find()
                                ->select([
                                    'accounting_season.id as id',
                                    'accounting_season.branch_program_id as branch_program_id',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsCashSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesCashSeason.')'],'finalFreebies.id = accounting_season.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesCashSeason.')'],'finalPettyExpense.id = accounting_season.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesCashSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesCashSeason.')'],'finalOtherExpense.id = accounting_season.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsCashSeason.')'],'finalBankDeposits.id = accounting_season.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesCashSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsCashSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersCashSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : Season::find()
                                ->select([
                                    'accounting_season.id as id',
                                    'accounting_season.branch_program_id as branch_program_id',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsCashSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesCashSeason.')'],'finalFreebies.id = accounting_season.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesCashSeason.')'],'finalPettyExpense.id = accounting_season.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesCashSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesCashSeason.')'],'finalOtherExpense.id = accounting_season.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsCashSeason.')'],'finalBankDeposits.id = accounting_season.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesCashSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsCashSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersCashSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : Season::find()
                                ->select([
                                    'accounting_season.id as id',
                                    'accounting_season.branch_program_id as branch_program_id',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.coh, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsCashSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesCashSeason.')'],'finalFreebies.id = accounting_season.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesCashSeason.')'],'finalPettyExpense.id = accounting_season.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesCashSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesCashSeason.')'],'finalOtherExpense.id = accounting_season.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsCashSeason.')'],'finalBankDeposits.id = accounting_season.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesCashSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsCashSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersCashSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all();
            }            
        }

        $content = $this->renderPartial('_audit_summary_cash', [
            'date' => $date,
            'auditSummaryCash' => $auditSummaryCash,
            'auditSummaryCashSeason' => $auditSummaryCashSeason,
        ]);

        $title = empty($date) ? 'Audit Summary Report: Overall' : 'Audit Summary Report: '.$date[0][0].' - '.$date[0][1].' - Cash';

        $pdf = new Pdf([
        'mode' => Pdf::MODE_CORE,
        'format' => Pdf::FORMAT_LEGAL, 
        'orientation' => Pdf::ORIENT_LANDSCAPE, 
        'destination' => Pdf::DEST_DOWNLOAD, 
        'filename' => $title.'.pdf',
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
        'options' => ['title' => $title],
        'methods' => [ 
            'SetHeader'=> [$title], 
            'SetFooter'=>['Page {PAGENO}'],
        ]
        ]);
        $response = Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_RAW;
        $headers = Yii::$app->response->headers;
        $headers->add('Content-Type', 'application/pdf');
        return $pdf->render();
    }

    public function actionGenerateAuditNonCash($date = null)
    {
        $date = json_decode($date, true);

        $user_info = Yii::$app->user->identity->userinfo;
        $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
        $rolenames =  ArrayHelper::map($roles, 'name','name');

        $access = AccessProgram::findOne(['user_id' => Yii::$app->user->identity->userinfo->user_id]) ? AccessProgram::findOne(['user_id' => Yii::$app->user->identity->userinfo->user_id]) : new AccessProgram();

        if(in_array('TopManagement',$rolenames)){

            if(!empty($date))
            {
                $beginningCoh = $this->getBeginningCash($date[0][0], $date[0][1]);

                $incomeEnrolmentsNonCash = $this->getIncomeEnrolmentByBranchProgramRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $incomeEnrolmentsNonCashSeason = $this->getIncomeEnrolmentBySeasonRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $incomeFreebiesNonCash = $this->getIncomeFreebieByBranchProgramRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $incomeFreebiesNonCashSeason = $this->getIncomeFreebieBySeasonRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $pettyExpensesNonCash = $this->getPettyExpenseByBranchProgramRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $pettyExpensesNonCashSeason = $this->getPettyExpenseBySeasonRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $photocopyExpensesNonCash = $this->getPhotocopyExpenseByBranchProgramRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $photocopyExpensesNonCashSeason = $this->getPhotocopyExpenseBySeasonRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $otherExpensesNonCash = $this->getOtherExpenseByBranchProgramRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $otherExpensesNonCashSeason = $this->getOtherExpenseBySeasonRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $bankDepositsNonCash = $this->getBankDepositByBranchProgramRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $bankDepositsNonCashSeason = $this->getBankDepositBySeasonRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $operatingExpensesNonCash = $this->getOperatingExpenseByBranchProgramRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $operatingExpensesNonCashSeason = $this->getOperatingExpenseBySeasonRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $budgetProposalsNonCash = $this->getBudgetProposalByBranchProgramRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $budgetProposalsNonCashSeason = $this->getBudgetProposalBySeasonRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $branchTransfersNonCash = $this->getBranchTransferByBranchProgramRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $branchTransfersNonCashSeason = $this->getBranchTransferBySeasonRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $auditSummaryNonCash = $access ? $access->branch_program_id != '' ? BranchProgram::find()
                            ->select([
                                'accounting_branch_program.id as id',
                                'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                'finalBeginningCoh.coh as beginningCoh',
                                'finalBeginningCoh.cob as beginningCob',
                                'finalBankDeposits.total as bankDepositsTotal',
                                '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                '(
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0) +
                                    COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                '((COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBankDeposits.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0) +
                                    COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal'
                            ])
                            ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                            ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                            ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsNonCash.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                            ->leftJoin(['finalFreebies' => '('.$incomeFreebiesNonCash.')'],'finalFreebies.id = accounting_branch_program.id')
                            ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsNonCash.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                            ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesNonCash.')'],'finalPettyExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesNonCash.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalOtherExpense' => '('.$otherExpensesNonCash.')'],'finalOtherExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalBankDeposits' => '('.$bankDepositsNonCash.')'],'finalBankDeposits.id = accounting_branch_program.id')
                            ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesNonCash.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                            ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersNonCash.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                            ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                            ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                            ->asArray()
                            ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                            ->all() : BranchProgram::find()
                            ->select([
                                'accounting_branch_program.id as id',
                                'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                'finalBeginningCoh.coh as beginningCoh',
                                'finalBeginningCoh.cob as beginningCob',
                                'finalBankDeposits.total as bankDepositsTotal',
                                '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                '(
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0) +
                                    COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                '((COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBankDeposits.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0) +
                                    COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal'
                            ])
                            ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                            ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                            ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsNonCash.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                            ->leftJoin(['finalFreebies' => '('.$incomeFreebiesNonCash.')'],'finalFreebies.id = accounting_branch_program.id')
                            ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsNonCash.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                            ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesNonCash.')'],'finalPettyExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesNonCash.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalOtherExpense' => '('.$otherExpensesNonCash.')'],'finalOtherExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalBankDeposits' => '('.$bankDepositsNonCash.')'],'finalBankDeposits.id = accounting_branch_program.id')
                            ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesNonCash.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                            ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersNonCash.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                            ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                            ->asArray()
                            ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                            ->all() : BranchProgram::find()
                            ->select([
                                'accounting_branch_program.id as id',
                                'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                'finalBeginningCoh.coh as beginningCoh',
                                'finalBeginningCoh.cob as beginningCob',
                                'finalBankDeposits.total as bankDepositsTotal',
                                '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                '(
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0) +
                                    COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                '((COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBankDeposits.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0) +
                                    COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal'
                            ])
                            ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                            ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                            ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsNonCash.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                            ->leftJoin(['finalFreebies' => '('.$incomeFreebiesNonCash.')'],'finalFreebies.id = accounting_branch_program.id')
                            ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsNonCash.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                            ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesNonCash.')'],'finalPettyExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesNonCash.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalOtherExpense' => '('.$otherExpensesNonCash.')'],'finalOtherExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalBankDeposits' => '('.$bankDepositsNonCash.')'],'finalBankDeposits.id = accounting_branch_program.id')
                            ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesNonCash.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                            ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersNonCash.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                            ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                            ->asArray()
                            ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                            ->all();

                $auditSummaryNonCashSeason = $access ? $access->branch_program_id != '' ? Season::find()
                            ->select([
                                'accounting_season.id as id',
                                'accounting_season.branch_program_id as branch_program_id',
                                'finalBeginningCoh.coh as beginningCoh',
                                'finalBeginningCoh.cob as beginningCob',
                                'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                'finalBankDeposits.total as bankDepositsTotal',
                                '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                '(
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBranchTransfers.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                '((COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBankDeposits.total, 0) + 
                                    COALESCE(finalBranchTransfers.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal'
                            ])
                            ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                            ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                            ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                            ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsNonCashSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                            ->leftJoin(['finalFreebies' => '('.$incomeFreebiesNonCashSeason.')'],'finalFreebies.id = accounting_season.id')
                            ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesNonCashSeason.')'],'finalPettyExpense.id = accounting_season.id')
                            ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesNonCashSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                            ->leftJoin(['finalOtherExpense' => '('.$otherExpensesNonCashSeason.')'],'finalOtherExpense.id = accounting_season.id')
                            ->leftJoin(['finalBankDeposits' => '('.$bankDepositsNonCashSeason.')'],'finalBankDeposits.id = accounting_season.id')
                            ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesNonCashSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                            ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsNonCashSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                            ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersNonCashSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                            ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                            ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                            ->asArray()
                            ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                            ->all() : Season::find()
                            ->select([
                                'accounting_season.id as id',
                                'accounting_season.branch_program_id as branch_program_id',
                                'finalBeginningCoh.coh as beginningCoh',
                                'finalBeginningCoh.cob as beginningCob',
                                'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                'finalBankDeposits.total as bankDepositsTotal',
                                '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                '(
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBranchTransfers.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                '((COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBankDeposits.total, 0) + 
                                    COALESCE(finalBranchTransfers.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal'
                            ])
                            ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                            ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                            ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                            ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsNonCashSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                            ->leftJoin(['finalFreebies' => '('.$incomeFreebiesNonCashSeason.')'],'finalFreebies.id = accounting_season.id')
                            ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesNonCashSeason.')'],'finalPettyExpense.id = accounting_season.id')
                            ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesNonCashSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                            ->leftJoin(['finalOtherExpense' => '('.$otherExpensesNonCashSeason.')'],'finalOtherExpense.id = accounting_season.id')
                            ->leftJoin(['finalBankDeposits' => '('.$bankDepositsNonCashSeason.')'],'finalBankDeposits.id = accounting_season.id')
                            ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesNonCashSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                            ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsNonCashSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                            ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersNonCashSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                            ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                            ->asArray()
                            ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                            ->all() : Season::find()
                            ->select([
                                'accounting_season.id as id',
                                'accounting_season.branch_program_id as branch_program_id',
                                'finalBeginningCoh.coh as beginningCoh',
                                'finalBeginningCoh.cob as beginningCob',
                                'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                'finalBankDeposits.total as bankDepositsTotal',
                                '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                '(
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBranchTransfers.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                '((COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBankDeposits.total, 0) + 
                                    COALESCE(finalBranchTransfers.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal'
                            ])
                            ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                            ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                            ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                            ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsNonCashSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                            ->leftJoin(['finalFreebies' => '('.$incomeFreebiesNonCashSeason.')'],'finalFreebies.id = accounting_season.id')
                            ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesNonCashSeason.')'],'finalPettyExpense.id = accounting_season.id')
                            ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesNonCashSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                            ->leftJoin(['finalOtherExpense' => '('.$otherExpensesNonCashSeason.')'],'finalOtherExpense.id = accounting_season.id')
                            ->leftJoin(['finalBankDeposits' => '('.$bankDepositsNonCashSeason.')'],'finalBankDeposits.id = accounting_season.id')
                            ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesNonCashSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                            ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsNonCashSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                            ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersNonCashSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                            ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                            ->asArray()
                            ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                            ->all();
            }else{

                $beginningCoh = $this->getBeginningCash();

                $incomeEnrolmentsNonCash = $this->getIncomeEnrolmentByBranchProgramRawSql(null, null, 'Non-Cash');

                $incomeEnrolmentsNonCashSeason = $this->getIncomeEnrolmentBySeasonRawSql(null, null, 'Non-Cash');

                $incomeFreebiesNonCash = $this->getIncomeFreebieByBranchProgramRawSql(null, null, 'Non-Cash');

                $incomeFreebiesNonCashSeason = $this->getIncomeFreebieBySeasonRawSql(null, null, 'Non-Cash');

                $pettyExpensesNonCash = $this->getPettyExpenseByBranchProgramRawSql(null, null, 'Non-Cash');

                $pettyExpensesNonCashSeason = $this->getPettyExpenseBySeasonRawSql(null, null, 'Non-Cash');

                $photocopyExpensesNonCash = $this->getPhotocopyExpenseByBranchProgramRawSql(null, null, 'Non-Cash');

                $photocopyExpensesNonCashSeason = $this->getPhotocopyExpenseBySeasonRawSql(null, null, 'Non-Cash');

                $otherExpensesNonCash = $this->getOtherExpenseByBranchProgramRawSql(null, null, 'Non-Cash');

                $otherExpensesNonCashSeason = $this->getOtherExpenseBySeasonRawSql(null, null, 'Non-Cash');

                $bankDepositsNonCash = $this->getBankDepositByBranchProgramRawSql(null, null, 'Non-Cash');

                $bankDepositsNonCashSeason = $this->getBankDepositBySeasonRawSql(null, null, 'Non-Cash');

                $operatingExpensesNonCash = $this->getOperatingExpenseByBranchProgramRawSql(null, null, 'Non-Cash');

                $operatingExpensesNonCashSeason = $this->getOperatingExpenseBySeasonRawSql(null, null, 'Non-Cash');

                $budgetProposalsNonCash = $this->getBudgetProposalByBranchProgramRawSql(null, null, 'Non-Cash');

                $budgetProposalsNonCashSeason = $this->getBudgetProposalBySeasonRawSql(null, null, 'Non-Cash');

                $branchTransfersNonCash = $this->getBranchTransferByBranchProgramRawSql(null, null, 'Non-Cash');

                $branchTransfersNonCashSeason = $this->getBranchTransferBySeasonRawSql(null, null, 'Non-Cash');

                $auditSummaryNonCash = $access ? $access->branch_program_id != '' ? BranchProgram::find()
                            ->select([
                                'accounting_branch_program.id as id',
                                'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                'finalBeginningCoh.coh as beginningCoh',
                                'finalBeginningCoh.cob as beginningCob',
                                'finalBankDeposits.total as bankDepositsTotal',
                                '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                '(
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0) +
                                    COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                '((COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBankDeposits.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0) +
                                    COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal'
                            ])
                            ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                            ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                            ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsNonCash.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                            ->leftJoin(['finalFreebies' => '('.$incomeFreebiesNonCash.')'],'finalFreebies.id = accounting_branch_program.id')
                            ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsNonCash.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                            ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesNonCash.')'],'finalPettyExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesNonCash.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalOtherExpense' => '('.$otherExpensesNonCash.')'],'finalOtherExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalBankDeposits' => '('.$bankDepositsNonCash.')'],'finalBankDeposits.id = accounting_branch_program.id')
                            ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesNonCash.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                            ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersNonCash.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                            ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                            ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                            ->asArray()
                            ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                            ->all() : BranchProgram::find()
                            ->select([
                                'accounting_branch_program.id as id',
                                'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                'finalBeginningCoh.coh as beginningCoh',
                                'finalBeginningCoh.cob as beginningCob',
                                'finalBankDeposits.total as bankDepositsTotal',
                                '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                '(
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0) +
                                    COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                '((COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBankDeposits.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0) +
                                    COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal'
                            ])
                            ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                            ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                            ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsNonCash.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                            ->leftJoin(['finalFreebies' => '('.$incomeFreebiesNonCash.')'],'finalFreebies.id = accounting_branch_program.id')
                            ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsNonCash.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                            ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesNonCash.')'],'finalPettyExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesNonCash.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalOtherExpense' => '('.$otherExpensesNonCash.')'],'finalOtherExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalBankDeposits' => '('.$bankDepositsNonCash.')'],'finalBankDeposits.id = accounting_branch_program.id')
                            ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesNonCash.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                            ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersNonCash.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                            ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                            ->asArray()
                            ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                            ->all() : BranchProgram::find()
                            ->select([
                                'accounting_branch_program.id as id',
                                'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                'finalBeginningCoh.coh as beginningCoh',
                                'finalBeginningCoh.cob as beginningCob',
                                'finalBankDeposits.total as bankDepositsTotal',
                                '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                '(
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0) +
                                    COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                '((COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBankDeposits.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0) +
                                    COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal'
                            ])
                            ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                            ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                            ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsNonCash.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                            ->leftJoin(['finalFreebies' => '('.$incomeFreebiesNonCash.')'],'finalFreebies.id = accounting_branch_program.id')
                            ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsNonCash.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                            ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesNonCash.')'],'finalPettyExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesNonCash.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalOtherExpense' => '('.$otherExpensesNonCash.')'],'finalOtherExpense.id = accounting_branch_program.id')
                            ->leftJoin(['finalBankDeposits' => '('.$bankDepositsNonCash.')'],'finalBankDeposits.id = accounting_branch_program.id')
                            ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesNonCash.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                            ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersNonCash.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                            ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                            ->asArray()
                            ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                            ->all();

                $auditSummaryNonCashSeason = $access ? $access->branch_program_id != '' ? Season::find()
                            ->select([
                                'accounting_season.id as id',
                                'accounting_season.branch_program_id as branch_program_id',
                                'finalBeginningCoh.coh as beginningCoh',
                                'finalBeginningCoh.cob as beginningCob',
                                'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                'finalBankDeposits.total as bankDepositsTotal',
                                '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                '(
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBranchTransfers.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                '((COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBankDeposits.total, 0) + 
                                    COALESCE(finalBranchTransfers.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal'
                            ])
                            ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                            ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                            ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                            ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsNonCashSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                            ->leftJoin(['finalFreebies' => '('.$incomeFreebiesNonCashSeason.')'],'finalFreebies.id = accounting_season.id')
                            ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesNonCashSeason.')'],'finalPettyExpense.id = accounting_season.id')
                            ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesNonCashSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                            ->leftJoin(['finalOtherExpense' => '('.$otherExpensesNonCashSeason.')'],'finalOtherExpense.id = accounting_season.id')
                            ->leftJoin(['finalBankDeposits' => '('.$bankDepositsNonCashSeason.')'],'finalBankDeposits.id = accounting_season.id')
                            ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesNonCashSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                            ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsNonCashSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                            ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersNonCashSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                            ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                            ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                            ->asArray()
                            ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                            ->all() : Season::find()
                            ->select([
                                'accounting_season.id as id',
                                'accounting_season.branch_program_id as branch_program_id',
                                'finalBeginningCoh.coh as beginningCoh',
                                'finalBeginningCoh.cob as beginningCob',
                                'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                'finalBankDeposits.total as bankDepositsTotal',
                                '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                '(
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBranchTransfers.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                '((COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBankDeposits.total, 0) + 
                                    COALESCE(finalBranchTransfers.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal'
                            ])
                            ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                            ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                            ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                            ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsNonCashSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                            ->leftJoin(['finalFreebies' => '('.$incomeFreebiesNonCashSeason.')'],'finalFreebies.id = accounting_season.id')
                            ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesNonCashSeason.')'],'finalPettyExpense.id = accounting_season.id')
                            ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesNonCashSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                            ->leftJoin(['finalOtherExpense' => '('.$otherExpensesNonCashSeason.')'],'finalOtherExpense.id = accounting_season.id')
                            ->leftJoin(['finalBankDeposits' => '('.$bankDepositsNonCashSeason.')'],'finalBankDeposits.id = accounting_season.id')
                            ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesNonCashSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                            ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsNonCashSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                            ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersNonCashSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                            ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                            ->asArray()
                            ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                            ->all() : Season::find()
                            ->select([
                                'accounting_season.id as id',
                                'accounting_season.branch_program_id as branch_program_id',
                                'finalBeginningCoh.coh as beginningCoh',
                                'finalBeginningCoh.cob as beginningCob',
                                'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                'finalBankDeposits.total as bankDepositsTotal',
                                '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                '(
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBranchTransfers.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                '((COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                    COALESCE(finalPettyExpense.total, 0) + 
                                    COALESCE(finalPhotocopyExpense.total, 0) + 
                                    COALESCE(finalOtherExpense.total, 0) + 
                                    COALESCE(finalBankDeposits.total, 0) + 
                                    COALESCE(finalBranchTransfers.total, 0) + 
                                    COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal'
                            ])
                            ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                            ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                            ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                            ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsNonCashSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                            ->leftJoin(['finalFreebies' => '('.$incomeFreebiesNonCashSeason.')'],'finalFreebies.id = accounting_season.id')
                            ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesNonCashSeason.')'],'finalPettyExpense.id = accounting_season.id')
                            ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesNonCashSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                            ->leftJoin(['finalOtherExpense' => '('.$otherExpensesNonCashSeason.')'],'finalOtherExpense.id = accounting_season.id')
                            ->leftJoin(['finalBankDeposits' => '('.$bankDepositsNonCashSeason.')'],'finalBankDeposits.id = accounting_season.id')
                            ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesNonCashSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                            ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsNonCashSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                            ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersNonCashSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                            ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                            ->asArray()
                            ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                            ->all();
            } 
        }else{
            if(!empty($date))
            {
                $beginningCoh = $this->getBeginningCash();

                $incomeEnrolmentsNonCash = $this->getIncomeEnrolmentByBranchProgramRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $incomeEnrolmentsNonCashSeason = $this->getIncomeEnrolmentBySeasonRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $incomeFreebiesNonCash = $this->getIncomeFreebieByBranchProgramRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $incomeFreebiesNonCashSeason = $this->getIncomeFreebieBySeasonRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $pettyExpensesNonCash = $this->getPettyExpenseByBranchProgramRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $pettyExpensesNonCashSeason = $this->getPettyExpenseBySeasonRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $photocopyExpensesNonCash = $this->getPhotocopyExpenseByBranchProgramRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $photocopyExpensesNonCashSeason = $this->getPhotocopyExpenseBySeasonRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $otherExpensesNonCash = $this->getOtherExpenseByBranchProgramRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $otherExpensesNonCashSeason = $this->getOtherExpenseBySeasonRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $bankDepositsNonCash = $this->getBankDepositByBranchProgramRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $bankDepositsNonCashSeason = $this->getBankDepositBySeasonRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $operatingExpensesNonCash = $this->getOperatingExpenseByBranchProgramRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $operatingExpensesNonCashSeason = $this->getOperatingExpenseBySeasonRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $budgetProposalsNonCash = $this->getBudgetProposalByBranchProgramRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $budgetProposalsNonCashSeason = $this->getBudgetProposalBySeasonRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $branchTransfersNonCash = $this->getBranchTransferByBranchProgramRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $branchTransfersNonCashSeason = $this->getBranchTransferBySeasonRawSql($date[0][0], $date[0][1], 'Non-Cash');

                $auditSummaryNonCash = $access ? $access->branch_program_id != '' ? BranchProgram::find()
                                ->select([
                                    'accounting_branch_program.id as id',
                                    'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsNonCash.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesNonCash.')'],'finalFreebies.id = accounting_branch_program.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsNonCash.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesNonCash.')'],'finalPettyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesNonCash.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesNonCash.')'],'finalOtherExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsNonCash.')'],'finalBankDeposits.id = accounting_branch_program.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesNonCash.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersNonCash.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : BranchProgram::find()
                                ->select([
                                    'accounting_branch_program.id as id',
                                    'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsNonCash.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesNonCash.')'],'finalFreebies.id = accounting_branch_program.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsNonCash.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesNonCash.')'],'finalPettyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesNonCash.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesNonCash.')'],'finalOtherExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsNonCash.')'],'finalBankDeposits.id = accounting_branch_program.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesNonCash.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersNonCash.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : BranchProgram::find()
                                ->select([
                                    'accounting_branch_program.id as id',
                                    'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsNonCash.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesNonCash.')'],'finalFreebies.id = accounting_branch_program.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsNonCash.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesNonCash.')'],'finalPettyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesNonCash.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesNonCash.')'],'finalOtherExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsNonCash.')'],'finalBankDeposits.id = accounting_branch_program.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesNonCash.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersNonCash.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all();

                $auditSummaryNonCashSeason = $access ? $access->branch_program_id != '' ? Season::find()
                                ->select([
                                    'accounting_season.id as id',
                                    'accounting_season.branch_program_id as branch_program_id',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsNonCashSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesNonCashSeason.')'],'finalFreebies.id = accounting_season.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesNonCashSeason.')'],'finalPettyExpense.id = accounting_season.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesNonCashSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesNonCashSeason.')'],'finalOtherExpense.id = accounting_season.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsNonCashSeason.')'],'finalBankDeposits.id = accounting_season.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesNonCashSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsNonCashSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersNonCashSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : Season::find()
                                ->select([
                                    'accounting_season.id as id',
                                    'accounting_season.branch_program_id as branch_program_id',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsNonCashSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesNonCashSeason.')'],'finalFreebies.id = accounting_season.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesNonCashSeason.')'],'finalPettyExpense.id = accounting_season.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesNonCashSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesNonCashSeason.')'],'finalOtherExpense.id = accounting_season.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsNonCashSeason.')'],'finalBankDeposits.id = accounting_season.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesNonCashSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsNonCashSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersNonCashSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : Season::find()
                                ->select([
                                    'accounting_season.id as id',
                                    'accounting_season.branch_program_id as branch_program_id',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsNonCashSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesNonCashSeason.')'],'finalFreebies.id = accounting_season.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesNonCashSeason.')'],'finalPettyExpense.id = accounting_season.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesNonCashSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesNonCashSeason.')'],'finalOtherExpense.id = accounting_season.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsNonCashSeason.')'],'finalBankDeposits.id = accounting_season.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesNonCashSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsNonCashSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersNonCashSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all();
            }else{
                $beginningCoh = $this->getBeginningCash();

                $incomeEnrolmentsNonCash = $this->getIncomeEnrolmentByBranchProgramRawSql(null, null, 'Non-Cash');

                $incomeEnrolmentsNonCashSeason = $this->getIncomeEnrolmentBySeasonRawSql(null, null, 'Non-Cash');

                $incomeFreebiesNonCash = $this->getIncomeFreebieByBranchProgramRawSql(null, null, 'Non-Cash');

                $incomeFreebiesNonCashSeason = $this->getIncomeFreebieBySeasonRawSql(null, null, 'Non-Cash');

                $pettyExpensesNonCash = $this->getPettyExpenseByBranchProgramRawSql(null, null, 'Non-Cash');

                $pettyExpensesNonCashSeason = $this->getPettyExpenseBySeasonRawSql(null, null, 'Non-Cash');

                $photocopyExpensesNonCash = $this->getPhotocopyExpenseByBranchProgramRawSql(null, null, 'Non-Cash');

                $photocopyExpensesNonCashSeason = $this->getPhotocopyExpenseBySeasonRawSql(null, null, 'Non-Cash');

                $otherExpensesNonCash = $this->getOtherExpenseByBranchProgramRawSql(null, null, 'Non-Cash');

                $otherExpensesNonCashSeason = $this->getOtherExpenseBySeasonRawSql(null, null, 'Non-Cash');

                $bankDepositsNonCash = $this->getBankDepositByBranchProgramRawSql(null, null, 'Non-Cash');

                $bankDepositsNonCashSeason = $this->getBankDepositBySeasonRawSql(null, null, 'Non-Cash');

                $operatingExpensesNonCash = $this->getOperatingExpenseByBranchProgramRawSql(null, null, 'Non-Cash');

                $operatingExpensesNonCashSeason = $this->getOperatingExpenseBySeasonRawSql(null, null, 'Non-Cash');

                $budgetProposalsNonCash = $this->getBudgetProposalByBranchProgramRawSql(null, null, 'Non-Cash');

                $budgetProposalsNonCashSeason = $this->getBudgetProposalBySeasonRawSql(null, null, 'Non-Cash');

                $branchTransfersNonCash = $this->getBranchTransferByBranchProgramRawSql(null, null, 'Non-Cash');

                $branchTransfersNonCashSeason = $this->getBranchTransferBySeasonRawSql(null, null, 'Non-Cash');

                $auditSummaryNonCash = $access ? $access->branch_program_id != '' ? BranchProgram::find()
                                ->select([
                                    'accounting_branch_program.id as id',
                                    'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsNonCash.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesNonCash.')'],'finalFreebies.id = accounting_branch_program.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsNonCash.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesNonCash.')'],'finalPettyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesNonCash.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesNonCash.')'],'finalOtherExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsNonCash.')'],'finalBankDeposits.id = accounting_branch_program.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesNonCash.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersNonCash.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : BranchProgram::find()
                                ->select([
                                    'accounting_branch_program.id as id',
                                    'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsNonCash.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesNonCash.')'],'finalFreebies.id = accounting_branch_program.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsNonCash.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesNonCash.')'],'finalPettyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesNonCash.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesNonCash.')'],'finalOtherExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsNonCash.')'],'finalBankDeposits.id = accounting_branch_program.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesNonCash.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersNonCash.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : BranchProgram::find()
                                ->select([
                                    'accounting_branch_program.id as id',
                                    'concat(accounting_branch.name," - ",accounting_program.name) as name',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0) +
                                        COALESCE(finalBranchTransfers.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsNonCash.')'],'finalIncomeEnrolments.id = accounting_branch_program.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesNonCash.')'],'finalFreebies.id = accounting_branch_program.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsNonCash.')'],'finalBudgetProposals.id = accounting_branch_program.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesNonCash.')'],'finalPettyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesNonCash.')'],'finalPhotocopyExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesNonCash.')'],'finalOtherExpense.id = accounting_branch_program.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsNonCash.')'],'finalBankDeposits.id = accounting_branch_program.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesNonCash.')'],'finalOperatingExpenses.id = accounting_branch_program.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersNonCash.')'],'finalBranchTransfers.id = accounting_branch_program.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCoh.')'],'finalBeginningCoh.id = accounting_branch_program.id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all();

                $auditSummaryNonCashSeason = $access ? $access->branch_program_id != '' ? Season::find()
                                ->select([
                                    'accounting_season.id as id',
                                    'accounting_season.branch_program_id as branch_program_id',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsNonCashSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesNonCashSeason.')'],'finalFreebies.id = accounting_season.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesNonCashSeason.')'],'finalPettyExpense.id = accounting_season.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesNonCashSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesNonCashSeason.')'],'finalOtherExpense.id = accounting_season.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsNonCashSeason.')'],'finalBankDeposits.id = accounting_season.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesNonCashSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsNonCashSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersNonCashSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : Season::find()
                                ->select([
                                    'accounting_season.id as id',
                                    'accounting_season.branch_program_id as branch_program_id',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsNonCashSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesNonCashSeason.')'],'finalFreebies.id = accounting_season.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesNonCashSeason.')'],'finalPettyExpense.id = accounting_season.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesNonCashSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesNonCashSeason.')'],'finalOtherExpense.id = accounting_season.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsNonCashSeason.')'],'finalBankDeposits.id = accounting_season.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesNonCashSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsNonCashSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersNonCashSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all() : Season::find()
                                ->select([
                                    'accounting_season.id as id',
                                    'accounting_season.branch_program_id as branch_program_id',
                                    'finalBeginningCoh.coh as beginningCoh',
                                    'finalBeginningCoh.cob as beginningCob',
                                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name',
                                    'finalBankDeposits.total as bankDepositsTotal',
                                    '(COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) as incomeTotal',
                                    '(
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0)) as expenseTotal',
                                    '((COALESCE(finalBeginningCoh.cob, 0) + COALESCE(finalIncomeEnrolments.total, 0) + COALESCE(finalFreebies.total, 0) + COALESCE(finalBudgetProposals.total, 0)) - (
                                        COALESCE(finalPettyExpense.total, 0) + 
                                        COALESCE(finalPhotocopyExpense.total, 0) + 
                                        COALESCE(finalOtherExpense.total, 0) + 
                                        COALESCE(finalBankDeposits.total, 0) + 
                                        COALESCE(finalBranchTransfers.total, 0) + 
                                        COALESCE(finalOperatingExpenses.total, 0))) as netIncomeTotal'
                                ])
                                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                                ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                                ->leftJoin(['finalIncomeEnrolments' => '('.$incomeEnrolmentsNonCashSeason.')'],'finalIncomeEnrolments.id = accounting_season.id')
                                ->leftJoin(['finalFreebies' => '('.$incomeFreebiesNonCashSeason.')'],'finalFreebies.id = accounting_season.id')
                                ->leftJoin(['finalPettyExpense' => '('.$pettyExpensesNonCashSeason.')'],'finalPettyExpense.id = accounting_season.id')
                                ->leftJoin(['finalPhotocopyExpense' => '('.$photocopyExpensesNonCashSeason.')'],'finalPhotocopyExpense.id = accounting_season.id')
                                ->leftJoin(['finalOtherExpense' => '('.$otherExpensesNonCashSeason.')'],'finalOtherExpense.id = accounting_season.id')
                                ->leftJoin(['finalBankDeposits' => '('.$bankDepositsNonCashSeason.')'],'finalBankDeposits.id = accounting_season.id')
                                ->leftJoin(['finalOperatingExpenses' => '('.$operatingExpensesNonCashSeason.')'],'finalOperatingExpenses.id = accounting_season.id')
                                ->leftJoin(['finalBudgetProposals' => '('.$budgetProposalsNonCashSeason.')'],'finalBudgetProposals.id = accounting_season.id')
                                ->leftJoin(['finalBranchTransfers' => '('.$branchTransfersNonCashSeason.')'],'finalBranchTransfers.id = accounting_season.id')
                                ->leftJoin(['finalBeginningCoh' => '('.$beginningCohSeason.')'],'finalBeginningCoh.id = accounting_season.id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['netIncomeTotal' => SORT_ASC, 'name' => SORT_ASC])
                                ->all();
            }            
        }

        $content = $this->renderPartial('_audit_summary_non_cash', [
                        'date' => $date,
                        'auditSummaryNonCash' => $auditSummaryNonCash,
                        'auditSummaryNonCashSeason' => $auditSummaryNonCashSeason,
                    ]);

        $title = empty($date) ? 'Audit Summary Report: Overall' : 'Audit Summary Report: '.$date[0][0].' - '.$date[0][1].' - Non-Cash';

        $pdf = new Pdf([
        'mode' => Pdf::MODE_CORE,
        'format' => Pdf::FORMAT_LEGAL, 
        'orientation' => Pdf::ORIENT_LANDSCAPE, 
        'destination' => Pdf::DEST_DOWNLOAD, 
        'filename' => $title.'.pdf',
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
        'options' => ['title' => $title],
        'methods' => [ 
            'SetHeader'=> [$title], 
            'SetFooter'=>['Page {PAGENO}'],
        ]
        ]);
        $response = Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_RAW;
        $headers = Yii::$app->response->headers;
        $headers->add('Content-Type', 'application/pdf');
        return $pdf->render();
    }

    public function actionProceed($id)
    {
        $model = $this->findModel($id);

        switch($model->model){
            case 'Audit':
                return $this->redirect(['/accounting/audit/']);
            break;
            case 'TargetEnrolee':
                return $this->redirect(['/accounting/target-enrolee/']);
            break;
            case 'TargetExpense':
                return $this->redirect(['/accounting/target-expense/']);
            break;
            case 'Season':
                return $this->redirect(['/accounting/season-or-list/']);
            break;
            case 'BudgetProposal':
                if($model->model_id == '')
                {
                    $model->delete();
                    return $this->redirect(['/accounting/budget-proposal/']);
                }else{
                    return $this->redirect(['/accounting/budget-proposal/particular', 'id' => $model->model_id]);
                }
                
            break;
            case 'BranchTransfer':
                $model->delete();
                return $this->redirect(['/accounting/branch-transfer/view', 'id' => $model->model_id]);
            break;
            case 'Transferee':
                $model->delete();
                return $this->redirect(['/accounting/transferee/view', 'id' => $model->model_id]);
            break;
        }
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if($model)
        {
            $model->delete();
        }
        \Yii::$app->getSession()->setFlash('success', 'Notification  has been dismissed successfully.');
        return $this->redirect(['index']);
    }

    /**
     * Finds the BudgetProposal model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return BudgetProposal the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {

        if (($model = Notification::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    protected function findStudent($id)
    {

        if (($model = Student::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
