<?php

namespace common\modules\accounting\models;

use Yii;

/**
 * This is the model class for table "accounting_season".
 *
 * @property int $id
 * @property int $branch_program_id
 * @property string $name
 * @property string $start_date
 * @property string $end_date
 *
 * @property AccountingDiscount[] $accountingDiscounts
 * @property AccountingDropout[] $accountingDropouts
 * @property AccountingEnhancement[] $accountingEnhancements
 * @property AccountingIncomeEnrolment[] $accountingIncomeEnrolments
 * @property AccountingIncomeFreebiesAndIcons[] $accountingIncomeFreebiesAndIcons
 * @property AccountingPackageStudent[] $accountingPackageStudents
 * @property AccountingBranchProgram $branchProgram
 * @property AccountingStudentTuition[] $accountingStudentTuitions
 * @property AccountingTransferee[] $accountingTransferees
 */
class Season extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'accounting_season';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['branch_program_id', 'name', 'start_date', 'end_date', 'or_start', 'no_of_pieces'], 'required'],
            [['branch_program_id', 'name'], 'integer'],
            [['start_date', 'end_date'], 'safe'],
            [['branch_program_id'], 'exist', 'skipOnError' => true, 'targetClass' => BranchProgram::className(), 'targetAttribute' => ['branch_program_id' => 'id']],
        ];
    }

    public function behaviors()
    {
        return [
            'bedezign\yii2\audit\AuditTrailBehavior'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'branch_program_id' => 'Branch - Program',
            'branchProgramName' => 'Branch - Program',
            'name' => 'Title',
            'newName' => 'Title',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'or_start' => 'OR (Start Number)',
            'no_of_pieces' => 'No. of Pieces',
        ];
    }

    public function getHiddenFormTokenField() {
        $token = \Yii::$app->getSecurity()->generateRandomString();
        $token = str_replace('+', '.', base64_encode($token));

        \Yii::$app->session->set(\Yii::$app->params['form_token_param'], $token);;
        return Html::hiddenInput(\Yii::$app->params['form_token_param'], $token);
    }

    public function getSeasonName()
    {
        return $this->branchProgramName.' - SEASON '.$this->name;
    }

    public function getNewName()
    {
        return 'SEASON '.$this->name;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDiscounts()
    {
        return $this->hasMany(Discount::className(), ['season_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDropouts()
    {
        return $this->hasMany(Dropout::className(), ['season_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEnhancements()
    {
        return $this->hasMany(Enhancement::className(), ['season_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEnrolments()
    {
        return $this->hasMany(Enrolment::className(), ['season_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFreebieAndIcons()
    {
        return $this->hasMany(FreebieAndIcons::className(), ['season_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPackageStudents()
    {
        return $this->hasMany(PackageStudent::className(), ['season_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAdvanceEnrolments()
    {
        return $this->hasMany(AdvanceEnrolment::className(), ['season_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBranchProgram()
    {
        return $this->hasOne(BranchProgram::className(), ['id' => 'branch_program_id']);
    }

    public function getBranchProgramName()
    {
        return $this->branchProgram ? $this->branchProgram->branchProgramName : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStudentTuitions()
    {
        return $this->hasMany(StudentTuition::className(), ['season_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTransferees()
    {
        return $this->hasMany(Transferee::className(), ['season_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCoachings()
    {
        return $this->hasMany(Coaching::className(), ['season_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTargetIncomes()
    {
        return $this->hasMany(TargetIncome::className(), ['season_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTargetPrograms()
    {
        return $this->hasMany(TargetProgram::className(), ['season_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTargetVenueRentals()
    {
        return $this->hasMany(TargetVenueRental::className(), ['season_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTargetFreebies()
    {
        return $this->hasMany(TargetFreebie::className(), ['season_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTargetReviews()
    {
        return $this->hasMany(TargetReview::className(), ['season_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTargetFoods()
    {
        return $this->hasMany(TargetFood::className(), ['season_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTargetTransportations()
    {
        return $this->hasMany(TargetTransportation::className(), ['season_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTargetStaffSalaries()
    {
        return $this->hasMany(TargetStaffSalary::className(), ['season_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTargetRebates()
    {
        return $this->hasMany(TargetRebate::className(), ['season_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTargetUtilities()
    {
        return $this->hasMany(TargetUtility::className(), ['season_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTargetAcademics()
    {
        return $this->hasMany(TargetAcademic::className(), ['season_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTargetEmergencyFunds()
    {
        return $this->hasMany(TargetEmergencyFund::className(), ['season_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTargetRoyaltyFees()
    {
        return $this->hasMany(TargetRoyaltyFee::className(), ['season_id' => 'id']);
    }

    public function getTotalGross()
    {
        $gross = TargetIncome::find()->select(['sum(quantity*unit_price) as total'])->where(['season_id' => $this->id])->asArray()->one();

        return $gross['total'];
    }

    public function getTotalStudents()
    {
        $student = TargetIncome::find()->select(['sum(quantity) as total'])->where(['season_id' => $this->id])->asArray()->one();

        return $student['total'];
    }

    public function getTotalGrossIncome()
    {
        $total = 0;

        $gross = TargetIncome::find()->select(['sum(quantity*unit_price) as total'])->where(['season_id' => $this->id])->asArray()->one();

        $total = $gross['total'] - (($gross['total']/1.12)*0.12);

        return $total;
    }

    public function getTotalPrograms()
    {
        $programs = TargetProgram::find()->select(['sum(quantity*unit_price) as total'])->where(['season_id' => $this->id])->asArray()->one();

        return $programs['total'];
    }

    public function getTotalVenueRentals()
    {
        $venueRentals = TargetVenueRental::find()->select(['sum(quantity*unit_price) as total'])->where(['season_id' => $this->id])->asArray()->one();

        return $venueRentals['total'];
    }

    public function getTotalFreebies()
    {
        $freebies = TargetFreebie::find()->select(['sum(quantity*unit_price) as total'])->where(['season_id' => $this->id])->asArray()->one();

        return $freebies['total'];
    }

    public function getTotalReviews()
    {
        $reviewMaterials = TargetReview::find()->select(['sum(quantity*unit_price) as total'])->where(['season_id' => $this->id])->asArray()->one();

        return $reviewMaterials['total'];
    }

    public function getTotalFoods()
    {
        $foods = TargetFood::find()->select(['sum(quantity*unit_price) as total'])->where(['season_id' => $this->id])->asArray()->one();

        return $foods['total'];
    }

    public function getTotalTransportations()
    {
        $transportations = TargetTransportation::find()->select(['sum(quantity*unit_price) as total'])->where(['season_id' => $this->id])->asArray()->one();

        return $transportations['total'];
    }

    public function getTotalStaffSalaries()
    {
        $staffSalaries = TargetStaffSalary::find()->select(['sum(quantity*unit_price) as total'])->where(['season_id' => $this->id])->asArray()->one();

        return $staffSalaries['total'];
    }

    public function getTotalRebates()
    {
        $rebates = TargetRebate::find()->select(['sum(quantity*unit_price) as total'])->where(['season_id' => $this->id])->asArray()->one();

        return $rebates['total'];
    }

    public function getTotalUtilities()
    {
        $utilities = TargetUtility::find()->select(['sum(quantity*unit_price) as total'])->where(['season_id' => $this->id])->asArray()->one();

        return $utilities['total'];
    }

    public function getTotalAcademics()
    {
        $academics = TargetAcademic::find()->select(['sum(quantity*unit_price) as total'])->where(['season_id' => $this->id])->asArray()->one();

        return $academics['total'];
    }

    public function getTotalEmergencyFunds()
    {
        $emergencyFunds = TargetEmergencyFund::find()->select(['sum(quantity*unit_price) as total'])->where(['season_id' => $this->id])->asArray()->one();

        return $emergencyFunds['total'];
    }

    public function getTotalExpenses()
    {
        $total = 0;

        $programs = TargetProgram::find()->select(['sum(quantity*unit_price) as total'])->where(['season_id' => $this->id])->asArray()->one();
        $venueRentals = TargetVenueRental::find()->select(['sum(quantity*unit_price) as total'])->where(['season_id' => $this->id])->asArray()->one();
        $freebies = TargetFreebie::find()->select(['sum(quantity*unit_price) as total'])->where(['season_id' => $this->id])->asArray()->one();
        $reviewMaterials = TargetReview::find()->select(['sum(quantity*unit_price) as total'])->where(['season_id' => $this->id])->asArray()->one();
        $foods = TargetFood::find()->select(['sum(quantity*unit_price) as total'])->where(['season_id' => $this->id])->asArray()->one();
        $transportations = TargetTransportation::find()->select(['sum(quantity*unit_price) as total'])->where(['season_id' => $this->id])->asArray()->one();
        $staffSalaries = TargetStaffSalary::find()->select(['sum(quantity*unit_price) as total'])->where(['season_id' => $this->id])->asArray()->one();
        $rebates = TargetRebate::find()->select(['sum(quantity*unit_price) as total'])->where(['season_id' => $this->id])->asArray()->one();
        $utilities = TargetUtility::find()->select(['sum(quantity*unit_price) as total'])->where(['season_id' => $this->id])->asArray()->one();
        $academics = TargetAcademic::find()->select(['sum(quantity*unit_price) as total'])->where(['season_id' => $this->id])->asArray()->one();
        $emergencyFunds = TargetEmergencyFund::find()->select(['sum(quantity*unit_price) as total'])->where(['season_id' => $this->id])->asArray()->one();

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

        $royaltyFees = TargetRoyaltyFee::find()->select(['percentage'])->where(['season_id' => $this->id])->asArray()->one();

        $total = $this->expectedIncome - ($this->expectedIncome * $royaltyFees['percentage']);

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
        $season = Season::findOne($this->id);

        $beginningcoh = BeginningCoh::find()->where(['date(datetime)' => $cutoff['start'], 'season_id' => $season->id])->one();
        $beginningcohAmount = $beginningcoh ? $beginningcoh->cash_on_hand : 0;

        $data = Season::find()
                ->select([
                    'accounting_season.id as id',
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
                            accounting_season.id,
                            sum(COALESCE(incomeEnrolments.total, 0)) as total
                            from accounting_season
                            LEFT JOIN 
                            (
                                SELECT 
                                    accounting_income_enrolment.season_id,
                                    sum(COALESCE(accounting_income_enrolment.amount, 0)) as total,
                                    DATE(accounting_income.datetime) as newDate,
                                    accounting_income.amount_type as amountType
                                from accounting_income_enrolment
                                LEFT JOIN accounting_income on accounting_income.income_id = accounting_income_enrolment.id and accounting_income.income_type_id = 1
                                LEFT JOIN accounting_season on accounting_season.id = accounting_income_enrolment.season_id
                                WHERE 
                                    accounting_income.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 23:59:59" and
                                    accounting_income.amount_type = "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) incomeEnrolments on incomeEnrolments.season_id = accounting_season.id
                        GROUP BY accounting_season.id
                )'], 'finalIncomeEnrolments.id = accounting_season.id')
                ->leftJoin(['finalFreebies' => '(
                        SELECT
                            accounting_season.id,
                            sum(COALESCE(freebies.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            ( 
                                SELECT 
                                    accounting_season.id,
                                    sum(COALESCE(accounting_income_freebies_and_icons.amount, 0)) as total,
                                    DATE(accounting_income.datetime) as newDate,
                                    accounting_income.amount_type as amountType
                                from accounting_income_freebies_and_icons
                                LEFT JOIN accounting_income on accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2
                                LEFT JOIN accounting_season on accounting_season.id = accounting_income_freebies_and_icons.season_id
                                WHERE 
                                    accounting_income.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 23:59:59" and
                                    accounting_income.amount_type = "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) freebies on freebies.id = accounting_season.id
                        GROUP BY accounting_season.id
                )'], 'finalFreebies.id = accounting_season.id')
                ->leftJoin(['finalPettyExpense' => '(
                    SELECT
                            accounting_season.id,
                            sum(COALESCE(pettyExpense.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 23:59:59" and
                                    accounting_expense.amount_type = "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) pettyExpense on pettyExpense.id = accounting_season.id
                    GROUP BY accounting_season.id
                )'], 'finalPettyExpense.id = accounting_season.id')
                ->leftJoin(['finalPhotocopyExpense' => '(
                    SELECT
                            accounting_season.id,
                            sum(COALESCE(photocopyExpense.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_season.id,
                                    sum(COALESCE(accounting_expense_photocopy_expense.total_amount, 0)) as total,
                                    DATE(accounting_expense.datetime) as newDate,
                                    accounting_expense.amount_type as amountType
                                from accounting_expense_photocopy_expense
                                LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2
                                LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                                WHERE 
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 23:59:59" and
                                    accounting_expense.amount_type = "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) photocopyExpense on photocopyExpense.id =  accounting_season.id
                    GROUP BY  accounting_season.id
                )'], 'finalPhotocopyExpense.id =  accounting_season.id')
                ->leftJoin(['finalOtherExpense' => '(
                    SELECT
                            accounting_season.id,
                            sum(COALESCE(otherExpense.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_season.id,
                                    sum(COALESCE(accounting_expense_other_expense.amount, 0)) as total,
                                    DATE(accounting_expense.datetime) as newDate,
                                    accounting_expense.amount_type as amountType
                                from accounting_expense_other_expense
                                LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_other_expense.id and accounting_expense.expense_type_id = 3
                                LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                                WHERE 
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 23:59:59" and
                                    accounting_expense.amount_type = "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) otherExpense on otherExpense.id = accounting_season.id
                    GROUP BY accounting_season.id
                )'], 'finalOtherExpense.id = accounting_season.id')
                ->leftJoin(['finalBankDeposits' => '(
                    SELECT
                            accounting_season.id,
                            sum(COALESCE(bankDeposits.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_season.id,
                                    sum(COALESCE(accounting_expense_bank_deposit.amount, 0)) as total,
                                    DATE(accounting_expense.datetime) as newDate,
                                    accounting_expense.amount_type as amountType
                                from accounting_expense_bank_deposit
                                LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_bank_deposit.id and accounting_expense.expense_type_id = 4
                                LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                                WHERE 
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 23:59:59" and
                                    accounting_expense.amount_type = "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) bankDeposits on bankDeposits.id = accounting_season.id
                    GROUP BY accounting_season.id
                )'], 'finalBankDeposits.id = accounting_season.id')
                ->leftJoin(['finalOperatingExpense' => '(
                    SELECT
                            accounting_season.id,
                            sum(COALESCE(operatingExpense.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 23:59:59" and
                                    accounting_expense.amount_type = "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) operatingExpense on operatingExpense.id = accounting_season.id
                    GROUP BY accounting_season.id
                )'], 'finalOperatingExpense.id = accounting_season.id')
                ->leftJoin(['finalBudgetProposal' => '(
                    SELECT
                            accounting_season.id,
                            sum(COALESCE(budgetProposal.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            (
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
                                    accounting_income.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 23:59:59" and 
                                    accounting_income.amount_type = "Cash" and
                                    accounting_income_budget_proposal.approval_status = "Approved"
                                GROUP BY newDate, accounting_season.id
                            ) budgetProposal on budgetProposal.id = accounting_season.id
                    GROUP BY accounting_season.id
                )'], 'finalBudgetProposal.id = accounting_season.id')
                ->leftJoin(['finalBranchTransfer' => '(
                    SELECT
                            accounting_season.id,
                            sum(COALESCE(branchTransfer.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 23:59:59" and 
                                    accounting_expense_branch_transfer.amount_source = "Cash On Hand" and
                                    accounting_expense.amount_type = "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) branchTransfer on branchTransfer.id = accounting_season.id
                    GROUP BY accounting_season.id
                )'], 'finalBranchTransfer.id = accounting_season.id')
                ->where(['accounting_season.id' => $this->id])
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
        $season = Season::findOne($this->id);

        $beginningcoh = BeginningCoh::find()->where(['date(datetime)' => $cutoff['start'], 'season_id' => $season->id])->one();
        $beginningcohAmount = $beginningcoh ? $beginningcoh->cash_on_hand : 0;

        $data = Season::find()
                ->select([
                    'accounting_season.id as id',
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
                            accounting_season.id,
                            sum(COALESCE(incomeEnrolments.total, 0)) as total
                            from accounting_season
                            LEFT JOIN 
                            (
                                SELECT 
                                    accounting_income_enrolment.season_id,
                                    sum(COALESCE(accounting_income_enrolment.amount, 0)) as total,
                                    DATE(accounting_income.datetime) as newDate,
                                    accounting_income.amount_type as amountType
                                from accounting_income_enrolment
                                LEFT JOIN accounting_income on accounting_income.income_id = accounting_income_enrolment.id and accounting_income.income_type_id = 1
                                LEFT JOIN accounting_season on accounting_season.id = accounting_income_enrolment.season_id
                                WHERE 
                                    accounting_income.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 00:00:00" and
                                    accounting_income.amount_type = "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) incomeEnrolments on incomeEnrolments.season_id = accounting_season.id
                        GROUP BY accounting_season.id
                )'], 'finalIncomeEnrolments.id = accounting_season.id')
                ->leftJoin(['finalFreebies' => '(
                        SELECT
                            accounting_season.id,
                            sum(COALESCE(freebies.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            ( 
                                SELECT 
                                    accounting_season.id,
                                    sum(COALESCE(accounting_income_freebies_and_icons.amount, 0)) as total,
                                    DATE(accounting_income.datetime) as newDate,
                                    accounting_income.amount_type as amountType
                                from accounting_income_freebies_and_icons
                                LEFT JOIN accounting_income on accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2
                                LEFT JOIN accounting_season on accounting_season.id = accounting_income_freebies_and_icons.season_id
                                WHERE 
                                    accounting_income.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 00:00:00" and
                                    accounting_income.amount_type = "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) freebies on freebies.id = accounting_season.id
                        GROUP BY accounting_season.id
                )'], 'finalFreebies.id = accounting_season.id')
                ->leftJoin(['finalPettyExpense' => '(
                    SELECT
                            accounting_season.id,
                            sum(COALESCE(pettyExpense.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 00:00:00" and
                                    accounting_expense.amount_type = "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) pettyExpense on pettyExpense.id = accounting_season.id
                    GROUP BY accounting_season.id
                )'], 'finalPettyExpense.id = accounting_season.id')
                ->leftJoin(['finalPhotocopyExpense' => '(
                    SELECT
                            accounting_season.id,
                            sum(COALESCE(photocopyExpense.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_season.id,
                                    sum(COALESCE(accounting_expense_photocopy_expense.total_amount, 0)) as total,
                                    DATE(accounting_expense.datetime) as newDate,
                                    accounting_expense.amount_type as amountType
                                from accounting_expense_photocopy_expense
                                LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2
                                LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                                WHERE 
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 00:00:00" and
                                    accounting_expense.amount_type = "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) photocopyExpense on photocopyExpense.id =  accounting_season.id
                    GROUP BY  accounting_season.id
                )'], 'finalPhotocopyExpense.id =  accounting_season.id')
                ->leftJoin(['finalOtherExpense' => '(
                    SELECT
                            accounting_season.id,
                            sum(COALESCE(otherExpense.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_season.id,
                                    sum(COALESCE(accounting_expense_other_expense.amount, 0)) as total,
                                    DATE(accounting_expense.datetime) as newDate,
                                    accounting_expense.amount_type as amountType
                                from accounting_expense_other_expense
                                LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_other_expense.id and accounting_expense.expense_type_id = 3
                                LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                                WHERE 
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 00:00:00" and
                                    accounting_expense.amount_type = "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) otherExpense on otherExpense.id = accounting_season.id
                    GROUP BY accounting_season.id
                )'], 'finalOtherExpense.id = accounting_season.id')
                ->leftJoin(['finalBankDeposits' => '(
                    SELECT
                            accounting_season.id,
                            sum(COALESCE(bankDeposits.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_season.id,
                                    sum(COALESCE(accounting_expense_bank_deposit.amount, 0)) as total,
                                    DATE(accounting_expense.datetime) as newDate,
                                    accounting_expense.amount_type as amountType
                                from accounting_expense_bank_deposit
                                LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_bank_deposit.id and accounting_expense.expense_type_id = 4
                                LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                                WHERE 
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 00:00:00" and
                                    accounting_expense.amount_type = "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) bankDeposits on bankDeposits.id = accounting_season.id
                    GROUP BY accounting_season.id
                )'], 'finalBankDeposits.id = accounting_season.id')
                ->leftJoin(['finalOperatingExpense' => '(
                    SELECT
                            accounting_season.id,
                            sum(COALESCE(operatingExpense.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 00:00:00" and
                                    accounting_expense.amount_type = "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) operatingExpense on operatingExpense.id = accounting_season.id
                    GROUP BY accounting_season.id
                )'], 'finalOperatingExpense.id = accounting_season.id')
                ->leftJoin(['finalBudgetProposal' => '(
                    SELECT
                            accounting_season.id,
                            sum(COALESCE(budgetProposal.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            (
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
                                    accounting_income.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 00:00:00" and 
                                    accounting_income.amount_type = "Cash" and
                                    accounting_income_budget_proposal.approval_status = "Approved"
                                GROUP BY newDate, accounting_season.id
                            ) budgetProposal on budgetProposal.id = accounting_season.id
                    GROUP BY accounting_season.id
                )'], 'finalBudgetProposal.id = accounting_season.id')
                ->leftJoin(['finalBranchTransfer' => '(
                    SELECT
                            accounting_season.id,
                            sum(COALESCE(branchTransfer.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 00:00:00" and 
                                    accounting_expense_branch_transfer.amount_source = "Cash On Hand" and
                                    accounting_expense.amount_type = "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) branchTransfer on branchTransfer.id = accounting_season.id
                    GROUP BY accounting_season.id
                )'], 'finalBranchTransfer.id = accounting_season.id')
                ->where(['accounting_season.id' => $this->id])
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
        $season = Season::findOne($this->id);

        $beginningcoh = BeginningCoh::find()->where(['date(datetime)' => $cutoff['start'], 'season_id' => $season->id])->one();
        $beginningcohAmount = $beginningcoh ? $beginningcoh->cash_on_hand : 0;

        $data = Season::find()
                ->select([
                    'accounting_season.id as id',
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
                            accounting_season.id,
                            sum(COALESCE(incomeEnrolments.total, 0)) as total
                            from accounting_season
                            LEFT JOIN 
                            (
                                SELECT 
                                    accounting_income_enrolment.season_id,
                                    sum(COALESCE(accounting_income_enrolment.amount, 0)) as total,
                                    DATE(accounting_income.datetime) as newDate,
                                    accounting_income.amount_type as amountType
                                from accounting_income_enrolment
                                LEFT JOIN accounting_income on accounting_income.income_id = accounting_income_enrolment.id and accounting_income.income_type_id = 1
                                LEFT JOIN accounting_season on accounting_season.id = accounting_income_enrolment.season_id
                                WHERE 
                                    accounting_income.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_income.amount_type = "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) incomeEnrolments on incomeEnrolments.season_id = accounting_season.id
                        GROUP BY accounting_season.id
                )'], 'finalIncomeEnrolments.id = accounting_season.id')
                ->leftJoin(['finalFreebies' => '(
                        SELECT
                            accounting_season.id,
                            sum(COALESCE(freebies.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            ( 
                                SELECT 
                                    accounting_season.id,
                                    sum(COALESCE(accounting_income_freebies_and_icons.amount, 0)) as total,
                                    DATE(accounting_income.datetime) as newDate,
                                    accounting_income.amount_type as amountType
                                from accounting_income_freebies_and_icons
                                LEFT JOIN accounting_income on accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2
                                LEFT JOIN accounting_season on accounting_season.id = accounting_income_freebies_and_icons.season_id
                                WHERE 
                                    accounting_income.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_income.amount_type = "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) freebies on freebies.id = accounting_season.id
                        GROUP BY accounting_season.id
                )'], 'finalFreebies.id = accounting_season.id')
                ->leftJoin(['finalPettyExpense' => '(
                    SELECT
                            accounting_season.id,
                            sum(COALESCE(pettyExpense.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_expense.amount_type = "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) pettyExpense on pettyExpense.id = accounting_season.id
                    GROUP BY accounting_season.id
                )'], 'finalPettyExpense.id = accounting_season.id')
                ->leftJoin(['finalPhotocopyExpense' => '(
                    SELECT
                            accounting_season.id,
                            sum(COALESCE(photocopyExpense.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_season.id,
                                    sum(COALESCE(accounting_expense_photocopy_expense.total_amount, 0)) as total,
                                    DATE(accounting_expense.datetime) as newDate,
                                    accounting_expense.amount_type as amountType
                                from accounting_expense_photocopy_expense
                                LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2
                                LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                                WHERE 
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_expense.amount_type = "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) photocopyExpense on photocopyExpense.id =  accounting_season.id
                    GROUP BY  accounting_season.id
                )'], 'finalPhotocopyExpense.id =  accounting_season.id')
                ->leftJoin(['finalOtherExpense' => '(
                    SELECT
                            accounting_season.id,
                            sum(COALESCE(otherExpense.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_season.id,
                                    sum(COALESCE(accounting_expense_other_expense.amount, 0)) as total,
                                    DATE(accounting_expense.datetime) as newDate,
                                    accounting_expense.amount_type as amountType
                                from accounting_expense_other_expense
                                LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_other_expense.id and accounting_expense.expense_type_id = 3
                                LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                                WHERE 
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 23:59:59" and
                                    accounting_expense.amount_type = "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) otherExpense on otherExpense.id = accounting_season.id
                    GROUP BY accounting_season.id
                )'], 'finalOtherExpense.id = accounting_season.id')
                ->leftJoin(['finalBankDeposits' => '(
                    SELECT
                            accounting_season.id,
                            sum(COALESCE(bankDeposits.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_season.id,
                                    sum(COALESCE(accounting_expense_bank_deposit.amount, 0)) as total,
                                    DATE(accounting_expense.datetime) as newDate,
                                    accounting_expense.amount_type as amountType
                                from accounting_expense_bank_deposit
                                LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_bank_deposit.id and accounting_expense.expense_type_id = 4
                                LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                                WHERE 
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_expense.amount_type = "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) bankDeposits on bankDeposits.id = accounting_season.id
                    GROUP BY accounting_season.id
                )'], 'finalBankDeposits.id = accounting_season.id')
                ->leftJoin(['finalOperatingExpense' => '(
                    SELECT
                            accounting_season.id,
                            sum(COALESCE(operatingExpense.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_expense.amount_type = "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) operatingExpense on operatingExpense.id = accounting_season.id
                    GROUP BY accounting_season.id
                )'], 'finalOperatingExpense.id = accounting_season.id')
                ->leftJoin(['finalBudgetProposal' => '(
                    SELECT
                            accounting_season.id,
                            sum(COALESCE(budgetProposal.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            (
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
                                    accounting_income.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 23:59:59" and 
                                    accounting_income.amount_type = "Cash" and
                                    accounting_income_budget_proposal.approval_status = "Approved"
                                GROUP BY newDate, accounting_season.id
                            ) budgetProposal on budgetProposal.id = accounting_season.id
                    GROUP BY accounting_season.id
                )'], 'finalBudgetProposal.id = accounting_season.id')
                ->leftJoin(['finalBranchTransfer' => '(
                    SELECT
                            accounting_season.id,
                            sum(COALESCE(branchTransfer.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 23:59:59" and 
                                    accounting_expense_branch_transfer.amount_source = "Cash On Hand" and
                                    accounting_expense.amount_type = "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) branchTransfer on branchTransfer.id = accounting_season.id
                    GROUP BY accounting_season.id
                )'], 'finalBranchTransfer.id = accounting_season.id')
                ->where(['accounting_season.id' => $this->id])
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
        $season = Season::findOne($this->id);

        $beginningcoh = BeginningCoh::find()->where(['date(datetime)' => $cutoff['start'], 'season_id' => $season->id])->one();
        $beginningcohAmount = $beginningcoh ? $beginningcoh->cash_on_hand : 0;

        $data = Season::find()
                ->select([
                    'accounting_season.id as id',
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
                            accounting_season.id,
                            sum(COALESCE(incomeEnrolments.total, 0)) as total
                            from accounting_season
                            LEFT JOIN 
                            (
                                SELECT 
                                    accounting_income_enrolment.season_id,
                                    sum(COALESCE(accounting_income_enrolment.amount, 0)) as total,
                                    DATE(accounting_income.datetime) as newDate,
                                    accounting_income.amount_type as amountType
                                from accounting_income_enrolment
                                LEFT JOIN accounting_income on accounting_income.income_id = accounting_income_enrolment.id and accounting_income.income_type_id = 1
                                LEFT JOIN accounting_season on accounting_season.id = accounting_income_enrolment.season_id
                                WHERE 
                                    accounting_income.datetime between "'.$date.' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_income.amount_type = "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) incomeEnrolments on incomeEnrolments.season_id = accounting_season.id
                        GROUP BY accounting_season.id
                )'], 'finalIncomeEnrolments.id = accounting_season.id')
                ->leftJoin(['finalFreebies' => '(
                        SELECT
                            accounting_season.id,
                            sum(COALESCE(freebies.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            ( 
                                SELECT 
                                    accounting_season.id,
                                    sum(COALESCE(accounting_income_freebies_and_icons.amount, 0)) as total,
                                    DATE(accounting_income.datetime) as newDate,
                                    accounting_income.amount_type as amountType
                                from accounting_income_freebies_and_icons
                                LEFT JOIN accounting_income on accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2
                                LEFT JOIN accounting_season on accounting_season.id = accounting_income_freebies_and_icons.season_id
                                WHERE 
                                    accounting_income.datetime between "'.$date.' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_income.amount_type = "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) freebies on freebies.id = accounting_season.id
                        GROUP BY accounting_season.id
                )'], 'finalFreebies.id = accounting_season.id')
                ->leftJoin(['finalPettyExpense' => '(
                    SELECT
                            accounting_season.id,
                            sum(COALESCE(pettyExpense.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$date.' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_expense.amount_type = "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) pettyExpense on pettyExpense.id = accounting_season.id
                    GROUP BY accounting_season.id
                )'], 'finalPettyExpense.id = accounting_season.id')
                ->leftJoin(['finalPhotocopyExpense' => '(
                    SELECT
                            accounting_season.id,
                            sum(COALESCE(photocopyExpense.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_season.id,
                                    sum(COALESCE(accounting_expense_photocopy_expense.total_amount, 0)) as total,
                                    DATE(accounting_expense.datetime) as newDate,
                                    accounting_expense.amount_type as amountType
                                from accounting_expense_photocopy_expense
                                LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2
                                LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                                WHERE 
                                    accounting_expense.datetime between "'.$date.' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_expense.amount_type = "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) photocopyExpense on photocopyExpense.id =  accounting_season.id
                    GROUP BY  accounting_season.id
                )'], 'finalPhotocopyExpense.id =  accounting_season.id')
                ->leftJoin(['finalOtherExpense' => '(
                    SELECT
                            accounting_season.id,
                            sum(COALESCE(otherExpense.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_season.id,
                                    sum(COALESCE(accounting_expense_other_expense.amount, 0)) as total,
                                    DATE(accounting_expense.datetime) as newDate,
                                    accounting_expense.amount_type as amountType
                                from accounting_expense_other_expense
                                LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_other_expense.id and accounting_expense.expense_type_id = 3
                                LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                                WHERE 
                                    accounting_expense.datetime between "'.$date.' 00:00:00" and "'.$cutoff['end'].' 23:59:59" and
                                    accounting_expense.amount_type = "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) otherExpense on otherExpense.id = accounting_season.id
                    GROUP BY accounting_season.id
                )'], 'finalOtherExpense.id = accounting_season.id')
                ->leftJoin(['finalBankDeposits' => '(
                    SELECT
                            accounting_season.id,
                            sum(COALESCE(bankDeposits.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_season.id,
                                    sum(COALESCE(accounting_expense_bank_deposit.amount, 0)) as total,
                                    DATE(accounting_expense.datetime) as newDate,
                                    accounting_expense.amount_type as amountType
                                from accounting_expense_bank_deposit
                                LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_bank_deposit.id and accounting_expense.expense_type_id = 4
                                LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                                WHERE 
                                    accounting_expense.datetime between "'.$date.' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_expense.amount_type = "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) bankDeposits on bankDeposits.id = accounting_season.id
                    GROUP BY accounting_season.id
                )'], 'finalBankDeposits.id = accounting_season.id')
                ->leftJoin(['finalOperatingExpense' => '(
                    SELECT
                            accounting_season.id,
                            sum(COALESCE(operatingExpense.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$date.' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_expense.amount_type = "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) operatingExpense on operatingExpense.id = accounting_season.id
                    GROUP BY accounting_season.id
                )'], 'finalOperatingExpense.id = accounting_season.id')
                ->leftJoin(['finalBudgetProposal' => '(
                    SELECT
                            accounting_season.id,
                            sum(COALESCE(budgetProposal.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            (
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
                                    accounting_income.datetime between "'.$date.' 00:00:00" and "'.$date.' 23:59:59" and 
                                    accounting_income.amount_type = "Cash" and
                                    accounting_income_budget_proposal.approval_status = "Approved"
                                GROUP BY newDate, accounting_season.id
                            ) budgetProposal on budgetProposal.id = accounting_season.id
                    GROUP BY accounting_season.id
                )'], 'finalBudgetProposal.id = accounting_season.id')
                ->leftJoin(['finalBranchTransfer' => '(
                    SELECT
                            accounting_season.id,
                            sum(COALESCE(branchTransfer.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$date.' 00:00:00" and "'.$date.' 23:59:59" and 
                                    accounting_expense_branch_transfer.amount_source = "Cash On Hand" and
                                    accounting_expense.amount_type = "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) branchTransfer on branchTransfer.id = accounting_season.id
                    GROUP BY accounting_season.id
                )'], 'finalBranchTransfer.id = accounting_season.id')
                ->where(['accounting_season.id' => $this->id])
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
        $season = Season::findOne($this->id);

        $beginningcoh = BeginningCoh::find()->where(['date(datetime)' => $cutoff['start'], 'season_id' => $season->id])->one();
        $beginningcohAmount = $beginningcoh ? $beginningcoh->cash_on_bank : 0;

        $data = Season::find()
                ->select([
                    'accounting_season.id as id',
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
                            accounting_season.id,
                            sum(COALESCE(incomeEnrolments.total, 0)) as total
                            from accounting_season
                            LEFT JOIN 
                            (
                                SELECT 
                                    accounting_income_enrolment.season_id,
                                    sum(COALESCE(accounting_income_enrolment.amount, 0)) as total,
                                    DATE(accounting_income.datetime) as newDate,
                                    accounting_income.amount_type as amountType
                                from accounting_income_enrolment
                                LEFT JOIN accounting_income on accounting_income.income_id = accounting_income_enrolment.id and accounting_income.income_type_id = 1
                                LEFT JOIN accounting_season on accounting_season.id = accounting_income_enrolment.season_id
                                WHERE 
                                    accounting_income.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 23:59:59" and
                                    accounting_income.amount_type <> "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) incomeEnrolments on incomeEnrolments.season_id = accounting_season.id
                        GROUP BY accounting_season.id
                )'], 'finalIncomeEnrolments.id = accounting_season.id')
                ->leftJoin(['finalFreebies' => '(
                        SELECT
                            accounting_season.id,
                            sum(COALESCE(freebies.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            ( 
                                SELECT 
                                    accounting_season.id,
                                    sum(COALESCE(accounting_income_freebies_and_icons.amount, 0)) as total,
                                    DATE(accounting_income.datetime) as newDate,
                                    accounting_income.amount_type as amountType
                                from accounting_income_freebies_and_icons
                                LEFT JOIN accounting_income on accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2
                                LEFT JOIN accounting_season on accounting_season.id = accounting_income_freebies_and_icons.season_id
                                WHERE 
                                    accounting_income.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 23:59:59" and
                                    accounting_income.amount_type <> "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) freebies on freebies.id = accounting_season.id
                        GROUP BY accounting_season.id
                )'], 'finalFreebies.id = accounting_season.id')
                ->leftJoin(['finalPettyExpense' => '(
                    SELECT
                            accounting_season.id,
                            sum(COALESCE(pettyExpense.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 23:59:59" and
                                    accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) pettyExpense on pettyExpense.id = accounting_season.id
                    GROUP BY accounting_season.id
                )'], 'finalPettyExpense.id = accounting_season.id')
                ->leftJoin(['finalPhotocopyExpense' => '(
                    SELECT
                            accounting_season.id,
                            sum(COALESCE(photocopyExpense.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_season.id,
                                    sum(COALESCE(accounting_expense_photocopy_expense.total_amount, 0)) as total,
                                    DATE(accounting_expense.datetime) as newDate,
                                    accounting_expense.amount_type as amountType
                                from accounting_expense_photocopy_expense
                                LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2
                                LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                                WHERE 
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 23:59:59" and
                                    accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) photocopyExpense on photocopyExpense.id =  accounting_season.id
                    GROUP BY  accounting_season.id
                )'], 'finalPhotocopyExpense.id =  accounting_season.id')
                ->leftJoin(['finalOtherExpense' => '(
                    SELECT
                            accounting_season.id,
                            sum(COALESCE(otherExpense.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_season.id,
                                    sum(COALESCE(accounting_expense_other_expense.amount, 0)) as total,
                                    DATE(accounting_expense.datetime) as newDate,
                                    accounting_expense.amount_type as amountType
                                from accounting_expense_other_expense
                                LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_other_expense.id and accounting_expense.expense_type_id = 3
                                LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                                WHERE 
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 23:59:59" and
                                    accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) otherExpense on otherExpense.id = accounting_season.id
                    GROUP BY accounting_season.id
                )'], 'finalOtherExpense.id = accounting_season.id')
                ->leftJoin(['finalBankDeposits' => '(
                    SELECT
                            accounting_season.id,
                            sum(COALESCE(bankDeposits.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_season.id,
                                    sum(COALESCE(accounting_expense_bank_deposit.amount, 0)) as total,
                                    DATE(accounting_expense.datetime) as newDate,
                                    accounting_expense.amount_type as amountType
                                from accounting_expense_bank_deposit
                                LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_bank_deposit.id and accounting_expense.expense_type_id = 4
                                LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                                WHERE 
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 23:59:59" and
                                    accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) bankDeposits on bankDeposits.id = accounting_season.id
                    GROUP BY accounting_season.id
                )'], 'finalBankDeposits.id = accounting_season.id')
                ->leftJoin(['finalOperatingExpense' => '(
                    SELECT
                            accounting_season.id,
                            sum(COALESCE(operatingExpense.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 23:59:59" and
                                    accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) operatingExpense on operatingExpense.id = accounting_season.id
                    GROUP BY accounting_season.id
                )'], 'finalOperatingExpense.id = accounting_season.id')
                ->leftJoin(['finalBudgetProposal' => '(
                    SELECT
                            accounting_season.id,
                            sum(COALESCE(budgetProposal.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            (
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
                                    accounting_income.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 23:59:59" and 
                                    accounting_income.amount_type <> "Cash" and
                                    accounting_income_budget_proposal.approval_status = "Approved"
                                GROUP BY newDate, accounting_season.id
                            ) budgetProposal on budgetProposal.id = accounting_season.id
                    GROUP BY accounting_season.id
                )'], 'finalBudgetProposal.id = accounting_season.id')
                ->leftJoin(['finalBranchTransfer' => '(
                    SELECT
                            accounting_season.id,
                            sum(COALESCE(branchTransfer.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 23:59:59" and 
                                    accounting_expense_branch_transfer.amount_source = "Cash On Bank" and
                                    accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) branchTransfer on branchTransfer.id = accounting_season.id
                    GROUP BY accounting_season.id
                )'], 'finalBranchTransfer.id = accounting_season.id')
                ->where(['accounting_season.id' => $this->id])
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
        $season = Season::findOne($this->id);

        $beginningcoh = BeginningCoh::find()->where(['date(datetime)' => $cutoff['start'], 'season_id' => $season->id])->one();
        $beginningcohAmount = $beginningcoh ? $beginningcoh->cash_on_bank : 0;

        $data = Season::find()
                ->select([
                    'accounting_season.id as id',
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
                            accounting_season.id,
                            sum(COALESCE(incomeEnrolments.total, 0)) as total
                            from accounting_season
                            LEFT JOIN 
                            (
                                SELECT 
                                    accounting_income_enrolment.season_id,
                                    sum(COALESCE(accounting_income_enrolment.amount, 0)) as total,
                                    DATE(accounting_income.datetime) as newDate,
                                    accounting_income.amount_type as amountType
                                from accounting_income_enrolment
                                LEFT JOIN accounting_income on accounting_income.income_id = accounting_income_enrolment.id and accounting_income.income_type_id = 1
                                LEFT JOIN accounting_season on accounting_season.id = accounting_income_enrolment.season_id
                                WHERE 
                                    accounting_income.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 00:00:00" and
                                    accounting_income.amount_type <> "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) incomeEnrolments on incomeEnrolments.season_id = accounting_season.id
                        GROUP BY accounting_season.id
                )'], 'finalIncomeEnrolments.id = accounting_season.id')
                ->leftJoin(['finalFreebies' => '(
                        SELECT
                            accounting_season.id,
                            sum(COALESCE(freebies.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            ( 
                                SELECT 
                                    accounting_season.id,
                                    sum(COALESCE(accounting_income_freebies_and_icons.amount, 0)) as total,
                                    DATE(accounting_income.datetime) as newDate,
                                    accounting_income.amount_type as amountType
                                from accounting_income_freebies_and_icons
                                LEFT JOIN accounting_income on accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2
                                LEFT JOIN accounting_season on accounting_season.id = accounting_income_freebies_and_icons.season_id
                                WHERE 
                                    accounting_income.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 00:00:00" and
                                    accounting_income.amount_type <> "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) freebies on freebies.id = accounting_season.id
                        GROUP BY accounting_season.id
                )'], 'finalFreebies.id = accounting_season.id')
                ->leftJoin(['finalPettyExpense' => '(
                    SELECT
                            accounting_season.id,
                            sum(COALESCE(pettyExpense.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 00:00:00" and
                                    accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) pettyExpense on pettyExpense.id = accounting_season.id
                    GROUP BY accounting_season.id
                )'], 'finalPettyExpense.id = accounting_season.id')
                ->leftJoin(['finalPhotocopyExpense' => '(
                    SELECT
                            accounting_season.id,
                            sum(COALESCE(photocopyExpense.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_season.id,
                                    sum(COALESCE(accounting_expense_photocopy_expense.total_amount, 0)) as total,
                                    DATE(accounting_expense.datetime) as newDate,
                                    accounting_expense.amount_type as amountType
                                from accounting_expense_photocopy_expense
                                LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2
                                LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                                WHERE 
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 00:00:00" and
                                    accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) photocopyExpense on photocopyExpense.id =  accounting_season.id
                    GROUP BY  accounting_season.id
                )'], 'finalPhotocopyExpense.id =  accounting_season.id')
                ->leftJoin(['finalOtherExpense' => '(
                    SELECT
                            accounting_season.id,
                            sum(COALESCE(otherExpense.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_season.id,
                                    sum(COALESCE(accounting_expense_other_expense.amount, 0)) as total,
                                    DATE(accounting_expense.datetime) as newDate,
                                    accounting_expense.amount_type as amountType
                                from accounting_expense_other_expense
                                LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_other_expense.id and accounting_expense.expense_type_id = 3
                                LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                                WHERE 
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 00:00:00" and
                                    accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) otherExpense on otherExpense.id = accounting_season.id
                    GROUP BY accounting_season.id
                )'], 'finalOtherExpense.id = accounting_season.id')
                ->leftJoin(['finalBankDeposits' => '(
                    SELECT
                            accounting_season.id,
                            sum(COALESCE(bankDeposits.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_season.id,
                                    sum(COALESCE(accounting_expense_bank_deposit.amount, 0)) as total,
                                    DATE(accounting_expense.datetime) as newDate,
                                    accounting_expense.amount_type as amountType
                                from accounting_expense_bank_deposit
                                LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_bank_deposit.id and accounting_expense.expense_type_id = 4
                                LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                                WHERE 
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 00:00:00" and
                                    accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) bankDeposits on bankDeposits.id = accounting_season.id
                    GROUP BY accounting_season.id
                )'], 'finalBankDeposits.id = accounting_season.id')
                ->leftJoin(['finalOperatingExpense' => '(
                    SELECT
                            accounting_season.id,
                            sum(COALESCE(operatingExpense.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 00:00:00" and
                                    accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) operatingExpense on operatingExpense.id = accounting_season.id
                    GROUP BY accounting_season.id
                )'], 'finalOperatingExpense.id = accounting_season.id')
                ->leftJoin(['finalBudgetProposal' => '(
                    SELECT
                            accounting_season.id,
                            sum(COALESCE(budgetProposal.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            (
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
                                    accounting_income.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 00:00:00" and 
                                    accounting_income.amount_type <> "Cash" and
                                    accounting_income_budget_proposal.approval_status = "Approved"
                                GROUP BY newDate, accounting_season.id
                            ) budgetProposal on budgetProposal.id = accounting_season.id
                    GROUP BY accounting_season.id
                )'], 'finalBudgetProposal.id = accounting_season.id')
                ->leftJoin(['finalBranchTransfer' => '(
                    SELECT
                            accounting_season.id,
                            sum(COALESCE(branchTransfer.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 00:00:00" and 
                                    accounting_expense_branch_transfer.amount_source = "Cash On Bank" and
                                    accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) branchTransfer on branchTransfer.id = accounting_season.id
                    GROUP BY accounting_season.id
                )'], 'finalBranchTransfer.id = accounting_season.id')
                ->where(['accounting_season.id' => $this->id])
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
        $season = Season::findOne($this->id);

        $beginningcoh = BeginningCoh::find()->where(['date(datetime)' => $cutoff['start'], 'season_id' => $season->id])->one();
        $beginningcohAmount = $beginningcoh ? $beginningcoh->cash_on_bank : 0;

        $data = Season::find()
                ->select([
                    'accounting_season.id as id',
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
                            accounting_season.id,
                            sum(COALESCE(incomeEnrolments.total, 0)) as total
                            from accounting_season
                            LEFT JOIN 
                            (
                                SELECT 
                                    accounting_income_enrolment.season_id,
                                    sum(COALESCE(accounting_income_enrolment.amount, 0)) as total,
                                    DATE(accounting_income.datetime) as newDate,
                                    accounting_income.amount_type as amountType
                                from accounting_income_enrolment
                                LEFT JOIN accounting_income on accounting_income.income_id = accounting_income_enrolment.id and accounting_income.income_type_id = 1
                                LEFT JOIN accounting_season on accounting_season.id = accounting_income_enrolment.season_id
                                WHERE 
                                    accounting_income.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_income.amount_type <> "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) incomeEnrolments on incomeEnrolments.season_id = accounting_season.id
                        GROUP BY accounting_season.id
                )'], 'finalIncomeEnrolments.id = accounting_season.id')
                ->leftJoin(['finalFreebies' => '(
                        SELECT
                            accounting_season.id,
                            sum(COALESCE(freebies.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            ( 
                                SELECT 
                                    accounting_season.id,
                                    sum(COALESCE(accounting_income_freebies_and_icons.amount, 0)) as total,
                                    DATE(accounting_income.datetime) as newDate,
                                    accounting_income.amount_type as amountType
                                from accounting_income_freebies_and_icons
                                LEFT JOIN accounting_income on accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2
                                LEFT JOIN accounting_season on accounting_season.id = accounting_income_freebies_and_icons.season_id
                                WHERE 
                                    accounting_income.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_income.amount_type <> "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) freebies on freebies.id = accounting_season.id
                        GROUP BY accounting_season.id
                )'], 'finalFreebies.id = accounting_season.id')
                ->leftJoin(['finalPettyExpense' => '(
                    SELECT
                            accounting_season.id,
                            sum(COALESCE(pettyExpense.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) pettyExpense on pettyExpense.id = accounting_season.id
                    GROUP BY accounting_season.id
                )'], 'finalPettyExpense.id = accounting_season.id')
                ->leftJoin(['finalPhotocopyExpense' => '(
                    SELECT
                            accounting_season.id,
                            sum(COALESCE(photocopyExpense.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_season.id,
                                    sum(COALESCE(accounting_expense_photocopy_expense.total_amount, 0)) as total,
                                    DATE(accounting_expense.datetime) as newDate,
                                    accounting_expense.amount_type as amountType
                                from accounting_expense_photocopy_expense
                                LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2
                                LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                                WHERE 
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) photocopyExpense on photocopyExpense.id =  accounting_season.id
                    GROUP BY  accounting_season.id
                )'], 'finalPhotocopyExpense.id =  accounting_season.id')
                ->leftJoin(['finalOtherExpense' => '(
                    SELECT
                            accounting_season.id,
                            sum(COALESCE(otherExpense.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_season.id,
                                    sum(COALESCE(accounting_expense_other_expense.amount, 0)) as total,
                                    DATE(accounting_expense.datetime) as newDate,
                                    accounting_expense.amount_type as amountType
                                from accounting_expense_other_expense
                                LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_other_expense.id and accounting_expense.expense_type_id = 3
                                LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                                WHERE 
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 23:59:59" and
                                    accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) otherExpense on otherExpense.id = accounting_season.id
                    GROUP BY accounting_season.id
                )'], 'finalOtherExpense.id = accounting_season.id')
                ->leftJoin(['finalBankDeposits' => '(
                    SELECT
                            accounting_season.id,
                            sum(COALESCE(bankDeposits.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_season.id,
                                    sum(COALESCE(accounting_expense_bank_deposit.amount, 0)) as total,
                                    DATE(accounting_expense.datetime) as newDate,
                                    accounting_expense.amount_type as amountType
                                from accounting_expense_bank_deposit
                                LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_bank_deposit.id and accounting_expense.expense_type_id = 4
                                LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                                WHERE 
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) bankDeposits on bankDeposits.id = accounting_season.id
                    GROUP BY accounting_season.id
                )'], 'finalBankDeposits.id = accounting_season.id')
                ->leftJoin(['finalOperatingExpense' => '(
                    SELECT
                            accounting_season.id,
                            sum(COALESCE(operatingExpense.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) operatingExpense on operatingExpense.id = accounting_season.id
                    GROUP BY accounting_season.id
                )'], 'finalOperatingExpense.id = accounting_season.id')
                ->leftJoin(['finalBudgetProposal' => '(
                    SELECT
                            accounting_season.id,
                            sum(COALESCE(budgetProposal.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            (
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
                                    accounting_income.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 23:59:59" and 
                                    accounting_income.amount_type <> "Cash" and
                                    accounting_income_budget_proposal.approval_status = "Approved"
                                GROUP BY newDate, accounting_season.id
                            ) budgetProposal on budgetProposal.id = accounting_season.id
                    GROUP BY accounting_season.id
                )'], 'finalBudgetProposal.id = accounting_season.id')
                ->leftJoin(['finalBranchTransfer' => '(
                    SELECT
                            accounting_season.id,
                            sum(COALESCE(branchTransfer.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 23:59:59" and 
                                    accounting_expense_branch_transfer.amount_source = "Cash On Bank" and
                                    accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) branchTransfer on branchTransfer.id = accounting_season.id
                    GROUP BY accounting_season.id
                )'], 'finalBranchTransfer.id = accounting_season.id')
                ->where(['accounting_season.id' => $this->id])
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
        $season = Season::findOne($this->id);

        $beginningcoh = BeginningCoh::find()->where(['date(datetime)' => $cutoff['start'], 'season_id' => $season->id])->one();
        $beginningcohAmount = $beginningcoh ? $beginningcoh->cash_on_bank : 0;

        $data = Season::find()
                ->select([
                    'accounting_season.id as id',
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
                            accounting_season.id,
                            sum(COALESCE(incomeEnrolments.total, 0)) as total
                            from accounting_season
                            LEFT JOIN 
                            (
                                SELECT 
                                    accounting_income_enrolment.season_id,
                                    sum(COALESCE(accounting_income_enrolment.amount, 0)) as total,
                                    DATE(accounting_income.datetime) as newDate,
                                    accounting_income.amount_type as amountType
                                from accounting_income_enrolment
                                LEFT JOIN accounting_income on accounting_income.income_id = accounting_income_enrolment.id and accounting_income.income_type_id = 1
                                LEFT JOIN accounting_season on accounting_season.id = accounting_income_enrolment.season_id
                                WHERE 
                                    accounting_income.datetime between "'.$date.' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_income.amount_type <> "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) incomeEnrolments on incomeEnrolments.season_id = accounting_season.id
                        GROUP BY accounting_season.id
                )'], 'finalIncomeEnrolments.id = accounting_season.id')
                ->leftJoin(['finalFreebies' => '(
                        SELECT
                            accounting_season.id,
                            sum(COALESCE(freebies.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            ( 
                                SELECT 
                                    accounting_season.id,
                                    sum(COALESCE(accounting_income_freebies_and_icons.amount, 0)) as total,
                                    DATE(accounting_income.datetime) as newDate,
                                    accounting_income.amount_type as amountType
                                from accounting_income_freebies_and_icons
                                LEFT JOIN accounting_income on accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2
                                LEFT JOIN accounting_season on accounting_season.id = accounting_income_freebies_and_icons.season_id
                                WHERE 
                                    accounting_income.datetime between "'.$date.' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_income.amount_type <> "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) freebies on freebies.id = accounting_season.id
                        GROUP BY accounting_season.id
                )'], 'finalFreebies.id = accounting_season.id')
                ->leftJoin(['finalPettyExpense' => '(
                    SELECT
                            accounting_season.id,
                            sum(COALESCE(pettyExpense.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$date.' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) pettyExpense on pettyExpense.id = accounting_season.id
                    GROUP BY accounting_season.id
                )'], 'finalPettyExpense.id = accounting_season.id')
                ->leftJoin(['finalPhotocopyExpense' => '(
                    SELECT
                            accounting_season.id,
                            sum(COALESCE(photocopyExpense.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_season.id,
                                    sum(COALESCE(accounting_expense_photocopy_expense.total_amount, 0)) as total,
                                    DATE(accounting_expense.datetime) as newDate,
                                    accounting_expense.amount_type as amountType
                                from accounting_expense_photocopy_expense
                                LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2
                                LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                                WHERE 
                                    accounting_expense.datetime between "'.$date.' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) photocopyExpense on photocopyExpense.id =  accounting_season.id
                    GROUP BY  accounting_season.id
                )'], 'finalPhotocopyExpense.id =  accounting_season.id')
                ->leftJoin(['finalOtherExpense' => '(
                    SELECT
                            accounting_season.id,
                            sum(COALESCE(otherExpense.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_season.id,
                                    sum(COALESCE(accounting_expense_other_expense.amount, 0)) as total,
                                    DATE(accounting_expense.datetime) as newDate,
                                    accounting_expense.amount_type as amountType
                                from accounting_expense_other_expense
                                LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_other_expense.id and accounting_expense.expense_type_id = 3
                                LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                                WHERE 
                                    accounting_expense.datetime between "'.$date.' 00:00:00" and "'.$cutoff['end'].' 23:59:59" and
                                    accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) otherExpense on otherExpense.id = accounting_season.id
                    GROUP BY accounting_season.id
                )'], 'finalOtherExpense.id = accounting_season.id')
                ->leftJoin(['finalBankDeposits' => '(
                    SELECT
                            accounting_season.id,
                            sum(COALESCE(bankDeposits.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_season.id,
                                    sum(COALESCE(accounting_expense_bank_deposit.amount, 0)) as total,
                                    DATE(accounting_expense.datetime) as newDate,
                                    accounting_expense.amount_type as amountType
                                from accounting_expense_bank_deposit
                                LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_bank_deposit.id and accounting_expense.expense_type_id = 4
                                LEFT JOIN accounting_season on accounting_season.id = accounting_expense.season_id
                                WHERE 
                                    accounting_expense.datetime between "'.$date.' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) bankDeposits on bankDeposits.id = accounting_season.id
                    GROUP BY accounting_season.id
                )'], 'finalBankDeposits.id = accounting_season.id')
                ->leftJoin(['finalOperatingExpense' => '(
                    SELECT
                            accounting_season.id,
                            sum(COALESCE(operatingExpense.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$date.' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) operatingExpense on operatingExpense.id = accounting_season.id
                    GROUP BY accounting_season.id
                )'], 'finalOperatingExpense.id = accounting_season.id')
                ->leftJoin(['finalBudgetProposal' => '(
                    SELECT
                            accounting_season.id,
                            sum(COALESCE(budgetProposal.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            (
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
                                    accounting_income.datetime between "'.$date.' 00:00:00" and "'.$date.' 23:59:59" and 
                                    accounting_income.amount_type <> "Cash" and
                                    accounting_income_budget_proposal.approval_status = "Approved"
                                GROUP BY newDate, accounting_season.id
                            ) budgetProposal on budgetProposal.id = accounting_season.id
                    GROUP BY accounting_season.id
                )'], 'finalBudgetProposal.id = accounting_season.id')
                ->leftJoin(['finalBranchTransfer' => '(
                    SELECT
                            accounting_season.id,
                            sum(COALESCE(branchTransfer.total, 0)) as total
                            from accounting_season
                            LEFT JOIN
                            (
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
                                    accounting_expense.datetime between "'.$date.' 00:00:00" and "'.$date.' 23:59:59" and 
                                    accounting_expense_branch_transfer.amount_source = "Cash On Bank" and
                                    accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate, accounting_season.id
                            ) branchTransfer on branchTransfer.id = accounting_season.id
                    GROUP BY accounting_season.id
                )'], 'finalBranchTransfer.id = accounting_season.id')
                ->where(['accounting_season.id' => $this->id])
                ->asArray()
                ->one();

        $data['beginningcoh'] = $beginningcohAmount;
        $data['cutoff'] = $cutoff;

        return $data;
    }
}
