<?php

namespace common\modules\accounting\models;

use Yii;

/**
 * This is the model class for table "accounting_branch".
 *
 * @property int $id
 * @property string $code
 * @property string $name
 *
 * @property AccountingAudit[] $accountingAudits
 * @property AccountingBranchProgram[] $accountingBranchPrograms
 * @property AccountingDateRestriction[] $accountingDateRestrictions
 * @property AccountingExpense[] $accountingExpenses
 * @property AccountingIncome[] $accountingIncomes
 * @property AccountingPackage[] $accountingPackages
 * @property AccountingSchool[] $accountingSchools
 * @property AccountingTargetEnrolee[] $accountingTargetEnrolees
 * @property AccountingTargetExpense[] $accountingTargetExpenses
 * @property AccountingTransferee[] $accountingTransferees
 */
class Branch extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'accounting_branch';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['code'], 'string', 'max' => 10],
            [['name'], 'string', 'max' => 250],
            [['code','name'], 'required'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Code',
            'name' => 'Name',
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
    public function getAudits()
    {
        return $this->hasMany(Audit::className(), ['branch_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBranchPrograms()
    {
        return $this->hasMany(BranchProgram::className(), ['branch_id' => 'id']);
    }

    public function getBranchProgramIDs()
    {
        $branchPrograms = $this->branchPrograms;
        $ids = [];

        if($branchPrograms)
        {
            foreach($branchPrograms as $branchProgram)
            {
                $ids[] = $branchProgram->id;
            }
        }

        return $ids;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDateRestrictions()
    {
        return $this->hasMany(DateRestriction::className(), ['branch_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getExpenses()
    {
        return $this->hasMany(Expense::className(), ['branch_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIncomes()
    {
        return $this->hasMany(Income::className(), ['branch_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPackages()
    {
        return $this->hasMany(Package::className(), ['branch_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSchools()
    {
        return $this->hasMany(School::className(), ['branch_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTargetEnrolees()
    {
        return $this->hasMany(TargetEnrolee::className(), ['branch_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTargetExpenses()
    {
        return $this->hasMany(TargetExpense::className(), ['branch_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTransferees()
    {
        return $this->hasMany(Transferee::className(), ['branch_id' => 'id']);
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
        $branch = Branch::findOne($this->id);

        $beginningcoh = BeginningCoh::find()
                        ->select(['sum(accounting_audit_beginning_coh.cash_on_hand) as total'])
                        ->leftJoin('accounting_branch_program', 'accounting_branch_program.id = accounting_audit_beginning_coh.branch_program_id')
                        ->where(['date(datetime)' => $cutoff['start'], 'accounting_branch_program.branch_id' => $branch->id])->asArray()->one();
        $beginningcohAmount = $beginningcoh ? $beginningcoh['total'] : 0;

        $data = Branch::find()
                ->select([
                    'accounting_branch.id as id',
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
                            accounting_branch.id,
                            sum(COALESCE(incomeEnrolments.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN 
                            (
                                SELECT 
                                    accounting_income.branch_id,
                                    sum(COALESCE(accounting_income_enrolment.amount, 0)) as total,
                                    DATE(accounting_income.datetime) as newDate,
                                    accounting_income.amount_type as amountType
                                from accounting_income_enrolment
                                LEFT JOIN accounting_income on accounting_income.income_id = accounting_income_enrolment.id and accounting_income.income_type_id = 1
                                LEFT JOIN accounting_package_student on accounting_package_student.season_id = accounting_income_enrolment.season_id and accounting_package_student.student_id = accounting_income_enrolment.student_id
                                LEFT JOIN accounting_package on accounting_package.id = accounting_package_student.package_id
                                LEFT JOIN accounting_package_type on accounting_package_type.id = accounting_package.package_type_id 
                                WHERE accounting_income.branch_id = '.$branch->id.' and
                                    accounting_income.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 23:59:59" and
                                    accounting_package_type.id not in ("5", "7")
                                    and accounting_income.amount_type = "Cash"
                                GROUP BY newDate
                            ) incomeEnrolments on incomeEnrolments.branch_id = accounting_branch.id
                        GROUP BY accounting_branch.id
                )'], 'finalIncomeEnrolments.id = accounting_branch.id')
                ->leftJoin(['finalFreebies' => '(
                        SELECT
                            accounting_branch.id,
                            sum(COALESCE(freebies.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            ( 
                                SELECT 
                                    accounting_income.branch_id,
                                    sum(COALESCE(accounting_income_freebies_and_icons.amount, 0)) as total,
                                    DATE(accounting_income.datetime) as newDate,
                                    accounting_income.amount_type as amountType
                                from accounting_income_freebies_and_icons
                                LEFT JOIN accounting_income on accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2
                                WHERE accounting_income.branch_id = '.$branch->id.' and
                                    accounting_income.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 23:59:59" and
                                    accounting_income_freebies_and_icons.code_id <> "9"
                                    and accounting_income.amount_type = "Cash"
                                GROUP BY newDate
                            ) freebies on freebies.branch_id = accounting_branch.id 
                        GROUP BY accounting_branch.id
                )'], 'finalFreebies.id = accounting_branch.id')
                ->leftJoin(['finalPettyExpense' => '(
                     SELECT
                            accounting_branch.id,
                            sum(COALESCE(pettyExpense.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_expense.branch_id,
                                    sum(COALESCE(accounting_expense_petty_expense.food, 0)) as foodTotal,
                                    sum(COALESCE(accounting_expense_petty_expense.supplies, 0)) as supplyTotal,
                                    sum(COALESCE(accounting_expense_petty_expense.load, 0)) as loadTotal,
                                    sum(COALESCE(accounting_expense_petty_expense.fare, 0)) as fareTotal,
                                    sum(accounting_expense_petty_expense.food + accounting_expense_petty_expense.supplies + accounting_expense_petty_expense.load + accounting_expense_petty_expense.fare) as total,
                                    DATE(accounting_expense.datetime) as newDate,
                                    accounting_expense.amount_type as amountType
                                from accounting_expense_petty_expense
                                LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_petty_expense.id and accounting_expense.expense_type_id = 1
                                WHERE accounting_expense.branch_id = '.$branch->id.' and
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 23:59:59" and
                                    accounting_expense_petty_expense.charge_to <> "Icon"
                                    and accounting_expense.amount_type = "Cash"
                                GROUP BY newDate
                            ) pettyExpense on pettyExpense.branch_id = accounting_branch.id
                    GROUP BY accounting_branch.id
                )'], 'finalPettyExpense.id = accounting_branch.id')
                ->leftJoin(['finalPhotocopyExpense' => '(
                    SELECT
                            accounting_branch.id,
                            sum(COALESCE(photocopyExpense.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_expense.branch_id,
                                    sum(COALESCE(accounting_expense_photocopy_expense.total_amount, 0)) as total,
                                    DATE(accounting_expense.datetime) as newDate,
                                    accounting_expense.amount_type as amountType
                                from accounting_expense_photocopy_expense
                                LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2
                                WHERE accounting_expense.branch_id = '.$branch->id.' and
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 23:59:59" and
                                    accounting_expense_photocopy_expense.charge_to <> "Icon"
                                    and accounting_expense.amount_type = "Cash"
                                GROUP BY newDate
                            ) photocopyExpense on photocopyExpense.branch_id = accounting_branch.id
                    GROUP BY accounting_branch.id
                )'], 'finalPhotocopyExpense.id = accounting_branch.id')
                ->leftJoin(['finalOtherExpense' => '(
                    SELECT
                            accounting_branch.id,
                            sum(COALESCE(otherExpense.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_expense.branch_id,
                                    sum(COALESCE(accounting_expense_other_expense.amount, 0)) as total,
                                    DATE(accounting_expense.datetime) as newDate,
                                    accounting_expense.amount_type as amountType
                                from accounting_expense_other_expense
                                LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_other_expense.id and accounting_expense.expense_type_id = 3
                                WHERE accounting_expense.branch_id = '.$branch->id.' and
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 00:00:00" and
                                    accounting_expense_other_expense.charge_to <> "Icon"
                                    and accounting_expense.amount_type = "Cash"
                                GROUP BY newDate
                            ) otherExpense on otherExpense.branch_id = accounting_branch.id 
                    GROUP BY accounting_branch.id
                )'], 'finalOtherExpense.id = accounting_branch.id')
                ->leftJoin(['finalBankDeposits' => '(
                    SELECT
                            accounting_branch.id,
                            sum(COALESCE(bankDeposits.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_expense.branch_id,
                                    sum(COALESCE(accounting_expense_bank_deposit.amount, 0)) as total,
                                    DATE(accounting_expense.datetime) as newDate,
                                    accounting_expense.amount_type as amountType
                                from accounting_expense_bank_deposit
                                LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_bank_deposit.id and accounting_expense.expense_type_id = 4
                                WHERE accounting_expense.branch_id = '.$branch->id.' and
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 23:59:59"
                                GROUP BY newDate
                            ) bankDeposits on bankDeposits.branch_id = accounting_branch.id
                    GROUP BY accounting_branch.id
                )'], 'finalBankDeposits.id = accounting_branch.id')
                ->leftJoin(['finalOperatingExpense' => '(
                    SELECT
                            accounting_branch.id,
                            sum(COALESCE(operatingExpense.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_expense.branch_id,
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
                                WHERE accounting_expense.branch_id = '.$branch->id.' and
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 23:59:59" and
                                    accounting_expense_operating_expense.charge_to <> "Icon"
                                    and accounting_expense.amount_type = "Cash"
                                GROUP BY newDate
                            ) operatingExpense on operatingExpense.branch_id = accounting_branch.id
                    GROUP BY accounting_branch.id 
                )'], 'finalOperatingExpense.id = accounting_branch.id')
                ->leftJoin(['finalBudgetProposal' => '(
                    SELECT
                            accounting_branch.id,
                            sum(COALESCE(budgetProposal.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_income.branch_id,
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
                                WHERE accounting_income.branch_id = '.$branch->id.' and
                                    accounting_income.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 23:59:59" and 
                                    accounting_income.amount_type = "Cash" and
                                    accounting_income_budget_proposal.approval_status = "Approved"
                                GROUP BY newDate
                            ) budgetProposal on budgetProposal.branch_id = accounting_branch.id 
                    GROUP BY accounting_branch.id
                )'], 'finalBudgetProposal.id = accounting_branch.id')
                ->leftJoin(['finalBranchTransfer' => '(
                    SELECT
                            accounting_branch.id,
                            sum(COALESCE(branchTransfer.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_expense.branch_id,
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
                                WHERE accounting_expense.branch_id = '.$branch->id.' and
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 23:59:59" and 
                                    accounting_expense_branch_transfer.amount_source = "Cash On Hand" and
                                    accounting_expense.amount_type = "Cash"
                                GROUP BY newDate
                            ) branchTransfer on branchTransfer.branch_id = accounting_branch.id
                    GROUP BY accounting_branch.id
                )'], 'finalBranchTransfer.id = accounting_branch.id')
                ->where(['accounting_branch.id' => $this->id])
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
        $branch = Branch::findOne($this->id);

        $beginningcoh = BeginningCoh::find()
                        ->select(['sum(accounting_audit_beginning_coh.cash_on_hand) as total'])
                        ->leftJoin('accounting_branch_program', 'accounting_branch_program.id = accounting_audit_beginning_coh.branch_program_id')
                        ->where(['date(datetime)' => $cutoff['start'], 'accounting_branch_program.branch_id' => $branch->id])->asArray()->all();
        $beginningcohAmount = $beginningcoh ? $beginningcoh['total'] : 0;

        $data = Branch::find()
                ->select([
                    'accounting_branch.id as id',
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
                            accounting_branch.id,
                            sum(COALESCE(incomeEnrolments.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN 
                            (
                                SELECT 
                                    accounting_income.branch_id,
                                    sum(COALESCE(accounting_income_enrolment.amount, 0)) as total,
                                    DATE(accounting_income.datetime) as newDate,
                                    accounting_income.amount_type as amountType
                                from accounting_income_enrolment
                                LEFT JOIN accounting_income on accounting_income.income_id = accounting_income_enrolment.id and accounting_income.income_type_id = 1
                                LEFT JOIN accounting_package_student on accounting_package_student.season_id = accounting_income_enrolment.season_id and accounting_package_student.student_id = accounting_income_enrolment.student_id
                                LEFT JOIN accounting_package on accounting_package.id = accounting_package_student.package_id
                                LEFT JOIN accounting_package_type on accounting_package_type.id = accounting_package.package_type_id 
                                WHERE accounting_income.branch_id = '.$branch->id.' and
                                    accounting_income.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 00:00:00" and
                                    accounting_package_type.id not in ("5", "7")
                                    and accounting_income.amount_type = "Cash"
                                GROUP BY newDate
                            ) incomeEnrolments on incomeEnrolments.branch_id = accounting_branch.id
                        GROUP BY accounting_branch.id
                )'], 'finalIncomeEnrolments.id = accounting_branch.id')
                ->leftJoin(['finalFreebies' => '(
                        SELECT
                            accounting_branch.id,
                            sum(COALESCE(freebies.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            ( 
                                SELECT 
                                    accounting_income.branch_id,
                                    sum(COALESCE(accounting_income_freebies_and_icons.amount, 0)) as total,
                                    DATE(accounting_income.datetime) as newDate,
                                    accounting_income.amount_type as amountType
                                from accounting_income_freebies_and_icons
                                LEFT JOIN accounting_income on accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2
                                WHERE accounting_income.branch_id = '.$branch->id.' and
                                    accounting_income.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 00:00:00" and
                                    accounting_income_freebies_and_icons.code_id <> "9"
                                    and accounting_income.amount_type = "Cash"
                                GROUP BY newDate
                            ) freebies on freebies.branch_id = accounting_branch.id 
                        GROUP BY accounting_branch.id
                )'], 'finalFreebies.id = accounting_branch.id')
                ->leftJoin(['finalPettyExpense' => '(
                     SELECT
                            accounting_branch.id,
                            sum(COALESCE(pettyExpense.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_expense.branch_id,
                                    sum(COALESCE(accounting_expense_petty_expense.food, 0)) as foodTotal,
                                    sum(COALESCE(accounting_expense_petty_expense.supplies, 0)) as supplyTotal,
                                    sum(COALESCE(accounting_expense_petty_expense.load, 0)) as loadTotal,
                                    sum(COALESCE(accounting_expense_petty_expense.fare, 0)) as fareTotal,
                                    sum(accounting_expense_petty_expense.food + accounting_expense_petty_expense.supplies + accounting_expense_petty_expense.load + accounting_expense_petty_expense.fare) as total,
                                    DATE(accounting_expense.datetime) as newDate,
                                    accounting_expense.amount_type as amountType
                                from accounting_expense_petty_expense
                                LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_petty_expense.id and accounting_expense.expense_type_id = 1
                                WHERE accounting_expense.branch_id = '.$branch->id.' and
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 00:00:00" and
                                    accounting_expense_petty_expense.charge_to <> "Icon"
                                    and accounting_expense.amount_type = "Cash"
                                GROUP BY newDate
                            ) pettyExpense on pettyExpense.branch_id = accounting_branch.id
                    GROUP BY accounting_branch.id
                )'], 'finalPettyExpense.id = accounting_branch.id')
                ->leftJoin(['finalPhotocopyExpense' => '(
                    SELECT
                            accounting_branch.id,
                            sum(COALESCE(photocopyExpense.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_expense.branch_id,
                                    sum(COALESCE(accounting_expense_photocopy_expense.total_amount, 0)) as total,
                                    DATE(accounting_expense.datetime) as newDate,
                                    accounting_expense.amount_type as amountType
                                from accounting_expense_photocopy_expense
                                LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2
                                WHERE accounting_expense.branch_id = '.$branch->id.' and
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 00:00:00" and
                                    accounting_expense_photocopy_expense.charge_to <> "Icon"
                                    and accounting_expense.amount_type = "Cash"
                                GROUP BY newDate
                            ) photocopyExpense on photocopyExpense.branch_id = accounting_branch.id
                    GROUP BY accounting_branch.id
                )'], 'finalPhotocopyExpense.id = accounting_branch.id')
                ->leftJoin(['finalOtherExpense' => '(
                    SELECT
                            accounting_branch.id,
                            sum(COALESCE(otherExpense.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_expense.branch_id,
                                    sum(COALESCE(accounting_expense_other_expense.amount, 0)) as total,
                                    DATE(accounting_expense.datetime) as newDate,
                                    accounting_expense.amount_type as amountType
                                from accounting_expense_other_expense
                                LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_other_expense.id and accounting_expense.expense_type_id = 3
                                WHERE accounting_expense.branch_id = '.$branch->id.' and
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 00:00:00" and
                                    accounting_expense_other_expense.charge_to <> "Icon"
                                    and accounting_expense.amount_type = "Cash"
                                GROUP BY newDate
                            ) otherExpense on otherExpense.branch_id = accounting_branch.id 
                    GROUP BY accounting_branch.id
                )'], 'finalOtherExpense.id = accounting_branch.id')
                ->leftJoin(['finalBankDeposits' => '(
                    SELECT
                            accounting_branch.id,
                            sum(COALESCE(bankDeposits.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_expense.branch_id,
                                    sum(COALESCE(accounting_expense_bank_deposit.amount, 0)) as total,
                                    DATE(accounting_expense.datetime) as newDate,
                                    accounting_expense.amount_type as amountType
                                from accounting_expense_bank_deposit
                                LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_bank_deposit.id and accounting_expense.expense_type_id = 4
                                WHERE accounting_expense.branch_id = '.$branch->id.' and
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 00:00:00"
                                GROUP BY newDate
                            ) bankDeposits on bankDeposits.branch_id = accounting_branch.id
                    GROUP BY accounting_branch.id
                )'], 'finalBankDeposits.id = accounting_branch.id')
                ->leftJoin(['finalOperatingExpense' => '(
                    SELECT
                            accounting_branch.id,
                            sum(COALESCE(operatingExpense.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_expense.branch_id,
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
                                WHERE accounting_expense.branch_id = '.$branch->id.' and
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 00:00:00" and
                                    accounting_expense_operating_expense.charge_to <> "Icon"
                                    and accounting_expense.amount_type = "Cash"
                                GROUP BY newDate
                            ) operatingExpense on operatingExpense.branch_id = accounting_branch.id
                    GROUP BY accounting_branch.id
                )'], 'finalOperatingExpense.id = accounting_branch.id')
                ->leftJoin(['finalBudgetProposal' => '(
                    SELECT
                            accounting_branch.id,
                            sum(COALESCE(budgetProposal.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_income.branch_id,
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
                                WHERE accounting_income.branch_id = '.$branch->id.' and
                                    accounting_income.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 00:00:00" and 
                                    accounting_income.amount_type = "Cash" and
                                    accounting_income_budget_proposal.approval_status = "Approved"
                                GROUP BY newDate
                            ) budgetProposal on budgetProposal.branch_id = accounting_branch.id
                    GROUP BY accounting_branch.id
                )'], 'finalBudgetProposal.id = accounting_branch.id')
                ->leftJoin(['finalBranchTransfer' => '(
                    SELECT
                            accounting_branch.id,
                            sum(COALESCE(branchTransfer.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_expense.branch_id,
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
                                WHERE accounting_expense.branch_id = '.$branch->id.' and
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 00:00:00" and 
                                    accounting_expense_branch_transfer.amount_source = "Cash On Hand" and
                                    accounting_expense.amount_type = "Cash"
                                GROUP BY newDate
                            ) branchTransfer on branchTransfer.branch_id = accounting_branch.id
                    GROUP BY accounting_branch.id
                )'], 'finalBranchTransfer.id = accounting_branch.id')
                ->where(['accounting_branch.id' => $this->id])
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
        $branch = Branch::findOne($this->id);

        $beginningcoh = BeginningCoh::find()
                        ->select(['sum(accounting_audit_beginning_coh.cash_on_hand) as total'])
                        ->leftJoin('accounting_branch_program', 'accounting_branch_program.id = accounting_audit_beginning_coh.branch_program_id')
                        ->where(['date(datetime)' => $cutoff['start'], 'accounting_branch_program.branch_id' => $branch->id])->asArray()->one();

        $beginningcohAmount = $beginningcoh ? $beginningcoh['total'] : 0;

        $data = Branch::find()
                ->select([
                    'accounting_branch.id as id',
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
                            accounting_branch.id,
                            sum(COALESCE(incomeEnrolments.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN 
                            (
                                SELECT 
                                    accounting_income.branch_id,
                                    sum(COALESCE(accounting_income_enrolment.amount, 0)) as total,
                                    DATE(accounting_income.datetime) as newDate,
                                    accounting_income.amount_type as amountType
                                from accounting_income_enrolment
                                LEFT JOIN accounting_income on accounting_income.income_id = accounting_income_enrolment.id and accounting_income.income_type_id = 1
                                LEFT JOIN accounting_package_student on accounting_package_student.season_id = accounting_income_enrolment.season_id and accounting_package_student.student_id = accounting_income_enrolment.student_id
                                LEFT JOIN accounting_package on accounting_package.id = accounting_package_student.package_id
                                LEFT JOIN accounting_package_type on accounting_package_type.id = accounting_package.package_type_id 
                                WHERE accounting_income.branch_id = '.$branch->id.' and
                                    accounting_income.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_package_type.id not in ("5", "7")
                                    and accounting_income.amount_type = "Cash"
                                GROUP BY newDate
                            ) incomeEnrolments on incomeEnrolments.branch_id = accounting_branch.id
                        GROUP BY accounting_branch.id
                )'], 'finalIncomeEnrolments.id = accounting_branch.id')
                ->leftJoin(['finalFreebies' => '(
                        SELECT
                            accounting_branch.id,
                            sum(COALESCE(freebies.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            ( 
                                SELECT 
                                    accounting_income.branch_id,
                                    sum(COALESCE(accounting_income_freebies_and_icons.amount, 0)) as total,
                                    DATE(accounting_income.datetime) as newDate,
                                    accounting_income.amount_type as amountType
                                from accounting_income_freebies_and_icons
                                LEFT JOIN accounting_income on accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2
                                WHERE accounting_income.branch_id = '.$branch->id.' and
                                    accounting_income.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_income_freebies_and_icons.code_id <> "9"
                                    and accounting_income.amount_type = "Cash"
                                GROUP BY newDate
                            ) freebies on freebies.branch_id = accounting_branch.id 
                        GROUP BY accounting_branch.id
                )'], 'finalFreebies.id = accounting_branch.id')
                ->leftJoin(['finalPettyExpense' => '(
                     SELECT
                            accounting_branch.id,
                            sum(COALESCE(pettyExpense.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_expense.branch_id,
                                    sum(COALESCE(accounting_expense_petty_expense.food, 0)) as foodTotal,
                                    sum(COALESCE(accounting_expense_petty_expense.supplies, 0)) as supplyTotal,
                                    sum(COALESCE(accounting_expense_petty_expense.load, 0)) as loadTotal,
                                    sum(COALESCE(accounting_expense_petty_expense.fare, 0)) as fareTotal,
                                    sum(accounting_expense_petty_expense.food + accounting_expense_petty_expense.supplies + accounting_expense_petty_expense.load + accounting_expense_petty_expense.fare) as total,
                                    DATE(accounting_expense.datetime) as newDate,
                                    accounting_expense.amount_type as amountType
                                from accounting_expense_petty_expense
                                LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_petty_expense.id and accounting_expense.expense_type_id = 1
                                WHERE accounting_expense.branch_id = '.$branch->id.' and
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_expense_petty_expense.charge_to <> "Icon"
                                    and accounting_expense.amount_type = "Cash"
                                GROUP BY newDate
                            ) pettyExpense on pettyExpense.branch_id = accounting_branch.id
                    GROUP BY accounting_branch.id
                )'], 'finalPettyExpense.id = accounting_branch.id')
                ->leftJoin(['finalPhotocopyExpense' => '(
                    SELECT
                            accounting_branch.id,
                            sum(COALESCE(photocopyExpense.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_expense.branch_id,
                                    sum(COALESCE(accounting_expense_photocopy_expense.total_amount, 0)) as total,
                                    DATE(accounting_expense.datetime) as newDate,
                                    accounting_expense.amount_type as amountType
                                from accounting_expense_photocopy_expense
                                LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2
                                WHERE accounting_expense.branch_id = '.$branch->id.' and
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_expense_photocopy_expense.charge_to <> "Icon"
                                    and accounting_expense.amount_type = "Cash"
                                GROUP BY newDate
                            ) photocopyExpense on photocopyExpense.branch_id = accounting_branch.id
                    GROUP BY accounting_branch.id
                )'], 'finalPhotocopyExpense.id = accounting_branch.id')
                ->leftJoin(['finalOtherExpense' => '(
                    SELECT
                            accounting_branch.id,
                            sum(COALESCE(otherExpense.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_expense.branch_id,
                                    sum(COALESCE(accounting_expense_other_expense.amount, 0)) as total,
                                    DATE(accounting_expense.datetime) as newDate,
                                    accounting_expense.amount_type as amountType
                                from accounting_expense_other_expense
                                LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_other_expense.id and accounting_expense.expense_type_id = 3
                                WHERE accounting_expense.branch_id = '.$branch->id.' and
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_expense_other_expense.charge_to <> "Icon"
                                    and accounting_expense.amount_type = "Cash"
                                GROUP BY newDate
                            ) otherExpense on otherExpense.branch_id = accounting_branch.id
                    GROUP BY accounting_branch.id 
                )'], 'finalOtherExpense.id = accounting_branch.id')
                ->leftJoin(['finalBankDeposits' => '(
                    SELECT
                            accounting_branch.id,
                            sum(COALESCE(bankDeposits.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_expense.branch_id,
                                    sum(COALESCE(accounting_expense_bank_deposit.amount, 0)) as total,
                                    DATE(accounting_expense.datetime) as newDate,
                                    accounting_expense.amount_type as amountType
                                from accounting_expense_bank_deposit
                                LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_bank_deposit.id and accounting_expense.expense_type_id = 4
                                WHERE accounting_expense.branch_id = '.$branch->id.' and
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 23:59:59"
                                GROUP BY newDate
                            ) bankDeposits on bankDeposits.branch_id = accounting_branch.id
                    GROUP BY accounting_branch.id
                )'], 'finalBankDeposits.id = accounting_branch.id')
                ->leftJoin(['finalOperatingExpense' => '(
                    SELECT
                            accounting_branch.id,
                            sum(COALESCE(operatingExpense.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_expense.branch_id,
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
                                WHERE accounting_expense.branch_id = '.$branch->id.' and
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_expense_operating_expense.charge_to <> "Icon"
                                    and accounting_expense.amount_type = "Cash"
                                GROUP BY newDate
                            ) operatingExpense on operatingExpense.branch_id = accounting_branch.id
                    GROUP BY accounting_branch.id 
                )'], 'finalOperatingExpense.id = accounting_branch.id')
                ->leftJoin(['finalBudgetProposal' => '(
                    SELECT
                            accounting_branch.id,
                            sum(COALESCE(budgetProposal.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_income.branch_id,
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
                                WHERE accounting_income.branch_id = '.$branch->id.' and
                                    accounting_income.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 23:59:59" and 
                                    accounting_income.amount_type = "Cash" and
                                    accounting_income_budget_proposal.approval_status = "Approved"
                                GROUP BY newDate
                            ) budgetProposal on budgetProposal.branch_id = accounting_branch.id 
                    GROUP BY accounting_branch.id
                )'], 'finalBudgetProposal.id = accounting_branch.id')
                ->leftJoin(['finalBranchTransfer' => '(
                    SELECT
                            accounting_branch.id,
                            sum(COALESCE(branchTransfer.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_expense.branch_id,
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
                                WHERE accounting_expense.branch_id = '.$branch->id.' and
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 23:59:59" and 
                                    accounting_expense_branch_transfer.amount_source = "Cash On Hand" and
                                    accounting_expense.amount_type = "Cash"
                                GROUP BY newDate
                            ) branchTransfer on branchTransfer.branch_id = accounting_branch.id
                    GROUP BY accounting_branch.id
                )'], 'finalBranchTransfer.id = accounting_branch.id')
                ->where(['accounting_branch.id' => $this->id])
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
        $branch = Branch::findOne($this->id);

        $beginningcoh = BeginningCoh::find()
                        ->select(['sum(accounting_audit_beginning_coh.cash_on_hand) as total'])
                        ->leftJoin('accounting_branch_program', 'accounting_branch_program.id = accounting_audit_beginning_coh.branch_program_id')
                        ->where(['date(datetime)' => $cutoff['start'], 'accounting_branch_program.branch_id' => $branch->id])->asArray()->all();
        $beginningcohAmount = $beginningcoh ? $beginningcoh['total'] : 0;

        $data = Branch::find()
                ->select([
                    'accounting_branch.id as id',
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
                            accounting_branch.id,
                            sum(COALESCE(incomeEnrolments.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN 
                            (
                                SELECT 
                                    accounting_income.branch_id,
                                    sum(COALESCE(accounting_income_enrolment.amount, 0)) as total,
                                    DATE(accounting_income.datetime) as newDate,
                                    accounting_income.amount_type as amountType
                                from accounting_income_enrolment
                                LEFT JOIN accounting_income on accounting_income.income_id = accounting_income_enrolment.id and accounting_income.income_type_id = 1
                                LEFT JOIN accounting_package_student on accounting_package_student.season_id = accounting_income_enrolment.season_id and accounting_package_student.student_id = accounting_income_enrolment.student_id
                                LEFT JOIN accounting_package on accounting_package.id = accounting_package_student.package_id
                                LEFT JOIN accounting_package_type on accounting_package_type.id = accounting_package.package_type_id 
                                WHERE accounting_income.branch_id = '.$branch->id.' and
                                    accounting_income.datetime between "'.$date.' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_package_type.id not in ("5", "7")
                                    and accounting_income.amount_type = "Cash"
                                GROUP BY newDate
                            ) incomeEnrolments on incomeEnrolments.branch_id = accounting_branch.id
                        GROUP BY accounting_branch.id
                )'], 'finalIncomeEnrolments.id = accounting_branch.id')
                ->leftJoin(['finalFreebies' => '(
                        SELECT
                            accounting_branch.id,
                            sum(COALESCE(freebies.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            ( 
                                SELECT 
                                    accounting_income.branch_id,
                                    sum(COALESCE(accounting_income_freebies_and_icons.amount, 0)) as total,
                                    DATE(accounting_income.datetime) as newDate,
                                    accounting_income.amount_type as amountType
                                from accounting_income_freebies_and_icons
                                LEFT JOIN accounting_income on accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2
                                WHERE accounting_income.branch_id = '.$branch->id.' and
                                    accounting_income.datetime between "'.$date.' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_income_freebies_and_icons.code_id <> "9"
                                    and accounting_income.amount_type = "Cash"
                                GROUP BY newDate
                            ) freebies on freebies.branch_id = accounting_branch.id
                        GROUP BY accounting_branch.id
                )'], 'finalFreebies.id = accounting_branch.id')
                ->leftJoin(['finalPettyExpense' => '(
                     SELECT
                            accounting_branch.id,
                            sum(COALESCE(pettyExpense.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_expense.branch_id,
                                    sum(COALESCE(accounting_expense_petty_expense.food, 0)) as foodTotal,
                                    sum(COALESCE(accounting_expense_petty_expense.supplies, 0)) as supplyTotal,
                                    sum(COALESCE(accounting_expense_petty_expense.load, 0)) as loadTotal,
                                    sum(COALESCE(accounting_expense_petty_expense.fare, 0)) as fareTotal,
                                    sum(accounting_expense_petty_expense.food + accounting_expense_petty_expense.supplies + accounting_expense_petty_expense.load + accounting_expense_petty_expense.fare) as total,
                                    DATE(accounting_expense.datetime) as newDate,
                                    accounting_expense.amount_type as amountType
                                from accounting_expense_petty_expense
                                LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_petty_expense.id and accounting_expense.expense_type_id = 1
                                WHERE accounting_expense.branch_id = '.$branch->id.' and
                                    accounting_expense.datetime between "'.$date.' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_expense_petty_expense.charge_to <> "Icon"
                                    and accounting_expense.amount_type = "Cash"
                                GROUP BY newDate
                            ) pettyExpense on pettyExpense.branch_id = accounting_branch.id
                    GROUP BY accounting_branch.id
                )'], 'finalPettyExpense.id = accounting_branch.id')
                ->leftJoin(['finalPhotocopyExpense' => '(
                    SELECT
                            accounting_branch.id,
                            sum(COALESCE(photocopyExpense.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_expense.branch_id,
                                    sum(COALESCE(accounting_expense_photocopy_expense.total_amount, 0)) as total,
                                    DATE(accounting_expense.datetime) as newDate,
                                    accounting_expense.amount_type as amountType
                                from accounting_expense_photocopy_expense
                                LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2
                                WHERE accounting_expense.branch_id = '.$branch->id.' and
                                    accounting_expense.datetime between "'.$date.' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_expense_photocopy_expense.charge_to <> "Icon"
                                    and accounting_expense.amount_type = "Cash"
                                GROUP BY newDate
                            ) photocopyExpense on photocopyExpense.branch_id = accounting_branch.id
                    GROUP BY accounting_branch.id
                )'], 'finalPhotocopyExpense.id = accounting_branch.id')
                ->leftJoin(['finalOtherExpense' => '(
                    SELECT
                            accounting_branch.id,
                            sum(COALESCE(otherExpense.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_expense.branch_id,
                                    sum(COALESCE(accounting_expense_other_expense.amount, 0)) as total,
                                    DATE(accounting_expense.datetime) as newDate,
                                    accounting_expense.amount_type as amountType
                                from accounting_expense_other_expense
                                LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_other_expense.id and accounting_expense.expense_type_id = 3
                                WHERE accounting_expense.branch_id = '.$branch->id.' and
                                    accounting_expense.datetime between "'.$date.' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_expense_other_expense.charge_to <> "Icon"
                                    and accounting_expense.amount_type = "Cash"
                                GROUP BY newDate
                            ) otherExpense on otherExpense.branch_id = accounting_branch.id
                    GROUP BY accounting_branch.id
                )'], 'finalOtherExpense.id = accounting_branch.id')
                ->leftJoin(['finalBankDeposits' => '(
                    SELECT
                            accounting_branch.id,
                            sum(COALESCE(bankDeposits.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_expense.branch_id,
                                    sum(COALESCE(accounting_expense_bank_deposit.amount, 0)) as total,
                                    DATE(accounting_expense.datetime) as newDate,
                                    accounting_expense.amount_type as amountType
                                from accounting_expense_bank_deposit
                                LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_bank_deposit.id and accounting_expense.expense_type_id = 4
                                WHERE accounting_expense.branch_id = '.$branch->id.' and
                                    accounting_expense.datetime between "'.$date.' 00:00:00" and "'.$date.' 23:59:59"
                                GROUP BY newDate
                            ) bankDeposits on bankDeposits.branch_id = accounting_branch.id
                    GROUP BY accounting_branch.id
                )'], 'finalBankDeposits.id = accounting_branch.id')
                ->leftJoin(['finalOperatingExpense' => '(
                    SELECT
                            accounting_branch.id,
                            sum(COALESCE(operatingExpense.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_expense.branch_id,
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
                                WHERE accounting_expense.branch_id = '.$branch->id.' and
                                    accounting_expense.datetime between "'.$date.' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_expense_operating_expense.charge_to <> "Icon"
                                    and accounting_expense.amount_type = "Cash"
                                GROUP BY newDate
                            ) operatingExpense on operatingExpense.branch_id = accounting_branch.id
                    GROUP BY accounting_branch.id 
                )'], 'finalOperatingExpense.id = accounting_branch.id')
                ->leftJoin(['finalBudgetProposal' => '(
                    SELECT
                            accounting_branch.id,
                            sum(COALESCE(budgetProposal.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_income.branch_id,
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
                                WHERE accounting_income.branch_id = '.$branch->id.' and
                                    accounting_income.datetime between "'.$date.' 00:00:00" and "'.$date.' 23:59:59" and 
                                    accounting_income.amount_type = "Cash" and
                                    accounting_income_budget_proposal.approval_status = "Approved"
                                GROUP BY newDate
                            ) budgetProposal on budgetProposal.branch_id = accounting_branch.id 
                    GROUP BY accounting_branch.id
                )'], 'finalBudgetProposal.id = accounting_branch.id')
                ->leftJoin(['finalBranchTransfer' => '(
                    SELECT
                            accounting_branch.id,
                            sum(COALESCE(branchTransfer.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_expense.branch_id,
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
                                WHERE accounting_expense.branch_id = '.$branch->id.' and
                                    accounting_expense.datetime between "'.$date.' 00:00:00" and "'.$date.' 23:59:59" and 
                                    accounting_expense_branch_transfer.amount_source = "Cash On Hand" and
                                    accounting_expense.amount_type = "Cash"
                                GROUP BY newDate
                            ) branchTransfer on branchTransfer.branch_id = accounting_branch.id
                    GROUP BY accounting_branch.id
                )'], 'finalBranchTransfer.id = accounting_branch.id')
                ->where(['accounting_branch.id' => $this->id])
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
        $branch = Branch::findOne($this->id);

        $beginningcoh = BeginningCoh::find()
                        ->select(['sum(accounting_audit_beginning_coh.cash_on_bank) as total'])
                        ->leftJoin('accounting_branch_program', 'accounting_branch_program.id = accounting_audit_beginning_coh.branch_program_id')
                        ->where(['date(datetime)' => $cutoff['start'], 'accounting_branch_program.branch_id' => $branch->id])->asArray()->all();
        $beginningcohAmount = $beginningcoh ? $beginningcoh['total'] : 0;

        $data = Branch::find()
                ->select([
                    'accounting_branch.id as id',
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
                            accounting_branch.id,
                            sum(COALESCE(incomeEnrolments.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN 
                            (
                                SELECT 
                                    accounting_income.branch_id,
                                    sum(COALESCE(accounting_income_enrolment.amount, 0)) as total,
                                    DATE(accounting_income.datetime) as newDate,
                                    accounting_income.amount_type as amountType
                                from accounting_income_enrolment
                                LEFT JOIN accounting_income on accounting_income.income_id = accounting_income_enrolment.id and accounting_income.income_type_id = 1
                                LEFT JOIN accounting_package_student on accounting_package_student.season_id = accounting_income_enrolment.season_id and accounting_package_student.student_id = accounting_income_enrolment.student_id
                                LEFT JOIN accounting_package on accounting_package.id = accounting_package_student.package_id
                                LEFT JOIN accounting_package_type on accounting_package_type.id = accounting_package.package_type_id 
                                WHERE accounting_income.branch_id = '.$branch->id.' and
                                    accounting_income.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 23:59:59" and
                                    accounting_package_type.id not in ("5", "7")
                                    and accounting_income.amount_type <> "Cash"
                                GROUP BY newDate
                            ) incomeEnrolments on incomeEnrolments.branch_id = accounting_branch.id
                    GROUP BY accounting_branch.id
                )'], 'finalIncomeEnrolments.id = accounting_branch.id')
                ->leftJoin(['finalFreebies' => '(
                    SELECT
                            accounting_branch.id,
                            sum(COALESCE(freebies.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_income.branch_id,
                                    sum(COALESCE(accounting_income_freebies_and_icons.amount, 0)) as total,
                                    DATE(accounting_income.datetime) as newDate,
                                    accounting_income.amount_type as amountType
                                from accounting_income_freebies_and_icons
                                LEFT JOIN accounting_income on accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2
                                WHERE accounting_income.branch_id = '.$branch->id.' and
                                    accounting_income.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 23:59:59" and
                                    accounting_income_freebies_and_icons.code_id <> "9"
                                    and accounting_income.amount_type <> "Cash"
                                GROUP BY newDate
                            ) freebies on freebies.branch_id = accounting_branch.id 
                    GROUP BY accounting_branch.id
                )'], 'finalFreebies.id = accounting_branch.id')
                ->leftJoin(['finalPettyExpense' => '(
                    SELECT
                            accounting_branch.id,
                            sum(COALESCE(pettyExpense.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_expense.branch_id,
                                    sum(COALESCE(accounting_expense_petty_expense.food, 0)) as foodTotal,
                                    sum(COALESCE(accounting_expense_petty_expense.supplies, 0)) as supplyTotal,
                                    sum(COALESCE(accounting_expense_petty_expense.load, 0)) as loadTotal,
                                    sum(COALESCE(accounting_expense_petty_expense.fare, 0)) as fareTotal,
                                    sum(accounting_expense_petty_expense.food + accounting_expense_petty_expense.supplies + accounting_expense_petty_expense.load + accounting_expense_petty_expense.fare) as total,
                                    DATE(accounting_expense.datetime) as newDate,
                                    accounting_expense.amount_type as amountType
                                from accounting_expense_petty_expense
                                LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_petty_expense.id and accounting_expense.expense_type_id = 1
                                WHERE accounting_expense.branch_id = '.$branch->id.' and
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 23:59:59" and
                                    accounting_expense_petty_expense.charge_to <> "Icon"
                                    and accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate
                            ) pettyExpense on pettyExpense.branch_id = accounting_branch.id
                    GROUP BY accounting_branch.id
                )'], 'finalPettyExpense.id = accounting_branch.id')
                ->leftJoin(['finalPhotocopyExpense' => '(
                    SELECT
                            accounting_branch.id,
                            sum(COALESCE(photocopyExpense.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_expense.branch_id,
                                    sum(COALESCE(accounting_expense_photocopy_expense.total_amount, 0)) as total,
                                    DATE(accounting_expense.datetime) as newDate,
                                    accounting_expense.amount_type as amountType
                                from accounting_expense_photocopy_expense
                                LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2
                                WHERE accounting_expense.branch_id = '.$branch->id.' and
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 23:59:59" and
                                    accounting_expense_photocopy_expense.charge_to <> "Icon"
                                    and accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate
                            ) photocopyExpense on photocopyExpense.branch_id = accounting_branch.id
                    GROUP BY accounting_branch.id
                )'], 'finalPhotocopyExpense.id = accounting_branch.id')
                ->leftJoin(['finalOtherExpense' => '(
                    SELECT
                            accounting_branch.id,
                            sum(COALESCE(otherExpense.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_expense.branch_id,
                                    sum(COALESCE(accounting_expense_other_expense.amount, 0)) as total,
                                    DATE(accounting_expense.datetime) as newDate,
                                    accounting_expense.amount_type as amountType
                                from accounting_expense_other_expense
                                LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_other_expense.id and accounting_expense.expense_type_id = 3
                                WHERE accounting_expense.branch_id = '.$branch->id.' and
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 23:59:59" and
                                    accounting_expense_other_expense.charge_to <> "Icon"
                                    and accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate
                            ) otherExpense on otherExpense.branch_id = accounting_branch.id 
                    GROUP BY accounting_branch.id
                )'], 'finalOtherExpense.id = accounting_branch.id')
                ->leftJoin(['finalBankDeposits' => '(
                    SELECT
                            accounting_branch.id,
                            sum(COALESCE(bankDeposits.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_expense.branch_id,
                                    sum(COALESCE(accounting_expense_bank_deposit.amount, 0)) as total,
                                    DATE(accounting_expense.datetime) as newDate,
                                    accounting_expense.amount_type as amountType
                                from accounting_expense_bank_deposit
                                LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_bank_deposit.id and accounting_expense.expense_type_id = 4
                                WHERE accounting_expense.branch_id = '.$branch->id.' and
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 23:59:59"
                                GROUP BY newDate
                            ) bankDeposits on bankDeposits.branch_id = accounting_branch.id
                    GROUP BY accounting_branch.id
                )'], 'finalBankDeposits.id = accounting_branch.id')
                ->leftJoin(['finalOperatingExpense' => '(
                    SELECT
                            accounting_branch.id,
                            sum(COALESCE(operatingExpense.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_expense.branch_id,
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
                                WHERE accounting_expense.branch_id = '.$branch->id.' and
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 23:59:59" and
                                    accounting_expense_operating_expense.charge_to <> "Icon"
                                    and accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate
                            ) operatingExpense on operatingExpense.branch_id = accounting_branch.id
                    GROUP BY accounting_branch.id
                )'], 'finalOperatingExpense.id = accounting_branch.id')
                ->leftJoin(['finalBudgetProposal' => '(
                    SELECT
                            accounting_branch.id,
                            sum(COALESCE(budgetProposal.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_income.branch_id,
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
                                WHERE accounting_income.branch_id = '.$branch->id.' and
                                    accounting_income.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 23:59:59" and 
                                    accounting_income.amount_type <> "Cash" and
                                    accounting_income_budget_proposal.approval_status = "Approved"
                                GROUP BY newDate
                            ) budgetProposal on budgetProposal.branch_id = accounting_branch.id
                    GROUP BY accounting_branch.id
                )'], 'finalBudgetProposal.id = accounting_branch.id')
                ->leftJoin(['finalBranchTransfer' => '(
                    SELECT
                            accounting_branch.id,
                            sum(COALESCE(branchTransfer.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_expense.branch_id,
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
                                WHERE accounting_expense.branch_id = '.$branch->id.' and
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$cutoff['end'].' 23:59:59" and 
                                    accounting_expense_branch_transfer.amount_source = "Cash On Bank" and
                                    accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate
                            ) branchTransfer on branchTransfer.branch_id = accounting_branch.id
                    GROUP BY accounting_branch.id
                )'], 'finalBranchTransfer.id = accounting_branch.id')
                ->where(['accounting_branch.id' => $this->id])
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
        $branch = Branch::findOne($this->id);

        $beginningcoh = BeginningCoh::find()
                        ->select(['sum(accounting_audit_beginning_coh.cash_on_bank) as total'])
                        ->leftJoin('accounting_branch_program', 'accounting_branch_program.id = accounting_audit_beginning_coh.branch_program_id')
                        ->where(['date(datetime)' => $cutoff['start'], 'accounting_branch_program.branch_id' => $branch->id])->asArray()->all();
        $beginningcohAmount = $beginningcoh ? $beginningcoh['total'] : 0;

        $data = Branch::find()
                ->select([
                    'accounting_branch.id as id',
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
                            accounting_branch.id,
                            sum(COALESCE(incomeEnrolments.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN 
                            (
                                SELECT 
                                    accounting_income.branch_id,
                                    sum(COALESCE(accounting_income_enrolment.amount, 0)) as total,
                                    DATE(accounting_income.datetime) as newDate,
                                    accounting_income.amount_type as amountType
                                from accounting_income_enrolment
                                LEFT JOIN accounting_income on accounting_income.income_id = accounting_income_enrolment.id and accounting_income.income_type_id = 1
                                LEFT JOIN accounting_package_student on accounting_package_student.season_id = accounting_income_enrolment.season_id and accounting_package_student.student_id = accounting_income_enrolment.student_id
                                LEFT JOIN accounting_package on accounting_package.id = accounting_package_student.package_id
                                LEFT JOIN accounting_package_type on accounting_package_type.id = accounting_package.package_type_id 
                                WHERE accounting_income.branch_id = '.$branch->id.' and
                                    accounting_income.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 00:00:00" and
                                    accounting_package_type.id not in ("5", "7")
                                    and accounting_income.amount_type <> "Cash"
                                GROUP BY newDate
                            ) incomeEnrolments on incomeEnrolments.branch_id = accounting_branch.id
                    GROUP BY accounting_branch.id
                )'], 'finalIncomeEnrolments.id = accounting_branch.id')
                ->leftJoin(['finalFreebies' => '(
                    SELECT
                            accounting_branch.id,
                            sum(COALESCE(freebies.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_income.branch_id,
                                    sum(COALESCE(accounting_income_freebies_and_icons.amount, 0)) as total,
                                    DATE(accounting_income.datetime) as newDate,
                                    accounting_income.amount_type as amountType
                                from accounting_income_freebies_and_icons
                                LEFT JOIN accounting_income on accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2
                                WHERE accounting_income.branch_id = '.$branch->id.' and
                                    accounting_income.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 00:00:00" and
                                    accounting_income_freebies_and_icons.code_id <> "9"
                                    and accounting_income.amount_type <> "Cash"
                                GROUP BY newDate
                            ) freebies on freebies.branch_id = accounting_branch.id 
                    GROUP BY accounting_branch.id
                )'], 'finalFreebies.id = accounting_branch.id')
                ->leftJoin(['finalPettyExpense' => '(
                    SELECT
                            accounting_branch.id,
                            sum(COALESCE(pettyExpense.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_expense.branch_id,
                                    sum(COALESCE(accounting_expense_petty_expense.food, 0)) as foodTotal,
                                    sum(COALESCE(accounting_expense_petty_expense.supplies, 0)) as supplyTotal,
                                    sum(COALESCE(accounting_expense_petty_expense.load, 0)) as loadTotal,
                                    sum(COALESCE(accounting_expense_petty_expense.fare, 0)) as fareTotal,
                                    sum(accounting_expense_petty_expense.food + accounting_expense_petty_expense.supplies + accounting_expense_petty_expense.load + accounting_expense_petty_expense.fare) as total,
                                    DATE(accounting_expense.datetime) as newDate,
                                    accounting_expense.amount_type as amountType
                                from accounting_expense_petty_expense
                                LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_petty_expense.id and accounting_expense.expense_type_id = 1
                                WHERE accounting_expense.branch_id = '.$branch->id.' and
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 00:00:00" and
                                    accounting_expense_petty_expense.charge_to <> "Icon"
                                    and accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate
                            ) pettyExpense on pettyExpense.branch_id = accounting_branch.id
                    GROUP BY accounting_branch.id
                )'], 'finalPettyExpense.id = accounting_branch.id')
                ->leftJoin(['finalPhotocopyExpense' => '(
                    SELECT
                            accounting_branch.id,
                            sum(COALESCE(photocopyExpense.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_expense.branch_id,
                                    sum(COALESCE(accounting_expense_photocopy_expense.total_amount, 0)) as total,
                                    DATE(accounting_expense.datetime) as newDate,
                                    accounting_expense.amount_type as amountType
                                from accounting_expense_photocopy_expense
                                LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2
                                WHERE accounting_expense.branch_id = '.$branch->id.' and
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 00:00:00" and
                                    accounting_expense_photocopy_expense.charge_to <> "Icon"
                                    and accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate
                            ) photocopyExpense on photocopyExpense.branch_id = accounting_branch.id
                    GROUP BY accounting_branch.id
                )'], 'finalPhotocopyExpense.id = accounting_branch.id')
                ->leftJoin(['finalOtherExpense' => '(
                    SELECT
                            accounting_branch.id,
                            sum(COALESCE(otherExpense.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_expense.branch_id,
                                    sum(COALESCE(accounting_expense_other_expense.amount, 0)) as total,
                                    DATE(accounting_expense.datetime) as newDate,
                                    accounting_expense.amount_type as amountType
                                from accounting_expense_other_expense
                                LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_other_expense.id and accounting_expense.expense_type_id = 3
                                WHERE accounting_expense.branch_id = '.$branch->id.' and
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 00:00:00" and
                                    accounting_expense_other_expense.charge_to <> "Icon"
                                    and accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate
                            ) otherExpense on otherExpense.branch_id = accounting_branch.id 
                    GROUP BY accounting_branch.id
                )'], 'finalOtherExpense.id = accounting_branch.id')
                ->leftJoin(['finalBankDeposits' => '(
                    SELECT
                            accounting_branch.id,
                            sum(COALESCE(bankDeposits.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_expense.branch_id,
                                    sum(COALESCE(accounting_expense_bank_deposit.amount, 0)) as total,
                                    DATE(accounting_expense.datetime) as newDate,
                                    accounting_expense.amount_type as amountType
                                from accounting_expense_bank_deposit
                                LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_bank_deposit.id and accounting_expense.expense_type_id = 4
                                WHERE accounting_expense.branch_id = '.$branch->id.' and
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 00:00:00"
                                GROUP BY newDate
                            ) bankDeposits on bankDeposits.branch_id = accounting_branch.id
                    GROUP BY accounting_branch.id
                )'], 'finalBankDeposits.id = accounting_branch.id')
                ->leftJoin(['finalOperatingExpense' => '(
                    SELECT
                            accounting_branch.id,
                            sum(COALESCE(operatingExpense.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_expense.branch_id,
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
                                WHERE accounting_expense.branch_id = '.$branch->id.' and
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 00:00:00" and
                                    accounting_expense_operating_expense.charge_to <> "Icon"
                                    and accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate
                            ) operatingExpense on operatingExpense.branch_id = accounting_branch.id
                    GROUP BY accounting_branch.id
                )'], 'finalOperatingExpense.id = accounting_branch.id')
                ->leftJoin(['finalBudgetProposal' => '(
                    SELECT
                            accounting_branch.id,
                            sum(COALESCE(budgetProposal.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_income.branch_id,
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
                                WHERE accounting_income.branch_id = '.$branch->id.' and
                                    accounting_income.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 00:00:00" and 
                                    accounting_income.amount_type <> "Cash" and
                                    accounting_income_budget_proposal.approval_status = "Approved"
                                GROUP BY newDate
                            ) budgetProposal on budgetProposal.branch_id = accounting_branch.id
                    GROUP BY accounting_branch.id
                )'], 'finalBudgetProposal.id = accounting_branch.id')
                ->leftJoin(['finalBranchTransfer' => '(
                    SELECT
                            accounting_branch.id,
                            sum(COALESCE(branchTransfer.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_expense.branch_id,
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
                                WHERE accounting_expense.branch_id = '.$branch->id.' and
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 00:00:00" and 
                                    accounting_expense_branch_transfer.amount_source = "Cash On Bank" and
                                    accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate
                            ) branchTransfer on branchTransfer.branch_id = accounting_branch.id
                    GROUP BY accounting_branch.id
                )'], 'finalBranchTransfer.id = accounting_branch.id')
                ->where(['accounting_branch.id' => $this->id])
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
        $branch = Branch::findOne($this->id);

        $beginningcoh = BeginningCoh::find()
                        ->select(['sum(accounting_audit_beginning_coh.cash_on_bank) as total'])
                        ->leftJoin('accounting_branch_program', 'accounting_branch_program.id = accounting_audit_beginning_coh.branch_program_id')
                        ->where(['date(datetime)' => $cutoff['start'], 'accounting_branch_program.branch_id' => $branch->id])->asArray()->all();
        $beginningcohAmount = $beginningcoh ? $beginningcoh['total'] : 0;

        $data = Branch::find()
                ->select([
                    'finalIncomeEnrolments.total as incomeEnrolmentTotal',
                    'finalFreebies.total as freebiesTotal',
                    'finalPettyExpense.total as pettyExpenseTotal',
                    'finalPhotocopyExpense.total as photocopyExpenseTotal',
                    'finalOtherExpense.total as otherExpenseTotal',
                    'finalBankDeposits.total as bankDepositsTotal',
                    'finalOperatingExpense.total as operatingExpenseTotal',
                    'finalBudgetProposal.total as budgetProposalTotal',
                    'finalBranchTransfer.total as branchTransferTotal',
                ])
                ->leftJoin(['finalIncomeEnrolments' => '(
                    SELECT
                            accounting_branch.id,
                            sum(COALESCE(incomeEnrolments.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN 
                            (
                                SELECT 
                                    accounting_income.branch_id,
                                    sum(COALESCE(accounting_income_enrolment.amount, 0)) as total,
                                    DATE(accounting_income.datetime) as newDate,
                                    accounting_income.amount_type as amountType
                                from accounting_income_enrolment
                                LEFT JOIN accounting_income on accounting_income.income_id = accounting_income_enrolment.id and accounting_income.income_type_id = 1
                                LEFT JOIN accounting_package_student on accounting_package_student.season_id = accounting_income_enrolment.season_id and accounting_package_student.student_id = accounting_income_enrolment.student_id
                                LEFT JOIN accounting_package on accounting_package.id = accounting_package_student.package_id
                                LEFT JOIN accounting_package_type on accounting_package_type.id = accounting_package.package_type_id 
                                WHERE accounting_income.branch_id = '.$branch->id.' and
                                    accounting_income.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_package_type.id not in ("5", "7")
                                    and accounting_income.amount_type <> "Cash"
                                GROUP BY newDate
                            ) incomeEnrolments on incomeEnrolments.branch_id = accounting_branch.id
                    GROUP BY accounting_branch.id
                )'], 'finalIncomeEnrolments.id = accounting_branch.id')
                ->leftJoin(['finalFreebies' => '(
                    SELECT
                            accounting_branch.id,
                            sum(COALESCE(freebies.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_income.branch_id,
                                    sum(COALESCE(accounting_income_freebies_and_icons.amount, 0)) as total,
                                    DATE(accounting_income.datetime) as newDate,
                                    accounting_income.amount_type as amountType
                                from accounting_income_freebies_and_icons
                                LEFT JOIN accounting_income on accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2
                                WHERE accounting_income.branch_id = '.$branch->id.' and
                                    accounting_income.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_income_freebies_and_icons.code_id <> "9"
                                    and accounting_income.amount_type <> "Cash"
                                GROUP BY newDate
                            ) freebies on freebies.branch_id = accounting_branch.id 
                    GROUP BY accounting_branch.id
                )'], 'finalFreebies.id = accounting_branch.id')
                ->leftJoin(['finalPettyExpense' => '(
                    SELECT
                            accounting_branch.id,
                            sum(COALESCE(pettyExpense.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_expense.branch_id,
                                    sum(COALESCE(accounting_expense_petty_expense.food, 0)) as foodTotal,
                                    sum(COALESCE(accounting_expense_petty_expense.supplies, 0)) as supplyTotal,
                                    sum(COALESCE(accounting_expense_petty_expense.load, 0)) as loadTotal,
                                    sum(COALESCE(accounting_expense_petty_expense.fare, 0)) as fareTotal,
                                    sum(accounting_expense_petty_expense.food + accounting_expense_petty_expense.supplies + accounting_expense_petty_expense.load + accounting_expense_petty_expense.fare) as total,
                                    DATE(accounting_expense.datetime) as newDate,
                                    accounting_expense.amount_type as amountType
                                from accounting_expense_petty_expense
                                LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_petty_expense.id and accounting_expense.expense_type_id = 1
                                WHERE accounting_expense.branch_id = '.$branch->id.' and
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_expense_petty_expense.charge_to <> "Icon"
                                    and accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate
                            ) pettyExpense on pettyExpense.branch_id = accounting_branch.id
                    GROUP BY accounting_branch.id
                )'], 'finalPettyExpense.id = accounting_branch.id')
                ->leftJoin(['finalPhotocopyExpense' => '(
                    SELECT
                            accounting_branch.id,
                            sum(COALESCE(photocopyExpense.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_expense.branch_id,
                                    sum(COALESCE(accounting_expense_photocopy_expense.total_amount, 0)) as total,
                                    DATE(accounting_expense.datetime) as newDate,
                                    accounting_expense.amount_type as amountType
                                from accounting_expense_photocopy_expense
                                LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2
                                WHERE accounting_expense.branch_id = '.$branch->id.' and
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_expense_photocopy_expense.charge_to <> "Icon"
                                    and accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate
                            ) photocopyExpense on photocopyExpense.branch_id = accounting_branch.id
                    GROUP BY accounting_branch.id
                )'], 'finalPhotocopyExpense.id = accounting_branch.id')
                ->leftJoin(['finalOtherExpense' => '(
                    SELECT
                            accounting_branch.id,
                            sum(COALESCE(otherExpense.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_expense.branch_id,
                                    sum(COALESCE(accounting_expense_other_expense.amount, 0)) as total,
                                    DATE(accounting_expense.datetime) as newDate,
                                    accounting_expense.amount_type as amountType
                                from accounting_expense_other_expense
                                LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_other_expense.id and accounting_expense.expense_type_id = 3
                                WHERE accounting_expense.branch_id = '.$branch->id.' and
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_expense_other_expense.charge_to <> "Icon"
                                    and accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate
                            ) otherExpense on otherExpense.branch_id = accounting_branch.id 
                    GROUP BY accounting_branch.id
                )'], 'finalOtherExpense.id = accounting_branch.id')
                ->leftJoin(['finalBankDeposits' => '(
                    SELECT
                            accounting_branch.id,
                            sum(COALESCE(bankDeposits.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_expense.branch_id,
                                    sum(COALESCE(accounting_expense_bank_deposit.amount, 0)) as total,
                                    DATE(accounting_expense.datetime) as newDate,
                                    accounting_expense.amount_type as amountType
                                from accounting_expense_bank_deposit
                                LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_bank_deposit.id and accounting_expense.expense_type_id = 4
                                WHERE accounting_expense.branch_id = '.$branch->id.' and
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 23:59:59"
                                GROUP BY newDate
                            ) bankDeposits on bankDeposits.branch_id = accounting_branch.id
                    GROUP BY accounting_branch.id
                )'], 'finalBankDeposits.id = accounting_branch.id')
                ->leftJoin(['finalOperatingExpense' => '(
                    SELECT
                            accounting_branch.id,
                            sum(COALESCE(operatingExpense.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_expense.branch_id,
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
                                WHERE accounting_expense.branch_id = '.$branch->id.' and
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_expense_operating_expense.charge_to <> "Icon"
                                    and accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate
                            ) operatingExpense on operatingExpense.branch_id = accounting_branch.id
                    GROUP BY accounting_branch.id
                )'], 'finalOperatingExpense.id = accounting_branch.id')
                ->leftJoin(['finalBudgetProposal' => '(
                    SELECT
                            accounting_branch.id,
                            sum(COALESCE(budgetProposal.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_income.branch_id,
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
                                WHERE accounting_income.branch_id = '.$branch->id.' and
                                    accounting_income.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 23:59:59" and 
                                    accounting_income.amount_type <> "Cash" and
                                    accounting_income_budget_proposal.approval_status = "Approved"
                                GROUP BY newDate
                            ) budgetProposal on budgetProposal.branch_id = accounting_branch.id
                    GROUP BY accounting_branch.id
                )'], 'finalBudgetProposal.id = accounting_branch.id')
                ->leftJoin(['finalBranchTransfer' => '(
                    SELECT
                            accounting_branch.id,
                            sum(COALESCE(branchTransfer.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_expense.branch_id,
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
                                WHERE accounting_expense.branch_id = '.$branch->id.' and
                                    accounting_expense.datetime between "'.$cutoff['start'].' 00:00:00" and "'.$date.' 23:59:59" and 
                                    accounting_expense_branch_transfer.amount_source = "Cash On Bank" and
                                    accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate
                            ) branchTransfer on branchTransfer.branch_id = accounting_branch.id
                    GROUP BY accounting_branch.id
                )'], 'finalBranchTransfer.id = accounting_branch.id')
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
        $branch = Branch::findOne($this->id);

        $beginningcoh = BeginningCoh::find()
                        ->select(['sum(accounting_audit_beginning_coh.cash_on_bank) as total'])
                        ->leftJoin('accounting_branch_program', 'accounting_branch_program.id = accounting_audit_beginning_coh.branch_program_id')
                        ->where(['date(datetime)' => $cutoff['start'], 'accounting_branch_program.branch_id' => $branch->id])->asArray()->all();
        $beginningcohAmount = $beginningcoh ? $beginningcoh['total'] : 0;

        $data = Branch::find()
                ->select([
                    'accounting_branch.id as id',
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
                            accounting_branch.id,
                            sum(COALESCE(incomeEnrolments.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN 
                            (
                                SELECT 
                                    accounting_income.branch_id,
                                    sum(COALESCE(accounting_income_enrolment.amount, 0)) as total,
                                    DATE(accounting_income.datetime) as newDate,
                                    accounting_income.amount_type as amountType
                                from accounting_income_enrolment
                                LEFT JOIN accounting_income on accounting_income.income_id = accounting_income_enrolment.id and accounting_income.income_type_id = 1
                                LEFT JOIN accounting_package_student on accounting_package_student.season_id = accounting_income_enrolment.season_id and accounting_package_student.student_id = accounting_income_enrolment.student_id
                                LEFT JOIN accounting_package on accounting_package.id = accounting_package_student.package_id
                                LEFT JOIN accounting_package_type on accounting_package_type.id = accounting_package.package_type_id 
                                WHERE accounting_income.branch_id = '.$branch->id.' and
                                    accounting_income.datetime between "'.$date.' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_package_type.id not in ("5", "7")
                                    and accounting_income.amount_type <> "Cash"
                                GROUP BY newDate
                            ) incomeEnrolments on incomeEnrolments.branch_id = accounting_branch.id
                    GROUP BY accounting_branch.id
                )'], 'finalIncomeEnrolments.id = accounting_branch.id')
                ->leftJoin(['finalFreebies' => '(
                    SELECT
                            accounting_branch.id,
                            sum(COALESCE(freebies.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_income.branch_id,
                                    sum(COALESCE(accounting_income_freebies_and_icons.amount, 0)) as total,
                                    DATE(accounting_income.datetime) as newDate,
                                    accounting_income.amount_type as amountType
                                from accounting_income_freebies_and_icons
                                LEFT JOIN accounting_income on accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2
                                WHERE accounting_income.branch_id = '.$branch->id.' and
                                    accounting_income.datetime between "'.$date.' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_income_freebies_and_icons.code_id <> "9"
                                    and accounting_income.amount_type <> "Cash"
                                GROUP BY newDate
                            ) freebies on freebies.branch_id = accounting_branch.id 
                    GROUP BY accounting_branch.id
                )'], 'finalFreebies.id = accounting_branch.id')
                ->leftJoin(['finalPettyExpense' => '(
                    SELECT
                            accounting_branch.id,
                            sum(COALESCE(pettyExpense.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_expense.branch_id,
                                    sum(COALESCE(accounting_expense_petty_expense.food, 0)) as foodTotal,
                                    sum(COALESCE(accounting_expense_petty_expense.supplies, 0)) as supplyTotal,
                                    sum(COALESCE(accounting_expense_petty_expense.load, 0)) as loadTotal,
                                    sum(COALESCE(accounting_expense_petty_expense.fare, 0)) as fareTotal,
                                    sum(accounting_expense_petty_expense.food + accounting_expense_petty_expense.supplies + accounting_expense_petty_expense.load + accounting_expense_petty_expense.fare) as total,
                                    DATE(accounting_expense.datetime) as newDate,
                                    accounting_expense.amount_type as amountType
                                from accounting_expense_petty_expense
                                LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_petty_expense.id and accounting_expense.expense_type_id = 1
                                WHERE accounting_expense.branch_id = '.$branch->id.' and
                                    accounting_expense.datetime between "'.$date.' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_expense_petty_expense.charge_to <> "Icon"
                                    and accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate
                            ) pettyExpense on pettyExpense.branch_id = accounting_branch.id
                    GROUP BY accounting_branch.id
                )'], 'finalPettyExpense.id = accounting_branch.id')
                ->leftJoin(['finalPhotocopyExpense' => '(
                    SELECT
                            accounting_branch.id,
                            sum(COALESCE(photocopyExpense.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_expense.branch_id,
                                    sum(COALESCE(accounting_expense_photocopy_expense.total_amount, 0)) as total,
                                    DATE(accounting_expense.datetime) as newDate,
                                    accounting_expense.amount_type as amountType
                                from accounting_expense_photocopy_expense
                                LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2
                                WHERE accounting_expense.branch_id = '.$branch->id.' and
                                    accounting_expense.datetime between "'.$date.' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_expense_photocopy_expense.charge_to <> "Icon"
                                    and accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate
                            ) photocopyExpense on photocopyExpense.branch_id = accounting_branch.id
                    GROUP BY accounting_branch.id
                )'], 'finalPhotocopyExpense.id = accounting_branch.id')
                ->leftJoin(['finalOtherExpense' => '(
                    SELECT
                            accounting_branch.id,
                            sum(COALESCE(otherExpense.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_expense.branch_id,
                                    sum(COALESCE(accounting_expense_other_expense.amount, 0)) as total,
                                    DATE(accounting_expense.datetime) as newDate,
                                    accounting_expense.amount_type as amountType
                                from accounting_expense_other_expense
                                LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_other_expense.id and accounting_expense.expense_type_id = 3
                                WHERE accounting_expense.branch_id = '.$branch->id.' and
                                    accounting_expense.datetime between "'.$date.' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_expense_other_expense.charge_to <> "Icon"
                                    and accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate
                            ) otherExpense on otherExpense.branch_id = accounting_branch.id 
                    GROUP BY accounting_branch.id
                )'], 'finalOtherExpense.id = accounting_branch.id')
                ->leftJoin(['finalBankDeposits' => '(
                    SELECT
                            accounting_branch.id,
                            sum(COALESCE(bankDeposits.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_expense.branch_id,
                                    sum(COALESCE(accounting_expense_bank_deposit.amount, 0)) as total,
                                    DATE(accounting_expense.datetime) as newDate,
                                    accounting_expense.amount_type as amountType
                                from accounting_expense_bank_deposit
                                LEFT JOIN accounting_expense on accounting_expense.expense_id = accounting_expense_bank_deposit.id and accounting_expense.expense_type_id = 4
                                WHERE accounting_expense.branch_id = '.$branch->id.' and
                                    accounting_expense.datetime between "'.$date.' 00:00:00" and "'.$date.' 23:59:59"
                                GROUP BY newDate
                            ) bankDeposits on bankDeposits.branch_id = accounting_branch.id
                    GROUP BY accounting_branch.id
                )'], 'finalBankDeposits.id = accounting_branch.id')
                ->leftJoin(['finalOperatingExpense' => '(
                    SELECT
                            accounting_branch.id,
                            sum(COALESCE(operatingExpense.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_expense.branch_id,
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
                                WHERE accounting_expense.branch_id = '.$branch->id.' and
                                    accounting_expense.datetime between "'.$date.' 00:00:00" and "'.$date.' 23:59:59" and
                                    accounting_expense_operating_expense.charge_to <> "Icon"
                                    and accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate
                            ) operatingExpense on operatingExpense.branch_id = accounting_branch.id
                    GROUP BY accounting_branch.id
                )'], 'finalOperatingExpense.id = accounting_branch.id')
                ->leftJoin(['finalBudgetProposal' => '(
                    SELECT
                            accounting_branch.id,
                            sum(COALESCE(budgetProposal.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_income.branch_id,
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
                                WHERE accounting_income.branch_id = '.$branch->id.' and
                                    accounting_income.datetime between "'.$date.' 00:00:00" and "'.$date.' 23:59:59" and 
                                    accounting_income.amount_type <> "Cash" and
                                    accounting_income_budget_proposal.approval_status = "Approved"
                                GROUP BY newDate
                            ) budgetProposal on budgetProposal.branch_id = accounting_branch.id
                    GROUP BY accounting_branch.id
                )'], 'finalBudgetProposal.id = accounting_branch.id')
                ->leftJoin(['finalBranchTransfer' => '(
                    SELECT
                            accounting_branch.id,
                            sum(COALESCE(branchTransfer.total, 0)) as total
                            from accounting_branch
                            LEFT JOIN
                            (
                                SELECT 
                                    accounting_expense.branch_id,
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
                                WHERE accounting_expense.branch_id = '.$branch->id.' and
                                    accounting_expense.datetime between "'.$date.' 00:00:00" and "'.$date.' 23:59:59" and 
                                    accounting_expense_branch_transfer.amount_source = "Cash On Bank" and
                                    accounting_expense.amount_type <> "Cash"
                                GROUP BY newDate
                            ) branchTransfer on branchTransfer.branch_id = accounting_branch.id
                    GROUP BY accounting_branch.id
                )'], 'finalBranchTransfer.id = accounting_branch.id')
                ->where(['accounting_branch.id' => $this->id])
                ->asArray()
                ->one();

        $data['beginningcoh'] = $beginningcohAmount;
        $data['cutoff'] = $cutoff;

        return $data;
    }
}

