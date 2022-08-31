<?php

namespace common\modules\accounting\controllers;

use Yii;
use common\modules\accounting\models\Branch;
use common\modules\accounting\models\TargetExpense;
use common\modules\accounting\models\TargetExpenseSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\filters\AccessControl;
/**
 * TargetExpenseController implements the CRUD actions for TargetExpense model.
 */
class TargetExpenseController extends Controller
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
                        'roles' => ['manageTargetExpense'],
                    ],
                    [
                        'actions' => ['update'],
                        'allow' => true,
                        'roles' => ['updateTargetExpense'],
                    ],
                    [
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['deleteTargetExpense'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all TargetExpense models.
     * @return mixed
     */
    public function actionIndex()
    {
        $model = new TargetExpense();
        $branches = Branch::find()->all();
        $branches = ArrayHelper::map($branches, 'id', 'name');

        $months = array(
            'January' => 'January',
            'February' => 'February',
            'March' => 'March',
            'April' => 'April',
            'May' => 'May',
            'June' => 'June',
            'July' => 'July',
            'August' => 'August',
            'September' => 'September',
            'October' => 'October',
            'November' => 'November',
            'December' => 'December',
        );

        $searchModel = new TargetExpenseSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        if ($model->load(Yii::$app->request->post())) {
           $postData = Yii::$app->request->post()['TargetExpense'];
           $model->month = $postData['month'].' '.$postData['year'];
           $model->save();
            \Yii::$app->getSession()->setFlash('success', 'Target expenses has been saved.');
            return $this->redirect(['index']);
        }

        return $this->render('index', [
            'model' => $model,
            'branches' => $branches, 
            'months' => $months, 
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single TargetExpense model.
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
     * Creates a new TargetExpense model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new TargetExpense();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing TargetExpense model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $monthYear = explode(' ', $model->month);
        $model->month = $monthYear[0];
        $model->year = $monthYear[1];
        $branches = Branch::find()->all();
        $branches = ArrayHelper::map($branches, 'id', 'name');

        $months = array(
            'January' => 'January',
            'February' => 'February',
            'March' => 'March',
            'April' => 'April',
            'May' => 'May',
            'June' => 'June',
            'July' => 'July',
            'August' => 'August',
            'September' => 'September',
            'October' => 'October',
            'November' => 'November',
            'December' => 'December',
        );

        $searchModel = new TargetExpenseSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        if ($model->load(Yii::$app->request->post())) {
           $postData = Yii::$app->request->post()['TargetExpense'];
           $model->month = $postData['month'].' '.$postData['year'];
           $model->save();
            \Yii::$app->getSession()->setFlash('success', 'Target expenses has been updated.');
            return $this->redirect(['index']);
        }

        return $this->render('index', [
            'model' => $model,
            'branches' => $branches, 
            'months' => $months, 
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Deletes an existing TargetExpense model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        \Yii::$app->getSession()->setFlash('success', 'Target expenses has been deleted.');
        return $this->redirect(['index']);
    }

    /**
     * Finds the TargetExpense model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return TargetExpense the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = TargetExpense::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
