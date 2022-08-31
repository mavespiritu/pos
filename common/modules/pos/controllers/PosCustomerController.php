<?php

namespace common\modules\pos\controllers;

use Yii;
use common\modules\pos\models\PosCustomer;
use common\modules\pos\models\Province;
use common\modules\pos\models\Citymun;
use common\modules\pos\models\PosSchool;
use common\modules\pos\models\PosCustomerSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;

/**
 * PosCustomerController implements the CRUD actions for PosCustomer model.
 */
class PosCustomerController extends Controller
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
                        'actions' => ['index', 'create', 'view' ,'update', 'delete', 'enrolment'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionCitymunList($province)
    {
        $citymuns = Citymun::find()->select(['citymun_c','citymun_m'])->where(['province_c' => $province])->all();
        $arr = [];
        $arr[] = ['id'=>'','text'=>''];
        foreach($citymuns as $citymun){
            $arr[] = ['id'=>$citymun->citymun_c,'text'=> $citymun->citymun_m];
        }
        \Yii::$app->response->format = 'json';
        return $arr;
    }

    public function actionSchoolList($q = null, $id = null) {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $out = ['results' => ['id' => '', 'name' => '']];
        if (!is_null($q)) {
            $names = PosSchool::find()
                    ->select(['id', 'concat(title," (",address,")") as title'])
                    ->where(['like','title', $q]);
                    

            if(Yii::$app->user->identity->userinfo->BRANCH_C != ""){
                $names = $names->andWhere(['branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C]);
            }

            $names = $names->limit(20)
                    ->asArray()
                    ->all();

            $out['results'] = array_values($names);
        }
        elseif ($id > 0) {
            $out['results'] = ['id' => $id, 'name' => PosSchool::find($id)->title.' ('.PosSchool::find($id)->adress.')'];
        }
        return $out;
    }

    /**
     * Lists all PosCustomer models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PosCustomerSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single PosCustomer model.
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
     * Creates a new PosCustomer model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new PosCustomer();

        $provinces = Province::find()->select(['tblprovince.province_c, concat(province_m," (",abbreviation,")") as title'])->leftJoin('tblregion','tblregion.region_c = tblprovince.region_c')->orderBy(['tblregion.region_sort' => SORT_ASC, 'tblprovince.province_m' => SORT_ASC])->asArray()->all();
        $provinces = ArrayHelper::map($provinces, 'province_c', 'title');

        $citymuns = [];

        if ($model->load(Yii::$app->request->post()) && $model->save()) {

            \Yii::$app->getSession()->setFlash('success', 'Record Saved');
            return $this->redirect(['index']);
        }

        return $this->render('create', [
            'model' => $model,
            'provinces' => $provinces,
            'citymuns' => $citymuns,
        ]);
    }

    /**
     * Updates an existing PosCustomer model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $provinces = Province::find()->select(['tblprovince.province_c, concat(province_m," (",abbreviation,")") as title'])->leftJoin('tblregion','tblregion.region_c = tblprovince.region_c')->orderBy(['tblregion.region_sort' => SORT_ASC, 'tblprovince.province_m' => SORT_ASC])->asArray()->all();
        $provinces = ArrayHelper::map($provinces, 'province_c', 'title');

        $citymuns = Citymun::find()->where(['province_c'=> $model->province_id])->all();
        $citymuns = ArrayHelper::map($citymuns,'citymun_c','citymun_m');

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            
            \Yii::$app->getSession()->setFlash('success', 'Record Saved');
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
            'provinces' => $provinces,
            'citymuns' => $citymuns,
        ]);
    }

    /**
     * Deletes an existing PosCustomer model.
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
     * Finds the PosCustomer model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return PosCustomer the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PosCustomer::findOne($id)) !== null) {

                return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
