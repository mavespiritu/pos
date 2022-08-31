<?php

namespace common\modules\pos\controllers;

use Yii;
use common\modules\pos\models\PosBranchProgram;
use common\modules\pos\models\PosSeason;
use common\modules\pos\models\PosSeasonDueDate;
use common\modules\pos\models\PosSeasonSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
/**
 * PosSeasonController implements the CRUD actions for PosSeason model.
 */
class PosSeasonController extends Controller
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
                        'actions' => ['index', 'view', 'create', 'update', 'delete'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all PosSeason models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PosSeasonSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $branchPrograms = Yii::$app->user->identity->userinfo->BRANCH_C != "" ? 
            PosBranchProgram::find()
            ->select(['pos_branch_program.id as id', 'concat(pos_branch.title," - ",pos_program.title) as branchProgramName'])
            ->leftJoin('pos_branch', 'pos_branch.id = pos_branch_program.branch_id')
            ->leftJoin('pos_program', 'pos_program.id = pos_branch_program.program_id')
            ->andWhere(['pos_branch.id' => Yii::$app->user->identity->userinfo->BRANCH_C])
            ->asArray()
            ->orderBy(['branchProgramName' => SORT_ASC])
            ->all() : PosBranchProgram::find()
            ->select(['pos_branch_program.id as id', 'concat(pos_branch.title," - ",pos_program.title) as branchProgramName'])
            ->leftJoin('pos_branch', 'pos_branch.id = pos_branch_program.branch_id')
            ->leftJoin('pos_program', 'pos_program.id = pos_branch_program.program_id')
            ->asArray()
            ->orderBy(['branchProgramName' => SORT_ASC])
            ->all();

        $branchPrograms = ArrayHelper::map($branchPrograms, 'id', 'branchProgramName');

        return $this->render('index', [
            'branchPrograms' => $branchPrograms,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new PosSeason model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new PosSeason();

        $branchPrograms = PosBranchProgram::find()
            ->select(['pos_branch_program.id as id', 'concat(pos_branch.title," - ",pos_program.title) as branchProgramName'])
            ->leftJoin('pos_branch', 'pos_branch.id = pos_branch_program.branch_id')
            ->leftJoin('pos_program', 'pos_program.id = pos_branch_program.program_id')
            ->asArray()
            ->orderBy(['branchProgramName' => SORT_ASC])
            ->all();

        $branchPrograms = ArrayHelper::map($branchPrograms, 'id', 'branchProgramName');

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            \Yii::$app->getSession()->setFlash('success', 'Record Saved');
            return $this->redirect(['index']);
        }

        return $this->render('create', [
            'model' => $model,
            'branchPrograms' => $branchPrograms,
        ]);
    }

    /**
     * Updates an existing PosSeason model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $branchPrograms = PosBranchProgram::find()
            ->select(['pos_branch_program.id as id', 'concat(pos_branch.title," - ",pos_program.title) as branchProgramName'])
            ->leftJoin('pos_branch', 'pos_branch.id = pos_branch_program.branch_id')
            ->leftJoin('pos_program', 'pos_program.id = pos_branch_program.program_id')
            ->asArray()
            ->orderBy(['branchProgramName' => SORT_ASC])
            ->all();

        $branchPrograms = ArrayHelper::map($branchPrograms, 'id', 'branchProgramName');

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            \Yii::$app->getSession()->setFlash('success', 'Record Saved');
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
            'branchPrograms' => $branchPrograms,
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
        $model = $this->findModel($id);

        $dueDateModel = new PosSeasonDueDate();

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing PosSeason model.
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
     * Finds the PosSeason model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return PosSeason the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PosSeason::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
