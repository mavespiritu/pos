<?php

namespace common\modules\accounting\controllers;

use Yii;
use common\modules\accounting\models\Notification;
use common\modules\accounting\models\Transferee;
use common\modules\accounting\models\TransfereeSearch;
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
use common\modules\accounting\models\StudentBranchProgram;
use common\modules\accounting\models\AccessProgram;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;
use yii\widgets\ActiveForm;
/**
 * TransfereeController implements the CRUD actions for Transferee model.
 */
class TransfereeController extends Controller
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
                'only' => ['index', 'view'],
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['manageTransferee'],
                    ],
                    [
                        'actions' => ['view'],
                        'allow' => true,
                        'roles' => ['viewTransferee'],
                    ],
                ],
            ],
        ];
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
     * Lists all Transferee models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new TransfereeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Transferee model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $user_info = Yii::$app->user->identity->userinfo;
        $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
        $rolenames =  ArrayHelper::map($roles, 'name','name');

        $access = AccessProgram::findOne(['user_id' => Yii::$app->user->identity->userinfo->user_id]);

        $model = $this->findModel($id);
        $newSeason = Season::find()->where(['id' => $model->to_season_id])->one();

        $notification = Notification::findOne(['branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C, 'model' => 'Season', 'model_id' => $newSeason->id]) ? Notification::findOne(['branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C, 'model' => 'Season', 'model_id' => $newSeason->id]) : new Notification(); 
            if($this->takeOrNow($newSeason->id) == 'No Available OR')
            {
                $notification->branch_id = Yii::$app->user->identity->userinfo->BRANCH_C; 
                $notification->model = 'Season';
                $notification->model_id = $newSeason->id;
                $notification->message = $newSeason->seasonName.' has used all the official receipts. Add official receipts immediately to continue operation.';
                $notification->save();
            }
            
        if(!StudentEnroleeType::findOne(['student_id' => $model->student_id, 'season_id' => $newSeason->id]))
        {          
            $season = Season::find()->where(['id' => $model->from_season_id])->one();
            $discountModel = Discount::findOne(['student_id' => $model->student_id, 'season_id' => $season->id]);
            $enroleeTypeModel = StudentEnroleeType::findOne(['student_id' => $model->student_id, 'season_id' => $season->id]);
            $packageStudentModel = PackageStudent::findOne(['student_id' => $model->student_id, 'season_id' => $season->id]);
            $studentTuitionModel = StudentTuition::findOne(['student_id' => $model->student_id, 'season_id' => $season->id]);
            $enhancementModel = Enhancement::findOne(['student_id' => $model->student_id, 'season_id' => $season->id]);
            $coachingModel = Coaching::findOne(['student_id' => $model->student_id, 'season_id' => $season->id]);

            $incomeEnrolmentModel = IncomeEnrolment::find()
                ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_enrolment.id and accounting_income.income_type_id = 1')
                ->where(['season_id' => $season->id, 'student_id' => $model->student_id])
                ->orderBy(['accounting_income.datetime' => SORT_ASC])
                ->one();

            $incomeModel = Income::find()
                            ->leftJoin('accounting_income_enrolment','accounting_income_enrolment.id = accounting_income.income_id')
                ->where(['accounting_income_enrolment.season_id' => $season->id, 'accounting_income_enrolment.student_id' => $model->student_id, 'accounting_income.income_type_id' => '1'])
                ->orderBy(['accounting_income.datetime' => SORT_ASC])
                ->one();

            $newDiscountModel = Discount::findOne(['student_id' => $model->student_id, 'season_id' => $newSeason->id]) ? Discount::findOne(['student_id' => $model->student_id, 'season_id' => $newSeason->id]) : new Discount();
            $newEnroleeTypeModel = StudentEnroleeType::findOne(['student_id' => $model->student_id, 'season_id' => $newSeason->id]) ? StudentEnroleeType::findOne(['student_id' => $model->student_id, 'season_id' => $newSeason->id]) : new StudentEnroleeType();
            $newPackageStudentModel = PackageStudent::findOne(['student_id' => $model->student_id, 'season_id' => $newSeason->id]) ? PackageStudent::findOne(['student_id' => $model->student_id, 'season_id' => $newSeason->id]) : new PackageStudent();
            $newStudentTuitionModel = StudentTuition::findOne(['student_id' => $model->student_id, 'season_id' => $newSeason->id]) ? StudentTuition::findOne(['student_id' => $model->student_id, 'season_id' => $newSeason->id]) : new StudentTuition();
            $newEnhancementModel = Enhancement::findOne(['student_id' => $model->student_id, 'season_id' => $newSeason->id]) ? Enhancement::findOne(['student_id' => $model->student_id, 'season_id' => $season->id]) : new Enhancement();
            $newCoachingModel = Coaching::findOne(['student_id' => $model->student_id, 'season_id' => $newSeason->id]) ? Coaching::findOne(['student_id' => $model->student_id, 'season_id' => $newSeason->id]) : new Coaching();
            
            $newIncomeEnrolmentModel = IncomeEnrolment::find()
                ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_enrolment.id and accounting_income.income_type_id = 1')
                ->where(['season_id' => $newSeason->id, 'student_id' => $model->student_id])
                ->orderBy(['accounting_income.datetime' => SORT_ASC])
                ->one() ? IncomeEnrolment::find()
                ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_enrolment.id and accounting_income.income_type_id = 1')
                ->where(['season_id' => $newSeason->id, 'student_id' => $model->student_id])
                ->orderBy(['accounting_income.datetime' => SORT_ASC])
                ->one() : new IncomeEnrolment();

            $newIncomeModel = Income::find()
                            ->leftJoin('accounting_income_enrolment','accounting_income_enrolment.id = accounting_income.income_id')
                ->where(['accounting_income_enrolment.season_id' => $newSeason->id, 'accounting_income_enrolment.student_id' => $model->student_id, 'accounting_income.income_type_id' => '1'])
                ->orderBy(['accounting_income.datetime' => SORT_ASC])
                ->one() ? Income::find()
                            ->leftJoin('accounting_income_enrolment','accounting_income_enrolment.id = accounting_income.income_id')
                ->where(['accounting_income_enrolment.season_id' => $newSeason->id, 'accounting_income_enrolment.student_id' => $model->student_id, 'accounting_income.income_type_id' => '1'])
                ->orderBy(['accounting_income.datetime' => SORT_ASC])
                ->one() : new Income();

            $newStudentProgramModel = StudentProgram::findOne(['student_id' => $model->student_id]) ? StudentProgram::findOne(['student_id' => $model->student_id]) : new StudentProgram(); 

            $orStatus = $this->takeOrStatus($newSeason->id);
            $current_or = $this->takeOrNow($season->id);

            $finalTuition = $studentTuitionModel->packageStudent->amount + $studentTuitionModel->enhancement->amount;
            $finalTuition = $studentTuitionModel->discount_id != '' ? $finalTuition - $studentTuitionModel->discount->amount : $finalTuition;

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

            if(Yii::$app->request->isAjax && ($newDiscountModel->load(Yii::$app->request->post()))){
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($newDiscountModel);
            }else if(Yii::$app->request->post())
            {
                $postData = Yii::$app->request->post();
                $advanceEnrolment = AdvanceEnrolment::findOne(['season_id' => $newSeason->id, 'student_id' => $model->student_id]);
                if($advanceEnrolment)
                {
                    $advanceEnrolment->delete();
                }

                $newEnroleeTypeModel->season_id = $newSeason->id;
                $newEnroleeTypeModel->student_id = $model->student_id;
                $newEnroleeTypeModel->enrolee_type_id = $postData['StudentEnroleeType']['enrolee_type_id'];
                if($newEnroleeTypeModel->save())
                {
                    $newPackageStudentModel->season_id = $newSeason->id;
                    $newPackageStudentModel->student_id = $model->student_id;
                    $newPackageStudentModel->package_id = $postData['PackageStudent']['package_id'];

                    $pck = Package::findOne($postData['PackageStudent']['package_id']);
                    $newPackageStudentModel->amount = $pck ? $pck->amount : 0;
                }

                

                if($postData['Enhancement']['amount'] != '' || $postData['Enhancement']['amount'] != '0')
                {
                    $newEnhancementModel->season_id = $newSeason->id;
                    $newEnhancementModel->student_id = $model->student_id;
                    $newEnhancementModel->amount = $postData['Enhancement']['amount'];
                    $newEnhancementModel->save();
                }

                if($postData['Discount']['discount_type_id'] != '')
                {
                    $newDiscountModel->discount_type_id = $postData['Discount']['discount_type_id'];
                    $newDiscountModel->season_id = $newSeason->id;
                    $newDiscountModel->student_id = $model->student_id;
                    $newDiscountModel->amount = $postData['Discount']['amount'];
                    $newDiscountModel->code_number = $postData['Discount']['code_number'];
                    $newDiscountModel->remarks = $postData['Discount']['remarks'];
                    $newDiscountModel->save();
                }

                if($postData['Coaching']['package_id'] != '')
                {
                    $newCoachingModel->season_id = $newSeason->id;
                    $newCoachingModel->student_id = $model->student_id;
                    $newCoachingModel->package_id = $postData['Coaching']['package_id'];

                    $chng = Package::findOne($postData['Coaching']['package_id']);
                    $newCoachingModel->amount = $chng ? $chng->amount : 0;
                    $newCoachingModel->save();
                }

                if($newPackageStudentModel->save())
                {
                    $newStudentTuitionModel->season_id = $newSeason->id;
                    $newStudentTuitionModel->student_id = $model->student_id;
                    $newStudentTuitionModel->package_student_id = $newPackageStudentModel->id;

                    if($postData['Discount']['discount_type_id'] != '')
                    {
                        $newStudentTuitionModel->discount_id = $newDiscountModel->id;
                    }

                    if($newEnhancementModel->amount != '' || $newEnhancementModel->amount != '0')
                    {
                        $newStudentTuitionModel->enhancement_id = $newEnhancementModel->id;
                    }

                    $newStudentTuitionModel->save();
                }


                $newIncomeEnrolmentModel->season_id = $newSeason->id;
                $newIncomeEnrolmentModel->or_no = $this->takeOrNow($newSeason->id);
                $newIncomeEnrolmentModel->code_id = $postData['IncomeEnrolment']['code_id'];
                $newIncomeEnrolmentModel->student_id = $model->student_id;
                $newIncomeEnrolmentModel->amount = $postData['IncomeEnrolment']['amount'];
                if($newIncomeEnrolmentModel->save(false))
                {
                    $newIncomeModel->income_type_id = 1;
                    $newIncomeModel->branch_id = Yii::$app->user->identity->userinfo->BRANCH_C;
                    $newIncomeModel->program_id =  $newSeason->branchProgram->program_id;
                    $newIncomeModel->income_id = $newIncomeEnrolmentModel->id;
                    $newIncomeModel->amount_type = $postData['Income']['amount_type'];
                    $newIncomeModel->save(false);

            
                    $orModel = SeasonOr::find()->where(['season_id' => $newSeason->id, 'or_no' => $this->takeOrNow($newSeason->id)])->one() ? SeasonOr::find()->where(['season_id' => $newSeason->id, 'or_no' => $this->takeOrNow($newSeason->id)])->one() : new SeasonOr();
                    $orModel->season_id = $newSeason->id;
                    $orModel->season_or_list_id = $this->takeOrList($newSeason->id);
                    $orModel->or_no = $this->takeOrNow($newSeason->id);
                    $orModel->save();
                }

                $newStudentProgramModel->student_id = $model->student_id;
                $newStudentProgramModel->program_id = $newSeason->branchProgram->program->id;
                $newStudentProgramModel->save();

                $studentBranchProgramModel = StudentBranchProgram::findOne(['student_id' => $model->student_id, 'branch_id' => $newSeason->branchProgram->branch->id, 'program_id' => $newSeason->branchProgram->program->id]) ? StudentBranchProgram::findOne(['student_id' => $model->student_id, 'branch_id' => $newSeason->branchProgram->branch->id, 'program_id' => $newSeason->branchProgram->program->id]) : new StudentBranchProgram();

                $studentBranchProgramModel->student_id = $model->student_id;
                $studentBranchProgramModel->branch_id = $newSeason->branchProgram->branch->id;
                $studentBranchProgramModel->program_id = $newSeason->branchProgram->program->id;
                $studentBranchProgramModel->save();

                $notification = Notification::find()->where(['model' => 'Transferee', 'model_id' => $model->id, 'branch_id' => $newSeason->branchProgram->branch->id])->one();
                if($notification)
                {
                    $notification->delete();
                }

                \Yii::$app->getSession()->setFlash('success', 'Student is successfully enrolled and removed in transferred student list');
                return $this->redirect(['index']);
            }

            return $this->render('view', [
                'model' => $this->findModel($id),
                'season' => $season,
                'newSeason' => $newSeason,
                'discountModel' => $discountModel,
                'enroleeTypeModel' => $enroleeTypeModel,
                'packageStudentModel' => $packageStudentModel,
                'studentTuitionModel' => $studentTuitionModel,
                'enhancementModel' => $enhancementModel,
                'coachingModel' => $coachingModel,
                'incomeEnrolmentModel' => $incomeEnrolmentModel,
                'incomeModel' => $incomeModel,
                'payments' => $payments,
                'newDiscountModel' => $newDiscountModel,
                'newEnroleeTypeModel' => $newEnroleeTypeModel,
                'newPackageStudentModel' => $newPackageStudentModel,
                'newStudentTuitionModel' => $newStudentTuitionModel,
                'newEnhancementModel' => $newEnhancementModel,
                'newCoachingModel' => $newCoachingModel,
                'newIncomeEnrolmentModel' => $newIncomeEnrolmentModel,
                'newIncomeModel' => $newIncomeModel,
                'discountModel' => $discountModel,
                'seasons' => $seasons,
                'enroleeTypes' => $enroleeTypes,
                'packages' => $packages,
                'discountTypes' => $discountTypes,
                'coachingPackages' => $coachingPackages,
                'incomeCodes' => $incomeCodes,
                'current_or' => $current_or,
                'orStatus' => $orStatus,
                'finalTuition' => $finalTuition
            ]);
        }else{
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * Finds the Transferee model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Transferee the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Transferee::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
