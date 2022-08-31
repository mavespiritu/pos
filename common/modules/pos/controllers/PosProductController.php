<?php

namespace common\modules\pos\controllers;

use Yii;
use common\modules\pos\models\PosBranchProgram;
use common\modules\pos\models\PosSeason;
use common\modules\pos\models\PosEnrolmentType;
use common\modules\pos\models\PosIncomeType;
use common\modules\pos\models\PosProductType;
use common\modules\pos\models\PosProduct;
use common\modules\pos\models\PosProductItem;
use common\modules\pos\models\PosItem;
use common\modules\pos\models\PosProductSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;

/**
 * PosProductController implements the CRUD actions for PosProduct model.
 */
class PosProductController extends Controller
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
                        'actions' => ['index', 'create', 'update', 'delete', 'view'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all PosProduct models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PosProductSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $seasons = PosSeason::find()
                    ->select(['pos_season.id as id', 'concat(pos_branch.title," - ",pos_program.title," - SEASON ",pos_season.title) as title'])
                    ->leftJoin('pos_branch_program','pos_branch_program.id = pos_season.branch_program_id')
                    ->leftJoin('pos_branch','pos_branch.id = pos_branch_program.branch_id')
                    ->leftJoin('pos_program','pos_program.id = pos_branch_program.program_id')
                    ->andWhere(['status' => 'Active'])
                    ->asArray()
                    ->orderBy(['title' => SORT_ASC])
                    ->all();

        $seasons = ArrayHelper::map($seasons, 'id', 'title');

        $productTypes = PosProductType::find()->all();
        $productTypes = ArrayHelper::map($productTypes, 'id', 'title');

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'seasons' => $seasons,
            'productTypes' => $productTypes,
        ]);
    }

    /**
     * Displays a single PosProduct model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        $items = PosItem::find()->all();
        $items = ArrayHelper::map($items, 'id', 'title');

        $productItemModel = new PosProductItem();
        $productItemModel->product_id = $model->id;

        $productItems = $model->productItems;

        if ($productItemModel->load(Yii::$app->request->post()) && $productItemModel->save()) {

            \Yii::$app->getSession()->setFlash('success', 'Record Saved');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('view', [
            'model' => $model,
            'productItems' => $productItems,
            'productItemModel' => $productItemModel,
            'items' => $items,
        ]);
    }

    public function actionUpdateProductItem($id)
    {
        $productItemModel = PosProductItem::findOne(['id' => $id]);
        $model = $productItemModel->product;

        $productItems = $model->productItems;

        $items = PosItem::find()->all();
        $items = ArrayHelper::map($items, 'id', 'title');

        if ($productItemModel->load(Yii::$app->request->post()) && $productItemModel->save()) {

            \Yii::$app->getSession()->setFlash('success', 'Record Saved');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('view', [
            'model' => $model,
            'productItems' => $productItems,
            'productItemModel' => $productItemModel,
            'items' => $items,
        ]);
    }

    /**
     * Creates a new PosProduct model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new PosProduct();

        $seasons = PosSeason::find()
        ->select(['pos_season.id as id', 'concat(pos_branch.title," - ",pos_program.title," - SEASON ",pos_season.title) as title'])
        ->leftJoin('pos_branch_program','pos_branch_program.id = pos_season.branch_program_id')
        ->leftJoin('pos_branch','pos_branch.id = pos_branch_program.branch_id')
        ->leftJoin('pos_program','pos_program.id = pos_branch_program.program_id')
        ->andWhere(['status' => 'Active'])
        ->asArray()
        ->orderBy(['title' => SORT_ASC])
        ->all();

        $seasons = ArrayHelper::map($seasons, 'id', 'title');

        $incomeTypes = PosIncomeType::find()->all();
        $incomeTypes = ArrayHelper::map($incomeTypes, 'id', 'title');

        $productTypes = PosProductType::find()->all();
        $productTypes = ArrayHelper::map($productTypes, 'id', 'title');

        if ($model->load(Yii::$app->request->post()) && $model->save()) {

            \Yii::$app->getSession()->setFlash('success', 'Record Saved');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
            'seasons' => $seasons,
            'incomeTypes' => $incomeTypes,
            'productTypes' => $productTypes,
        ]);
    }

    /**
     * Updates an existing PosProduct model.
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
        ->andWhere(['status' => 'Active'])
        ->asArray()
        ->orderBy(['title' => SORT_ASC])
        ->all();

        $seasons = ArrayHelper::map($seasons, 'id', 'title');

        $incomeTypes = PosIncomeType::find()->all();
        $incomeTypes = ArrayHelper::map($incomeTypes, 'id', 'title');

        $productTypes = PosProductType::find()->all();
        $productTypes = ArrayHelper::map($productTypes, 'id', 'title');

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            
            \Yii::$app->getSession()->setFlash('success', 'Record Saved');
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
            'seasons' => $seasons,
            'incomeTypes' => $incomeTypes,
            'productTypes' => $productTypes,
        ]);
    }

    /**
     * Deletes an existing PosProduct model.
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
     * Deletes an existing PosProduct model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDeleteProductItem($id)
    {
        $productItemModel = PosProductItem::findOne(['id' => $id]);
        $model = $productItemModel->product;

        $productItemModel->delete();

        \Yii::$app->getSession()->setFlash('success', 'Record Saved');
        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Finds the PosProduct model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return PosProduct the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PosProduct::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
