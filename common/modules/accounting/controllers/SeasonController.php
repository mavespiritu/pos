<?php

namespace common\modules\accounting\controllers;

use Yii;
use common\modules\accounting\models\BranchProgram;
use common\modules\accounting\models\AccessProgram;
use common\modules\accounting\models\Season;
use common\modules\accounting\models\SeasonOrList;
use common\modules\accounting\models\SeasonSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\filters\AccessControl;
/**
 * SeasonController implements the CRUD actions for Season model.
 */
class SeasonController extends Controller
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
                        'roles' => ['manageSeason'],
                    ],
                    [
                        'actions' => ['update'],
                        'allow' => true,
                        'roles' => ['updateSeason'],
                    ],
                    [
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['deleteSeason'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all Season models.
     * @return mixed
     */
    public function actionIndex()
    {
        $model = new Season();

        $user_info = Yii::$app->user->identity->userinfo;
        $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
        $rolenames =  ArrayHelper::map($roles, 'name','name');

        $access = AccessProgram::findOne(['user_id' => Yii::$app->user->identity->userinfo->user_id]);

        if(in_array('TopManagement',$rolenames)){
            $branchPrograms = $access ? $access->branch_program_id!= '' ? BranchProgram::find()
                                ->select(['accounting_branch_program.id as id', 'concat(accounting_branch.name," - ",accounting_program.name) as branchProgramName'])
                                ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->asArray()
                                ->orderBy(['branchProgramName' => SORT_ASC])
                                ->all() : BranchProgram::find()
                                ->select(['accounting_branch_program.id as id', 'concat(accounting_branch.name," - ",accounting_program.name) as branchProgramName'])
                                ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                                ->asArray()
                                ->orderBy(['branchProgramName' => SORT_ASC])
                                ->all() : BranchProgram::find()
                                ->select(['accounting_branch_program.id as id', 'concat(accounting_branch.name," - ",accounting_program.name) as branchProgramName'])
                                ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                                ->asArray()
                                ->orderBy(['branchProgramName' => SORT_ASC])
                                ->all();

            $branchPrograms = ArrayHelper::map($branchPrograms, 'id', 'branchProgramName');
        }else{
            $branchPrograms = $access ? $access->branch_program_id!= '' ? BranchProgram::find()
                                ->select(['accounting_branch_program.id as id', 'concat(accounting_branch.name," - ",accounting_program.name) as branchProgramName'])
                                ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->asArray()
                                ->orderBy(['branchProgramName' => SORT_ASC])
                                ->all() : BranchProgram::find()
                                ->select(['accounting_branch_program.id as id', 'concat(accounting_branch.name," - ",accounting_program.name) as branchProgramName'])
                                ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['branchProgramName' => SORT_ASC])
                                ->all() : BranchProgram::find()
                                ->select(['accounting_branch_program.id as id', 'concat(accounting_branch.name," - ",accounting_program.name) as branchProgramName'])
                                ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['branchProgramName' => SORT_ASC])
                                ->all();
            $branchPrograms = ArrayHelper::map($branchPrograms, 'id', 'branchProgramName');
        }

        $searchModel = new SeasonSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        if ($model->load(Yii::$app->request->post())) {
            $postData = Yii::$app->request->post()['Season'];
            if(Season::find()->where(['branch_program_id' => $postData['branch_program_id'], 'name' => $postData['name'], 'start_date' => $postData['start_date'], 'end_date' => $postData['end_date']])->exists())
            {
                \Yii::$app->getSession()->setFlash('danger', 'Season already exists.');
            }else{
                $model->save();

                $orList = new SeasonOrList();
                $orList->season_id = $model->id;
                $orList->or_start = $model->or_start;
                $orList->no_of_pieces = $model->no_of_pieces;
                $orList->save();

                \Yii::$app->getSession()->setFlash('success', 'Season has been saved.');
                return $this->redirect(['index']);
            }
        }

        return $this->render('index', [
            'model' => $model,
            'branchPrograms' => $branchPrograms,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Updates an existing Season model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $user_info = Yii::$app->user->identity->userinfo;
        $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
        $rolenames =  ArrayHelper::map($roles, 'name','name');

        $access = AccessProgram::findOne(['user_id' => Yii::$app->user->identity->userinfo->user_id]);

        if(in_array('TopManagement',$rolenames)){
            $branchPrograms = $access ? $access->branch_program_id!= '' ? BranchProgram::find()
                                ->select(['accounting_branch_program.id as id', 'concat(accounting_branch.name," - ",accounting_program.name) as branchProgramName'])
                                ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->asArray()
                                ->orderBy(['branchProgramName' => SORT_ASC])
                                ->all() : BranchProgram::find()
                                ->select(['accounting_branch_program.id as id', 'concat(accounting_branch.name," - ",accounting_program.name) as branchProgramName'])
                                ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                                ->asArray()
                                ->orderBy(['branchProgramName' => SORT_ASC])
                                ->all() : BranchProgram::find()
                                ->select(['accounting_branch_program.id as id', 'concat(accounting_branch.name," - ",accounting_program.name) as branchProgramName'])
                                ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                                ->asArray()
                                ->orderBy(['branchProgramName' => SORT_ASC])
                                ->all();

            $branchPrograms = ArrayHelper::map($branchPrograms, 'id', 'branchProgramName');
        }else{
            $branchPrograms = $access ? $access->branch_program_id!= '' ? BranchProgram::find()
                                ->select(['accounting_branch_program.id as id', 'concat(accounting_branch.name," - ",accounting_program.name) as branchProgramName'])
                                ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                                ->asArray()
                                ->orderBy(['branchProgramName' => SORT_ASC])
                                ->all() : BranchProgram::find()
                                ->select(['accounting_branch_program.id as id', 'concat(accounting_branch.name," - ",accounting_program.name) as branchProgramName'])
                                ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['branchProgramName' => SORT_ASC])
                                ->all() : BranchProgram::find()
                                ->select(['accounting_branch_program.id as id', 'concat(accounting_branch.name," - ",accounting_program.name) as branchProgramName'])
                                ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                                ->asArray()
                                ->orderBy(['branchProgramName' => SORT_ASC])
                                ->all();
            $branchPrograms = ArrayHelper::map($branchPrograms, 'id', 'branchProgramName');
        }

        $searchModel = new SeasonSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        if ($model->load(Yii::$app->request->post())) {
            $postData = Yii::$app->request->post()['Season'];
            if(Season::find()->where(['branch_program_id' => $postData['branch_program_id'], 'name' => $postData['name'], 'start_date' => $postData['start_date'], 'end_date' => $postData['end_date']])->exists())
            {
                \Yii::$app->getSession()->setFlash('danger', 'Season already exists.');
            }else{
                $model->save();
                \Yii::$app->getSession()->setFlash('success', 'Season has been updated.');
                return $this->redirect(['index']);
            }
        }

        return $this->render('index', [
            'model' => $model,
            'branchPrograms' => $branchPrograms,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Deletes an existing Season model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if($model->delete())
        {
            $orList = SeasonOrList::deleteAll('season_id = '.$model->id);
        }
        \Yii::$app->getSession()->setFlash('success', 'Season has been deleted.');
        return $this->redirect(['index']);
    }

    /**
     * Finds the Season model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Season the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Season::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
