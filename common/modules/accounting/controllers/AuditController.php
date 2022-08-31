<?php

namespace common\modules\accounting\controllers;

use Yii;
use common\modules\accounting\models\ArchiveSeason;
use common\modules\accounting\models\AccessProgram;
use common\modules\accounting\models\Branch;
use common\modules\accounting\models\BranchProgram;
use common\modules\accounting\models\Notification;
use common\modules\accounting\models\BeginningCoh;
use common\modules\accounting\models\Audit;
use common\modules\accounting\models\Season;
use common\modules\accounting\models\Income;
use common\modules\accounting\models\Expense;
use common\modules\accounting\models\Denomination;
use common\modules\accounting\models\IncomeEnrolment;
use common\modules\accounting\models\FreebieAndIcon;
use common\modules\accounting\models\BudgetProposal;
use common\modules\accounting\models\PettyExpense;
use common\modules\accounting\models\PhotocopyExpense;
use common\modules\accounting\models\BankDeposit;
use common\modules\accounting\models\OperatingExpense;
use common\modules\accounting\models\OtherExpense;
use common\modules\accounting\models\BranchTransfer;
use common\modules\accounting\models\TargetEnrolee;
use common\modules\accounting\models\TargetExpense;
use common\modules\accounting\models\StudentTuition;
use common\modules\accounting\models\AuditSearch;
use yii\web\Controller;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use kartik\mpdf\Pdf;
use yii\filters\AccessControl;
/**
 * AuditController implements the CRUD actions for Audit model.
 */
class AuditController extends Controller
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
                'only' => ['index','cut-off-summary', 'cut-off-summary-icon'],
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['manageAudit'],
                    ],
                    [
                        'actions' => ['cut-off-summary'],
                        'allow' => true,
                        'roles' => ['cutOffSummaryAudit'],
                    ],
                    [
                        'actions' => ['cut-off-summary-icon'],
                        'allow' => true,
                        'roles' => ['cutOffSummaryIconAudit'],
                    ],
                ],
            ],
        ];
    }

    public function actionSeasonList($id) {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $archivedSeasons = ArchiveSeason::find()->select(['season_id as id'])->asArray()->all();
        $archivedSeasons = ArrayHelper::map($archivedSeasons, 'id', 'id');
        $seasons = Season::find()
                    ->select(['accounting_season.id as id', 'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name'])
                    ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                    ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                    ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                    ->where(['accounting_branch_program.id' => $id])
                    ->andWhere(['not in', 'accounting_season.id', $archivedSeasons])
                    ->asArray()
                    ->orderBy(['accounting_season.id' => SORT_DESC])
                    ->all();

        $out = [];

        $out[] = ['id' => '', 'text' => ''];

        if($seasons)
        {
            foreach($seasons as $season)
            {
                $out[] = ['id' => $season['id'], 'text' => $season['name']];
            }
        }

        return $out;
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
    public function actionGenerate($id, $branch_program_id = '', $season = '')
    {
        $cutoff = $this->check_in_cutoff($id);
        $dates = $this->dateRange($cutoff['start'], $cutoff['end']);
        $data = [];
        $branchProgramName = '';
        $seasonName = '';
        $beginningcohAmount = 0;
        $beginningcobAmount = 0;

        $incomeEnrolments = IncomeEnrolment::find()
            ->select([
                'accounting_income.branch_id',
                'accounting_income.program_id',
                'DATE(accounting_income.datetime) as newDate',
                'accounting_income.amount_type as amountType',
                'sum(accounting_income_enrolment.amount) as total',
            ])
            ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_enrolment.id and accounting_income.income_type_id = 1')
            ->leftJoin('accounting_package_student', 'accounting_package_student.season_id = accounting_income_enrolment.season_id and accounting_package_student.student_id = accounting_income_enrolment.student_id')
            ->leftJoin('accounting_package', 'accounting_package.id = accounting_package_student.package_id')
            ->leftJoin('accounting_package_type', 'accounting_package_type.id = accounting_package.package_type_id')
            ->leftJoin('accounting_branch_program', 'accounting_branch_program.branch_id = accounting_income.branch_id and accounting_branch_program.program_id = accounting_income.program_id');

        $freebiesAndIcons = FreebieAndIcon::find()
            ->select([
                'accounting_income.branch_id',
                'accounting_income.program_id',
                'DATE(accounting_income.datetime) as newDate',
                'accounting_income.amount_type as amountType',
                'sum(amount) as total',
            ])
            ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
            ->leftJoin('accounting_branch_program', 'accounting_branch_program.branch_id = accounting_income.branch_id and accounting_branch_program.program_id = accounting_income.program_id');

        $budgetProposals = BudgetProposal::find()
            ->select([
                'accounting_income.branch_id',
                'accounting_income.program_id',
                'DATE(accounting_income.datetime) as newDate',
                'accounting_income.amount_type as amountType',
                'sum(COALESCE(particulars.total, 0)) as total',
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
            ->leftJoin('accounting_branch_program', 'accounting_branch_program.branch_id = accounting_income.branch_id and accounting_branch_program.program_id = accounting_income.program_id');

        $pettyExpenses = PettyExpense::find()
            ->select([
                'accounting_expense.branch_id',
                'accounting_expense.program_id',
                'DATE(accounting_expense.datetime) as newDate',
                'accounting_expense.amount_type as amountType',
                'sum(accounting_expense_petty_expense.food) as foodTotal',
                'sum(accounting_expense_petty_expense.supplies) as supplyTotal',
                'sum(accounting_expense_petty_expense.load) as loadTotal',
                'sum(accounting_expense_petty_expense.fare) as fareTotal',
                'sum(accounting_expense_petty_expense.food + accounting_expense_petty_expense.supplies + accounting_expense_petty_expense.load + accounting_expense_petty_expense.fare) as total',
            ])
            ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_petty_expense.id and accounting_expense.expense_type_id = 1')
            ->leftJoin('accounting_branch_program', 'accounting_branch_program.branch_id = accounting_expense.branch_id and accounting_branch_program.program_id = accounting_expense.program_id');

        $photocopyExpenses = PhotocopyExpense::find()
            ->select([
                'accounting_expense.branch_id',
                'accounting_expense.program_id',
                'DATE(accounting_expense.datetime) as newDate',
                'accounting_expense.amount_type as amountType',
                'sum(total_amount) as total',
            ])
            ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
            ->leftJoin('accounting_branch_program', 'accounting_branch_program.branch_id = accounting_expense.branch_id and accounting_branch_program.program_id = accounting_expense.program_id');

        $otherExpenses = OtherExpense::find()
            ->select([
                'accounting_expense.branch_id',
                'accounting_expense.program_id',
                'DATE(accounting_expense.datetime) as newDate',
                'accounting_expense.amount_type as amountType',
                'sum(amount) as total',
            ])
            ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_other_expense.id and accounting_expense.expense_type_id = 3')
            ->leftJoin('accounting_branch_program', 'accounting_branch_program.branch_id = accounting_expense.branch_id and accounting_branch_program.program_id = accounting_expense.program_id');

        $bankDeposits = BankDeposit::find()
            ->select([
                'accounting_expense.branch_id',
                'accounting_expense.program_id',
                'DATE(accounting_expense.datetime) as newDate',
                'accounting_expense.amount_type as amountType',
                'sum(amount) as total',
            ])
            ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_bank_deposit.id and accounting_expense.expense_type_id = 4')
            ->leftJoin('accounting_branch_program', 'accounting_branch_program.branch_id = accounting_expense.branch_id and accounting_branch_program.program_id = accounting_expense.program_id');

        $operatingExpenses = OperatingExpense::find()
            ->select([
                'accounting_expense.branch_id',
                'accounting_expense.program_id',
                'DATE(accounting_expense.datetime) as newDate',
                'accounting_expense.amount_type as amountType',
                'sum(accounting_expense_operating_expense.staff_salary) as staffSalaryTotal',
                'sum(accounting_expense_operating_expense.cash_pf) as cashPfTotal',
                'sum(accounting_expense_operating_expense.rent) as rentTotal',
                'sum(accounting_expense_operating_expense.utilities) as utilitiesTotal',
                'sum(accounting_expense_operating_expense.equipment_and_labor) as equipmentAndLaborTotal',
                'sum(accounting_expense_operating_expense.bir_and_docs) as bir_and_docsTotal',
                'sum(accounting_expense_operating_expense.marketing) as marketingTotal',
                'sum(
                    accounting_expense_operating_expense.staff_salary + 
                    accounting_expense_operating_expense.cash_pf + 
                    accounting_expense_operating_expense.rent + 
                    accounting_expense_operating_expense.utilities + 
                    accounting_expense_operating_expense.equipment_and_labor + 
                    accounting_expense_operating_expense.bir_and_docs + 
                    accounting_expense_operating_expense.marketing
                ) as total',
            ])
            ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_operating_expense.id and accounting_expense.expense_type_id = 5')
            ->leftJoin('accounting_branch_program', 'accounting_branch_program.branch_id = accounting_expense.branch_id and accounting_branch_program.program_id = accounting_expense.program_id');

        $branchTransfers = BranchTransfer::find()
            ->select([
                'accounting_expense.branch_id',
                'accounting_expense.program_id',
                'DATE(accounting_expense.datetime) as newDate',
                'accounting_expense.amount_type as amountType',
                'sum(COALESCE(particulars.total, 0)) as total',
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
            ->leftJoin('accounting_branch_program', 'accounting_branch_program.branch_id = accounting_expense.branch_id and accounting_branch_program.program_id = accounting_expense.program_id');

        if($branch_program_id != '')
        {
            $branchProgram = BranchProgram::findOne($branch_program_id);
            $branchProgramName = $branchProgram->branchProgramName;

            if($season != '')
            {
                $season = Season::findOne($season);
                $seasonName = $season->seasonName;

                $beginningcoh = BeginningCoh::find()->where(['date(datetime)' => $cutoff['start'], 'season_id' => $season->id])->one();
                $beginningcohAmount = $beginningcoh ? $beginningcoh->cash_on_hand : 0;
                $beginningcobAmount = $beginningcoh ? $beginningcoh->cash_on_bank : 0;

                $incomeEnrolments = $incomeEnrolments->andWhere([
                    'accounting_income_enrolment.season_id' => $season->id,
                ]);

                $freebiesAndIcons = $freebiesAndIcons->andWhere([
                    'accounting_income_freebies_and_icons.season_id' => $season->id,
                ]);

                $budgetProposals = $budgetProposals->andWhere([
                    'accounting_income_budget_proposal.season_id' => $season->id,
                ]);

                $pettyExpenses = $pettyExpenses->andWhere([
                    'accounting_expense.season_id' => $season->id,
                ]);

                $photocopyExpenses = $photocopyExpenses->andWhere([
                    'accounting_expense.season_id' => $season->id,
                ]);

                $otherExpenses = $otherExpenses->andWhere([
                    'accounting_expense.season_id' => $season->id,
                ]);

                $bankDeposits = $bankDeposits->andWhere([
                    'accounting_expense.season_id' => $season->id,
                ]);

                $operatingExpenses = $operatingExpenses->andWhere([
                    'accounting_expense.season_id' => $season->id,
                ]);

                $branchTransfers = $branchTransfers->andWhere([
                    'accounting_expense.season_id' => $season->id,
                ]);

            }else{
                $beginningcoh = BeginningCoh::find()->where(['date(datetime)' => $cutoff['start'], 'branch_program_id' => $branchProgram->id])->one();
                $beginningcohAmount = $beginningcoh ? $beginningcoh->cash_on_hand : 0;
                $beginningcobAmount = $beginningcoh ? $beginningcoh->cash_on_bank : 0;

                $incomeEnrolments = $incomeEnrolments->andWhere([
                    'accounting_income.branch_id' => $branchProgram->branch_id,
                    'accounting_income.program_id' => $branchProgram->program_id
                ]);

                $freebiesAndIcons = $freebiesAndIcons->andWhere([
                    'accounting_income.branch_id' => $branchProgram->branch_id,
                    'accounting_income.program_id' => $branchProgram->program_id
                ]);

                $budgetProposals = $budgetProposals->andWhere([
                    'accounting_income.branch_id' => $branchProgram->branch_id,
                    'accounting_income.program_id' => $branchProgram->program_id
                ]);

                $pettyExpenses = $pettyExpenses->andWhere([
                    'accounting_expense.branch_id' => $branchProgram->branch_id,
                    'accounting_expense.program_id' => $branchProgram->program_id
                ]);

                $photocopyExpenses = $photocopyExpenses->andWhere([
                    'accounting_expense.branch_id' => $branchProgram->branch_id,
                    'accounting_expense.program_id' => $branchProgram->program_id
                ]);

                $otherExpenses = $otherExpenses->andWhere([
                    'accounting_expense.branch_id' => $branchProgram->branch_id,
                    'accounting_expense.program_id' => $branchProgram->program_id
                ]);

                $bankDeposits = $bankDeposits->andWhere([
                    'accounting_expense.branch_id' => $branchProgram->branch_id,
                    'accounting_expense.program_id' => $branchProgram->program_id
                ]);

                $operatingExpenses = $operatingExpenses->andWhere([
                    'accounting_expense.branch_id' => $branchProgram->branch_id,
                    'accounting_expense.program_id' => $branchProgram->program_id
                ]);

                $branchTransfers = $branchTransfers->andWhere([
                    'accounting_expense.branch_id' => $branchProgram->branch_id,
                    'accounting_expense.program_id' => $branchProgram->program_id
                ]);

            }
        }else{
            $user_info = Yii::$app->user->identity->userinfo;
            $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
            $rolenames =  ArrayHelper::map($roles, 'name','name');

            $branchProgramIds = [];

            if(in_array('TopManagement',$rolenames)){
                $branchPrograms = BranchProgram::find()
                                    ->select(['accounting_branch_program.id as id', 'concat(accounting_branch.name," - ",accounting_program.name) as branchProgramName'])
                                    ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                                    ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                                    ->asArray()
                                    ->orderBy(['branchProgramName' => SORT_ASC])
                                    ->all();
            }else{
                $branchPrograms = BranchProgram::find()
                                    ->select(['accounting_branch_program.id as id', 'concat(accounting_branch.name," - ",accounting_program.name) as branchProgramName'])
                                    ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                                    ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                                    ->where(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                    ->asArray()
                                    ->orderBy(['branchProgramName' => SORT_ASC])
                                    ->all();
            }

            if(!empty($branchPrograms))
            {
                foreach($branchPrograms as $bp)
                {
                    $branchProgramIds[] = $bp['id'];
                }
            }

            $beginningcoh = BeginningCoh::find()->where(['date(datetime)' => $cutoff['start']])->all();
            if($beginningcoh)
            {
                foreach($beginningcoh as $coh)
                {
                    $beginningcohAmount+=$coh->cash_on_hand;
                    $beginningcobAmount+=$coh->cash_on_bank;
                }
            }

            $branchProgramName = 'All Branch Programs';
            $data = [];

            $incomeEnrolments = $incomeEnrolments->andWhere([
                'in', 'accounting_branch_program.id', $branchProgramIds
            ]);

            $freebiesAndIcons = $freebiesAndIcons->andWhere([
                'in', 'accounting_branch_program.id', $branchProgramIds
            ]);

            $budgetProposals = $budgetProposals->andWhere([
                'in', 'accounting_branch_program.id', $branchProgramIds
            ]);

            $pettyExpenses = $pettyExpenses->andWhere([
                'in', 'accounting_branch_program.id', $branchProgramIds
            ]);

            $photocopyExpenses = $photocopyExpenses->andWhere([
                'in', 'accounting_branch_program.id', $branchProgramIds
            ]);

            $otherExpenses = $otherExpenses->andWhere([
                'in', 'accounting_branch_program.id', $branchProgramIds
            ]);

            $bankDeposits = $bankDeposits->andWhere([
                'in', 'accounting_branch_program.id', $branchProgramIds
            ]);

            $operatingExpenses = $operatingExpenses->andWhere([
                'in', 'accounting_branch_program.id', $branchProgramIds
            ]);

            $branchTransfers = $branchTransfers->andWhere([
                'in', 'accounting_branch_program.id', $branchProgramIds
            ]);
        }

        $totals = [];
        $totals2 = [];

        if(!empty($dates))
        {
            foreach($dates as $date)
            {
                $totals['incomes'][$date] = 0;
                $totals['expenses'][$date] = 0;
                $totals2['incomes'][$date] = 0;
                $totals2['expenses'][$date] = 0;
            }
        }
        
        $incomeEnrolments = $incomeEnrolments
            ->andWhere(['between', 'accounting_income.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
            ->groupBy(['newDate', 'amountType'])
            ->asArray()
            ->all();

        $freebiesAndIcons = $freebiesAndIcons
            ->andWhere(['between', 'accounting_income.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
            ->groupBy(['newDate', 'amountType'])
            ->asArray()
            ->all();

        $budgetProposals = $budgetProposals
            ->andWhere([
                'accounting_income_budget_proposal.approval_status' => 'Approved'
            ])
            ->andWhere(['between', 'accounting_income.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
            ->groupBy(['newDate', 'amountType'])
            ->asArray()
            ->all();

        $pettyExpenses = $pettyExpenses
            ->andWhere(['between', 'accounting_expense.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
            ->andWhere(['<>', 'charge_to', 'Icon'])
            ->groupBy(['newDate', 'amountType'])
            ->asArray()
            ->all();

        $photocopyExpenses = $photocopyExpenses
            ->andWhere(['between', 'accounting_expense.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
            ->andWhere(['<>', 'charge_to', 'Icon'])
            ->groupBy(['newDate', 'amountType'])
            ->asArray()
            ->all();

        $otherExpenses = $otherExpenses
            ->andWhere(['between', 'accounting_expense.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
            ->andWhere(['<>', 'charge_to', 'Icon'])
            ->groupBy(['newDate', 'amountType'])
            ->asArray()
            ->all();

        $bankDeposits = $bankDeposits
            ->andWhere(['between', 'accounting_expense.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
            ->groupBy(['newDate', 'amountType'])
            ->asArray()
            ->all();

        $operatingExpenses = $operatingExpenses
            ->andWhere(['between', 'accounting_expense.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
            ->andWhere(['<>', 'charge_to', 'Icon'])
            ->groupBy(['newDate', 'amountType'])
            ->asArray()
            ->all();

        $branchTransfers = $branchTransfers
            ->andWhere(['between', 'accounting_expense.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
            ->groupBy(['newDate', 'amountType'])
            ->asArray()
            ->all();

        if($incomeEnrolments)
        {
            foreach($incomeEnrolments as $enrolment)
            {
                if($enrolment['amountType'] == 'Cash')
                {
                    $data['incomes']['enrolments'][$enrolment['amountType']][$enrolment['newDate']] = $enrolment;
                }else{
                    $data['incomes']['enrolments']['Non-Cash'][$enrolment['newDate']] = $enrolment;
                }
            }
        }

        if($freebiesAndIcons)
        {
            foreach($freebiesAndIcons as $freebies)
            {
                if($freebies['amountType'] == 'Cash')
                {
                    $data['incomes']['freebies'][$freebies['amountType']][$freebies['newDate']] = $freebies;
                }else{
                    $data['incomes']['freebies']['Non-Cash'][$freebies['newDate']] = $freebies;
                }
            }
        }

        if($budgetProposals)
        {
            foreach($budgetProposals as $budgetProposal)
            {
                if($budgetProposal['amountType'] == 'Cash')
                {
                    $data['incomes']['budgetProposals'][$budgetProposal['amountType']][$budgetProposal['newDate']] = $budgetProposal;
                }else{
                    $data['incomes']['budgetProposals']['Non-Cash'][$budgetProposal['newDate']] = $budgetProposal;
                }
            }
        }

        if($pettyExpenses)
        {
            foreach($pettyExpenses as $pettyExpense)
            {
                if($pettyExpense['amountType'] == 'Cash')
                {
                    $data['expenses']['pettyExpenses'][$pettyExpense['amountType']][$pettyExpense['newDate']] = $pettyExpense;
                }else{
                    $data['expenses']['pettyExpenses']['Non-Cash'][$pettyExpense['newDate']] = $pettyExpense;
                }
            }
        }

        if($photocopyExpenses)
        {
            foreach($photocopyExpenses as $photocopyExpense)
            {
                if($photocopyExpense['amountType'] == 'Cash')
                {
                    $data['expenses']['photocopyExpenses'][$photocopyExpense['amountType']][$photocopyExpense['newDate']] = $photocopyExpense;
                }else{
                    $data['expenses']['photocopyExpenses']['Non-Cash'][$photocopyExpense['newDate']] = $photocopyExpense;
                }
            }
        }

        if($otherExpenses)
        {
            foreach($otherExpenses as $otherExpense)
            {
                if($otherExpense['amountType'] == 'Cash')
                {
                    $data['expenses']['otherExpenses'][$otherExpense['amountType']][$otherExpense['newDate']] = $otherExpense;
                }else{
                    $data['expenses']['otherExpenses']['Non-Cash'][$otherExpense['newDate']] = $otherExpense;
                }
            }
        }

        if($bankDeposits)
        {
            foreach($bankDeposits as $bankDeposit)
            {
                if($bankDeposit['amountType'] == 'Cash')
                {
                    $data['expenses']['bankDeposits'][$bankDeposit['amountType']][$bankDeposit['newDate']] = $bankDeposit;
                }else{
                    $data['expenses']['bankDeposits']['Non-Cash'][$bankDeposit['newDate']] = $bankDeposit;
                }
            }
        }

        if($operatingExpenses)
        {
            foreach($operatingExpenses as $operatingExpense)
            {
                if($operatingExpense['amountType'] == 'Cash')
                {
                    $data['expenses']['operatingExpenses'][$operatingExpense['amountType']][$operatingExpense['newDate']] = $operatingExpense;
                }else{
                    $data['expenses']['operatingExpenses']['Non-Cash'][$operatingExpense['newDate']] = $operatingExpense;
                }
            }
        }

        if($branchTransfers)
        {
            foreach($branchTransfers as $branchTransfer)
            {
                if($branchTransfer['amountType'] == 'Cash')
                {
                    $data['expenses']['branchTransfers'][$branchTransfer['amountType']][$branchTransfer['newDate']] = $branchTransfer;
                }else{
                    $data['expenses']['branchTransfers']['Non-Cash'][$branchTransfer['newDate']] = $branchTransfer;
                }
            }
        }

        $content = $this->renderPartial('_cutoff_summary', [
            'id' => $id,
            'cutoff' => $cutoff,
            'dates' => $dates,
            'branchProgram' => $branchProgram,
            'branchProgramName' => $branchProgramName,
            'season' => $season,
            'seasonName' => $seasonName,
            'data' => $data,
            'totals' => $totals,
            'totals2' => $totals2,
            'beginningcohAmount' => $beginningcohAmount,
            'beginningcobAmount' => $beginningcobAmount,
        ]);

        $pdf = new Pdf([
        'mode' => Pdf::MODE_CORE,
        'format' => Pdf::FORMAT_LEGAL, 
        'orientation' => Pdf::ORIENT_LANDSCAPE, 
        'destination' => Pdf::DEST_DOWNLOAD, 
        'filename' => 'Cut-Off Summary Report: Program - '.$branchProgramName.' - ('.$cutoff['start'].' - '.$cutoff['end'].').pdf',
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
        'options' => ['title' => 'Cut-Off Summary Report: Program'],
        'methods' => [ 
            'SetHeader'=>['Cut-Off Summary Report: Program - '.$branchProgramName], 
            'SetFooter'=>['Page {PAGENO}'],
        ]
        ]);
        $response = Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_RAW;
        $headers = Yii::$app->response->headers;
        $headers->add('Content-Type', 'application/pdf');
        return $pdf->render();   
    }

    public function actionGenerateIcon($id, $branch_program_id = '', $season = '')
    {
        
        $cutoff = $this->check_in_cutoff($id);
        $dates = $this->dateRange($cutoff['start'], $cutoff['end']);
        $data = [];
        $branchProgramName = '';
        $seasonName = '';
        $beginningcohAmount = 0;
        $beginningcobAmount = 0;
        if($branch_program_id != '')
        {
            $branchProgram = BranchProgram::findOne($branch_program_id);
            $branchProgramName = $branchProgram->branchProgramName;

            $incomeEnrolments = IncomeEnrolment::find()
                                ->select([
                                    'accounting_income.branch_id',
                                    'accounting_income.program_id',
                                    'DATE(accounting_income.datetime) as newDate',
                                    'accounting_income.amount_type as amountType',
                                    'sum(accounting_income_enrolment.amount) as total',
                                ])
                                ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_enrolment.id and accounting_income.income_type_id = 1')
                                ->leftJoin('accounting_package_student', 'accounting_package_student.season_id = accounting_income_enrolment.season_id and accounting_package_student.student_id = accounting_income_enrolment.student_id')
                                ->leftJoin('accounting_package', 'accounting_package.id = accounting_package_student.package_id')
                                ->leftJoin('accounting_package_type', 'accounting_package_type.id = accounting_package.package_type_id')
                                ->where([
                                    'accounting_income.branch_id' => $branchProgram->branch_id,
                                    'accounting_income.program_id' => $branchProgram->program_id
                                ])
                                ->andWhere(['between', 'accounting_income.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
                                ->andWhere(['in', 'accounting_package_type.id', ['5','7']])
                                ->groupBy(['newDate', 'amountType'])
                                ->asArray()
                                ->all();

            if($incomeEnrolments)
            {
                foreach($incomeEnrolments as $enrolment)
                {
                    if($enrolment['amountType'] == 'Cash')
                    {
                        $data['incomes']['enrolments'][$enrolment['amountType']][$enrolment['newDate']] = $enrolment;
                    }else{
                        $data['incomes']['enrolments']['Non-Cash'][$enrolment['newDate']] = $enrolment;
                    }
                }
            }

            $freebiesAndIcons = FreebieAndIcon::find()
                                ->select([
                                    'accounting_income.branch_id',
                                    'accounting_income.program_id',
                                    'DATE(accounting_income.datetime) as newDate',
                                    'accounting_income.amount_type as amountType',
                                    'sum(amount) as total',
                                ])
                                ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                                ->where([
                                    'accounting_income.branch_id' => $branchProgram->branch_id,
                                    'accounting_income.program_id' => $branchProgram->program_id
                                ])
                                ->andWhere(['between', 'accounting_income.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
                                ->andWhere(['code_id' => '9'])
                                ->groupBy(['newDate', 'amountType'])
                                ->asArray()
                                ->all();

            if($freebiesAndIcons)
            {
                foreach($freebiesAndIcons as $freebies)
                {
                    if($freebies['amountType'] == 'Cash')
                    {
                        $data['incomes']['freebies'][$freebies['amountType']][$freebies['newDate']] = $freebies;
                    }else{
                        $data['incomes']['freebies']['Non-Cash'][$freebies['newDate']] = $freebies;
                    }
                }
            }

            $pettyExpenses = PettyExpense::find()
                                ->select([
                                    'accounting_expense.branch_id',
                                    'accounting_expense.program_id',
                                    'DATE(accounting_expense.datetime) as newDate',
                                    'accounting_expense.amount_type as amountType',
                                    'sum(accounting_expense_petty_expense.food) as foodTotal',
                                    'sum(accounting_expense_petty_expense.supplies) as supplyTotal',
                                    'sum(accounting_expense_petty_expense.load) as loadTotal',
                                    'sum(accounting_expense_petty_expense.fare) as fareTotal',
                                    'sum(accounting_expense_petty_expense.food + accounting_expense_petty_expense.supplies + accounting_expense_petty_expense.load + accounting_expense_petty_expense.fare) as total',
                                ])
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_petty_expense.id and accounting_expense.expense_type_id = 1')
                                ->where([
                                    'accounting_expense.branch_id' => $branchProgram->branch_id,
                                    'accounting_expense.program_id' => $branchProgram->program_id,
                                ])
                                ->andWhere(['between', 'accounting_expense.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
                                ->andWhere(['charge_to' => 'Icon'])
                                ->groupBy(['newDate', 'amountType'])
                                ->asArray()
                                ->all();

            if($pettyExpenses)
            {
                foreach($pettyExpenses as $pettyExpense)
                {
                    if($pettyExpense['amountType'] == 'Cash')
                    {
                        $data['expenses']['pettyExpenses'][$pettyExpense['amountType']][$pettyExpense['newDate']] = $pettyExpense;
                    }else{
                        $data['expenses']['pettyExpenses']['Non-Cash'][$pettyExpense['newDate']] = $pettyExpense;
                    }
                }
            }

            $photocopyExpenses = PhotocopyExpense::find()
                                ->select([
                                    'accounting_expense.branch_id',
                                    'accounting_expense.program_id',
                                    'DATE(accounting_expense.datetime) as newDate',
                                    'accounting_expense.amount_type as amountType',
                                    'sum(total_amount) as total',
                                ])
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                                ->where([
                                    'accounting_expense.branch_id' => $branchProgram->branch_id,
                                    'accounting_expense.program_id' => $branchProgram->program_id,
                                ])
                                ->andWhere(['between', 'accounting_expense.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
                                ->andWhere(['charge_to' => 'Icon'])
                                ->groupBy(['newDate', 'amountType'])
                                ->asArray()
                                ->all();

            if($photocopyExpenses)
            {
                foreach($photocopyExpenses as $photocopyExpense)
                {
                    if($photocopyExpense['amountType'] == 'Cash')
                    {
                        $data['expenses']['photocopyExpenses'][$photocopyExpense['amountType']][$photocopyExpense['newDate']] = $photocopyExpense;
                    }else{
                        $data['expenses']['photocopyExpenses']['Non-Cash'][$photocopyExpense['newDate']] = $photocopyExpense;
                    }
                }
            }

            $otherExpenses = OtherExpense::find()
                                ->select([
                                    'accounting_expense.branch_id',
                                    'accounting_expense.program_id',
                                    'DATE(accounting_expense.datetime) as newDate',
                                    'accounting_expense.amount_type as amountType',
                                    'sum(amount) as total',
                                ])
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_other_expense.id and accounting_expense.expense_type_id = 3')
                                ->where([
                                    'accounting_expense.branch_id' => $branchProgram->branch_id,
                                    'accounting_expense.program_id' => $branchProgram->program_id,
                                ])
                                ->andWhere(['between', 'accounting_expense.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
                                ->andWhere(['charge_to' => 'Icon'])
                                ->groupBy(['newDate', 'amountType'])
                                ->asArray()
                                ->all();

            if($otherExpenses)
            {
                foreach($otherExpenses as $otherExpense)
                {
                    if($otherExpense['amountType'] == 'Cash')
                    {
                        $data['expenses']['otherExpenses'][$otherExpense['amountType']][$otherExpense['newDate']] = $otherExpense;
                    }else{
                        $data['expenses']['otherExpenses']['Non-Cash'][$otherExpense['newDate']] = $otherExpense;
                    }
                }
            }

            $operatingExpenses = OperatingExpense::find()
                                ->select([
                                    'accounting_expense.branch_id',
                                    'accounting_expense.program_id',
                                    'DATE(accounting_expense.datetime) as newDate',
                                    'accounting_expense.amount_type as amountType',
                                    'sum(accounting_expense_operating_expense.staff_salary) as staffSalaryTotal',
                                    'sum(accounting_expense_operating_expense.cash_pf) as cashPfTotal',
                                    'sum(accounting_expense_operating_expense.rent) as rentTotal',
                                    'sum(accounting_expense_operating_expense.utilities) as utilitiesTotal',
                                    'sum(accounting_expense_operating_expense.equipment_and_labor) as equipmentAndLaborTotal',
                                    'sum(accounting_expense_operating_expense.bir_and_docs) as bir_and_docsTotal',
                                    'sum(accounting_expense_operating_expense.marketing) as marketingTotal',
                                    'sum(
                                        accounting_expense_operating_expense.staff_salary + 
                                        accounting_expense_operating_expense.cash_pf + 
                                        accounting_expense_operating_expense.rent + 
                                        accounting_expense_operating_expense.utilities + 
                                        accounting_expense_operating_expense.equipment_and_labor + 
                                        accounting_expense_operating_expense.bir_and_docs + 
                                        accounting_expense_operating_expense.marketing
                                    ) as total',
                                ])
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_operating_expense.id and accounting_expense.expense_type_id = 5')
                                ->where([
                                    'accounting_expense.branch_id' => $branchProgram->branch_id,
                                    'accounting_expense.program_id' => $branchProgram->program_id,
                                ])
                                ->andWhere(['between', 'accounting_expense.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
                                ->andWhere(['charge_to' => 'Icon'])
                                ->groupBy(['newDate', 'amountType'])
                                ->asArray()
                                ->all();

            if($operatingExpenses)
            {
                foreach($operatingExpenses as $operatingExpense)
                {
                    if($operatingExpense['amountType'] == 'Cash')
                    {
                        $data['expenses']['operatingExpenses'][$operatingExpense['amountType']][$operatingExpense['newDate']] = $operatingExpense;
                    }else{
                        $data['expenses']['operatingExpenses']['Non-Cash'][$operatingExpense['newDate']] = $operatingExpense;
                    }
                }
            }

            $totals = [];

            if(!empty($dates))
            {
                foreach($dates as $date)
                {
                    $totals['incomes'][$date] = 0;
                    $totals['expenses'][$date] = 0;
                }
            }

            if($season != '')
            {
                $season = Season::findOne($season);
                $seasonName = $season->seasonName;

                $incomeEnrolments = IncomeEnrolment::find()
                                ->select([
                                    'accounting_income.branch_id',
                                    'accounting_income.program_id',
                                    'DATE(accounting_income.datetime) as newDate',
                                    'accounting_income.amount_type as amountType',
                                    'sum(accounting_income_enrolment.amount) as total',
                                ])
                                ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_enrolment.id and accounting_income.income_type_id = 1')
                                ->leftJoin('accounting_package_student', 'accounting_package_student.season_id = accounting_income_enrolment.season_id and accounting_package_student.student_id = accounting_income_enrolment.student_id')
                                ->leftJoin('accounting_package', 'accounting_package.id = accounting_package_student.package_id')
                                ->leftJoin('accounting_package_type', 'accounting_package_type.id = accounting_package.package_type_id')
                                ->where([
                                    'accounting_income_enrolment.season_id' => $season->id,
                                ])
                                ->andWhere(['between', 'accounting_income.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
                                ->andWhere(['in', 'accounting_package_type.id', ['5','7']])
                                ->groupBy(['newDate', 'amountType'])
                                ->asArray()
                                ->all();

                if($incomeEnrolments)
                {
                    foreach($incomeEnrolments as $enrolment)
                    {
                        if($enrolment['amountType'] == 'Cash')
                        {
                            $data['incomes']['enrolments'][$enrolment['amountType']][$enrolment['newDate']] = $enrolment;
                        }else{
                            $data['incomes']['enrolments']['Non-Cash'][$enrolment['newDate']] = $enrolment;
                        }
                    }
                }

                $freebiesAndIcons = FreebieAndIcon::find()
                                    ->select([
                                        'accounting_income.branch_id',
                                        'accounting_income.program_id',
                                        'DATE(accounting_income.datetime) as newDate',
                                        'accounting_income.amount_type as amountType',
                                        'sum(amount) as total',
                                    ])
                                    ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                                    ->where([
                                        'accounting_income_freebies_and_icons.season_id' => $season->id,
                                    ])
                                    ->andWhere(['between', 'accounting_income.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
                                    ->andWhere(['code_id' => '9'])
                                    ->groupBy(['newDate', 'amountType'])
                                    ->asArray()
                                    ->all();

                if($freebiesAndIcons)
                {
                    foreach($freebiesAndIcons as $freebies)
                    {
                        if($freebies['amountType'] == 'Cash')
                        {
                            $data['incomes']['freebies'][$freebies['amountType']][$freebies['newDate']] = $freebies;
                        }else{
                            $data['incomes']['freebies']['Non-Cash'][$freebies['newDate']] = $freebies;
                        }
                    }
                }

                $pettyExpenses = PettyExpense::find()
                                    ->select([
                                        'accounting_expense.branch_id',
                                        'accounting_expense.program_id',
                                        'DATE(accounting_expense.datetime) as newDate',
                                        'accounting_expense.amount_type as amountType',
                                        'sum(accounting_expense_petty_expense.food) as foodTotal',
                                        'sum(accounting_expense_petty_expense.supplies) as supplyTotal',
                                        'sum(accounting_expense_petty_expense.load) as loadTotal',
                                        'sum(accounting_expense_petty_expense.fare) as fareTotal',
                                        'sum(accounting_expense_petty_expense.food + accounting_expense_petty_expense.supplies + accounting_expense_petty_expense.load + accounting_expense_petty_expense.fare) as total',
                                    ])
                                    ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_petty_expense.id and accounting_expense.expense_type_id = 1')
                                    ->where([
                                        'accounting_expense.branch_id' => $branchProgram->branch_id,
                                        'accounting_expense.program_id' => $branchProgram->program_id,
                                        'accounting_expense.season_id' => $season->id,
                                    ])
                                    ->andWhere(['between', 'accounting_expense.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
                                    ->andWhere(['charge_to' => 'Icon'])
                                    ->groupBy(['newDate', 'amountType'])
                                    ->asArray()
                                    ->all();

                if($pettyExpenses)
                {
                    foreach($pettyExpenses as $pettyExpense)
                    {
                        if($pettyExpense['amountType'] == 'Cash')
                        {
                            $data['expenses']['pettyExpenses'][$pettyExpense['amountType']][$pettyExpense['newDate']] = $pettyExpense;
                        }else{
                            $data['expenses']['pettyExpenses']['Non-Cash'][$pettyExpense['newDate']] = $pettyExpense;
                        }
                    }
                }

                $photocopyExpenses = PhotocopyExpense::find()
                                    ->select([
                                        'accounting_expense.branch_id',
                                        'accounting_expense.program_id',
                                        'DATE(accounting_expense.datetime) as newDate',
                                        'accounting_expense.amount_type as amountType',
                                        'sum(total_amount) as total',
                                    ])
                                    ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                                    ->where([
                                        'accounting_expense.branch_id' => $branchProgram->branch_id,
                                        'accounting_expense.program_id' => $branchProgram->program_id,
                                        'accounting_expense.season_id' => $season->id,
                                    ])
                                    ->andWhere(['between', 'accounting_expense.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
                                    ->andWhere(['charge_to' => 'Icon'])
                                    ->groupBy(['newDate', 'amountType'])
                                    ->asArray()
                                    ->all();

                if($photocopyExpenses)
                {
                    foreach($photocopyExpenses as $photocopyExpense)
                    {
                        if($photocopyExpense['amountType'] == 'Cash')
                        {
                            $data['expenses']['photocopyExpenses'][$photocopyExpense['amountType']][$photocopyExpense['newDate']] = $photocopyExpense;
                        }else{
                            $data['expenses']['photocopyExpenses']['Non-Cash'][$photocopyExpense['newDate']] = $photocopyExpense;
                        }
                    }
                }

                $otherExpenses = OtherExpense::find()
                                    ->select([
                                        'accounting_expense.branch_id',
                                        'accounting_expense.program_id',
                                        'DATE(accounting_expense.datetime) as newDate',
                                        'accounting_expense.amount_type as amountType',
                                        'sum(amount) as total',
                                    ])
                                    ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_other_expense.id and accounting_expense.expense_type_id = 3')
                                    ->where([
                                        'accounting_expense.branch_id' => $branchProgram->branch_id,
                                        'accounting_expense.program_id' => $branchProgram->program_id,
                                        'accounting_expense.season_id' => $season->id,
                                    ])
                                    ->andWhere(['between', 'accounting_expense.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
                                    ->andWhere(['charge_to' => 'Icon'])
                                    ->groupBy(['newDate', 'amountType'])
                                    ->asArray()
                                    ->all();

                if($otherExpenses)
                {
                    foreach($otherExpenses as $otherExpense)
                    {
                        if($otherExpense['amountType'] == 'Cash')
                        {
                            $data['expenses']['otherExpenses'][$otherExpense['amountType']][$otherExpense['newDate']] = $otherExpense;
                        }else{
                            $data['expenses']['otherExpenses']['Non-Cash'][$otherExpense['newDate']] = $otherExpense;
                        }
                    }
                }

                $operatingExpenses = OperatingExpense::find()
                                    ->select([
                                        'accounting_expense.branch_id',
                                        'accounting_expense.program_id',
                                        'DATE(accounting_expense.datetime) as newDate',
                                        'accounting_expense.amount_type as amountType',
                                        'sum(accounting_expense_operating_expense.staff_salary) as staffSalaryTotal',
                                        'sum(accounting_expense_operating_expense.cash_pf) as cashPfTotal',
                                        'sum(accounting_expense_operating_expense.rent) as rentTotal',
                                        'sum(accounting_expense_operating_expense.utilities) as utilitiesTotal',
                                        'sum(accounting_expense_operating_expense.equipment_and_labor) as equipmentAndLaborTotal',
                                        'sum(accounting_expense_operating_expense.bir_and_docs) as bir_and_docsTotal',
                                        'sum(accounting_expense_operating_expense.marketing) as marketingTotal',
                                        'sum(
                                            accounting_expense_operating_expense.staff_salary + 
                                            accounting_expense_operating_expense.cash_pf + 
                                            accounting_expense_operating_expense.rent + 
                                            accounting_expense_operating_expense.utilities + 
                                            accounting_expense_operating_expense.equipment_and_labor + 
                                            accounting_expense_operating_expense.bir_and_docs + 
                                            accounting_expense_operating_expense.marketing
                                        ) as total',
                                    ])
                                    ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_operating_expense.id and accounting_expense.expense_type_id = 5')
                                    ->where([
                                        'accounting_expense.branch_id' => $branchProgram->branch_id,
                                        'accounting_expense.program_id' => $branchProgram->program_id,
                                        'accounting_expense.season_id' => $season->id,
                                    ])
                                    ->andWhere(['between', 'accounting_expense.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
                                    ->andWhere(['charge_to' => 'Icon'])
                                    ->groupBy(['newDate', 'amountType'])
                                    ->asArray()
                                    ->all();

                if($operatingExpenses)
                {
                    foreach($operatingExpenses as $operatingExpense)
                    {
                        if($operatingExpense['amountType'] == 'Cash')
                        {
                            $data['expenses']['operatingExpenses'][$operatingExpense['amountType']][$operatingExpense['newDate']] = $operatingExpense;
                        }else{
                            $data['expenses']['operatingExpenses']['Non-Cash'][$operatingExpense['newDate']] = $operatingExpense;
                        }
                    }
                }
            }

        }else{

            $user_info = Yii::$app->user->identity->userinfo;
            $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
            $rolenames =  ArrayHelper::map($roles, 'name','name');

            $branchProgramIds = [];

            if(in_array('TopManagement',$rolenames)){
                $branchPrograms = BranchProgram::find()
                                    ->select(['accounting_branch_program.id as id', 'concat(accounting_branch.name," - ",accounting_program.name) as branchProgramName'])
                                    ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                                    ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                                    ->asArray()
                                    ->orderBy(['branchProgramName' => SORT_ASC])
                                    ->all();
            }else{
                $branchPrograms = BranchProgram::find()
                                    ->select(['accounting_branch_program.id as id', 'concat(accounting_branch.name," - ",accounting_program.name) as branchProgramName'])
                                    ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                                    ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                                    ->where(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                    ->asArray()
                                    ->orderBy(['branchProgramName' => SORT_ASC])
                                    ->all();
            }

            if(!empty($branchPrograms))
            {
                foreach($branchPrograms as $bp)
                {
                    $branchProgramIds[] = $bp['id'];
                }
            }

            $branchProgramName = 'All Branch Programs';
            $data = [];

            $incomeEnrolments = IncomeEnrolment::find()
                                ->select([
                                    'DATE(accounting_income.datetime) as newDate',
                                    'accounting_income.amount_type as amountType',
                                    'sum(accounting_income_enrolment.amount) as total',
                                ])
                                ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_enrolment.id and accounting_income.income_type_id = 1')
                                ->leftJoin('accounting_branch_program', 'accounting_branch_program.branch_id = accounting_income.branch_id and accounting_branch_program.program_id = accounting_income.program_id')
                                ->leftJoin('accounting_package_student', 'accounting_package_student.season_id = accounting_income_enrolment.season_id and accounting_package_student.student_id = accounting_income_enrolment.student_id')
                                ->leftJoin('accounting_package', 'accounting_package.id = accounting_package_student.package_id')
                                ->leftJoin('accounting_package_type', 'accounting_package_type.id = accounting_package.package_type_id')
                                ->andWhere(['between', 'accounting_income.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
                                ->andWhere(['in', 'accounting_package_type.id', ['5','7']])
                                ->andWhere(['in', 'accounting_branch_program.id', $branchProgramIds])
                                ->groupBy(['newDate'])
                                ->asArray()
                                ->all();

            if($incomeEnrolments)
            {
                foreach($incomeEnrolments as $enrolment)
                {
                    if($enrolment['amountType'] == 'Cash')
                    {
                        $data['incomes']['enrolments'][$enrolment['amountType']][$enrolment['newDate']] = $enrolment;
                    }else{
                        $data['incomes']['enrolments']['Non-Cash'][$enrolment['newDate']] = $enrolment;
                    }
                }
            }

            $freebiesAndIcons = FreebieAndIcon::find()
                                ->select([
                                    'DATE(accounting_income.datetime) as newDate',
                                    'accounting_income.amount_type as amountType',
                                    'sum(amount) as total',
                                ])
                                ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                                ->leftJoin('accounting_branch_program', 'accounting_branch_program.branch_id = accounting_income.branch_id and accounting_branch_program.program_id = accounting_income.program_id')
                                ->andWhere(['between', 'accounting_income.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
                                ->andWhere(['code_id' => '9'])
                                ->andWhere(['in', 'accounting_branch_program.id', $branchProgramIds])
                                ->groupBy(['newDate', 'amountType'])
                                ->asArray()
                                ->all();

            if($freebiesAndIcons)
            {
                foreach($freebiesAndIcons as $freebies)
                {
                    if($freebies['amountType'] == 'Cash')
                    {
                        $data['incomes']['freebies'][$freebies['amountType']][$freebies['newDate']] = $freebies;
                    }else{
                        $data['incomes']['freebies']['Non-Cash'][$freebies['newDate']] = $freebies;
                    }
                }
            }

            $pettyExpenses = PettyExpense::find()
                                ->select([
                                    'DATE(accounting_expense.datetime) as newDate',
                                    'accounting_expense.amount_type as amountType',
                                    'sum(accounting_expense_petty_expense.food) as foodTotal',
                                    'sum(accounting_expense_petty_expense.supplies) as supplyTotal',
                                    'sum(accounting_expense_petty_expense.load) as loadTotal',
                                    'sum(accounting_expense_petty_expense.fare) as fareTotal',
                                    'sum(accounting_expense_petty_expense.food + accounting_expense_petty_expense.supplies + accounting_expense_petty_expense.load + accounting_expense_petty_expense.fare) as total',
                                ])
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_petty_expense.id and accounting_expense.expense_type_id = 1')
                                ->leftJoin('accounting_branch_program', 'accounting_branch_program.branch_id = accounting_expense.branch_id and accounting_branch_program.program_id = accounting_expense.program_id')
                                ->andWhere(['between', 'accounting_expense.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
                                ->andWhere(['charge_to' => 'Icon'])
                                ->andWhere(['in', 'accounting_branch_program.id', $branchProgramIds])
                                ->groupBy(['newDate', 'amountType'])
                                ->asArray()
                                ->all();

            if($pettyExpenses)
            {
                foreach($pettyExpenses as $pettyExpense)
                {
                    if($pettyExpense['amountType'] == 'Cash')
                    {
                        $data['expenses']['pettyExpenses'][$pettyExpense['amountType']][$pettyExpense['newDate']] = $pettyExpense;
                    }else{
                        $data['expenses']['pettyExpenses']['Non-Cash'][$pettyExpense['newDate']] = $pettyExpense;
                    }
                }
            }

            $photocopyExpenses = PhotocopyExpense::find()
                                ->select([
                                    'DATE(accounting_expense.datetime) as newDate',
                                    'accounting_expense.amount_type as amountType',
                                    'sum(total_amount) as total',
                                ])
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
                                ->leftJoin('accounting_branch_program', 'accounting_branch_program.branch_id = accounting_expense.branch_id and accounting_branch_program.program_id = accounting_expense.program_id')
                                ->andWhere(['between', 'accounting_expense.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
                                ->andWhere(['charge_to' => 'Icon'])
                                ->andWhere(['in', 'accounting_branch_program.id', $branchProgramIds])
                                ->groupBy(['newDate', 'amountType'])
                                ->asArray()
                                ->all();

            if($photocopyExpenses)
            {
                foreach($photocopyExpenses as $photocopyExpense)
                {
                    if($photocopyExpense['amountType'] == 'Cash')
                    {
                        $data['expenses']['photocopyExpenses'][$photocopyExpense['amountType']][$photocopyExpense['newDate']] = $photocopyExpense;
                    }else{
                        $data['expenses']['photocopyExpenses']['Non-Cash'][$photocopyExpense['newDate']] = $photocopyExpense;
                    }
                }
            }

            $otherExpenses = OtherExpense::find()
                                ->select([
                                    'DATE(accounting_expense.datetime) as newDate',
                                    'accounting_expense.amount_type as amountType',
                                    'sum(amount) as total',
                                ])
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_other_expense.id and accounting_expense.expense_type_id = 3')
                                ->leftJoin('accounting_branch_program', 'accounting_branch_program.branch_id = accounting_expense.branch_id and accounting_branch_program.program_id = accounting_expense.program_id')
                                ->andWhere(['between', 'accounting_expense.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
                                ->andWhere(['charge_to' => 'Icon'])
                                ->andWhere(['in', 'accounting_branch_program.id', $branchProgramIds])
                                ->groupBy(['newDate', 'amountType'])
                                ->asArray()
                                ->all();

            if($otherExpenses)
            {
                foreach($otherExpenses as $otherExpense)
                {
                    if($otherExpense['amountType'] == 'Cash')
                    {
                        $data['expenses']['otherExpenses'][$otherExpense['amountType']][$otherExpense['newDate']] = $otherExpense;
                    }else{
                        $data['expenses']['otherExpenses']['Non-Cash'][$otherExpense['newDate']] = $otherExpense;
                    }
                }
            }

            $operatingExpenses = OperatingExpense::find()
                                ->select([
                                    'DATE(accounting_expense.datetime) as newDate',
                                    'accounting_expense.amount_type as amountType',
                                    'sum(accounting_expense_operating_expense.staff_salary) as staffSalaryTotal',
                                    'sum(accounting_expense_operating_expense.cash_pf) as cashPfTotal',
                                    'sum(accounting_expense_operating_expense.rent) as rentTotal',
                                    'sum(accounting_expense_operating_expense.utilities) as utilitiesTotal',
                                    'sum(accounting_expense_operating_expense.equipment_and_labor) as equipmentAndLaborTotal',
                                    'sum(accounting_expense_operating_expense.bir_and_docs) as bir_and_docsTotal',
                                    'sum(accounting_expense_operating_expense.marketing) as marketingTotal',
                                    'sum(
                                        accounting_expense_operating_expense.staff_salary + 
                                        accounting_expense_operating_expense.cash_pf + 
                                        accounting_expense_operating_expense.rent + 
                                        accounting_expense_operating_expense.utilities + 
                                        accounting_expense_operating_expense.equipment_and_labor + 
                                        accounting_expense_operating_expense.bir_and_docs + 
                                        accounting_expense_operating_expense.marketing
                                    ) as total',
                                ])
                                ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_operating_expense.id and accounting_expense.expense_type_id = 5')
                                ->leftJoin('accounting_branch_program', 'accounting_branch_program.branch_id = accounting_expense.branch_id and accounting_branch_program.program_id = accounting_expense.program_id')
                                ->andWhere(['between', 'accounting_expense.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
                                ->andWhere(['charge_to' => 'Icon'])
                                ->andWhere(['in', 'accounting_branch_program.id', $branchProgramIds])
                                ->groupBy(['newDate', 'amountType'])
                                ->asArray()
                                ->all();

            if($operatingExpenses)
            {
                foreach($operatingExpenses as $operatingExpense)
                {
                    if($operatingExpense['amountType'] == 'Cash')
                    {
                        $data['expenses']['operatingExpenses'][$operatingExpense['amountType']][$operatingExpense['newDate']] = $operatingExpense;
                    }else{
                        $data['expenses']['operatingExpenses']['Non-Cash'][$operatingExpense['newDate']] = $operatingExpense;
                    }
                }
            }
        }

        $totals = [];
        $totals2 = [];

        if(!empty($dates))
        {
            foreach($dates as $date)
            {
                $totals['incomes'][$date] = 0;
                $totals['expenses'][$date] = 0;
                $totals2['incomes'][$date] = 0;
                $totals2['expenses'][$date] = 0;
            }
        }

        $content = $this->renderPartial('_cutoff_summary_icon', [
            'id' => $id,
            'cutoff' => $cutoff,
            'dates' => $dates,
            'season' => $season,
            'seasonName' => $seasonName,
            'branchProgram' => $branchProgram,
            'branchProgramName' => $branchProgramName,
            'data' => $data,
            'totals' => $totals,
            'totals2' => $totals2
        ]);

        $pdf = new Pdf([
        'mode' => Pdf::MODE_CORE,
        'format' => Pdf::FORMAT_LEGAL, 
        'orientation' => Pdf::ORIENT_LANDSCAPE, 
        'destination' => Pdf::DEST_DOWNLOAD, 
        'filename' => 'Cut-Off Summary Report: Icon - '.$branchProgramName.' - ('.$cutoff['start'].' - '.$cutoff['end'].').pdf',
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
        'options' => ['title' => 'Cut-Off Summary Report: Icon'],
        'methods' => [ 
            'SetHeader'=>['Cut-Off Summary Report: Icon - '.$branchProgramName], 
            'SetFooter'=>['Page {PAGENO}'],
        ]
        ]);
        $response = Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_RAW;
        $headers = Yii::$app->response->headers;
        $headers->add('Content-Type', 'application/pdf');
        return $pdf->render();   
    }

    public function actionAudit($id, $branchProgram, $season)
    {
        $audits = [];
        $denominations = Denomination::find()->asArray()->all();
        $bp = BranchProgram::findOne($branchProgram);
        $selectedSeason = Season::findOne($season);
        $branch = Branch::findOne($bp->branch_id);
        if(!empty($denominations))
        {
            foreach($denominations as $denomination)
            {
                $audit = Audit::find()->where(['branch_program_id' => $bp->id, 'season_id' => $selectedSeason->id, 'denomination_id' => $denomination['id'] ,'date(datetime)' => $id])->one() ? Audit::find()->where(['branch_program_id' => $bp->id, 'season_id' => $selectedSeason->id, 'denomination_id' => $denomination['id'] ,'date(datetime)' => $id])->one() : new Audit();
                $audit->branch_program_id = $bp->id;
                $audit->season_id = $selectedSeason->id;
                $audit->denomination_id = $denomination['id'];
                $audits[] = $audit;
            }
        }

        $cutoff = $this->check_in_cutoff($id);

        $beginningcoh = BeginningCoh::find()->where(['branch_program_id' => $bp->id, 'season_id' => $selectedSeason->id, 'date(datetime)' => $cutoff['start']])->one() ? BeginningCoh::find()->where(['branch_program_id' => $bp->id, 'season_id' => $selectedSeason->id, 'date(datetime)' => $cutoff['start']])->one() : new BeginningCoh();

        $beginningcoh->branch_program_id = $bp->id;
        $beginningcoh->season_id = $selectedSeason->id;

        $dataOnCashCutOffToEndDay = $selectedSeason->getCashOnHandByDate($id);
        $dataOnBankCutOffToEndDay = $selectedSeason->getCashOnBankByDate($id);

        $dataOnCashCutOffToStartDay = $selectedSeason->getCashOnHandByStartOfTheDate($id);
        $dataOnBankCutOffToStartDay = $selectedSeason->getCashOnBankByStartOfTheDate($id);

        $dataOnCashStartToEndDay = $selectedSeason->getCashOnHandByWholeDay($id);
        $dataOnBankStartToEndDay = $selectedSeason->getCashOnBankByWholeDay($id);

        if($id == $cutoff['start'])
        {
            $total_income_cash = $dataOnCashCutOffToEndDay['incomeEnrolmentTotal'] + $dataOnCashCutOffToEndDay['freebiesTotal'] + $dataOnCashCutOffToEndDay['budgetProposalTotal'];
            $total_expenses_cash = $dataOnCashCutOffToEndDay['pettyExpenseTotal'] + $dataOnCashCutOffToEndDay['photocopyExpenseTotal'] + $dataOnCashCutOffToEndDay['otherExpenseTotal'] + $dataOnCashCutOffToEndDay['operatingExpenseTotal'] + $dataOnCashCutOffToEndDay['bankDepositsTotal'] + $dataOnCashCutOffToEndDay['branchTransferTotal'];

            $beginning_coh_cash = $dataOnCashCutOffToEndDay['beginningcoh'];
            $ending_coh_cash = $beginning_coh_cash + $total_income_cash - $total_expenses_cash;

            $total_income_bank = $dataOnBankCutOffToEndDay['incomeEnrolmentTotal'] + $dataOnBankCutOffToEndDay['freebiesTotal'] + $dataOnBankCutOffToEndDay['budgetProposalTotal'] + $dataOnBankCutOffToEndDay['bankDepositsTotal'];
            $total_expenses_bank = $dataOnBankCutOffToEndDay['pettyExpenseTotal'] + $dataOnBankCutOffToEndDay['photocopyExpenseTotal'] + $dataOnBankCutOffToEndDay['otherExpenseTotal'] + $dataOnBankCutOffToEndDay['operatingExpenseTotal'] + $dataOnBankCutOffToEndDay['branchTransferTotal'];

            $beginning_coh_bank = $dataOnBankCutOffToEndDay['beginningcoh'];
            $ending_coh_bank = $beginning_coh_bank + $total_income_bank - $total_expenses_bank;

        }else{
            $total_income_cash = $dataOnCashStartToEndDay['incomeEnrolmentTotal'] + $dataOnCashStartToEndDay['freebiesTotal'] + $dataOnCashStartToEndDay['budgetProposalTotal'];

            $total_expenses_cash = $dataOnCashStartToEndDay['pettyExpenseTotal'] + $dataOnCashStartToEndDay['photocopyExpenseTotal'] + $dataOnCashStartToEndDay['otherExpenseTotal'] + $dataOnCashStartToEndDay['operatingExpenseTotal'] + $dataOnCashStartToEndDay['bankDepositsTotal'] + $dataOnCashStartToEndDay['branchTransferTotal'];

            $total_income_beginning_cash = $dataOnCashCutOffToStartDay['incomeEnrolmentTotal'] + $dataOnCashCutOffToStartDay['freebiesTotal'] + $dataOnCashCutOffToStartDay['budgetProposalTotal'];
            $total_expenses_beginning_cash = $dataOnCashCutOffToStartDay['pettyExpenseTotal'] + $dataOnCashCutOffToStartDay['photocopyExpenseTotal'] + $dataOnCashCutOffToStartDay['otherExpenseTotal'] + $dataOnCashCutOffToStartDay['operatingExpenseTotal'] + $dataOnCashCutOffToStartDay['bankDepositsTotal'] + $dataOnCashCutOffToStartDay['branchTransferTotal'];

            $beginning_coh_cash = ($total_income_beginning_cash - $total_expenses_beginning_cash) == 0 ? $dataOnCashCutOffToEndDay['beginningcoh'] : ($dataOnCashCutOffToEndDay['beginningcoh'] + ($total_income_beginning_cash - $total_expenses_beginning_cash));
            $ending_coh_cash = $beginning_coh_cash + ($total_income_cash - $total_expenses_cash);

            $total_income_bank = $dataOnBankStartToEndDay['incomeEnrolmentTotal'] + $dataOnBankStartToEndDay['freebiesTotal'] + $dataOnBankStartToEndDay['budgetProposalTotal'] + $dataOnBankStartToEndDay['bankDepositsTotal'];
            $total_expenses_bank = $dataOnBankStartToEndDay['pettyExpenseTotal'] + $dataOnBankStartToEndDay['photocopyExpenseTotal'] + $dataOnBankStartToEndDay['otherExpenseTotal'] + $dataOnBankStartToEndDay['operatingExpenseTotal'] + $dataOnBankStartToEndDay['branchTransferTotal'];

            $total_income_beginning_bank = $dataOnBankCutOffToStartDay['incomeEnrolmentTotal'] + $dataOnBankCutOffToStartDay['freebiesTotal'] + $dataOnBankCutOffToStartDay['budgetProposalTotal'] + $dataOnBankCutOffToStartDay['bankDepositsTotal'];
            $total_expenses_beginning_bank = $dataOnBankCutOffToStartDay['pettyExpenseTotal'] + $dataOnBankCutOffToStartDay['photocopyExpenseTotal'] + $dataOnBankCutOffToStartDay['otherExpenseTotal'] + $dataOnBankCutOffToStartDay['operatingExpenseTotal'] + $dataOnBankCutOffToStartDay['branchTransferTotal'];

            $beginning_coh_bank = ($total_income_beginning_bank - $total_expenses_beginning_bank) == 0 ? $dataOnBankCutOffToEndDay['beginningcoh'] : ($dataOnBankCutOffToEndDay['beginningcoh'] + $total_income_beginning_bank - $total_expenses_beginning_bank);
            $ending_coh_bank = $beginning_coh_bank + ($total_income_bank - $total_expenses_bank);
        }

        $expensesWithoutBankDeposits = !empty($expenses) ? ($total_expenses_cash - $dataOnCashStartToEndDay['bankDepositsTotal']) : $total_expenses_cash;

        if(Yii::$app->request->post())
        {
            $postData = Yii::$app->request->post();
            if(isset($postData['BeginningCoh'])){ 
                $beginningcoh->branch_program_id = $bp->id;
                $beginningcoh->season_id = $selectedSeason->id;
                $beginningcoh->cash_on_hand = $postData['BeginningCoh']['cash_on_hand']; 
                $beginningcoh->cash_on_bank = $postData['BeginningCoh']['cash_on_bank']; 
                $beginningcoh->datetime = $id.' '.date("H:i:s");
                $beginningcoh->save();
            }

            if(!empty($postData['Audit']))
            {
                foreach($postData['Audit'] as $key => $auditing)
                {
                    $audit = Audit::find()->where(['branch_program_id' => $bp->id, 'season_id' => $selectedSeason->id, 'denomination_id' => $denominations[$key]['id'] ,'date(datetime)' => $id])->one() ? Audit::find()->where(['branch_program_id' =>  $bp->id, 'season_id' => $selectedSeason->id, 'denomination_id' => $denominations[$key]['id'] ,'date(datetime)' => $id])->one() : new Audit();
                    $audit->branch_program_id = $bp->id;
                    $audit->season_id = $selectedSeason->id;
                    $audit->denomination_id = $denominations[$key]['id'];
                    $audit->total = $auditing['total'];
                    $audit->datetime = $id.' '.date("H:i:s");
                    $audit->save();
                }

                Notification::deleteAll(['model' => 'Audit', 'branch_id' => $bp->branch_id]);
            }

            \Yii::$app->getSession()->setFlash('success', 'Daily audit has been saved.');
            return $this->redirect(['/accounting/audit']);
        }

        return $this->renderAjax('_form', [
            'id' => $id,
            'branchProgram' => $bp,
            'season' => $selectedSeason,
            'audits' => $audits,
            'denominations' => $denominations,
            'beginning_coh_cash' => $beginning_coh_cash,
            'ending_coh_cash' => $ending_coh_cash,
            'total_income_cash' => $total_income_cash,
            'total_expenses_cash' => $total_expenses_cash,
            'beginning_coh_bank' => $beginning_coh_bank,
            'ending_coh_bank' => $ending_coh_bank,
            'total_income_bank' => $total_income_bank,
            'total_expenses_bank' => $total_expenses_bank,
            'dataOnCashCutOffToEndDay' => $dataOnCashCutOffToEndDay,
            'dataOnBankCutOffToEndDay' => $dataOnBankCutOffToEndDay,
            'dataOnCashCutOffToStartDay' => $dataOnCashCutOffToStartDay,
            'dataOnBankCutOffToStartDay' => $dataOnCashCutOffToStartDay,
            'dataOnCashStartToEndDay' => $dataOnCashStartToEndDay,
            'dataOnBankStartToEndDay' => $dataOnBankStartToEndDay,
            'cutoff' => $cutoff,
            'beginningcoh' => $beginningcoh,
        ]);
    }

    public function actionAuditSummary($id, $branchProgram, $season)
    {
        $bp = BranchProgram::findOne($branchProgram);   
        $selectedSeason = Season::findOne($season);

        $cashOnHand = $selectedSeason->cashOnHand;
        $cashOnBank = $selectedSeason->cashOnBank;

        $totalCash = ($cashOnHand['beginningcoh'] + $cashOnHand['incomeEnrolmentTotal'] + $cashOnHand['freebiesTotal'] + $cashOnHand['budgetProposalTotal']) - ($cashOnHand['pettyExpenseTotal'] + $cashOnHand['photocopyExpenseTotal'] + $cashOnHand['otherExpenseTotal'] + $cashOnHand['bankDepositsTotal'] + $cashOnHand['operatingExpenseTotal'] + $cashOnHand['branchTransferTotal']);

        $totalBank = ($cashOnBank['beginningcoh'] + $cashOnBank['incomeEnrolmentTotal'] + $cashOnBank['freebiesTotal'] + $cashOnBank['budgetProposalTotal']) - ($cashOnBank['pettyExpenseTotal'] + $cashOnBank['photocopyExpenseTotal'] + $cashOnBank['otherExpenseTotal'] + $cashOnBank['bankDepositsTotal'] + $cashOnBank['operatingExpenseTotal'] + $cashOnBank['branchTransferTotal']);

        $branch = Branch::findOne($bp->branch_id);
        if(!empty($denominations))
        {
            foreach($denominations as $denomination)
            {
                $audit = Audit::find()->where(['branch_program_id' => $bp->id, 'season_id' => $selectedSeason->id, 'denomination_id' => $denomination['id'] ,'date(datetime)' => $id])->one() ? Audit::find()->where(['branch_program_id' => $bp->id, 'season_id' => $selectedSeason->id, 'denomination_id' => $denomination['id'] ,'date(datetime)' => $id])->one() : new Audit();
                $audit->branch_program_id = $bp->id;
                $audit->denomination_id = $denomination['id'];
                $audits[] = $audit;
            }
        }

        $cutoff = $this->check_in_cutoff($id);

        $beginningcoh = BeginningCoh::find()->where(['branch_program_id' => $bp->id, 'season_id' => $selectedSeason->id, 'date(datetime)' => $cutoff['start']])->one() ? BeginningCoh::find()->where(['branch_program_id' => $bp->id, 'season_id' => $selectedSeason->id, 'date(datetime)' => $cutoff['start']])->one() : new BeginningCoh();
        $beginningcoh->branch_program_id = $bp->id;
        $beginningcoh->season_id = $selectedSeason->id;

        $dataOnCashCutOffToEndDay = $selectedSeason->getCashOnHandByDate($id);
        $dataOnBankCutOffToEndDay = $selectedSeason->getCashOnBankByDate($id);

        $dataOnCashCutOffToStartDay = $selectedSeason->getCashOnHandByStartOfTheDate($id);
        $dataOnBankCutOffToStartDay = $selectedSeason->getCashOnBankByStartOfTheDate($id);

        $dataOnCashStartToEndDay = $selectedSeason->getCashOnHandByWholeDay($id);
        $dataOnBankStartToEndDay = $selectedSeason->getCashOnBankByWholeDay($id);

        if($id == $cutoff['start'])
        {
            $total_income_cash = $dataOnCashCutOffToEndDay['incomeEnrolmentTotal'] + $dataOnCashCutOffToEndDay['freebiesTotal'] + $dataOnCashCutOffToEndDay['budgetProposalTotal'];
            $total_expenses_cash = $dataOnCashCutOffToEndDay['pettyExpenseTotal'] + $dataOnCashCutOffToEndDay['photocopyExpenseTotal'] + $dataOnCashCutOffToEndDay['otherExpenseTotal'] + $dataOnCashCutOffToEndDay['operatingExpenseTotal'] + $dataOnCashCutOffToEndDay['bankDepositsTotal'] + $dataOnCashCutOffToEndDay['branchTransferTotal'];

            $beginning_coh_cash = $dataOnCashCutOffToEndDay['beginningcoh'];
            $ending_coh_cash = $beginning_coh_cash + $total_income_cash - $total_expenses_cash;

            $total_income_bank = $dataOnBankCutOffToEndDay['incomeEnrolmentTotal'] + $dataOnBankCutOffToEndDay['freebiesTotal'] + $dataOnBankCutOffToEndDay['budgetProposalTotal'] + $dataOnBankCutOffToEndDay['bankDepositsTotal'];
            $total_expenses_bank = $dataOnBankCutOffToEndDay['pettyExpenseTotal'] + $dataOnBankCutOffToEndDay['photocopyExpenseTotal'] + $dataOnBankCutOffToEndDay['otherExpenseTotal'] + $dataOnBankCutOffToEndDay['operatingExpenseTotal'] + $dataOnBankCutOffToEndDay['branchTransferTotal'];

            $beginning_coh_bank = $dataOnBankCutOffToEndDay['beginningcoh'];
            $ending_coh_bank = $beginning_coh_bank + $total_income_bank - $total_expenses_bank;

        }else{
            $total_income_cash = $dataOnCashStartToEndDay['incomeEnrolmentTotal'] + $dataOnCashStartToEndDay['freebiesTotal'] + $dataOnCashStartToEndDay['budgetProposalTotal'];

            $total_expenses_cash = $dataOnCashStartToEndDay['pettyExpenseTotal'] + $dataOnCashStartToEndDay['photocopyExpenseTotal'] + $dataOnCashStartToEndDay['otherExpenseTotal'] + $dataOnCashStartToEndDay['operatingExpenseTotal'] + $dataOnCashStartToEndDay['bankDepositsTotal'] + $dataOnCashStartToEndDay['branchTransferTotal'];

            $total_income_beginning_cash = $dataOnCashCutOffToStartDay['incomeEnrolmentTotal'] + $dataOnCashCutOffToStartDay['freebiesTotal'] + $dataOnCashCutOffToStartDay['budgetProposalTotal'];
            $total_expenses_beginning_cash = $dataOnCashCutOffToStartDay['pettyExpenseTotal'] + $dataOnCashCutOffToStartDay['photocopyExpenseTotal'] + $dataOnCashCutOffToStartDay['otherExpenseTotal'] + $dataOnCashCutOffToStartDay['operatingExpenseTotal'] + $dataOnCashCutOffToStartDay['bankDepositsTotal'] + $dataOnCashCutOffToStartDay['branchTransferTotal'];

            $beginning_coh_cash = ($total_income_beginning_cash - $total_expenses_beginning_cash) == 0 ? $dataOnCashCutOffToEndDay['beginningcoh'] : ($dataOnCashCutOffToEndDay['beginningcoh'] + ($total_income_beginning_cash - $total_expenses_beginning_cash));
            $ending_coh_cash = $beginning_coh_cash + ($total_income_cash - $total_expenses_cash);

            $total_income_bank = $dataOnBankStartToEndDay['incomeEnrolmentTotal'] + $dataOnBankStartToEndDay['freebiesTotal'] + $dataOnBankStartToEndDay['budgetProposalTotal'] + $dataOnBankStartToEndDay['bankDepositsTotal'];
            $total_expenses_bank = $dataOnBankStartToEndDay['pettyExpenseTotal'] + $dataOnBankStartToEndDay['photocopyExpenseTotal'] + $dataOnBankStartToEndDay['otherExpenseTotal'] + $dataOnBankStartToEndDay['operatingExpenseTotal'] + $dataOnBankStartToEndDay['branchTransferTotal'];

            $total_income_beginning_bank = $dataOnBankCutOffToStartDay['incomeEnrolmentTotal'] + $dataOnBankCutOffToStartDay['freebiesTotal'] + $dataOnBankCutOffToStartDay['budgetProposalTotal'] + $dataOnBankCutOffToStartDay['bankDepositsTotal'];
            $total_expenses_beginning_bank = $dataOnBankCutOffToStartDay['pettyExpenseTotal'] + $dataOnBankCutOffToStartDay['photocopyExpenseTotal'] + $dataOnBankCutOffToStartDay['otherExpenseTotal'] + $dataOnBankCutOffToStartDay['operatingExpenseTotal'] + $dataOnBankCutOffToStartDay['branchTransferTotal'];

            $beginning_coh_bank = ($total_income_beginning_bank - $total_expenses_beginning_bank) == 0 ? $dataOnBankCutOffToEndDay['beginningcoh'] : ($dataOnBankCutOffToEndDay['beginningcoh'] + $total_income_beginning_bank - $total_expenses_beginning_bank);
            $ending_coh_bank = $beginning_coh_bank + ($total_income_bank - $total_expenses_bank);
        }

        $expensesWithoutBankDeposits = !empty($expenses) ? ($total_expenses_cash - $dataOnCashStartToEndDay['bankDepositsTotal']) : $total_expenses_cash;

        return $this->renderAjax('_audit-summary',[
            'branchProgram' => $bp,
            'season' => $selectedSeason,
            'totalCash' => $totalCash,
            'totalBank' => $totalBank,
            'beginning_coh_cash' => $beginning_coh_cash,
            'ending_coh_cash' => $ending_coh_cash,
            'total_income_cash' => $total_income_cash,
            'total_expenses_cash' => $total_expenses_cash,
            'beginning_coh_bank' => $beginning_coh_bank,
            'ending_coh_bank' => $ending_coh_bank,
            'total_income_bank' => $total_income_bank,
            'total_expenses_bank' => $total_expenses_bank,
            'dataOnCashCutOffToEndDay' => $dataOnCashCutOffToEndDay,
            'dataOnBankCutOffToEndDay' => $dataOnBankCutOffToEndDay,
            'dataOnCashCutOffToStartDay' => $dataOnCashCutOffToStartDay,
            'dataOnBankCutOffToStartDay' => $dataOnCashCutOffToStartDay,
            'dataOnCashStartToEndDay' => $dataOnCashStartToEndDay,
            'dataOnBankStartToEndDay' => $dataOnBankStartToEndDay,
            'cutoff' => $cutoff,
            'beginningcoh' => $beginningcoh,
        ]);
    }

    public function actionCutOffSummary()
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

        return $this->render('summary',[
            'model' => $model,
            'branchPrograms' => $branchPrograms,
            'seasons' => $seasons,
        ]);
    }

    public function actionCutOffSummaryIcon()
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

        return $this->render('summary-icon',[
            'model' => $model,
            'branchPrograms' => $branchPrograms,
            'seasons' => $seasons,
        ]);
    }

    public function actionGenerateSummary($id, $branchProgram = '', $season = '')
    {
        $cutoff = $this->check_in_cutoff($id);
        $dates = $this->dateRange($cutoff['start'], $cutoff['end']);
        $data = [];
        $branchProgramName = '';
        $seasonName = '';
        $beginningcohAmount = 0;
        $beginningcobAmount = 0;

        $incomeEnrolments = IncomeEnrolment::find()
            ->select([
                'accounting_income.branch_id',
                'accounting_income.program_id',
                'DATE(accounting_income.datetime) as newDate',
                'accounting_income.amount_type as amountType',
                'sum(accounting_income_enrolment.amount) as total',
            ])
            ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_enrolment.id and accounting_income.income_type_id = 1')
            ->leftJoin('accounting_package_student', 'accounting_package_student.season_id = accounting_income_enrolment.season_id and accounting_package_student.student_id = accounting_income_enrolment.student_id')
            ->leftJoin('accounting_package', 'accounting_package.id = accounting_package_student.package_id')
            ->leftJoin('accounting_package_type', 'accounting_package_type.id = accounting_package.package_type_id')
            ->leftJoin('accounting_branch_program', 'accounting_branch_program.branch_id = accounting_income.branch_id and accounting_branch_program.program_id = accounting_income.program_id');

        $freebiesAndIcons = FreebieAndIcon::find()
            ->select([
                'accounting_income.branch_id',
                'accounting_income.program_id',
                'DATE(accounting_income.datetime) as newDate',
                'accounting_income.amount_type as amountType',
                'sum(amount) as total',
            ])
            ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
            ->leftJoin('accounting_branch_program', 'accounting_branch_program.branch_id = accounting_income.branch_id and accounting_branch_program.program_id = accounting_income.program_id');

        $budgetProposals = BudgetProposal::find()
            ->select([
                'accounting_income.branch_id',
                'accounting_income.program_id',
                'DATE(accounting_income.datetime) as newDate',
                'accounting_income.amount_type as amountType',
                'sum(COALESCE(particulars.total, 0)) as total',
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
            ->leftJoin('accounting_branch_program', 'accounting_branch_program.branch_id = accounting_income.branch_id and accounting_branch_program.program_id = accounting_income.program_id');

        $pettyExpenses = PettyExpense::find()
            ->select([
                'accounting_expense.branch_id',
                'accounting_expense.program_id',
                'DATE(accounting_expense.datetime) as newDate',
                'accounting_expense.amount_type as amountType',
                'sum(accounting_expense_petty_expense.food) as foodTotal',
                'sum(accounting_expense_petty_expense.supplies) as supplyTotal',
                'sum(accounting_expense_petty_expense.load) as loadTotal',
                'sum(accounting_expense_petty_expense.fare) as fareTotal',
                'sum(accounting_expense_petty_expense.food + accounting_expense_petty_expense.supplies + accounting_expense_petty_expense.load + accounting_expense_petty_expense.fare) as total',
            ])
            ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_petty_expense.id and accounting_expense.expense_type_id = 1')
            ->leftJoin('accounting_branch_program', 'accounting_branch_program.branch_id = accounting_expense.branch_id and accounting_branch_program.program_id = accounting_expense.program_id');

        $photocopyExpenses = PhotocopyExpense::find()
            ->select([
                'accounting_expense.branch_id',
                'accounting_expense.program_id',
                'DATE(accounting_expense.datetime) as newDate',
                'accounting_expense.amount_type as amountType',
                'sum(total_amount) as total',
            ])
            ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
            ->leftJoin('accounting_branch_program', 'accounting_branch_program.branch_id = accounting_expense.branch_id and accounting_branch_program.program_id = accounting_expense.program_id');

        $otherExpenses = OtherExpense::find()
            ->select([
                'accounting_expense.branch_id',
                'accounting_expense.program_id',
                'DATE(accounting_expense.datetime) as newDate',
                'accounting_expense.amount_type as amountType',
                'sum(amount) as total',
            ])
            ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_other_expense.id and accounting_expense.expense_type_id = 3')
            ->leftJoin('accounting_branch_program', 'accounting_branch_program.branch_id = accounting_expense.branch_id and accounting_branch_program.program_id = accounting_expense.program_id');

        $bankDeposits = BankDeposit::find()
            ->select([
                'accounting_expense.branch_id',
                'accounting_expense.program_id',
                'DATE(accounting_expense.datetime) as newDate',
                'accounting_expense.amount_type as amountType',
                'sum(amount) as total',
            ])
            ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_bank_deposit.id and accounting_expense.expense_type_id = 4')
            ->leftJoin('accounting_branch_program', 'accounting_branch_program.branch_id = accounting_expense.branch_id and accounting_branch_program.program_id = accounting_expense.program_id');

        $operatingExpenses = OperatingExpense::find()
            ->select([
                'accounting_expense.branch_id',
                'accounting_expense.program_id',
                'DATE(accounting_expense.datetime) as newDate',
                'accounting_expense.amount_type as amountType',
                'sum(accounting_expense_operating_expense.staff_salary) as staffSalaryTotal',
                'sum(accounting_expense_operating_expense.cash_pf) as cashPfTotal',
                'sum(accounting_expense_operating_expense.rent) as rentTotal',
                'sum(accounting_expense_operating_expense.utilities) as utilitiesTotal',
                'sum(accounting_expense_operating_expense.equipment_and_labor) as equipmentAndLaborTotal',
                'sum(accounting_expense_operating_expense.bir_and_docs) as bir_and_docsTotal',
                'sum(accounting_expense_operating_expense.marketing) as marketingTotal',
                'sum(
                    accounting_expense_operating_expense.staff_salary + 
                    accounting_expense_operating_expense.cash_pf + 
                    accounting_expense_operating_expense.rent + 
                    accounting_expense_operating_expense.utilities + 
                    accounting_expense_operating_expense.equipment_and_labor + 
                    accounting_expense_operating_expense.bir_and_docs + 
                    accounting_expense_operating_expense.marketing
                ) as total',
            ])
            ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_operating_expense.id and accounting_expense.expense_type_id = 5')
            ->leftJoin('accounting_branch_program', 'accounting_branch_program.branch_id = accounting_expense.branch_id and accounting_branch_program.program_id = accounting_expense.program_id');

        $branchTransfers = BranchTransfer::find()
            ->select([
                'accounting_expense.branch_id',
                'accounting_expense.program_id',
                'DATE(accounting_expense.datetime) as newDate',
                'accounting_expense.amount_type as amountType',
                'sum(COALESCE(particulars.total, 0)) as total',
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
            ->leftJoin('accounting_branch_program', 'accounting_branch_program.branch_id = accounting_expense.branch_id and accounting_branch_program.program_id = accounting_expense.program_id');

        if($branchProgram != '')
        {
            $branchProgram = BranchProgram::findOne($branchProgram);
            $branchProgramName = $branchProgram->branchProgramName;

            if($season != '')
            {
                $season = Season::findOne($season);
                $seasonName = $season->seasonName;

                $beginningcoh = BeginningCoh::find()->where(['date(datetime)' => $cutoff['start'], 'season_id' => $season->id])->one();
                $beginningcohAmount = $beginningcoh ? $beginningcoh->cash_on_hand : 0;
                $beginningcobAmount = $beginningcoh ? $beginningcoh->cash_on_bank : 0;

                $incomeEnrolments = $incomeEnrolments->andWhere([
                    'accounting_income_enrolment.season_id' => $season->id,
                ]);

                $freebiesAndIcons = $freebiesAndIcons->andWhere([
                    'accounting_income_freebies_and_icons.season_id' => $season->id,
                ]);

                $budgetProposals = $budgetProposals->andWhere([
                    'accounting_income_budget_proposal.season_id' => $season->id,
                ]);

                $pettyExpenses = $pettyExpenses->andWhere([
                    'accounting_expense.season_id' => $season->id,
                ]);

                $photocopyExpenses = $photocopyExpenses->andWhere([
                    'accounting_expense.season_id' => $season->id,
                ]);

                $otherExpenses = $otherExpenses->andWhere([
                    'accounting_expense.season_id' => $season->id,
                ]);

                $bankDeposits = $bankDeposits->andWhere([
                    'accounting_expense.season_id' => $season->id,
                ]);

                $operatingExpenses = $operatingExpenses->andWhere([
                    'accounting_expense.season_id' => $season->id,
                ]);

                $branchTransfers = $branchTransfers->andWhere([
                    'accounting_expense.season_id' => $season->id,
                ]);

            }else{
                $beginningcoh = BeginningCoh::find()->where(['date(datetime)' => $cutoff['start'], 'branch_program_id' => $branchProgram->id])->one();
                $beginningcohAmount = $beginningcoh ? $beginningcoh->cash_on_hand : 0;
                $beginningcobAmount = $beginningcoh ? $beginningcoh->cash_on_bank : 0;

                $incomeEnrolments = $incomeEnrolments->andWhere([
                    'accounting_income.branch_id' => $branchProgram->branch_id,
                    'accounting_income.program_id' => $branchProgram->program_id
                ]);

                $freebiesAndIcons = $freebiesAndIcons->andWhere([
                    'accounting_income.branch_id' => $branchProgram->branch_id,
                    'accounting_income.program_id' => $branchProgram->program_id
                ]);

                $budgetProposals = $budgetProposals->andWhere([
                    'accounting_income.branch_id' => $branchProgram->branch_id,
                    'accounting_income.program_id' => $branchProgram->program_id
                ]);

                $pettyExpenses = $pettyExpenses->andWhere([
                    'accounting_expense.branch_id' => $branchProgram->branch_id,
                    'accounting_expense.program_id' => $branchProgram->program_id
                ]);

                $photocopyExpenses = $photocopyExpenses->andWhere([
                    'accounting_expense.branch_id' => $branchProgram->branch_id,
                    'accounting_expense.program_id' => $branchProgram->program_id
                ]);

                $otherExpenses = $otherExpenses->andWhere([
                    'accounting_expense.branch_id' => $branchProgram->branch_id,
                    'accounting_expense.program_id' => $branchProgram->program_id
                ]);

                $bankDeposits = $bankDeposits->andWhere([
                    'accounting_expense.branch_id' => $branchProgram->branch_id,
                    'accounting_expense.program_id' => $branchProgram->program_id
                ]);

                $operatingExpenses = $operatingExpenses->andWhere([
                    'accounting_expense.branch_id' => $branchProgram->branch_id,
                    'accounting_expense.program_id' => $branchProgram->program_id
                ]);

                $branchTransfers = $branchTransfers->andWhere([
                    'accounting_expense.branch_id' => $branchProgram->branch_id,
                    'accounting_expense.program_id' => $branchProgram->program_id
                ]);

            }
        }else{
            $user_info = Yii::$app->user->identity->userinfo;
            $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
            $rolenames =  ArrayHelper::map($roles, 'name','name');

            $branchProgramIds = [];

            if(in_array('TopManagement',$rolenames)){
                $branchPrograms = BranchProgram::find()
                                    ->select(['accounting_branch_program.id as id', 'concat(accounting_branch.name," - ",accounting_program.name) as branchProgramName'])
                                    ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                                    ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                                    ->asArray()
                                    ->orderBy(['branchProgramName' => SORT_ASC])
                                    ->all();
            }else{
                $branchPrograms = BranchProgram::find()
                                    ->select(['accounting_branch_program.id as id', 'concat(accounting_branch.name," - ",accounting_program.name) as branchProgramName'])
                                    ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                                    ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                                    ->where(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                    ->asArray()
                                    ->orderBy(['branchProgramName' => SORT_ASC])
                                    ->all();
            }

            if(!empty($branchPrograms))
            {
                foreach($branchPrograms as $bp)
                {
                    $branchProgramIds[] = $bp['id'];
                }
            }

            $beginningcoh = BeginningCoh::find()->where(['date(datetime)' => $cutoff['start']])->all();
            if($beginningcoh)
            {
                foreach($beginningcoh as $coh)
                {
                    $beginningcohAmount+=$coh->cash_on_hand;
                    $beginningcobAmount+=$coh->cash_on_bank;
                }
            }

            $branchProgramName = 'All Branch Programs';
            $data = [];

            $incomeEnrolments = $incomeEnrolments->andWhere([
                'in', 'accounting_branch_program.id', $branchProgramIds
            ]);

            $freebiesAndIcons = $freebiesAndIcons->andWhere([
                'in', 'accounting_branch_program.id', $branchProgramIds
            ]);

            $budgetProposals = $budgetProposals->andWhere([
                'in', 'accounting_branch_program.id', $branchProgramIds
            ]);

            $pettyExpenses = $pettyExpenses->andWhere([
                'in', 'accounting_branch_program.id', $branchProgramIds
            ]);

            $photocopyExpenses = $photocopyExpenses->andWhere([
                'in', 'accounting_branch_program.id', $branchProgramIds
            ]);

            $otherExpenses = $otherExpenses->andWhere([
                'in', 'accounting_branch_program.id', $branchProgramIds
            ]);

            $bankDeposits = $bankDeposits->andWhere([
                'in', 'accounting_branch_program.id', $branchProgramIds
            ]);

            $operatingExpenses = $operatingExpenses->andWhere([
                'in', 'accounting_branch_program.id', $branchProgramIds
            ]);

            $branchTransfers = $branchTransfers->andWhere([
                'in', 'accounting_branch_program.id', $branchProgramIds
            ]);
        }

        $totals = [];
        $totals2 = [];

        if(!empty($dates))
        {
            foreach($dates as $date)
            {
                $totals['incomes'][$date] = 0;
                $totals['expenses'][$date] = 0;
                $totals2['incomes'][$date] = 0;
                $totals2['expenses'][$date] = 0;
            }
        }
        
        $incomeEnrolments = $incomeEnrolments
            ->andWhere(['between', 'accounting_income.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
            ->groupBy(['newDate', 'amountType'])
            ->asArray()
            ->all();

        $freebiesAndIcons = $freebiesAndIcons
            ->andWhere(['between', 'accounting_income.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
            ->groupBy(['newDate', 'amountType'])
            ->asArray()
            ->all();

        $budgetProposals = $budgetProposals
            ->andWhere([
                'accounting_income_budget_proposal.approval_status' => 'Approved'
            ])
            ->andWhere(['between', 'accounting_income.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
            ->groupBy(['newDate', 'amountType'])
            ->asArray()
            ->all();

        $pettyExpenses = $pettyExpenses
            ->andWhere(['between', 'accounting_expense.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
            ->andWhere(['<>', 'charge_to', 'Icon'])
            ->groupBy(['newDate', 'amountType'])
            ->asArray()
            ->all();

        $photocopyExpenses = $photocopyExpenses
            ->andWhere(['between', 'accounting_expense.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
            ->andWhere(['<>', 'charge_to', 'Icon'])
            ->groupBy(['newDate', 'amountType'])
            ->asArray()
            ->all();

        $otherExpenses = $otherExpenses
            ->andWhere(['between', 'accounting_expense.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
            ->andWhere(['<>', 'charge_to', 'Icon'])
            ->groupBy(['newDate', 'amountType'])
            ->asArray()
            ->all();

        $bankDeposits = $bankDeposits
            ->andWhere(['between', 'accounting_expense.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
            ->groupBy(['newDate', 'amountType'])
            ->asArray()
            ->all();

        $operatingExpenses = $operatingExpenses
            ->andWhere(['between', 'accounting_expense.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
            ->andWhere(['<>', 'charge_to', 'Icon'])
            ->groupBy(['newDate', 'amountType'])
            ->asArray()
            ->all();

        $branchTransfers = $branchTransfers
            ->andWhere(['between', 'accounting_expense.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
            ->groupBy(['newDate', 'amountType'])
            ->asArray()
            ->all();

        if($incomeEnrolments)
        {
            foreach($incomeEnrolments as $enrolment)
            {
                if($enrolment['amountType'] == 'Cash')
                {
                    $data['incomes']['enrolments'][$enrolment['amountType']][$enrolment['newDate']] = $enrolment;
                }else{
                    $data['incomes']['enrolments']['Non-Cash'][$enrolment['newDate']] = $enrolment;
                }
            }
        }

        if($freebiesAndIcons)
        {
            foreach($freebiesAndIcons as $freebies)
            {
                if($freebies['amountType'] == 'Cash')
                {
                    $data['incomes']['freebies'][$freebies['amountType']][$freebies['newDate']] = $freebies;
                }else{
                    $data['incomes']['freebies']['Non-Cash'][$freebies['newDate']] = $freebies;
                }
            }
        }

        if($budgetProposals)
        {
            foreach($budgetProposals as $budgetProposal)
            {
                if($budgetProposal['amountType'] == 'Cash')
                {
                    $data['incomes']['budgetProposals'][$budgetProposal['amountType']][$budgetProposal['newDate']] = $budgetProposal;
                }else{
                    $data['incomes']['budgetProposals']['Non-Cash'][$budgetProposal['newDate']] = $budgetProposal;
                }
            }
        }

        if($pettyExpenses)
        {
            foreach($pettyExpenses as $pettyExpense)
            {
                if($pettyExpense['amountType'] == 'Cash')
                {
                    $data['expenses']['pettyExpenses'][$pettyExpense['amountType']][$pettyExpense['newDate']] = $pettyExpense;
                }else{
                    $data['expenses']['pettyExpenses']['Non-Cash'][$pettyExpense['newDate']] = $pettyExpense;
                }
            }
        }

        if($photocopyExpenses)
        {
            foreach($photocopyExpenses as $photocopyExpense)
            {
                if($photocopyExpense['amountType'] == 'Cash')
                {
                    $data['expenses']['photocopyExpenses'][$photocopyExpense['amountType']][$photocopyExpense['newDate']] = $photocopyExpense;
                }else{
                    $data['expenses']['photocopyExpenses']['Non-Cash'][$photocopyExpense['newDate']] = $photocopyExpense;
                }
            }
        }

        if($otherExpenses)
        {
            foreach($otherExpenses as $otherExpense)
            {
                if($otherExpense['amountType'] == 'Cash')
                {
                    $data['expenses']['otherExpenses'][$otherExpense['amountType']][$otherExpense['newDate']] = $otherExpense;
                }else{
                    $data['expenses']['otherExpenses']['Non-Cash'][$otherExpense['newDate']] = $otherExpense;
                }
            }
        }

        if($bankDeposits)
        {
            foreach($bankDeposits as $bankDeposit)
            {
                if($bankDeposit['amountType'] == 'Cash')
                {
                    $data['expenses']['bankDeposits'][$bankDeposit['amountType']][$bankDeposit['newDate']] = $bankDeposit;
                }else{
                    $data['expenses']['bankDeposits']['Non-Cash'][$bankDeposit['newDate']] = $bankDeposit;
                }
            }
        }

        if($operatingExpenses)
        {
            foreach($operatingExpenses as $operatingExpense)
            {
                if($operatingExpense['amountType'] == 'Cash')
                {
                    $data['expenses']['operatingExpenses'][$operatingExpense['amountType']][$operatingExpense['newDate']] = $operatingExpense;
                }else{
                    $data['expenses']['operatingExpenses']['Non-Cash'][$operatingExpense['newDate']] = $operatingExpense;
                }
            }
        }

        if($branchTransfers)
        {
            foreach($branchTransfers as $branchTransfer)
            {
                if($branchTransfer['amountType'] == 'Cash')
                {
                    $data['expenses']['branchTransfers'][$branchTransfer['amountType']][$branchTransfer['newDate']] = $branchTransfer;
                }else{
                    $data['expenses']['branchTransfers']['Non-Cash'][$branchTransfer['newDate']] = $branchTransfer;
                }
            }
        }

        return $this->renderAjax('_generate_summary', [
            'id' => $id,
            'cutoff' => $cutoff,
            'dates' => $dates,
            'branchProgram' => $branchProgram,
            'branchProgramName' => $branchProgramName,
            'season' => $season,
            'seasonName' => $seasonName,
            'data' => $data,
            'totals' => $totals,
            'totals2' => $totals2,
            'beginningcohAmount' => $beginningcohAmount,
            'beginningcobAmount' => $beginningcobAmount,
        ]);
    }

    public function actionGenerateSummaryIcon($id, $branchProgram = '', $season = '')
    {
        $cutoff = $this->check_in_cutoff($id);
        $dates = $this->dateRange($cutoff['start'], $cutoff['end']);
        $data = [];
        $branchProgramName = '';
        $seasonName = '';
        $beginningcohAmount = 0;
        $beginningcobAmount = 0;

        $incomeEnrolments = IncomeEnrolment::find()
            ->select([
                'accounting_income.branch_id',
                'accounting_income.program_id',
                'DATE(accounting_income.datetime) as newDate',
                'accounting_income.amount_type as amountType',
                'sum(accounting_income_enrolment.amount) as total',
            ])
            ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_enrolment.id and accounting_income.income_type_id = 1')
            ->leftJoin('accounting_package_student', 'accounting_package_student.season_id = accounting_income_enrolment.season_id and accounting_package_student.student_id = accounting_income_enrolment.student_id')
            ->leftJoin('accounting_package', 'accounting_package.id = accounting_package_student.package_id')
            ->leftJoin('accounting_package_type', 'accounting_package_type.id = accounting_package.package_type_id')
            ->leftJoin('accounting_branch_program', 'accounting_branch_program.branch_id = accounting_income.branch_id and accounting_branch_program.program_id = accounting_income.program_id');

        $freebiesAndIcons = FreebieAndIcon::find()
            ->select([
                'accounting_income.branch_id',
                'accounting_income.program_id',
                'DATE(accounting_income.datetime) as newDate',
                'accounting_income.amount_type as amountType',
                'sum(amount) as total',
            ])
            ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
            ->leftJoin('accounting_branch_program', 'accounting_branch_program.branch_id = accounting_income.branch_id and accounting_branch_program.program_id = accounting_income.program_id');

        $budgetProposals = BudgetProposal::find()
            ->select([
                'accounting_income.branch_id',
                'accounting_income.program_id',
                'DATE(accounting_income.datetime) as newDate',
                'accounting_income.amount_type as amountType',
                'sum(COALESCE(particulars.total, 0)) as total',
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
            ->leftJoin('accounting_branch_program', 'accounting_branch_program.branch_id = accounting_income.branch_id and accounting_branch_program.program_id = accounting_income.program_id');

        $pettyExpenses = PettyExpense::find()
            ->select([
                'accounting_expense.branch_id',
                'accounting_expense.program_id',
                'DATE(accounting_expense.datetime) as newDate',
                'accounting_expense.amount_type as amountType',
                'sum(accounting_expense_petty_expense.food) as foodTotal',
                'sum(accounting_expense_petty_expense.supplies) as supplyTotal',
                'sum(accounting_expense_petty_expense.load) as loadTotal',
                'sum(accounting_expense_petty_expense.fare) as fareTotal',
                'sum(accounting_expense_petty_expense.food + accounting_expense_petty_expense.supplies + accounting_expense_petty_expense.load + accounting_expense_petty_expense.fare) as total',
            ])
            ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_petty_expense.id and accounting_expense.expense_type_id = 1')
            ->leftJoin('accounting_branch_program', 'accounting_branch_program.branch_id = accounting_expense.branch_id and accounting_branch_program.program_id = accounting_expense.program_id');

        $photocopyExpenses = PhotocopyExpense::find()
            ->select([
                'accounting_expense.branch_id',
                'accounting_expense.program_id',
                'DATE(accounting_expense.datetime) as newDate',
                'accounting_expense.amount_type as amountType',
                'sum(total_amount) as total',
            ])
            ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_photocopy_expense.id and accounting_expense.expense_type_id = 2')
            ->leftJoin('accounting_branch_program', 'accounting_branch_program.branch_id = accounting_expense.branch_id and accounting_branch_program.program_id = accounting_expense.program_id');

        $otherExpenses = OtherExpense::find()
            ->select([
                'accounting_expense.branch_id',
                'accounting_expense.program_id',
                'DATE(accounting_expense.datetime) as newDate',
                'accounting_expense.amount_type as amountType',
                'sum(amount) as total',
            ])
            ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_other_expense.id and accounting_expense.expense_type_id = 3')
            ->leftJoin('accounting_branch_program', 'accounting_branch_program.branch_id = accounting_expense.branch_id and accounting_branch_program.program_id = accounting_expense.program_id');

        $bankDeposits = BankDeposit::find()
            ->select([
                'accounting_expense.branch_id',
                'accounting_expense.program_id',
                'DATE(accounting_expense.datetime) as newDate',
                'accounting_expense.amount_type as amountType',
                'sum(amount) as total',
            ])
            ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_bank_deposit.id and accounting_expense.expense_type_id = 4')
            ->leftJoin('accounting_branch_program', 'accounting_branch_program.branch_id = accounting_expense.branch_id and accounting_branch_program.program_id = accounting_expense.program_id');

        $operatingExpenses = OperatingExpense::find()
            ->select([
                'accounting_expense.branch_id',
                'accounting_expense.program_id',
                'DATE(accounting_expense.datetime) as newDate',
                'accounting_expense.amount_type as amountType',
                'sum(accounting_expense_operating_expense.staff_salary) as staffSalaryTotal',
                'sum(accounting_expense_operating_expense.cash_pf) as cashPfTotal',
                'sum(accounting_expense_operating_expense.rent) as rentTotal',
                'sum(accounting_expense_operating_expense.utilities) as utilitiesTotal',
                'sum(accounting_expense_operating_expense.equipment_and_labor) as equipmentAndLaborTotal',
                'sum(accounting_expense_operating_expense.bir_and_docs) as bir_and_docsTotal',
                'sum(accounting_expense_operating_expense.marketing) as marketingTotal',
                'sum(
                    accounting_expense_operating_expense.staff_salary + 
                    accounting_expense_operating_expense.cash_pf + 
                    accounting_expense_operating_expense.rent + 
                    accounting_expense_operating_expense.utilities + 
                    accounting_expense_operating_expense.equipment_and_labor + 
                    accounting_expense_operating_expense.bir_and_docs + 
                    accounting_expense_operating_expense.marketing
                ) as total',
            ])
            ->leftJoin('accounting_expense','accounting_expense.expense_id = accounting_expense_operating_expense.id and accounting_expense.expense_type_id = 5')
            ->leftJoin('accounting_branch_program', 'accounting_branch_program.branch_id = accounting_expense.branch_id and accounting_branch_program.program_id = accounting_expense.program_id');

        $branchTransfers = BranchTransfer::find()
            ->select([
                'accounting_expense.branch_id',
                'accounting_expense.program_id',
                'DATE(accounting_expense.datetime) as newDate',
                'accounting_expense.amount_type as amountType',
                'sum(COALESCE(particulars.total, 0)) as total',
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
            ->leftJoin('accounting_branch_program', 'accounting_branch_program.branch_id = accounting_expense.branch_id and accounting_branch_program.program_id = accounting_expense.program_id');

        if($branchProgram != '')
        {
            $branchProgram = BranchProgram::findOne($branchProgram);
            $branchProgramName = $branchProgram->branchProgramName;

            if($season != '')
            {
                $season = Season::findOne($season);
                $seasonName = $season->seasonName;

                $beginningcoh = BeginningCoh::find()->where(['date(datetime)' => $cutoff['start'], 'season_id' => $season->id])->one();
                $beginningcohAmount = $beginningcoh ? $beginningcoh->cash_on_hand : 0;
                $beginningcobAmount = $beginningcoh ? $beginningcoh->cash_on_bank : 0;

                $incomeEnrolments = $incomeEnrolments->andWhere([
                    'accounting_income_enrolment.season_id' => $season->id,
                ]);

                $freebiesAndIcons = $freebiesAndIcons->andWhere([
                    'accounting_income_freebies_and_icons.season_id' => $season->id,
                ]);

                $budgetProposals = $budgetProposals->andWhere([
                    'accounting_income_budget_proposal.season_id' => $season->id,
                ]);

                $pettyExpenses = $pettyExpenses->andWhere([
                    'accounting_expense.season_id' => $season->id,
                ]);

                $photocopyExpenses = $photocopyExpenses->andWhere([
                    'accounting_expense.season_id' => $season->id,
                ]);

                $otherExpenses = $otherExpenses->andWhere([
                    'accounting_expense.season_id' => $season->id,
                ]);

                $bankDeposits = $bankDeposits->andWhere([
                    'accounting_expense.season_id' => $season->id,
                ]);

                $operatingExpenses = $operatingExpenses->andWhere([
                    'accounting_expense.season_id' => $season->id,
                ]);

                $branchTransfers = $branchTransfers->andWhere([
                    'accounting_expense.season_id' => $season->id,
                ]);

            }else{
                $beginningcoh = BeginningCoh::find()->where(['date(datetime)' => $cutoff['start'], 'branch_program_id' => $branchProgram->id])->one();
                $beginningcohAmount = $beginningcoh ? $beginningcoh->cash_on_hand : 0;
                $beginningcobAmount = $beginningcoh ? $beginningcoh->cash_on_bank : 0;

                $incomeEnrolments = $incomeEnrolments->andWhere([
                    'accounting_income.branch_id' => $branchProgram->branch_id,
                    'accounting_income.program_id' => $branchProgram->program_id
                ]);

                $freebiesAndIcons = $freebiesAndIcons->andWhere([
                    'accounting_income.branch_id' => $branchProgram->branch_id,
                    'accounting_income.program_id' => $branchProgram->program_id
                ]);

                $budgetProposals = $budgetProposals->andWhere([
                    'accounting_income.branch_id' => $branchProgram->branch_id,
                    'accounting_income.program_id' => $branchProgram->program_id
                ]);

                $pettyExpenses = $pettyExpenses->andWhere([
                    'accounting_expense.branch_id' => $branchProgram->branch_id,
                    'accounting_expense.program_id' => $branchProgram->program_id
                ]);

                $photocopyExpenses = $photocopyExpenses->andWhere([
                    'accounting_expense.branch_id' => $branchProgram->branch_id,
                    'accounting_expense.program_id' => $branchProgram->program_id
                ]);

                $otherExpenses = $otherExpenses->andWhere([
                    'accounting_expense.branch_id' => $branchProgram->branch_id,
                    'accounting_expense.program_id' => $branchProgram->program_id
                ]);

                $bankDeposits = $bankDeposits->andWhere([
                    'accounting_expense.branch_id' => $branchProgram->branch_id,
                    'accounting_expense.program_id' => $branchProgram->program_id
                ]);

                $operatingExpenses = $operatingExpenses->andWhere([
                    'accounting_expense.branch_id' => $branchProgram->branch_id,
                    'accounting_expense.program_id' => $branchProgram->program_id
                ]);

                $branchTransfers = $branchTransfers->andWhere([
                    'accounting_expense.branch_id' => $branchProgram->branch_id,
                    'accounting_expense.program_id' => $branchProgram->program_id
                ]);

            }
        }else{
            $user_info = Yii::$app->user->identity->userinfo;
            $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
            $rolenames =  ArrayHelper::map($roles, 'name','name');

            $branchProgramIds = [];

            if(in_array('TopManagement',$rolenames)){
                $branchPrograms = BranchProgram::find()
                                    ->select(['accounting_branch_program.id as id', 'concat(accounting_branch.name," - ",accounting_program.name) as branchProgramName'])
                                    ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                                    ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                                    ->asArray()
                                    ->orderBy(['branchProgramName' => SORT_ASC])
                                    ->all();
            }else{
                $branchPrograms = BranchProgram::find()
                                    ->select(['accounting_branch_program.id as id', 'concat(accounting_branch.name," - ",accounting_program.name) as branchProgramName'])
                                    ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                                    ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                                    ->where(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                    ->asArray()
                                    ->orderBy(['branchProgramName' => SORT_ASC])
                                    ->all();
            }

            if(!empty($branchPrograms))
            {
                foreach($branchPrograms as $bp)
                {
                    $branchProgramIds[] = $bp['id'];
                }
            }

            $beginningcoh = BeginningCoh::find()->where(['date(datetime)' => $cutoff['start']])->all();
            if($beginningcoh)
            {
                foreach($beginningcoh as $coh)
                {
                    $beginningcohAmount+=$coh->cash_on_hand;
                    $beginningcobAmount+=$coh->cash_on_bank;
                }
            }

            $branchProgramName = 'All Branch Programs';
            $data = [];

            $incomeEnrolments = $incomeEnrolments->andWhere([
                'in', 'accounting_branch_program.id', $branchProgramIds
            ]);

            $freebiesAndIcons = $freebiesAndIcons->andWhere([
                'in', 'accounting_branch_program.id', $branchProgramIds
            ]);

            $budgetProposals = $budgetProposals->andWhere([
                'in', 'accounting_branch_program.id', $branchProgramIds
            ]);

            $pettyExpenses = $pettyExpenses->andWhere([
                'in', 'accounting_branch_program.id', $branchProgramIds
            ]);

            $photocopyExpenses = $photocopyExpenses->andWhere([
                'in', 'accounting_branch_program.id', $branchProgramIds
            ]);

            $otherExpenses = $otherExpenses->andWhere([
                'in', 'accounting_branch_program.id', $branchProgramIds
            ]);

            $bankDeposits = $bankDeposits->andWhere([
                'in', 'accounting_branch_program.id', $branchProgramIds
            ]);

            $operatingExpenses = $operatingExpenses->andWhere([
                'in', 'accounting_branch_program.id', $branchProgramIds
            ]);

            $branchTransfers = $branchTransfers->andWhere([
                'in', 'accounting_branch_program.id', $branchProgramIds
            ]);
        }

        $totals = [];
        $totals2 = [];

        if(!empty($dates))
        {
            foreach($dates as $date)
            {
                $totals['incomes'][$date] = 0;
                $totals['expenses'][$date] = 0;
                $totals2['incomes'][$date] = 0;
                $totals2['expenses'][$date] = 0;
            }
        }
        
        $incomeEnrolments = $incomeEnrolments
            ->andWhere(['between', 'accounting_income.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
            ->andWhere(['in', 'accounting_package_type.id', ['5','7']])
            ->groupBy(['newDate', 'amountType'])
            ->asArray()
            ->all();

        $freebiesAndIcons = $freebiesAndIcons
            ->andWhere(['between', 'accounting_income.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
            ->andWhere(['code_id' => '9'])
            ->groupBy(['newDate', 'amountType'])
            ->asArray()
            ->all();

        $budgetProposals = $budgetProposals
            ->andWhere([
                'accounting_income_budget_proposal.approval_status' => 'Approved'
            ])
            ->andWhere(['between', 'accounting_income.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
            ->groupBy(['newDate', 'amountType'])
            ->asArray()
            ->all();

        $pettyExpenses = $pettyExpenses
            ->andWhere(['between', 'accounting_expense.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
            ->andWhere(['charge_to' => 'Icon'])
            ->groupBy(['newDate', 'amountType'])
            ->asArray()
            ->all();

        $photocopyExpenses = $photocopyExpenses
            ->andWhere(['between', 'accounting_expense.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
            ->andWhere(['charge_to' => 'Icon'])
            ->groupBy(['newDate', 'amountType'])
            ->asArray()
            ->all();

        $otherExpenses = $otherExpenses
            ->andWhere(['between', 'accounting_expense.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
            ->andWhere(['charge_to' => 'Icon'])
            ->groupBy(['newDate', 'amountType'])
            ->asArray()
            ->all();

        $bankDeposits = $bankDeposits
            ->andWhere(['between', 'accounting_expense.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
            ->groupBy(['newDate', 'amountType'])
            ->asArray()
            ->all();

        $operatingExpenses = $operatingExpenses
            ->andWhere(['between', 'accounting_expense.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
            ->andWhere(['charge_to' => 'Icon'])
            ->groupBy(['newDate', 'amountType'])
            ->asArray()
            ->all();

        $branchTransfers = $branchTransfers
            ->andWhere(['between', 'accounting_expense.datetime', $cutoff['start'].' 00:00:00', $cutoff['end'].' 23:59:59'])
            ->groupBy(['newDate', 'amountType'])
            ->asArray()
            ->all();

        if($incomeEnrolments)
        {
            foreach($incomeEnrolments as $enrolment)
            {
                if($enrolment['amountType'] == 'Cash')
                {
                    $data['incomes']['enrolments'][$enrolment['amountType']][$enrolment['newDate']] = $enrolment;
                }else{
                    $data['incomes']['enrolments']['Non-Cash'][$enrolment['newDate']] = $enrolment;
                }
            }
        }

        if($freebiesAndIcons)
        {
            foreach($freebiesAndIcons as $freebies)
            {
                if($freebies['amountType'] == 'Cash')
                {
                    $data['incomes']['freebies'][$freebies['amountType']][$freebies['newDate']] = $freebies;
                }else{
                    $data['incomes']['freebies']['Non-Cash'][$freebies['newDate']] = $freebies;
                }
            }
        }

        if($budgetProposals)
        {
            foreach($budgetProposals as $budgetProposal)
            {
                if($budgetProposal['amountType'] == 'Cash')
                {
                    $data['incomes']['budgetProposals'][$budgetProposal['amountType']][$budgetProposal['newDate']] = $budgetProposal;
                }else{
                    $data['incomes']['budgetProposals']['Non-Cash'][$budgetProposal['newDate']] = $budgetProposal;
                }
            }
        }

        if($pettyExpenses)
        {
            foreach($pettyExpenses as $pettyExpense)
            {
                if($pettyExpense['amountType'] == 'Cash')
                {
                    $data['expenses']['pettyExpenses'][$pettyExpense['amountType']][$pettyExpense['newDate']] = $pettyExpense;
                }else{
                    $data['expenses']['pettyExpenses']['Non-Cash'][$pettyExpense['newDate']] = $pettyExpense;
                }
            }
        }

        if($photocopyExpenses)
        {
            foreach($photocopyExpenses as $photocopyExpense)
            {
                if($photocopyExpense['amountType'] == 'Cash')
                {
                    $data['expenses']['photocopyExpenses'][$photocopyExpense['amountType']][$photocopyExpense['newDate']] = $photocopyExpense;
                }else{
                    $data['expenses']['photocopyExpenses']['Non-Cash'][$photocopyExpense['newDate']] = $photocopyExpense;
                }
            }
        }

        if($otherExpenses)
        {
            foreach($otherExpenses as $otherExpense)
            {
                if($otherExpense['amountType'] == 'Cash')
                {
                    $data['expenses']['otherExpenses'][$otherExpense['amountType']][$otherExpense['newDate']] = $otherExpense;
                }else{
                    $data['expenses']['otherExpenses']['Non-Cash'][$otherExpense['newDate']] = $otherExpense;
                }
            }
        }

        if($bankDeposits)
        {
            foreach($bankDeposits as $bankDeposit)
            {
                if($bankDeposit['amountType'] == 'Cash')
                {
                    $data['expenses']['bankDeposits'][$bankDeposit['amountType']][$bankDeposit['newDate']] = $bankDeposit;
                }else{
                    $data['expenses']['bankDeposits']['Non-Cash'][$bankDeposit['newDate']] = $bankDeposit;
                }
            }
        }

        if($operatingExpenses)
        {
            foreach($operatingExpenses as $operatingExpense)
            {
                if($operatingExpense['amountType'] == 'Cash')
                {
                    $data['expenses']['operatingExpenses'][$operatingExpense['amountType']][$operatingExpense['newDate']] = $operatingExpense;
                }else{
                    $data['expenses']['operatingExpenses']['Non-Cash'][$operatingExpense['newDate']] = $operatingExpense;
                }
            }
        }

        if($branchTransfers)
        {
            foreach($branchTransfers as $branchTransfer)
            {
                if($branchTransfer['amountType'] == 'Cash')
                {
                    $data['expenses']['branchTransfers'][$branchTransfer['amountType']][$branchTransfer['newDate']] = $branchTransfer;
                }else{
                    $data['expenses']['branchTransfers']['Non-Cash'][$branchTransfer['newDate']] = $branchTransfer;
                }
            }
        }

        return $this->renderAjax('_generate_summary_icon', [
            'id' => $id,
            'cutoff' => $cutoff,
            'dates' => $dates,
            'branchProgram' => $branchProgram,
            'branchProgramName' => $branchProgramName,
            'season' => $season,
            'seasonName' => $seasonName,
            'data' => $data,
            'totals' => $totals,
            'totals2' => $totals2,
            'beginningcohAmount' => $beginningcohAmount,
            'beginningcobAmount' => $beginningcobAmount,
        ]);
    }


    /**
     * Lists all Audit models.
     * @return mixed
     */
    public function actionIndex()
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

        return $this->render('index', [
            'model' => $model,
            'branchPrograms' => $branchPrograms
        ]);
    }

    /**
     * Finds the Audit model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Audit the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Audit::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
