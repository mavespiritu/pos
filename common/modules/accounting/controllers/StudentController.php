<?php

namespace common\modules\accounting\controllers;

use Yii;
use common\modules\accounting\models\AccessProgram;
use common\modules\accounting\models\Region;
use common\modules\accounting\models\Province;
use common\modules\accounting\models\Citymun;
use common\modules\accounting\models\Student;
use common\modules\accounting\models\Dropout;
use common\modules\accounting\models\IncomeEnrolment;
use common\modules\accounting\models\FreebieAndIcon;
use common\modules\accounting\models\Income;
use common\modules\accounting\models\Coaching;
use common\modules\accounting\models\IncomeCode;
use common\modules\accounting\models\Program;
use common\modules\accounting\models\School;
use common\modules\accounting\models\Package;
use common\modules\accounting\models\PackageStudent;
use common\modules\accounting\models\StudentEnroleeType;
use common\modules\accounting\models\StudentTuition;
use common\modules\accounting\models\StudentProgram;
use common\modules\accounting\models\Discount;
use common\modules\accounting\models\DiscountType;
use common\modules\accounting\models\BranchProgramEnhancement;
use common\modules\accounting\models\Enhancement;
use common\modules\accounting\models\Season;
use common\modules\accounting\models\SeasonOr;
use common\modules\accounting\models\SeasonOrList;
use common\modules\accounting\models\EnroleeType;
use common\modules\accounting\models\AdvanceEnrolment;
use common\modules\accounting\models\StudentSearch;
use common\modules\accounting\models\Transferee;
use common\modules\accounting\models\StudentBranchProgram;
use common\modules\accounting\models\Notification;
use common\modules\accounting\models\BranchProgram;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\filters\AccessControl;
use yii\web\Response;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use kartik\mpdf\Pdf;
/**
 * StudentController implements the CRUD actions for Student model.
 */
class StudentController extends Controller
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
                'only' => ['list','index','create','update','view', 'delete'],
                'rules' => [
                    [
                        'actions' => ['list'],
                        'allow' => true,
                        'roles' => ['manageStudent'],
                    ],
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['enrolStudent'],
                    ],
                    [
                        'actions' => ['create'],
                        'allow' => true,
                        'roles' => ['createStudent'],
                    ],
                    [
                        'actions' => ['update'],
                        'allow' => true,
                        'roles' => ['updateStudent'],
                    ],
                    [
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['deleteStudent'],
                    ],
                    [
                        'actions' => ['view'],
                        'allow' => true,
                        'roles' => ['viewStudent']
                    ],

                ],
            ],
        ];
    }

    /*public function actionTest()
    {
        Yii::$app->mailer->compose(['view','id'=>$model->code])
            ->setFrom('support@cbms.beta')
            ->setTo('m_espiritu11@outlook.com')
            ->setSubject('CBMS BUB Portal: Project Approval')
            ->setHtmlBody('<p>Test</p>')
            ->send();
    }*/

    public function actionCitymunList($province)
    {
        $citymuns = Citymun::find()->select(['citymun_c','citymun_m'])->where(['province_c' => $province ])->all();
        $arr = [];
        $arr[] = ['id'=>'','text'=>''];
        foreach($citymuns as $citymun){
            $arr[] = ['id'=>$citymun->citymun_c,'text'=> $citymun->citymun_m];
        }
        \Yii::$app->response->format = 'json';
        return $arr;
    }

    public function actionStudentList($q = null, $id = null) {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $user_info = Yii::$app->user->identity->userinfo;
        $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
        $rolenames =  ArrayHelper::map($roles, 'name','name');

        $access = AccessProgram::findOne(['user_id' => Yii::$app->user->identity->userinfo->user_id]);

        $out = ['results' => ['id' => '', 'name' => '']];
        if (!is_null($q)) {
            if(in_array('TopManagement',$rolenames)){
                $names = $access ? $access->branch_program_id!= '' ? Student::find()
                    ->select(['accounting_student.id', 'concat(id_number," - ",first_name," ",middle_name," ",last_name," ",extension_name) as name'])
                    ->leftJoin('accounting_student_branch_program','accounting_student_branch_program.student_id = accounting_student.id')
                    ->leftJoin('accounting_branch_program','accounting_student_branch_program.branch_id = accounting_branch_program.branch_id and accounting_student_branch_program.program_id = accounting_branch_program.program_id')
                    ->where(['like','id_number', $q])
                    ->orWhere(['like','first_name', $q])
                    ->orWhere(['like','middle_name', $q])
                    ->orWhere(['like','last_name', $q])
                    ->orWhere(['like','extension_name', $q])
                    ->andWhere(['status' => 'Active'])
                    ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                    ->limit(20)
                    ->asArray()
                    ->all() : Student::find()
                    ->select(['accounting_student.id', 'concat(id_number," - ",first_name," ",middle_name," ",last_name," ",extension_name) as name'])
                    ->leftJoin('accounting_student_branch_program','accounting_student_branch_program.student_id = accounting_student.id')
                    ->leftJoin('accounting_branch_program','accounting_student_branch_program.branch_id = accounting_branch_program.branch_id and accounting_student_branch_program.program_id = accounting_branch_program.program_id')
                    ->where(['like','id_number', $q])
                    ->orWhere(['like','first_name', $q])
                    ->orWhere(['like','middle_name', $q])
                    ->orWhere(['like','last_name', $q])
                    ->orWhere(['like','extension_name', $q])
                    ->andWhere(['status' => 'Active'])
                    ->limit(20)
                    ->asArray()
                    ->all() : Student::find()
                    ->select(['accounting_student.id', 'concat(id_number," - ",first_name," ",middle_name," ",last_name," ",extension_name) as name'])
                    ->leftJoin('accounting_student_branch_program','accounting_student_branch_program.student_id = accounting_student.id')
                    ->leftJoin('accounting_branch_program','accounting_student_branch_program.branch_id = accounting_branch_program.branch_id and accounting_student_branch_program.program_id = accounting_branch_program.program_id')
                    ->where(['like','id_number', $q])
                    ->orWhere(['like','first_name', $q])
                    ->orWhere(['like','middle_name', $q])
                    ->orWhere(['like','last_name', $q])
                    ->orWhere(['like','extension_name', $q])
                    ->andWhere(['status' => 'Active'])
                    ->limit(20)
                    ->asArray()
                    ->all();
            }else{
                $names = $access ? $access->branch_program_id!= '' ? Student::find()
                    ->select(['accounting_student.id', 'concat(id_number," - ",first_name," ",middle_name," ",last_name," ",extension_name) as name'])
                    ->leftJoin('accounting_student_branch_program','accounting_student_branch_program.student_id = accounting_student.id')
                    ->leftJoin('accounting_branch_program','accounting_student_branch_program.branch_id = accounting_branch_program.branch_id and accounting_student_branch_program.program_id = accounting_branch_program.program_id')
                    ->where(['like','id_number', $q])
                    ->orWhere(['like','first_name', $q])
                    ->orWhere(['like','middle_name', $q])
                    ->orWhere(['like','last_name', $q])
                    ->orWhere(['like','extension_name', $q])
                    ->andWhere(['status' => 'Active'])
                    ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                    ->andWhere(['accounting_student_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                    ->limit(20)
                    ->asArray()
                    ->all() : Student::find()
                    ->select(['accounting_student.id', 'concat(id_number," - ",first_name," ",middle_name," ",last_name," ",extension_name) as name'])
                    ->leftJoin('accounting_student_branch_program','accounting_student_branch_program.student_id = accounting_student.id')
                    ->leftJoin('accounting_branch_program','accounting_student_branch_program.branch_id = accounting_branch_program.branch_id and accounting_student_branch_program.program_id = accounting_branch_program.program_id')
                    ->where(['like','id_number', $q])
                    ->orWhere(['like','first_name', $q])
                    ->orWhere(['like','middle_name', $q])
                    ->orWhere(['like','last_name', $q])
                    ->orWhere(['like','extension_name', $q])
                    ->andWhere(['status' => 'Active'])
                    ->andWhere(['accounting_student_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                    ->limit(20)
                    ->asArray()
                    ->all() : Student::find()
                    ->select(['accounting_student.id', 'concat(id_number," - ",first_name," ",middle_name," ",last_name," ",extension_name) as name'])
                    ->leftJoin('accounting_student_branch_program','accounting_student_branch_program.student_id = accounting_student.id')
                    ->leftJoin('accounting_branch_program','accounting_student_branch_program.branch_id = accounting_branch_program.branch_id and accounting_student_branch_program.program_id = accounting_branch_program.program_id')
                    ->where(['like','id_number', $q])
                    ->orWhere(['like','first_name', $q])
                    ->orWhere(['like','middle_name', $q])
                    ->orWhere(['like','last_name', $q])
                    ->orWhere(['like','extension_name', $q])
                    ->andWhere(['status' => 'Active'])
                    ->andWhere(['accounting_student_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                    ->limit(20)
                    ->asArray()
                    ->all();
            }

            $out['results'] = array_values($names);
        }
        elseif ($id > 0) {
            $out['results'] = ['id' => $id, 'name' => Student::find($id)->first_name.' '.Student::find($id)->middle_name.' '.Student::find($id)->last_name.' '.Student::find($id)->extension_name];
        }
        return $out;
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

    public function actionCheckGc($code)
    {
        return Discount::findOne(['code_number' => $code]) ? 1 : 0;
    }

    public function actionShowSeason($id)
    {
        $model = Season::findOne($id);
        $enhancement = BranchProgramEnhancement::find()->where(['branch_program_id' => $model->branchProgram->id])->one();
        $lastNo = StudentEnroleeType::find()->where(['season_id' => $model->id])->count();
        $lastNo = str_pad($lastNo + 1, 4, 0, STR_PAD_LEFT);

        return $this->renderAjax('_show-season',[
            'model' => $model,
            'enhancement' => $enhancement,
            'lastNo' => $lastNo,
        ]);
    }

    public function actionShowAdvanceEnrolment($id)
    {
        $model = AdvanceEnrolment::findOne($id);
        $income = IncomeEnrolment::find()->where(['season_id' => $model->season_id, 'student_id' => $model->student_id])->one();
        $student = Student::findOne($model->student_id);
        $season = Season::findOne($model->season_id);
        $enhancement = BranchProgramEnhancement::find()->where(['branch_program_id' => $season->branchProgram->id])->one();

        return $this->renderAjax('_show-advance-enrolment',[
            'model' => $model,
            'student' => $student,
            'season' => $season,
            'enhancement' => $enhancement,
            'income' => $income,
        ]);
    }

    public function actionShowPackage($id)
    {
        $model = Package::findOne($id);

        return $this->renderAjax('_show-package',[
            'model' => $model
        ]);
    }

    public function actionShowCoaching($id)
    {
        $model = Package::findOne($id);

        return $this->renderAjax('_show-coaching',[
            'model' => $model
        ]);
    }

    public function actionShowAdvanceEnrolmentTable($id)
    {
        $model = Student::findOne($id);

        return $this->renderAjax('_advance_enrolment_table',[
            'model' => $model
        ]);
    }

    public function actionShowBalance($id, $season_id)
    {
        $model = $this->findModel($id);
        $season = Season::findOne($season_id);
        $paymentEnrolments = IncomeEnrolment::find()
                            ->select([
                                'accounting_income.amount_type as amount_type',
                                'accounting_income.datetime as datetime',
                                'accounting_income_enrolment.or_no as or_no',
                                'concat(accounting_income_code.name) as code',
                                'accounting_income_enrolment.amount as amount'
                            ])
                            ->leftJoin('accounting_income', 'accounting_income.income_id = accounting_income_enrolment.id and accounting_income.income_type_id = 1')
                            ->leftJoin('accounting_income_code', 'accounting_income_code.id = accounting_income_enrolment.code_id')
                            ->where(['student_id' => $model->id, 'season_id' => $season->id])
                            ->asArray()
                            ->orderBy(['datetime' => SORT_DESC])
                            ->all();

        $paymentFreebies = FreebieAndIcon::find()
                            ->select([
                                'accounting_income.amount_type as amount_type',
                                'accounting_income.datetime as datetime',
                                'accounting_income_freebies_and_icons.pr as or_no',
                                'concat(accounting_income_code.name) as code',
                                'accounting_income_freebies_and_icons.amount as amount'
                            ])
                            ->leftJoin('accounting_income', 'accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                            ->leftJoin('accounting_income_code', 'accounting_income_code.id = accounting_income_freebies_and_icons.code_id')
                            ->where(['student_id' => $model->id, 'season_id' => $season->id])
                            ->andWhere(['<>', 'accounting_income_code.name', 'CWI'])
                            ->asArray()
                            ->orderBy(['datetime' => SORT_DESC])
                            ->all();

        $paymentIcons = FreebieAndIcon::find()
                            ->select([
                                'accounting_income.amount_type as amount_type',
                                'accounting_income.datetime as datetime',
                                'accounting_income_freebies_and_icons.pr as or_no',
                                'concat(accounting_income_code.name) as code',
                                'accounting_income_freebies_and_icons.amount as amount'
                            ])
                            ->leftJoin('accounting_income', 'accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                            ->leftJoin('accounting_income_code', 'accounting_income_code.id = accounting_income_freebies_and_icons.code_id')
                            ->where(['student_id' => $model->id, 'season_id' => $season->id])
                            ->andWhere(['accounting_income_code.name' => 'CWI'])
                            ->asArray()
                            ->orderBy(['datetime' => SORT_DESC])
                            ->all();

        $payments = array_merge($paymentEnrolments, $paymentFreebies, $paymentIcons);

        arsort($payments);

        $studentTuition = StudentTuition::find()
                        ->select([
                            'accounting_package_student.amount as packageAmount',
                            'accounting_discount.amount as discountAmount',
                            'accounting_enhancement.amount as enhancementAmount'
                        ])
                        ->leftJoin('accounting_package_student','accounting_package_student.id = accounting_student_tuition.package_student_id')
                        ->leftJoin('accounting_enhancement','accounting_enhancement.id = accounting_student_tuition.enhancement_id')
                        ->leftJoin('accounting_discount','accounting_discount.id = accounting_student_tuition.discount_id')
                        ->where(['accounting_student_tuition.student_id' => $model->id])
                        ->andwhere(['accounting_student_tuition.season_id' => $season->id])
                        ->asArray()
                        ->one();

        $coaching = Coaching::find()
                    ->select([
                        'accounting_package.amount as amount'
                    ])
                    ->leftJoin('accounting_package','accounting_package.id = accounting_coaching.package_id')
                    ->where(['accounting_coaching.student_id' => $model->id, 'accounting_coaching.season_id' => $season->id])
                    ->asArray()
                    ->one();

        return $this->renderAjax('_show-balance', [
            'model' => $model,
            'season' => $season,
            'paymentEnrolments' => $paymentEnrolments,
            'paymentFreebies' => $paymentFreebies,
            'paymentIcons' => $paymentIcons,
            'studentTuition' => $studentTuition,
            'payments' => $payments,
            'coaching' => $coaching,
        ]);
    }
    
    function takeOrNow($id)
    {
        $orList = SeasonOrList::find()
                ->select([
                    'accounting_season_or_list.id as id',
                    'IF(count(accounting_season_or.id)<accounting_season_or_list.no_of_pieces,"Yes","No") as availability'
                ])
                ->leftJoin('accounting_season_or','accounting_season_or.season_or_list_id = accounting_season_or_list.id')
                ->where(['accounting_season_or_list.season_id' => $id])
                ->orderBy(['accounting_season_or_list.id' => SORT_ASC])
                ->groupBy(['accounting_season_or_list.id'])
                ->having(['availability' => 'Yes'])
                ->asArray()
                ->one();

        if(!empty($orList))
        {
            $or = SeasonOr::find()->where(['season_or_list_id' => $orList['id']])->orderBy(['id' => SORT_DESC])->one();
            $orCount = SeasonOr::find()->where(['season_or_list_id' => $orList['id']])->count();
            $season = SeasonOrList::findOne($orList['id']);

            if($orCount < $season->no_of_pieces)
            {
                if($or)
                {
                    $no_of_places = strlen($or->or_no);
                    $current_or = intval($or->or_no);
                    $current_or = str_pad($current_or + 1, $no_of_places, 0, STR_PAD_LEFT);

                    return $current_or;
                }else{
                    $season = SeasonOrList::findOne($orList['id']);

                    return $season->or_start;
                }
            }else{
                return 'No Available OR';
            }
        }else{
            return 'No Available OR';
        }
    }

    function actionTakeOr($id)
    {
        $orValue = '';
        $orList = SeasonOrList::find()
                ->select([
                    'accounting_season_or_list.id as id',
                    'IF(count(accounting_season_or.id)<accounting_season_or_list.no_of_pieces,"Yes","No") as availability'
                ])
                ->leftJoin('accounting_season_or','accounting_season_or.season_or_list_id = accounting_season_or_list.id')
                ->where(['accounting_season_or_list.season_id' => $id])
                ->orderBy(['accounting_season_or_list.id' => SORT_ASC])
                ->groupBy(['accounting_season_or_list.id'])
                ->having(['availability' => 'Yes'])
                ->asArray()
                ->one();

        if(!empty($orList))
        {
            $or = SeasonOr::find()->where(['season_or_list_id' => $orList['id']])->orderBy(['id' => SORT_DESC])->one();
            $orCount = SeasonOr::find()->where(['season_or_list_id' => $orList['id']])->count();
            $season = SeasonOrList::findOne($orList['id']);

            if($orCount < $season->no_of_pieces)
            {
                if($or)
                {
                    $no_of_places = strlen($or->or_no);
                    $current_or = intval($or->or_no);
                    $current_or = str_pad($current_or + 1, $no_of_places, 0, STR_PAD_LEFT);

                    return $current_or;
                }else{
                    $season = SeasonOrList::findOne($orList['id']);

                   $orValue = $season->or_start;
                }
            }else{
                $orValue = 'No Available OR';
            }
        }else{
            $orValue = 'No Available OR';
        }

        $seasoning = Season::findOne(['id' => $id]);
        if($seasoning)
        {
            $notification = Notification::findOne(['model' => 'Season', 'model_id' => $id]) ? Notification::findOne(['model' => 'Season', 'model_id' => $id]) : new Notification(); 
            if($orValue == 'No Available OR')
            {
                $notification->model = 'Season';
                $notification->model_id = $id;
                $notification->message = $seasoning->seasonName.' has used all the official receipts. Add official receipts immediately to continue operation.';
                $notification->save();
            }
        }
        

        return $orValue;
    }

    function takeOrList($id)
    {
        $orList = SeasonOrList::find()
                ->select([
                    'accounting_season_or_list.id as id',
                    'IF(count(accounting_season_or.id)<accounting_season_or_list.no_of_pieces,"Yes","No") as availability'
                ])
                ->leftJoin('accounting_season_or','accounting_season_or.season_or_list_id = accounting_season_or_list.id')
                ->where(['accounting_season_or_list.season_id' => $id])
                ->orderBy(['accounting_season_or_list.id' => SORT_ASC])
                ->groupBy(['accounting_season_or_list.id'])
                ->having(['availability' => 'Yes'])
                ->asArray()
                ->one();

        return !empty($orList) ? $orList['id'] : '';
    }

    function takeOrStatus($id)
    {
        $orList = SeasonOrList::find()
                ->select([
                    'accounting_season_or_list.id as id',
                    'IF(count(accounting_season_or.id)<accounting_season_or_list.no_of_pieces,"Yes","No") as availability'
                ])
                ->leftJoin('accounting_season_or','accounting_season_or.season_or_list_id = accounting_season_or_list.id')
                ->where(['accounting_season_or_list.season_id' => $id])
                ->orderBy(['accounting_season_or_list.id' => SORT_ASC])
                ->groupBy(['accounting_season_or_list.id'])
                ->having(['availability' => 'Yes'])
                ->asArray()
                ->one();

        if(!empty($orList))
        {
            $or = SeasonOr::find()->where(['season_or_list_id' => $orList['id']])->orderBy(['id' => SORT_DESC])->one();
            $orCount = SeasonOr::find()->where(['season_or_list_id' => $orList['id']])->count();
            $season = SeasonOrList::findOne($orList['id']);

            if($orCount < $season->no_of_pieces)
            {
                return 1;
            }else{
                return 0;
            }
        }else{
            return 0;
        }
    }   

    /**
     * Lists all Student models.
     * @return mixed
     */
    public function actionIndex()
    {   

        $model = new Student();

        $user_info = Yii::$app->user->identity->userinfo;
        $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
        $rolenames =  ArrayHelper::map($roles, 'name','name');

        $access = AccessProgram::findOne(['user_id' => Yii::$app->user->identity->userinfo->user_id]);

        if(in_array('TopManagement',$rolenames)){
            $seasons = $access ? $access->branch_program_id != '' ? Season::find()
                   ->select([
                    'accounting_season.id as id',
                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name'
                   ])
                   ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                   ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                   ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                   ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                   ->asArray()
                   ->orderBy(['accounting_season.end_date' => SORT_DESC, 'name' => SORT_ASC])
                   ->all() : Season::find()
                   ->select([
                    'accounting_season.id as id',
                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name'
                   ])
                   ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                   ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                   ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                   ->asArray()
                   ->orderBy(['accounting_season.end_date' => SORT_DESC, 'name' => SORT_ASC])
                   ->all() : Season::find()
                   ->select([
                    'accounting_season.id as id',
                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name'
                   ])
                   ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                   ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                   ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                   ->asArray()
                   ->orderBy(['accounting_season.end_date' => SORT_DESC, 'name' => SORT_ASC])
                   ->all();
        }else{
            $seasons = $access ? $access->branch_program_id != '' ? Season::find()
                   ->select([
                    'accounting_season.id as id',
                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name'
                   ])
                   ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                   ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                   ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                   ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                   ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                   ->asArray()
                   ->orderBy(['accounting_season.end_date' => SORT_DESC, 'name' => SORT_ASC])
                   ->all() : Season::find()
                   ->select([
                    'accounting_season.id as id',
                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name'
                   ])
                   ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                   ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                   ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                   ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                   ->asArray()
                   ->orderBy(['accounting_season.end_date' => SORT_DESC, 'name' => SORT_ASC])
                   ->all() : Season::find()
                   ->select([
                    'accounting_season.id as id',
                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name'
                   ])
                   ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                   ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                   ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                   ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                   ->asArray()
                   ->orderBy(['accounting_season.end_date' => SORT_DESC, 'name' => SORT_ASC])
                   ->all();
        }

        $seasons = ArrayHelper::map($seasons, 'id', 'name');

        return $this->render('index', [
            'model' => $model,
            'seasons' => $seasons,
        ]);
    }

    public function actionList()
    {
        $model = new Student();
        $enroleeTypeModel = new StudentEnroleeType();
        $searchModel = new StudentSearch();
        $searchModel->status = 'Active';
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);   

        $user_info = Yii::$app->user->identity->userinfo;
        $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
        $rolenames =  ArrayHelper::map($roles, 'name','name');

        $access = AccessProgram::findOne(['user_id' => Yii::$app->user->identity->userinfo->user_id]);

        if(in_array('TopManagement',$rolenames)){
            $seasons = $access ? $access->branch_program_id != '' ? Season::find()
                   ->select([
                    'accounting_season.id as id',
                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name'
                   ])
                   ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                   ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                   ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                   ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                   ->asArray()
                   ->orderBy(['accounting_season.end_date' => SORT_DESC, 'name' => SORT_ASC])
                   ->all() : Season::find()
                   ->select([
                    'accounting_season.id as id',
                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name'
                   ])
                   ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                   ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                   ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                   ->asArray()
                   ->orderBy(['accounting_season.end_date' => SORT_DESC, 'name' => SORT_ASC])
                   ->all() : Season::find()
                   ->select([
                    'accounting_season.id as id',
                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name'
                   ])
                   ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                   ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                   ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                   ->asArray()
                   ->orderBy(['accounting_season.end_date' => SORT_DESC, 'name' => SORT_ASC])
                   ->all();
        }else{
            $seasons = $access ? $access->branch_program_id != '' ? Season::find()
                   ->select([
                    'accounting_season.id as id',
                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name'
                   ])
                   ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                   ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                   ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                   ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                   ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                   ->asArray()
                   ->orderBy(['accounting_season.end_date' => SORT_DESC, 'name' => SORT_ASC])
                   ->all() : Season::find()
                   ->select([
                    'accounting_season.id as id',
                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name'
                   ])
                   ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                   ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                   ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                   ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                   ->asArray()
                   ->orderBy(['accounting_season.end_date' => SORT_DESC, 'name' => SORT_ASC])
                   ->all() : Season::find()
                   ->select([
                    'accounting_season.id as id',
                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name'
                   ])
                   ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                   ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                   ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                   ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                   ->asArray()
                   ->orderBy(['accounting_season.end_date' => SORT_DESC, 'name' => SORT_ASC])
                   ->all();
        }

        $seasons = ArrayHelper::map($seasons, 'id', 'name');

        return $this->render('_list', [
            'model' => $model,
            'enroleeTypeModel' => $enroleeTypeModel,
            'seasons' => $seasons,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider
        ]);
    }

    public function actionSearchStudentInformation()
    {
        $model = new Student();
        $model->scenario = 'searchStudent';

        $user_info = Yii::$app->user->identity->userinfo;
        $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
        $rolenames =  ArrayHelper::map($roles, 'name','name');

        $access = AccessProgram::findOne(['user_id' => Yii::$app->user->identity->userinfo->user_id]);

        if(in_array('TopManagement',$rolenames)){
            $seasons = $access ? $access->branch_program_id != '' ? Season::find()
                   ->select([
                    'accounting_season.id as id',
                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name'
                   ])
                   ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                   ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                   ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                   ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                   ->asArray()
                   ->orderBy(['name' => SORT_ASC])
                   ->all() : Season::find()
                   ->select([
                    'accounting_season.id as id',
                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name'
                   ])
                   ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                   ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                   ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                   ->asArray()
                   ->orderBy(['name' => SORT_ASC])
                   ->all() : Season::find()
                   ->select([
                    'accounting_season.id as id',
                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name'
                   ])
                   ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                   ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                   ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                   ->asArray()
                   ->orderBy(['name' => SORT_ASC])
                   ->all();
        }else{
            $seasons = $access ? $access->branch_program_id != '' ? Season::find()
                   ->select([
                    'accounting_season.id as id',
                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name'
                   ])
                   ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                   ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                   ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                   ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                   ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                   ->asArray()
                   ->orderBy(['name' => SORT_ASC])
                   ->all() : Season::find()
                   ->select([
                    'accounting_season.id as id',
                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name'
                   ])
                   ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                   ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                   ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                   ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                   ->asArray()
                   ->orderBy(['name' => SORT_ASC])
                   ->all() : Season::find()
                   ->select([
                    'accounting_season.id as id',
                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name'
                   ])
                   ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                   ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                   ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                   ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                   ->asArray()
                   ->orderBy(['name' => SORT_ASC])
                   ->all();
        }

        $seasons = ArrayHelper::map($seasons, 'id', 'name');

        $fields = [
            'accounting_student.id_number' => 'ID NUMBER',
            'concat(accounting_student.last_name," ",accounting_student.extension_name) as lastName' => 'LAST NAME',
            'accounting_student.first_name' => 'FIRST NAME',
            'accounting_student.middle_name' => 'MIDDLE NAME',
            'accounting_school.name as schoolName' => 'SCHOOL',
            'accounting_school.location as schoolLocation' => 'LOCATION OF SCHOOL',
            'accounting_student.year_graduated' => 'YEAR GRADUATED',
            'tblprovince.province_m as provinceName' => 'PROVINCE',
            'tblcitymun.citymun_m as citymunName' => 'CITY/MUNICIPALITY',
            'accounting_student.permanent_address' => 'PERMANENT ADDRESS',
            'accounting_student.contact_no' => 'CONTACT NO.',
            'accounting_student.birthday' => 'BIRTHDAY',
            'accounting_student.prc' => 'PRC APPLICATION NO.',
            'accounting_student.email_address' => 'EMAIL ADDRESS',
        ];

        $pages = [];

        if(Yii::$app->request->post())
        {
            $postData = Yii::$app->request->post()['Student'];

            $season = Season::findOne($postData['season_id']);

            $limit = 1000;

            if(in_array('TopManagement',$rolenames)){
                $data = $access ? $access->branch_program_id!= '' ? StudentTuition::find()
                    ->select($postData['field_id'])
                    ->leftJoin('accounting_student', 'accounting_student.id = accounting_student_tuition.student_id')
                    ->leftJoin('tblprovince', 'tblprovince.province_c = accounting_student.province_id')
                    ->leftJoin('tblcitymun', 'tblcitymun.province_c = accounting_student.province_id and tblcitymun.citymun_c = accounting_student.citymun_id')
                    ->leftJoin('accounting_school', 'accounting_school.id = accounting_student.school_id')
                    ->leftJoin('accounting_season','accounting_season.id = accounting_student_tuition.season_id')
                    ->andWhere(['accounting_student.status' => 'Active'])
                    ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                    ->andWhere(['accounting_season.id' => $postData['season_id']])
                    ->asArray()
                    ->limit($limit)
                    ->offset($postData['page_id'])
                    ->all() : StudentTuition::find()
                    ->select($postData['field_id'])
                    ->leftJoin('accounting_student', 'accounting_student.id = accounting_student_tuition.student_id')
                    ->leftJoin('tblprovince', 'tblprovince.province_c = accounting_student.province_id')
                    ->leftJoin('tblcitymun', 'tblcitymun.province_c = accounting_student.province_id and tblcitymun.citymun_c = accounting_student.citymun_id')
                    ->leftJoin('accounting_school', 'accounting_school.id = accounting_student.school_id')
                    ->leftJoin('accounting_season','accounting_season.id = accounting_student_tuition.season_id')
                    ->andWhere(['accounting_student.status' => 'Active'])
                    ->andWhere(['accounting_season.id' => $postData['season_id']])
                    ->asArray()
                    ->limit($limit)
                    ->offset($postData['page_id'])
                    ->all() : StudentTuition::find()
                    ->select($postData['field_id'])
                    ->leftJoin('accounting_student', 'accounting_student.id = accounting_student_tuition.student_id')
                    ->leftJoin('tblprovince', 'tblprovince.province_c = accounting_student.province_id')
                    ->leftJoin('tblcitymun', 'tblcitymun.province_c = accounting_student.province_id and tblcitymun.citymun_c = accounting_student.citymun_id')
                    ->leftJoin('accounting_school', 'accounting_school.id = accounting_student.school_id')
                    ->leftJoin('accounting_season','accounting_season.id = accounting_student_tuition.season_id')
                    ->andWhere(['accounting_student.status' => 'Active'])
                    ->andWhere(['accounting_season.id' => $postData['season_id']])
                    ->asArray()
                    ->limit($limit)
                    ->offset($postData['page_id'])
                    ->all();
            }else{
                $data = $access ? $access->branch_program_id!= '' ? StudentTuition::find()
                    ->select($postData['field_id'])
                    ->leftJoin('accounting_student', 'accounting_student.id = accounting_student_tuition.student_id')
                    ->leftJoin('tblprovince', 'tblprovince.province_c = accounting_student.province_id')
                    ->leftJoin('tblcitymun', 'tblcitymun.province_c = accounting_student.province_id and tblcitymun.citymun_c = accounting_student.citymun_id')
                    ->leftJoin('accounting_school', 'accounting_school.id = accounting_student.school_id')
                    ->leftJoin('accounting_season','accounting_season.id = accounting_student_tuition.season_id')
                    ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                    ->andWhere(['accounting_student.status' => 'Active'])
                    ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                    ->andWhere(['accounting_season.id' => $postData['season_id']])
                    ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                    ->asArray()
                    ->limit($limit)
                    ->offset($postData['page_id'])
                    ->all() : StudentTuition::find()
                    ->select($postData['field_id'])
                    ->leftJoin('accounting_student', 'accounting_student.id = accounting_student_tuition.student_id')
                    ->leftJoin('tblprovince', 'tblprovince.province_c = accounting_student.province_id')
                    ->leftJoin('tblcitymun', 'tblcitymun.province_c = accounting_student.province_id and tblcitymun.citymun_c = accounting_student.citymun_id')
                    ->leftJoin('accounting_school', 'accounting_school.id = accounting_student.school_id')
                    ->leftJoin('accounting_season','accounting_season.id = accounting_student_tuition.season_id')
                    ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                    ->andWhere(['accounting_student.status' => 'Active'])
                    ->andWhere(['accounting_season.id' => $postData['season_id']])
                    ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                    ->asArray()
                    ->limit($limit)
                    ->offset($postData['page_id'])
                    ->all() : StudentTuition::find()
                    ->select($postData['field_id'])
                    ->leftJoin('accounting_student', 'accounting_student.id = accounting_student_tuition.student_id')
                    ->leftJoin('tblprovince', 'tblprovince.province_c = accounting_student.province_id')
                    ->leftJoin('tblcitymun', 'tblcitymun.province_c = accounting_student.province_id and tblcitymun.citymun_c = accounting_student.citymun_id')
                    ->leftJoin('accounting_school', 'accounting_school.id = accounting_student.school_id')
                    ->leftJoin('accounting_season','accounting_season.id = accounting_student_tuition.season_id')
                    ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                    ->andWhere(['accounting_student.status' => 'Active'])
                    ->andWhere(['accounting_season.id' => $postData['season_id']])
                    ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                    ->asArray()
                    ->limit($limit)
                    ->offset($postData['page_id'])
                    ->all();
            }

            $pages = ceil(count($data) / $limit);

            $content = $this->renderPartial('_student-information', [
                'data' => $data,
                'season' => $season,
                'fields' => $postData['field_id'],
            ]);

            $pdf = new Pdf([
            'mode' => Pdf::MODE_CORE,
            'format' => Pdf::FORMAT_LEGAL, 
            'orientation' => Pdf::ORIENT_LANDSCAPE, 
            'destination' => Pdf::DEST_DOWNLOAD, 
            'filename' => 'Student Information - '.$season->seasonName.' - Page '.($postData['page_id']+1).' of '.$pages.'.pdf',
            'content' => $content,  
            'marginLeft' => 11.4,
            'marginRight' => 11.4,
            //'cssFile' => '@vendor/kartik-v/yii2-mpdf/assets/kv-mpdf-bootstrap.min.css',
            'cssInline' => 'th { text-align:center; 
                                      padding:6px 6px 6px 6px;
                                      font-size:12px;
                                      }
                            .title {
                                      font-size:16px;
                                      font-weight:bold;
                                      }
                            td { padding:8px 4px 4px 4px; font-size:12px;
                          }', 
            'options' => ['title' => 'Student Information'],
            'methods' => [ 
                'SetHeader'=>['Student Information - '.$season->seasonName.' - Page '.($postData['page_id']+1).' of '.$pages], 
                'SetFooter'=>['Page {PAGENO}'],
            ]
            ]);
            $response = Yii::$app->response;
            $response->format = \yii\web\Response::FORMAT_RAW;
            $headers = Yii::$app->response->headers;
            $headers->add('Content-Type', 'application/pdf');
            return $pdf->render();   
        }

        return $this->renderAjax('_search-student-information',[
            'model' => $model,
            'seasons' => $seasons,
            'fields' => $fields,
            'pages' => $pages,
        ]);
    }

    public function actionSearchPaymentUpdates()
    {
        $model = new Student();
        $model->scenario = 'searchPayment';

        $user_info = Yii::$app->user->identity->userinfo;
        $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
        $rolenames =  ArrayHelper::map($roles, 'name','name');

        $access = AccessProgram::findOne(['user_id' => Yii::$app->user->identity->userinfo->user_id]);

        if(in_array('TopManagement',$rolenames)){
            $seasons = $access ? $access->branch_program_id != '' ? Season::find()
                   ->select([
                    'accounting_season.id as id',
                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name'
                   ])
                   ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                   ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                   ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                   ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                   ->asArray()
                   ->orderBy(['name' => SORT_ASC])
                   ->all() : Season::find()
                   ->select([
                    'accounting_season.id as id',
                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name'
                   ])
                   ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                   ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                   ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                   ->asArray()
                   ->orderBy(['name' => SORT_ASC])
                   ->all() : Season::find()
                   ->select([
                    'accounting_season.id as id',
                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name'
                   ])
                   ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                   ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                   ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                   ->asArray()
                   ->orderBy(['name' => SORT_ASC])
                   ->all();
        }else{
            $seasons = $access ? $access->branch_program_id != '' ? Season::find()
                   ->select([
                    'accounting_season.id as id',
                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name'
                   ])
                   ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                   ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                   ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                   ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                   ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                   ->asArray()
                   ->orderBy(['name' => SORT_ASC])
                   ->all() : Season::find()
                   ->select([
                    'accounting_season.id as id',
                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name'
                   ])
                   ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                   ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                   ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                   ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                   ->asArray()
                   ->orderBy(['name' => SORT_ASC])
                   ->all() : Season::find()
                   ->select([
                    'accounting_season.id as id',
                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name'
                   ])
                   ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                   ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                   ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                   ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                   ->asArray()
                   ->orderBy(['name' => SORT_ASC])
                   ->all();
        }

        $seasons = ArrayHelper::map($seasons, 'id', 'name');

        $pages = [];

        if(Yii::$app->request->post())
        {
            $postData = Yii::$app->request->post()['Student'];

            $season = Season::findOne($postData['season_id']);

            $limit = 1000;

            if(in_array('TopManagement',$rolenames)){
                $data = $access ? $access->branch_program_id!= '' ? StudentTuition::find()
                      ->select([
                        'accounting_student.id_number',
                        'concat(accounting_student.last_name," ",accounting_student.extension_name) as lastName',
                        'accounting_student.first_name',
                        'accounting_student.middle_name',
                        'accounting_package_student.amount as packageAmount',
                        'accounting_package.code as packageName',
                        'accounting_enrolee_type.name as enroleeTypeName',
                        'accounting_discount.amount as discountAmount',
                        'accounting_discount_type.name as discountType',
                        'accounting_enhancement.amount as enhancementAmount',
                        'accounting_coaching.amount as coachingAmount',
                        'incomeEnrolments.totalAmount as incomeEnrolmentsAmount',
                        'freebies.totalAmount as freebiesAmount',
                        '((COALESCE(accounting_package_student.amount,0) + COALESCE(accounting_enhancement.amount,0) + COALESCE(accounting_coaching.amount,0)) - COALESCE(accounting_discount.amount,0)) as finalTuitionFee',
                        '((COALESCE(accounting_package_student.amount,0) + COALESCE(accounting_enhancement.amount,0) + COALESCE(accounting_coaching.amount,0)) - COALESCE(accounting_discount.amount,0)) - (COALESCE(incomeEnrolments.totalAmount, 0) + COALESCE(freebies.totalAmount, 0)) as balanceAmount',
                        'IF(((COALESCE(accounting_package_student.amount,0) + COALESCE(accounting_enhancement.amount,0) + COALESCE(accounting_coaching.amount,0)) - COALESCE(accounting_discount.amount,0)) - (COALESCE(incomeEnrolments.totalAmount, 0) + COALESCE(freebies.totalAmount, 0)) > 0, "With Balance", "Cleared") as balanceStatus'
                      ])
                      ->leftJoin('accounting_student', 'accounting_student.id = accounting_student_tuition.student_id')
                      ->leftJoin('accounting_package_student', 'accounting_package_student.id = accounting_student_tuition.package_student_id')
                      ->leftJoin('accounting_package', 'accounting_package.id = accounting_package_student.package_id')
                      ->leftJoin('accounting_season', 'accounting_season.id = accounting_student_tuition.season_id')
                      ->leftJoin('accounting_coaching', 'accounting_coaching.season_id = accounting_season.id and accounting_coaching.student_id = accounting_student_tuition.student_id')
                      ->leftJoin('accounting_student_enrolee_type', 'accounting_student_enrolee_type.season_id = accounting_student_tuition.season_id and accounting_student_enrolee_type.student_id = accounting_student_tuition.student_id')
                      ->leftJoin('accounting_enhancement', 'accounting_enhancement.season_id = accounting_student_tuition.season_id and accounting_enhancement.student_id = accounting_student_tuition.student_id')
                      ->leftJoin('accounting_discount', 'accounting_discount.season_id = accounting_student_tuition.season_id and accounting_discount.student_id = accounting_student_tuition.student_id')
                      ->leftJoin('accounting_discount_type', 'accounting_discount_type.id = accounting_discount.discount_type_id')
                      ->leftJoin('accounting_enrolee_type', 'accounting_enrolee_type.id = accounting_student_enrolee_type.enrolee_type_id')
                      ->leftJoin('accounting_branch_program', 'accounting_branch_program.id = accounting_season.branch_program_id')
                      ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                      ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                      ->leftJoin(['incomeEnrolments' => '(
                        SELECT 
                            season_id,
                            student_id,
                            sum(amount) as totalAmount
                        from accounting_income_enrolment
                        group by season_id, student_id
                        )'], 'incomeEnrolments.season_id = accounting_student_tuition.season_id and incomeEnrolments.student_id = accounting_student_tuition.student_id')
                      ->leftJoin(['freebies' => '(
                        SELECT 
                            season_id,
                            student_id,
                            sum(amount) as totalAmount
                        from accounting_income_freebies_and_icons
                        group by season_id, student_id
                        )'], 'freebies.season_id = accounting_student_tuition.season_id and freebies.student_id = accounting_student_tuition.student_id')
                      ->andWhere(['accounting_student_tuition.season_id' => $postData['season_id']])
                      ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                      ->orderBy(['lastName' => SORT_ASC, 'accounting_student.id_number' => SORT_ASC])
                      ->limit($limit)
                      ->offset($postData['page_id'])
                      ->asArray()
                      ->all() : StudentTuition::find()
                      ->select([
                        'accounting_student.id_number',
                        'concat(accounting_student.last_name," ",accounting_student.extension_name) as lastName',
                        'accounting_student.first_name',
                        'accounting_student.middle_name',
                        'accounting_package_student.amount as packageAmount',
                        'accounting_package.code as packageName',
                        'accounting_enrolee_type.name as enroleeTypeName',
                        'accounting_discount.amount as discountAmount',
                        'accounting_discount_type.name as discountType',
                        'accounting_enhancement.amount as enhancementAmount',
                        'accounting_coaching.amount as coachingAmount',
                        'incomeEnrolments.totalAmount as incomeEnrolmentsAmount',
                        'freebies.totalAmount as freebiesAmount',
                        '((COALESCE(accounting_package_student.amount,0) + COALESCE(accounting_enhancement.amount,0) + COALESCE(accounting_coaching.amount,0)) - COALESCE(accounting_discount.amount,0)) as finalTuitionFee',
                        '((COALESCE(accounting_package_student.amount,0) + COALESCE(accounting_enhancement.amount,0) + COALESCE(accounting_coaching.amount,0)) - COALESCE(accounting_discount.amount,0)) - (COALESCE(incomeEnrolments.totalAmount, 0) + COALESCE(freebies.totalAmount, 0)) as balanceAmount',
                        'IF(((COALESCE(accounting_package_student.amount,0) + COALESCE(accounting_enhancement.amount,0) + COALESCE(accounting_coaching.amount,0)) - COALESCE(accounting_discount.amount,0)) - (COALESCE(incomeEnrolments.totalAmount, 0) + COALESCE(freebies.totalAmount, 0)) > 0, "With Balance", "Cleared") as balanceStatus'
                      ])
                      ->leftJoin('accounting_student', 'accounting_student.id = accounting_student_tuition.student_id')
                      ->leftJoin('accounting_package_student', 'accounting_package_student.id = accounting_student_tuition.package_student_id')
                      ->leftJoin('accounting_package', 'accounting_package.id = accounting_package_student.package_id')
                      ->leftJoin('accounting_season', 'accounting_season.id = accounting_student_tuition.season_id')
                      ->leftJoin('accounting_coaching', 'accounting_coaching.season_id = accounting_season.id and accounting_coaching.student_id = accounting_student_tuition.student_id')
                      ->leftJoin('accounting_student_enrolee_type', 'accounting_student_enrolee_type.season_id = accounting_student_tuition.season_id and accounting_student_enrolee_type.student_id = accounting_student_tuition.student_id')
                      ->leftJoin('accounting_enhancement', 'accounting_enhancement.season_id = accounting_student_tuition.season_id and accounting_enhancement.student_id = accounting_student_tuition.student_id')
                      ->leftJoin('accounting_discount', 'accounting_discount.season_id = accounting_student_tuition.season_id and accounting_discount.student_id = accounting_student_tuition.student_id')
                      ->leftJoin('accounting_discount_type', 'accounting_discount_type.id = accounting_discount.discount_type_id')
                      ->leftJoin('accounting_enrolee_type', 'accounting_enrolee_type.id = accounting_student_enrolee_type.enrolee_type_id')
                      ->leftJoin('accounting_branch_program', 'accounting_branch_program.id = accounting_season.branch_program_id')
                      ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                      ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                      ->leftJoin(['incomeEnrolments' => '(
                        SELECT 
                            season_id,
                            student_id,
                            sum(amount) as totalAmount
                        from accounting_income_enrolment
                        group by season_id, student_id
                        )'], 'incomeEnrolments.season_id = accounting_student_tuition.season_id and incomeEnrolments.student_id = accounting_student_tuition.student_id')
                      ->leftJoin(['freebies' => '(
                        SELECT 
                            season_id,
                            student_id,
                            sum(amount) as totalAmount
                        from accounting_income_freebies_and_icons
                        group by season_id, student_id
                        )'], 'freebies.season_id = accounting_student_tuition.season_id and freebies.student_id = accounting_student_tuition.student_id')
                      ->andWhere(['accounting_student_tuition.season_id' => $postData['season_id']])
                      ->orderBy(['lastName' => SORT_ASC, 'accounting_student.id_number' => SORT_ASC])
                      ->limit($limit)
                      ->offset($postData['page_id'])
                      ->asArray()
                      ->all() : StudentTuition::find()
                      ->select([
                        'accounting_student.id_number',
                        'concat(accounting_student.last_name," ",accounting_student.extension_name) as lastName',
                        'accounting_student.first_name',
                        'accounting_student.middle_name',
                        'accounting_package_student.amount as packageAmount',
                        'accounting_package.code as packageName',
                        'accounting_enrolee_type.name as enroleeTypeName',
                        'accounting_discount.amount as discountAmount',
                        'accounting_discount_type.name as discountType',
                        'accounting_enhancement.amount as enhancementAmount',
                        'accounting_coaching.amount as coachingAmount',
                        'incomeEnrolments.totalAmount as incomeEnrolmentsAmount',
                        'freebies.totalAmount as freebiesAmount',
                        '((COALESCE(accounting_package_student.amount,0) + COALESCE(accounting_enhancement.amount,0) + COALESCE(accounting_coaching.amount,0)) - COALESCE(accounting_discount.amount,0)) as finalTuitionFee',
                        '((COALESCE(accounting_package_student.amount,0) + COALESCE(accounting_enhancement.amount,0) + COALESCE(accounting_coaching.amount,0)) - COALESCE(accounting_discount.amount,0)) - (COALESCE(incomeEnrolments.totalAmount, 0) + COALESCE(freebies.totalAmount, 0)) as balanceAmount',
                        'IF(((COALESCE(accounting_package_student.amount,0) + COALESCE(accounting_enhancement.amount,0) + COALESCE(accounting_coaching.amount,0)) - COALESCE(accounting_discount.amount,0)) - (COALESCE(incomeEnrolments.totalAmount, 0) + COALESCE(freebies.totalAmount, 0)) > 0, "With Balance", "Cleared") as balanceStatus'
                      ])
                      ->leftJoin('accounting_student', 'accounting_student.id = accounting_student_tuition.student_id')
                      ->leftJoin('accounting_package_student', 'accounting_package_student.id = accounting_student_tuition.package_student_id')
                      ->leftJoin('accounting_package', 'accounting_package.id = accounting_package_student.package_id')
                      ->leftJoin('accounting_season', 'accounting_season.id = accounting_student_tuition.season_id')
                      ->leftJoin('accounting_coaching', 'accounting_coaching.season_id = accounting_season.id and accounting_coaching.student_id = accounting_student_tuition.student_id')
                      ->leftJoin('accounting_student_enrolee_type', 'accounting_student_enrolee_type.season_id = accounting_student_tuition.season_id and accounting_student_enrolee_type.student_id = accounting_student_tuition.student_id')
                      ->leftJoin('accounting_enhancement', 'accounting_enhancement.season_id = accounting_student_tuition.season_id and accounting_enhancement.student_id = accounting_student_tuition.student_id')
                      ->leftJoin('accounting_discount', 'accounting_discount.season_id = accounting_student_tuition.season_id and accounting_discount.student_id = accounting_student_tuition.student_id')
                      ->leftJoin('accounting_discount_type', 'accounting_discount_type.id = accounting_discount.discount_type_id')
                      ->leftJoin('accounting_enrolee_type', 'accounting_enrolee_type.id = accounting_student_enrolee_type.enrolee_type_id')
                      ->leftJoin('accounting_branch_program', 'accounting_branch_program.id = accounting_season.branch_program_id')
                      ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                      ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                      ->leftJoin(['incomeEnrolments' => '(
                        SELECT 
                            season_id,
                            student_id,
                            sum(amount) as totalAmount
                        from accounting_income_enrolment
                        group by season_id, student_id
                        )'], 'incomeEnrolments.season_id = accounting_student_tuition.season_id and incomeEnrolments.student_id = accounting_student_tuition.student_id')
                      ->leftJoin(['freebies' => '(
                        SELECT 
                            season_id,
                            student_id,
                            sum(amount) as totalAmount
                        from accounting_income_freebies_and_icons
                        group by season_id, student_id
                        )'], 'freebies.season_id = accounting_student_tuition.season_id and freebies.student_id = accounting_student_tuition.student_id')
                      ->where(['accounting_student_tuition.season_id' => $postData['season_id']])
                      ->orderBy(['lastName' => SORT_ASC, 'accounting_student.id_number' => SORT_ASC])
                      ->limit($limit)
                      ->offset($postData['page_id'])
                      ->asArray()
                      ->all();
            }else{
                $data = $access ? $access->branch_program_id!= '' ? StudentTuition::find()
                      ->select([
                        'accounting_student.id_number',
                        'concat(accounting_student.last_name," ",accounting_student.extension_name) as lastName',
                        'accounting_student.first_name',
                        'accounting_student.middle_name',
                        'accounting_package_student.amount as packageAmount',
                        'accounting_package.code as packageName',
                        'accounting_enrolee_type.name as enroleeTypeName',
                        'accounting_discount.amount as discountAmount',
                        'accounting_discount_type.name as discountType',
                        'accounting_enhancement.amount as enhancementAmount',
                        'accounting_coaching.amount as coachingAmount',
                        'incomeEnrolments.totalAmount as incomeEnrolmentsAmount',
                        'freebies.totalAmount as freebiesAmount',
                        '((COALESCE(accounting_package_student.amount,0) + COALESCE(accounting_enhancement.amount,0) + COALESCE(accounting_coaching.amount,0)) - COALESCE(accounting_discount.amount,0)) as finalTuitionFee',
                        '((COALESCE(accounting_package_student.amount,0) + COALESCE(accounting_enhancement.amount,0) + COALESCE(accounting_coaching.amount,0)) - COALESCE(accounting_discount.amount,0)) - (COALESCE(incomeEnrolments.totalAmount, 0) + COALESCE(freebies.totalAmount, 0)) as balanceAmount',
                        'IF(((COALESCE(accounting_package_student.amount,0) + COALESCE(accounting_enhancement.amount,0) + COALESCE(accounting_coaching.amount,0)) - COALESCE(accounting_discount.amount,0)) - (COALESCE(incomeEnrolments.totalAmount, 0) + COALESCE(freebies.totalAmount, 0)) > 0, "With Balance", "Cleared") as balanceStatus'
                      ])
                      ->leftJoin('accounting_student', 'accounting_student.id = accounting_student_tuition.student_id')
                      ->leftJoin('accounting_package_student', 'accounting_package_student.id = accounting_student_tuition.package_student_id')
                      ->leftJoin('accounting_package', 'accounting_package.id = accounting_package_student.package_id')
                      ->leftJoin('accounting_season', 'accounting_season.id = accounting_student_tuition.season_id')
                      ->leftJoin('accounting_coaching', 'accounting_coaching.season_id = accounting_season.id and accounting_coaching.student_id = accounting_student_tuition.student_id')
                      ->leftJoin('accounting_student_enrolee_type', 'accounting_student_enrolee_type.season_id = accounting_student_tuition.season_id and accounting_student_enrolee_type.student_id = accounting_student_tuition.student_id')
                      ->leftJoin('accounting_enhancement', 'accounting_enhancement.season_id = accounting_student_tuition.season_id and accounting_enhancement.student_id = accounting_student_tuition.student_id')
                      ->leftJoin('accounting_discount', 'accounting_discount.season_id = accounting_student_tuition.season_id and accounting_discount.student_id = accounting_student_tuition.student_id')
                      ->leftJoin('accounting_discount_type', 'accounting_discount_type.id = accounting_discount.discount_type_id')
                      ->leftJoin('accounting_enrolee_type', 'accounting_enrolee_type.id = accounting_student_enrolee_type.enrolee_type_id')
                      ->leftJoin('accounting_branch_program', 'accounting_branch_program.id = accounting_season.branch_program_id')
                      ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                      ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                      ->leftJoin(['incomeEnrolments' => '(
                        SELECT 
                            season_id,
                            student_id,
                            sum(amount) as totalAmount
                        from accounting_income_enrolment
                        group by season_id, student_id
                        )'], 'incomeEnrolments.season_id = accounting_student_tuition.season_id and incomeEnrolments.student_id = accounting_student_tuition.student_id')
                      ->leftJoin(['freebies' => '(
                        SELECT 
                            season_id,
                            student_id,
                            sum(amount) as totalAmount
                        from accounting_income_freebies_and_icons
                        group by season_id, student_id
                        )'], 'freebies.season_id = accounting_student_tuition.season_id and freebies.student_id = accounting_student_tuition.student_id')
                      ->andWhere(['accounting_student_tuition.season_id' => $postData['season_id']])
                      ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                      ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                      ->orderBy(['lastName' => SORT_ASC, 'accounting_student.id_number' => SORT_ASC])
                      ->limit($limit)
                      ->offset($postData['page_id'])
                      ->asArray()
                      ->all() : StudentTuition::find()
                      ->select([
                        'accounting_student.id_number',
                        'concat(accounting_student.last_name," ",accounting_student.extension_name) as lastName',
                        'accounting_student.first_name',
                        'accounting_student.middle_name',
                        'accounting_package_student.amount as packageAmount',
                        'accounting_package.code as packageName',
                        'accounting_enrolee_type.name as enroleeTypeName',
                        'accounting_discount.amount as discountAmount',
                        'accounting_discount_type.name as discountType',
                        'accounting_enhancement.amount as enhancementAmount',
                        'accounting_coaching.amount as coachingAmount',
                        'incomeEnrolments.totalAmount as incomeEnrolmentsAmount',
                        'freebies.totalAmount as freebiesAmount',
                        '((COALESCE(accounting_package_student.amount,0) + COALESCE(accounting_enhancement.amount,0) + COALESCE(accounting_coaching.amount,0)) - COALESCE(accounting_discount.amount,0)) as finalTuitionFee',
                        '((COALESCE(accounting_package_student.amount,0) + COALESCE(accounting_enhancement.amount,0) + COALESCE(accounting_coaching.amount,0)) - COALESCE(accounting_discount.amount,0)) - (COALESCE(incomeEnrolments.totalAmount, 0) + COALESCE(freebies.totalAmount, 0)) as balanceAmount',
                        'IF(((COALESCE(accounting_package_student.amount,0) + COALESCE(accounting_enhancement.amount,0) + COALESCE(accounting_coaching.amount,0)) - COALESCE(accounting_discount.amount,0)) - (COALESCE(incomeEnrolments.totalAmount, 0) + COALESCE(freebies.totalAmount, 0)) > 0, "With Balance", "Cleared") as balanceStatus'
                      ])
                      ->leftJoin('accounting_student', 'accounting_student.id = accounting_student_tuition.student_id')
                      ->leftJoin('accounting_package_student', 'accounting_package_student.id = accounting_student_tuition.package_student_id')
                      ->leftJoin('accounting_package', 'accounting_package.id = accounting_package_student.package_id')
                      ->leftJoin('accounting_season', 'accounting_season.id = accounting_student_tuition.season_id')
                      ->leftJoin('accounting_coaching', 'accounting_coaching.season_id = accounting_season.id and accounting_coaching.student_id = accounting_student_tuition.student_id')
                      ->leftJoin('accounting_student_enrolee_type', 'accounting_student_enrolee_type.season_id = accounting_student_tuition.season_id and accounting_student_enrolee_type.student_id = accounting_student_tuition.student_id')
                      ->leftJoin('accounting_enhancement', 'accounting_enhancement.season_id = accounting_student_tuition.season_id and accounting_enhancement.student_id = accounting_student_tuition.student_id')
                      ->leftJoin('accounting_discount', 'accounting_discount.season_id = accounting_student_tuition.season_id and accounting_discount.student_id = accounting_student_tuition.student_id')
                      ->leftJoin('accounting_discount_type', 'accounting_discount_type.id = accounting_discount.discount_type_id')
                      ->leftJoin('accounting_enrolee_type', 'accounting_enrolee_type.id = accounting_student_enrolee_type.enrolee_type_id')
                      ->leftJoin('accounting_branch_program', 'accounting_branch_program.id = accounting_season.branch_program_id')
                      ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                      ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                      ->leftJoin(['incomeEnrolments' => '(
                        SELECT 
                            season_id,
                            student_id,
                            sum(amount) as totalAmount
                        from accounting_income_enrolment
                        group by season_id, student_id
                        )'], 'incomeEnrolments.season_id = accounting_student_tuition.season_id and incomeEnrolments.student_id = accounting_student_tuition.student_id')
                      ->leftJoin(['freebies' => '(
                        SELECT 
                            season_id,
                            student_id,
                            sum(amount) as totalAmount
                        from accounting_income_freebies_and_icons
                        group by season_id, student_id
                        )'], 'freebies.season_id = accounting_student_tuition.season_id and freebies.student_id = accounting_student_tuition.student_id')
                      ->andWhere(['accounting_student_tuition.season_id' => $postData['season_id']])
                      ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                      ->orderBy(['lastName' => SORT_ASC, 'accounting_student.id_number' => SORT_ASC])
                      ->limit($limit)
                      ->offset($postData['page_id'])
                      ->asArray()
                      ->all() : StudentTuition::find()
                      ->select([
                        'accounting_student.id_number',
                        'concat(accounting_student.last_name," ",accounting_student.extension_name) as lastName',
                        'accounting_student.first_name',
                        'accounting_student.middle_name',
                        'accounting_package_student.amount as packageAmount',
                        'accounting_package.code as packageName',
                        'accounting_enrolee_type.name as enroleeTypeName',
                        'accounting_discount.amount as discountAmount',
                        'accounting_discount_type.name as discountType',
                        'accounting_enhancement.amount as enhancementAmount',
                        'accounting_coaching.amount as coachingAmount',
                        'incomeEnrolments.totalAmount as incomeEnrolmentsAmount',
                        'freebies.totalAmount as freebiesAmount',
                        '((COALESCE(accounting_package_student.amount,0) + COALESCE(accounting_enhancement.amount,0) + COALESCE(accounting_coaching.amount,0)) - COALESCE(accounting_discount.amount,0)) as finalTuitionFee',
                        '((COALESCE(accounting_package_student.amount,0) + COALESCE(accounting_enhancement.amount,0) + COALESCE(accounting_coaching.amount,0)) - COALESCE(accounting_discount.amount,0)) - (COALESCE(incomeEnrolments.totalAmount, 0) + COALESCE(freebies.totalAmount, 0)) as balanceAmount',
                        'IF(((COALESCE(accounting_package_student.amount,0) + COALESCE(accounting_enhancement.amount,0) + COALESCE(accounting_coaching.amount,0)) - COALESCE(accounting_discount.amount,0)) - (COALESCE(incomeEnrolments.totalAmount, 0) + COALESCE(freebies.totalAmount, 0)) > 0, "With Balance", "Cleared") as balanceStatus'
                      ])
                      ->leftJoin('accounting_student', 'accounting_student.id = accounting_student_tuition.student_id')
                      ->leftJoin('accounting_package_student', 'accounting_package_student.id = accounting_student_tuition.package_student_id')
                      ->leftJoin('accounting_package', 'accounting_package.id = accounting_package_student.package_id')
                      ->leftJoin('accounting_season', 'accounting_season.id = accounting_student_tuition.season_id')
                      ->leftJoin('accounting_coaching', 'accounting_coaching.season_id = accounting_season.id and accounting_coaching.student_id = accounting_student_tuition.student_id')
                      ->leftJoin('accounting_student_enrolee_type', 'accounting_student_enrolee_type.season_id = accounting_student_tuition.season_id and accounting_student_enrolee_type.student_id = accounting_student_tuition.student_id')
                      ->leftJoin('accounting_enhancement', 'accounting_enhancement.season_id = accounting_student_tuition.season_id and accounting_enhancement.student_id = accounting_student_tuition.student_id')
                      ->leftJoin('accounting_discount', 'accounting_discount.season_id = accounting_student_tuition.season_id and accounting_discount.student_id = accounting_student_tuition.student_id')
                      ->leftJoin('accounting_discount_type', 'accounting_discount_type.id = accounting_discount.discount_type_id')
                      ->leftJoin('accounting_enrolee_type', 'accounting_enrolee_type.id = accounting_student_enrolee_type.enrolee_type_id')
                      ->leftJoin('accounting_branch_program', 'accounting_branch_program.id = accounting_season.branch_program_id')
                      ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                      ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                      ->leftJoin(['incomeEnrolments' => '(
                        SELECT 
                            season_id,
                            student_id,
                            sum(amount) as totalAmount
                        from accounting_income_enrolment
                        group by season_id, student_id
                        )'], 'incomeEnrolments.season_id = accounting_student_tuition.season_id and incomeEnrolments.student_id = accounting_student_tuition.student_id')
                      ->leftJoin(['freebies' => '(
                        SELECT 
                            season_id,
                            student_id,
                            sum(amount) as totalAmount
                        from accounting_income_freebies_and_icons
                        group by season_id, student_id
                        )'], 'freebies.season_id = accounting_student_tuition.season_id and freebies.student_id = accounting_student_tuition.student_id')
                      ->andWhere(['accounting_student_tuition.season_id' => $postData['season_id']])
                      ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                      ->orderBy(['lastName' => SORT_ASC, 'accounting_student.id_number' => SORT_ASC])
                      ->limit($limit)
                      ->offset($postData['page_id'])
                      ->asArray()
                      ->all();
            }

            $pages = ceil(count($data) / $limit);

            $content = $this->renderPartial('_payment-updates', [
                'data' => $data,
                'season' => $season,
            ]);

            $pdf = new Pdf([
            'mode' => Pdf::MODE_CORE,
            'format' => Pdf::FORMAT_LEGAL, 
            'orientation' => Pdf::ORIENT_LANDSCAPE, 
            'destination' => Pdf::DEST_DOWNLOAD, 
            'filename' => 'Payment Updates - '.$season->seasonName.' - Page '.($postData['page_id']+1).' of '.$pages.'.pdf',
            'content' => $content,  
            'marginLeft' => 11.4,
            'marginRight' => 11.4,
            //'cssFile' => '@vendor/kartik-v/yii2-mpdf/assets/kv-mpdf-bootstrap.min.css',
            'cssInline' => 'th { text-align:center; 
                                      padding:6px 6px 6px 6px;
                                      font-size:12px;
                                      }
                            .title {
                                      font-size:16px;
                                      font-weight:bold;
                                      }
                            td { padding:8px 4px 4px 4px; font-size:12px;
                          }', 
            'options' => ['title' => 'Payment Updates'],
            'methods' => [ 
                'SetHeader'=>['Payment Updates - '.$season->seasonName.' - Page '.($postData['page_id']+1).' of '.$pages], 
                'SetFooter'=>['Page {PAGENO}'],
            ]
            ]);
            $response = Yii::$app->response;
            $response->format = \yii\web\Response::FORMAT_RAW;
            $headers = Yii::$app->response->headers;
            $headers->add('Content-Type', 'application/pdf');
            return $pdf->render();   
        }

        return $this->renderAjax('_search-payment-updates',[
            'model' => $model,
            'seasons' => $seasons,
            'pages' => $pages,
        ]);
    }

    public function actionPageList($id) {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $user_info = Yii::$app->user->identity->userinfo;
        $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
        $rolenames =  ArrayHelper::map($roles, 'name','name');

        $season = Season::findOne($id);

        $access = AccessProgram::findOne(['user_id' => Yii::$app->user->identity->userinfo->user_id]);

        if(in_array('TopManagement',$rolenames)){
            $count = $access ? $access->branch_program_id!= '' ? StudentTuition::find()
                ->leftJoin('accounting_student','accounting_student.id = accounting_student_tuition.student_id')
                ->leftJoin('accounting_season','accounting_season.id = accounting_student_tuition.season_id')
                ->andWhere(['accounting_student.status' => 'Active'])
                ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                ->andWhere(['accounting_season.id' => $season->id])
                ->asArray()
                ->count() : StudentTuition::find()
                ->leftJoin('accounting_student','accounting_student.id = accounting_student_tuition.student_id')
                ->leftJoin('accounting_season','accounting_season.id = accounting_student_tuition.season_id')
                ->andWhere(['accounting_student.status' => 'Active'])
                ->andWhere(['accounting_season.id' => $season->id])
                ->asArray()
                ->count() : StudentTuition::find()
                ->leftJoin('accounting_student','accounting_student.id = accounting_student_tuition.student_id')
                ->leftJoin('accounting_season','accounting_season.id = accounting_student_tuition.season_id')
                ->andWhere(['accounting_student.status' => 'Active'])
                ->andWhere(['accounting_season.id' => $season->id])
                ->asArray()
                ->count();
        }else{
            $count = $access ? $access->branch_program_id!= '' ? StudentTuition::find()
                ->leftJoin('accounting_student','accounting_student.id = accounting_student_tuition.student_id')
                ->leftJoin('accounting_season','accounting_season.id = accounting_student_tuition.season_id')
                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                ->andWhere(['accounting_student.status' => 'Active'])
                ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                ->andWhere(['accounting_season.id' => $season->id])
                ->asArray()
                ->count() : StudentTuition::find()
                ->leftJoin('accounting_student','accounting_student.id = accounting_student_tuition.student_id')
                ->leftJoin('accounting_season','accounting_season.id = accounting_student_tuition.season_id')
                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                ->andWhere(['accounting_student.status' => 'Active'])
                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                ->andWhere(['accounting_season.id' => $season->id])
                ->asArray()
                ->count() : StudentTuition::find()
                ->leftJoin('accounting_student','accounting_student.id = accounting_student_tuition.student_id')
                ->leftJoin('accounting_season','accounting_season.id = accounting_student_tuition.season_id')
                ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                ->andWhere(['accounting_student.status' => 'Active'])
                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                ->andWhere(['accounting_season.id' => $season->id])
                ->asArray()
                ->count();
        }

        $reportPages = [];
        $limit = 1000;
        $pages = ceil($count / $limit);
        $offset = 0;
        $rangeValue = 0;

        for($x=0;$x<$pages;$x++)
        {
            $offset += $rangeValue;
            $reportPages[] = ['id' => $offset, 'text' => ($x+1).' of '.$pages];
            $rangeValue = 1000;
        }

        return $reportPages;
    }

    public function actionDropout($id, $season_id)
    {
        $model = $this->findModel($id);
        $season = Season::find()->where(['id' => $season_id])->one();
        $dropoutModel = Dropout::findOne(['student_id' => $model->id, 'season_id' => $season->id]) ? Dropout::findOne(['student_id' => $model->id, 'season_id' => $season->id]) : new Dropout();

        if($dropoutModel->load(Yii::$app->request->post()))
        {
            $dropoutModel->season_id = $season->id;
            $dropoutModel->student_id = $model->id;
            $dropoutModel->drop_date = date("Y-m-d H:i:s");
            $dropoutModel->save();
        }

        return $this->renderAjax('_dropout', [
            'model' => $model,
            'season' => $season,
            'dropoutModel' => $dropoutModel,
        ]);
    }

    public function actionRemoveDropout($id, $season_id)
    {
        $model = $this->findModel($id);
        $season = Season::find()->where(['id' => $season_id])->one();
        $dropoutModel = Dropout::findOne(['student_id' => $model->id, 'season_id' => $season->id]);

        if(Yii::$app->request->post())
        {
            $dropoutModel->delete();
        }

        return $this->renderAjax('_remove-dropout', [
            'model' => $model,
            'season' => $season,
            'dropoutModel' => $dropoutModel,
        ]);
    }

    public function actionTransfer($id, $branch_id, $season_id)
    {
        $model = new Transferee();
        $oldSeason = Season::findOne($season_id);
        $model->from_branch_id = $branch_id;
        $model->from_program_id = $oldSeason->branchProgram->program_id;
        $model->from_season_id = $season_id;

        $seasons = Season::find()
           ->select([
            'accounting_season.id as id',
            'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name'
           ])
           ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
           ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
           ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
           ->where(['<>','accounting_season.id', $season_id])
           ->asArray()
           ->orderBy(['name' => SORT_ASC])
           ->all();

        $seasons = ArrayHelper::map($seasons, 'id', 'name');

        if($model->load(Yii::$app->request->post()))
        {
            $newSeason = Season::findOne($model->to_season_id);
            $model->student_id = $id;
            $model->from_branch_id = $branch_id;
            $model->from_program_id = $oldSeason->branchProgram->program_id;
            $model->from_season_id = $season_id;
            $model->to_branch_id = $newSeason->branchProgram->branch_id;
            $model->to_program_id = $newSeason->branchProgram->program_id;
            if($model->save())
            {
                $notification = new Notification();
                $notification->branch_id = $model->to_branch_id;
                $notification->model = 'Transferee';
                $notification->model_id = $model->id;
                $notification->message = 'Transferred student, '.$model->studentName.' needs your action for enrolment';
                $notification->save();
            }
        }

        return $this->renderAjax('_transfer', [
            'model' => $model,
            'seasons' => $seasons,
        ]);
    }

    public function actionEnroll($id, $season_id)
    {
        $user_info = Yii::$app->user->identity->userinfo;
        $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
        $rolenames =  ArrayHelper::map($roles, 'name','name');

        $model = $this->findModel($id);
        $season = Season::find()->where(['id' => $season_id])->one();
        $discountModel = Discount::findOne(['student_id' => $model->id, 'season_id' => $season->id]) ? Discount::findOne(['student_id' => $model->id, 'season_id' => $season->id]) : new Discount();
        $enroleeTypeModel = StudentEnroleeType::findOne(['student_id' => $model->id, 'season_id' => $season->id]) ? StudentEnroleeType::findOne(['student_id' => $model->id, 'season_id' => $season->id]) : new StudentEnroleeType();
        $packageStudentModel = PackageStudent::findOne(['student_id' => $model->id, 'season_id' => $season->id]) ? PackageStudent::findOne(['student_id' => $model->id, 'season_id' => $season->id]) : new PackageStudent();
        $studentTuitionModel = StudentTuition::findOne(['student_id' => $model->id, 'season_id' => $season->id]) ? StudentTuition::findOne(['student_id' => $model->id, 'season_id' => $season->id]) : new StudentTuition();
        $tuition = StudentTuition::find()->where(['student_id' => $model->id, 'season_id' => $season->id])->asArray()->one();
        $enhancementModel = Enhancement::findOne(['student_id' => $model->id, 'season_id' => $season->id]) ? Enhancement::findOne(['student_id' => $model->id, 'season_id' => $season->id]) : new Enhancement();
        $coachingModel = Coaching::findOne(['student_id' => $model->id, 'season_id' => $season->id]) ? Coaching::findOne(['student_id' => $model->id, 'season_id' => $season->id]) : new Coaching();
        $studentProgramModel = StudentProgram::findOne(['student_id' => $model->id]) ? StudentProgram::findOne(['student_id' => $model->id]) : new StudentProgram(); 

        $dropoutModel = Dropout::findOne(['student_id' => $model->id, 'season_id' => $season->id]) ? Dropout::findOne(['student_id' => $model->id, 'season_id' => $season->id]) : new Dropout();
        $dropout = Dropout::find()->where(['student_id' => $model->id, 'season_id' => $season->id])->asArray()->one();

        $transfereeModel = Transferee::findOne(['student_id' => $model->id, 'from_branch_id' => $season->branchProgram->branch->id, 'from_season_id' => $season->id]) ? Transferee::findOne(['student_id' => $model->id, 'from_branch_id' => $season->branchProgram->branch->id, 'from_season_id' => $season->id]) : new Transferee();
        $transferee = Transferee::find()
                        ->where([
                            'student_id' => $model->id, 
                            'from_branch_id' => $season->branchProgram->branch->id, 
                            'from_season_id' => $season->id])
                        ->asArray()
                        ->one();

        $transferred = Transferee::find()
                        ->leftJoin('accounting_student_enrolee_type','accounting_student_enrolee_type.student_id = accounting_transferee.student_id and accounting_student_enrolee_type.season_id = accounting_transferee.to_season_id')
                        ->where([
                            'accounting_transferee.student_id' => $model->id, 
                            'from_branch_id' => $season->branchProgram->branch->id, 
                            'from_season_id' => $season->id])
                        ->andWhere(['is', 'accounting_student_enrolee_type.student_id', null])
                        ->andWhere(['is', 'accounting_student_enrolee_type.season_id', null])
                        ->asArray()
                        ->one();

        $orStatus = $this->takeOrStatus($season->id);
        $current_or = $this->takeOrNow($season->id);

        $incomeEnrolmentModel = IncomeEnrolment::find()
                                ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_enrolment.id and accounting_income.income_type_id = 1')
                                ->where(['season_id' => $season->id, 'student_id' => $model->id])
                                ->orderBy(['accounting_income.datetime' => SORT_ASC])
                                ->one() ? IncomeEnrolment::find()
                                ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_enrolment.id and accounting_income.income_type_id = 1')
                                ->where(['season_id' => $season->id, 'student_id' => $model->id])
                                ->orderBy(['accounting_income.datetime' => SORT_ASC])
                                ->one() : new IncomeEnrolment();

        $incomeModel = Income::find()
                        ->leftJoin('accounting_income_enrolment','accounting_income_enrolment.id = accounting_income.income_id')
                        ->where(['accounting_income_enrolment.season_id' => $season->id, 'accounting_income_enrolment.student_id' => $model->id, 'accounting_income.income_type_id' => '1'])
                        ->orderBy(['accounting_income.datetime' => SORT_ASC])
                        ->one() ? Income::find()
                        ->leftJoin('accounting_income_enrolment','accounting_income_enrolment.id = accounting_income.income_id')
                        ->where(['accounting_income_enrolment.season_id' => $season->id, 'accounting_income_enrolment.student_id' => $model->id, 'accounting_income.income_type_id' => '1'])
                        ->orderBy(['accounting_income.datetime' => SORT_ASC])
                        ->one() : new Income();

        $access = AccessProgram::findOne(['user_id' => Yii::$app->user->identity->userinfo->user_id]);

        if(in_array('TopManagement',$rolenames)){
            $seasons = $access ? $access->branch_program_id != '' ? Season::find()
                   ->select([
                    'accounting_season.id as id',
                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name'
                   ])
                   ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                   ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                   ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                   ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                   ->asArray()
                   ->orderBy(['name' => SORT_ASC])
                   ->all() : Season::find()
                   ->select([
                    'accounting_season.id as id',
                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name'
                   ])
                   ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                   ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                   ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                   ->asArray()
                   ->orderBy(['name' => SORT_ASC])
                   ->all() : Season::find()
                   ->select([
                    'accounting_season.id as id',
                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name'
                   ])
                   ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                   ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                   ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                   ->asArray()
                   ->orderBy(['name' => SORT_ASC])
                   ->all(); 
        }else{
           $seasons = $access ? $access->branch_program_id != '' ? Season::find()
                   ->select([
                    'accounting_season.id as id',
                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name'
                   ])
                   ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                   ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                   ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                   ->where(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                   ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                   ->asArray()
                   ->orderBy(['name' => SORT_ASC])
                   ->all() : Season::find()
                   ->select([
                    'accounting_season.id as id',
                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name'
                   ])
                   ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                   ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                   ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                   ->where(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                   ->asArray()
                   ->orderBy(['name' => SORT_ASC])
                   ->all() : Season::find()
                   ->select([
                    'accounting_season.id as id',
                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name'
                   ])
                   ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                   ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                   ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                   ->where(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                   ->asArray()
                   ->orderBy(['name' => SORT_ASC])
                   ->all(); 
        }

        $seasons = ArrayHelper::map($seasons, 'id', 'name');

        $enroleeTypes = EnroleeType::find()->asArray()->all();
        $enroleeTypes = ArrayHelper::map($enroleeTypes, 'id', 'name');

        if(in_array('TopManagement',$rolenames)){
            $packages = $access ? $access->branch_program_id != '' ? Package::find()->select([
                    'accounting_package.id as id',
                    'concat(accounting_package.code," - ",accounting_package_type.name," - TIER ",accounting_package.tier) as name'
                    ])
                    ->leftJoin('accounting_package_type','accounting_package_type.id = accounting_package.package_type_id')
                    ->andwhere(['accounting_package.branch_id' => $access->branchProgram->branch_id, 'accounting_package.program_id' => $access->branchProgram->program_id])
                    ->asArray()
                    ->all() : Package::find()->select([
                    'accounting_package.id as id',
                    'concat(accounting_package.code," - ",accounting_package_type.name," - TIER ",accounting_package.tier) as name'
                    ])
                    ->leftJoin('accounting_package_type','accounting_package_type.id = accounting_package.package_type_id')
                    ->asArray()
                    ->all() : Package::find()->select([
                    'accounting_package.id as id',
                    'concat(accounting_package.code," - ",accounting_package_type.name," - TIER ",accounting_package.tier) as name'
                    ])
                    ->leftJoin('accounting_package_type','accounting_package_type.id = accounting_package.package_type_id')
                    ->asArray()
                    ->all();
        }else{
            $packages = $access ? $access->branch_program_id != '' ? Package::find()->select([
                    'accounting_package.id as id',
                    'concat(accounting_package.code," - ",accounting_package_type.name," - TIER ",accounting_package.tier) as name'
                    ])
                    ->leftJoin('accounting_package_type','accounting_package_type.id = accounting_package.package_type_id')
                    ->andWhere(['accounting_package.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                    ->andwhere(['accounting_package.branch_id' => $access->branchProgram->branch_id, 'accounting_package.program_id' => $access->branchProgram->program_id])
                    ->asArray()
                    ->all() : Package::find()->select([
                    'accounting_package.id as id',
                    'concat(accounting_package.code," - ",accounting_package_type.name," - TIER ",accounting_package.tier) as name'
                    ])
                    ->leftJoin('accounting_package_type','accounting_package_type.id = accounting_package.package_type_id')
                    ->andWhere(['accounting_package.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                    ->asArray()
                    ->all() : Package::find()->select([
                    'accounting_package.id as id',
                    'concat(accounting_package.code," - ",accounting_package_type.name," - TIER ",accounting_package.tier) as name'
                    ])
                    ->leftJoin('accounting_package_type','accounting_package_type.id = accounting_package.package_type_id')
                    ->andWhere(['accounting_package.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                    ->asArray()
                    ->all();
        }

        

        $packages = ArrayHelper::map($packages, 'id', 'name');

        $discountTypes = DiscountType::find()->all();
        $discountTypes = ArrayHelper::map($discountTypes, 'id', 'name');

        if(in_array('TopManagement',$rolenames)){
            $coachingPackages = $access ? $access->branch_program_id != '' ? Package::find()->select([
                    'accounting_package.id as id',
                    'concat(accounting_package.code," - ",accounting_package_type.name," - TIER ",accounting_package.tier) as name'
                    ])
                    ->leftJoin('accounting_package_type','accounting_package_type.id = accounting_package.package_type_id')
                    ->leftJoin('accounting_enrolee_type','accounting_enrolee_type.id = accounting_package_type.enrolee_type_id')
                    ->andWhere(['accounting_enrolee_type.id' => '4'])
                    ->andwhere(['accounting_package.branch_id' => $access->branchProgram->branch_id, 'accounting_package.program_id' => $access->branchProgram->program_id])
                    ->asArray()
                    ->all() : Package::find()->select([
                    'accounting_package.id as id',
                    'concat(accounting_package.code," - ",accounting_package_type.name," - TIER ",accounting_package.tier) as name'
                    ])
                    ->leftJoin('accounting_package_type','accounting_package_type.id = accounting_package.package_type_id')
                    ->leftJoin('accounting_enrolee_type','accounting_enrolee_type.id = accounting_package_type.enrolee_type_id')
                    ->andWhere(['accounting_enrolee_type.id' => '4'])
                    ->asArray()
                    ->all() : Package::find()->select([
                    'accounting_package.id as id',
                    'concat(accounting_package.code," - ",accounting_package_type.name," - TIER ",accounting_package.tier) as name'
                    ])
                    ->leftJoin('accounting_package_type','accounting_package_type.id = accounting_package.package_type_id')
                    ->leftJoin('accounting_enrolee_type','accounting_enrolee_type.id = accounting_package_type.enrolee_type_id')
                    ->andWhere(['accounting_enrolee_type.id' => '4'])
                    ->asArray()
                    ->all();
        }else{
            $coachingPackages = $access ? $access->branch_program_id != '' ? Package::find()->select([
                    'accounting_package.id as id',
                    'concat(accounting_package.code," - ",accounting_package_type.name," - TIER ",accounting_package.tier) as name'
                    ])
                    ->leftJoin('accounting_package_type','accounting_package_type.id = accounting_package.package_type_id')
                    ->leftJoin('accounting_enrolee_type','accounting_enrolee_type.id = accounting_package_type.enrolee_type_id')
                    ->andWhere(['accounting_package.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                    ->andWhere(['accounting_enrolee_type.id' => '4'])
                    ->andwhere(['accounting_package.branch_id' => $access->branchProgram->branch_id, 'accounting_package.program_id' => $access->branchProgram->program_id])
                    ->asArray()
                    ->all() : Package::find()->select([
                    'accounting_package.id as id',
                    'concat(accounting_package.code," - ",accounting_package_type.name," - TIER ",accounting_package.tier) as name'
                    ])
                    ->leftJoin('accounting_package_type','accounting_package_type.id = accounting_package.package_type_id')
                    ->leftJoin('accounting_enrolee_type','accounting_enrolee_type.id = accounting_package_type.enrolee_type_id')
                    ->andWhere(['accounting_package.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                    ->andWhere(['accounting_enrolee_type.id' => '4'])
                    ->asArray()
                    ->all() : Package::find()->select([
                    'accounting_package.id as id',
                    'concat(accounting_package.code," - ",accounting_package_type.name," - TIER ",accounting_package.tier) as name'
                    ])
                    ->leftJoin('accounting_package_type','accounting_package_type.id = accounting_package.package_type_id')
                    ->leftJoin('accounting_enrolee_type','accounting_enrolee_type.id = accounting_package_type.enrolee_type_id')
                    ->andWhere(['accounting_package.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                    ->andWhere(['accounting_enrolee_type.id' => '4'])
                    ->asArray()
                    ->all();
        }

        $coachingPackages = ArrayHelper::map($coachingPackages, 'id', 'name');

        $incomeCodes = IncomeCode::find()->select(['id, concat(name," - ",description) as name'])->all();
        $incomeCodes = ArrayHelper::map($incomeCodes, 'id', 'name');

        if(Yii::$app->request->isAjax && ($discountModel->load(Yii::$app->request->post()))){
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($discountModel);
        }else if(Yii::$app->request->post())
        {
            $postData = Yii::$app->request->post();
            $advanceEnrolment = AdvanceEnrolment::findOne(['season_id' => $season->id, 'student_id' => $model->id]);
            if($advanceEnrolment)
            {
                $advanceEnrolment->delete();
            }

            $enroleeTypeModel->season_id = $season->id;
            $enroleeTypeModel->student_id = $model->id;
            $enroleeTypeModel->enrolee_type_id = $postData['StudentEnroleeType']['enrolee_type_id'];
            if($enroleeTypeModel->save())
            {
                $packageStudentModel->season_id = $season->id;
                $packageStudentModel->student_id = $model->id;
                $packageStudentModel->package_id = $postData['PackageStudent']['package_id'];
                
                $pck = Package::findOne($postData['PackageStudent']['package_id']);
                $packageStudentModel->amount = $pck ? $pck->amount : 0;
            }

            

            if($postData['Enhancement']['amount'] != '' || $postData['Enhancement']['amount'] != '0')
            {
                $enhancementModel->season_id = $season->id;
                $enhancementModel->student_id = $model->id;
                $enhancementModel->amount = $postData['Enhancement']['amount'];
                $enhancementModel->save();
            }

            if($postData['Discount']['discount_type_id'] != '')
            {
                $discountModel->discount_type_id = $postData['Discount']['discount_type_id'];
                $discountModel->season_id = $season->id;
                $discountModel->student_id = $model->id;
                $discountModel->amount = $postData['Discount']['amount'];
                $discountModel->code_number = $postData['Discount']['code_number'];
                $discountModel->remarks = $postData['Discount']['remarks'];
                $discountModel->save();
            }

            if($postData['Coaching']['package_id'] != '')
            {
                $coachingModel->season_id = $season->id;
                $coachingModel->student_id = $model->id;
                $coachingModel->package_id = $postData['Coaching']['package_id'];

                $chng = Package::findOne($postData['Coaching']['package_id']);
                $coachingModel->amount = $chng ? $chng->amount : 0;
                $coachingModel->save();
            }

            if($packageStudentModel->save())
            {
                $studentTuitionModel->season_id = $season->id;
                $studentTuitionModel->student_id = $model->id;
                $studentTuitionModel->package_student_id = $packageStudentModel->id;

                if($postData['Discount']['discount_type_id'] != '')
                {
                    $studentTuitionModel->discount_id = $discountModel->id;
                }

                if($enhancementModel->amount != '' || $enhancementModel->amount != '0')
                {
                    $studentTuitionModel->enhancement_id = $enhancementModel->id;
                }

                $studentTuitionModel->save();
            }


            $incomeEnrolmentModel->season_id = $season->id;
            $incomeEnrolmentModel->or_no = $this->takeOrNow($season->id);
            $incomeEnrolmentModel->code_id = $postData['IncomeEnrolment']['code_id'];
            $incomeEnrolmentModel->student_id = $model->id;
            $incomeEnrolmentModel->amount = $postData['IncomeEnrolment']['amount'];
            if($incomeEnrolmentModel->save(false))
            {
                $incomeModel->income_type_id = 1;
                $incomeModel->branch_id = Yii::$app->user->identity->userinfo->BRANCH_C;
                $incomeModel->program_id =  $season->branchProgram->program_id;
                $incomeModel->income_id = $incomeEnrolmentModel->id;
                $incomeModel->amount_type = $postData['Income']['amount_type'];
                $incomeModel->transaction_number = $postData['Income']['transaction_number'];
                $incomeModel->save(false);

        
                $orModel = SeasonOr::find()->where(['season_id' => $season->id, 'or_no' => $this->takeOrNow($season->id)])->one() ? SeasonOr::find()->where(['season_id' => $season->id, 'or_no' => $this->takeOrNow($season->id)])->one() : new SeasonOr();
                $orModel->season_id = $season->id;
                $orModel->season_or_list_id = $this->takeOrList($season->id);
                $orModel->or_no = $this->takeOrNow($season->id);
                $orModel->save();
            }

            $studentProgramModel->student_id = $model->id;
            $studentProgramModel->program_id = $season->branchProgram->program->id;
            $studentProgramModel->save();

            $studentBranchProgramModel = StudentBranchProgram::findOne(['student_id' => $model->id, 'branch_id' => $season->branchProgram->branch->id, 'program_id' => $season->branchProgram->program->id]) ? StudentBranchProgram::findOne(['student_id' => $model->id, 'branch_id' => $season->branchProgram->branch->id, 'program_id' => $season->branchProgram->program->id]) : new StudentBranchProgram();

            $studentBranchProgramModel->student_id = $model->id;
            $studentBranchProgramModel->branch_id = $season->branchProgram->branch->id;
            $studentBranchProgramModel->program_id = $season->branchProgram->program->id;
            $studentBranchProgramModel->save();

            \Yii::$app->getSession()->setFlash('success', 'Student enrolment has been updated');
            return $this->redirect(['/accounting/student/']);
        }

        return $this->renderAjax('enroll', [
            'model' => $model,
            'season' => $season,
            'discountModel' => $discountModel,
            'enroleeTypeModel' => $enroleeTypeModel,
            'packageStudentModel' => $packageStudentModel,
            'studentTuitionModel' => $studentTuitionModel,
            'enhancementModel' => $enhancementModel,
            'coachingModel' => $coachingModel,
            'incomeEnrolmentModel' => $incomeEnrolmentModel,
            'incomeModel' => $incomeModel,
            'seasons' => $seasons,
            'enroleeTypes' => $enroleeTypes,
            'packages' => $packages,
            'discountTypes' => $discountTypes,
            'coachingPackages' => $coachingPackages,
            'incomeCodes' => $incomeCodes,
            'current_or' => $current_or,
            'orStatus' => $orStatus,
            'tuition' => $tuition,
            'dropoutModel' => $dropoutModel,
            'dropout' => $dropout,
            'transfereeModel' => $transfereeModel,
            'transferee' => $transferee,
            'transferred' => $transferred,
        ]);
    }

    /**
     * Displays a single Student model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $enrolments = StudentTuition::find()
                      ->select([
                        'accounting_student_enrolee_type.id as id',
                        'accounting_student_tuition.season_id as season_id',
                        'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as seasonName',
                        'accounting_package_student.amount as packageAmount',
                        'accounting_package.code as packageName',
                        'accounting_enrolee_type.name as enroleeTypeName',
                        'accounting_branch.name as branchName',
                        'accounting_program.name as programName',
                        'accounting_discount.amount as discountAmount',
                        'accounting_discount_type.name as discountType',
                        'accounting_enhancement.amount as enhancementAmount',
                        'accounting_coaching.amount as coachingAmount',
                        '((COALESCE(accounting_package_student.amount,0) + COALESCE(accounting_enhancement.amount,0) + COALESCE(accounting_coaching.amount,0)) - COALESCE(accounting_discount.amount,0)) as finalTuitionFee',
                        '((COALESCE(accounting_package_student.amount,0) + COALESCE(accounting_enhancement.amount,0) + COALESCE(accounting_coaching.amount,0)) - COALESCE(accounting_discount.amount,0)) - (COALESCE(incomeEnrolments.totalAmount, 0) + COALESCE(freebies.totalAmount, 0)) as balanceAmount',
                        'IF(((COALESCE(accounting_package_student.amount,0) + COALESCE(accounting_enhancement.amount,0) + COALESCE(accounting_coaching.amount,0)) - COALESCE(accounting_discount.amount,0)) - (COALESCE(incomeEnrolments.totalAmount, 0) + COALESCE(freebies.totalAmount, 0)) > 0, "With Balance", "Cleared") as balanceStatus'
                      ])
                      ->leftJoin('accounting_package_student', 'accounting_package_student.id = accounting_student_tuition.package_student_id')
                      ->leftJoin('accounting_package', 'accounting_package.id = accounting_package_student.package_id')
                      ->leftJoin('accounting_season', 'accounting_season.id = accounting_student_tuition.season_id')
                      ->leftJoin('accounting_coaching', 'accounting_coaching.season_id = accounting_season.id and accounting_coaching.student_id = accounting_student_tuition.student_id')
                      ->leftJoin('accounting_student_enrolee_type', 'accounting_student_enrolee_type.season_id = accounting_season.id and accounting_student_enrolee_type.student_id = accounting_student_tuition.student_id')
                      ->leftJoin('accounting_enhancement', 'accounting_enhancement.season_id = accounting_season.id and accounting_enhancement.student_id = accounting_student_tuition.student_id')
                      ->leftJoin('accounting_discount', 'accounting_discount.season_id = accounting_season.id and accounting_discount.student_id = accounting_student_tuition.student_id')
                      ->leftJoin('accounting_discount_type', 'accounting_discount_type.id = accounting_discount.discount_type_id')
                      ->leftJoin('accounting_enrolee_type', 'accounting_enrolee_type.id = accounting_student_enrolee_type.enrolee_type_id')
                      ->leftJoin('accounting_branch_program', 'accounting_branch_program.id = accounting_season.branch_program_id')
                      ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                      ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                      ->leftJoin(['incomeEnrolments' => '(
                        SELECT 
                            season_id,
                            student_id,
                            sum(amount) as totalAmount
                        from accounting_income_enrolment
                        group by season_id, student_id
                        )'], 'incomeEnrolments.season_id = accounting_season.id and incomeEnrolments.student_id = accounting_student_tuition.student_id')
                      ->leftJoin(['freebies' => '(
                        SELECT 
                            season_id,
                            student_id,
                            sum(amount) as totalAmount
                        from accounting_income_freebies_and_icons
                        group by season_id, student_id
                        )'], 'freebies.season_id = accounting_season.id and freebies.student_id = accounting_student_tuition.student_id')
                      ->where(['accounting_student_tuition.student_id' => $model->id])
                      ->orderBy(['accounting_student_tuition.id' => SORT_DESC])
                      ->asArray()
                      ->all();

        return $this->render('view', [
            'model' => $model,
            'enrolments' => $enrolments,
        ]);
    }

    public function actionShowEnrolment($id)
    {
        $model = StudentTuition::find()
                      ->select([
                        'accounting_student_tuition.id as id',
                        'accounting_student_tuition.season_id as season_id',
                        'accounting_student_tuition.student_id as student_id',
                        'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as seasonName',
                        'accounting_package_student.amount as packageAmount',
                        'concat(accounting_package.code," - ",accounting_package_type.name," - TIER ",accounting_package.tier) as packageName',
                        'accounting_enrolee_type.name as enroleeTypeName',
                        'accounting_branch.name as branchName',
                        'accounting_program.name as programName',
                        'accounting_discount.amount as discountAmount',
                        'IF(accounting_discount_type.name = "GC", concat(accounting_discount_type.name," - ",accounting_discount.code_number), accounting_discount_type.name) as discountType',
                        'accounting_enhancement.amount as enhancementAmount',
                        'accounting_coaching.amount as coachingAmount',
                        '((COALESCE(accounting_package_student.amount,0) + COALESCE(accounting_enhancement.amount,0) + COALESCE(accounting_coaching.amount,0)) - COALESCE(accounting_discount.amount,0)) as finalTuitionFee',
                        '((COALESCE(accounting_package_student.amount,0) + COALESCE(accounting_enhancement.amount,0) + COALESCE(accounting_coaching.amount,0)) - COALESCE(accounting_discount.amount,0)) - (COALESCE(incomeEnrolments.totalAmount, 0) + COALESCE(freebies.totalAmount, 0)) as balanceAmount',
                        'IF(((COALESCE(accounting_package_student.amount,0) + COALESCE(accounting_enhancement.amount,0) + COALESCE(accounting_coaching.amount,0)) - COALESCE(accounting_discount.amount,0)) - (COALESCE(incomeEnrolments.totalAmount, 0) + COALESCE(freebies.totalAmount, 0)) > 0, "With Balance", "Cleared") as balanceStatus',
                        'accounting_dropout.id as dropoutId',
                        'accounting_dropout.drop_date as dropDate',
                        'accounting_dropout.reason as reason',
                        'accounting_dropout.authorized_by as authorized_by',
                      ])
                      ->leftJoin('accounting_package_student', 'accounting_package_student.id = accounting_student_tuition.package_student_id')
                      ->leftJoin('accounting_package', 'accounting_package.id = accounting_package_student.package_id')
                      ->leftJoin('accounting_package_type', 'accounting_package_type.id = accounting_package.package_type_id')
                      ->leftJoin('accounting_season', 'accounting_season.id = accounting_student_tuition.season_id')
                      ->leftJoin('accounting_coaching', 'accounting_coaching.season_id = accounting_season.id and accounting_coaching.student_id = accounting_student_tuition.student_id')
                      ->leftJoin('accounting_student_enrolee_type', 'accounting_student_enrolee_type.season_id = accounting_season.id and accounting_student_enrolee_type.student_id = accounting_student_tuition.student_id')
                      ->leftJoin('accounting_enhancement', 'accounting_enhancement.season_id = accounting_season.id and accounting_enhancement.student_id = accounting_student_tuition.student_id')
                      ->leftJoin('accounting_discount', 'accounting_discount.season_id = accounting_season.id and accounting_discount.student_id = accounting_student_tuition.student_id')
                      ->leftJoin('accounting_dropout', 'accounting_dropout.season_id = accounting_season.id and accounting_dropout.student_id = accounting_student_tuition.student_id')
                      ->leftJoin('accounting_discount_type', 'accounting_discount_type.id = accounting_discount.discount_type_id')
                      ->leftJoin('accounting_enrolee_type', 'accounting_enrolee_type.id = accounting_student_enrolee_type.enrolee_type_id')
                      ->leftJoin('accounting_branch_program', 'accounting_branch_program.id = accounting_season.branch_program_id')
                      ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                      ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                      ->leftJoin(['incomeEnrolments' => '(
                        SELECT 
                            season_id,
                            student_id,
                            sum(amount) as totalAmount
                        from accounting_income_enrolment
                        group by season_id, student_id
                        )'], 'incomeEnrolments.season_id = accounting_season.id and incomeEnrolments.student_id = accounting_student_tuition.student_id')
                      ->leftJoin(['freebies' => '(
                        SELECT 
                            season_id,
                            student_id,
                            sum(amount) as totalAmount
                        from accounting_income_freebies_and_icons
                        group by season_id, student_id
                        )'], 'freebies.season_id = accounting_season.id and freebies.student_id = accounting_student_tuition.student_id')
                      ->where(['accounting_student_enrolee_type.id' => $id])
                      ->orderBy(['accounting_student_enrolee_type.id' => SORT_DESC])
                      ->asArray()
                      ->one();

        $payments = [];

        if(!empty($model))
        {
            $paymentEnrolments = IncomeEnrolment::find()
                        ->select([
                            'accounting_income.amount_type as amount_type',
                            'accounting_income.datetime as datetime',
                            'accounting_income_enrolment.or_no as or_no',
                            'concat(accounting_income_code.description) as code',
                            'accounting_income_enrolment.amount as amount',
                            'accounting_income.amount_type as amountType',
                        ])
                        ->leftJoin('accounting_income', 'accounting_income.income_id = accounting_income_enrolment.id and accounting_income.income_type_id = 1')
                        ->leftJoin('accounting_income_code', 'accounting_income_code.id = accounting_income_enrolment.code_id')
                        ->where(['student_id' => $model['student_id'], 'season_id' => $model['season_id']])
                        ->asArray()
                        ->orderBy(['datetime' => SORT_DESC])
                        ->all();

            $paymentFreebies = FreebieAndIcon::find()
                                ->select([
                                    'accounting_income.amount_type as amount_type',
                                    'accounting_income.datetime as datetime',
                                    'accounting_income_freebies_and_icons.pr as or_no',
                                    'concat(accounting_income_code.description) as code',
                                    'accounting_income_freebies_and_icons.amount as amount',
                                    'accounting_income.amount_type as amountType',
                                ])
                                ->leftJoin('accounting_income', 'accounting_income.income_id = accounting_income_freebies_and_icons.id and accounting_income.income_type_id = 2')
                                ->leftJoin('accounting_income_code', 'accounting_income_code.id = accounting_income_freebies_and_icons.code_id')
                                ->where(['student_id' => $model['student_id'], 'season_id' => $model['season_id']])
                                ->asArray()
                                ->orderBy(['datetime' => SORT_DESC])
                                ->all();

            $payments = array_merge($paymentEnrolments, $paymentFreebies);
                    
        }

        arsort($payments);

        return $this->renderAjax('_show-enrolment', [
            'model' => $model,
            'payments' => $payments,
        ]);
    }

    /**
     * Creates a new Student model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Student(); 

        $user_info = Yii::$app->user->identity->userinfo;
        $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
        $rolenames =  ArrayHelper::map($roles, 'name','name');

        $discountModel = new Discount();
        $enroleeTypeModel = new StudentEnroleeType();
        $packageStudentModel = new PackageStudent();
        $studentTuitionModel = new StudentTuition();
        $enhancementModel = new Enhancement();
        $coachingModel = new Coaching();
        $studentProgramModel = new StudentProgram(); 

        $incomeEnrolmentModel = new IncomeEnrolment();

        $incomeModel = new Income();

        $provinces = Province::find()->select(['tblprovince.province_c, concat(province_m," (",abbreviation,")") as name'])->leftJoin('tblregion','tblregion.region_c = tblprovince.region_c')->orderBy(['tblregion.region_sort' => SORT_ASC, 'tblprovince.province_m' => SORT_ASC])->asArray()->all();
        $provinces = ArrayHelper::map($provinces, 'province_c', 'name');

        $access = AccessProgram::findOne(['user_id' => Yii::$app->user->identity->userinfo->user_id]);

        if(in_array('TopManagement',$rolenames)){
            $seasons = $access ? $access->branch_program_id != '' ? Season::find()
                   ->select([
                    'accounting_season.id as id',
                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name'
                   ])
                   ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                   ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                   ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                   ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                   ->asArray()
                   ->orderBy(['accounting_season.end_date' => SORT_DESC])
                   ->all() : Season::find()
                   ->select([
                    'accounting_season.id as id',
                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name'
                   ])
                   ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                   ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                   ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                   ->asArray()
                   ->orderBy(['accounting_season.end_date' => SORT_DESC])
                   ->all() : Season::find()
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
        }else{
           $seasons = $access ? $access->branch_program_id != '' ? Season::find()
                   ->select([
                    'accounting_season.id as id',
                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name'
                   ])
                   ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                   ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                   ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                   ->where(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                   ->andWhere(['accounting_branch_program.id' => $access->branch_program_id])
                   ->asArray()
                   ->orderBy(['accounting_season.end_date' => SORT_DESC])
                   ->all() : Season::find()
                   ->select([
                    'accounting_season.id as id',
                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name'
                   ])
                   ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                   ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                   ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                   ->where(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                   ->asArray()
                   ->orderBy(['accounting_season.end_date' => SORT_DESC])
                   ->all() : Season::find()
                   ->select([
                    'accounting_season.id as id',
                    'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name) as name'
                   ])
                   ->leftJoin('accounting_branch_program','accounting_branch_program.id = accounting_season.branch_program_id')
                   ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                   ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                   ->where(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                   ->asArray()
                   ->orderBy(['accounting_season.end_date' => SORT_DESC])
                   ->all(); 
        }

        $seasons = ArrayHelper::map($seasons, 'id', 'name');

        $enroleeTypes = EnroleeType::find()->asArray()->all();
        $enroleeTypes = ArrayHelper::map($enroleeTypes, 'id', 'name');

        if(in_array('TopManagement',$rolenames)){
            $packages = $access ? $access->branch_program_id != '' ? Package::find()->select([
                    'accounting_package.id as id',
                    'concat(accounting_package.code," - ",accounting_package_type.name," - TIER ",accounting_package.tier) as name'
                    ])
                    ->leftJoin('accounting_package_type','accounting_package_type.id = accounting_package.package_type_id')
                    ->andwhere(['accounting_package.branch_id' => $access->branchProgram->branch_id, 'accounting_package.program_id' => $access->branchProgram->program_id])
                    ->asArray()
                    ->all() : Package::find()->select([
                    'accounting_package.id as id',
                    'concat(accounting_package.code," - ",accounting_package_type.name," - TIER ",accounting_package.tier) as name'
                    ])
                    ->leftJoin('accounting_package_type','accounting_package_type.id = accounting_package.package_type_id')
                    ->asArray()
                    ->all() : Package::find()->select([
                    'accounting_package.id as id',
                    'concat(accounting_package.code," - ",accounting_package_type.name," - TIER ",accounting_package.tier) as name'
                    ])
                    ->leftJoin('accounting_package_type','accounting_package_type.id = accounting_package.package_type_id')
                    ->asArray()
                    ->all();
        }else{
            $packages = $access ? $access->branch_program_id != '' ? Package::find()->select([
                    'accounting_package.id as id',
                    'concat(accounting_package.code," - ",accounting_package_type.name," - TIER ",accounting_package.tier) as name'
                    ])
                    ->leftJoin('accounting_package_type','accounting_package_type.id = accounting_package.package_type_id')
                    ->andWhere(['accounting_package.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                    ->andwhere(['accounting_package.branch_id' => $access->branchProgram->branch_id, 'accounting_package.program_id' => $access->branchProgram->program_id])
                    ->asArray()
                    ->all() : Package::find()->select([
                    'accounting_package.id as id',
                    'concat(accounting_package.code," - ",accounting_package_type.name," - TIER ",accounting_package.tier) as name'
                    ])
                    ->leftJoin('accounting_package_type','accounting_package_type.id = accounting_package.package_type_id')
                    ->andWhere(['accounting_package.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                    ->asArray()
                    ->all() : Package::find()->select([
                    'accounting_package.id as id',
                    'concat(accounting_package.code," - ",accounting_package_type.name," - TIER ",accounting_package.tier) as name'
                    ])
                    ->leftJoin('accounting_package_type','accounting_package_type.id = accounting_package.package_type_id')
                    ->andWhere(['accounting_package.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                    ->asArray()
                    ->all();
        }

        

        $packages = ArrayHelper::map($packages, 'id', 'name');

        $discountTypes = DiscountType::find()->all();
        $discountTypes = ArrayHelper::map($discountTypes, 'id', 'name');

        if(in_array('TopManagement',$rolenames)){
            $coachingPackages = $access ? $access->branch_program_id != '' ? Package::find()->select([
                    'accounting_package.id as id',
                    'concat(accounting_package.code," - ",accounting_package_type.name," - TIER ",accounting_package.tier) as name'
                    ])
                    ->leftJoin('accounting_package_type','accounting_package_type.id = accounting_package.package_type_id')
                    ->leftJoin('accounting_enrolee_type','accounting_enrolee_type.id = accounting_package_type.enrolee_type_id')
                    ->andWhere(['accounting_enrolee_type.id' => '4'])
                    ->andwhere(['accounting_package.branch_id' => $access->branchProgram->branch_id, 'accounting_package.program_id' => $access->branchProgram->program_id])
                    ->asArray()
                    ->all() : Package::find()->select([
                    'accounting_package.id as id',
                    'concat(accounting_package.code," - ",accounting_package_type.name," - TIER ",accounting_package.tier) as name'
                    ])
                    ->leftJoin('accounting_package_type','accounting_package_type.id = accounting_package.package_type_id')
                    ->leftJoin('accounting_enrolee_type','accounting_enrolee_type.id = accounting_package_type.enrolee_type_id')
                    ->andWhere(['accounting_enrolee_type.id' => '4'])
                    ->asArray()
                    ->all() : Package::find()->select([
                    'accounting_package.id as id',
                    'concat(accounting_package.code," - ",accounting_package_type.name," - TIER ",accounting_package.tier) as name'
                    ])
                    ->leftJoin('accounting_package_type','accounting_package_type.id = accounting_package.package_type_id')
                    ->leftJoin('accounting_enrolee_type','accounting_enrolee_type.id = accounting_package_type.enrolee_type_id')
                    ->andWhere(['accounting_enrolee_type.id' => '4'])
                    ->asArray()
                    ->all();
        }else{
            $coachingPackages = $access ? $access->branch_program_id != '' ? Package::find()->select([
                    'accounting_package.id as id',
                    'concat(accounting_package.code," - ",accounting_package_type.name," - TIER ",accounting_package.tier) as name'
                    ])
                    ->leftJoin('accounting_package_type','accounting_package_type.id = accounting_package.package_type_id')
                    ->leftJoin('accounting_enrolee_type','accounting_enrolee_type.id = accounting_package_type.enrolee_type_id')
                    ->andWhere(['accounting_package.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                    ->andWhere(['accounting_enrolee_type.id' => '4'])
                    ->andwhere(['accounting_package.branch_id' => $access->branchProgram->branch_id, 'accounting_package.program_id' => $access->branchProgram->program_id])
                    ->asArray()
                    ->all() : Package::find()->select([
                    'accounting_package.id as id',
                    'concat(accounting_package.code," - ",accounting_package_type.name," - TIER ",accounting_package.tier) as name'
                    ])
                    ->leftJoin('accounting_package_type','accounting_package_type.id = accounting_package.package_type_id')
                    ->leftJoin('accounting_enrolee_type','accounting_enrolee_type.id = accounting_package_type.enrolee_type_id')
                    ->andWhere(['accounting_package.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                    ->andWhere(['accounting_enrolee_type.id' => '4'])
                    ->asArray()
                    ->all() : Package::find()->select([
                    'accounting_package.id as id',
                    'concat(accounting_package.code," - ",accounting_package_type.name," - TIER ",accounting_package.tier) as name'
                    ])
                    ->leftJoin('accounting_package_type','accounting_package_type.id = accounting_package.package_type_id')
                    ->leftJoin('accounting_enrolee_type','accounting_enrolee_type.id = accounting_package_type.enrolee_type_id')
                    ->andWhere(['accounting_package.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                    ->andWhere(['accounting_enrolee_type.id' => '4'])
                    ->asArray()
                    ->all();
        }

        $coachingPackages = ArrayHelper::map($coachingPackages, 'id', 'name');

        $incomeCodes = IncomeCode::find()->select(['id, concat(name," - ",description) as name'])->all();
        $incomeCodes = ArrayHelper::map($incomeCodes, 'id', 'name');

        if(Yii::$app->request->isAjax && ($model->load(Yii::$app->request->post()) && $discountModel->load(Yii::$app->request->post()))){
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model, $discountModel);
        } 
        else if ($model->load(Yii::$app->request->post())) {
            $postData = Yii::$app->request->post();
            $season = Season::findOne($postData['StudentEnroleeType']['season_id']);
            $latestOr = $this->takeOrNow($postData['StudentEnroleeType']['season_id']);
            

            if($latestOr == 'No Available OR')
            {
                \Yii::$app->getSession()->setFlash('danger', ' No Available OR. Please request additional ORs to the management to save payments.');
            }else{
                $model->status = 'Active';

                if(in_array('SchoolBased',$rolenames)){
                
                    $model->school_id = Yii::$app->user->identity->userinfo->SCHOOL_C;
                }
                if($model->save())
                {
                    $enroleeTypeModel->season_id = $postData['StudentEnroleeType']['season_id'];
                    $enroleeTypeModel->student_id = $model->id;
                    $enroleeTypeModel->enrolee_type_id = $postData['StudentEnroleeType']['enrolee_type_id'];
                    if($enroleeTypeModel->save())
                    {
                        $packageStudentModel->season_id = $postData['StudentEnroleeType']['season_id'];
                        $packageStudentModel->student_id = $model->id;
                        $packageStudentModel->package_id = $postData['PackageStudent']['package_id'];
                        $packageStudentModel->amount = $postData['regular_review_price'];
                    }

                    if($postData['Enhancement']['amount'] != '' || $postData['Enhancement']['amount'] != '0')
                    {
                        $enhancementModel->season_id = $postData['StudentEnroleeType']['season_id'];
                        $enhancementModel->student_id = $model->id;
                        $enhancementModel->amount = $postData['Enhancement']['amount'];
                        $enhancementModel->save();
                    }

                    if($postData['Discount']['discount_type_id'] != '')
                    {
                        $discountModel->discount_type_id = $postData['Discount']['discount_type_id'];
                        $discountModel->season_id = $postData['StudentEnroleeType']['season_id'];
                        $discountModel->student_id = $model->id;
                        $discountModel->code_number = $postData['Discount']['code_number'];
                        $discountModel->amount = $postData['Discount']['amount'];
                        $discountModel->remarks = $postData['Discount']['remarks'];
                        $discountModel->save();
                    }

                    if($postData['Coaching']['package_id'] != '')
                    {
                        $coachingModel->season_id = $postData['StudentEnroleeType']['season_id'];
                        $coachingModel->student_id = $model->id;
                        $coachingModel->package_id = $postData['Coaching']['package_id'];
                        $coachingModel->amount = $postData['coaching_amount'];
                        $coachingModel->save();
                    }

                    if($packageStudentModel->save())
                    {
                        $studentTuitionModel->season_id = $postData['StudentEnroleeType']['season_id'];
                        $studentTuitionModel->student_id = $model->id;
                        $studentTuitionModel->package_student_id = $packageStudentModel->id;

                        if($postData['Discount']['discount_type_id'] != '')
                        {
                            $studentTuitionModel->discount_id = $discountModel->id;
                        }

                        if($enhancementModel->amount != '' || $enhancementModel->amount != '0')
                        {
                            $studentTuitionModel->enhancement_id = $enhancementModel->id;
                        }

                        $studentTuitionModel->save();
                    }

                    $studentProgramModel->student_id = $model->id;
                    $studentProgramModel->program_id = $season->branchProgram->program->id;
                    $studentProgramModel->save();

                    $studentBranchProgramModel = StudentBranchProgram::findOne(['student_id' => $model->id, 'branch_id' => $season->branchProgram->branch->id, 'program_id' => $season->branchProgram->program->id]) ? StudentBranchProgram::findOne(['student_id' => $model->id, 'branch_id' => $season->branchProgram->branch->id, 'program_id' => $season->branchProgram->program->id]) : new StudentBranchProgram();

                    $studentBranchProgramModel->student_id = $model->id;
                    $studentBranchProgramModel->branch_id = $season->branchProgram->branch->id;
                    $studentBranchProgramModel->program_id = $season->branchProgram->program->id;
                    $studentBranchProgramModel->save();

                    $incomeEnrolmentModel->season_id = $postData['StudentEnroleeType']['season_id'];
                    $incomeEnrolmentModel->or_no = $this->takeOrNow($postData['StudentEnroleeType']['season_id']);
                    $incomeEnrolmentModel->code_id = $postData['IncomeEnrolment']['code_id'];
                    $incomeEnrolmentModel->student_id = $model->id;
                    $incomeEnrolmentModel->amount = $postData['IncomeEnrolment']['amount'];
                    if($incomeEnrolmentModel->save(false))
                    {
                        $incomeModel->income_type_id = 1;
                        $incomeModel->branch_id = Yii::$app->user->identity->userinfo->BRANCH_C;
                        $incomeModel->program_id =  $season->branchProgram->program_id;
                        $incomeModel->income_id = $incomeEnrolmentModel->id;
                        $incomeModel->amount_type = $postData['Income']['amount_type'];
                        $incomeModel->transaction_number = $postData['Income']['transaction_number'];
                        $incomeModel->save(false);

                
                        $orModel = SeasonOr::find()->where(['season_id' => $postData['StudentEnroleeType']['season_id'], 'or_no' => $this->takeOrNow($postData['StudentEnroleeType']['season_id'])])->one() ? SeasonOr::find()->where(['season_id' => $postData['StudentEnroleeType']['season_id'], 'or_no' => $this->takeOrNow($postData['StudentEnroleeType']['season_id'])])->one() : new SeasonOr();
                        $orModel->season_id = $postData['StudentEnroleeType']['season_id'];
                        $orModel->season_or_list_id = $this->takeOrList($postData['StudentEnroleeType']['season_id']);
                        $orModel->or_no = $this->takeOrNow($postData['StudentEnroleeType']['season_id']);
                        $orModel->save();
                    }

                    \Yii::$app->getSession()->setFlash('success', 'Student information has been saved. Generated user account for the student has also been created');

                    return $this->redirect(['list']);
                }
            }
        }

        $citymuns = [];

        return $this->render('create', [
            'model' => $model,
            'seasons' => $seasons,
            'discountModel' => $discountModel,
            'enroleeTypeModel' => $enroleeTypeModel,
            'packageStudentModel' => $packageStudentModel,
            'studentTuitionModel' => $studentTuitionModel,
            'enhancementModel' => $enhancementModel,
            'coachingModel' => $coachingModel,
            'incomeEnrolmentModel' => $incomeEnrolmentModel,
            'incomeModel' => $incomeModel,
            'seasons' => $seasons,
            'enroleeTypes' => $enroleeTypes,
            'packages' => $packages,
            'discountTypes' => $discountTypes,
            'coachingPackages' => $coachingPackages,
            'incomeCodes' => $incomeCodes,
            'provinces' => $provinces,
            'citymuns' => $citymuns,
        ]);
    }

    /**
     * Updates an existing Student model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $provinces = Province::find()->select(['tblprovince.province_c, concat(province_m," (",abbreviation,")") as name'])->leftJoin('tblregion','tblregion.region_c = tblprovince.region_c')->orderBy(['tblregion.region_sort' => SORT_ASC, 'tblprovince.province_m' => SORT_ASC])->asArray()->all();
        $provinces = ArrayHelper::map($provinces, 'province_c', 'name');
        
        $citymuns = Citymun::find()->where(['province_c'=> $model->province_id])->all();
        $citymuns = ArrayHelper::map($citymuns,'citymun_c','citymun_m');

        if ($model->load(Yii::$app->request->post()) && $model->save(false)) {
            \Yii::$app->getSession()->setFlash('success', 'Student information has been updated.');
            return $this->redirect(['list']);
        }

        return $this->render('update', [
            'model' => $model,
            'provinces' => $provinces,
            'citymuns' => $citymuns,
        ]);
    }

    /**
     * Deletes an existing Student model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if($model)
        {
            if($model->delete())
            {
                \Yii::$app->getSession()->setFlash('success', 'Student information has been deleted.');
            }else{
                \Yii::$app->getSession()->setFlash('danger', 'Deletion has occcurred an error.');
            }
        }
        return $this->redirect(['list']);
    }

    /**
     * Finds the Student model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Student the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Student::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
