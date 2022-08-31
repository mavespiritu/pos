<?php

namespace common\modules\accounting\controllers;

use Yii;
use common\modules\accounting\models\EnroleeType;
use common\modules\accounting\models\PackageType;
use common\modules\accounting\models\PackageTypeSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
/**
 * PackageTypeController implements the CRUD actions for PackageType model.
 */
class PackageTypeController extends Controller
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
     * Lists all PackageType models.
     * @return mixed
     */
    public function actionIndex()
    {
        $model = new PackageType();
        $enroleeTypes = EnroleeType::find()->all();
        $enroleeTypes = ArrayHelper::map($enroleeTypes, 'id', 'name');

        $searchModel = new PackageTypeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        if ($model->load(Yii::$app->request->post()) ) {
            $postData = Yii::$app->request->post()['PackageType'];
            if(PackageType::find()->where(['enrolee_type_id' => $postData['enrolee_type_id'], 'name' => $postData['name']])->exists())
            {
                \Yii::$app->getSession()->setFlash('danger', 'Package type already exists.');
            }else{
                $model->save();
                \Yii::$app->getSession()->setFlash('success', 'Package type has been saved.');
            }
            return $this->redirect(['index',
                'model' => $model, 
            ]);
        }

        return $this->render('index', [
            'model' => $model,
            'enroleeTypes' => $enroleeTypes, 
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single PackageType model.
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
     * Creates a new PackageType model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new PackageType();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing PackageType model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $enroleeTypes = EnroleeType::find()->all();
        $enroleeTypes = ArrayHelper::map($enroleeTypes, 'id', 'name');

        $searchModel = new PackageTypeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        if ($model->load(Yii::$app->request->post()) ) {
            $postData = Yii::$app->request->post()['PackageType'];
            if(PackageType::find()->where(['enrolee_type_id' => $postData['enrolee_type_id'], 'name' => $postData['name']])->exists())
            {
                \Yii::$app->getSession()->setFlash('danger', 'Package type already exists.');
            }else{
                $model->save();
                \Yii::$app->getSession()->setFlash('success', 'Package type has been updated.');
            }
            return $this->redirect(['index',
                'model' => $model, 
            ]);
        }

        return $this->render('index', [
            'model' => $model,
            'enroleeTypes' => $enroleeTypes, 
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Deletes an existing PackageType model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        \Yii::$app->getSession()->setFlash('success', 'Package type has been deleted.');
        return $this->redirect(['index']);
    }

    /**
     * Finds the PackageType model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return PackageType the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PackageType::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
