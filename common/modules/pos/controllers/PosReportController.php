<?php

namespace common\modules\pos\controllers;

use Yii;
use common\modules\pos\models\PosEnrolment;
use common\modules\pos\models\PosBranchProgram;
use common\modules\pos\models\PosSeason;
use common\modules\pos\models\PosIncome;
use common\modules\pos\models\PosIncomeItem;
use common\modules\pos\models\PosExpense;
use common\modules\pos\models\PosExpenseItem;
use common\modules\pos\models\PosBeginningAmount;
use common\modules\pos\models\PosAudit;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
/**
 * PosProgramController implements the CRUD actions for PosProgram model.
 */
class PosReportController extends Controller
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
                        'actions' => ['enrolment'],
                        'allow' => true,
                        'roles' => ['@'],
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

    function dateRange( $first, $last, $step = '+1 day', $format = 'Y-m-d' )
    {
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

    public function actionSeasonList($id) {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $seasons = Yii::$app->user->identity->userinfo->BRANCH_C != "" ?
                    PosSeason::find()
                    ->select(['pos_season.id as id', 'concat("SEASON ",pos_season.title) as title'])
                    ->leftJoin('pos_branch_program','pos_branch_program.id = pos_season.branch_program_id')
                    ->andWhere(['pos_branch_program.id' => $id])
                    ->andWhere(['pos_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                    ->andWhere(['status' => 'Active'])
                    ->asArray()
                    ->orderBy(['title' => SORT_ASC])
                    ->all() :
                    PosSeason::find()
                    ->select(['pos_season.id as id', 'concat("SEASON ",pos_season.title) as title'])
                    ->where(['branch_program_id' => $id])
                    ->andWhere(['status' => 'Active'])
                    ->asArray()
                    ->orderBy(['title' => SORT_ASC])
                    ->all();

        $out = [];

        if($seasons)
        {
            $out[] = ['id' => '0', 'text' => 'ALL'];
            foreach($seasons as $season)
            {
                $out[] = ['id' => $season['id'], 'text' => $season['title']];
            }
        }

        return $out;
    }

    /**
     * Lists all PosProgram models.
     * @return mixed
     */
    /**
     * Lists all PosProgram models.
     * @return mixed
     */
    public function actionEnrolment()
    {   
        $model = new PosEnrolment();
        $model->scenario = 'reportEnrolment';

        $branchPrograms = Yii::$app->user->identity->userinfo->BRANCH_C != "" ?
                    PosBranchProgram::find()
                    ->select([
                        'pos_branch_program.id as id',
                        'concat(pos_branch.title," - ",pos_program.title) as title'
                    ])
                    ->leftJoin('pos_branch', 'pos_branch.id = pos_branch_program.branch_id')
                    ->leftJoin('pos_program', 'pos_program.id = pos_branch_program.program_id')
                    ->andWhere(['pos_branch.id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                    ->orderBy(['title' => SORT_ASC])
                    ->asArray()
                    ->all() :
                    PosBranchProgram::find()
                    ->select([
                        'pos_branch_program.id as id',
                        'concat(pos_branch.title," - ",pos_program.title) as title'
                    ])
                    ->leftJoin('pos_branch', 'pos_branch.id = pos_branch_program.branch_id')
                    ->leftJoin('pos_program', 'pos_program.id = pos_branch_program.program_id')
                    ->orderBy(['title' => SORT_ASC])
                    ->asArray()
                    ->all();

        $branchPrograms = ArrayHelper::map($branchPrograms, 'id', 'title');

        $seasons = [];

        if($model->load(Yii::$app->request->post()))
        {
            $seasons = PosSeason::find()
                    ->select(['pos_season.id as id', 'concat("SEASON ",pos_season.title) as title'])
                    ->where(['branch_program_id' => $model->branch_program_id])
                    ->andWhere(['status' => 'Active'])
                    ->asArray()
                    ->orderBy(['title' => SORT_ASC])
                    ->all(); 

            $seasons = ['0' => 'ALL'] + ArrayHelper::map($seasons, 'id', 'title');

            $data = PosEnrolment::find()
                    ->select([
                        'pos_enrolment.enrolment_date as enrolmentDate',
                        'concat(pos_branch.title," - ",pos_program.title) as branchProgramTitle',
                        'concat("SEASON ",pos_season.title) as seasonTitle',
                        'pos_customer.id_number',
                        /*'tblprovince.province_m as province',
                        'tblcitymun.citymun_m as citymun',*/
                        'pos_customer.first_name',
                        'pos_customer.middle_name',
                        'pos_customer.last_name',
                        'pos_customer.ext_name',
                        /*'pos_customer.year_graduated',
                        'pos_customer.address',
                        'pos_customer.contact_no',
                        'pos_customer.birthday',
                        'pos_customer.prc',
                        'pos_customer.email_address',*/
                        'pos_enrolment_type.title as enrolmentType',
                        'pos_product.title as productTitle',
                        'COALESCE(pos_product.amount, 0) as productAmount',
                        'pos_discount_type.title as discountType',
                        'COALESCE(pos_discount.amount, 0) as discountAmount',
                        'COALESCE(pos_product.amount - pos_discount.amount, 0) as finalAmount',
                        'COALESCE(sum(pos_income_item.amount), 0) as paymentAmount',
                        'COALESCE((pos_product.amount - pos_discount.amount) - COALESCE(sum(pos_income_item.amount), 0), 0) as balanceAmount',
                        'CASE
                            WHEN (COALESCE(pos_product.amount, 0) - COALESCE(pos_discount.amount, 0)) = ((COALESCE(pos_product.amount, 0) - COALESCE(pos_discount.amount, 0)) - COALESCE(sum(pos_income_item.amount), 0)) THEN "UNPAID"
                            WHEN (COALESCE(pos_product.amount, 0) - COALESCE(pos_discount.amount, 0)) - COALESCE(sum(pos_income_item.amount), 0) != 0 THEN "PARTIAL"
                            WHEN (COALESCE(pos_product.amount, 0) - COALESCE(pos_discount.amount, 0)) - COALESCE(sum(pos_income_item.amount), 0) = 0 THEN "FULL"
                         END as status'
                    ])
                    ->leftJoin('pos_customer', 'pos_customer.id = pos_enrolment.customer_id')
                    ->leftJoin('pos_discount', 'pos_discount.enrolment_id = pos_enrolment.id')
                    ->leftJoin('pos_discount_type', 'pos_discount_type.id = pos_discount.discount_type_id')
                    ->leftJoin('pos_enrolment_type', 'pos_enrolment_type.id = pos_enrolment.enrolment_type_id')
                    ->leftJoin('pos_product', 'pos_product.id = pos_enrolment.product_id')
                    ->leftJoin('pos_season', 'pos_season.id = pos_enrolment.season_id')
                    ->leftJoin('pos_branch_program', 'pos_branch_program.id = pos_season.branch_program_id')
                    ->leftJoin('pos_branch', 'pos_branch.id = pos_branch_program.branch_id')
                    ->leftJoin('pos_program', 'pos_program.id = pos_branch_program.program_id')
                    ->leftJoin('pos_income_item', 'pos_income_item.product_id = pos_enrolment.product_id and pos_income_item.customer_id = pos_enrolment.customer_id and pos_income_item.season_id = pos_enrolment.season_id');

                $data = Yii::$app->user->identity->userinfo->BRANCH_C != "" ? $data->andWhere(['pos_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C != ""]) : $data; 

                if($model->branch_program_id != 0)
                {
                    $data = $data->andWhere(['pos_season.branch_program_id' => $model->branch_program_id]);
                }

                if($model->search_season_id != 0)
                {
                    $data = $data->andWhere(['pos_season.id' => $model->search_season_id]);
                }

                $data = $data->andWhere(['BETWEEN', 'pos_enrolment.enrolment_date', $model->from_date, $model->to_date]);
                $data = $data
                        ->groupBy(['pos_enrolment.id'])
                        ->orderBy(['pos_enrolment.enrolment_date' => SORT_DESC])
                        ->asArray()
                        ->all();

                $filepath = Yii::getAlias('@frontend').'/web/reports/enrolment_report.xlsx';

                $reader = IOFactory::createReader("Xlsx");
                $spreadsheet = $reader->load($filepath);
                $sheet = $spreadsheet->getActiveSheet();
                $sheet->setCellValue('B7', date("F j, Y"));

                if(!empty($data))
                {
                    $i = 11;
                    foreach($data as $dt)
                    {
                        $sheet->setCellValue('A'.$i, date("F j, Y", strtotime($dt['enrolmentDate'])));
                        $sheet->setCellValue('B'.$i, $dt['branchProgramTitle']);
                        $sheet->setCellValue('C'.$i, $dt['seasonTitle']);
                        $sheet->setCellValue('D'.$i, $dt['id_number']);
                        $sheet->setCellValue('E'.$i, $dt['first_name']);
                        $sheet->setCellValue('F'.$i, $dt['middle_name']);
                        $sheet->setCellValue('G'.$i, $dt['last_name']);
                        $sheet->setCellValue('H'.$i, $dt['ext_name']);
                        $sheet->setCellValue('I'.$i, $dt['enrolmentType']);
                        $sheet->setCellValue('J'.$i, $dt['productTitle']);
                        $sheet->setCellValue('K'.$i, $dt['productAmount']);
                        $sheet->setCellValue('L'.$i, $dt['discountType']);
                        $sheet->setCellValue('M'.$i, $dt['discountAmount']);
                        $sheet->setCellValue('N'.$i, $dt['finalAmount']);
                        $sheet->setCellValue('O'.$i, $dt['paymentAmount']);
                        $sheet->setCellValue('P'.$i, $dt['balanceAmount']);
                        $sheet->setCellValue('Q'.$i, $dt['status']);

                        $i++;
                    }
                }

                $spreadsheet->setActiveSheetIndex(0);
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="enrolment_report.xlsx"');
                header('Cache-Control: max-age=0');
                header('Cache-Control: max-age=1');

                $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
                $writer->save('php://output');
        }

        return $this->render('index', [
            'title' => 'Enrolment Report',
            'model' => $model,
            'branchPrograms' => $branchPrograms,
            'seasons' => $seasons,
        ]);
    }

    public function actionIncome()
    {
        $model = new PosEnrolment();
        $model->scenario = 'reportEnrolment';

        $branchPrograms = Yii::$app->user->identity->userinfo->BRANCH_C != "" ?
                    PosBranchProgram::find()
                    ->select([
                        'pos_branch_program.id as id',
                        'concat(pos_branch.title," - ",pos_program.title) as title'
                    ])
                    ->leftJoin('pos_branch', 'pos_branch.id = pos_branch_program.branch_id')
                    ->leftJoin('pos_program', 'pos_program.id = pos_branch_program.program_id')
                    ->andWhere(['pos_branch.id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                    ->orderBy(['title' => SORT_ASC])
                    ->asArray()
                    ->all() :
                    PosBranchProgram::find()
                    ->select([
                        'pos_branch_program.id as id',
                        'concat(pos_branch.title," - ",pos_program.title) as title'
                    ])
                    ->leftJoin('pos_branch', 'pos_branch.id = pos_branch_program.branch_id')
                    ->leftJoin('pos_program', 'pos_program.id = pos_branch_program.program_id')
                    ->orderBy(['title' => SORT_ASC])
                    ->asArray()
                    ->all();

        $branchPrograms = ArrayHelper::map($branchPrograms, 'id', 'title');

        $seasons = [];

        if($model->load(Yii::$app->request->post()))
        {
            $seasons = PosSeason::find()
                    ->select(['pos_season.id as id', 'concat("SEASON ",pos_season.title) as title'])
                    ->where(['branch_program_id' => $model->branch_program_id])
                    ->andWhere(['status' => 'Active'])
                    ->asArray()
                    ->orderBy(['title' => SORT_ASC])
                    ->all(); 

            $seasons = ['0' => 'ALL'] + ArrayHelper::map($seasons, 'id', 'title');

            $data = PosIncomeItem::find()
                    ->select([
                        'concat(pos_branch.title," - ",pos_program.title) as branchProgramTitle',
                        'concat("SEASON ",pos_season.title) as seasonTitle',
                        'concat(pos_customer.first_name," ",pos_customer.middle_name," ",pos_customer.last_name," ",pos_customer.ext_name) as customerName',
                        'pos_product.title as productTitle',
                        'COALESCE(pos_product.amount, 0) as productAmount',
                        'pos_income.official_receipt_id as officialReceiptNo',
                        'pos_income.ar_number as acknowledgmentReceiptNo',
                        'COALESCE(pos_income_item.amount, 0) as amountPaid',
                        'pos_income.invoice_date as invoiceDate',
                        'pos_income.payment_due as paymentDue',
                        'pos_amount_type.title as paymentMethod',
                        'pos_income_item.transaction_no as transactionNo',
                        'pos_account.title as accountName',
                        'pos_income_type.title as incomeTypeName',
                        'pos_income.status as status',
                    ])
                    ->leftJoin('pos_customer', 'pos_customer.id = pos_income_item.customer_id')
                    ->leftJoin('pos_season', 'pos_season.id = pos_income_item.season_id')
                    ->leftJoin('pos_branch_program', 'pos_branch_program.id = pos_season.branch_program_id')
                    ->leftJoin('pos_branch', 'pos_branch.id = pos_branch_program.branch_id')
                    ->leftJoin('pos_program', 'pos_program.id = pos_branch_program.program_id')
                    ->leftJoin('pos_amount_type', 'pos_amount_type.id = pos_income_item.amount_type_id')
                    ->leftJoin('pos_account', 'pos_account.id = pos_income_item.account_id')
                    ->leftJoin('pos_income', 'pos_income.id = pos_income_item.income_id')
                    ->leftJoin('pos_product', 'pos_product.id = pos_income_item.product_id')
                    ->leftJoin('pos_income_type', 'pos_income_type.id = pos_income_item.income_type_id')
                    ;

                $data = Yii::$app->user->identity->userinfo->BRANCH_C != "" ? $data->andWhere(['pos_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C != ""]) : $data; 

                if($model->branch_program_id != 0)
                {
                    $data = $data->andWhere(['pos_season.branch_program_id' => $model->branch_program_id]);
                }

                if($model->search_season_id != 0)
                {
                    $data = $data->andWhere(['pos_season.id' => $model->search_season_id]);
                }

                $data = $data->andWhere(['BETWEEN', 'pos_income.invoice_date', $model->from_date, $model->to_date]);
                $data = $data
                        ->orderBy(['pos_income.invoice_date' => SORT_DESC])
                        ->asArray()
                        ->all();

                $filepath = Yii::getAlias('@frontend').'/web/reports/income_report.xlsx';

                $reader = IOFactory::createReader("Xlsx");
                $spreadsheet = $reader->load($filepath);
                $sheet = $spreadsheet->getActiveSheet();
                $sheet->setCellValue('B7', date("F j, Y"));

                if(!empty($data))
                {
                    $i = 11;
                    foreach($data as $dt)
                    {
                        $sheet->setCellValue('A'.$i, $dt['branchProgramTitle']);
                        $sheet->setCellValue('B'.$i, $dt['seasonTitle']);
                        $sheet->setCellValue('C'.$i, $dt['customerName']);
                        $sheet->setCellValue('D'.$i, $dt['productTitle']);
                        $sheet->setCellValue('E'.$i, $dt['productAmount']);
                        $sheet->setCellValue('F'.$i, $dt['officialReceiptNo']);
                        $sheet->setCellValue('G'.$i, $dt['acknowledgmentReceiptNo']);
                        $sheet->setCellValue('H'.$i, $dt['amountPaid']);
                        $sheet->setCellValue('I'.$i, $dt['invoiceDate']);
                        $sheet->setCellValue('J'.$i, $dt['paymentDue']);
                        $sheet->setCellValue('K'.$i, $dt['paymentMethod']);
                        $sheet->setCellValue('L'.$i, $dt['transactionNo']);
                        $sheet->setCellValue('M'.$i, $dt['accountName']);
                        $sheet->setCellValue('N'.$i, $dt['incomeTypeName']);
                        $sheet->setCellValue('O'.$i, $dt['status']);

                        $i++;
                    }
                }

                $spreadsheet->setActiveSheetIndex(0);
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="income_report.xlsx"');
                header('Cache-Control: max-age=0');
                header('Cache-Control: max-age=1');

                $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
                $writer->save('php://output');
        }

        return $this->render('index', [
            'title' => 'Income Report',
            'model' => $model,
            'branchPrograms' => $branchPrograms,
            'seasons' => $seasons,
        ]);
    }

    public function actionExpense()
    {
        $model = new PosEnrolment();
        $model->scenario = 'reportEnrolment';

        $branchPrograms = Yii::$app->user->identity->userinfo->BRANCH_C != "" ?
                    PosBranchProgram::find()
                    ->select([
                        'pos_branch_program.id as id',
                        'concat(pos_branch.title," - ",pos_program.title) as title'
                    ])
                    ->leftJoin('pos_branch', 'pos_branch.id = pos_branch_program.branch_id')
                    ->leftJoin('pos_program', 'pos_program.id = pos_branch_program.program_id')
                    ->andWhere(['pos_branch.id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                    ->orderBy(['title' => SORT_ASC])
                    ->asArray()
                    ->all() :
                    PosBranchProgram::find()
                    ->select([
                        'pos_branch_program.id as id',
                        'concat(pos_branch.title," - ",pos_program.title) as title'
                    ])
                    ->leftJoin('pos_branch', 'pos_branch.id = pos_branch_program.branch_id')
                    ->leftJoin('pos_program', 'pos_program.id = pos_branch_program.program_id')
                    ->orderBy(['title' => SORT_ASC])
                    ->asArray()
                    ->all();

        $branchPrograms = ArrayHelper::map($branchPrograms, 'id', 'title');

        $seasons = [];

        if($model->load(Yii::$app->request->post()))
        {
            $seasons = PosSeason::find()
                    ->select(['pos_season.id as id', 'concat("SEASON ",pos_season.title) as title'])
                    ->where(['branch_program_id' => $model->branch_program_id])
                    ->andWhere(['status' => 'Active'])
                    ->asArray()
                    ->orderBy(['title' => SORT_ASC])
                    ->all(); 

            $seasons = ['0' => 'ALL'] + ArrayHelper::map($seasons, 'id', 'title');

            $data = PosExpenseItem::find()
                    ->select([
                        'concat(pos_branch.title," - ",pos_program.title) as branchProgramTitle',
                        'concat("SEASON ",pos_season.title) as seasonTitle',
                        'pos_vendor.title as vendorTitle',
                        'pos_expense.expense_date as expenseDate',
                        'pos_expense.voucher_no as voucherNo',
                        'pos_expense_type.title as category',
                        'pos_expense_item.description as description',
                        'pos_expense_item.quantity as quantity',
                        'COALESCE(pos_expense_item.amount, 0) as unitPrice',
                        'COALESCE(pos_expense_item.quantity * pos_expense_item.amount, 0) as totalAmount',
                        'pos_amount_type.title as amountType',
                        'pos_account.title as accountName',
                        'pos_expense.transaction_no as transactionNo',
                    ])
                    ->leftJoin('pos_expense', 'pos_expense.id = pos_expense_item.expense_id')
                    ->leftJoin('pos_season', 'pos_season.id = pos_expense_item.season_id')
                    ->leftJoin('pos_branch_program', 'pos_branch_program.id = pos_season.branch_program_id')
                    ->leftJoin('pos_branch', 'pos_branch.id = pos_branch_program.branch_id')
                    ->leftJoin('pos_program', 'pos_program.id = pos_branch_program.program_id')
                    ->leftJoin('pos_amount_type', 'pos_amount_type.id = pos_expense_item.amount_type_id')
                    ->leftJoin('pos_account', 'pos_account.id = pos_expense.account_id')
                    ->leftJoin('pos_vendor', 'pos_vendor.id = pos_expense.vendor_id')
                    ->leftJoin('pos_expense_type', 'pos_expense_type.id = pos_expense_item.expense_type_id')
                    ;

                $data = Yii::$app->user->identity->userinfo->BRANCH_C != "" ? $data->andWhere(['pos_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C != ""]) : $data; 
                if($model->branch_program_id != 0)
                {
                    $data = $data->andWhere(['pos_season.branch_program_id' => $model->branch_program_id]);
                }

                if($model->search_season_id != 0)
                {
                    $data = $data->andWhere(['pos_season.id' => $model->search_season_id]);
                }

                $data = $data->andWhere(['BETWEEN', 'pos_expense.expense_date', $model->from_date, $model->to_date]);
                $data = $data
                        ->orderBy(['pos_expense.expense_date' => SORT_DESC])
                        ->asArray()
                        ->all();

                $filepath = Yii::getAlias('@frontend').'/web/reports/expense_report.xlsx';

                $reader = IOFactory::createReader("Xlsx");
                $spreadsheet = $reader->load($filepath);
                $sheet = $spreadsheet->getActiveSheet();
                $sheet->setCellValue('B7', date("F j, Y"));

                if(!empty($data))
                {
                    $i = 11;
                    foreach($data as $dt)
                    {
                        $sheet->setCellValue('A'.$i, $dt['branchProgramTitle']);
                        $sheet->setCellValue('B'.$i, $dt['seasonTitle']);
                        $sheet->setCellValue('C'.$i, $dt['vendorTitle']);
                        $sheet->setCellValue('D'.$i, $dt['expenseDate']);
                        $sheet->setCellValue('E'.$i, $dt['voucherNo']);
                        $sheet->setCellValue('F'.$i, $dt['category']);
                        $sheet->setCellValue('G'.$i, $dt['description']);
                        $sheet->setCellValue('H'.$i, $dt['quantity']);
                        $sheet->setCellValue('I'.$i, $dt['unitPrice']);
                        $sheet->setCellValue('J'.$i, $dt['totalAmount']);
                        $sheet->setCellValue('K'.$i, $dt['amountType']);
                        $sheet->setCellValue('L'.$i, $dt['transactionNo']);
                        $sheet->setCellValue('M'.$i, $dt['accountName']);

                        $i++;
                    }
                }

                $spreadsheet->setActiveSheetIndex(0);
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="expense_report.xlsx"');
                header('Cache-Control: max-age=0');
                header('Cache-Control: max-age=1');

                $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
                $writer->save('php://output');
        }

        return $this->render('index', [
            'title' => 'Expense Report',
            'model' => $model,
            'branchPrograms' => $branchPrograms,
            'seasons' => $seasons,
        ]);
    }

    public function actionAudit()
    {
        $model = new PosEnrolment();
        $model->scenario = 'reportEnrolment';

        $branchPrograms = Yii::$app->user->identity->userinfo->BRANCH_C != "" ?
                    PosBranchProgram::find()
                    ->select([
                        'pos_branch_program.id as id',
                        'concat(pos_branch.title," - ",pos_program.title) as title'
                    ])
                    ->leftJoin('pos_branch', 'pos_branch.id = pos_branch_program.branch_id')
                    ->leftJoin('pos_program', 'pos_program.id = pos_branch_program.program_id')
                    ->andWhere(['pos_branch.id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                    ->orderBy(['title' => SORT_ASC])
                    ->asArray()
                    ->all() :
                    PosBranchProgram::find()
                    ->select([
                        'pos_branch_program.id as id',
                        'concat(pos_branch.title," - ",pos_program.title) as title'
                    ])
                    ->leftJoin('pos_branch', 'pos_branch.id = pos_branch_program.branch_id')
                    ->leftJoin('pos_program', 'pos_program.id = pos_branch_program.program_id')
                    ->orderBy(['title' => SORT_ASC])
                    ->asArray()
                    ->all();

        $branchPrograms = ArrayHelper::map($branchPrograms, 'id', 'title');

        $seasons = [];

        if($model->load(Yii::$app->request->post()))
        {
            $seasons = PosSeason::find()
                    ->select(['pos_season.id as id', 'concat("SEASON ",pos_season.title) as title'])
                    ->where(['branch_program_id' => $model->branch_program_id])
                    ->andWhere(['status' => 'Active'])
                    ->asArray()
                    ->orderBy(['title' => SORT_ASC])
                    ->all(); 

            $seasons = ['0' => 'ALL'] + ArrayHelper::map($seasons, 'id', 'title');

            $beginning = PosBeginningAmount::find()
                        ->select([
                            'pos_beginning_amount.amount as totalAmount',
                            'pos_beginning_amount.type as amountType'
                        ])
                        ->leftJoin('pos_season', 'pos_season.id = pos_beginning_amount.season_id')
                        ->leftJoin('pos_branch_program', 'pos_branch_program.id = pos_season.branch_program_id')
                        ;

            $income = PosIncomeItem::find()
                    ->select([
                        'COALESCE(sum(pos_income_item.amount), 0) as totalAmount',
                        'pos_amount_type.type as amountType'
                    ])
                    ->leftJoin('pos_income', 'pos_income.id = pos_income_item.income_id')
                    ->leftJoin('pos_season', 'pos_season.id = pos_income_item.season_id')
                    ->leftJoin('pos_branch_program', 'pos_branch_program.id = pos_season.branch_program_id')
                    ->leftJoin('pos_amount_type', 'pos_amount_type.id = pos_income_item.amount_type_id')
                    ->andWhere(['pos_income.status' => 'Active'])
                    ;

            $expense = PosExpenseItem::find()
                    ->select([
                        'COALESCE(sum(pos_expense_item.quantity * pos_expense_item.amount), 0) as totalAmount',
                        'pos_amount_type.title as amountType',
                    ])
                    ->leftJoin('pos_expense', 'pos_expense.id = pos_expense_item.expense_id')
                    ->leftJoin('pos_season', 'pos_season.id = pos_expense_item.season_id')
                    ->leftJoin('pos_branch_program', 'pos_branch_program.id = pos_season.branch_program_id')
                    ->leftJoin('pos_amount_type', 'pos_amount_type.id = pos_expense_item.amount_type_id')
                    ;

            $audit = PosAudit::find()
                    ->select([
                        'COALESCE(sum(pos_denomination.title * COALESCE(pos_audit.total, 0)), 0) as totalAmount'
                    ])
                    ->leftJoin('pos_denomination', 'pos_denomination.id = pos_audit.denomination_id')
                    ->leftJoin('pos_season', 'pos_season.id = pos_audit.season_id')
                    ->leftJoin('pos_branch_program', 'pos_branch_program.id = pos_season.branch_program_id')
                    ->leftJoin('pos_branch', 'pos_branch.id = pos_branch_program.branch_id')
                    ->leftJoin('pos_program', 'pos_program.id = pos_branch_program.program_id')
                    ;


            $branchProgramTitle = 'ALL';
            $seasonTitle = 'ALL';

            $beginning = Yii::$app->user->identity->userinfo->BRANCH_C != "" ? $beginning->andWhere(['pos_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C != ""]) : $beginning; 
            $income = Yii::$app->user->identity->userinfo->BRANCH_C != "" ? $income->andWhere(['pos_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C != ""]) : $income; 
            $expense = Yii::$app->user->identity->userinfo->BRANCH_C != "" ? $expense->andWhere(['pos_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C != ""]) : $expense; 
            $audit = Yii::$app->user->identity->userinfo->BRANCH_C != "" ? $audit->andWhere(['pos_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C != ""]) : $audit; 

            if($model->branch_program_id != 0)
            {   
                $branchProgram = PosBranchProgram::findOne(['id' => $model->branch_program_id]);
                $branchProgramTitle = $branchProgram->branchProgramName;

                $beginning = $beginning->andWhere(['pos_season.branch_program_id' => $model->branch_program_id]);
                $income = $income->andWhere(['pos_season.branch_program_id' => $model->branch_program_id]);
                $expense = $expense->andWhere(['pos_season.branch_program_id' => $model->branch_program_id]);
                $audit = $audit->andWhere(['pos_season.branch_program_id' => $model->branch_program_id]);
            }

            if($model->search_season_id != 0)
            {
                $season = PosSeason::findOne(['id' => $model->search_season_id]);
                $seasonTitle = $season->seasonTitle;

                $beginning = $beginning->andWhere(['pos_season.id' => $model->search_season_id]);
                $income = $income->andWhere(['pos_season.id' => $model->search_season_id]);
                $expense = $expense->andWhere(['pos_season.id' => $model->search_season_id]);
                $audit = $audit->andWhere(['pos_season.id' => $model->search_season_id]);
            }

            $beginning = $beginning->andWhere(['BETWEEN','pos_beginning_amount.account_date', $model->from_date, $model->to_date]);
            $income = $income->andWhere(['BETWEEN','pos_income.invoice_date', $model->from_date, $model->to_date]);
            $expense = $expense->andWhere(['BETWEEN','pos_expense.expense_date', $model->from_date, $model->to_date]);
            $audit = $audit->andWhere(['BETWEEN','pos_audit.audit_date', $model->from_date, $model->to_date]);

            $beginning = $beginning
                    ->groupBy(['amountType'])
                    ->asArray()
                    ->all();

            $income = $income
                    ->groupBy(['amountType'])
                    ->asArray()
                    ->all();


            $expense = $expense
                    ->groupBy(['amountType'])
                    ->asArray()
                    ->all();


            $audit = $audit
                    ->asArray()
                    ->one();

            $filepath = Yii::getAlias('@frontend').'/web/reports/audit_report.xlsx';

            $reader = IOFactory::createReader("Xlsx");
            $spreadsheet = $reader->load($filepath);
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setCellValue('B7', date("F j, Y"));
            $sheet->setCellValue('B9', $branchProgramTitle);
            $sheet->setCellValue('B10', $seasonTitle);
            $sheet->setCellValue('B11', date("F j, Y", strtotime($model->from_date)));
            $sheet->setCellValue('B12', date("F j, Y", strtotime($model->to_date)));

            $beginningCash = 0;
            $beginningNonCash = 0;

            $incomeCash = 0;
            $incomeNonCash = 0;

            $expenseCash = 0;
            $expenseNonCash = 0;

            if(!empty($beginning))
            {
                foreach($beginning as $dt)
                {
                    if($dt['amountType'] == 'CASH'){ $beginningCash = $dt['totalAmount'];}
                    if($dt['amountType'] == 'NON-CASH'){ $beginningNonCash = $dt['totalAmount'];}
                }
            }

            if(!empty($income))
            {
                foreach($income as $dt)
                {
                    if($dt['amountType'] == 'CASH'){ $incomeCash = $dt['totalAmount'];}
                    if($dt['amountType'] == 'NON-CASH'){ $incomeNonCash = $dt['totalAmount'];}
                }
            }

            if(!empty($expense))
            {
                foreach($expense as $dt)
                {
                    if($dt['amountType'] == 'CASH'){ $expenseCash = $dt['totalAmount'];}
                    if($dt['amountType'] == 'NON-CASH'){ $expenseNonCash = $dt['totalAmount'];}
                }
            }

            $sheet->setCellValue('B15', $beginningCash);
            $sheet->setCellValue('B16', $incomeCash);
            $sheet->setCellValue('B17', $expenseCash);
            $sheet->setCellValue('B18', ($beginningCash + $incomeCash) - $expenseCash);

            $sheet->setCellValue('B22', $beginningNonCash);
            $sheet->setCellValue('B23', $incomeNonCash);
            $sheet->setCellValue('B24', $expenseNonCash);
            $sheet->setCellValue('B25', ($beginningNonCash + $incomeNonCash) - $expenseNonCash);

            $sheet->setCellValue('B27', !empty($audit) ? $audit['totalAmount'] : 0);

            $spreadsheet->setActiveSheetIndex(0);
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="audit_report.xlsx"');
            header('Cache-Control: max-age=0');
            header('Cache-Control: max-age=1');

            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
        }

        return $this->render('index', [
            'title' => 'Audit Report',
            'model' => $model,
            'branchPrograms' => $branchPrograms,
            'seasons' => $seasons,
        ]);
    }
}
