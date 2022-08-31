<?php

namespace common\modules\accounting\controllers;

use Yii;
use common\modules\accounting\models\Branch;
use common\modules\accounting\models\Program;
use common\modules\accounting\models\BranchProgram;
use common\modules\accounting\models\BranchProgramEnhancement;
use common\modules\accounting\models\BranchProgramSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\filters\AccessControl;
/**
 * BranchProgramController implements the CRUD actions for BranchProgram model.
 */
class BranchProgramController extends Controller
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
                        'roles' => ['manageBranchProgram'],
                    ],
                    [
                        'actions' => ['update'],
                        'allow' => true,
                        'roles' => ['updateBranchProgram'],
                    ],
                    [
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['deleteBranchProgram'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all BranchProgram models.
     * @return mixed
     */
    public function actionIndex()
    {
        $model = new BranchProgram();
        $branches = Branch::find()->all();
        $branches = ArrayHelper::map($branches, 'id', 'name');

        $programs = Program::find()->all();
        $programs = ArrayHelper::map($programs, 'id', 'name');

        $searchModel = new BranchProgramSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        if ($model->load(Yii::$app->request->post()) ) {
            $postData = Yii::$app->request->post()['BranchProgram'];
            if(BranchProgram::find()->where(['branch_id' => $postData['branch_id'], 'program_id' => $postData['program_id']])->exists())
            {
                \Yii::$app->getSession()->setFlash('danger', 'Linked branch to program already exists.');
            }else{
                if($model->save())
                {
                    $enhancement = new BranchProgramEnhancement();
                    $enhancement->branch_program_id = $model->id;
                    $enhancement->amount = 0;
                    $enhancement->save();
                }
                \Yii::$app->getSession()->setFlash('success', 'Branch linking to program has been saved.');
            }
            return $this->redirect(['index']);
        }

        return $this->render('index', [
            'model' => $model, 
            'branches' => $branches, 
            'programs' => $programs, 
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]); 
    }

    /**
     * Updates an existing BranchProgram model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $branches = Branch::find()->orderBy(['name' => SORT_ASC])->all();
        $branches = ArrayHelper::map($branches, 'id', 'name');

        $programs = Program::find()->orderBy(['name' => SORT_ASC])->all();
        $programs = ArrayHelper::map($programs, 'id', 'name');

        $searchModel = new BranchProgramSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        if ($model->load(Yii::$app->request->post()) ) {
            $postData = Yii::$app->request->post()['BranchProgram'];
            if(BranchProgram::find()->where(['branch_id' => $postData['branch_id'], 'program_id' => $postData['program_id']])->exists())
            {
                \Yii::$app->getSession()->setFlash('danger', 'Linked branch to program already exists.');
            }else{
                $model->save();
                \Yii::$app->getSession()->setFlash('success', 'Branch linking to program has been updated.');
            }
            return $this->redirect(['index']);
        }

        return $this->render('index', [
            'model' => $model, 
            'branches' => $branches, 
            'programs' => $programs, 
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]); 
    }

    /**
     * Deletes an existing BranchProgram model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        \Yii::$app->getSession()->setFlash('success', 'Branch - Program link has been deleted.');
        return $this->redirect(['index']);
    }

    /**
     * Finds the BranchProgram model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return BranchProgram the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = BranchProgram::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
