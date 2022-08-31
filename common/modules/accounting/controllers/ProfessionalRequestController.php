<?php

namespace common\modules\accounting\controllers;

use Yii;
use common\modules\accounting\models\ProfessionalRequest;
use common\modules\accounting\models\BranchProgram;
use common\modules\accounting\models\School;
use common\modules\accounting\models\ProfessionalRequestDetail;
use common\modules\accounting\models\ProfessionalRequestSearch;
use common\modules\accounting\models\ProfessionalRequestDetailSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
/**
 * ProfessionalRequestController implements the CRUD actions for ProfessionalRequest model.
 */
class ProfessionalRequestController extends Controller
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
                'only' => ['index','create', 'update', 'delete', 'view'],
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['manageProfessionalRequest'],
                    ],
                    [
                        'actions' => ['create'],
                        'allow' => true,
                        'roles' => ['createProfessionalRequest'],
                    ],
                    [
                        'actions' => ['update'],
                        'allow' => true,
                        'roles' => ['updateProfessionalRequest'],
                    ],
                    [
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['deleteProfessionalRequest'],
                    ],
                    [
                        'actions' => ['view'],
                        'allow' => true,
                        'roles' => ['viewProfessionalRequest'],
                    ],
                ],
            ],
        ];
    }

    public function actionSchoolList($q = null, $id = null) {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $user_info = Yii::$app->user->identity->userinfo;
        $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
        $rolenames =  ArrayHelper::map($roles, 'name','name');

        $out = ['results' => ['id' => '', 'name' => '']];
        if (!is_null($q)) {
            $names = School::find()
                    ->select(['id', 'concat(name," (",location,")") as name'])
                    ->where(['like','name', $q])
                    ->limit(20)
                    ->asArray()
                    ->all();
            $out['results'] = array_values($names);
        }
        elseif ($id > 0) {
            $out['results'] = ['id' => $id, 'name' => School::find($id)->name];
        }
        return $out;
    }

    /**
     * Lists all ProfessionalRequest models.
     * @return mixed
     */
    public function actionIndex()
    {
        $user_info = Yii::$app->user->identity->userinfo;
        $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
        $rolenames =  ArrayHelper::map($roles, 'name','name');

        $model = new ProfessionalRequest();
        $searchModel = new ProfessionalRequestSearch();

        if(in_array('Professional',$rolenames)){
            $searchModel->user_id = Yii::$app->user->id;
        }

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        if ($model->load(Yii::$app->request->post())) {
            $postData = Yii::$app->request->post();

            $date[] = explode(" - ", $postData['ProfessionalRequest']['start_date']);

            $model->user_id = Yii::$app->user->id;
            $model->start_date = date("Y-m-d", strtotime($date[0][0]));
            $model->end_date = date("Y-m-d", strtotime($date[0][1]));
            $model->approval_status = 'For Approval';
            $model->save();

            \Yii::$app->getSession()->setFlash('success', 'Request has been saved.');
             return $this->redirect(['/accounting/professional-request/']);
        }

        return $this->render('index', [
            'model' => $model,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single ProfessionalRequest model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        $detailModel = new ProfessionalRequestDetail();

        $branchPrograms = BranchProgram::find()
                                ->select(['accounting_branch_program.id as id', 'concat(accounting_branch.name," - ",accounting_program.name) as branchProgramName'])
                                ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                                ->asArray()
                                ->orderBy(['branchProgramName' => SORT_ASC])
                                ->all();

        $branchPrograms = ArrayHelper::map($branchPrograms, 'id', 'branchProgramName');

        $searchModel = new ProfessionalRequestDetailSearch();
        $searchModel->professional_request_id = $model->id;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        if ($detailModel->load(Yii::$app->request->post())) {
            $postData = Yii::$app->request->post();

            $detailModel->professional_request_id = $model->id;
            $detailModel->save();

            \Yii::$app->getSession()->setFlash('success', 'Particular has been saved.');
             return $this->redirect(['/accounting/professional-request/view','id' => $model->id]);
        }

        return $this->render('view', [
            'model' => $model,
            'detailModel' => $detailModel,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'branchPrograms' => $branchPrograms,
        ]);
    }

    public function actionUpdateDetail($id)
    {
        $detailModel = ProfessionalRequestDetail::findOne($id);

        $model = $detailModel->professionalRequest;

        $branchPrograms = BranchProgram::find()
                                ->select(['accounting_branch_program.id as id', 'concat(accounting_branch.name," - ",accounting_program.name) as branchProgramName'])
                                ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                                ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                                ->asArray()
                                ->orderBy(['branchProgramName' => SORT_ASC])
                                ->all();

        $branchPrograms = ArrayHelper::map($branchPrograms, 'id', 'branchProgramName');

        $searchModel = new ProfessionalRequestDetailSearch();
        $searchModel->professional_request_id = $model->id;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        if ($detailModel->load(Yii::$app->request->post())) {
            $postData = Yii::$app->request->post();

            $detailModel->professional_request_id = $model->id;
            $detailModel->save();

            \Yii::$app->getSession()->setFlash('success', 'Particular has been saved.');
             return $this->redirect(['/accounting/professional-request/view','id' => $model->id]);
        }

        return $this->render('view', [
            'model' => $model,
            'detailModel' => $detailModel,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'branchPrograms' => $branchPrograms,
        ]);
    }

    public function actionApprove()
    {
        if(Yii::$app->request->post())
        {
            $postData = Yii::$app->request->post();
            $model = $this->findModel($postData['ProfessionalRequest']['id']);
            $model->approval_status = 'Approved';
            $model->save(false);

            \Yii::$app->getSession()->setFlash('success', 'Request has been approved.');
             return $this->redirect(['/accounting/professional-request/view','id' => $model->id]);
        }
    }

    public function actionDecline()
    {
        if(Yii::$app->request->post())
        {
            $postData = Yii::$app->request->post();
            $model = $this->findModel($postData['ProfessionalRequest']['id']);
            $model->approval_status = 'Declined';
            $model->save(false);

            \Yii::$app->getSession()->setFlash('success', 'Request has been declined.');
             return $this->redirect(['/accounting/professional-request/view','id' => $model->id]);
        }
    }

    /**
     * Creates a new ProfessionalRequest model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ProfessionalRequest();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing ProfessionalRequest model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->start_date = date("m/d/Y", strtotime($model->start_date)).' - '.date("m/d/Y", strtotime($model->end_date));

        $user_info = Yii::$app->user->identity->userinfo;
        $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
        $rolenames =  ArrayHelper::map($roles, 'name','name');

        $searchModel = new ProfessionalRequestSearch();

        if(in_array('Professional',$rolenames)){
            $searchModel->user_id = Yii::$app->user->id;
        }

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        if ($model->load(Yii::$app->request->post())) {
            $postData = Yii::$app->request->post();

            $date[] = explode(" - ", $postData['ProfessionalRequest']['start_date']);

            $model->start_date = date("Y-m-d", strtotime($date[0][0]));
            $model->end_date = date("Y-m-d", strtotime($date[0][1]));
            $model->save();

            \Yii::$app->getSession()->setFlash('success', 'Request has been updated.');
            return $this->redirect(['/accounting/professional-request/']);
        }

        return $this->render('index', [
            'model' => $model,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Deletes an existing ProfessionalRequest model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        \Yii::$app->getSession()->setFlash('success', 'Request has been deleted.');
        return $this->redirect(['/accounting/professional-request/']);
    }

    public function actionDeleteDetail($id)
    {
        $detailModel = ProfessionalRequestDetail::findOne($id);

        $model = $detailModel->professionalRequest;

        $detailModel->delete();

        \Yii::$app->getSession()->setFlash('success', 'Particular has been deleted.');
        return $this->redirect(['/accounting/professional-request/view', 'id' => $model->id]);
    }

    /**
     * Finds the ProfessionalRequest model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ProfessionalRequest the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ProfessionalRequest::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
