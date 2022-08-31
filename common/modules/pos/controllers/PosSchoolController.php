<?php

namespace common\modules\pos\controllers;

use Yii;
use common\modules\pos\models\PosBranch;
use common\modules\pos\models\PosSchool;
use common\modules\pos\models\PosSchoolSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;

/**
 * PosSchoolController implements the CRUD actions for PosSchool model.
 */
class PosSchoolController extends Controller
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
     * Lists all PosSchool models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PosSchoolSearch();

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $branches = Yii::$app->user->identity->userinfo->BRANCH_C != "" ? PosBranch::find()
            ->select(['id', 'title'])
            ->andWhere(['id' => Yii::$app->user->identity->userinfo->BRANCH_C])
            ->asArray()
            ->orderBy(['title' => SORT_ASC])
            ->all() : PosBranch::find()
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
     * Creates a new PosSchool model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new PosSchool();

        $branches = Yii::$app->user->identity->userinfo->BRANCH_C != "" ? PosBranch::find()
            ->select(['id', 'title'])
            ->andWhere(['id' => Yii::$app->user->identity->userinfo->BRANCH_C])
            ->asArray()
            ->orderBy(['title' => SORT_ASC])
            ->all() : PosBranch::find()
            ->select(['id', 'title'])
            ->asArray()
            ->orderBy(['title' => SORT_ASC])
            ->all();

        $branches = ArrayHelper::map($branches, 'id', 'title');

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            
            \Yii::$app->getSession()->setFlash('success', 'Record Saved');
            return $this->redirect(['index']);

        }

        return $this->render('create', [
            'model' => $model,
            'branches' => $branches,
        ]);
    }

    /**
     * Updates an existing PosSchool model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $branches = Yii::$app->user->identity->userinfo->BRANCH_C != "" ? PosBranch::find()
            ->select(['id', 'title'])
            ->andWhere(['id' => Yii::$app->user->identity->userinfo->BRANCH_C])
            ->asArray()
            ->orderBy(['title' => SORT_ASC])
            ->all() : PosBranch::find()
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
     * Deletes an existing PosSchool model.
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
     * Finds the PosSchool model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return PosSchool the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PosSchool::findOne($id)) !== null) {

            if(Yii::$app->user->identity->userinfo->BRANCH_C != '')
            {
                if(PosSchool::findOne($id)->branch_id == Yii::$app->user->identity->userinfo->BRANCH_C)
                {
                    return $model;
                }
            }else{
                return $model;
            }
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
