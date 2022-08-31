<?php

namespace common\modules\accounting\controllers;

use Yii;
use common\modules\accounting\models\Branch;
use common\modules\accounting\models\DateRestriction;
use common\modules\accounting\models\BranchSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
/**
 * BranchController implements the CRUD actions for branch model.
 */
class BranchController extends Controller
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
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'update', 'delete'],
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['manageBranch'],
                    ],
                    [
                        'actions' => ['update'],
                        'allow' => true,
                        'roles' => ['updateBranch'],
                    ],
                    [
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['deleteBranch'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all branch models.
     * @return mixed
     */
    public function actionIndex()
    {
        $model = new Branch();
        $searchModel = new BranchSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        if ($model->load(Yii::$app->request->post()) ) {
            $postData = Yii::$app->request->post()['Branch'];
            if(Branch::find()->where(['code' => $postData['code']])->orWhere(['name' => $postData['name']])->exists())
            {
                \Yii::$app->getSession()->setFlash('danger', 'Branch already exists.');
            }else{
                if($model->save())
                {
                    $restriction = new DateRestriction();
                    $restriction->branch_id = $model->id;
                    $restriction->allow = 'No';
                    $restriction->save();
                }
                \Yii::$app->getSession()->setFlash('success', 'Branch has been saved.');
            }
            return $this->redirect(['index']);
        }

        return $this->render('index', [
            'model' => $model,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Updates an existing branch model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $searchModel = new BranchSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        if ($model->load(Yii::$app->request->post()) ) {
            $postData = Yii::$app->request->post()['Branch'];
            if(Branch::find()->where(['code' => $postData['code']])->orWhere(['name' => $postData['name']])->exists())
            {
                \Yii::$app->getSession()->setFlash('danger', 'Branch already exists.');
            }else{
                if($model->save())
                {
                    $restriction = DateRestriction::findOne(['branch_id' => $model->id]);
                    $restriction->branch_id = $model->id;
                    $restriction->allow = 'No';
                    $restriction->save();
                }
                \Yii::$app->getSession()->setFlash('success', 'Branch has been updated.');
            }
            return $this->redirect(['index']);
        }

        return $this->render('index', [
            'model' => $model,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Deletes an existing branch model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        \Yii::$app->getSession()->setFlash('success', 'Branch has been deleted.');
        return $this->redirect(['index']);
    }

    /**
     * Finds the branch model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return branch the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = branch::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
