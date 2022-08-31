<?php

namespace common\modules\accounting\controllers;

use Yii;
use common\modules\accounting\models\MultipleModel;
use common\modules\accounting\models\Package;
use common\modules\accounting\models\PackageFreebie;
use common\modules\accounting\models\Branch;
use common\modules\accounting\models\Program;
use common\modules\accounting\models\BranchProgram;
use common\modules\accounting\models\Season;
use common\modules\accounting\models\PackageType;
use common\modules\accounting\models\Freebie;
use common\modules\accounting\models\PackageSearch;
use common\modules\accounting\models\ArchiveSeason;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\filters\AccessControl;
/**
 * PackageController implements the CRUD actions for Package model.
 */
class PackageController extends Controller
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
                'only' => ['index', 'update', 'view', 'delete'],
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['managePackage'],
                    ],
                    [
                        'actions' => ['update'],
                        'allow' => true,
                        'roles' => ['updatePackage'],
                    ],
                    [
                        'actions' => ['view'],
                        'allow' => true,
                        'roles' => ['updatePackage'],
                    ],
                    [
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['deletePackage'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all Package models.
     * @return mixed
     */
    public function actionIndex()
    {
        $model = new Package();

        $branchPrograms = BranchProgram::find()
                                ->select(['accounting_branch_program.id as id', 'concat(accounting_branch.name," - ",accounting_program.name) as name'])
                                ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                                ->asArray()
                                ->orderBy(['name' => SORT_ASC])
                                ->all();

        $branchPrograms = ArrayHelper::map($branchPrograms,'id','name');

        $seasons = [];

        $packageTypes = PackageType::find()
                        ->select([
                            'accounting_package_type.id as id',
                            'concat(accounting_enrolee_type.name," - ",accounting_package_type.name) as name',
                        ])
                        ->leftJoin('accounting_enrolee_type','accounting_enrolee_type.id = accounting_package_type.enrolee_type_id')
                        ->asArray()
                        ->all();

        $packageTypes = ArrayHelper::map($packageTypes,'id','name');

        $packageFreebies = [];
        $freebies = Freebie::find()->all();

        if($freebies)
        {
            foreach($freebies as $freebie)
            {
                $value = new PackageFreebie();
                $packageFreebies[] = $value;
            }
        }

        $searchModel = new PackageSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        if ($model->load(Yii::$app->request->post())) {
            $bp = BranchProgram::findOne($model->branch_program_id);

            $model->branch_id = $bp->branch_id;
            $model->program_id = $bp->program_id;
            if ($model->save()) {
                $packageFreebies = Yii::$app->request->post()['PackageFreebie'];
                foreach($packageFreebies as $packageFreebie)
                {   
                    $freebie = new PackageFreebie();
                    $freebie->package_id = $model->id;
                    $freebie->freebie_id = $packageFreebie['freebie_id'];
                    $freebie->amount = $packageFreebie['amount'];
                    $freebie->save();
                }
            }

            \Yii::$app->getSession()->setFlash('success', 'Package has been saved.');
            return $this->redirect(['index']);
        }

        return $this->render('index', [
            'model' => $model,
            'branchPrograms' => $branchPrograms,
            'seasons' => $seasons,
            'packageTypes' => $packageTypes,
            'freebies' => $freebies,
            'packageFreebies' => $packageFreebies,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Package model.
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
     * Updates an existing Package model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $branchPrograms = BranchProgram::find()
                                ->select(['accounting_branch_program.id as id', 'concat(accounting_branch.name," - ",accounting_program.name) as name'])
                                ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                                ->asArray()
                                ->orderBy(['name' => SORT_ASC])
                                ->all();

        $branchPrograms = ArrayHelper::map($branchPrograms,'id','name');

        $archivedSeasons = ArchiveSeason::find()->select(['season_id as id'])->asArray()->all();
        $archivedSeasons = ArrayHelper::map($archivedSeasons, 'id', 'id');
        $seasons = Season::find()
                    ->select(['accounting_season.id as id', 'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name'])
                    ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                    ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                    ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                    ->where(['accounting_branch_program.id' => $model->branch_program_id])
                    ->andWhere(['not in', 'accounting_season.id', $archivedSeasons])
                    ->asArray()
                    ->orderBy(['accounting_season.id' => SORT_DESC])
                    ->all();

        $seasons = ArrayHelper::map($seasons,'id','name');


        $packageTypes = PackageType::find()->all();
        $packageTypes = PackageType::find()
                        ->select([
                            'accounting_package_type.id as id',
                            'concat(accounting_enrolee_type.name," - ",accounting_package_type.name) as name',
                        ])
                        ->leftJoin('accounting_enrolee_type','accounting_enrolee_type.id = accounting_package_type.enrolee_type_id')
                        ->asArray()
                        ->all();
        $packageTypes = ArrayHelper::map($packageTypes,'id','name');

        $packageFreebies = [];
        $freebies = Freebie::find()->all();

        if($freebies)
        {
            foreach($freebies as $freebie)
            {
                $packageFreebies[] = PackageFreebie::findOne(['package_id' => $model->id, 'freebie_id' => $freebie->id]) ? PackageFreebie::findOne(['package_id' => $model->id, 'freebie_id' => $freebie->id]) : new PackageFreebie();
            }
        }

        $searchModel = new PackageSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            \Yii::$app->getSession()->setFlash('success', 'Package has been updated.');
            return $this->redirect(['index']);
        }

        return $this->render('index', [
            'model' => $model,
            'branchPrograms' => $branchPrograms,
            'seasons' => $seasons,
            'packageTypes' => $packageTypes,
            'freebies' => $freebies,
            'packageFreebies' => $packageFreebies,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Deletes an existing Package model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        \Yii::$app->getSession()->setFlash('success', 'Package has been deleted.');
        return $this->redirect(['index']);
    }

    /**
     * Finds the Package model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Package the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Package::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
