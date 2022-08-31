<?php

namespace common\modules\pos\controllers;

use Yii;
use common\modules\pos\models\Model;
use common\modules\pos\models\PosIncome;
use common\modules\pos\models\PosAccount;
use common\modules\pos\models\PosEnrolment;
use common\modules\pos\models\PosAmountType;
use common\modules\pos\models\PosIncomeItem;
use common\modules\pos\models\PosSeason;
use common\modules\pos\models\PosProduct;
use common\modules\pos\models\PosCustomer;
use common\modules\pos\models\PosOfficialReceipt;
use common\modules\pos\models\PosBacktrack;
use common\modules\pos\models\PosIncomeSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;

/**
 * PosIncomeController implements the CRUD actions for PosIncome model.
 */
class PosIncomeController extends Controller
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
                    'void' => ['POST'],
                    'activate' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index'],
                'rules' => [
                    [
                        'actions' => ['index', 'create', 'update', 'delete', 'void', 'activate'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionCustomerList($season)
    {
        $names = PosEnrolment::find()
                    ->select(['pos_customer.id', 'concat(pos_customer.first_name," ",pos_customer.middle_name," ",pos_customer.last_name," ",pos_customer.ext_name) as title'])
                    ->leftJoin('pos_customer','pos_customer.id = pos_enrolment.customer_id')
                    ->andWhere(['pos_enrolment.season_id' => $season])
                    ->asArray()
                    ->all();
        $arr = [];
        $arr[] = ['id'=>'','text'=>''];

        foreach($names as $name){
            $arr[] = ['id' => $name['id'] , 'text' => $name['title']];
        }
        \Yii::$app->response->format = 'json';
        return $arr;
    }

    public function actionProductList($season)
    {
        $names = PosProduct::find()
                    ->select(['pos_product.id', 'concat(pos_product.title," - ",pos_product.amount) as title'])
                    ->andWhere(['pos_product.season_id' => $season])
                    ->asArray()
                    ->all();
        $arr = [];
        $arr[] = ['id'=>'','text'=>''];

        foreach($names as $name){
            $arr[] = ['id' => $name['id'] , 'text' => $name['title']];
        }
        \Yii::$app->response->format = 'json';
        return $arr;
    }

    function generateReceipt($season)
    {
        $lastReceipt = PosIncome::find()->where(['season_id' => $season])->orderBy(['official_receipt_id' => SORT_DESC])->one();

        $receiptNo = '';
        
        if($lastReceipt)
        {
            $receiptNo = intval($lastReceipt->official_receipt_id);

            $officialReceipt = PosOfficialReceipt::find()
                            ->andWhere(['season_id' => $season])
                            ->andWhere(['BETWEEN', 'CAST(start_number as SIGNED)', 'CAST(last_number as SIGNED)', $receiptNo])
                            ->orderBy(['id' => SORT_ASC])
                            ->one();

            if($officialReceipt)
            {
                $no_of_places = strlen($officialReceipt->start_number);
                $receiptNo = str_pad($receiptNo + 1, $no_of_places, 0, STR_PAD_LEFT);
            }else{
                $receiptNo = 'No Official Receipt';
            }
        }else{
            $officialReceipt = PosOfficialReceipt::find()
                            ->andWhere(['season_id' => $season])
                            ->orderBy(['id' => SORT_ASC])
                            ->one();

            if($officialReceipt)
            {
                $receiptNo = $officialReceipt->start_number;
            }else{
                $receiptNo = 'No Official Receipt';
            }
        }

        return $receiptNo;
    }

    function actionGenerateReceipt($season)
    {
        $lastReceipt = PosIncome::find()->where(['season_id' => $season])->orderBy(['official_receipt_id' => SORT_DESC])->one();

        $receiptNo = '';

        if($lastReceipt)
        {
            $receiptNo = intval($lastReceipt->official_receipt_id);

            $officialReceipt = PosOfficialReceipt::find()
                            ->andWhere(['season_id' => $season])
                            ->andWhere(['BETWEEN', 'CAST(start_number as SIGNED)', 'CAST(last_number as SIGNED)', $receiptNo])
                            ->orderBy(['id' => SORT_ASC])
                            ->one();

            if($officialReceipt)
            {
                $no_of_places = strlen($officialReceipt->start_number);
                $receiptNo = str_pad($receiptNo + 1, $no_of_places, 0, STR_PAD_LEFT);
            }else{
                $receiptNo = 'No Official Receipt';
            }
        }else{
            $officialReceipt = PosOfficialReceipt::find()
                            ->andWhere(['season_id' => $season])
                            ->orderBy(['id' => SORT_ASC])
                            ->one();

            if($officialReceipt)
            {
                $receiptNo = $officialReceipt->start_number;
            }else{
                $receiptNo = 'No Official Receipt';
            }
        }

        return $receiptNo;
    }

    /**
     * Lists all PosIncome models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PosIncomeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

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

        $params = Yii::$app->request->getQueryParam('PosIncomeSearch');
        $customers = !empty($params) ? $params['season_id'] != "" ? PosEnrolment::find()
                    ->select(['pos_customer.id', 'concat(pos_customer.first_name," ",pos_customer.middle_name," ",pos_customer.last_name," ",pos_customer.ext_name) as title'])
                    ->leftJoin('pos_customer','pos_customer.id = pos_enrolment.customer_id')
                    ->andWhere(['pos_enrolment.season_id' => $params['season_id']])
                    ->asArray()
                    ->all() : [] : [];

        $products = !empty($params) ? $params['season_id'] != "" ? PosProduct::find()
                    ->select(['pos_product.id', 'concat(pos_product.title," - ",pos_product.amount) as title'])
                    ->andWhere(['pos_product.season_id' => $params['season_id']])
                    ->asArray()
                    ->all() : [] : [];

        $customers = !empty($params) ? $params['season_id'] != "" ? ArrayHelper::map($customers, 'id', 'title') : [] : [];
        $products = !empty($params) ? $params['season_id'] != "" ? ArrayHelper::map($products, 'id', 'title') : [] : [];

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'seasons' => $seasons,
            'customers' => $customers,
            'products' => $products,
        ]);
    }

    /**
     * Displays a single PosIncome model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new PosIncome model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new PosIncome();
        $incomeItemModel = new PosIncomeItem();

        $seasons = PosSeason::find()
                    ->select(['pos_season.id as id', 'concat(pos_branch.title," - ",pos_program.title," - SEASON ",pos_season.title) as title'])
                    ->leftJoin('pos_branch_program','pos_branch_program.id = pos_season.branch_program_id')
                    ->leftJoin('pos_branch','pos_branch.id = pos_branch_program.branch_id')
                    ->leftJoin('pos_program','pos_program.id = pos_branch_program.program_id')
                    ->andWhere(['status' => 'Active']);

        $backtrack = Yii::$app->user->identity->userinfo->BRANCH_C != '' ? PosBacktrack::findOne(['branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C, 'field' => 'Income']) ? PosBacktrack::findOne(['branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C, 'field' => 'Income']) : null : null;

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

        $customers = [];
        $products = [];

        if ($model->load(Yii::$app->request->post()) && $incomeItemModel->load(Yii::$app->request->post())) {
            $postData = Yii::$app->request->post();

            if($this->generateReceipt($model->season_id) != 'No Official Receipt')
            {
                $model->invoice_date = is_null($backtrack) ? date("Y-m-d") : $postData['PosIncome']['invoice_date'];
                $model->amount_type_id = $incomeItemModel->amount_type_id;
                $model->official_receipt_id = $this->generateReceipt($model->season_id);
                $model->status = 'Active';
                if($model->save())
                {
                    $incomeItemModel->income_id = $model->id;
                    $incomeItemModel->season_id = $model->season_id;
                    $incomeItemModel->customer_id = $model->customer_id;
                    $incomeItemModel->account_id = $model->account_id;
                    $incomeItemModel->income_type_id = PosProduct::findOne($incomeItemModel->product_id)->income_type_id;
                    $incomeItemModel->quantity = 1;
                    $incomeItemModel->save();

                    \Yii::$app->getSession()->setFlash('success', 'Record Saved');
                    return $this->redirect(['index']);
                }
            }else{
                \Yii::$app->getSession()->setFlash('danger', 'No available official receipt. Request new set of official receipts to the management');
                
                return $this->render('create', [
                    'model' => $model,
                    'incomeItemModel' => $incomeItemModel,
                    'backtrack' => $backtrack,
                    'seasons' => $seasons,
                    'products' => $products,
                    'customers' => $customers,
                    'amountTypes' => $amountTypes,
                    'accounts' => $accounts,
                ]);
            }
        }

        return $this->render('create', [
            'model' => $model,
            'incomeItemModel' => $incomeItemModel,
            'backtrack' => $backtrack,
            'seasons' => $seasons,
            'products' => $products,
            'customers' => $customers,
            'amountTypes' => $amountTypes,
            'accounts' => $accounts,
        ]);
    }

    /**
     * Updates an existing PosIncome model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $incomeItemModel = $model->incomeItem;

        $seasons = PosSeason::find()
                    ->select(['pos_season.id as id', 'concat(pos_branch.title," - ",pos_program.title," - SEASON ",pos_season.title) as title'])
                    ->leftJoin('pos_branch_program','pos_branch_program.id = pos_season.branch_program_id')
                    ->leftJoin('pos_branch','pos_branch.id = pos_branch_program.branch_id')
                    ->leftJoin('pos_program','pos_program.id = pos_branch_program.program_id')
                    ->andWhere(['status' => 'Active']);

        $backtrack = Yii::$app->user->identity->userinfo->BRANCH_C != '' ? PosBacktrack::findOne(['branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C, 'field' => 'Income']) ? PosBacktrack::findOne(['branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C, 'field' => 'Income']) : null : null;

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

        $customers = PosEnrolment::find()
                    ->select(['pos_customer.id', 'concat(pos_customer.first_name," ",pos_customer.middle_name," ",pos_customer.last_name," ",pos_customer.ext_name) as title'])
                    ->leftJoin('pos_customer','pos_customer.id = pos_enrolment.customer_id')
                    ->andWhere(['pos_enrolment.season_id' => $model->season_id])
                    ->asArray()
                    ->all();

        $customers = ArrayHelper::map($customers, 'id', 'title');

        $products = PosProduct::find()
                    ->select(['pos_product.id', 'concat(pos_product.title," - ",pos_product.amount) as title'])
                    ->andWhere(['pos_product.season_id' => $model->season_id])
                    ->asArray()
                    ->all();

        $products = ArrayHelper::map($products, 'id', 'title');

        if ($model->load(Yii::$app->request->post()) && $incomeItemModel->load(Yii::$app->request->post())) {
            $postData = Yii::$app->request->post();

            if(!is_null($backtrack))
            {
                $model->invoice_date = $postData['PosIncome']['invoice_date'];
            }

            $model->amount_type_id = $incomeItemModel->amount_type_id;
            if($model->save())
            {
                $incomeItemModel->income_id = $model->id;
                $incomeItemModel->season_id = $model->season_id;
                $incomeItemModel->customer_id = $model->customer_id;
                $incomeItemModel->account_id = $model->account_id;
                $incomeItemModel->income_type_id = PosProduct::findOne($incomeItemModel->product_id)->income_type_id;
                $incomeItemModel->save();

                \Yii::$app->getSession()->setFlash('success', 'Record Saved');
                return $this->redirect(['index']);
            }
        }

        return $this->render('update', [
            'model' => $model,
            'incomeItemModel' => $incomeItemModel,
            'backtrack' => $backtrack,
            'seasons' => $seasons,
            'products' => $products,
            'customers' => $customers,
            'amountTypes' => $amountTypes,
            'accounts' => $accounts,
        ]);
    }

    /**
     * Deletes an existing PosIncome model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionVoid($id)
    {
        $model = $this->findModel($id);
        $model->status = 'Void';
        $model->save();

        \Yii::$app->getSession()->setFlash('success', 'Record Saved');
            return $this->redirect(['index']);
    }

    public function actionActivate($id)
    {
        $model = $this->findModel($id);
        $model->status = 'Active';
        $model->save();

        \Yii::$app->getSession()->setFlash('success', 'Record Saved');
            return $this->redirect(['index']);
    }

    /**
     * Finds the PosIncome model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return PosIncome the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PosIncome::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
