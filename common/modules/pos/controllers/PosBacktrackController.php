<?php

namespace common\modules\pos\controllers;

use Yii;
use common\modules\pos\models\PosBranch;
use common\modules\pos\models\PosBacktrack;
use common\modules\pos\models\PosBacktrackSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;

/**
 * PosBacktrackController implements the CRUD actions for PosBacktrack model.
 */
class PosBacktrackController extends Controller
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
     * Lists all PosBacktrack models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PosBacktrackSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $branches = PosBranch::find()
                    ->select(['id', 'title'])
                    ->asArray()
                    ->orderBy(['title' => SORT_ASC])
                    ->all();

        $branches = ArrayHelper::map($branches, 'id', 'title');

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'branches' => $branches,
        ]);
    }

    /**
     * Creates a new PosBacktrack model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new PosBacktrack();

        $branches = PosBranch::find()
                    ->select(['id', 'title'])
                    ->asArray()
                    ->orderBy(['title' => SORT_ASC])
                    ->all();

        $branches = ArrayHelper::map($branches, 'id', 'title');

        if ($model->load(Yii::$app->request->post())) {

            $backtrack = PosBacktrack::findOne(['branch_id' => $model->branch_id]) ? PosBacktrack::findOne(['branch_id' => $model->branch_id]) : new PosBacktrack();

            $backtrack->branch_id = $model->branch_id;
            $backtrack->date_from = $model->date_from;
            $backtrack->date_to = $model->date_to;
            $backtrack->field = $model->field;
            $backtrack->save(false);
            
            \Yii::$app->getSession()->setFlash('success', 'Record Saved');
            return $this->redirect(['index']);

        }

        return $this->render('create', [
            'model' => $model,
            'branches' => $branches,
        ]);
    }

    /**
     * Updates an existing PosBacktrack model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $branches = PosBranch::find()
                    ->select(['id', 'title'])
                    ->asArray()
                    ->orderBy(['title' => SORT_ASC])
                    ->all();

        $branches = ArrayHelper::map($branches, 'id', 'title');

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            
            \Yii::$app->getSession()->setFlash('success', 'Record Saved');
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
            'branches' => $branches,
        ]);
    }

    /**
     * Deletes an existing PosBacktrack model.
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
     * Finds the PosBacktrack model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return PosBacktrack the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PosBacktrack::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
