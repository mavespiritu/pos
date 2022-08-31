<?php

namespace common\modules\accounting\models;

use Yii;

/**
 * This is the model class for table "accounting_branch_program".
 *
 * @property int $id
 * @property int $branch_id
 * @property int $program_id
 *
 * @property AccountingProgram $program
 * @property AccountingBranch $branch
 * @property AccountingProgram $program0
 * @property AccountingBranchProgramEnhancement[] $accountingBranchProgramEnhancements
 * @property AccountingSeason[] $accountingSeasons
 */
class BranchProgram extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'accounting_branch_program';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['branch_id', 'program_id'], 'required'],
            [['branch_id', 'program_id'], 'integer'],
            [['program_id'], 'exist', 'skipOnError' => true, 'targetClass' => Program::className(), 'targetAttribute' => ['program_id' => 'id']],
            [['branch_id'], 'exist', 'skipOnError' => true, 'targetClass' => Branch::className(), 'targetAttribute' => ['branch_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'branch_id' => 'Branch',
            'program_id' => 'Program',
            'branchName' => 'Branch',
            'programName' => 'Program',
        ];
    }

    public function behaviors()
    {
        return [
            'bedezign\yii2\audit\AuditTrailBehavior'
        ];
    }

    public function getHiddenFormTokenField() {
        $token = \Yii::$app->getSecurity()->generateRandomString();
        $token = str_replace('+', '.', base64_encode($token));

        \Yii::$app->session->set(\Yii::$app->params['form_token_param'], $token);;
        return Html::hiddenInput(\Yii::$app->params['form_token_param'], $token);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProgram()
    {
        return $this->hasOne(Program::className(), ['id' => 'program_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBranch()
    {
        return $this->hasOne(Branch::className(), ['id' => 'branch_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBranchProgramEnhancements()
    {
        return $this->hasMany(BranchProgramEnhancement::className(), ['branch_program_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSeasons()
    {
        return $this->hasMany(Season::className(), ['branch_program_id' => 'id']);
    }

    public function getBranchName()
    {
        return $this->branch ? $this->branch->name : '';
    }

    public function getProgramName()
    {
        return $this->program ? $this->program->name : '';
    }

    public function getBranchProgramName()
    {
        return $this->branchName.' - '.$this->programName;
    }

    public function getTotalGross()
    {
        $gross = TargetIncome::find()
                ->select(['sum(quantity*unit_price) as total'])
                ->leftJoin('accounting_season', 'accounting_season.id = accounting_target_income.season_id')
                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                ->where(['accounting_branch_program.id' => $this->id])
                ->groupBy(['accounting_branch_program.id'])
                ->asArray()
                ->one();

        return $gross['total'];
    }

    public function getTotalStudents()
    {
        $student = TargetIncome::find()
                ->select(['sum(quantity) as total'])
                ->leftJoin('accounting_season', 'accounting_season.id = accounting_target_student.season_id')
                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                ->where(['accounting_branch_program.id' => $this->id])
                ->groupBy(['accounting_branch_program.id'])
                ->asArray()
                ->one();

        return $student['total'];
    }

    public function getTotalGrossIncome()
    {
        $total = 0;

        $seasons = $this->seasons;

        if($seasons)
        {
            foreach($seasons as $season)
            {
                $total += $season->totalGrossIncome;
            }
        }

        return $total;
    }

    public function getTotalPrograms()
    {
        $programs = TargetProgram::find()
                    ->select(['sum(quantity*unit_price) as total'])
                    ->leftJoin('accounting_season', 'accounting_season.id = accounting_target_program.season_id')
                    ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                    ->where(['accounting_branch_program.id' => $this->id])
                    ->groupBy(['accounting_branch_program.id'])
                    ->asArray()
                    ->one();

        return $programs['total'];
    }

    public function getTotalVenueRentals()
    {
        $venueRentals = TargetVenueRental::find()
                        ->select(['sum(quantity*unit_price) as total'])
                        ->leftJoin('accounting_season', 'accounting_season.id = accounting_target_venue_rental.season_id')
                        ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                        ->where(['accounting_branch_program.id' => $this->id])
                        ->groupBy(['accounting_branch_program.id'])
                        ->asArray()
                        ->one();

        return $venueRentals['total'];
    }

    public function getTotalFreebies()
    {
        $freebies = TargetFreebie::find()
                    ->select(['sum(quantity*unit_price) as total'])
                    ->leftJoin('accounting_season', 'accounting_season.id = accounting_target_freebie.season_id')
                    ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                    ->where(['accounting_branch_program.id' => $this->id])
                    ->groupBy(['accounting_branch_program.id'])
                    ->asArray()
                    ->one();

        return $freebies['total'];
    }

    public function getTotalReviews()
    {
        $reviewMaterials = TargetReview::find()
                            ->select(['sum(quantity*unit_price) as total'])
                            ->leftJoin('accounting_season', 'accounting_season.id = accounting_target_review.season_id')
                            ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                            ->where(['accounting_branch_program.id' => $this->id])
                            ->groupBy(['accounting_branch_program.id'])
                            ->asArray()
                            ->one();

        return $reviewMaterials['total'];
    }

    public function getTotalFoods()
    {
        $foods = TargetFood::find()
                ->select(['sum(quantity*unit_price) as total'])
                ->leftJoin('accounting_season', 'accounting_season.id = accounting_target_food.season_id')
                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                ->where(['accounting_branch_program.id' => $this->id])
                ->groupBy(['accounting_branch_program.id'])
                ->asArray()
                ->one();

        return $foods['total'];
    }

    public function getTotalTransportations()
    {
        $transportations = TargetTransportation::find()
                            ->select(['sum(quantity*unit_price) as total'])
                            ->leftJoin('accounting_season', 'accounting_season.id = accounting_target_transportation.season_id')
                            ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                            ->where(['accounting_branch_program.id' => $this->id])
                            ->groupBy(['accounting_branch_program.id'])
                            ->asArray()
                            ->one();

        return $transportations['total'];
    }

    public function getTotalStaffSalaries()
    {
        $staffSalaries = TargetStaffSalary::find()
                        ->select(['sum(quantity*unit_price) as total'])
                        ->leftJoin('accounting_season', 'accounting_season.id = accounting_target_staff_salary.season_id')
                        ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                        ->where(['accounting_branch_program.id' => $this->id])
                        ->groupBy(['accounting_branch_program.id'])
                        ->asArray()
                        ->one();

        return $staffSalaries['total'];
    }

    public function getTotalRebates()
    {
        $rebates = TargetRebate::find()
                    ->select(['sum(quantity*unit_price) as total'])
                    ->leftJoin('accounting_season', 'accounting_season.id = accounting_target_rebate.season_id')
                    ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                    ->where(['accounting_branch_program.id' => $this->id])
                    ->groupBy(['accounting_branch_program.id'])
                    ->asArray()
                    ->one();

        return $rebates['total'];
    }

    public function getTotalUtilities()
    {
        $utilities = TargetUtility::find()
                    ->select(['sum(quantity*unit_price) as total'])
                    ->leftJoin('accounting_season', 'accounting_season.id = accounting_target_utility.season_id')
                    ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                    ->where(['accounting_branch_program.id' => $this->id])
                    ->groupBy(['accounting_branch_program.id'])
                    ->asArray()
                    ->one();

        return $utilities['total'];
    }

    public function getTotalAcademics()
    {
        $academics = TargetAcademic::find()
                    ->select(['sum(quantity*unit_price) as total'])
                    ->leftJoin('accounting_season', 'accounting_season.id = accounting_target_academic.season_id')
                    ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                    ->where(['accounting_branch_program.id' => $this->id])
                    ->groupBy(['accounting_branch_program.id'])
                    ->asArray()
                    ->one();

        return $academics['total'];
    }

    public function getTotalEmergencyFunds()
    {
        $emergencyFunds = TargetEmergencyFund::find()
                        ->select(['sum(quantity*unit_price) as total'])
                        ->leftJoin('accounting_season', 'accounting_season.id = accounting_target_emergency_fund.season_id')
                        ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                        ->where(['accounting_branch_program.id' => $this->id])
                        ->groupBy(['accounting_branch_program.id'])
                        ->asArray()
                        ->one();

        return $emergencyFunds['total'];
    }

    public function getTotalExpenses()
    {
        $total = 0;

        $programs = TargetProgram::find()
                    ->select(['sum(quantity*unit_price) as total'])
                    ->leftJoin('accounting_season', 'accounting_season.id = accounting_target_program.season_id')
                    ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                    ->where(['accounting_branch_program.id' => $this->id])
                    ->groupBy(['accounting_branch_program.id'])
                    ->asArray()
                    ->one();
        $venueRentals = TargetVenueRental::find()
                        ->select(['sum(quantity*unit_price) as total'])
                        ->leftJoin('accounting_season', 'accounting_season.id = accounting_target_venue_rental.season_id')
                        ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                        ->where(['accounting_branch_program.id' => $this->id])
                        ->groupBy(['accounting_branch_program.id'])
                        ->asArray()
                        ->one();
        $freebies = TargetFreebie::find()
                    ->select(['sum(quantity*unit_price) as total'])
                    ->leftJoin('accounting_season', 'accounting_season.id = accounting_target_freebie.season_id')
                    ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                    ->where(['accounting_branch_program.id' => $this->id])
                    ->groupBy(['accounting_branch_program.id'])
                    ->asArray()
                    ->one();
        $reviewMaterials = TargetReview::find()
                            ->select(['sum(quantity*unit_price) as total'])
                            ->leftJoin('accounting_season', 'accounting_season.id = accounting_target_review.season_id')
                            ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                            ->where(['accounting_branch_program.id' => $this->id])
                            ->groupBy(['accounting_branch_program.id'])
                            ->asArray()
                            ->one();
        $foods = TargetFood::find()
                ->select(['sum(quantity*unit_price) as total'])
                ->leftJoin('accounting_season', 'accounting_season.id = accounting_target_food.season_id')
                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                ->where(['accounting_branch_program.id' => $this->id])
                ->groupBy(['accounting_branch_program.id'])
                ->asArray()
                ->one();
        $transportations = TargetTransportation::find()
                            ->select(['sum(quantity*unit_price) as total'])
                            ->leftJoin('accounting_season', 'accounting_season.id = accounting_target_transportation.season_id')
                            ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                            ->where(['accounting_branch_program.id' => $this->id])
                            ->groupBy(['accounting_branch_program.id'])
                            ->asArray()
                            ->one();
        $staffSalaries = TargetStaffSalary::find()
                        ->select(['sum(quantity*unit_price) as total'])
                        ->leftJoin('accounting_season', 'accounting_season.id = accounting_target_staff_salary.season_id')
                        ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                        ->where(['accounting_branch_program.id' => $this->id])
                        ->groupBy(['accounting_branch_program.id'])
                        ->asArray()
                        ->one();
        $rebates = TargetRebate::find()
                    ->select(['sum(quantity*unit_price) as total'])
                    ->leftJoin('accounting_season', 'accounting_season.id = accounting_target_rebate.season_id')
                    ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                    ->where(['accounting_branch_program.id' => $this->id])
                    ->groupBy(['accounting_branch_program.id'])
                    ->asArray()
                    ->one();
        $utilities = TargetUtility::find()
                    ->select(['sum(quantity*unit_price) as total'])
                    ->leftJoin('accounting_season', 'accounting_season.id = accounting_target_utility.season_id')
                    ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                    ->where(['accounting_branch_program.id' => $this->id])
                    ->groupBy(['accounting_branch_program.id'])
                    ->asArray()
                    ->one();
        $academics = TargetAcademic::find()
                    ->select(['sum(quantity*unit_price) as total'])
                    ->leftJoin('accounting_season', 'accounting_season.id = accounting_target_academic.season_id')
                    ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                    ->where(['accounting_branch_program.id' => $this->id])
                    ->groupBy(['accounting_branch_program.id'])
                    ->asArray()
                    ->one();
        $emergencyFunds = TargetEmergencyFund::find()
                        ->select(['sum(quantity*unit_price) as total'])
                        ->leftJoin('accounting_season', 'accounting_season.id = accounting_target_emergency_fund.season_id')
                        ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                        ->where(['accounting_branch_program.id' => $this->id])
                        ->groupBy(['accounting_branch_program.id'])
                        ->asArray()
                        ->one();

        $total = $programs['total'] + $venueRentals['total'] + $freebies['total'] + $reviewMaterials['total'] + $foods['total'] + $transportations['total'] + $staffSalaries['total'] + $rebates['total'] + $utilities['total'] + $academics['total'] + $emergencyFunds['total'];

        return $total;
    }

    public function getExpectedIncome()
    {
        $total = $this->totalGrossIncome - $this->totalExpenses;

        return $total;
    }

    public function getNetIncome()
    {
        $total = 0;

        $seasons = $this->seasons;

        if($seasons)
        {
            foreach($seasons as $season)
            {
                $total += $season->netIncome;
            }
        }

        return $total;
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

     // by cutoff
    public function getCashOnHand()
    {
        $cutoff = $this->check_in_cutoff(date('Y-m-d'));
        $dates = $this->dateRange($cutoff['start'], $cutoff['end']);
        $branchProgram = BranchProgram::findOne($this->id);

        $beginningcoh = BeginningCoh::find()->where(['date(datetime)' => $cutoff['start'], 'branch_program_id' => $branchProgram->id])->one();
        $beginningcohAmount = $beginningcoh ? $beginningcoh->cash_on_hand : 0;

        $data = BranchProgram::find()
                ->select([
                    'accounting_branch_program.id as id',
                    'COALESCE(finalIncomeEnrolments.total, 0) as incomeEnrolmentTotal',
                    'COALESCE(finalFreebies.total, 0) as freebiesTotal',
                    'COALESCE(finalPettyExpense.total, 0) as pettyExpenseTotal',
                    'COALESCE(finalPhotocopyExpense.total, 0) as photocopyExpenseTotal',
                    'COALESCE(finalOtherExpense.total, 0) as otherExpenseTotal',
                    'COALESCE(finalBankDeposits.total, 0) as bankDepositsTotal',
                    'COALESCE(finalOperatingExpense.total, 0) as operatingExpenseTotal',
                    'COALESCE(finalBudgetProposal.total, 0) as budgetProposalTotal',
                    'COALESCE(finalBranchTransfer.total, 0) as branchTransferTotal',
                ])
                ->leftJoin(['finalIncomeEnrolments' => '(
                        SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(incomeEnrolments.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN 
                            (
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
                                    accounting_income.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 23:59:59" and
                                    accounting_income.amount_type = "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) incomeEnrolments on incomeEnrolments.id = accounting_branch_program.id
                        GROUP BY accounting_branch_program.id
                )'], 'finalIncomeEnrolments.id = accounting_branch_program.id')
                ->leftJoin(['finalFreebies' => '(
                        SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(freebies.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            ( 
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
                                    accounting_income.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 23:59:59" and
                                    accounting_income.amount_type = "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) freebies on freebies.id = accounting_branch_program.id 
                        GROUP BY accounting_branch_program.id
                )'], 'finalFreebies.id = accounting_branch_program.id')
                ->leftJoin(['finalPettyExpense' => '(
                     SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(pettyExpense.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 23:59:59" and
                                    accounting_expense.amount_type = "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) pettyExpense on pettyExpense.id = accounting_branch_program.id
                    GROUP BY accounting_branch_program.id
                )'], 'finalPettyExpense.id = accounting_branch_program.id')
                ->leftJoin(['finalPhotocopyExpense' => '(
                    SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(photocopyExpense.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 23:59:59" and
                                    accounting_expense.amount_type = "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) photocopyExpense on photocopyExpense.id = accounting_branch_program.id
                    GROUP BY accounting_branch_program.id
                )'], 'finalPhotocopyExpense.id = accounting_branch_program.id')
                ->leftJoin(['finalOtherExpense' => '(
                    SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(otherExpense.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 23:59:59" and
                                    accounting_expense.amount_type = "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) otherExpense on otherExpense.id = accounting_branch_program.id
                    GROUP BY accounting_branch_program.id
                )'], 'finalOtherExpense.id = accounting_branch_program.id')
                ->leftJoin(['finalBankDeposits' => '(
                    SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(bankDeposits.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 23:59:59" and
                                    accounting_expense.amount_type = "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) bankDeposits on bankDeposits.id = accounting_branch_program.id
                    GROUP BY accounting_branch_program.id
                )'], 'finalBankDeposits.id = accounting_branch_program.id')
                ->leftJoin(['finalOperatingExpense' => '(
                    SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(operatingExpense.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 23:59:59" and
                                    accounting_expense.amount_type = "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) operatingExpense on operatingExpense.id = accounting_branch_program.id
                    GROUP BY accounting_branch_program.id
                )'], 'finalOperatingExpense.id = accounting_branch_program.id')
                ->leftJoin(['finalBudgetProposal' => '(
                    SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(budgetProposal.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            (
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
                                    accounting_income.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 23:59:59" and 
                                    accounting_income.amount_type = "Cash" and
                                    accounting_income_budget_proposal.approval_status = "Approved"
                                GROUP BY newDate, accounting_branch_program.id
                            ) budgetProposal on budgetProposal.id = accounting_branch_program.id
                    GROUP BY accounting_branch_program.id
                )'], 'finalBudgetProposal.id = accounting_branch_program.id')
                ->leftJoin(['finalBranchTransfer' => '(
                    SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(branchTransfer.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 23:59:59" and 
                                    accounting_expense_branch_transfer.amount_source = "Cash On Hand" and
                                    accounting_expense.amount_type = "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) branchTransfer on branchTransfer.id = accounting_branch_program.id
                    GROUP BY accounting_branch_program.id
                )'], 'finalBranchTransfer.id = accounting_branch_program.id')
                ->where(['accounting_branch_program.id' => $this->id])
                ->asArray()
                ->one();

        $data['beginningcoh'] = $beginningcohAmount;
        $data['cutoff'] = $cutoff;

        return $data;
    }

    // by cutoff to start of the day
    public function getCashOnHandByStartOfTheDate($date)
    {
        $cutoff = $this->check_in_cutoff($date);
        $dates = $this->dateRange($cutoff['start'], $cutoff['end']);
        $branchProgram = BranchProgram::findOne($this->id);

        $beginningcoh = BeginningCoh::find()->where(['date(datetime)' => $cutoff['start'], 'branch_program_id' => $branchProgram->id])->one();
        $beginningcohAmount = $beginningcoh ? $beginningcoh->cash_on_hand : 0;

        $data = BranchProgram::find()
                ->select([
                    'accounting_branch_program.id as id',
                    'COALESCE(finalIncomeEnrolments.total, 0) as incomeEnrolmentTotal',
                    'COALESCE(finalFreebies.total, 0) as freebiesTotal',
                    'COALESCE(finalPettyExpense.total, 0) as pettyExpenseTotal',
                    'COALESCE(finalPhotocopyExpense.total, 0) as photocopyExpenseTotal',
                    'COALESCE(finalOtherExpense.total, 0) as otherExpenseTotal',
                    'COALESCE(finalBankDeposits.total, 0) as bankDepositsTotal',
                    'COALESCE(finalOperatingExpense.total, 0) as operatingExpenseTotal',
                    'COALESCE(finalBudgetProposal.total, 0) as budgetProposalTotal',
                    'COALESCE(finalBranchTransfer.total, 0) as branchTransferTotal',
                ])
                ->leftJoin(['finalIncomeEnrolments' => '(
                        SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(incomeEnrolments.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN 
                            (
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
                                    accounting_income.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 00:00:00" and
                                    accounting_income.amount_type = "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) incomeEnrolments on incomeEnrolments.id = accounting_branch_program.id
                        GROUP BY accounting_branch_program.id
                )'], 'finalIncomeEnrolments.id = accounting_branch_program.id')
                ->leftJoin(['finalFreebies' => '(
                        SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(freebies.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            ( 
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
                                    accounting_income.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 00:00:00" and
                                    accounting_income.amount_type = "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) freebies on freebies.id = accounting_branch_program.id 
                        GROUP BY accounting_branch_program.id
                )'], 'finalFreebies.id = accounting_branch_program.id')
                ->leftJoin(['finalPettyExpense' => '(
                     SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(pettyExpense.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 00:00:00" and
                                    accounting_expense.amount_type = "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) pettyExpense on pettyExpense.id = accounting_branch_program.id
                    GROUP BY accounting_branch_program.id
                )'], 'finalPettyExpense.id = accounting_branch_program.id')
                ->leftJoin(['finalPhotocopyExpense' => '(
                    SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(photocopyExpense.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 00:00:00" and
                                    accounting_expense.amount_type = "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) photocopyExpense on photocopyExpense.id = accounting_branch_program.id
                    GROUP BY accounting_branch_program.id
                )'], 'finalPhotocopyExpense.id = accounting_branch_program.id')
                ->leftJoin(['finalOtherExpense' => '(
                    SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(otherExpense.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 00:00:00" and
                                    accounting_expense.amount_type = "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) otherExpense on otherExpense.id = accounting_branch_program.id
                    GROUP BY accounting_branch_program.id
                )'], 'finalOtherExpense.id = accounting_branch_program.id')
                ->leftJoin(['finalBankDeposits' => '(
                    SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(bankDeposits.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 00:00:00" and
                                    accounting_expense.amount_type = "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) bankDeposits on bankDeposits.id = accounting_branch_program.id
                    GROUP BY accounting_branch_program.id
                )'], 'finalBankDeposits.id = accounting_branch_program.id')
                ->leftJoin(['finalOperatingExpense' => '(
                    SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(operatingExpense.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 00:00:00" and
                                    accounting_expense.amount_type = "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) operatingExpense on operatingExpense.id = accounting_branch_program.id
                    GROUP BY accounting_branch_program.id
                )'], 'finalOperatingExpense.id = accounting_branch_program.id')
                ->leftJoin(['finalBudgetProposal' => '(
                    SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(budgetProposal.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            (
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
                                    accounting_income.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 00:00:00" and 
                                    accounting_income.amount_type = "Cash" and
                                    accounting_income_budget_proposal.approval_status = "Approved"
                                GROUP BY newDate, accounting_branch_program.id
                            ) budgetProposal on budgetProposal.id = accounting_branch_program.id
                    GROUP BY accounting_branch_program.id
                )'], 'finalBudgetProposal.id = accounting_branch_program.id')
                ->leftJoin(['finalBranchTransfer' => '(
                    SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(branchTransfer.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 00:00:00" and 
                                    accounting_expense_branch_transfer.amount_source = "Cash On Hand" and
                                    accounting_expense.amount_type = "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) branchTransfer on branchTransfer.id = accounting_branch_program.id
                    GROUP BY accounting_branch_program.id
                )'], 'finalBranchTransfer.id = accounting_branch_program.id')
                ->where(['accounting_branch_program.id' => $this->id])
                ->asArray()
                ->one();

        $data['beginningcoh'] = $beginningcohAmount;
        $data['cutoff'] = $cutoff;

        return $data;
    }

    // by cutoff to end of the day
    public function getCashOnHandByDate($date)
    {
        $cutoff = $this->check_in_cutoff($date);
        $dates = $this->dateRange($cutoff['start'], $cutoff['end']);
        $branchProgram = BranchProgram::findOne($this->id);

        $beginningcoh = BeginningCoh::find()->where(['date(datetime)' => $cutoff['start'], 'branch_program_id' => $branchProgram->id])->one();
        $beginningcohAmount = $beginningcoh ? $beginningcoh->cash_on_hand : 0;

        $data = BranchProgram::find()
                ->select([
                    'accounting_branch_program.id as id',
                    'COALESCE(finalIncomeEnrolments.total, 0) as incomeEnrolmentTotal',
                    'COALESCE(finalFreebies.total, 0) as freebiesTotal',
                    'COALESCE(finalPettyExpense.total, 0) as pettyExpenseTotal',
                    'COALESCE(finalPhotocopyExpense.total, 0) as photocopyExpenseTotal',
                    'COALESCE(finalOtherExpense.total, 0) as otherExpenseTotal',
                    'COALESCE(finalBankDeposits.total, 0) as bankDepositsTotal',
                    'COALESCE(finalOperatingExpense.total, 0) as operatingExpenseTotal',
                    'COALESCE(finalBudgetProposal.total, 0) as budgetProposalTotal',
                    'COALESCE(finalBranchTransfer.total, 0) as branchTransferTotal',
                ])
                ->leftJoin(['finalIncomeEnrolments' => '(
                        SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(incomeEnrolments.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN 
                            (
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
                                    accounting_income.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_income.amount_type = "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) incomeEnrolments on incomeEnrolments.id = accounting_branch_program.id
                        GROUP BY accounting_branch_program.id
                )'], 'finalIncomeEnrolments.id = accounting_branch_program.id')
                ->leftJoin(['finalFreebies' => '(
                        SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(freebies.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            ( 
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
                                    accounting_income.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_income.amount_type = "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) freebies on freebies.id = accounting_branch_program.id 
                        GROUP BY accounting_branch_program.id
                )'], 'finalFreebies.id = accounting_branch_program.id')
                ->leftJoin(['finalPettyExpense' => '(
                     SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(pettyExpense.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_expense.amount_type = "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) pettyExpense on pettyExpense.id = accounting_branch_program.id
                    GROUP BY accounting_branch_program.id
                )'], 'finalPettyExpense.id = accounting_branch_program.id')
                ->leftJoin(['finalPhotocopyExpense' => '(
                    SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(photocopyExpense.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_expense.amount_type = "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) photocopyExpense on photocopyExpense.id = accounting_branch_program.id
                    GROUP BY accounting_branch_program.id
                )'], 'finalPhotocopyExpense.id = accounting_branch_program.id')
                ->leftJoin(['finalOtherExpense' => '(
                    SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(otherExpense.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_expense.amount_type = "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) otherExpense on otherExpense.id = accounting_branch_program.id
                    GROUP BY accounting_branch_program.id
                )'], 'finalOtherExpense.id = accounting_branch_program.id')
                ->leftJoin(['finalBankDeposits' => '(
                    SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(bankDeposits.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_expense.amount_type = "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) bankDeposits on bankDeposits.id = accounting_branch_program.id
                    GROUP BY accounting_branch_program.id
                )'], 'finalBankDeposits.id = accounting_branch_program.id')
                ->leftJoin(['finalOperatingExpense' => '(
                    SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(operatingExpense.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_expense.amount_type = "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) operatingExpense on operatingExpense.id = accounting_branch_program.id
                    GROUP BY accounting_branch_program.id
                )'], 'finalOperatingExpense.id = accounting_branch_program.id')
                ->leftJoin(['finalBudgetProposal' => '(
                    SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(budgetProposal.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            (
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
                                    accounting_income.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 23:59:59" and 
                                    accounting_income.amount_type = "Cash" and
                                    accounting_income_budget_proposal.approval_status = "Approved"
                                GROUP BY newDate, accounting_branch_program.id
                            ) budgetProposal on budgetProposal.id = accounting_branch_program.id
                    GROUP BY accounting_branch_program.id
                )'], 'finalBudgetProposal.id = accounting_branch_program.id')
                ->leftJoin(['finalBranchTransfer' => '(
                    SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(branchTransfer.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 23:59:59" and 
                                    accounting_expense_branch_transfer.amount_source = "Cash On Hand" and
                                    accounting_expense.amount_type = "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) branchTransfer on branchTransfer.id = accounting_branch_program.id
                    GROUP BY accounting_branch_program.id
                )'], 'finalBranchTransfer.id = accounting_branch_program.id')
                ->where(['accounting_branch_program.id' => $this->id])
                ->asArray()
                ->one();

        $data['beginningcoh'] = $beginningcohAmount;
        $data['cutoff'] = $cutoff;

        return $data;
    }

    // by  day
    public function getCashOnHandByWholeDay($date)
    {
        $cutoff = $this->check_in_cutoff($date);
        $dates = $this->dateRange($cutoff['start'], $cutoff['end']);
        $branchProgram = BranchProgram::findOne($this->id);

        $beginningcoh = BeginningCoh::find()->where(['date(datetime)' => $cutoff['start'], 'branch_program_id' => $branchProgram->id])->one();
        $beginningcohAmount = $beginningcoh ? $beginningcoh->cash_on_hand : 0;

        $data = BranchProgram::find()
                ->select([
                    'accounting_branch_program.id as id',
                    'COALESCE(finalIncomeEnrolments.total, 0) as incomeEnrolmentTotal',
                    'COALESCE(finalFreebies.total, 0) as freebiesTotal',
                    'COALESCE(finalPettyExpense.total, 0) as pettyExpenseTotal',
                    'COALESCE(finalPhotocopyExpense.total, 0) as photocopyExpenseTotal',
                    'COALESCE(finalOtherExpense.total, 0) as otherExpenseTotal',
                    'COALESCE(finalBankDeposits.total, 0) as bankDepositsTotal',
                    'COALESCE(finalOperatingExpense.total, 0) as operatingExpenseTotal',
                    'COALESCE(finalBudgetProposal.total, 0) as budgetProposalTotal',
                    'COALESCE(finalBranchTransfer.total, 0) as branchTransferTotal',
                ])
                ->leftJoin(['finalIncomeEnrolments' => '(
                        SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(incomeEnrolments.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN 
                            (
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
                                    accounting_income.datetime between "'.$date.' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_income.amount_type = "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) incomeEnrolments on incomeEnrolments.id = accounting_branch_program.id
                        GROUP BY accounting_branch_program.id
                )'], 'finalIncomeEnrolments.id = accounting_branch_program.id')
                ->leftJoin(['finalFreebies' => '(
                        SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(freebies.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            ( 
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
                                    accounting_income.datetime between "'.$date.' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_income.amount_type = "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) freebies on freebies.id = accounting_branch_program.id 
                        GROUP BY accounting_branch_program.id
                )'], 'finalFreebies.id = accounting_branch_program.id')
                ->leftJoin(['finalPettyExpense' => '(
                     SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(pettyExpense.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$date.' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_expense.amount_type = "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) pettyExpense on pettyExpense.id = accounting_branch_program.id
                    GROUP BY accounting_branch_program.id
                )'], 'finalPettyExpense.id = accounting_branch_program.id')
                ->leftJoin(['finalPhotocopyExpense' => '(
                    SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(photocopyExpense.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$date.' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_expense.amount_type = "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) photocopyExpense on photocopyExpense.id = accounting_branch_program.id
                    GROUP BY accounting_branch_program.id
                )'], 'finalPhotocopyExpense.id = accounting_branch_program.id')
                ->leftJoin(['finalOtherExpense' => '(
                    SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(otherExpense.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$date.' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_expense.amount_type = "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) otherExpense on otherExpense.id = accounting_branch_program.id
                    GROUP BY accounting_branch_program.id
                )'], 'finalOtherExpense.id = accounting_branch_program.id')
                ->leftJoin(['finalBankDeposits' => '(
                    SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(bankDeposits.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$date.' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_expense.amount_type = "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) bankDeposits on bankDeposits.id = accounting_branch_program.id
                    GROUP BY accounting_branch_program.id
                )'], 'finalBankDeposits.id = accounting_branch_program.id')
                ->leftJoin(['finalOperatingExpense' => '(
                    SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(operatingExpense.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$date.' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_expense.amount_type = "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) operatingExpense on operatingExpense.id = accounting_branch_program.id
                    GROUP BY accounting_branch_program.id
                )'], 'finalOperatingExpense.id = accounting_branch_program.id')
                ->leftJoin(['finalBudgetProposal' => '(
                    SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(budgetProposal.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            (
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
                                    accounting_income.datetime between "'.$date.' 00:00:00" and "'.$date.' 23:59:59" and 
                                    accounting_income.amount_type = "Cash" and
                                    accounting_income_budget_proposal.approval_status = "Approved"
                                GROUP BY newDate, accounting_branch_program.id
                            ) budgetProposal on budgetProposal.id = accounting_branch_program.id
                    GROUP BY accounting_branch_program.id
                )'], 'finalBudgetProposal.id = accounting_branch_program.id')
                ->leftJoin(['finalBranchTransfer' => '(
                    SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(branchTransfer.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$date.' 00:00:00" and "'.$date.' 23:59:59" and 
                                    accounting_expense_branch_transfer.amount_source = "Cash On Hand" and
                                    accounting_expense.amount_type = "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) branchTransfer on branchTransfer.id = accounting_branch_program.id
                    GROUP BY accounting_branch_program.id
                )'], 'finalBranchTransfer.id = accounting_branch_program.id')
                ->where(['accounting_branch_program.id' => $this->id])
                ->asArray()
                ->one();

        $data['beginningcoh'] = $beginningcohAmount;
        $data['cutoff'] = $cutoff;

        return $data;
    }

    // by cutoff
    public function getCashOnBank()
    {
        $cutoff = $this->check_in_cutoff(date('Y-m-d'));
        $dates = $this->dateRange($cutoff['start'], $cutoff['end']);
        $branchProgram = BranchProgram::findOne($this->id);

        $beginningcoh = BeginningCoh::find()->where(['date(datetime)' => $cutoff['start'], 'branch_program_id' => $branchProgram->id])->one();
        $beginningcohAmount = $beginningcoh ? $beginningcoh->cash_on_bank : 0;

        $data = BranchProgram::find()
                ->select([
                    'accounting_branch_program.id as id',
                    'COALESCE(finalIncomeEnrolments.total, 0) as incomeEnrolmentTotal',
                    'COALESCE(finalFreebies.total, 0) as freebiesTotal',
                    'COALESCE(finalPettyExpense.total, 0) as pettyExpenseTotal',
                    'COALESCE(finalPhotocopyExpense.total, 0) as photocopyExpenseTotal',
                    'COALESCE(finalOtherExpense.total, 0) as otherExpenseTotal',
                    'COALESCE(finalBankDeposits.total, 0) as bankDepositsTotal',
                    'COALESCE(finalOperatingExpense.total, 0) as operatingExpenseTotal',
                    'COALESCE(finalBudgetProposal.total, 0) as budgetProposalTotal',
                    'COALESCE(finalBranchTransfer.total, 0) as branchTransferTotal',
                ])
                ->leftJoin(['finalIncomeEnrolments' => '(
                        SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(incomeEnrolments.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN 
                            (
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
                                    accounting_income.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 23:59:59" and
                                    accounting_income.amount_type <> "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) incomeEnrolments on incomeEnrolments.id = accounting_branch_program.id
                        GROUP BY accounting_branch_program.id
                )'], 'finalIncomeEnrolments.id = accounting_branch_program.id')
                ->leftJoin(['finalFreebies' => '(
                        SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(freebies.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            ( 
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
                                    accounting_income.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 23:59:59" and
                                    accounting_income.amount_type <> "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) freebies on freebies.id = accounting_branch_program.id 
                        GROUP BY accounting_branch_program.id
                )'], 'finalFreebies.id = accounting_branch_program.id')
                ->leftJoin(['finalPettyExpense' => '(
                     SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(pettyExpense.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 23:59:59" and
                                    accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) pettyExpense on pettyExpense.id = accounting_branch_program.id
                    GROUP BY accounting_branch_program.id
                )'], 'finalPettyExpense.id = accounting_branch_program.id')
                ->leftJoin(['finalPhotocopyExpense' => '(
                    SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(photocopyExpense.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 23:59:59" and
                                    accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) photocopyExpense on photocopyExpense.id = accounting_branch_program.id
                    GROUP BY accounting_branch_program.id
                )'], 'finalPhotocopyExpense.id = accounting_branch_program.id')
                ->leftJoin(['finalOtherExpense' => '(
                    SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(otherExpense.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 23:59:59" and
                                    accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) otherExpense on otherExpense.id = accounting_branch_program.id
                    GROUP BY accounting_branch_program.id
                )'], 'finalOtherExpense.id = accounting_branch_program.id')
                ->leftJoin(['finalBankDeposits' => '(
                    SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(bankDeposits.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 23:59:59" and
                                    accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) bankDeposits on bankDeposits.id = accounting_branch_program.id
                    GROUP BY accounting_branch_program.id
                )'], 'finalBankDeposits.id = accounting_branch_program.id')
                ->leftJoin(['finalOperatingExpense' => '(
                    SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(operatingExpense.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 23:59:59" and
                                    accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) operatingExpense on operatingExpense.id = accounting_branch_program.id
                    GROUP BY accounting_branch_program.id
                )'], 'finalOperatingExpense.id = accounting_branch_program.id')
                ->leftJoin(['finalBudgetProposal' => '(
                    SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(budgetProposal.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            (
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
                                    accounting_income.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 23:59:59" and 
                                    accounting_income.amount_type <> "Cash" and
                                    accounting_income_budget_proposal.approval_status = "Approved"
                                GROUP BY newDate, accounting_branch_program.id
                            ) budgetProposal on budgetProposal.id = accounting_branch_program.id
                    GROUP BY accounting_branch_program.id
                )'], 'finalBudgetProposal.id = accounting_branch_program.id')
                ->leftJoin(['finalBranchTransfer' => '(
                    SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(branchTransfer.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 23:59:59" and 
                                    accounting_expense_branch_transfer.amount_source = "Cash On Bank" and
                                    accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) branchTransfer on branchTransfer.id = accounting_branch_program.id
                    GROUP BY accounting_branch_program.id
                )'], 'finalBranchTransfer.id = accounting_branch_program.id')
                ->where(['accounting_branch_program.id' => $this->id])
                ->asArray()
                ->one();

        $data['beginningcoh'] = $beginningcohAmount;
        $data['cutoff'] = $cutoff;

        return $data;
    }

    // by cutoff to start of the day
    public function getCashOnBankByStartOfTheDate($date)
    {
        $cutoff = $this->check_in_cutoff($date);
        $dates = $this->dateRange($cutoff['start'], $cutoff['end']);
        $branchProgram = BranchProgram::findOne($this->id);

        $beginningcoh = BeginningCoh::find()->where(['date(datetime)' => $cutoff['start'], 'branch_program_id' => $branchProgram->id])->one();
        $beginningcohAmount = $beginningcoh ? $beginningcoh->cash_on_bank : 0;

        $data = BranchProgram::find()
                ->select([
                    'accounting_branch_program.id as id',
                    'COALESCE(finalIncomeEnrolments.total, 0) as incomeEnrolmentTotal',
                    'COALESCE(finalFreebies.total, 0) as freebiesTotal',
                    'COALESCE(finalPettyExpense.total, 0) as pettyExpenseTotal',
                    'COALESCE(finalPhotocopyExpense.total, 0) as photocopyExpenseTotal',
                    'COALESCE(finalOtherExpense.total, 0) as otherExpenseTotal',
                    'COALESCE(finalBankDeposits.total, 0) as bankDepositsTotal',
                    'COALESCE(finalOperatingExpense.total, 0) as operatingExpenseTotal',
                    'COALESCE(finalBudgetProposal.total, 0) as budgetProposalTotal',
                    'COALESCE(finalBranchTransfer.total, 0) as branchTransferTotal',
                ])
                ->leftJoin(['finalIncomeEnrolments' => '(
                        SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(incomeEnrolments.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN 
                            (
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
                                    accounting_income.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 00:00:00" and
                                    accounting_income.amount_type <> "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) incomeEnrolments on incomeEnrolments.id = accounting_branch_program.id
                        GROUP BY accounting_branch_program.id
                )'], 'finalIncomeEnrolments.id = accounting_branch_program.id')
                ->leftJoin(['finalFreebies' => '(
                        SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(freebies.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            ( 
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
                                    accounting_income.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 00:00:00" and
                                    accounting_income.amount_type <> "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) freebies on freebies.id = accounting_branch_program.id 
                        GROUP BY accounting_branch_program.id
                )'], 'finalFreebies.id = accounting_branch_program.id')
                ->leftJoin(['finalPettyExpense' => '(
                     SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(pettyExpense.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 00:00:00" and
                                    accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) pettyExpense on pettyExpense.id = accounting_branch_program.id
                    GROUP BY accounting_branch_program.id
                )'], 'finalPettyExpense.id = accounting_branch_program.id')
                ->leftJoin(['finalPhotocopyExpense' => '(
                    SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(photocopyExpense.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 00:00:00" and
                                    accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) photocopyExpense on photocopyExpense.id = accounting_branch_program.id
                    GROUP BY accounting_branch_program.id
                )'], 'finalPhotocopyExpense.id = accounting_branch_program.id')
                ->leftJoin(['finalOtherExpense' => '(
                    SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(otherExpense.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 00:00:00" and
                                    accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) otherExpense on otherExpense.id = accounting_branch_program.id
                    GROUP BY accounting_branch_program.id
                )'], 'finalOtherExpense.id = accounting_branch_program.id')
                ->leftJoin(['finalBankDeposits' => '(
                    SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(bankDeposits.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 00:00:00" and
                                    accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) bankDeposits on bankDeposits.id = accounting_branch_program.id
                    GROUP BY accounting_branch_program.id
                )'], 'finalBankDeposits.id = accounting_branch_program.id')
                ->leftJoin(['finalOperatingExpense' => '(
                    SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(operatingExpense.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 00:00:00" and
                                    accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) operatingExpense on operatingExpense.id = accounting_branch_program.id
                    GROUP BY accounting_branch_program.id
                )'], 'finalOperatingExpense.id = accounting_branch_program.id')
                ->leftJoin(['finalBudgetProposal' => '(
                    SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(budgetProposal.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            (
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
                                    accounting_income.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 00:00:00" and 
                                    accounting_income.amount_type <> "Cash" and
                                    accounting_income_budget_proposal.approval_status = "Approved"
                                GROUP BY newDate, accounting_branch_program.id
                            ) budgetProposal on budgetProposal.id = accounting_branch_program.id
                    GROUP BY accounting_branch_program.id
                )'], 'finalBudgetProposal.id = accounting_branch_program.id')
                ->leftJoin(['finalBranchTransfer' => '(
                    SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(branchTransfer.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 00:00:00" and 
                                    accounting_expense_branch_transfer.amount_source = "Cash On Bank" and
                                    accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) branchTransfer on branchTransfer.id = accounting_branch_program.id
                    GROUP BY accounting_branch_program.id
                )'], 'finalBranchTransfer.id = accounting_branch_program.id')
                ->where(['accounting_branch_program.id' => $this->id])
                ->asArray()
                ->one();

        $data['beginningcoh'] = $beginningcohAmount;
        $data['cutoff'] = $cutoff;

        return $data;
    }    

    // by cutoff to end of the day
    public function getCashOnBankByDate($date)
    {
        $cutoff = $this->check_in_cutoff($date);
        $dates = $this->dateRange($cutoff['start'], $cutoff['end']);
        $branchProgram = BranchProgram::findOne($this->id);

        $beginningcoh = BeginningCoh::find()->where(['date(datetime)' => $cutoff['start'], 'branch_program_id' => $branchProgram->id])->one();
        $beginningcohAmount = $beginningcoh ? $beginningcoh->cash_on_bank : 0;

        $data = BranchProgram::find()
                ->select([
                    'accounting_branch_program.id as id',
                    'COALESCE(finalIncomeEnrolments.total, 0) as incomeEnrolmentTotal',
                    'COALESCE(finalFreebies.total, 0) as freebiesTotal',
                    'COALESCE(finalPettyExpense.total, 0) as pettyExpenseTotal',
                    'COALESCE(finalPhotocopyExpense.total, 0) as photocopyExpenseTotal',
                    'COALESCE(finalOtherExpense.total, 0) as otherExpenseTotal',
                    'COALESCE(finalBankDeposits.total, 0) as bankDepositsTotal',
                    'COALESCE(finalOperatingExpense.total, 0) as operatingExpenseTotal',
                    'COALESCE(finalBudgetProposal.total, 0) as budgetProposalTotal',
                    'COALESCE(finalBranchTransfer.total, 0) as branchTransferTotal',
                ])
                ->leftJoin(['finalIncomeEnrolments' => '(
                        SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(incomeEnrolments.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN 
                            (
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
                                    accounting_income.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_income.amount_type <> "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) incomeEnrolments on incomeEnrolments.id = accounting_branch_program.id
                        GROUP BY accounting_branch_program.id
                )'], 'finalIncomeEnrolments.id = accounting_branch_program.id')
                ->leftJoin(['finalFreebies' => '(
                        SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(freebies.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            ( 
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
                                    accounting_income.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_income.amount_type <> "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) freebies on freebies.id = accounting_branch_program.id 
                        GROUP BY accounting_branch_program.id
                )'], 'finalFreebies.id = accounting_branch_program.id')
                ->leftJoin(['finalPettyExpense' => '(
                     SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(pettyExpense.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) pettyExpense on pettyExpense.id = accounting_branch_program.id
                    GROUP BY accounting_branch_program.id
                )'], 'finalPettyExpense.id = accounting_branch_program.id')
                ->leftJoin(['finalPhotocopyExpense' => '(
                    SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(photocopyExpense.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) photocopyExpense on photocopyExpense.id = accounting_branch_program.id
                    GROUP BY accounting_branch_program.id
                )'], 'finalPhotocopyExpense.id = accounting_branch_program.id')
                ->leftJoin(['finalOtherExpense' => '(
                    SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(otherExpense.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) otherExpense on otherExpense.id = accounting_branch_program.id
                    GROUP BY accounting_branch_program.id
                )'], 'finalOtherExpense.id = accounting_branch_program.id')
                ->leftJoin(['finalBankDeposits' => '(
                    SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(bankDeposits.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) bankDeposits on bankDeposits.id = accounting_branch_program.id
                    GROUP BY accounting_branch_program.id
                )'], 'finalBankDeposits.id = accounting_branch_program.id')
                ->leftJoin(['finalOperatingExpense' => '(
                    SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(operatingExpense.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) operatingExpense on operatingExpense.id = accounting_branch_program.id
                    GROUP BY accounting_branch_program.id
                )'], 'finalOperatingExpense.id = accounting_branch_program.id')
                ->leftJoin(['finalBudgetProposal' => '(
                    SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(budgetProposal.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            (
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
                                    accounting_income.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 23:59:59" and 
                                    accounting_income.amount_type <> "Cash" and
                                    accounting_income_budget_proposal.approval_status = "Approved"
                                GROUP BY newDate, accounting_branch_program.id
                            ) budgetProposal on budgetProposal.id = accounting_branch_program.id
                    GROUP BY accounting_branch_program.id
                )'], 'finalBudgetProposal.id = accounting_branch_program.id')
                ->leftJoin(['finalBranchTransfer' => '(
                    SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(branchTransfer.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 23:59:59" and 
                                    accounting_expense_branch_transfer.amount_source = "Cash On Bank" and
                                    accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) branchTransfer on branchTransfer.id = accounting_branch_program.id
                    GROUP BY accounting_branch_program.id
                )'], 'finalBranchTransfer.id = accounting_branch_program.id')
                ->where(['accounting_branch_program.id' => $this->id])
                ->asArray()
                ->one();

        $data['beginningcoh'] = $beginningcohAmount;
        $data['cutoff'] = $cutoff;

        return $data;
    }

    // by day
    public function getCashOnBankByWholeDay($date)
    {
        $cutoff = $this->check_in_cutoff($date);
        $dates = $this->dateRange($cutoff['start'], $cutoff['end']);
        $branchProgram = BranchProgram::findOne($this->id);

        $beginningcoh = BeginningCoh::find()->where(['date(datetime)' => $cutoff['start'], 'branch_program_id' => $branchProgram->id])->one();
        $beginningcohAmount = $beginningcoh ? $beginningcoh->cash_on_bank : 0;

        $data = BranchProgram::find()
                ->select([
                    'accounting_branch_program.id as id',
                    'COALESCE(finalIncomeEnrolments.total, 0) as incomeEnrolmentTotal',
                    'COALESCE(finalFreebies.total, 0) as freebiesTotal',
                    'COALESCE(finalPettyExpense.total, 0) as pettyExpenseTotal',
                    'COALESCE(finalPhotocopyExpense.total, 0) as photocopyExpenseTotal',
                    'COALESCE(finalOtherExpense.total, 0) as otherExpenseTotal',
                    'COALESCE(finalBankDeposits.total, 0) as bankDepositsTotal',
                    'COALESCE(finalOperatingExpense.total, 0) as operatingExpenseTotal',
                    'COALESCE(finalBudgetProposal.total, 0) as budgetProposalTotal',
                    'COALESCE(finalBranchTransfer.total, 0) as branchTransferTotal',
                ])
                ->leftJoin(['finalIncomeEnrolments' => '(
                        SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(incomeEnrolments.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN 
                            (
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
                                    accounting_income.datetime between "'.$date.' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_income.amount_type <> "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) incomeEnrolments on incomeEnrolments.id = accounting_branch_program.id
                        GROUP BY accounting_branch_program.id
                )'], 'finalIncomeEnrolments.id = accounting_branch_program.id')
                ->leftJoin(['finalFreebies' => '(
                        SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(freebies.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            ( 
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
                                    accounting_income.datetime between "'.$date.' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_income.amount_type <> "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) freebies on freebies.id = accounting_branch_program.id 
                        GROUP BY accounting_branch_program.id
                )'], 'finalFreebies.id = accounting_branch_program.id')
                ->leftJoin(['finalPettyExpense' => '(
                     SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(pettyExpense.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$date.' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) pettyExpense on pettyExpense.id = accounting_branch_program.id
                    GROUP BY accounting_branch_program.id
                )'], 'finalPettyExpense.id = accounting_branch_program.id')
                ->leftJoin(['finalPhotocopyExpense' => '(
                    SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(photocopyExpense.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$date.' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) photocopyExpense on photocopyExpense.id = accounting_branch_program.id
                    GROUP BY accounting_branch_program.id
                )'], 'finalPhotocopyExpense.id = accounting_branch_program.id')
                ->leftJoin(['finalOtherExpense' => '(
                    SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(otherExpense.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$date.' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) otherExpense on otherExpense.id = accounting_branch_program.id
                    GROUP BY accounting_branch_program.id
                )'], 'finalOtherExpense.id = accounting_branch_program.id')
                ->leftJoin(['finalBankDeposits' => '(
                    SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(bankDeposits.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$date.' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) bankDeposits on bankDeposits.id = accounting_branch_program.id
                    GROUP BY accounting_branch_program.id
                )'], 'finalBankDeposits.id = accounting_branch_program.id')
                ->leftJoin(['finalOperatingExpense' => '(
                    SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(operatingExpense.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$date.' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) operatingExpense on operatingExpense.id = accounting_branch_program.id
                    GROUP BY accounting_branch_program.id
                )'], 'finalOperatingExpense.id = accounting_branch_program.id')
                ->leftJoin(['finalBudgetProposal' => '(
                    SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(budgetProposal.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            (
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
                                    accounting_income.datetime between "'.$date.' 00:00:00" and "'.$date.' 23:59:59" and 
                                    accounting_income.amount_type <> "Cash" and
                                    accounting_income_budget_proposal.approval_status = "Approved"
                                GROUP BY newDate, accounting_branch_program.id
                            ) budgetProposal on budgetProposal.id = accounting_branch_program.id
                    GROUP BY accounting_branch_program.id
                )'], 'finalBudgetProposal.id = accounting_branch_program.id')
                ->leftJoin(['finalBranchTransfer' => '(
                    SELECT
                            accounting_branch_program.id,
                            sum(COALESCE(branchTransfer.total, 0)) as total
                            from accounting_branch_program
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$date.' 00:00:00" and "'.$date.' 23:59:59" and 
                                    accounting_expense_branch_transfer.amount_source = "Cash On Bank" and
                                    accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate, accounting_branch_program.id
                            ) branchTransfer on branchTransfer.id = accounting_branch_program.id
                    GROUP BY accounting_branch_program.id
                )'], 'finalBranchTransfer.id = accounting_branch_program.id')
                ->where(['accounting_branch_program.id' => $this->id])
                ->asArray()
                ->one();

        $data['beginningcoh'] = $beginningcohAmount;
        $data['cutoff'] = $cutoff;

        return $data;
    }
}
