<?php

namespace common\modules\pos\controllers;

use Yii;
use common\modules\pos\models\Model;
use common\modules\pos\models\PosAudit;
use common\modules\pos\models\PosBeginningAmount;
use common\modules\pos\models\PosSeason;
use common\modules\pos\models\PosIncome;
use common\modules\pos\models\PosIncomeItem;
use common\modules\pos\models\PosIncomeType;
use common\modules\pos\models\PosExpense;
use common\modules\pos\models\PosExpenseItem;
use common\modules\pos\models\PosExpenseType;
use common\modules\pos\models\PosDenomination;
use common\modules\pos\models\PosAuditSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;

/**
 * PosAuditController implements the CRUD actions for PosAudit model.
 */
class PosAuditController extends Controller
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
                    'update' => ['POST'],
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index'],
                'rules' => [
                    [
                        'actions' => ['index', 'create', 'update', 'delete'],
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

    /**
     * Lists all PosAudit models.
     * @return mixed
     */
    public function actionIndex()
    {
        $model = new PosAudit();
        $model->scenario = 'searchAudit';

        $seasons = Yii::$app->user->identity->userinfo->BRANCH_C != "" ? PosSeason::find()
                    ->select(['pos_season.id as id', 'concat(pos_branch.title," - ",pos_program.title," - SEASON ",pos_season.title) as title'])
                    ->leftJoin('pos_branch_program','pos_branch_program.id = pos_season.branch_program_id')
                    ->leftJoin('pos_branch','pos_branch.id = pos_branch_program.branch_id')
                    ->leftJoin('pos_program','pos_program.id = pos_branch_program.program_id')
                    ->andWhere(['status' => 'Active'])
                    ->andWhere(['pos_branch.id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                    ->asArray()
                    ->orderBy(['title' => SORT_ASC])
                    ->all() : PosSeason::find()
                    ->select(['pos_season.id as id', 'concat(pos_branch.title," - ",pos_program.title," - SEASON ",pos_season.title) as title'])
                    ->leftJoin('pos_branch_program','pos_branch_program.id = pos_season.branch_program_id')
                    ->leftJoin('pos_branch','pos_branch.id = pos_branch_program.branch_id')
                    ->leftJoin('pos_program','pos_program.id = pos_branch_program.program_id')
                    ->andWhere(['status' => 'Active'])
                    ->asArray()
                    ->orderBy(['title' => SORT_ASC])
                    ->all();

        $seasons = ArrayHelper::map($seasons, 'id', 'title');

        return $this->render('index', [
            'model' => $model,
            'seasons' => $seasons,
        ]);
    }

    /**
     * Displays a single PosAudit model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($season, $date)
    {
        $selectedSeason = $season != 0 ? PosSeason::findOne(['id' => $season]) : null;
        $data = [];
        $cutoff = $this->check_in_cutoff($date);
        $totalBeginningCash = 0;
        $totalBeginningNonCash = 0;

        // Beginning Amount Models
        $beginningCashModel = $season != 0 ? PosBeginningAmount::findOne(['season_id' => $selectedSeason->id, 'type' => 'CASH', 'account_date' => $date]) ? PosBeginningAmount::findOne(['season_id' => $selectedSeason->id, 'type' => 'CASH', 'account_date' => $date]) : new PosBeginningAmount() : new PosBeginningAmount();
        $beginningCashModel->season_id = $season != 0 ? $selectedSeason->id : 0;
        $beginningCashModel->account_date = $date;
        $beginningCashModel->type = 'CASH';
        $beginningAmountModel['CASH'] = $beginningCashModel;

        $beginningNonCashModel = $season != 0 ? PosBeginningAmount::findOne(['season_id' => $selectedSeason->id, 'type' => 'NON-CASH', 'account_date' => $date]) ? PosBeginningAmount::findOne(['season_id' => $selectedSeason->id, 'type' => 'NON-CASH', 'account_date' => $date]) : new PosBeginningAmount() : new PosBeginningAmount();
        $beginningNonCashModel->season_id = $season != 0 ? $selectedSeason->id : 0;
        $beginningNonCashModel->account_date = $date;
        $beginningNonCashModel->type = 'NON-CASH';
        $beginningAmountModel['NON-CASH'] = $beginningNonCashModel;

        // Beginning Amounts
        $beginningCashes = $season != 0 ? PosBeginningAmount::findAll(['season_id' => $selectedSeason->id, 'type' => 'CASH', 'account_date' => $cutoff['start']]) : PosBeginningAmount::findAll(['type' => 'CASH', 'account_date' => $cutoff['start']]);

        $data['CASH']['BeginningAmount'] = 0;

        if($beginningCashes)
        {
            foreach($beginningCashes as $beginningCash)
            {
                $data['CASH']['BeginningAmount'] += $beginningCash->amount;
            }
        }

        $beginningNonCashes = $season != 0 ? PosBeginningAmount::findAll(['season_id' => $selectedSeason->id, 'type' => 'NON-CASH', 'account_date' => $cutoff['start']]) : PosBeginningAmount::findAll(['type' => 'NON-CASH', 'account_date' => $cutoff['start']]);

        $data['NON-CASH']['BeginningAmount'] = 0;

        if($beginningNonCashes)
        {
            foreach($beginningNonCashes as $beginningNonCash)
            {
                $data['NON-CASH']['BeginningAmount'] += $beginningNonCash->amount;
            }
        }

        // income since cutoff start date up to date
        $incomeSinceCutoff = $season != 0 ? PosIncomeItem::find()
            ->select([
                'sum(pos_income_item.quantity * pos_income_item.amount) as total',
                'pos_amount_type.type as amountType',
            ])
            ->leftJoin('pos_income_type','pos_income_type.id = pos_income_item.income_type_id')
            ->leftJoin('pos_income','pos_income.id = pos_income_item.income_id')
            ->leftJoin('pos_amount_type','pos_amount_type.id = pos_income_item.amount_type_id')
            ->andWhere(['pos_income_item.season_id' => $selectedSeason->id])
            ->andWhere(['pos_income.status' => 'Active'])
            ->andWhere(['BETWEEN','pos_income.invoice_date', $cutoff['start'], $date])
            ->groupBy(['pos_amount_type.id'])
            ->asArray()
            ->all() : PosIncomeItem::find()
            ->select([
                'sum(pos_income_item.quantity * pos_income_item.amount) as total',
                'pos_amount_type.type as amountType',
            ])
            ->leftJoin('pos_income_type','pos_income_type.id = pos_income_item.income_type_id')
            ->leftJoin('pos_income','pos_income.id = pos_income_item.income_id')
            ->leftJoin('pos_amount_type','pos_amount_type.id = pos_income_item.amount_type_id')
            ->andWhere(['pos_income.status' => 'Active'])
            ->andWhere(['BETWEEN','pos_income.invoice_date', $cutoff['start'], $date])
            ->groupBy(['pos_amount_type.id'])
            ->asArray()
            ->all();

        if(!empty($incomeSinceCutoff))
        {
            foreach($incomeSinceCutoff as $income)
            {
                $data[$income['amountType']]['Cutoff']['Income'] = $income;
            }
        }

        // income since cutoff start date up to yesterday
        $incomeSinceYesterday = $season != 0 ? PosIncomeItem::find()
            ->select([
                'sum(pos_income_item.quantity * pos_income_item.amount) as total',
                'pos_amount_type.type as amountType',
            ])
            ->leftJoin('pos_income_type','pos_income_type.id = pos_income_item.income_type_id')
            ->leftJoin('pos_income','pos_income.id = pos_income_item.income_id')
            ->leftJoin('pos_amount_type','pos_amount_type.id = pos_income_item.amount_type_id')
            ->andWhere(['pos_income_item.season_id' => $selectedSeason->id])
            ->andWhere(['pos_income.status' => 'Active'])
            ->andWhere(['BETWEEN','pos_income.invoice_date', $cutoff['start'], date("Y-m-d", strtotime('-1 day', strtotime($date)))])
            ->groupBy(['pos_amount_type.id'])
            ->asArray()
            ->all() : PosIncomeItem::find()
            ->select([
                'sum(pos_income_item.quantity * pos_income_item.amount) as total',
                'pos_amount_type.type as amountType',
            ])
            ->leftJoin('pos_income_type','pos_income_type.id = pos_income_item.income_type_id')
            ->leftJoin('pos_income','pos_income.id = pos_income_item.income_id')
            ->leftJoin('pos_amount_type','pos_amount_type.id = pos_income_item.amount_type_id')
            ->andWhere(['pos_income.status' => 'Active'])
            ->andWhere(['BETWEEN','pos_income.invoice_date', $cutoff['start'], date("Y-m-d", strtotime('-1 day', strtotime($date)))])
            ->groupBy(['pos_amount_type.id'])
            ->asArray()
            ->all();

        if(!empty($incomeSinceYesterday))
        {
            foreach($incomeSinceYesterday as $income)
            {
                $data[$income['amountType']]['Yesterday']['Income'] = $income;
            }
        }

        // income for that date
        $incomes = $season != 0 ? PosIncomeItem::find()
            ->select([
                'pos_income_type.title as incomeType',
                'sum(pos_income_item.quantity * pos_income_item.amount) as total',
                'pos_amount_type.type as amountType',
            ])
            ->leftJoin('pos_income_type','pos_income_type.id = pos_income_item.income_type_id')
            ->leftJoin('pos_income','pos_income.id = pos_income_item.income_id')
            ->leftJoin('pos_amount_type','pos_amount_type.id = pos_income_item.amount_type_id')
            ->andWhere(['pos_income_item.season_id' => $selectedSeason->id])
            ->andWhere(['pos_income.invoice_date' => $date])
            ->andWhere(['pos_income.status' => 'Active'])
            ->groupBy(['pos_income_type.id', 'pos_amount_type.id'])
            ->asArray()
            ->all() : PosIncomeItem::find()
            ->select([
                'pos_income_type.title as incomeType',
                'sum(pos_income_item.quantity * pos_income_item.amount) as total',
                'pos_amount_type.type as amountType',
            ])
            ->leftJoin('pos_income_type','pos_income_type.id = pos_income_item.income_type_id')
            ->leftJoin('pos_income','pos_income.id = pos_income_item.income_id')
            ->leftJoin('pos_amount_type','pos_amount_type.id = pos_income_item.amount_type_id')
            ->andWhere(['pos_income.invoice_date' => $date])
            ->andWhere(['pos_income.status' => 'Active'])
            ->groupBy(['pos_income_type.id', 'pos_amount_type.id'])
            ->asArray()
            ->all();

        if(!empty($incomes))
        {
            foreach($incomes as $income)
            {
                $data[$income['amountType']]['Today']['Income'][] = $income;
            }
        }

        // expense since cutoff start date up to yesterday
        $expenseSinceYesterday = $season != 0 ? PosExpenseItem::find()
            ->select([
                'sum(pos_expense_item.quantity * pos_expense_item.amount) as total',
                'pos_amount_type.type as amountType',
            ])
            ->leftJoin('pos_expense','pos_expense.id = pos_expense_item.expense_id')
            ->leftJoin('pos_vendor','pos_vendor.id = pos_expense.vendor_id')
            ->leftJoin('pos_amount_type','pos_amount_type.id = pos_expense_item.amount_type_id')
            ->andWhere(['pos_expense_item.season_id' => $selectedSeason->id])
            ->andWhere(['BETWEEN','pos_expense.expense_date', $cutoff['start'], date("Y-m-d", strtotime('-1 day', strtotime($date)))])
            ->groupBy(['pos_vendor.id', 'pos_amount_type.id'])
            ->asArray()
            ->all() : PosExpenseItem::find()
            ->select([
                'sum(pos_expense_item.quantity * pos_expense_item.amount) as total',
                'pos_amount_type.type as amountType',
            ])
            ->leftJoin('pos_expense','pos_expense.id = pos_expense_item.expense_id')
            ->leftJoin('pos_vendor','pos_vendor.id = pos_expense.vendor_id')
            ->leftJoin('pos_amount_type','pos_amount_type.id = pos_expense_item.amount_type_id')
            ->andWhere(['BETWEEN','pos_expense.expense_date', $cutoff['start'], date("Y-m-d", strtotime('-1 day', strtotime($date)))])
            ->groupBy(['pos_vendor.id', 'pos_amount_type.id'])
            ->asArray()
            ->all();

        if(!empty($expenseSinceCutoff))
        {
            foreach($expenseSinceCutoff as $expense)
            {
                $data[$expense['amountType']]['Yesterday']['Expenses'] = $expense;
            }
        }

        // expense since cutoff start date up to date
        $expenseSinceCutoff = $season != 0 ? PosExpenseItem::find()
            ->select([
                'sum(pos_expense_item.quantity * pos_expense_item.amount) as total',
                'pos_amount_type.type as amountType',
            ])
            ->leftJoin('pos_expense','pos_expense.id = pos_expense_item.expense_id')
            ->leftJoin('pos_vendor','pos_vendor.id = pos_expense.vendor_id')
            ->leftJoin('pos_amount_type','pos_amount_type.id = pos_expense_item.amount_type_id')
            ->andWhere(['pos_expense_item.season_id' => $selectedSeason->id])
            ->andWhere(['BETWEEN','pos_expense.expense_date', $cutoff['start'], $date])
            ->groupBy(['pos_vendor.id', 'pos_amount_type.id'])
            ->asArray()
            ->all() : PosExpenseItem::find()
            ->select([
                'sum(pos_expense_item.quantity * pos_expense_item.amount) as total',
                'pos_amount_type.type as amountType',
            ])
            ->leftJoin('pos_expense','pos_expense.id = pos_expense_item.expense_id')
            ->leftJoin('pos_vendor','pos_vendor.id = pos_expense.vendor_id')
            ->leftJoin('pos_amount_type','pos_amount_type.id = pos_expense_item.amount_type_id')
            ->andWhere(['BETWEEN','pos_expense.expense_date', $cutoff['start'], $date])
            ->groupBy(['pos_vendor.id', 'pos_amount_type.id'])
            ->asArray()
            ->all();

        if(!empty($expenseSinceCutoff))
        {
            foreach($expenseSinceCutoff as $expense)
            {
                $data[$expense['amountType']]['Cutoff']['Expenses'] = $expense;
            }
        }

        // expense for that date
        $expenses = $season != 0 ? PosExpenseItem::find()
            ->select([
                'pos_vendor.title as vendor',
                'sum(pos_expense_item.quantity * pos_expense_item.amount) as total',
                'pos_amount_type.type as amountType',
            ])
            ->leftJoin('pos_expense','pos_expense.id = pos_expense_item.expense_id')
            ->leftJoin('pos_vendor','pos_vendor.id = pos_expense.vendor_id')
            ->leftJoin('pos_amount_type','pos_amount_type.id = pos_expense_item.amount_type_id')
            ->andWhere(['pos_expense_item.season_id' => $selectedSeason->id])
            ->andWhere(['pos_expense.expense_date' => $date])
            ->groupBy(['pos_vendor.id', 'pos_amount_type.id'])
            ->asArray()
            ->all() : PosExpenseItem::find()
            ->select([
                'pos_vendor.title as vendor',
                'sum(pos_expense_item.quantity * pos_expense_item.amount) as total',
                'pos_amount_type.type as amountType',
            ])
            ->leftJoin('pos_expense','pos_expense.id = pos_expense_item.expense_id')
            ->leftJoin('pos_vendor','pos_vendor.id = pos_expense.vendor_id')
            ->leftJoin('pos_amount_type','pos_amount_type.id = pos_expense_item.amount_type_id')
            ->andWhere(['pos_expense.expense_date' => $date])
            ->groupBy(['pos_vendor.id', 'pos_amount_type.id'])
            ->asArray()
            ->all();

        if(!empty($expenses))
        {
            foreach($expenses as $expense)
            {
                $data[$expense['amountType']]['Today']['Expenses'][] = $expense;
            }
        }

        $models = [];
        $denominations = PosDenomination::find()->all();
        if($season != 0)
        {
            if($denominations)
            {
                foreach($denominations as $denomination)
                {
                    $audit = $season != 0 ? PosAudit::findOne(['season_id' => $selectedSeason->id, 'audit_date' => $date, 'denomination_id' => $denomination->id]) ? PosAudit::findOne(['season_id' => $selectedSeason->id, 'audit_date' => $date, 'denomination_id' => $denomination->id]) : new PosAudit() : new PosAudit();
                    $audit->season_id = $selectedSeason->id;
                    $audit->denomination_id = $denomination->id;
                    $audit->audit_date = $date;
                    $models[] = $audit;
                }
            }
        }

        if($cutoff['start'] == $date)
        {
            if(Model::loadMultiple($models, Yii::$app->request->post()) && Model::loadMultiple($beginningAmountModel, Yii::$app->request->post()))
            {
                if($models)
                {
                    foreach($models as $model)
                    {
                        $model->save(false);
                    }
                }

                if($beginningAmountModel)
                {
                    foreach($beginningAmountModel as $model)
                    {
                        $model->save(false);
                    }
                }

                \Yii::$app->getSession()->setFlash('success', 'Record Saved');
                return $this->redirect(['index']);
            }
        }else{
            if(Model::loadMultiple($models, Yii::$app->request->post()))
            {
                if($models)
                {
                    foreach($models as $model)
                    {
                        $model->save(false);
                    }
                }
                \Yii::$app->getSession()->setFlash('success', 'Record Saved');
                return $this->redirect(['index']);
            }
        }

        return $this->renderAjax('_form', [
            'models' => $models,
            'denominations' => $denominations,
            'selectedSeason' => $selectedSeason,
            'season' => $season,
            'cutoff' => $cutoff,
            'date' => $date,
            'data' => $data,
            'beginningAmountModel' => $beginningAmountModel,
            'totalBeginningCash' => $totalBeginningCash,
            'totalBeginningNonCash' => $totalBeginningNonCash,
        ]);
    }

    /**
     * Creates a new PosAudit model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new PosAudit();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {

            \Yii::$app->getSession()->setFlash('success', 'Record Saved');
            return $this->redirect(['index']);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing PosAudit model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            
            \Yii::$app->getSession()->setFlash('success', 'Record Saved');
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing PosAudit model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        \Yii::$app->getSession()->setFlash('success', 'Record Saved');
            return $this->redirect(['index']);
    }

    /**
     * Finds the PosAudit model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return PosAudit the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PosAudit::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
