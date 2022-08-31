<?php

namespace common\modules\pos\controllers;

use Yii;
use common\modules\pos\models\PosBranchProgram;
use common\modules\pos\models\PosSeason;
use common\modules\pos\models\PosOfficialReceipt;
use common\modules\pos\models\PosOfficialReceiptSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;

/**
 * PosOfficialReceiptController implements the CRUD actions for PosOfficialReceipt model.
 */
class PosOfficialReceiptController extends Controller
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

    public function actionSeasonList($id) {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $seasons = PosSeason::find()
                    ->select(['pos_season.id as id', 'concat("SEASON ",pos_season.title) as title'])
                    ->where(['branch_program_id' => $id])
                    ->andWhere(['status' => 'Active'])
                    ->asArray()
                    ->orderBy(['title' => SORT_ASC])
                    ->all();

        $out = [];

        $out[] = ['id' => '', 'text' => ''];

        if($seasons)
        {
            foreach($seasons as $season)
            {
                $out[] = ['id' => $season['id'], 'text' => $season['title']];
            }
        }

        return $out;
    }

    /**
     * Lists all PosOfficialReceipt models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PosOfficialReceiptSearch();
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

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'seasons' => $seasons,
        ]);
    }

    /**
     * Creates a new PosOfficialReceipt model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new PosOfficialReceipt();

        $branchPrograms = PosBranchProgram::find()
            ->select(['pos_branch_program.id as id', 'concat(pos_branch.title," - ",pos_program.title) as branchProgramName'])
            ->leftJoin('pos_branch', 'pos_branch.id = pos_branch_program.branch_id')
            ->leftJoin('pos_program', 'pos_program.id = pos_branch_program.program_id')
            ->asArray()
            ->orderBy(['branchProgramName' => SORT_ASC])
            ->all();

        $branchPrograms = ArrayHelper::map($branchPrograms, 'id', 'branchProgramName');

        $seasons = [];

        if ($model->load(Yii::$app->request->post()) && $model->save()) {

            \Yii::$app->getSession()->setFlash('success', 'Record Saved');
            return $this->redirect(['index']);
        }

        return $this->render('create', [
            'model' => $model,
            'branchPrograms' => $branchPrograms,
            'seasons' => $seasons,
        ]);
    }

    /**
     * Updates an existing PosOfficialReceipt model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->branch_program_id = $model->season->branch_program_id;

        $branchPrograms = PosBranchProgram::find()
            ->select(['pos_branch_program.id as id', 'concat(pos_branch.title," - ",pos_program.title) as branchProgramName'])
            ->leftJoin('pos_branch', 'pos_branch.id = pos_branch_program.branch_id')
            ->leftJoin('pos_program', 'pos_program.id = pos_branch_program.program_id')
            ->asArray()
            ->orderBy(['branchProgramName' => SORT_ASC])
            ->all();

        $branchPrograms = ArrayHelper::map($branchPrograms, 'id', 'branchProgramName');

        $seasons = PosSeason::find()
                ->select(['pos_season.id as id','concat("SEASON ",pos_season.title) as title'])
                ->where(['branch_program_id' => $model->branch_program_id])
                ->andWhere(['status' => 'Active'])
                ->asArray()
                ->orderBy(['title' => SORT_ASC])
                ->all();

        $seasons = ArrayHelper::map($seasons, 'id', 'title');

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            
            \Yii::$app->getSession()->setFlash('success', 'Record Saved');
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
            'branchPrograms' => $branchPrograms,
            'seasons' => $seasons,
        ]);
    }

    /**
     * Deletes an existing PosOfficialReceipt model.
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
     * Finds the PosOfficialReceipt model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return PosOfficialReceipt the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PosOfficialReceipt::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
