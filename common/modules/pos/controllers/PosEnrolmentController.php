<?php

namespace common\modules\pos\controllers;

use Yii;
use common\modules\pos\models\PosEnrolment;
use common\modules\pos\models\PosDiscount;
use common\modules\pos\models\PosDropout;
use common\modules\pos\models\PosSeason;
use common\modules\pos\models\PosEnrolmentType;
use common\modules\pos\models\PosCustomer;
use common\modules\pos\models\PosDiscountType;
use common\modules\pos\models\PosProduct;
use common\modules\pos\models\PosEnrolmentSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;

/**
 * PosEnrolmentController implements the CRUD actions for PosEnrolment model.
 */
class PosEnrolmentController extends Controller
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
                    'drop' => ['POST'],
                    'undo-drop' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index'],
                'rules' => [
                    [
                        'actions' => ['index', 'create', 'update', 'delete', 'drop', 'undo-drop'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionCustomerList($q = null, $id = null) {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $out = ['results' => ['id' => '', 'name' => '']];
        if (!is_null($q)) {
            if(Yii::$app->user->identity->userinfo->BRANCH_C != "")
            {
                $names = PosCustomer::find()
                    ->select(['pos_customer.id', 'concat(first_name," ",middle_name," ",last_name," ",ext_name) as name'])
                    ->leftJoin('pos_school', 'pos_school.id = pos_customer.school_id')
                    ->andWhere(['pos_school.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                    ->andWhere(['like','concat(first_name," ",middle_name," ",last_name," ",ext_name)', $q])
                    ->andWhere(['like','concat(first_name," ",last_name," ",ext_name)', $q])
                    ->limit(20)
                    ->asArray()
                    ->all();
            }else{
                $names = PosCustomer::find()
                ->select(['pos_customer.id', 'concat(first_name," ",middle_name," ",last_name," ",ext_name) as name'])
                ->leftJoin('pos_school', 'pos_school.id = pos_customer.school_id')
                ->andWhere(['like','concat(first_name," ",middle_name," ",last_name," ",ext_name)', $q])
                ->andWhere(['like','concat(first_name," ",last_name," ",ext_name)', $q])
                ->limit(20)
                ->asArray()
                ->all();
            }
            

            $out['results'] = array_values($names);
        }
        elseif ($id > 0) {
            $out['results'] = ['id' => $id, 'name' => PosCustomer::find($id)->first_name.' '.PosCustomer::find($id)->middle_name.' '.PosCustomer::find($id)->last_name.' '.PosCustomer::find($id)->ext_name];
        }
        return $out;
    }

    public function actionProductList($season)
    {
        $products = PosProduct::find()
                    ->select(['id','concat(title," - ",description," - ",amount) as title'])
                    ->andWhere(['season_id' => $season])
                    ->andWhere(['product_type_id' => 1])
                    ->all();
        $arr = [];
        $arr[] = ['id'=>'','text'=>''];

        foreach($products as $product){
            $arr[] = ['id' => $product['id'] , 'text' => $product['title']];
        }
        \Yii::$app->response->format = 'json';
        return $arr;
    }

    public function actionShowSeason($id)
    {
        return $this->renderAjax('season', [
            'model' => PosSeason::findOne(['id' => $id]),
        ]);
    }

    public function actionShowProduct($id)
    {
        return $this->renderAjax('product', [
            'model' => PosProduct::findOne(['id' => $id]),
        ]);
    }

    public function actionShowCustomer($id)
    {
        return $this->renderAjax('customer', [
            'model' => PosCustomer::findOne(['id' => $id]),
        ]);
    }

    /**
     * Lists all PosEnrolment models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PosEnrolmentSearch();
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

        $enrolmentTypes = PosEnrolmentType::find()->all();
        $enrolmentTypes = ArrayHelper::map($enrolmentTypes, 'id', 'title');

        $params = Yii::$app->request->getQueryParam('PosEnrolmentSearch');
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
            'enrolmentTypes' => $enrolmentTypes,
            'customers' => $customers,
            'products' => $products,
        ]);
    }

    /**
     * Displays a single PosEnrolment model.
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

    public function actionDrop($id)
    {
        $enrolmentModel = $this->findModel($id);
        $model = $enrolmentModel->dropout ? $enrolmentModel->dropout : new PosDropout();
        $model->enrolment_id = $enrolmentModel->id;
        $model->processed_by = Yii::$app->user->id;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {

            \Yii::$app->getSession()->setFlash('success', 'Record Saved');
            return $this->redirect(['view', 'id' => $enrolmentModel->id]);
        }

        return $this->render('drop', [
            'model' => $model,
            'enrolmentModel' => $enrolmentModel,
        ]);
    }

    public function actionUndoDrop($id)
    {
        $enrolmentModel = $this->findModel($id);
        $model = $enrolmentModel->dropout;

        $model->delete();
        \Yii::$app->getSession()->setFlash('success', 'Record Saved');
        return $this->redirect(['view', 'id' => $enrolmentModel->id]);
    }

    /**
     * Creates a new PosEnrolment model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new PosEnrolment();
        $discountModel = new PosDiscount();

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

        $enrolmentTypes = PosEnrolmentType::find()->all();
        $enrolmentTypes = ArrayHelper::map($enrolmentTypes, 'id', 'title');

        $discountTypes = PosDiscountType::find()->all();
        $discountTypes = ArrayHelper::map($discountTypes, 'id', 'title');

        $products = [];

        if ($model->load(Yii::$app->request->post()) && $model->save() && $discountModel->load(Yii::$app->request->post())) {

            $discountModel->enrolment_id = $model->id;
            $discountModel->save();

            \Yii::$app->getSession()->setFlash('success', 'Record Saved');
            return $this->redirect(['index']);
        }

        return $this->render('create', [
            'model' => $model,
            'discountModel' => $discountModel,
            'seasons' => $seasons,
            'products' => $products,
            'enrolmentTypes' => $enrolmentTypes,
            'discountTypes' => $discountTypes,
        ]);
    }

    /**
     * Updates an existing PosEnrolment model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $discountModel = $model->discount;

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

        $enrolmentTypes = PosEnrolmentType::find()->all();
        $enrolmentTypes = ArrayHelper::map($enrolmentTypes, 'id', 'title');

        $discountTypes = PosDiscountType::find()->all();
        $discountTypes = ArrayHelper::map($discountTypes, 'id', 'title');

        $products = PosProduct::find()
                    ->select(['id','concat(title," - ",description," - ",amount) as title'])
                    ->andWhere(['season_id' => $model->season_id])
                    ->andWhere(['product_type_id' => 1])
                    ->all();

        $products = ArrayHelper::map($products, 'id', 'title');

        if ($model->load(Yii::$app->request->post()) && $discountModel->load(Yii::$app->request->post())) {

            if($model->save())
            {
                $discountModel->save();

                \Yii::$app->getSession()->setFlash('success', 'Record Saved');
                return $this->redirect(['index']);
            }
        }

        return $this->render('update', [
            'model' => $model,
            'discountModel' => $discountModel,
            'seasons' => $seasons,
            'products' => $products,
            'enrolmentTypes' => $enrolmentTypes,
            'discountTypes' => $discountTypes,
        ]);
    }

    /**
     * Deletes an existing PosEnrolment model.
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
     * Finds the PosEnrolment model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return PosEnrolment the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PosEnrolment::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
