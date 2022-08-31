<?php

namespace common\modules\accounting\controllers;

use Yii;
use common\modules\accounting\models\AccessProgram;
use common\modules\accounting\models\Season;
use common\modules\accounting\models\SeasonSearch;
use common\modules\accounting\models\Freebie;
use common\modules\accounting\models\TargetAcademic;
use common\modules\accounting\models\TargetEmergencyFund;
use common\modules\accounting\models\TargetFood;
use common\modules\accounting\models\TargetFreebie;
use common\modules\accounting\models\TargetIncome;
use common\modules\accounting\models\TargetProgram;
use common\modules\accounting\models\TargetRebate;
use common\modules\accounting\models\TargetReview;
use common\modules\accounting\models\TargetRoyaltyFee;
use common\modules\accounting\models\TargetStaffSalary;
use common\modules\accounting\models\TargetTransportation;
use common\modules\accounting\models\TargetTax;
use common\modules\accounting\models\TargetUtility;
use common\modules\accounting\models\TargetVenueRental;
use common\modules\accounting\models\EnroleeType;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use kartik\mpdf\Pdf;

class CostEstimationController extends \yii\web\Controller
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
                'only' => ['index'],
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['TopManagement','AreaManager','AccountingStaff'],
                    ]
                ],
            ],
        ];
    }

    public function actionIndex()
    {
      $searchModel = new SeasonSearch();
      $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

      return $this->render('index',[
        'searchModel' => $searchModel,
        'dataProvider' => $dataProvider,
      ]);
    }

    public function actionCreate()
    {
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

      $model = new TargetIncome();

      $incomeModels = [];
      $enroleeTypes = EnroleeType::find()->all();

      if($enroleeTypes)
      {
      	foreach($enroleeTypes as $enroleeType)
      	{
      		$incomeModel = new TargetIncome();
      		$incomeModel->enrolee_type_id = $enroleeType->id;
      		$incomeModels[] = $incomeModel;
      	}
      }

      $taxModels = []; 

      $taxModels[] = new TargetTax();

      $programModels = [];
      $programs = [
      	'___ Hours of Lecture (Senior Lecturer)',
      	'___ Hours of Lecture (Junior Lecturer)',
      	'___ Hours of Lecture (Adjunct Lecturer)',
      	'___ Hours of Lecture (Guest Lecturer)',
  		];

    	foreach($programs as $program)
    	{
    		$programModel = new TargetProgram();
    		$programModel->label = $program;
    		$programModels[] = $programModel;
    	}

    	$venueRentalModels = [];
    	$venueRentals = ['___ Months/Days'];

    	foreach($venueRentals as $rental)
    	{
    		$venueRentalModel = new TargetVenueRental();
    		$venueRentalModel->label = $rental;
    		$venueRentalModels[] = $venueRentalModel;
    	}

    	$freebieModels = [];
    	$freebies = Freebie::find()->all();
    	if($freebies)
    	{
    		foreach($freebies as $freebie)
    		{
    			$freebieModel = new TargetFreebie();
    			$freebieModel->freebie_id = $freebie->id;
    			$freebieModels[] = $freebieModel;
    		}
    	}

    	$reviewModels = [];
    	$reviewMaterials = ['Exams'];

    	foreach($reviewMaterials as $material)
    	{
    		$reviewModel = new TargetReview();
    		$reviewModel->label = $material;
    		$reviewModels[] = $reviewModel;
    	}

    	$foodModels = [];
    	$foods = ['___ Days'];

    	foreach($foods as $food)
    	{
    		$foodModel = new TargetFood();
    		$foodModel->label = $food;
    		$foodModels[] = $foodModel;
    	}

    	$transportationModels = [];
    	$transportations = [
    		'___ Ticket Allowance for Lecturers',
    		'___ Transportation Allowance for Lecturers'
    	];

    	foreach($transportations as $transportation)
    	{
    		$transportationModel = new TargetTransportation();
    		$transportationModel->label = $transportation;
    		$transportationModels[] = $transportationModel;
    	}

    	$staffSalaryModels = [];
    	$staffSalaries = ['___ Days/Months'];

    	foreach($staffSalaries as $salary)
    	{
    		$staffSalaryModel = new TargetStaffSalary();
    		$staffSalaryModel->label = $salary;
    		$staffSalaryModels[] = $staffSalaryModel;
    	}    

     	$rebateModels = [];
     	$rebates = [
     		'___ Incentives',
     		'___ Faculty Development Fund'
     	];

     	foreach($rebates as $rebate)
     	{
     		$rebateModel = new TargetRebate();
     		$rebateModel->label = $rebate;
     		$rebateModels[] = $rebateModel;
     	}

     	$utilityModels = [];
     	$utilities = [
     		'Water Bill',
     		'Electricity',
     		'Staff House',
     		'Phone Bill',
     		'Wifi',
     		'Office Supplies',
     		'Transpo for the staff',
     		'Marketing facebook ads/flyers/tarp',
     	];

     	foreach($utilities as $utility)
     	{
     		$utilityModel = new TargetUtility();
     		$utilityModel->label = $utility;
     		$utilityModels[] = $utilityModel;
     	}

     	$academicModels = [];
     	$academics = [
     		'Grand Day',
     		'Support Drive',
     		'Victory Party',
     		'Stress Management',
     	];

     	foreach($academics as $academic)
     	{
     		$academicModel = new TargetAcademic();
     		$academicModel->label = $academic;
     		$academicModels[] = $academicModel;
     	}

     	$emergencyFundModels = [];
     	$emergencies = ['Emergency Fund'];

     	foreach ($emergencies as $emergency)
     	{
     		$emergencyFundModel = new TargetEmergencyFund();
     		$emergencyFundModel->label = $emergency;
     		$emergencyFundModels[] = $emergencyFundModel;	
     	}

   	  $royaltyFeeModels = [];
      
      $royaltyFeeModels[] = new TargetRoyaltyFee();

      if(Yii::$app->request->post())
      {
        $postData = Yii::$app->request->post();

        $season = Season::findOne($postData['TargetIncome']['season_id']);     

        $postIncomeModels = $postData['TargetIncome'];

        unset($postIncomeModels['season_id']);

        if($enroleeTypes)
        {
          foreach($enroleeTypes as $i => $enroleeType)
          {
            $incomeModel = new TargetIncome();
            $incomeModel->season_id = $season->id;
            $incomeModel->enrolee_type_id = $enroleeType->id;
            $incomeModel->quantity = $postIncomeModels[$i]['quantity'];
            $incomeModel->unit_price = $postIncomeModels[$i]['unit_price'];
            $incomeModel->save(false);
          }
        }

        $postProgramModels = $postData['TargetProgram'];

        foreach($programs as $i => $program)
        {
          $programModel = new TargetProgram();
          $programModel->season_id = $season->id;
          $programModel->label = $program;
          $programModel->quantity = $postProgramModels[$i]['quantity'];
          $programModel->unit_price = $postProgramModels[$i]['unit_price'];
          $programModel->save(false);
        }

        $postVenueRentalModels = $postData['TargetVenueRental'];

        foreach($venueRentals as $i => $rental)
        {
          $venueRentalModel = new TargetVenueRental();
          $venueRentalModel->season_id = $season->id;
          $venueRentalModel->label = $rental;
          $venueRentalModel->quantity = $postVenueRentalModels[$i]['quantity'];
          $venueRentalModel->unit_price = $postVenueRentalModels[$i]['unit_price'];
          $venueRentalModel->save(false);
        }

        $postFreebieModels = $postData['TargetFreebie'];

        if($freebies)
        {
          foreach($freebies as $i => $freebie)
          {
            $freebieModel = new TargetFreebie();
            $freebieModel->season_id = $season->id;
            $freebieModel->freebie_id = $freebie->id;
            $freebieModel->quantity = $postFreebieModels[$i]['quantity'];
            $freebieModel->unit_price = $postFreebieModels[$i]['unit_price'];
            $freebieModel->save(false);
          }
        }

        $postReviewModels = $postData['TargetReview'];

        foreach($reviewMaterials as $i => $material)
        {
          $reviewModel = new TargetReview();
          $reviewModel->season_id = $season->id;
          $reviewModel->label = $material;
          $reviewModel->quantity = $postReviewModels[$i]['quantity'];
          $reviewModel->unit_price = $postReviewModels[$i]['unit_price'];
          $reviewModel->save(false);
        }

        $postFoodModels = $postData['TargetFood'];

        foreach($foods as $i => $food)
        {
          $foodModel = new TargetFood();
          $foodModel->season_id = $season->id;
          $foodModel->label = $food;
          $foodModel->quantity = $postFoodModels[$i]['quantity'];
          $foodModel->unit_price = $postFoodModels[$i]['unit_price'];
          $foodModel->save(false);  
        }

        $postTransportationModels = $postData['TargetTransportation'];

        foreach($transportations as $i => $transportation)
        {
          $transportationModel = new TargetTransportation();
          $transportationModel->season_id = $season->id;
          $transportationModel->label = $transportation;
          $transportationModel->quantity = $postTransportationModels[$i]['quantity'];
          $transportationModel->unit_price = $postTransportationModels[$i]['unit_price'];
          $transportationModel->save(false);
        }

        $postStaffSalaryModels = $postData['TargetStaffSalary'];

        foreach($staffSalaries as $i => $salary)
        {
          $staffSalaryModel = new TargetStaffSalary();
          $staffSalaryModel->season_id = $season->id;
          $staffSalaryModel->label = $salary;
          $staffSalaryModel->quantity = $postStaffSalaryModels[$i]['quantity'];
          $staffSalaryModel->unit_price = $postStaffSalaryModels[$i]['unit_price'];
          $staffSalaryModel->save(false);
        }

        $postRebateModels = $postData['TargetRebate'];

        foreach($rebates as $i => $rebate)
        {
          $rebateModel = new TargetRebate();
          $rebateModel->season_id = $season->id;
          $rebateModel->label = $rebate;
          $rebateModel->quantity = $postRebateModels[$i]['quantity'];
          $rebateModel->unit_price = $postRebateModels[$i]['unit_price'];
          $rebateModel->save(false);
        }

        $postUtilityModels = $postData['TargetUtility'];

        foreach($utilities as $i => $utility)
        {
          $utilityModel = new TargetUtility();
          $utilityModel->season_id = $season->id;
          $utilityModel->label = $utility;
          $utilityModel->quantity = $postUtilityModels[$i]['quantity'];
          $utilityModel->unit_price = $postUtilityModels[$i]['unit_price'];
          $utilityModel->save(false);
        }

        $postAcademicModels = $postData['TargetAcademic'];

        foreach($academics as $i => $academic)
        {
          $academicModel = new TargetAcademic();
          $academicModel->season_id = $season->id;
          $academicModel->label = $academic;
          $academicModel->quantity = $postAcademicModels[$i]['quantity'];
          $academicModel->unit_price = $postAcademicModels[$i]['unit_price'];
          $academicModel->save(false);
        }

        $postEmergencyModels = $postData['TargetEmergencyFund'];

        foreach ($emergencies as $i => $emergency)
        {
          $emergencyFundModel = new TargetEmergencyFund();
          $emergencyFundModel->season_id = $season->id;
          $emergencyFundModel->label = $emergency;
          $emergencyFundModel->quantity = $postEmergencyModels[$i]['quantity'];
          $emergencyFundModel->unit_price = $postEmergencyModels[$i]['unit_price'];
          $emergencyFundModel->save(false);
        }

        $postRoyaltyFeeModels = $postData['TargetRoyaltyFee'];

        foreach ($postRoyaltyFeeModels as $i => $royalty)
        {
          $royaltyFeeModel = new TargetRoyaltyFee();
          $royaltyFeeModel->season_id = $season->id;
          $royaltyFeeModel->percentage = $royalty['percentage'];
          $royaltyFeeModel->save(false);
        }

        \Yii::$app->getSession()->setFlash('success', 'Cost Estimation has been saved.');
        return $this->redirect(['view', 'id' => $season->id]);
      }

      return $this->render('create',[
      	'model' => $model,
      	'seasons' => $seasons,
      	'enroleeTypes' => $enroleeTypes,
      	'freebies' => $freebies,
      	'incomeModels' => $incomeModels,
      	'taxModels' => $taxModels,
      	'programModels' => $programModels,
      	'venueRentalModels' => $venueRentalModels,
      	'freebieModels' => $freebieModels,
      	'reviewModels' => $reviewModels,
      	'foodModels' => $foodModels,
      	'transportationModels' => $transportationModels,
      	'staffSalaryModels' => $staffSalaryModels,
      	'rebateModels' => $rebateModels,
      	'utilityModels' => $utilityModels,
      	'academicModels' => $academicModels,
      	'emergencyFundModels' => $emergencyFundModels,
      	'royaltyFeeModels' => $royaltyFeeModels,
      ]);
    }

    public function actionView($id)
    {
      $model = Season::findOne($id);

      return $this->render('view',[
        'model' => $model,
      ]);
    }

    public function actionUpdate($id)
    {
      $season = Season::findOne($id);

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

      $model = TargetIncome::find()->where(['season_id' => $season->id])->orderBy(['id' => SORT_DESC])->one();

      $incomeModels = [];
      $enroleeTypes = EnroleeType::find()->all();

      if($season->targetIncomes)
      {
        foreach($season->targetIncomes as $targetIncome)
        {
          $incomeModels[] = $targetIncome;
        }
      }

      $taxModels = []; 

      $taxModels[] = new TargetTax();

      $programModels = [];
      $programs = [
        '___ Hours of Lecture (Senior Lecturer)',
        '___ Hours of Lecture (Junior Lecturer)',
        '___ Hours of Lecture (Adjunct Lecturer)',
        '___ Hours of Lecture (Guest Lecturer)',
      ];

      if($season->targetPrograms)
      {
        foreach($season->targetPrograms as $targetProgram)
        {
          $programModels[] = $targetProgram;
        }
      }

      $venueRentalModels = [];
      $venueRentals = ['___ Months/Days'];

      if($season->targetVenueRentals)
      {
        foreach($season->targetVenueRentals as $targetVenueRental)
        {
          $venueRentalModels[] = $targetVenueRental;
        }
      }

      $freebieModels = [];
      $freebies = Freebie::find()->all();
      if($season->targetFreebies)
      {
        foreach($season->targetFreebies as $targetFreebie)
        {
          $freebieModels[] = $targetFreebie;
        }
      }

      $reviewModels = [];
      $reviewMaterials = ['Exams'];

      if($season->targetReviews)
      {
        foreach($season->targetReviews as $targetReview)
        {
          $reviewModels[] = $targetReview;
        }
      }

      $foodModels = [];
      $foods = ['___ Days'];

      if($season->targetFoods)
      {
        foreach($season->targetFoods as $targetFood)
        {
          $foodModels[] = $targetFood;
        }
      }
      

      $transportationModels = [];
      $transportations = [
        '___ Ticket Allowance for Lecturers',
        '___ Transportation Allowance for Lecturers'
      ];

      if($season->targetTransportations)
      {
        foreach($season->targetTransportations as $targetTransportation)
        {
          $transportationModels[] = $targetTransportation;
        }
      }

      $staffSalaryModels = [];
      $staffSalaries = ['___ Days/Months'];

      if($season->targetStaffSalaries)
      {
        foreach($season->targetStaffSalaries as $staffSalary)
        {
          $staffSalaryModels[] = $staffSalary;
        }    
      }

      $rebateModels = [];
      $rebates = [
        '___ Incentives',
        '___ Faculty Development Fund'
      ];

      if($season->targetRebates)
      {
        foreach($season->targetRebates as $targetRebate)
        {
          $rebateModels[] = $targetRebate;
        }
      }

      $utilityModels = [];
      $utilities = [
        'Water Bill',
        'Electricity',
        'Staff House',
        'Phone Bill',
        'Wifi',
        'Office Supplies',
        'Transpo for the staff',
        'Marketing facebook ads/flyers/tarp',
      ];

      if($season->targetUtilities)
      {
        foreach($season->targetUtilities as $targetUtility)
        {
          $utilityModels[] = $targetUtility;
        }
      }

      $academicModels = [];
      $academics = [
        'Grand Day',
        'Support Drive',
        'Victory Party',
        'Stress Management',
      ];

      if($season->targetAcademics)
      {
        foreach($season->targetAcademics as $targetAcademic)
        {
          $academicModels[] = $targetAcademic;
        }
      }

      $emergencyFundModels = [];
      $emergencies = ['Emergency Fund'];

      if($season->targetEmergencyFunds)
      {
        foreach($season->targetEmergencyFunds as $targetEmergencyFund)
        {
          $emergencyFundModels[] = $targetEmergencyFund; 
        }
      }

      $royaltyFeeModels = [];

      if($season->targetRoyaltyFees)
      {
        foreach($season->targetRoyaltyFees as $targetRoyaltyFee)
        {
          $royaltyFeeModels[] = $targetRoyaltyFee;
        }
      }

      if(Yii::$app->request->post())
      {
        $postData = Yii::$app->request->post();

        $season = Season::findOne($postData['TargetIncome']['season_id']);     

        $postIncomeModels = $postData['TargetIncome'];

        unset($postIncomeModels['season_id']);

        if($enroleeTypes)
        {
          foreach($enroleeTypes as $i => $enroleeType)
          {
            $incomeModel = new TargetIncome();
            $incomeModel->season_id = $season->id;
            $incomeModel->enrolee_type_id = $enroleeType->id;
            $incomeModel->quantity = $postIncomeModels[$i]['quantity'];
            $incomeModel->unit_price = $postIncomeModels[$i]['unit_price'];
            $incomeModel->save(false);
          }
        }

        $postProgramModels = $postData['TargetProgram'];

        foreach($programs as $i => $program)
        {
          $programModel = new TargetProgram();
          $programModel->season_id = $season->id;
          $programModel->label = $program;
          $programModel->quantity = $postProgramModels[$i]['quantity'];
          $programModel->unit_price = $postProgramModels[$i]['unit_price'];
          $programModel->save(false);
        }

        $postVenueRentalModels = $postData['TargetVenueRental'];

        foreach($venueRentals as $i => $rental)
        {
          $venueRentalModel = new TargetVenueRental();
          $venueRentalModel->season_id = $season->id;
          $venueRentalModel->label = $rental;
          $venueRentalModel->quantity = $postVenueRentalModels[$i]['quantity'];
          $venueRentalModel->unit_price = $postVenueRentalModels[$i]['unit_price'];
          $venueRentalModel->save(false);
        }

        $postFreebieModels = $postData['TargetFreebie'];

        if($freebies)
        {
          foreach($freebies as $i => $freebie)
          {
            $freebieModel = new TargetFreebie();
            $freebieModel->season_id = $season->id;
            $freebieModel->freebie_id = $freebie->id;
            $freebieModel->quantity = $postFreebieModels[$i]['quantity'];
            $freebieModel->unit_price = $postFreebieModels[$i]['unit_price'];
            $freebieModel->save(false);
          }
        }

        $postReviewModels = $postData['TargetReview'];

        foreach($reviewMaterials as $i => $material)
        {
          $reviewModel = new TargetReview();
          $reviewModel->season_id = $season->id;
          $reviewModel->label = $material;
          $reviewModel->quantity = $postReviewModels[$i]['quantity'];
          $reviewModel->unit_price = $postReviewModels[$i]['unit_price'];
          $reviewModel->save(false);
        }

        $postFoodModels = $postData['TargetFood'];

        foreach($foods as $i => $food)
        {
          $foodModel = new TargetFood();
          $foodModel->season_id = $season->id;
          $foodModel->label = $food;
          $foodModel->quantity = $postFoodModels[$i]['quantity'];
          $foodModel->unit_price = $postFoodModels[$i]['unit_price'];
          $foodModel->save(false);  
        }

        $postTransportationModels = $postData['TargetTransportation'];

        foreach($transportations as $i => $transportation)
        {
          $transportationModel = new TargetTransportation();
          $transportationModel->season_id = $season->id;
          $transportationModel->label = $transportation;
          $transportationModel->quantity = $postTransportationModels[$i]['quantity'];
          $transportationModel->unit_price = $postTransportationModels[$i]['unit_price'];
          $transportationModel->save(false);
        }

        $postStaffSalaryModels = $postData['TargetStaffSalary'];

        foreach($staffSalaries as $i => $salary)
        {
          $staffSalaryModel = new TargetStaffSalary();
          $staffSalaryModel->season_id = $season->id;
          $staffSalaryModel->label = $salary;
          $staffSalaryModel->quantity = $postStaffSalaryModels[$i]['quantity'];
          $staffSalaryModel->unit_price = $postStaffSalaryModels[$i]['unit_price'];
          $staffSalaryModel->save(false);
        }

        $postRebateModels = $postData['TargetRebate'];

        foreach($rebates as $i => $rebate)
        {
          $rebateModel = new TargetRebate();
          $rebateModel->season_id = $season->id;
          $rebateModel->label = $rebate;
          $rebateModel->quantity = $postRebateModels[$i]['quantity'];
          $rebateModel->unit_price = $postRebateModels[$i]['unit_price'];
          $rebateModel->save(false);
        }

        $postUtilityModels = $postData['TargetUtility'];

        foreach($utilities as $i => $utility)
        {
          $utilityModel = new TargetUtility();
          $utilityModel->season_id = $season->id;
          $utilityModel->label = $utility;
          $utilityModel->quantity = $postUtilityModels[$i]['quantity'];
          $utilityModel->unit_price = $postUtilityModels[$i]['unit_price'];
          $utilityModel->save(false);
        }

        $postAcademicModels = $postData['TargetAcademic'];

        foreach($academics as $i => $academic)
        {
          $academicModel = new TargetAcademic();
          $academicModel->season_id = $season->id;
          $academicModel->label = $academic;
          $academicModel->quantity = $postAcademicModels[$i]['quantity'];
          $academicModel->unit_price = $postAcademicModels[$i]['unit_price'];
          $academicModel->save(false);
        }

        $postEmergencyModels = $postData['TargetEmergencyFund'];

        foreach ($emergencies as $i => $emergency)
        {
          $emergencyFundModel = new TargetEmergencyFund();
          $emergencyFundModel->season_id = $season->id;
          $emergencyFundModel->label = $emergency;
          $emergencyFundModel->quantity = $postEmergencyModels[$i]['quantity'];
          $emergencyFundModel->unit_price = $postEmergencyModels[$i]['unit_price'];
          $emergencyFundModel->save(false);
        }

        $postRoyaltyFeeModels = $postData['TargetRoyaltyFee'];

        foreach ($postRoyaltyFeeModels as $i => $royalty)
        {
          $royaltyFeeModel = new TargetRoyaltyFee();
          $royaltyFeeModel->season_id = $season->id;
          $royaltyFeeModel->percentage = $royalty['percentage'];
          $royaltyFeeModel->save(false);
        }

        \Yii::$app->getSession()->setFlash('success', 'Cost Estimation has been updated.');
        return $this->redirect(['view', 'id' => $season->id]);
      }

      return $this->render('update',[
        'model' => $model,
        'seasons' => $seasons,
        'enroleeTypes' => $enroleeTypes,
        'freebies' => $freebies,
        'incomeModels' => $incomeModels,
        'taxModels' => $taxModels,
        'programModels' => $programModels,
        'venueRentalModels' => $venueRentalModels,
        'freebieModels' => $freebieModels,
        'reviewModels' => $reviewModels,
        'foodModels' => $foodModels,
        'transportationModels' => $transportationModels,
        'staffSalaryModels' => $staffSalaryModels,
        'rebateModels' => $rebateModels,
        'utilityModels' => $utilityModels,
        'academicModels' => $academicModels,
        'emergencyFundModels' => $emergencyFundModels,
        'royaltyFeeModels' => $royaltyFeeModels,
      ]);
    }
}
