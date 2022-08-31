<?php

namespace common\modules\pos\controllers;

use Yii;
use common\modules\pos\models\PosExpense;
use common\modules\pos\models\PosExpenseType;
use common\modules\pos\models\PosAccount;
use common\modules\pos\models\PosAmountType;
use common\modules\pos\models\PosExpenseItem;
use common\modules\pos\models\PosSeason;
use common\modules\pos\models\PosVendor;
use common\modules\pos\models\PosBacktrack;
use common\modules\pos\models\PosExpenseSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;

/**
 * PosExpenseController implements the CRUD actions for PosExpense model.
 */
class PosExpenseController extends Controller
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

    /**
     * Lists all PosExpense models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PosExpenseSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $seasons = PosSeason::find()
                    ->select(['pos_season.id as id', 'concat(pos_branch.title," - ",pos_program.title," - SEASON ",pos_season.title) as title'])
                    ->leftJoin('pos_branch_program','pos_branch_program.id = pos_season.branch_program_id')
                    ->leftJoin('pos_branch','pos_branch.id = pos_branch_program.branch_id')
                    ->leftJoin('pos_program','pos_program.id = pos_branch_program.program_id')
                    ->andWhere(['status' => 'Active']);
        $seasons = Yii::$app->user->identity->userinfo->BRANCH_C != "" ? $seasons->andWhere((['pos_branch.id' => Yii::$app->user->identity->userinfo->BRANCH_C])) : $seasons;
        $seasons = $seasons
                    ->asArray()
                    ->orderBy(['title' => SORT_ASC])
                    ->all();

        $seasons = ArrayHelper::map($seasons, 'id', 'title');

        $amountTypes = PosAmountType::find()->all();
        $amountTypes = ArrayHelper::map($amountTypes, 'id', 'title');

        $accounts = PosAccount::find()->all();
        $accounts = ArrayHelper::map($accounts, 'id', 'title');

        $vendors = Posvendor::find()->all();
        $vendors = ArrayHelper::map($vendors, 'id', 'title');

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'seasons' => $seasons,
            'amountTypes' => $amountTypes,
            'accounts' => $accounts,
            'vendors' => $vendors,
        ]);
    }

    /**
     * Displays a single PosExpense model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        $expenseTypes = PosExpenseType::find()->all();
        $expenseTypes = ArrayHelper::map($expenseTypes, 'id', 'title');

        $expenseItemModel = new PosExpenseItem();
        $expenseItemModel->expense_id = $model->id;
        $expenseItemModel->season_id = $model->season_id;
        $expenseItemModel->amount_type_id = $model->amount_type_id;

        $expenseItems = $model->expenseItems;

        if ($expenseItemModel->load(Yii::$app->request->post()) && $expenseItemModel->save()) {

            \Yii::$app->getSession()->setFlash('success', 'Record Saved');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('view', [
            'model' => $model,
            'expenseTypes' => $expenseTypes,
            'expenseItemModel' => $expenseItemModel,
            'expenseItems' => $expenseItems,
        ]);
    }

    public function actionUpdateExpenseItem($id)
    {
        $expenseItemModel = PosExpenseItem::findOne(['id' => $id]);
        $model = $expenseItemModel->expense;

        $expenseTypes = PosExpenseType::find()->all();
        $expenseTypes = ArrayHelper::map($expenseTypes, 'id', 'title');

        $expenseItems = $model->expenseItems;

        if ($expenseItemModel->load(Yii::$app->request->post()) && $expenseItemModel->save()) {

            \Yii::$app->getSession()->setFlash('success', 'Record Saved');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('view', [
            'model' => $model,
            'expenseTypes' => $expenseTypes,
            'expenseItemModel' => $expenseItemModel,
            'expenseItems' => $expenseItems,
        ]);
    }

    /**
     * Creates a new PosExpense model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new PosExpense();

        $seasons = PosSeason::find()
                    ->select(['pos_season.id as id', 'concat(pos_branch.title," - ",pos_program.title," - SEASON ",pos_season.title) as title'])
                    ->leftJoin('pos_branch_program','pos_branch_program.id = pos_season.branch_program_id')
                    ->leftJoin('pos_branch','pos_branch.id = pos_branch_program.branch_id')
                    ->leftJoin('pos_program','pos_program.id = pos_branch_program.program_id')
                    ->andWhere(['status' => 'Active']);

        $backtrack = Yii::$app->user->identity->userinfo->BRANCH_C != '' ? PosBacktrack::findOne(['branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C, 'field' => 'Expense']) ? PosBacktrack::findOne(['branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C, 'field' => 'Expense']) : null : null;

        $seasons = Yii::$app->user->identity->userinfo->BRANCH_C != "" ? $seasons->andWhere((['pos_branch.id' => Yii::$app->user->identity->userinfo->BRANCH_C])) : $seasons;
        $seasons = $seasons
                    ->asArray()
                    ->orderBy(['title' => SORT_ASC])
                    ->all();

        $seasons = ArrayHelper::map($seasons, 'id', 'title');

        $amountTypes = PosAmountType::find()->all();
        $amountTypes = ArrayHelper::map($amountTypes, 'id', 'title');

        $accounts = PosAccount::find()->all();
        $accounts = ArrayHelper::map($accounts, 'id', 'title');

        $vendors = Posvendor::find()->all();
        $vendors = ArrayHelper::map($vendors, 'id', 'title');

        $model->status = 'Active';

        if ($model->load(Yii::$app->request->post())) {
            $postData = Yii::$app->request->post();

            $model->expense_date = is_null($backtrack) ? date("Y-m-d") : $postData['PosExpense']['expense_date'];
            $model->save();

            \Yii::$app->getSession()->setFlash('success', 'Record Saved. Now you can add the particulars of the expense');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
            'seasons' => $seasons,
            'backtrack' => $backtrack,
            'accounts' => $accounts,
            'vendors' => $vendors,
            'amountTypes' => $amountTypes,
        ]);
    }

    /**
     * Updates an existing PosExpense model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $seasons = PosSeason::find()
                    ->select(['pos_season.id as id', 'concat(pos_branch.title," - ",pos_program.title," - SEASON ",pos_season.title) as title'])
                    ->leftJoin('pos_branch_program','pos_branch_program.id = pos_season.branch_program_id')
                    ->leftJoin('pos_branch','pos_branch.id = pos_branch_program.branch_id')
                    ->leftJoin('pos_program','pos_program.id = pos_branch_program.program_id')
                    ->andWhere(['status' => 'Active']);

        $backtrack = Yii::$app->user->identity->userinfo->BRANCH_C != '' ? PosBacktrack::findOne(['branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C, 'field' => 'Expense']) ? PosBacktrack::findOne(['branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C, 'field' => 'Expense']) : null : null;

        $seasons = Yii::$app->user->identity->userinfo->BRANCH_C != "" ? $seasons->andWhere((['pos_branch.id' => Yii::$app->user->identity->userinfo->BRANCH_C])) : $seasons;
        $seasons = $seasons
                    ->asArray()
                    ->orderBy(['title' => SORT_ASC])
                    ->all();

        $seasons = ArrayHelper::map($seasons, 'id', 'title');

        $amountTypes = PosAmountType::find()->all();
        $amountTypes = ArrayHelper::map($amountTypes, 'id', 'title');

        $accounts = PosAccount::find()->all();
        $accounts = ArrayHelper::map($accounts, 'id', 'title');

        $vendors = Posvendor::find()->all();
        $vendors = ArrayHelper::map($vendors, 'id', 'title');

        if ($model->load(Yii::$app->request->post())) {

            if($model->save())
            {
                if($model->expenseItems)
                {
                    foreach($model->expenseItems as $item)
                    {
                        $item->season_id = $model->season_id;
                        $item->amount_type_id = $model->amount_type_id;
                        $item->save(false);
                    }
                }
            }

            \Yii::$app->getSession()->setFlash('success', 'Record Saved. Now you can add the particulars of the expense');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'seasons' => $seasons,
            'backtrack' => $backtrack,
            'accounts' => $accounts,
            'vendors' => $vendors,
            'amountTypes' => $amountTypes,
        ]);
    }

    /**
     * Deletes an existing PosExpense model.
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

    public function actionDeleteExpenseItem($id)
    {
        $expenseItemModel = PosExpenseItem::findOne(['id' => $id]);
        $model = $expenseItemModel->expense;

        $expenseItemModel->delete();

        \Yii::$app->getSession()->setFlash('success', 'Record Saved');
        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Finds the PosExpense model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return PosExpense the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PosExpense::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
