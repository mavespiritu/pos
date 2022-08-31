<?php

namespace common\modules\accounting\controllers;

use Yii;
use common\modules\accounting\models\Season;
use common\modules\accounting\models\Notification;
use common\modules\accounting\models\SeasonOrList;
use common\modules\accounting\models\SeasonOrListSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\filters\AccessControl;
/**
 * SeasonOrListController implements the CRUD actions for SeasonOrList model.
 */
class SeasonOrListController extends Controller
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
                        'roles' => ['manageSeasonOrList'],
                    ],
                    [
                        'actions' => ['update'],
                        'allow' => true,
                        'roles' => ['updateSeasonOrList'],
                    ],
                    [
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['deleteSeasonOrList'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all SeasonOrList models.
     * @return mixed
     */
    public function actionIndex()
    {
        $model = new SeasonOrList();

        $seasons = Season::find()
           ->select([
            'accounting_season.id as id',
            'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name'
           ])
           ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
           ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
           ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
           ->asArray()
           ->orderBy(['accounting_season.end_date' => SORT_DESC])
           ->all();

        $seasons = ArrayHelper::map($seasons, 'id', 'name');

        $searchModel = new SeasonOrListSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        if($model->load(Yii::$app->request->post()))
        {
            $postData = Yii::$app->request->post();

            if(SeasonOrList::find()->where(['season_id' => $postData['SeasonOrList']['season_id'], 'or_start' => $postData['SeasonOrList']['or_start'], 'no_of_pieces' => $postData['SeasonOrList']['no_of_pieces']])->exists())
            {
                \Yii::$app->getSession()->setFlash('danger', 'OR numbers already taken.');
            }else{
                \Yii::$app->getSession()->setFlash('success', 'OR list has been saved.');
                if($model->save())
                {
                    $notification = Notification::find()->where(['model' => 'Season', 'model_id' => $postData['SeasonOrList']['season_id']])->one();
                    if($notification)
                    {
                        $notification->delete();
                    }
                }

                return $this->redirect(['index']);
            }
        }

        return $this->render('index', [
            'model' => $model,
            'seasons' => $seasons,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Updates an existing SeasonOrList model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    /*public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $seasons = Season::find()
           ->select([
            'accounting_season.id as id',
            'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name'
           ])
           ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
           ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
           ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
           ->asArray()
           ->orderBy(['accounting_season.end_date' => SORT_DESC])
           ->all();

        $seasons = ArrayHelper::map($seasons, 'id', 'name');

        $searchModel = new SeasonOrListSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        if ($model->load(Yii::$app->request->post())) {
            $postData = Yii::$app->request->post();
            if(SeasonOrList::find()->where(['season_id' => $postData['SeasonOrList']['season_id'], 'or_start' => $postData['SeasonOrList']['or_start'], 'no_of_pieces' => $postData['SeasonOrList']['no_of_pieces']])->exists())
            {
                \Yii::$app->getSession()->setFlash('danger', 'OR numbers already taken.');
            }else{
                \Yii::$app->getSession()->setFlash('success', 'OR list has been updated.');
                $model->save();

                return $this->redirect(['index']);
            }
        }

        return $this->render('index', [
            'model' => $model,
            'seasons' => $seasons,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }*/

    /**
     * Deletes an existing SeasonOrList model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        \Yii::$app->getSession()->setFlash('success', 'OR list has been deleted.');
        return $this->redirect(['index']);
    }

    /**
     * Finds the SeasonOrList model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return SeasonOrList the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = SeasonOrList::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
