<?php

namespace common\modules\accounting\controllers;

use Yii;
use common\modules\accounting\models\IncomeType;
use common\modules\accounting\models\IncomeCode;
use common\modules\accounting\models\IncomeCodeSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;

/**
 * IncomeCodeController implements the CRUD actions for IncomeCode model.
 */
class IncomeCodeController extends Controller
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
        ];
    }

    /**
     * Lists all IncomeCode models.
     * @return mixed
     */
    public function actionIndex()
    {
        $model = new IncomeCode();
        $searchModel = new IncomeCodeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $incomeTypes = IncomeType::find()->all();
        $incomeTypes = ArrayHelper::map($incomeTypes, 'id', 'name');

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            \Yii::$app->getSession()->setFlash('success', 'Code has been saved.');
            return $this->redirect(['index',
                'model' => $model, 
            ]);
        }

        return $this->render('index', [
            'model' => $model,
            'incomeTypes' => $incomeTypes,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'incomeTypes' => $incomeTypes,
        ]);
    }

    /**
     * Displays a single IncomeCode model.
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
     * Creates a new IncomeCode model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new IncomeCode();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {

            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing IncomeCode model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $searchModel = new IncomeCodeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $incomeTypes = IncomeType::find()->all();
        $incomeTypes = ArrayHelper::map($incomeTypes, 'id', 'name');

        if ($model->load(Yii::$app->request->post()) && $model->save()) {

            \Yii::$app->getSession()->setFlash('success', 'Code has been updated.');
            return $this->redirect(['index',
                'model' => $model,
            ]);
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'model' => $model,
            'incomeTypes' => $incomeTypes,
        ]);
    }

    /**
     * Deletes an existing IncomeCode model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        \Yii::$app->getSession()->setFlash('success', 'Code has been deleted.');
        return $this->redirect(['index']);
    }

    /**
     * Finds the IncomeCode model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return IncomeCode the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = IncomeCode::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
