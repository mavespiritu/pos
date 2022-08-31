<?php

namespace common\modules\pos\controllers;

use Yii;
use common\modules\pos\models\PosBranch;
use common\modules\pos\models\PosProgram;
use common\modules\pos\models\PosBranchProgram;
use common\modules\pos\models\PosBranchProgramSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
/**
 * PosBranchProgramController implements the CRUD actions for PosBranchProgram model.
 */
class PosBranchProgramController extends Controller
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
                        'roles' => ['TopManagement'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all PosBranchProgram models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PosBranchProgramSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $branches = PosBranch::find()->all();
        $branches = ArrayHelper::map($branches, 'id', 'title');

        $programs = PosProgram::find()->all();
        $programs = ArrayHelper::map($programs, 'id', 'title');

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'branches' => $branches,
            'programs' => $programs,
        ]);
    }

    /**
     * Creates a new PosBranchProgram model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new PosBranchProgram();

        $branches = PosBranch::find()->all();
        $branches = ArrayHelper::map($branches, 'id', 'title');

        $programs = PosProgram::find()->all();
        $programs = ArrayHelper::map($programs, 'id', 'title');

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            
            \Yii::$app->getSession()->setFlash('success', 'Record Saved');
            return $this->redirect(['index']);

        }

        return $this->render('create', [
            'model' => $model,
            'branches' => $branches, 
            'programs' => $programs, 
        ]);
    }

    /**
     * Updates an existing PosBranchProgram model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $branches = PosBranch::find()->all();
        $branches = ArrayHelper::map($branches, 'id', 'title');

        $programs = PosProgram::find()->all();
        $programs = ArrayHelper::map($programs, 'id', 'title');

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            
            \Yii::$app->getSession()->setFlash('success', 'Record Saved');
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
            'branches' => $branches, 
            'programs' => $programs, 
        ]);
    }

    /**
     * Deletes an existing PosBranchProgram model.
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
     * Finds the PosBranchProgram model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return PosBranchProgram the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PosBranchProgram::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
