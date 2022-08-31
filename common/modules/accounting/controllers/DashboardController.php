<?php
namespace common\modules\accounting\controllers;

use Yii;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use common\modules\accounting\models\Student;
use common\modules\accounting\models\StudentTuition;
use common\modules\accounting\models\StudentEnroleeType;
use common\modules\accounting\models\EnroleeType;
use common\modules\accounting\models\DiscountType;
use common\modules\accounting\models\Package;
use common\modules\accounting\models\PackageStudent;
use common\modules\accounting\models\Province;
use common\modules\accounting\models\Branch;
use common\modules\accounting\models\Program;
use common\modules\accounting\models\BranchProgram;
use common\modules\accounting\models\AccessProgram;
use common\modules\accounting\models\Transferee;
use common\modules\accounting\models\Income;
use common\modules\accounting\models\Expense;
use common\modules\accounting\models\Coaching;
use common\modules\accounting\models\IncomeEnrolment;
use common\modules\accounting\models\FreebieAndIcon;
use common\modules\accounting\models\PettyExpense;
use common\modules\accounting\models\PhotocopyExpense;
use common\modules\accounting\models\OtherExpense;
use common\modules\accounting\models\BankDeposit;
use common\modules\accounting\models\OperatingExpense;
use common\modules\accounting\models\BudgetProposal;
use common\modules\accounting\models\BudgetProposalType;
use common\modules\accounting\models\Season;

class DashboardController extends \yii\web\Controller
{
    public function actionProvinceList()
    {
        $provinces = Student::find()
                    ->select([
                        'distinct(province_id) as province_c',
                        'tblprovince.province_m'
                    ])
                    ->leftJoin('tblprovince','tblprovince.province_c = accounting_student.province_id')
                    ->asArray()
                    ->orderBy(['province_m' => SORT_ASC])
                    ->all();

        $arr = [];
        $arr[] = ['id'=>'','text'=>''];

        foreach($provinces as $province){
            $arr[] = ['id' => $province['province_c'],'text' => $province['province_m']];
        }
        \Yii::$app->response->format = 'json';
        return $arr;
    }

    public function actionYearList()
    {
        $years = Income::find()
                ->select(['distinct(YEAR(datetime)) as year'])
                ->orderBy(['year' => SORT_DESC])
                ->asArray()
                ->all();

        $arr = [];
        $arr[] = ['id'=>'','text'=>''];

        foreach($years as $year){
            $arr[] = ['id' => $year['year'],'text' => $year['year']];
        }
        \Yii::$app->response->format = 'json';
        return $arr;
    }

    public function actionBranchList()
    {
        $user_info = Yii::$app->user->identity->userinfo;
        $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
        $rolenames =  ArrayHelper::map($roles, 'name','name');

        if(in_array('TopManagement',$rolenames)){

            $branches = Branch::find()
                        ->select(['id','name'])
                        ->orderBy(['name' => SORT_ASC])
                        ->asArray()
                        ->all();
        }else{

            $branches = Branch::find()
                        ->select(['id','name'])
                        ->andWhere(['id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                        ->orderBy(['name' => SORT_ASC])
                        ->asArray()
                        ->all();
        }

        $arr = [];
        $arr[] = ['id'=>'','text'=>''];

        foreach($branches as $branch){
            $arr[] = ['id' => $branch['id'],'text' => $branch['name']];
        }
        \Yii::$app->response->format = 'json';
        return $arr;
    }

    function getBranchList()
    {
        $user_info = Yii::$app->user->identity->userinfo;
        $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
        $rolenames =  ArrayHelper::map($roles, 'name','name');

        if(in_array('TopManagement',$rolenames)){

            $branches = Branch::find()
                        ->select(['id','name'])
                        ->orderBy(['name' => SORT_ASC])
                        ->asArray()
                        ->all();
        }else{

            $branches = Branch::find()
                        ->select(['id','name'])
                        ->andWhere(['id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                        ->orderBy(['name' => SORT_ASC])
                        ->asArray()
                        ->all();
        }

        return $branches;
    }

    public function actionBranchProgramList()
    {
        $user_info = Yii::$app->user->identity->userinfo;
        $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
        $rolenames =  ArrayHelper::map($roles, 'name','name');

        if(in_array('TopManagement',$rolenames)){

            $branchPrograms = BranchProgram::find()
                        ->select(['accounting_branch_program.id as id', 'accounting_branch.id as branchID', 'accounting_branch.name as branchName', 'concat(accounting_branch.name," - ",accounting_program.name) as name'])
                        ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                        ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                        ->orderBy(['accounting_branch.name' => SORT_ASC, 'accounting_program.name' => SORT_ASC])
                        ->asArray()
                        ->all();

        }else{

            $branchPrograms = BranchProgram::find()
                        ->select(['accounting_branch_program.id as id', 'accounting_branch.id as branchID', 'accounting_branch.name as branchName', 'concat(accounting_branch.name," - ",accounting_program.name) as name'])
                        ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                        ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                        ->andWhere(['accounting_branch.id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                        ->orderBy(['accounting_branch.name' => SORT_ASC, 'accounting_program.name' => SORT_ASC])
                        ->asArray()
                        ->all();

        }

        $arr = [];

        foreach($branchPrograms as $branchProgram){
            $arr[$branchProgram['branchID']]['text'] = $branchProgram['branchName'];
            $arr[$branchProgram['branchID']]['children'][] = ['id' => $branchProgram['id'], 'text' => $branchProgram['name']];
        }

        \Yii::$app->response->format = 'json';
        return $arr;

    }

    public function getBranchProgramList()
    {
        $user_info = Yii::$app->user->identity->userinfo;
        $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
        $rolenames =  ArrayHelper::map($roles, 'name','name');

        if(in_array('TopManagement',$rolenames)){

            $branchPrograms = BranchProgram::find()
                        ->select(['accounting_branch_program.id as id', 'accounting_branch.id as branchID', 'accounting_branch.name as branchName', 'concat(accounting_branch.name," - ",accounting_program.name) as name'])
                        ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                        ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                        ->orderBy(['accounting_branch.name' => SORT_ASC, 'accounting_program.name' => SORT_ASC])
                        ->asArray()
                        ->all();

        }else{

            $branchPrograms = BranchProgram::find()
                        ->select(['accounting_branch_program.id as id', 'accounting_branch.id as branchID', 'accounting_branch.name as branchName', 'concat(accounting_branch.name," - ",accounting_program.name) as name'])
                        ->leftJoin('accounting_branch','accounting_branch.id = accounting_branch_program.branch_id')
                        ->leftJoin('accounting_program','accounting_program.id = accounting_branch_program.program_id')
                        ->andWhere(['accounting_branch.id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                        ->orderBy(['accounting_branch.name' => SORT_ASC, 'accounting_program.name' => SORT_ASC])
                        ->asArray()
                        ->all();

        }

        return $branchPrograms;

    }

    public function actionFilter($params)
    {
        $params = json_decode($params, true);

        $date = [];
        $branch_program_id = [];

        $dates = Income::find()->select(['distinct(date(datetime)) as year'])->orderBy(['date(datetime)' => SORT_DESC]);

        if(empty($params))
        {
            $dates = $dates->asArray()->all();

            if(!empty($dates))
            {
                $date[0][0] = 'ALL';
            }
        }

        if(!empty($params))
        {
            foreach($params as $param)
            {
                if($param['name'] == 'date')
                {
                    if(!empty($param['value']))
                    {
                        $date[] = explode(" - ", $param['value']);
                    }else{
                        $dates = $dates->asArray()->all();

                        if(!empty($dates))
                        {
                            $date[0][0] = $dates[count($dates)-1]['year'];
                            $date[0][1] = $dates[0]['year'];
                        }
                    }
                }else{
                    $branch_program_id[] = $param;
                }
            }
        }

        $filters = [];

        $filters['Date'] = empty($params)? $date[0][0] : $date[0][0].' - '.$date[0][1];

        if(!empty($branch_program_id))
        {
            if(count($branch_program_id) > 1){
                $filters['Branch - Program'] = 'MULTIPLE';
            }else{
                $bp = BranchProgram::findOne(['id' => $branch_program_id[0]['value']]);
                if($bp){
                    $filters['Branch - Program'] = $bp->branchProgramName;
                }else{
                    $filters['Branch - Program'] = 'NONE';
                }
            }
        }else{
            $filters['Branch - Program'] = 'ALL';
        }

        return $this->renderAjax('_filter',[
            'filters' => $filters,
        ]);
    }

    public function actionByProvince($params)
    {
        $params = json_decode($params, true);

        $date = [];
        $branch_program_id = [];

        $dates = StudentTuition::find()->select(['distinct(date(datetime)) as year'])->orderBy(['date(datetime)' => SORT_DESC]);

        if(empty($params))
        {
            $dates = $dates->asArray()->all();

            if(!empty($dates))
            {
                $date[0][0] = $dates[count($dates)-1]['year'];
                $date[0][1] = $dates[0]['year'];
            }
        }

        if(!empty($params))
        {
            foreach($params as $param)
            {
                if($param['name'] == 'date')
                {
                    if(!empty($param['value']))
                    {
                        $date[] = explode(" - ", $param['value']);
                    }else{
                        $dates = $dates->asArray()->all();

                        if(!empty($dates))
                        {
                            $date[0][0] = $dates[count($dates)-1]['year'];
                            $date[0][1] = $dates[0]['year'];
                        }
                    }
                }else{
                    $branch_program_id[] = $param['value'];
                }
            }
        }

        $data = StudentTuition::find()
                ->select([
                    'tblprovince.province_c as id',
                    'tblprovince.province_m as name',
                    'count(accounting_student_tuition.id) as total',
                    'transferred.total as transferred',
                    'dropped.total as dropped',
                ]);

        $transferred = Province::find()
                    ->select([
                        'tblprovince.province_c as id',
                        'tblprovince.province_m as name',
                        'count(accounting_transferee.id) as total'
                    ])
                    ->leftJoin('accounting_student','accounting_student.province_id = tblprovince.province_c')
                    ->leftJoin('accounting_transferee','accounting_transferee.student_id = accounting_student.id')
                    ->leftJoin('accounting_season','accounting_season.id = accounting_transferee.from_season_id');

        $dropped = Province::find()
                    ->select([
                        'tblprovince.province_c as id',
                        'tblprovince.province_m as name',
                        'count(accounting_dropout.id) as total'
                    ])
                    ->leftJoin('accounting_student','accounting_student.province_id = tblprovince.province_c')
                    ->leftJoin('accounting_dropout','accounting_dropout.student_id = accounting_student.id')
                    ->leftJoin('accounting_season','accounting_season.id = accounting_dropout.season_id');
                    

        if(!empty($date))
        {
            $data = $data->andWhere(['between', 'accounting_student_tuition.datetime', $date[0][0].' 00:00:00', $date[0][1].' 23:59:59']);
            $transferred = $transferred->andWhere(['between', 'accounting_transferee.datetime', $date[0][0].' 00:00:00', $date[0][1].' 23:59:59']);
            $dropped = $dropped->andWhere(['between', 'accounting_dropout.drop_date', $date[0][0].' 00:00:00', $date[0][1].' 23:59:59']);
        }

        if(!empty($branch_program_id))
        {
            $data = $data->andWhere(['in', 'accounting_season.branch_program_id', $branch_program_id]);
            $transferred = $transferred->andWhere(['in', 'accounting_season.branch_program_id', $branch_program_id]);
            $dropped = $dropped->andWhere(['in', 'accounting_season.branch_program_id', $branch_program_id]);
        }

        $transferred = $transferred->groupBy(['tblprovince.province_c'])
                     ->orderBy(['total' => SORT_DESC])
                     ->createCommand()
                     ->getRawSql();

        $dropped = $dropped->groupBy(['tblprovince.province_c'])
                     ->orderBy(['total' => SORT_DESC])
                     ->createCommand()
                     ->getRawSql();

        $data = $data->leftJoin('accounting_student','accounting_student.id = accounting_student_tuition.student_id')
                     ->leftJoin('accounting_season','accounting_season.id = accounting_student_tuition.season_id')
                     ->leftJoin('tblprovince','tblprovince.province_c = accounting_student.province_id')
                     ->leftJoin(['transferred' => '('.$transferred.')'],'transferred.id = tblprovince.province_c')
                     ->leftJoin(['dropped' => '('.$dropped.')'],'dropped.id = tblprovince.province_c')
                     ->groupBy(['tblprovince.province_c'])
                     ->orderBy(['total' => SORT_DESC])
                     ->asArray()
                     ->all();

        return $this->renderAjax('_by-province',[
            'data' => $data,
        ]);
    }

    public function actionPayment($params)
    {
        $params = json_decode($params, true);

        $date = [];
        $branch_program_id = [];

        $dates = Income::find()->select(['distinct(date(datetime)) as year'])->orderBy(['date(datetime)' => SORT_DESC]);

        if(empty($params))
        {
            $dates = $dates->asArray()->all();

            if(!empty($dates))
            {
                $date[0][0] = $dates[count($dates)-1]['year'];
                $date[0][1] = $dates[0]['year'];
            }
        }

        if(!empty($params))
        {
            foreach($params as $param)
            {
                if($param['name'] == 'date')
                {
                    if(!empty($param['value']))
                    {
                        $date[] = explode(" - ", $param['value']);
                    }else{
                        $dates = $dates->asArray()->all();

                        if(!empty($dates))
                        {
                            $date[0][0] = $dates[count($dates)-1]['year'];
                            $date[0][1] = $dates[0]['year'];
                        }
                    }
                }else{
                    $branch_program_id[] = $param['value'];
                }
            }
        }

        $incomeEnrolments = Income::find()
                           ->select([
                                'YEAR(accounting_income.datetime) as yeartime',
                                'sum(accounting_income_enrolment.amount) as total',
                           ])
                           ->leftJoin('accounting_income_enrolment', 'accounting_income_enrolment.id = accounting_income.income_id and accounting_income.income_type_id = 1')
                           ->leftJoin('accounting_season', 'accounting_season.id = accounting_income_enrolment.season_id');

        $incomeFreebies = Income::find()
                           ->select([
                                'YEAR(accounting_income.datetime) as yeartime',
                                'sum(accounting_income_freebies_and_icons.amount) as total',
                           ])
                           ->leftJoin('accounting_income_freebies_and_icons', 'accounting_income_freebies_and_icons.id = accounting_income.income_id and accounting_income.income_type_id = 2')
                           ->leftJoin('accounting_season', 'accounting_season.id = accounting_income_freebies_and_icons.season_id');

        $expectedIncomes = StudentTuition::find()
                      ->select([
                        'YEAR(accounting_student_tuition.datetime) as yeartime',
                        'sum(accounting_package_student.amount) as packageAmount',
                        'sum(accounting_enhancement.amount) as enhancementAmount',
                        'sum(accounting_coaching.amount) as coachingAmount',
                        'sum(accounting_discount.amount) as discountAmount',
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
                      ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id');

        if(!empty($date))
        {
            $incomeEnrolments = $incomeEnrolments->andWhere(['between', 'accounting_income.datetime', $date[0][0].' 00:00:00', $date[0][1].' 23:59:59']);
            $incomeFreebies = $incomeFreebies->andWhere(['between', 'accounting_income.datetime', $date[0][0].' 00:00:00', $date[0][1].' 23:59:59']);
            $expectedIncomes = $expectedIncomes->andWhere(['between', 'accounting_student_tuition.datetime', $date[0][0].' 00:00:00', $date[0][1].' 23:59:59']);
        }

        if(!empty($branch_program_id))
        {
            $incomeEnrolments = $incomeEnrolments->andWhere(['in', 'accounting_season.branch_program_id', $branch_program_id]);
            $incomeFreebies = $incomeFreebies->andWhere(['in', 'accounting_season.branch_program_id', $branch_program_id]);
            $expectedIncomes = $expectedIncomes->andWhere(['in', 'accounting_branch_program.id', $branch_program_id]);
        }

        $incomeEnrolments = $incomeEnrolments
                            ->groupBy(['YEAR(accounting_income.datetime)'])
                            ->createCommand()
                            ->getRawSql();

        $incomeFreebies = $incomeFreebies
                            ->groupBy(['YEAR(accounting_income.datetime)'])
                            ->createCommand()
                            ->getRawSql();

        $expectedIncomes = $expectedIncomes
                            ->groupBy(['YEAR(accounting_student_tuition.datetime)'])
                            ->createCommand()
                            ->getRawSql();

        $data = Income::find()
                    ->select([
                        'distinct(YEAR(accounting_income.datetime)) as yeartime',
                        '((COALESCE(expectedIncomes.packageAmount,0) + COALESCE(expectedIncomes.enhancementAmount,0) + COALESCE(expectedIncomes.coachingAmount,0)) - COALESCE(expectedIncomes.discountAmount,0)) as expectedAmount',
                        '(COALESCE(incomeEnrolments.total, 0) + COALESCE(incomeFreebies.total, 0)) as paidAmount',
                        '((COALESCE(expectedIncomes.packageAmount,0) + COALESCE(expectedIncomes.enhancementAmount,0) + COALESCE(expectedIncomes.coachingAmount,0)) - COALESCE(expectedIncomes.discountAmount,0)) - (COALESCE(incomeEnrolments.total, 0) + COALESCE(incomeFreebies.total, 0)) as balanceAmount',
                    ])
                    ->leftJoin(['incomeEnrolments' => '('.$incomeEnrolments.')'], 'incomeEnrolments.yeartime = YEAR(accounting_income.datetime)')
                    ->leftJoin(['incomeFreebies' => '('.$incomeFreebies.')'], 'incomeFreebies.yeartime = YEAR(accounting_income.datetime)')
                    ->leftJoin(['expectedIncomes' => '('.$expectedIncomes.')'], 'expectedIncomes.yeartime = YEAR(accounting_income.datetime)')
                    ->orderBy(['YEAR(accounting_income.datetime)' => SORT_DESC])
                    ->groupBy(['YEAR(accounting_income.datetime)'])
                    ->asArray()
                    ->all();

        $seriesData = [];
        $seriesLabels = [];
        $seriesData[0]['name'] = 'Gross Expected Income';
        $seriesData[1]['name'] = 'Total Payments Made';
        $seriesData[2]['name'] = 'Remaining Balance';

        if(!empty($data))
        {
            foreach($data as $key => $datum)
            {
                $seriesLabels[] = $datum['yeartime'];
                $seriesData[0]['data'][] = floatval($datum['expectedAmount']);
                $seriesData[1]['data'][] = floatval($datum['paidAmount']);
                $seriesData[2]['data'][] = floatval($datum['balanceAmount']);
            }
        }

        return $this->renderAjax('_payments',[
            'data' => $data,
            'seriesLabels' => $seriesLabels,
            'seriesData' => $seriesData,
        ]);
    }

    public function actionEnrolmentTypes($params)
    {
        $params = json_decode($params, true);

        $date = [];
        $branch_program_id = [];

        $dates = StudentTuition::find()->select(['distinct(date(datetime)) as year'])->orderBy(['date(datetime)' => SORT_DESC]);

        if(empty($params))
        {
            $dates = $dates->asArray()->all();

            if(!empty($dates))
            {
                $date[0][0] = $dates[count($dates)-1]['year'];
                $date[0][1] = $dates[0]['year'];
            }
        }

        if(!empty($params))
        {
            foreach($params as $param)
            {
                if($param['name'] == 'date')
                {
                    if(!empty($param['value']))
                    {
                        $date[] = explode(" - ", $param['value']);
                    }else{
                        $dates = $dates->asArray()->all();

                        if(!empty($dates))
                        {
                            $date[0][0] = $dates[count($dates)-1]['year'];
                            $date[0][1] = $dates[0]['year'];
                        }
                    }
                }else{
                    $branch_program_id[] = $param['value'];
                }
            }
        }

        $enroleeTypes = StudentTuition::find()
                      ->select([
                        'accounting_student_enrolee_type.enrolee_type_id as id',
                        'count(accounting_student_enrolee_type.id) as total'
                      ])
                      ->leftJoin('accounting_season', 'accounting_season.id = accounting_student_tuition.season_id')
                      ->leftJoin('accounting_student_enrolee_type', 'accounting_student_enrolee_type.season_id = accounting_season.id and accounting_student_enrolee_type.student_id = accounting_student_tuition.student_id')
                      ->leftJoin('accounting_branch_program', 'accounting_branch_program.id = accounting_season.branch_program_id');

        $total = StudentTuition::find()
                      ->select([
                        'COALESCE(count(accounting_student_enrolee_type.id), 0) as total'
                      ])
                      ->leftJoin('accounting_season', 'accounting_season.id = accounting_student_tuition.season_id')
                      ->leftJoin('accounting_student_enrolee_type', 'accounting_student_enrolee_type.season_id = accounting_season.id and accounting_student_enrolee_type.student_id = accounting_student_tuition.student_id')
                      ->leftJoin('accounting_branch_program', 'accounting_branch_program.id = accounting_season.branch_program_id');

        if(!empty($date))
        {
            $enroleeTypes = $enroleeTypes->andWhere(['between', 'accounting_student_tuition.datetime', $date[0][0].' 00:00:00', $date[0][1].' 23:59:59']);
            $total = $total->andWhere(['between', 'accounting_student_tuition.datetime', $date[0][0].' 00:00:00', $date[0][1].' 23:59:59']);
        }

        if(!empty($branch_program_id))
        {
            $enroleeTypes = $enroleeTypes->andWhere(['in', 'accounting_branch_program.id', $branch_program_id]);
            $total = $total->andWhere(['in', 'accounting_branch_program.id', $branch_program_id]);
        }

        $enroleeTypes = $enroleeTypes
                            ->groupBy(['accounting_student_enrolee_type.enrolee_type_id'])
                            ->createCommand()
                            ->getRawSql();

        $total = $total->asArray()
                       ->one();

        $data = EnroleeType::find()
                    ->select([
                        'accounting_enrolee_type.id as id',
                        'accounting_enrolee_type.name as name',
                        'COALESCE(enroleeTypes.total, 0) as total'
                    ])
                    ->leftJoin(['enroleeTypes' => '('.$enroleeTypes.')'], 'enroleeTypes.id = accounting_enrolee_type.id')
                    ->orderBy(['accounting_enrolee_type.id' => SORT_ASC])
                    ->asArray()
                    ->all();

        $seriesData = [];

        if(!empty($data))
        {
            foreach($data as $key => $datum)
            {
                $seriesData[$key]['name'] = $datum['name'];
                $seriesData[$key]['y'] = $total['total'] > 0 ? floatval(number_format(($datum['total']/$total['total'])*100, 2)) : floatval(0);
            }
        }
        
        //echo "<pre>"; print_r($seriesData); exit;

        return $this->renderAjax('_enrolment-types',[
            'seriesData' => $seriesData,
        ]);
    }

    public function actionDiscount($params)
    {
        $params = json_decode($params, true);

        $date = [];
        $branch_program_id = [];

        $dates = StudentTuition::find()->select(['distinct(date(datetime)) as year'])->orderBy(['date(datetime)' => SORT_DESC]);

        if(empty($params))
        {
            $dates = $dates->asArray()->all();

            if(!empty($dates))
            {
                $date[0][0] = $dates[count($dates)-1]['year'];
                $date[0][1] = $dates[0]['year'];
            }
        }

        if(!empty($params))
        {
            foreach($params as $param)
            {
                if($param['name'] == 'date')
                {
                    if(!empty($param['value']))
                    {
                        $date[] = explode(" - ", $param['value']);
                    }else{
                        $dates = $dates->asArray()->all();

                        if(!empty($dates))
                        {
                            $date[0][0] = $dates[count($dates)-1]['year'];
                            $date[0][1] = $dates[0]['year'];
                        }
                    }
                }else{
                    $branch_program_id[] = $param['value'];
                }
            }
        }

        $discount = StudentTuition::find()
                      ->select([
                        'COALESCE(sum(accounting_discount.amount), 0) as total',
                        'count(accounting_discount.id) as no_of_students'
                      ])
                      ->leftJoin('accounting_season', 'accounting_season.id = accounting_student_tuition.season_id')
                      ->leftJoin('accounting_discount', 'accounting_discount.season_id = accounting_season.id and accounting_discount.student_id = accounting_student_tuition.student_id')
                      ;

        $discountTypes = StudentTuition::find()
                      ->select([
                        'accounting_discount_type.id as id',
                        'accounting_discount_type.name as name',
                        'count(accounting_discount.id) as total'
                      ])
                      ->leftJoin('accounting_season', 'accounting_season.id = accounting_student_tuition.season_id')
                      ->leftJoin('accounting_discount', 'accounting_discount.season_id = accounting_season.id and accounting_discount.student_id = accounting_student_tuition.student_id')
                      ->leftJoin('accounting_discount_type', 'accounting_discount_type.id = accounting_discount.discount_type_id')
                      ;

        if(!empty($date))
        {
            $discount = $discount->andWhere(['between', 'accounting_student_tuition.datetime', $date[0][0].' 00:00:00', $date[0][1].' 23:59:59']);
            $discountTypes = $discountTypes->andWhere(['between', 'accounting_student_tuition.datetime', $date[0][0].' 00:00:00', $date[0][1].' 23:59:59']);
        }

        if(!empty($branch_program_id))
        {
            $discount = $discount->andWhere(['in', 'accounting_season.branch_program_id', $branch_program_id]);
            $discountTypes = $discountTypes->andWhere(['in', 'accounting_season.branch_program_id', $branch_program_id]);
        }

        $discount = $discount
                    ->andWhere(['>', 'accounting_discount.amount', 0])
                    ->asArray()
                    ->one();

        $discountTypes = $discountTypes
                    ->andWhere(['>', 'accounting_discount.amount', 0])
                    ->groupBy(['accounting_discount_type.id'])
                    ->asArray()
                    ->all();

        return $this->renderAjax('_discount',[
            'discount' => $discount,
            'discountTypes' => $discountTypes,
        ]);
    }

    public function actionEnhancement($params)
    {
        $params = json_decode($params, true);

        $date = [];
        $branch_program_id = [];

        $dates = StudentTuition::find()->select(['distinct(date(datetime)) as year'])->orderBy(['date(datetime)' => SORT_DESC]);

        if(empty($params))
        {
            $dates = $dates->asArray()->all();

            if(!empty($dates))
            {
                $date[0][0] = $dates[count($dates)-1]['year'];
                $date[0][1] = $dates[0]['year'];
            }
        }

        if(!empty($params))
        {
            foreach($params as $param)
            {
                if($param['name'] == 'date')
                {
                    if(!empty($param['value']))
                    {
                        $date[] = explode(" - ", $param['value']);
                    }else{
                        $dates = $dates->asArray()->all();

                        if(!empty($dates))
                        {
                            $date[0][0] = $dates[count($dates)-1]['year'];
                            $date[0][1] = $dates[0]['year'];
                        }
                    }
                }else{
                    $branch_program_id[] = $param['value'];
                }
            }
        }

        $enhancement = StudentTuition::find()
                      ->select([
                        'COALESCE(sum(accounting_enhancement.amount), 0) as total',
                        'count(accounting_enhancement.id) as no_of_students'
                      ])
                      ->leftJoin('accounting_season', 'accounting_season.id = accounting_student_tuition.season_id')
                      ->leftJoin('accounting_enhancement', 'accounting_enhancement.season_id = accounting_season.id and accounting_enhancement.student_id = accounting_student_tuition.student_id')
                      ;

        if(!empty($date))
        {
            $enhancement = $enhancement->andWhere(['between', 'accounting_student_tuition.datetime', $date[0][0].' 00:00:00', $date[0][1].' 23:59:59']);
        }

        if(!empty($branch_program_id))
        {
            $enhancement = $enhancement->andWhere(['in', 'accounting_season.branch_program_id', $branch_program_id]);
        }

        $enhancement = $enhancement
                    ->andWhere(['>', 'accounting_enhancement.amount', 0])
                    ->asArray()
                    ->one();

        return $this->renderAjax('_enhancement',[
            'enhancement' => $enhancement,
        ]);
    }

    public function actionPackageType($params)
    {
        $params = json_decode($params, true);

        $date = [];
        $branch_program_id = [];

        $dates = StudentTuition::find()->select(['distinct(date(datetime)) as year'])->orderBy(['date(datetime)' => SORT_DESC]);

        if(empty($params))
        {
            $dates = $dates->asArray()->all();

            if(!empty($dates))
            {
                $date[0][0] = $dates[count($dates)-1]['year'];
                $date[0][1] = $dates[0]['year'];
            }
        }

        if(!empty($params))
        {
            foreach($params as $param)
            {
                if($param['name'] == 'date')
                {
                    if(!empty($param['value']))
                    {
                        $date[] = explode(" - ", $param['value']);
                    }else{
                        $dates = $dates->asArray()->all();

                        if(!empty($dates))
                        {
                            $date[0][0] = $dates[count($dates)-1]['year'];
                            $date[0][1] = $dates[0]['year'];
                        }
                    }
                }else{
                    $branch_program_id[] = $param['value'];
                }
            }
        }

        $series = [];

        $packageTypes = PackageStudent::find()
                      ->select([
                        'accounting_package_type.id as id',
                        'accounting_package_type.name as name',
                        'accounting_package_type.name as drilldown',
                        'count(accounting_package_student.id) as total',
                      ])
                      ->leftJoin('accounting_season', 'accounting_season.id = accounting_package_student.season_id')
                      ->leftJoin('accounting_package', 'accounting_package.id = accounting_package_student.package_id')
                      ->leftJoin('accounting_package_type', 'accounting_package_type.id = accounting_package.package_type_id')
                      ->leftJoin('accounting_student_tuition', 'accounting_student_tuition.student_id = accounting_package_student.student_id and accounting_student_tuition.season_id = accounting_package_student.season_id')
                      ;

        if(!empty($date))
        {
            $packageTypes = $packageTypes->andWhere(['between', 'accounting_student_tuition.datetime', $date[0][0].' 00:00:00', $date[0][1].' 23:59:59']);
        }

        if(!empty($branch_program_id))
        {
            $packageTypes = $packageTypes->andWhere(['in', 'accounting_season.branch_program_id', $branch_program_id]);
        }

        $packageTypes = $packageTypes
                    ->groupBy(['accounting_package_type.id'])
                    ->asArray()
                    ->all();

        if(!empty($packageTypes)){
            foreach($packageTypes as $key => $packageType){
                $series[$key]['name'] = $packageType['name'];
                $series[$key]['y'] = intval($packageType['total']);
                $series[$key]['drilldown'] = $packageType['name'];
            }
        }

        return $this->renderAjax('_package-type',[
            'series' => $series
        ]);
    }

    public function actionSeason($params)
    {
        $params = json_decode($params, true);

        $date = [];
        $branch_program_id = [];

        $dates = StudentTuition::find()->select(['distinct(date(datetime)) as year'])->orderBy(['date(datetime)' => SORT_DESC]);

        if(empty($params))
        {
            $dates = $dates->asArray()->all();

            if(!empty($dates))
            {
                $date[0][0] = $dates[count($dates)-1]['year'];
                $date[0][1] = $dates[0]['year'];
            }
        }

        if(!empty($params))
        {
            foreach($params as $param)
            {
                if($param['name'] == 'date')
                {
                    if(!empty($param['value']))
                    {
                        $date[] = explode(" - ", $param['value']);
                    }else{
                        $dates = $dates->asArray()->all();

                        if(!empty($dates))
                        {
                            $date[0][0] = $dates[count($dates)-1]['year'];
                            $date[0][1] = $dates[0]['year'];
                        }
                    }
                }else{
                    $branch_program_id[] = $param['value'];
                }
            }
        }

        $seasons = StudentTuition::find()
                      ->select([
                        'concat(accounting_branch.name," - ",accounting_program.name) as branchProgramName',
                        'concat("SEASON ",accounting_season.name) as seasonName',
                        'count(accounting_student_tuition.id) as total',
                      ])
                      ->leftJoin('accounting_season', 'accounting_season.id = accounting_student_tuition.season_id')
                      ->leftJoin('accounting_branch_program', 'accounting_branch_program.id = accounting_season.branch_program_id')
                      ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                      ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                      ;

        if(!empty($date))
        {
            $seasons = $seasons->andWhere(['between', 'accounting_student_tuition.datetime', $date[0][0].' 00:00:00', $date[0][1].' 23:59:59']);
        }

        if(!empty($branch_program_id))
        {
            $seasons = $seasons->andWhere(['in', 'accounting_season.branch_program_id', $branch_program_id]);
        }

        $seasons = $seasons
                    ->groupBy(['accounting_season.id'])
                    ->orderBy(['total' => SORT_DESC])
                    ->asArray()
                    ->all();

        return $this->renderAjax('_season',[
            'seasons' => $seasons,
        ]);
    }

    public function actionSchool($params)
    {
        $params = json_decode($params, true);

        $date = [];
        $branch_program_id = [];

        $dates = StudentTuition::find()->select(['distinct(date(datetime)) as year'])->orderBy(['date(datetime)' => SORT_DESC]);

        if(empty($params))
        {
            $dates = $dates->asArray()->all();

            if(!empty($dates))
            {
                $date[0][0] = $dates[count($dates)-1]['year'];
                $date[0][1] = $dates[0]['year'];
            }
        }

        if(!empty($params))
        {
            foreach($params as $param)
            {
                if($param['name'] == 'date')
                {
                    if(!empty($param['value']))
                    {
                        $date[] = explode(" - ", $param['value']);
                    }else{
                        $dates = $dates->asArray()->all();

                        if(!empty($dates))
                        {
                            $date[0][0] = $dates[count($dates)-1]['year'];
                            $date[0][1] = $dates[0]['year'];
                        }
                    }
                }else{
                    $branch_program_id[] = $param['value'];
                }
            }
        }

        $schools = StudentTuition::find()
                      ->select([
                        'accounting_school.id as id',
                        'accounting_school.name as name',
                        'accounting_school.location as location',
                        'count(accounting_student_tuition.id) as total',
                      ])
                      ->leftJoin('accounting_season', 'accounting_season.id = accounting_student_tuition.season_id')
                      ->leftJoin('accounting_student', 'accounting_student.id = accounting_student_tuition.student_id')
                      ->leftJoin('accounting_school', 'accounting_school.id = accounting_student.school_id')
                      ;

        if(!empty($date))
        {
            $schools = $schools->andWhere(['between', 'accounting_student_tuition.datetime', $date[0][0].' 00:00:00', $date[0][1].' 23:59:59']);
        }

        if(!empty($branch_program_id))
        {
            $schools = $schools->andWhere(['in', 'accounting_season.branch_program_id', $branch_program_id]);
        }

        $schools = $schools
                    ->groupBy(['accounting_school.id'])
                    ->orderBy(['total' => SORT_DESC])
                    ->asArray()
                    ->all();

        return $this->renderAjax('_school',[
            'schools' => $schools,
        ]);
    }

    public function actionCoaching($params)
    {
        $params = json_decode($params, true);

        $date = [];
        $branch_program_id = [];

        $dates = StudentTuition::find()->select(['distinct(date(datetime)) as year'])->orderBy(['date(datetime)' => SORT_DESC]);

        if(empty($params))
        {
            $dates = $dates->asArray()->all();

            if(!empty($dates))
            {
                $date[0][0] = $dates[count($dates)-1]['year'];
                $date[0][1] = $dates[0]['year'];
            }
        }

        if(!empty($params))
        {
            foreach($params as $param)
            {
                if($param['name'] == 'date')
                {
                    if(!empty($param['value']))
                    {
                        $date[] = explode(" - ", $param['value']);
                    }else{
                        $dates = $dates->asArray()->all();

                        if(!empty($dates))
                        {
                            $date[0][0] = $dates[count($dates)-1]['year'];
                            $date[0][1] = $dates[0]['year'];
                        }
                    }
                }else{
                    $branch_program_id[] = $param['value'];
                }
            }
        }

        $coaching = StudentTuition::find()
                    ->select([
                        'COALESCE(sum(accounting_package.amount), 0) as total',
                        'count(accounting_student_tuition.id) as no_of_students'
                    ])
                    ->leftJoin('accounting_season', 'accounting_season.id = accounting_student_tuition.season_id')
                    ->leftJoin('accounting_package_student', 'accounting_package_student.season_id = accounting_season.id and accounting_package_student.student_id = accounting_student_tuition.student_id')
                    ->leftJoin('accounting_package','accounting_package.id = accounting_package_student.package_id');

        if(!empty($date))
        {
            $coaching = $coaching->andWhere(['between', 'accounting_student_tuition.datetime', $date[0][0].' 00:00:00', $date[0][1].' 23:59:59']);
        }

        if(!empty($branch_program_id))
        {
            $coaching = $coaching->andWhere(['in', 'accounting_season.branch_program_id', $branch_program_id]);
        }

        $coaching = $coaching
                    ->andWhere(['accounting_package.package_type_id' => 3])
                    ->asArray()
                    ->one();

        return $this->renderAjax('_coaching',[
            'coaching' => $coaching,
        ]);
    }

    public function actionPackage($params)
    {
        $params = json_decode($params, true);

        $date = [];
        $branch_program_id = [];

        $dates = StudentTuition::find()->select(['distinct(date(datetime)) as year'])->orderBy(['date(datetime)' => SORT_DESC]);

        if(empty($params))
        {
            $dates = $dates->asArray()->all();

            if(!empty($dates))
            {
                $date[0][0] = $dates[count($dates)-1]['year'];
                $date[0][1] = $dates[0]['year'];
            }
        }

        if(!empty($params))
        {
            foreach($params as $param)
            {
                if($param['name'] == 'date')
                {
                    if(!empty($param['value']))
                    {
                        $date[] = explode(" - ", $param['value']);
                    }else{
                        $dates = $dates->asArray()->all();

                        if(!empty($dates))
                        {
                            $date[0][0] = $dates[count($dates)-1]['year'];
                            $date[0][1] = $dates[0]['year'];
                        }
                    }
                }else{
                    $branch_program_id[] = $param['value'];
                }
            }
        }

        $packages = StudentTuition::find()
                      ->select([
                        'concat(accounting_branch.name," - ",accounting_program.name) as branchProgramName',
                        'concat(accounting_package.code," - ",accounting_package_type.name," - TIER ",accounting_package.tier) as packageName',
                        'accounting_package.amount as packageAmount',
                        'count(accounting_student_tuition.id) as no_of_students',
                        'sum(accounting_package_student.amount) as availedAmount',
                      ])
                      ->leftJoin('accounting_season', 'accounting_season.id = accounting_student_tuition.season_id')
                      ->leftJoin('accounting_branch_program', 'accounting_branch_program.id = accounting_season.branch_program_id')
                      ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                      ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                      ->leftJoin('accounting_package_student', 'accounting_package_student.season_id = accounting_season.id and accounting_package_student.student_id = accounting_student_tuition.student_id')
                      ->leftJoin('accounting_package', 'accounting_package.id = accounting_package_student.package_id')
                      ->leftJoin('accounting_package_type', 'accounting_package_type.id = accounting_package.package_type_id')
                      ;

        if(!empty($date))
        {
            $packages = $packages->andWhere(['between', 'accounting_student_tuition.datetime', $date[0][0].' 00:00:00', $date[0][1].' 23:59:59']);
        }

        if(!empty($branch_program_id))
        {
            $packages = $packages->andWhere(['in', 'accounting_season.branch_program_id', $branch_program_id]);
        }

        $packages = $packages
                    ->groupBy(['accounting_package.id'])
                    ->orderBy(['availedAmount' => SORT_DESC])
                    ->asArray()
                    ->all();

        return $this->renderAjax('_package',[
            'packages' => $packages,
        ]);
    }

    public function actionStudentEnrolment()
    {
        $branchPrograms = $this->getBranchProgramList();

        return $this->render('student-enrolment',[
            'branchPrograms' => $branchPrograms
        ]);
    }

    public function actionFinanceFilter($params)
    {
        $params = json_decode($params, true);

        $dates = Income::find()->select(['distinct(date(datetime)) as year'])->orderBy(['date(datetime)' => SORT_DESC]);

        if(empty($params))
        {
            $dates = $dates->asArray()->all();

            if(!empty($dates))
            {
                $date[0][0] = 'ALL';
            }
        }

        $branch_program_id = [];
        $amount_type_id = [];
        $charge_to_id = [];

        if(!empty($params))
        {
            foreach($params as $param)
            {
                if($param['name'] == 'date')
                {
                    if(!empty($param['value']))
                    {
                        $date[] = explode(" - ", $param['value']);
                    }else{
                        $dates = $dates->asArray()->all();

                        if(!empty($dates))
                        {
                            $date[0][0] = $dates[count($dates)-1]['year'];
                            $date[0][1] = $dates[0]['year'];
                        }
                    }
                }else if($param['name'] == 'branch_program_id[]'){
                    $branch_program_id[] = $param['value'];
                }else if($param['name'] == 'amount_type_id[]'){
                    $amount_type_id[] = $param['value'];
                }else if($param['name'] == 'charge_to_id[]'){
                    $charge_to_id[] = $param['value'];
                }
            }
        }

        $filters = [];

        $filters['Date'] = empty($params)? $date[0][0] : $date[0][0].' - '.$date[0][1];

        if(!empty($branch_program_id))
        {
            if(count($branch_program_id) > 1){
                $filters['Branch - Program'] = 'MULTIPLE';
            }else{
                $bp = BranchProgram::findOne(['id' => $branch_program_id[0]['value']]);
                if($bp){
                    $filters['Branch - Program'] = $bp->branchProgramName;
                }else{
                    $filters['Branch - Program'] = 'NONE';
                }
            }
        }else{
            $filters['Branch - Program'] = 'ALL';
        }

        if(!empty($amount_type_id))
        {
            if(count($amount_type_id) > 1){
                $filters['Amount Type'] = 'MULTIPLE';
            }else{
                $filters['Amount Type'] = $amount_type_id[0];
            }
        }else{
            $filters['Amount Type'] = 'ALL';
        }

        if(!empty($charge_to_id))
        {
            if(count($charge_to_id) > 1){
                $filters['Charge To'] = 'MULTIPLE';
            }else{
                $filters['Charge To'] = $charge_to_id[0];
            }
        }else{
            $filters['Charge To'] = 'ALL';
        }

        return $this->renderAjax('_finance-filter',[
            'filters' => $filters,
        ]);
    }

    public function actionFinance()
    {
        $branchPrograms = $this->getBranchProgramList();
        $amountTypes = ['Bank Deposit' => 'Bank Deposit', 'Cash' => 'Cash', 'Check' => 'Check', 'Credit/Debit Card' => 'Credit/Debit Card'];
        $chargeTos = ['Admin' => 'Admin', 'Area Manager' => 'Area Manager', 'Icon' => 'Icon', 'Program' => 'Program'];

        return $this->render('finance',[
            'branchPrograms' => $branchPrograms,
            'amountTypes' => $amountTypes,
            'chargeTos' => $chargeTos,
        ]);
    }

    public function actionIncome($params)
    {
        $params = json_decode($params, true);
        $date = [];

        $dates = Income::find()->select(['distinct(date(datetime)) as year'])->orderBy(['date(datetime)' => SORT_DESC]);

        if(empty($params))
        {
            $dates = $dates->asArray()->all();

            if(!empty($dates))
            {
                $date[0][0] = $dates[count($dates)-1]['year'];
                $date[0][1] = $dates[0]['year'];
            }
        }

        $branch_program_id = [];
        $amount_type_id = [];
        $charge_to_id = [];

        if(!empty($params))
        {
            foreach($params as $param)
            {
                if($param['name'] == 'date')
                {
                    if(!empty($param['value']))
                    {
                        $date[] = explode(" - ", $param['value']);
                    }else{
                        $dates = $dates->asArray()->all();

                        if(!empty($dates))
                        {
                            $date[0][0] = $dates[count($dates)-1]['year'];
                            $date[0][1] = $dates[0]['year'];
                        }
                    }
                }else if($param['name'] == 'branch_program_id[]'){
                    $branch_program_id[] = $param['value'];
                }else if($param['name'] == 'amount_type_id[]'){
                    $amount_type_id[] = $param['value'];
                }else if($param['name'] == 'charge_to_id[]'){
                    $charge_to_id[] = $param['value'];
                }
            }
        }

        $income = Income::find()
                ->select([
                    'date(accounting_income.datetime) as dt',
                    'incomeOne.total as incomeOneTotal',
                    'incomeTwo.total as incomeTwoTotal',
                ]);

        $incomeOne = Income::find()
                ->select([
                    'date(accounting_income.datetime) as dt',
                    'COALESCE(sum(accounting_income_enrolment.amount), 0) as total',
                ])
                ->leftJoin('accounting_income_enrolment','accounting_income_enrolment.id = accounting_income.income_id and accounting_income.income_type_id = 1')
                ->leftJoin('accounting_season','accounting_season.id = accounting_income_enrolment.season_id');

        $incomeTwo = Income::find()
                ->select([
                    'date(accounting_income.datetime) as dt',
                    'sum(COALESCE(accounting_income_freebies_and_icons.amount, 0)) as total'
                ])
                ->leftJoin('accounting_income_freebies_and_icons','accounting_income_freebies_and_icons.id = accounting_income.income_id and accounting_income.income_type_id = 2')
                ->leftJoin('accounting_season','accounting_season.id = accounting_income_freebies_and_icons.season_id')
                ;

        if(!empty($date))
        {
            $income = $income->andWhere(['between', 'accounting_income.datetime', $date[0][0].' 00:00:00', $date[0][1].' 23:59:59']);
            $incomeOne = $incomeOne->andWhere(['between', 'accounting_income.datetime', $date[0][0].' 00:00:00', $date[0][1].' 23:59:59']);
            $incomeTwo = $incomeTwo->andWhere(['between', 'accounting_income.datetime', $date[0][0].' 00:00:00', $date[0][1].' 23:59:59']);
        }

        if(!empty($branch_program_id))
        {
            $incomeOne = $incomeOne->andWhere(['in', 'accounting_season.branch_program_id', $branch_program_id]);
            $incomeTwo = $incomeTwo->andWhere(['in', 'accounting_season.branch_program_id', $branch_program_id]);
        }

        if(!empty($amount_type_id))
        {
            $incomeOne = $incomeOne->andWhere(['in', 'accounting_income.amount_type', $amount_type_id]);
            $incomeTwo = $incomeTwo->andWhere(['in', 'accounting_income.amount_type', $amount_type_id]);
        }

        $incomeOne = $incomeOne
                    ->groupBy(['date(accounting_income.datetime)'])
                    ->createCommand()
                    ->getRawSql();

        $incomeTwo = $incomeTwo
                    ->groupBy(['date(accounting_income.datetime)'])
                    ->createCommand()
                    ->getRawSql();

        $income = $income
                    ->leftJoin(['incomeOne' => '('.$incomeOne.')'], 'incomeOne.dt = date(accounting_income.datetime)')
                    ->leftJoin(['incomeTwo' => '('.$incomeTwo.')'], 'incomeTwo.dt = date(accounting_income.datetime)')
                    ->groupBy(['date(accounting_income.datetime)'])
                    ->orderBy(['date(accounting_income.datetime)' => SORT_ASC])
                    ->asArray()
                    ->all();


        $data = [];
        $incomeOneTotal = 0;
        $incomeTwoTotal = 0;

        $data[0]['name'] = 'Enrolments';
        $data[1]['name'] = 'Freebies and Icons';
        if(!empty($income)){
            foreach($income as $i)
            {
                $data[0]['data'][] = [strtotime($i['dt'])*1000.05, floatval($i['incomeOneTotal'])];
                $data[1]['data'][] = [strtotime($i['dt'])*1000.05, floatval($i['incomeTwoTotal'])];

                $incomeOneTotal+=$i['incomeOneTotal'];
                $incomeTwoTotal+=$i['incomeTwoTotal'];
            }
        }

        return $this->renderAjax('_income',[
            'data' => $data,
            'date' => $date,
            'income' => $income,
            'incomeOneTotal' => $incomeOneTotal,
            'incomeTwoTotal' => $incomeTwoTotal,
        ]);
    }

    public function actionExpense($params)
    {
        $params = json_decode($params, true);
        $date = [];

        $dates = Expense::find()->select(['distinct(date(datetime)) as year'])->orderBy(['date(datetime)' => SORT_DESC]);

        if(empty($params))
        {
            $dates = $dates->asArray()->all();

            if(!empty($dates))
            {
                $date[0][0] = $dates[count($dates)-1]['year'];
                $date[0][1] = $dates[0]['year'];
            }
        }

        $branch_program_id = [];
        $amount_type_id = [];
        $charge_to_id = [];

        if(!empty($params))
        {
            foreach($params as $param)
            {
                if($param['name'] == 'date')
                {
                    if(!empty($param['value']))
                    {
                        $date[] = explode(" - ", $param['value']);
                    }else{
                        $dates = $dates->asArray()->all();

                        if(!empty($dates))
                        {
                            $date[0][0] = $dates[count($dates)-1]['year'];
                            $date[0][1] = $dates[0]['year'];
                        }
                    }
                }else if($param['name'] == 'branch_program_id[]'){
                    $branch_program_id[] = $param['value'];
                }else if($param['name'] == 'amount_type_id[]'){
                    $amount_type_id[] = $param['value'];
                }else if($param['name'] == 'charge_to_id[]'){
                    $charge_to_id[] = $param['value'];
                }
            }
        }

        $expense = Expense::find()
                ->select([
                    'date(accounting_expense.datetime) as date',
                    'COALESCE(expenseOne.total, 0) as pettyExpenseTotal',
                    'COALESCE(expenseTwo.total, 0) as photocopyExpenseTotal',
                    'COALESCE(expenseThree.total, 0) as otherExpenseTotal',
                    'COALESCE(expenseFour.total, 0) as bankDepositTotal',
                    'COALESCE(expenseFive.total, 0) as operatingExpenseTotal',
                ])
                ->leftJoin('accounting_season','accounting_season.id = accounting_expense.season_id');

        $expenseOne = Expense::find()
                ->select([
                    'date(accounting_expense.datetime) as date',
                    'COALESCE(sum(accounting_expense_petty_expense.food + accounting_expense_petty_expense.supplies + accounting_expense_petty_expense.load + accounting_expense_petty_expense.fare), 0) as total'
                ])
                ->leftJoin('accounting_expense_petty_expense','accounting_expense_petty_expense.id = accounting_expense.expense_id and accounting_expense.expense_type_id = 1')
                ->leftJoin('accounting_season','accounting_season.id = accounting_expense.season_id')
                ;

        $expenseTwo = Expense::find()
                ->select([
                    'date(accounting_expense.datetime) as date',
                    'COALESCE(sum(accounting_expense_photocopy_expense.total_amount), 0) as total'
                ])
                ->leftJoin('accounting_expense_photocopy_expense','accounting_expense_photocopy_expense.id = accounting_expense.expense_id and accounting_expense.expense_type_id = 2')
                ->leftJoin('accounting_season','accounting_season.id = accounting_expense.season_id')
                ;

        $expenseThree = Expense::find()
                ->select([
                    'date(accounting_expense.datetime) as date',
                    'COALESCE(sum(accounting_expense_other_expense.amount), 0) as total'
                ])
                ->leftJoin('accounting_expense_other_expense','accounting_expense_other_expense.id = accounting_expense.expense_id and accounting_expense.expense_type_id = 3')
                ->leftJoin('accounting_season','accounting_season.id = accounting_expense.season_id')
                ;

        $expenseFour = Expense::find()
                ->select([
                    'date(accounting_expense.datetime) as date',
                    'COALESCE(sum(accounting_expense_bank_deposit.amount), 0) as total'
                ])
                ->leftJoin('accounting_expense_bank_deposit','accounting_expense_bank_deposit.id = accounting_expense.expense_id and accounting_expense.expense_type_id = 4')
                ->leftJoin('accounting_season','accounting_season.id = accounting_expense.season_id')
                ;

        $expenseFive = Expense::find()
                ->select([
                    'date(accounting_expense.datetime) as date',
                    'COALESCE(sum(
                        accounting_expense_operating_expense.staff_salary + 
                        accounting_expense_operating_expense.cash_pf + 
                        accounting_expense_operating_expense.rent + 
                        accounting_expense_operating_expense.utilities + 
                        accounting_expense_operating_expense.equipment_and_labor + 
                        accounting_expense_operating_expense.bir_and_docs + 
                        accounting_expense_operating_expense.marketing
                    ), 0) as total'
                ])
                ->leftJoin('accounting_expense_operating_expense','accounting_expense_operating_expense.id = accounting_expense.expense_id and accounting_expense.expense_type_id = 5')
                ->leftJoin('accounting_season','accounting_season.id = accounting_expense.season_id')
                ;

        if(!empty($date))
        {
            $expense = $expense->andWhere(['between', 'accounting_expense.datetime', $date[0][0].' 00:00:00', $date[0][1].' 23:59:59']);
            $expenseOne = $expenseOne->andWhere(['between', 'accounting_expense.datetime', $date[0][0].' 00:00:00', $date[0][1].' 23:59:59']);
            $expenseTwo = $expenseTwo->andWhere(['between', 'accounting_expense.datetime', $date[0][0].' 00:00:00', $date[0][1].' 23:59:59']);
            $expenseThree = $expenseThree->andWhere(['between', 'accounting_expense.datetime', $date[0][0].' 00:00:00', $date[0][1].' 23:59:59']);
            $expenseFour = $expenseFour->andWhere(['between', 'accounting_expense.datetime', $date[0][0].' 00:00:00', $date[0][1].' 23:59:59']);
            $expenseFive = $expenseFive->andWhere(['between', 'accounting_expense.datetime', $date[0][0].' 00:00:00', $date[0][1].' 23:59:59']);
        }

        if(!empty($branch_program_id))
        {
            $expense = $expense->andWhere(['in', 'accounting_season.branch_program_id', $branch_program_id]);
            $expenseOne = $expenseOne->andWhere(['in', 'accounting_season.branch_program_id', $branch_program_id]);
            $expenseTwo = $expenseTwo->andWhere(['in', 'accounting_season.branch_program_id', $branch_program_id]);
            $expenseThree = $expenseThree->andWhere(['in', 'accounting_season.branch_program_id', $branch_program_id]);
            $expenseFour = $expenseFour->andWhere(['in', 'accounting_season.branch_program_id', $branch_program_id]);
            $expenseFive = $expenseFive->andWhere(['in', 'accounting_season.branch_program_id', $branch_program_id]);
        }

        if(!empty($amount_type_id))
        {
            $expense = $expense->andWhere(['in', 'accounting_expense.amount_type', $amount_type_id]);
            $expenseOne = $expenseOne->andWhere(['in', 'accounting_expense.amount_type', $amount_type_id]);
            $expenseTwo = $expenseTwo->andWhere(['in', 'accounting_expense.amount_type', $amount_type_id]);
            $expenseThree = $expenseThree->andWhere(['in', 'accounting_expense.amount_type', $amount_type_id]);
            $expenseFour = $expenseFour->andWhere(['in', 'accounting_expense.amount_type', $amount_type_id]);
            $expenseFive = $expenseFive->andWhere(['in', 'accounting_expense.amount_type', $amount_type_id]);
        }

        if(!empty($charge_to_id))
        {
            $expenseOne = $expenseOne->andWhere(['in', 'accounting_expense_petty_expense.charge_to', $charge_to_id]);
            $expenseTwo = $expenseTwo->andWhere(['in', 'accounting_expense_photocopy_expense.charge_to', $charge_to_id]);
            $expenseThree = $expenseThree->andWhere(['in', 'accounting_expense_other_expense.charge_to', $charge_to_id]);
            //$expenseFour = $expenseFour->andWhere(['in', 'accounting_expense_bank_deposit.charge_to', $amount_type_id]);
            $expenseFive = $expenseFive->andWhere(['in', 'accounting_expense_operating_expense.charge_to', $charge_to_id]);
        }

        $expenseOne = $expenseOne
                    ->groupBy(['date(accounting_expense.datetime)'])
                    ->orderBy(['date(accounting_expense.datetime)' => SORT_ASC])
                    ->createCommand()
                    ->getRawSql();

        $expenseTwo = $expenseTwo
                    ->groupBy(['date(accounting_expense.datetime)'])
                    ->orderBy(['date(accounting_expense.datetime)' => SORT_ASC])
                    ->createCommand()
                    ->getRawSql();

        $expenseThree = $expenseThree
                    ->groupBy(['date(accounting_expense.datetime)'])
                    ->orderBy(['date(accounting_expense.datetime)' => SORT_ASC])
                    ->createCommand()
                    ->getRawSql();

        $expenseFour = $expenseFour
                    ->groupBy(['date(accounting_expense.datetime)'])
                    ->orderBy(['date(accounting_expense.datetime)' => SORT_ASC])
                    ->createCommand()
                    ->getRawSql();

        $expenseFive = $expenseFive
                    ->groupBy(['date(accounting_expense.datetime)'])
                    ->orderBy(['date(accounting_expense.datetime)' => SORT_ASC])
                    ->createCommand()
                    ->getRawSql();

        $expense = $expense
                    ->leftJoin(['expenseOne' => '('.$expenseOne.')'], 'expenseOne.date = date(accounting_expense.datetime)')
                    ->leftJoin(['expenseTwo' => '('.$expenseTwo.')'], 'expenseTwo.date = date(accounting_expense.datetime)')
                    ->leftJoin(['expenseThree' => '('.$expenseThree.')'], 'expenseThree.date = date(accounting_expense.datetime)')
                    ->leftJoin(['expenseFour' => '('.$expenseFour.')'], 'expenseFour.date = date(accounting_expense.datetime)')
                    ->leftJoin(['expenseFive' => '('.$expenseFive.')'], 'expenseFive.date = date(accounting_expense.datetime)')
                    ->groupBy(['date(accounting_expense.datetime)'])
                    ->orderBy(['date(accounting_expense.datetime)' => SORT_ASC])
                    ->asArray()
                    ->all();

        $data = [];
        $expenseOneTotal = 0;
        $expenseTwoTotal = 0;
        $expenseThreeTotal = 0;
        $expenseFourTotal = 0;
        $expenseFiveTotal = 0;

        $data[0]['name'] = 'Petty Expenses';
        $data[1]['name'] = 'Photocopy Expenses';
        $data[2]['name'] = 'Other Expenses';
        $data[3]['name'] = 'Bank Deposits';
        $data[4]['name'] = 'Operating Expenses';
        if(!empty($expense)){
            foreach($expense as $i)
            {
                $data[0]['data'][] = [strtotime($i['date'])*1000.05, floatval($i['pettyExpenseTotal'])];
                $data[1]['data'][] = [strtotime($i['date'])*1000.05, floatval($i['photocopyExpenseTotal'])];
                $data[2]['data'][] = [strtotime($i['date'])*1000.05, floatval($i['otherExpenseTotal'])];
                $data[3]['data'][] = [strtotime($i['date'])*1000.05, floatval($i['bankDepositTotal'])];
                $data[4]['data'][] = [strtotime($i['date'])*1000.05, floatval($i['operatingExpenseTotal'])];

                $expenseOneTotal+=$i['pettyExpenseTotal'];
                $expenseTwoTotal+=$i['photocopyExpenseTotal'];
                $expenseThreeTotal+=$i['otherExpenseTotal'];
                $expenseFourTotal+=$i['bankDepositTotal'];
                $expenseFiveTotal+=$i['operatingExpenseTotal'];
            }
        }
        
        return $this->renderAjax('_expense',[
            'data' => $data,
            'date' => $date,
            'expense' => $expense,
            'expenseOneTotal' => $expenseOneTotal,
            'expenseTwoTotal' => $expenseTwoTotal,
            'expenseThreeTotal' => $expenseThreeTotal,
            'expenseFourTotal' => $expenseFourTotal,
            'expenseFiveTotal' => $expenseFiveTotal,
        ]);
    }

    public function actionBranchProgramSummary($params)
    {
        $params = json_decode($params, true);
        $incomeDate = [];
        $expenseDate = [];
        $studentDate = [];

        $incomeDates = Income::find()->select(['distinct(date(datetime)) as year'])->orderBy(['date(datetime)' => SORT_DESC]);
        $expenseDates = Expense::find()->select(['distinct(date(datetime)) as year'])->orderBy(['date(datetime)' => SORT_DESC]);
        $studentDates = StudentTuition::find()->select(['distinct(date(datetime)) as year'])->orderBy(['date(datetime)' => SORT_DESC]);

        if(empty($params))
        {
            $incomeDates = $incomeDates->asArray()->all();
            $expenseDates = $expenseDates->asArray()->all();
            $studentDates = $studentDates->asArray()->all();

            if(!empty($incomeDates))
            {
                $incomeDate[0][0] = $incomeDates[count($incomeDates)-1]['year'];
                $incomeDate[0][1] = $incomeDates[0]['year'];

                $incomeDate[0][0] = $incomeDates[count($incomeDates)-1]['year'];
                $incomeDate[0][1] = $incomeDates[0]['year'];
            }

            if(!empty($expenseDates))
            {
                $expenseDate[0][0] = $expenseDates[count($expenseDates)-1]['year'];
                $expenseDate[0][1] = $expenseDates[0]['year'];

                $expenseDate[0][0] = $expenseDates[count($expenseDates)-1]['year'];
                $expenseDate[0][1] = $expenseDates[0]['year'];
            }

            if(!empty($studentDates))
            {
                $studentDate[0][0] = $studentDates[count($studentDates)-1]['year'];
                $studentDate[0][1] = $studentDates[0]['year'];

                $studentDate[0][0] = $studentDates[count($studentDates)-1]['year'];
                $studentDate[0][1] = $studentDates[0]['year'];
            }
        }

        $branch_program_id = [];
        $amount_type_id = [];
        $charge_to_id = [];

        if(!empty($params))
        {
            foreach($params as $param)
            {
                if($param['name'] == 'date')
                {
                    if(!empty($param['value']))
                    {
                        $incomeDate[] = explode(" - ", $param['value']);
                        $expenseDate[] = explode(" - ", $param['value']);
                        $studentDate[] = explode(" - ", $param['value']);
                    }else{
                        $incomeDates = $incomeDates->asArray()->all();
                        $expenseDates = $expenseDates->asArray()->all();
                        $studentDates = $studentDates->asArray()->all();

                        if(!empty($incomeDates))
                        {
                            $incomeDate[0][0] = $incomeDates[count($incomeDates)-1]['year'];
                            $incomeDate[0][1] = $incomeDates[0]['year'];

                            $incomeDate[0][0] = $incomeDates[count($incomeDates)-1]['year'];
                            $incomeDate[0][1] = $incomeDates[0]['year'];
                        }

                        if(!empty($expenseDates))
                        {
                            $expenseDate[0][0] = $expenseDates[count($expenseDates)-1]['year'];
                            $expenseDate[0][1] = $expenseDates[0]['year'];

                            $expenseDate[0][0] = $expenseDates[count($expenseDates)-1]['year'];
                            $expenseDate[0][1] = $expenseDates[0]['year'];
                        }

                        if(!empty($studentDates))
                        {
                            $studentDate[0][0] = $studentDates[count($studentDates)-1]['year'];
                            $studentDate[0][1] = $studentDates[0]['year'];

                            $studentDate[0][0] = $studentDates[count($studentDates)-1]['year'];
                            $studentDate[0][1] = $studentDates[0]['year'];
                        }
                    }
                }else if($param['name'] == 'branch_program_id[]'){
                    $branch_program_id[] = $param['value'];
                }else if($param['name'] == 'amount_type_id[]'){
                    $amount_type_id[] = $param['value'];
                }else if($param['name'] == 'charge_to_id[]'){
                    $charge_to_id[] = $param['value'];
                }
            }
        }

        $branchPrograms = BranchProgram::find()
                      ->select([
                        'accounting_branch_program.id as id',
                        'concat(accounting_branch.name," - ",accounting_program.name) as branchProgramName',
                        'no_of_seasons.total as no_of_seasons',
                        'no_of_students.total as no_of_students',
                        'COALESCE(incomeOne.total, 0) as incomeOneTotal',
                        'COALESCE(incomeTwo.total, 0) as incomeTwoTotal',
                        'COALESCE(expenseOne.total, 0) as expenseOneTotal',
                        'COALESCE(expenseTwo.total, 0) as expenseTwoTotal',
                        'COALESCE(expenseThree.total, 0) as expenseThreeTotal',
                        'COALESCE(expenseFour.total, 0) as expenseFourTotal',
                        'COALESCE(expenseFive.total, 0) as expenseFiveTotal',
                      ])
                      ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                      ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                      ;

        $no_of_seasons = Season::find()
                        ->select([
                            'accounting_season.branch_program_id as id',
                            'count(accounting_season.id) as total',
                        ]);

        $no_of_students = StudentTuition::find()
                        ->select([
                            'accounting_season.branch_program_id as id',
                            'count(accounting_season.id) as no_of_season',
                            'count(accounting_student_tuition.id) as total'
                        ])
                        ->leftJoin('accounting_season','accounting_season.id = accounting_student_tuition.season_id');

        $incomeOne = Income::find()
                ->select([
                    'accounting_season.branch_program_id as id',
                    'sum(COALESCE(accounting_income_enrolment.amount, 0)) as total'
                ])
                ->leftJoin('accounting_income_enrolment','accounting_income_enrolment.id = accounting_income.income_id and accounting_income.income_type_id = 1')
                ->leftJoin('accounting_season','accounting_season.id = accounting_income_enrolment.season_id')
                ;

        $incomeTwo = Income::find()
                ->select([
                    'accounting_season.branch_program_id as id',
                    'sum(COALESCE(accounting_income_freebies_and_icons.amount, 0)) as total'
                ])
                ->leftJoin('accounting_income_freebies_and_icons','accounting_income_freebies_and_icons.id = accounting_income.income_id and accounting_income.income_type_id = 2')
                ->leftJoin('accounting_season','accounting_season.id = accounting_income_freebies_and_icons.season_id')
                ;

        $expenseOne = Expense::find()
                ->select([
                    'accounting_season.branch_program_id as id',
                    'COALESCE(sum(accounting_expense_petty_expense.food + accounting_expense_petty_expense.supplies + accounting_expense_petty_expense.load + accounting_expense_petty_expense.fare), 0) as total'
                ])
                ->leftJoin('accounting_expense_petty_expense','accounting_expense_petty_expense.id = accounting_expense.expense_id and accounting_expense.expense_type_id = 1')
                ->leftJoin('accounting_season','accounting_season.id = accounting_expense.season_id')
                ;

        $expenseTwo = Expense::find()
                ->select([
                    'accounting_season.branch_program_id as id',
                    'COALESCE(sum(accounting_expense_photocopy_expense.total_amount), 0) as total'
                ])
                ->leftJoin('accounting_expense_photocopy_expense','accounting_expense_photocopy_expense.id = accounting_expense.expense_id and accounting_expense.expense_type_id = 2')
                ->leftJoin('accounting_season','accounting_season.id = accounting_expense.season_id')
                ;

        $expenseThree = Expense::find()
                ->select([
                    'accounting_season.branch_program_id as id',
                    'COALESCE(sum(accounting_expense_other_expense.amount), 0) as total'
                ])
                ->leftJoin('accounting_expense_other_expense','accounting_expense_other_expense.id = accounting_expense.expense_id and accounting_expense.expense_type_id = 3')
                ->leftJoin('accounting_season','accounting_season.id = accounting_expense.season_id')
                ;

        $expenseFour = Expense::find()
                ->select([
                    'accounting_season.branch_program_id as id',
                    'COALESCE(sum(accounting_expense_bank_deposit.amount), 0) as total'
                ])
                ->leftJoin('accounting_expense_bank_deposit','accounting_expense_bank_deposit.id = accounting_expense.expense_id and accounting_expense.expense_type_id = 4')
                ->leftJoin('accounting_season','accounting_season.id = accounting_expense.season_id')
                ;

        $expenseFive = Expense::find()
                ->select([
                    'accounting_season.branch_program_id as id',
                    'COALESCE(sum(
                        accounting_expense_operating_expense.staff_salary + 
                        accounting_expense_operating_expense.cash_pf + 
                        accounting_expense_operating_expense.rent + 
                        accounting_expense_operating_expense.utilities + 
                        accounting_expense_operating_expense.equipment_and_labor + 
                        accounting_expense_operating_expense.bir_and_docs + 
                        accounting_expense_operating_expense.marketing
                    ), 0) as total'
                ])
                ->leftJoin('accounting_expense_operating_expense','accounting_expense_operating_expense.id = accounting_expense.id and accounting_expense.expense_type_id = 5')
                ->leftJoin('accounting_season','accounting_season.id = accounting_expense.season_id')
                ;

        if(!empty($incomeDate))
        {
            
            $incomeOne = $incomeOne->andWhere(['between', 'accounting_income.datetime', $incomeDate[0][0].' 00:00:00', $incomeDate[0][1].' 23:59:59']);
            $incomeTwo = $incomeTwo->andWhere(['between', 'accounting_income.datetime', $incomeDate[0][0].' 00:00:00', $incomeDate[0][1].' 23:59:59']);
        }

        if(!empty($expenseDate))
        {
            $expenseOne = $expenseOne->andWhere(['between', 'accounting_expense.datetime', $expenseDate[0][0].' 00:00:00', $expenseDate[0][1].' 23:59:59']);
            $expenseTwo = $expenseTwo->andWhere(['between', 'accounting_expense.datetime', $expenseDate[0][0].' 00:00:00', $expenseDate[0][1].' 23:59:59']);
            $expenseThree = $expenseThree->andWhere(['between', 'accounting_expense.datetime', $expenseDate[0][0].' 00:00:00', $expenseDate[0][1].' 23:59:59']);
            $expenseFour = $expenseFour->andWhere(['between', 'accounting_expense.datetime', $expenseDate[0][0].' 00:00:00', $expenseDate[0][1].' 23:59:59']);
            $expenseFive = $expenseFive->andWhere(['between', 'accounting_expense.datetime', $expenseDate[0][0].' 00:00:00', $expenseDate[0][1].' 23:59:59']);
        }

        if(!empty($studentDate))
        {
            $no_of_students = $no_of_students->andWhere(['between', 'accounting_student_tuition.datetime', $studentDate[0][0].' 00:00:00', $studentDate[0][1].' 23:59:59']);
        }

        if(!empty($branch_program_id))
        {
            $branchPrograms = $branchPrograms->andWhere(['in', 'accounting_branch_program.id', $branch_program_id]);
            $no_of_seasons = $no_of_seasons->andWhere(['in', 'accounting_season.branch_program_id', $branch_program_id]);
            $no_of_students = $no_of_students->andWhere(['in', 'accounting_season.branch_program_id', $branch_program_id]);
            $incomeOne = $incomeOne->andWhere(['in', 'accounting_season.branch_program_id', $branch_program_id]);
            $incomeTwo = $incomeTwo->andWhere(['in', 'accounting_season.branch_program_id', $branch_program_id]);
            $expenseOne = $expenseOne->andWhere(['in', 'accounting_season.branch_program_id', $branch_program_id]);
            $expenseTwo = $expenseTwo->andWhere(['in', 'accounting_season.branch_program_id', $branch_program_id]);
            $expenseThree = $expenseThree->andWhere(['in', 'accounting_season.branch_program_id', $branch_program_id]);
            $expenseFour = $expenseFour->andWhere(['in', 'accounting_season.branch_program_id', $branch_program_id]);
            $expenseFive = $expenseFive->andWhere(['in', 'accounting_season.branch_program_id', $branch_program_id]);
        }

        if(!empty($amount_type_id))
        {
            $incomeOne = $incomeOne->andWhere(['in', 'accounting_income.amount_type', $amount_type_id]);
            $incomeTwo = $incomeTwo->andWhere(['in', 'accounting_income.amount_type', $amount_type_id]);
            $expenseOne = $expenseOne->andWhere(['in', 'accounting_expense.amount_type', $amount_type_id]);
            $expenseTwo = $expenseTwo->andWhere(['in', 'accounting_expense.amount_type', $amount_type_id]);
            $expenseThree = $expenseThree->andWhere(['in', 'accounting_expense.amount_type', $amount_type_id]);
            $expenseFour = $expenseFour->andWhere(['in', 'accounting_expense.amount_type', $amount_type_id]);
            $expenseFive = $expenseFive->andWhere(['in', 'accounting_expense.amount_type', $amount_type_id]);
        }

        if(!empty($charge_to_id))
        {
            $expenseOne = $expenseOne->andWhere(['in', 'accounting_expense_petty_expense.charge_to', $charge_to_id]);
            $expenseTwo = $expenseTwo->andWhere(['in', 'accounting_expense_photocopy_expense.charge_to', $charge_to_id]);
            $expenseThree = $expenseThree->andWhere(['in', 'accounting_expense_other_expense.charge_to', $charge_to_id]);
            //$expenseFour = $expenseFour->andWhere(['in', 'accounting_expense_bank_deposit.charge_to', $amount_type_id]);
            $expenseFive = $expenseFive->andWhere(['in', 'accounting_expense_operating_expense.charge_to', $charge_to_id]);
        }

        $no_of_seasons = $no_of_seasons
                    ->groupBy(['accounting_season.branch_program_id'])
                    ->createCommand()
                    ->getRawSql();

        $no_of_students = $no_of_students
                    ->groupBy(['accounting_season.branch_program_id'])
                    ->createCommand()
                    ->getRawSql();

        $incomeOne = $incomeOne
                    ->groupBy(['accounting_season.branch_program_id'])
                    ->createCommand()
                    ->getRawSql();

        $incomeTwo = $incomeTwo
                    ->groupBy(['accounting_season.branch_program_id'])
                    ->createCommand()
                    ->getRawSql();

        $expenseOne = $expenseOne
                    ->groupBy(['accounting_season.branch_program_id'])
                    ->createCommand()
                    ->getRawSql();

        $expenseTwo = $expenseTwo
                    ->groupBy(['accounting_season.branch_program_id'])
                    ->createCommand()
                    ->getRawSql();

        $expenseThree = $expenseThree
                    ->groupBy(['accounting_season.branch_program_id'])
                    ->createCommand()
                    ->getRawSql();

        $expenseFour = $expenseFour
                    ->groupBy(['accounting_season.branch_program_id'])
                    ->createCommand()
                    ->getRawSql();

        $expenseFive = $expenseFive
                    ->groupBy(['accounting_season.id'])
                    ->createCommand()
                    ->getRawSql();

        $branchPrograms = $branchPrograms
                    ->leftJoin(['no_of_seasons' => '('.$no_of_seasons.')'], 'no_of_seasons.id = accounting_branch_program.id')
                    ->leftJoin(['no_of_students' => '('.$no_of_students.')'], 'no_of_students.id = accounting_branch_program.id')
                    ->leftJoin(['incomeOne' => '('.$incomeOne.')'], 'incomeOne.id = accounting_branch_program.id')
                    ->leftJoin(['incomeTwo' => '('.$incomeTwo.')'], 'incomeTwo.id = accounting_branch_program.id')
                    ->leftJoin(['expenseOne' => '('.$expenseOne.')'], 'expenseOne.id = accounting_branch_program.id')
                    ->leftJoin(['expenseTwo' => '('.$expenseTwo.')'], 'expenseTwo.id = accounting_branch_program.id')
                    ->leftJoin(['expenseThree' => '('.$expenseThree.')'], 'expenseThree.id = accounting_branch_program.id')
                    ->leftJoin(['expenseFour' => '('.$expenseFour.')'], 'expenseFour.id = accounting_branch_program.id')
                    ->leftJoin(['expenseFive' => '('.$expenseFive.')'], 'expenseFive.id = accounting_branch_program.id')
                    ->orderBy(['branchProgramName' => SORT_ASC])
                    ->asArray()
                    ->all();

        $branchProgramNames = [];
        $enrolmentData = [];
        $freebieData = [];
        $pettyExpenseData = [];
        $photocopyExpenseData = [];
        $otherExpenseData = [];
        $bankDepositData = [];
        $operatingExpenseData = [];
        $netIncomeData = [];
        
        if(!empty($branchPrograms))
        {
            foreach($branchPrograms as $branchProgram)
            {
                $branchProgramNames[] = $branchProgram['branchProgramName'];
                $enrolmentData[] = floatval($branchProgram['incomeOneTotal']);
                $freebieData[] = floatval($branchProgram['incomeTwoTotal']);
                $pettyExpenseData[] = floatval($branchProgram['expenseOneTotal']);
                $photocopyExpenseData[] = floatval($branchProgram['expenseTwoTotal']);
                $otherExpenseData[] = floatval($branchProgram['expenseThreeTotal']);
                $bankDepositData[] = floatval($branchProgram['expenseFourTotal']);
                $operatingExpenseData[] = floatval($branchProgram['expenseFiveTotal']);
                $netIncomeData[] = floatval(($branchProgram['incomeOneTotal'] + $branchProgram['incomeTwoTotal']) - ($branchProgram['expenseOneTotal'] + $branchProgram['expenseTwoTotal'] + $branchProgram['expenseThreeTotal'] + $branchProgram['expenseFourTotal'] + $branchProgram['expenseFiveTotal']));
            }
        }
        
        return $this->renderAjax('_branch-program-summary',[
            'incomeDate' => $incomeDate,
            'expenseDate' => $expenseDate,
            'studentDate' => $studentDate,
            'branchPrograms' => $branchPrograms,
            'branchProgramNames' => $branchProgramNames,
            'enrolmentData' => $enrolmentData,
            'freebieData' => $freebieData,
            'pettyExpenseData' => $pettyExpenseData,
            'photocopyExpenseData' => $photocopyExpenseData,
            'otherExpenseData' => $otherExpenseData,
            'bankDepositData' => $bankDepositData,
            'operatingExpenseData' => $operatingExpenseData,
            'netIncomeData' => $netIncomeData,
        ]);
    }

    public function actionSeasonSummary($params)
    {
        $params = json_decode($params, true);
        $incomeDate = [];
        $expenseDate = [];
        $studentDate = [];

        $incomeDates = Income::find()->select(['distinct(date(datetime)) as year'])->orderBy(['date(datetime)' => SORT_DESC]);
        $expenseDates = Expense::find()->select(['distinct(date(datetime)) as year'])->orderBy(['date(datetime)' => SORT_DESC]);
        $studentDates = StudentTuition::find()->select(['distinct(date(datetime)) as year'])->orderBy(['date(datetime)' => SORT_DESC]);

        if(empty($params))
        {
            $incomeDates = $incomeDates->asArray()->all();
            $expenseDates = $expenseDates->asArray()->all();
            $studentDates = $studentDates->asArray()->all();

            if(!empty($incomeDates))
            {
                $incomeDate[0][0] = $incomeDates[count($incomeDates)-1]['year'];
                $incomeDate[0][1] = $incomeDates[0]['year'];

                $incomeDate[0][0] = $incomeDates[count($incomeDates)-1]['year'];
                $incomeDate[0][1] = $incomeDates[0]['year'];
            }

            if(!empty($expenseDates))
            {
                $expenseDate[0][0] = $expenseDates[count($expenseDates)-1]['year'];
                $expenseDate[0][1] = $expenseDates[0]['year'];

                $expenseDate[0][0] = $expenseDates[count($expenseDates)-1]['year'];
                $expenseDate[0][1] = $expenseDates[0]['year'];
            }

            if(!empty($studentDates))
            {
                $studentDate[0][0] = $studentDates[count($studentDates)-1]['year'];
                $studentDate[0][1] = $studentDates[0]['year'];

                $studentDate[0][0] = $studentDates[count($studentDates)-1]['year'];
                $studentDate[0][1] = $studentDates[0]['year'];
            }
        }

        $branch_program_id = [];
        $amount_type_id = [];
        $charge_to_id = [];

        if(!empty($params))
        {
            foreach($params as $param)
            {
                if($param['name'] == 'date')
                {
                    if(!empty($param['value']))
                    {
                        $incomeDate[] = explode(" - ", $param['value']);
                        $expenseDate[] = explode(" - ", $param['value']);
                        $studentDate[] = explode(" - ", $param['value']);
                    }else{
                        $incomeDates = $incomeDates->asArray()->all();
                        $expenseDates = $expenseDates->asArray()->all();
                        $studentDates = $studentDates->asArray()->all();

                        if(!empty($incomeDates))
                        {
                            $incomeDate[0][0] = $incomeDates[count($incomeDates)-1]['year'];
                            $incomeDate[0][1] = $incomeDates[0]['year'];

                            $incomeDate[0][0] = $incomeDates[count($incomeDates)-1]['year'];
                            $incomeDate[0][1] = $incomeDates[0]['year'];
                        }

                        if(!empty($expenseDates))
                        {
                            $expenseDate[0][0] = $expenseDates[count($expenseDates)-1]['year'];
                            $expenseDate[0][1] = $expenseDates[0]['year'];

                            $expenseDate[0][0] = $expenseDates[count($expenseDates)-1]['year'];
                            $expenseDate[0][1] = $expenseDates[0]['year'];
                        }

                        if(!empty($studentDates))
                        {
                            $studentDate[0][0] = $studentDates[count($studentDates)-1]['year'];
                            $studentDate[0][1] = $studentDates[0]['year'];

                            $studentDate[0][0] = $studentDates[count($studentDates)-1]['year'];
                            $studentDate[0][1] = $studentDates[0]['year'];
                        }
                    }
                }else if($param['name'] == 'branch_program_id[]'){
                    $branch_program_id[] = $param['value'];
                }else if($param['name'] == 'amount_type_id[]'){
                    $amount_type_id[] = $param['value'];
                }else if($param['name'] == 'charge_to_id[]'){
                    $charge_to_id[] = $param['value'];
                }
            }
        }

        $seasons = Season::find()
                      ->select([
                        'concat(accounting_branch.name," - ",accounting_program.name) as branchProgramName',
                        'accounting_season.id as id',
                        'concat("SEASON ",accounting_season.name) as seasonName',
                        'no_of_students.total as no_of_students',
                        'COALESCE(incomeOne.total, 0) as incomeOneTotal',
                        'COALESCE(incomeTwo.total, 0) as incomeTwoTotal',
                        'COALESCE(expenseOne.total, 0) as expenseOneTotal',
                        'COALESCE(expenseTwo.total, 0) as expenseTwoTotal',
                        'COALESCE(expenseThree.total, 0) as expenseThreeTotal',
                        'COALESCE(expenseFour.total, 0) as expenseFourTotal',
                        'COALESCE(expenseFive.total, 0) as expenseFiveTotal',
                      ])
                      ->leftJoin('accounting_branch_program', 'accounting_branch_program.id = accounting_season.branch_program_id')
                      ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                      ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                      ;

        $no_of_students = StudentTuition::find()
                        ->select([
                            'accounting_season.id as id',
                            'count(accounting_student_tuition.id) as total'
                        ])
                        ->leftJoin('accounting_season','accounting_season.id = accounting_student_tuition.season_id');

        $incomeOne = Income::find()
                ->select([
                    'accounting_season.id as id',
                    'sum(COALESCE(accounting_income_enrolment.amount, 0)) as total'
                ])
                ->leftJoin('accounting_income_enrolment','accounting_income_enrolment.id = accounting_income.income_id and accounting_income.income_type_id = 1')
                ->leftJoin('accounting_season','accounting_season.id = accounting_income_enrolment.season_id')
                ;

        $incomeTwo = Income::find()
                ->select([
                    'accounting_season.id as id',
                    'sum(COALESCE(accounting_income_freebies_and_icons.amount, 0)) as total'
                ])
                ->leftJoin('accounting_income_freebies_and_icons','accounting_income_freebies_and_icons.id = accounting_income.income_id and accounting_income.income_type_id = 2')
                ->leftJoin('accounting_season','accounting_season.id = accounting_income_freebies_and_icons.season_id')
                ;

        $expenseOne = Expense::find()
                ->select([
                    'accounting_season.id as id',
                    'COALESCE(sum(accounting_expense_petty_expense.food + accounting_expense_petty_expense.supplies + accounting_expense_petty_expense.load + accounting_expense_petty_expense.fare), 0) as total'
                ])
                ->leftJoin('accounting_expense_petty_expense','accounting_expense_petty_expense.id = accounting_expense.expense_id and accounting_expense.expense_type_id = 1')
                ->leftJoin('accounting_season','accounting_season.id = accounting_expense.season_id')
                ;

        $expenseTwo = Expense::find()
                ->select([
                    'accounting_season.id as id',
                    'COALESCE(sum(accounting_expense_photocopy_expense.total_amount), 0) as total'
                ])
                ->leftJoin('accounting_expense_photocopy_expense','accounting_expense_photocopy_expense.id = accounting_expense.expense_id and accounting_expense.expense_type_id = 2')
                ->leftJoin('accounting_season','accounting_season.id = accounting_expense.season_id')
                ;

        $expenseThree = Expense::find()
                ->select([
                    'accounting_season.id as id',
                    'COALESCE(sum(accounting_expense_other_expense.amount), 0) as total'
                ])
                ->leftJoin('accounting_expense_other_expense','accounting_expense_other_expense.id = accounting_expense.expense_id and accounting_expense.expense_type_id = 3')
                ->leftJoin('accounting_season','accounting_season.id = accounting_expense.season_id')
                ;

        $expenseFour = Expense::find()
                ->select([
                    'accounting_season.id as id',
                    'COALESCE(sum(accounting_expense_bank_deposit.amount), 0) as total'
                ])
                ->leftJoin('accounting_expense_bank_deposit','accounting_expense_bank_deposit.id = accounting_expense.expense_id and accounting_expense.expense_type_id = 4')
                ->leftJoin('accounting_season','accounting_season.id = accounting_expense.season_id')
                ;

        $expenseFive = Expense::find()
                ->select([
                    'accounting_season.id as id',
                    'COALESCE(sum(
                        accounting_expense_operating_expense.staff_salary + 
                        accounting_expense_operating_expense.cash_pf + 
                        accounting_expense_operating_expense.rent + 
                        accounting_expense_operating_expense.utilities + 
                        accounting_expense_operating_expense.equipment_and_labor + 
                        accounting_expense_operating_expense.bir_and_docs + 
                        accounting_expense_operating_expense.marketing
                    ), 0) as total'
                ])
                ->leftJoin('accounting_expense_operating_expense','accounting_expense_operating_expense.id = accounting_expense.id and accounting_expense.expense_type_id = 5')
                ->leftJoin('accounting_season','accounting_season.id = accounting_expense.season_id')
                ;

        if(!empty($incomeDate))
        {
            
            $incomeOne = $incomeOne->andWhere(['between', 'accounting_income.datetime', $incomeDate[0][0].' 00:00:00', $incomeDate[0][1].' 23:59:59']);
            $incomeTwo = $incomeTwo->andWhere(['between', 'accounting_income.datetime', $incomeDate[0][0].' 00:00:00', $incomeDate[0][1].' 23:59:59']);
        }

        if(!empty($expenseDate))
        {
            $expenseOne = $expenseOne->andWhere(['between', 'accounting_expense.datetime', $expenseDate[0][0].' 00:00:00', $expenseDate[0][1].' 23:59:59']);
            $expenseTwo = $expenseTwo->andWhere(['between', 'accounting_expense.datetime', $expenseDate[0][0].' 00:00:00', $expenseDate[0][1].' 23:59:59']);
            $expenseThree = $expenseThree->andWhere(['between', 'accounting_expense.datetime', $expenseDate[0][0].' 00:00:00', $expenseDate[0][1].' 23:59:59']);
            $expenseFour = $expenseFour->andWhere(['between', 'accounting_expense.datetime', $expenseDate[0][0].' 00:00:00', $expenseDate[0][1].' 23:59:59']);
            $expenseFive = $expenseFive->andWhere(['between', 'accounting_expense.datetime', $expenseDate[0][0].' 00:00:00', $expenseDate[0][1].' 23:59:59']);
        }

        if(!empty($studentDate))
        {
            $no_of_students = $no_of_students->andWhere(['between', 'accounting_student_tuition.datetime', $studentDate[0][0].' 00:00:00', $studentDate[0][1].' 23:59:59']);
        }

        if(!empty($branch_program_id))
        {
            $seasons = $seasons->andWhere(['in', 'accounting_season.branch_program_id', $branch_program_id]);
            $no_of_students = $no_of_students->andWhere(['in', 'accounting_season.branch_program_id', $branch_program_id]);
            $incomeOne = $incomeOne->andWhere(['in', 'accounting_season.branch_program_id', $branch_program_id]);
            $incomeTwo = $incomeTwo->andWhere(['in', 'accounting_season.branch_program_id', $branch_program_id]);
            $expenseOne = $expenseOne->andWhere(['in', 'accounting_season.branch_program_id', $branch_program_id]);
            $expenseTwo = $expenseTwo->andWhere(['in', 'accounting_season.branch_program_id', $branch_program_id]);
            $expenseThree = $expenseThree->andWhere(['in', 'accounting_season.branch_program_id', $branch_program_id]);
            $expenseFour = $expenseFour->andWhere(['in', 'accounting_season.branch_program_id', $branch_program_id]);
            $expenseFive = $expenseFive->andWhere(['in', 'accounting_season.branch_program_id', $branch_program_id]);
        }

        if(!empty($amount_type_id))
        {
            $incomeOne = $incomeOne->andWhere(['in', 'accounting_income.amount_type', $amount_type_id]);
            $incomeTwo = $incomeTwo->andWhere(['in', 'accounting_income.amount_type', $amount_type_id]);
            $expenseOne = $expenseOne->andWhere(['in', 'accounting_expense.amount_type', $amount_type_id]);
            $expenseTwo = $expenseTwo->andWhere(['in', 'accounting_expense.amount_type', $amount_type_id]);
            $expenseThree = $expenseThree->andWhere(['in', 'accounting_expense.amount_type', $amount_type_id]);
            $expenseFour = $expenseFour->andWhere(['in', 'accounting_expense.amount_type', $amount_type_id]);
            $expenseFive = $expenseFive->andWhere(['in', 'accounting_expense.amount_type', $amount_type_id]);
        }

        if(!empty($charge_to_id))
        {
            $expenseOne = $expenseOne->andWhere(['in', 'accounting_expense_petty_expense.charge_to', $charge_to_id]);
            $expenseTwo = $expenseTwo->andWhere(['in', 'accounting_expense_photocopy_expense.charge_to', $charge_to_id]);
            $expenseThree = $expenseThree->andWhere(['in', 'accounting_expense_other_expense.charge_to', $charge_to_id]);
            //$expenseFour = $expenseFour->andWhere(['in', 'accounting_expense_bank_deposit.charge_to', $amount_type_id]);
            $expenseFive = $expenseFive->andWhere(['in', 'accounting_expense_operating_expense.charge_to', $charge_to_id]);
        }

        $no_of_students = $no_of_students
                    ->groupBy(['accounting_season.id'])
                    ->createCommand()
                    ->getRawSql();

        $incomeOne = $incomeOne
                    ->groupBy(['accounting_season.id'])
                    ->createCommand()
                    ->getRawSql();

        $incomeTwo = $incomeTwo
                    ->groupBy(['accounting_season.id'])
                    ->createCommand()
                    ->getRawSql();

        $expenseOne = $expenseOne
                    ->groupBy(['accounting_season.id'])
                    ->createCommand()
                    ->getRawSql();

        $expenseTwo = $expenseTwo
                    ->groupBy(['accounting_season.id'])
                    ->createCommand()
                    ->getRawSql();

        $expenseThree = $expenseThree
                    ->groupBy(['accounting_season.id'])
                    ->createCommand()
                    ->getRawSql();

        $expenseFour = $expenseFour
                    ->groupBy(['accounting_season.id'])
                    ->createCommand()
                    ->getRawSql();

        $expenseFive = $expenseFive
                    ->groupBy(['accounting_season.id'])
                    ->createCommand()
                    ->getRawSql();

        $seasons = $seasons
                    ->leftJoin(['no_of_students' => '('.$no_of_students.')'], 'no_of_students.id = accounting_season.id')
                    ->leftJoin(['incomeOne' => '('.$incomeOne.')'], 'incomeOne.id = accounting_season.id')
                    ->leftJoin(['incomeTwo' => '('.$incomeTwo.')'], 'incomeTwo.id = accounting_season.id')
                    ->leftJoin(['expenseOne' => '('.$expenseOne.')'], 'expenseOne.id = accounting_season.id')
                    ->leftJoin(['expenseTwo' => '('.$expenseTwo.')'], 'expenseTwo.id = accounting_season.id')
                    ->leftJoin(['expenseThree' => '('.$expenseThree.')'], 'expenseThree.id = accounting_season.id')
                    ->leftJoin(['expenseFour' => '('.$expenseFour.')'], 'expenseFour.id = accounting_season.id')
                    ->leftJoin(['expenseFive' => '('.$expenseFive.')'], 'expenseFive.id = accounting_season.id')
                    ->asArray()
                    ->all();

        $seasonNames = [];
        $enrolmentData = [];
        $freebieData = [];
        $pettyExpenseData = [];
        $photocopyExpenseData = [];
        $otherExpenseData = [];
        $bankDepositData = [];
        $operatingExpenseData = [];
        $netIncomeData = [];
        
        if(!empty($seasons))
        {
            foreach($seasons as $season)
            {
                $seasonNames[] = $season['branchProgramName'].' - '.$season['seasonName'];
                $enrolmentData[] = floatval($season['incomeOneTotal']);
                $freebieData[] = floatval($season['incomeTwoTotal']);
                $pettyExpenseData[] = floatval($season['expenseOneTotal']);
                $photocopyExpenseData[] = floatval($season['expenseTwoTotal']);
                $otherExpenseData[] = floatval($season['expenseThreeTotal']);
                $bankDepositData[] = floatval($season['expenseFourTotal']);
                $operatingExpenseData[] = floatval($season['expenseFiveTotal']);
                $netIncomeData[] = floatval(($season['incomeOneTotal'] + $season['incomeTwoTotal']) - ($season['expenseOneTotal'] + $season['expenseTwoTotal'] + $season['expenseThreeTotal'] + $season['expenseFourTotal'] + $season['expenseFiveTotal']));
            }
        }
        
        return $this->renderAjax('_season-summary',[
            'incomeDate' => $incomeDate,
            'expenseDate' => $expenseDate,
            'studentDate' => $studentDate,
            'seasons' => $seasons,
            'seasonNames' => $seasonNames,
            'enrolmentData' => $enrolmentData,
            'freebieData' => $freebieData,
            'pettyExpenseData' => $pettyExpenseData,
            'photocopyExpenseData' => $photocopyExpenseData,
            'otherExpenseData' => $otherExpenseData,
            'bankDepositData' => $bankDepositData,
            'operatingExpenseData' => $operatingExpenseData,
            'netIncomeData' => $netIncomeData,
        ]);
    }

    public function actionBudgetProposal($params)
    {
        $params = json_decode($params, true);
        $date = [];

        $dates = Income::find()->select(['distinct(date(datetime)) as year'])->orderBy(['date(datetime)' => SORT_DESC]);

        if(empty($params))
        {
            $dates = $dates->asArray()->all();

            if(!empty($dates))
            {
                $date[0][0] = $dates[count($dates)-1]['year'];
                $date[0][1] = $dates[0]['year'];
            }
        }

        $branch_program_id = [];
        $amount_type_id = [];
        $charge_to_id = [];

        if(!empty($params))
        {
            foreach($params as $param)
            {
                if($param['name'] == 'date')
                {
                    if(!empty($param['value']))
                    {
                        $date[] = explode(" - ", $param['value']);
                    }else{
                        $dates = $dates->asArray()->all();

                        if(!empty($dates))
                        {
                            $date[0][0] = $dates[count($dates)-1]['year'];
                            $date[0][1] = $dates[0]['year'];
                        }
                    }
                }else if($param['name'] == 'branch_program_id[]'){
                    $branch_program_id[] = $param['value'];
                }else if($param['name'] == 'amount_type_id[]'){
                    $amount_type_id[] = $param['value'];
                }else if($param['name'] == 'charge_to_id[]'){
                    $charge_to_id[] = $param['value'];
                }
            }
        }

        $byType  = BudgetProposal::find()
                ->select([
                    'accounting_income_budget_proposal.budget_proposal_type_id as id',
                    'accounting_budget_proposal_type.name as name',
                    'count(accounting_income_budget_proposal.id) as total',
                ])
                ->leftJoin('accounting_budget_proposal_type','accounting_budget_proposal_type.id = accounting_income_budget_proposal.budget_proposal_type_id');

        $byApproval  = BudgetProposal::find()
                ->select([
                    'accounting_income_budget_proposal.approval_status as name',
                    'count(accounting_income_budget_proposal.id) as total',
                ]);

        $approvedAmount = BudgetProposal::find()
                                ->select([
                                    'count(accounting_income_budget_proposal.id) as no_of_proposal',
                                    'sum(COALESCE(particulars.total, 0)) as total',
                                ])
                                ->leftJoin(['particulars' => '(
                                    (
                                        SELECT
                                            accounting_budget_proposal_particular.budget_proposal_id,
                                            sum(accounting_budget_proposal_particular.amount) as total
                                        from accounting_budget_proposal_particular
                                        LEFT JOIN accounting_income_budget_proposal on accounting_income_budget_proposal.id = accounting_budget_proposal_particular.budget_proposal_id
                                        WHERE accounting_budget_proposal_particular.approval_status = "Approved"
                                        GROUP BY accounting_budget_proposal_particular.budget_proposal_id
                                    )
                                )'], 'particulars.budget_proposal_id = accounting_income_budget_proposal.id')
                                ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_budget_proposal.id and accounting_income.income_type_id = 3')
                                ->leftJoin('accounting_branch_program','accounting_branch_program.branch_id = accounting_income.branch_id and accounting_branch_program.program_id = accounting_income.program_id')
                                ->andWhere(['accounting_income_budget_proposal.approval_status' => 'Approved']);

        $liquidatedAmount = BudgetProposal::find()
                                ->select([
                                    'count(particulars.total) as no_of_proposal',
                                    'sum(COALESCE(particulars.total, 0)) as total',
                                ])
                                ->leftJoin(['particulars' => '(
                                    (
                                        SELECT
                                            accounting_budget_proposal_liquidation.budget_proposal_id,
                                            sum(accounting_budget_proposal_liquidation.amount) as total
                                        from accounting_budget_proposal_liquidation
                                        LEFT JOIN accounting_income_budget_proposal on accounting_income_budget_proposal.id = accounting_budget_proposal_liquidation.budget_proposal_id
                                        WHERE accounting_budget_proposal_liquidation.approval_status = "Approved"
                                        GROUP BY accounting_budget_proposal_liquidation.budget_proposal_id
                                    )
                                )'], 'particulars.budget_proposal_id = accounting_income_budget_proposal.id')
                                ->leftJoin('accounting_income','accounting_income.income_id = accounting_income_budget_proposal.id and accounting_income.income_type_id = 3')
                                ->leftJoin('accounting_branch_program','accounting_branch_program.branch_id = accounting_income.branch_id and accounting_branch_program.program_id = accounting_income.program_id')
                                ->andWhere(['accounting_income_budget_proposal.approval_status' => 'Approved']);

        if(!empty($date))
        {
            $byType = $byType->andWhere(['between', 'accounting_income_budget_proposal.datetime', $date[0][0].' 00:00:00', $date[0][1].' 23:59:59']);
            $byApproval = $byApproval->andWhere(['between', 'accounting_income_budget_proposal.datetime', $date[0][0].' 00:00:00', $date[0][1].' 23:59:59']);
            $approvedAmount = $approvedAmount->andWhere(['between', 'accounting_income_budget_proposal.datetime', $date[0][0].' 00:00:00', $date[0][1].' 23:59:59']);
            $liquidatedAmount = $liquidatedAmount->andWhere(['between', 'accounting_income_budget_proposal.datetime', $date[0][0].' 00:00:00', $date[0][1].' 23:59:59']);
        }

        if(!empty($branch_program_id))
        {
            $byType = $byType->andWhere(['in', 'accounting_income_budget_proposal.branch_program_id', $branch_program_id]);
            $byApproval = $byApproval->andWhere(['in', 'accounting_income_budget_proposal.branch_program_id', $branch_program_id]);
            $approvedAmount = $approvedAmount->andWhere(['in', 'accounting_branch_program.id', $branch_program_id]);
            $liquidatedAmount = $liquidatedAmount->andWhere(['in', 'accounting_branch_program.id', $branch_program_id]);
        }

        if(!empty($amount_type_id))
        {
            $approvedAmount = $approvedAmount->andWhere(['in', 'accounting_income.amount_type', $amount_type_id]);
            $liquidatedAmount = $liquidatedAmount->andWhere(['in', 'accounting_income.amount_type', $amount_type_id]);
        }

        $byTypeData = [];
        $byApprovalData = [];

        $byType = $byType
                    ->groupBy(['accounting_income_budget_proposal.budget_proposal_type_id'])
                    ->orderBy(['accounting_income_budget_proposal.budget_proposal_type_id' => SORT_ASC])
                    ->asArray()
                    ->all();

        $byApproval = $byApproval
                    ->groupBy(['accounting_income_budget_proposal.approval_status'])
                    ->orderBy(['accounting_income_budget_proposal.approval_status' => SORT_ASC])
                    ->asArray()
                    ->all();

        $approvedAmount = $approvedAmount
                    ->asArray()
                    ->one();

        $liquidatedAmount = $liquidatedAmount
                    ->asArray()
                    ->one();

        if(!empty($byType))
        {
            foreach($byType as $by)
            {
                $percentage = count($byType) > 0 ? ($by['total']/count($byType))*100 : 0;
                $byTypeData[] = [$by['name'], floatval($percentage)];
            }
        } 

        if(!empty($byApproval))
        {
            foreach($byApproval as $by)
            {
                $percentage = count($byApproval) > 0 ? ($by['total']/count($byApproval))*100 : 0;
                $byApprovalData[] = [$by['name'], floatval($percentage)];
            }
        }        

        return $this->renderAjax('_budget-proposal',[
            'byTypeData' => $byTypeData,
            'byApprovalData' => $byApprovalData,
            'approvedAmount' => $approvedAmount,
            'liquidatedAmount' => $liquidatedAmount,
            'date' => $date
        ]);
    }

    public function actionPettyExpense($params)
    {
        $params = json_decode($params, true);
        $date = [];

        $dates = Expense::find()->select(['distinct(date(datetime)) as year'])->orderBy(['date(datetime)' => SORT_DESC]);

        if(empty($params))
        {
            $dates = $dates->asArray()->all();

            if(!empty($dates))
            {
                $date[0][0] = $dates[count($dates)-1]['year'];
                $date[0][1] = $dates[0]['year'];
            }
        }

        $branch_program_id = [];
        $amount_type_id = [];
        $charge_to_id = [];

        if(!empty($params))
        {
            foreach($params as $param)
            {
                if($param['name'] == 'date')
                {
                    if(!empty($param['value']))
                    {
                        $date[] = explode(" - ", $param['value']);
                    }else{
                        $dates = $dates->asArray()->all();

                        if(!empty($dates))
                        {
                            $date[0][0] = $dates[count($dates)-1]['year'];
                            $date[0][1] = $dates[0]['year'];
                        }
                    }
                }else if($param['name'] == 'branch_program_id[]'){
                    $branch_program_id[] = $param['value'];
                }else if($param['name'] == 'amount_type_id[]'){
                    $amount_type_id[] = $param['value'];
                }else if($param['name'] == 'charge_to_id[]'){
                    $charge_to_id[] = $param['value'];
                }
            }
        }

        $expense = Expense::find()
                ->select([
                    'COALESCE(SUM(accounting_expense_petty_expense.food), 0) as foodTotal',
                    'COALESCE(SUM(accounting_expense_petty_expense.supplies), 0) as suppliesTotal',
                    'COALESCE(SUM(accounting_expense_petty_expense.load), 0) as loadTotal',
                    'COALESCE(SUM(accounting_expense_petty_expense.fare), 0) as fareTotal',
                    'COALESCE(SUM(accounting_expense_petty_expense.food + accounting_expense_petty_expense.supplies + accounting_expense_petty_expense.load + accounting_expense_petty_expense.fare), 0) as expenseTotal',
                ])
                ->leftJoin('accounting_expense_petty_expense','accounting_expense_petty_expense.id = accounting_expense.expense_id and accounting_expense.expense_type_id = 1')
                ->leftJoin('accounting_season','accounting_season.id = accounting_expense.season_id');

        if(!empty($date))
        {
            $expense = $expense->andWhere(['between', 'accounting_expense.datetime', $date[0][0].' 00:00:00', $date[0][1].' 23:59:59']);
        }

        if(!empty($branch_program_id))
        {
            $expense = $expense->andWhere(['in', 'accounting_season.branch_program_id', $branch_program_id]);
        }

        if(!empty($amount_type_id))
        {
            $expense = $expense->andWhere(['in', 'accounting_expense.amount_type', $amount_type_id]);
        }

        if(!empty($charge_to_id))
        {
            $expense = $expense->andWhere(['in', 'accounting_expense_petty_expense.charge_to', $charge_to_id]);
        }

        $expense = $expense
                    ->asArray()
                    ->one();

        $data = [];
        $data[0]['name'] = 'Food';
        $data[0]['y'] = $expense['expenseTotal'] > 0 ? floatval(($expense['foodTotal']/$expense['expenseTotal'])*100) : floatval(0);
        $data[1]['name'] = 'Supplies';
        $data[1]['y'] = $expense['expenseTotal'] > 0 ? floatval(($expense['suppliesTotal']/$expense['expenseTotal'])*100) : floatval(0);
        $data[2]['name'] = 'Load';
        $data[2]['y'] = $expense['expenseTotal'] > 0 ? floatval(($expense['loadTotal']/$expense['expenseTotal'])*100) : floatval(0);
        $data[3]['name'] = 'Fare';
        $data[3]['y'] = $expense['expenseTotal'] > 0 ? floatval(($expense['fareTotal']/$expense['expenseTotal'])*100) : floatval(0);

        return $this->renderAjax('_petty-expense',[
            'data' => $data,
            'date' => $date,
            'expense' => $expense,
        ]);
    }

    public function actionOperatingExpense($params)
    {
        $params = json_decode($params, true);
        $date = [];

        $dates = Expense::find()->select(['distinct(date(datetime)) as year'])->orderBy(['date(datetime)' => SORT_DESC]);

        if(empty($params))
        {
            $dates = $dates->asArray()->all();

            if(!empty($dates))
            {
                $date[0][0] = $dates[count($dates)-1]['year'];
                $date[0][1] = $dates[0]['year'];
            }
        }

        $branch_program_id = [];
        $amount_type_id = [];
        $charge_to_id = [];

        if(!empty($params))
        {
            foreach($params as $param)
            {
                if($param['name'] == 'date')
                {
                    if(!empty($param['value']))
                    {
                        $date[] = explode(" - ", $param['value']);
                    }else{
                        $dates = $dates->asArray()->all();

                        if(!empty($dates))
                        {
                            $date[0][0] = $dates[count($dates)-1]['year'];
                            $date[0][1] = $dates[0]['year'];
                        }
                    }
                }else if($param['name'] == 'branch_program_id[]'){
                    $branch_program_id[] = $param['value'];
                }else if($param['name'] == 'amount_type_id[]'){
                    $amount_type_id[] = $param['value'];
                }else if($param['name'] == 'charge_to_id[]'){
                    $charge_to_id[] = $param['value'];
                }
            }
        }

        $expense = Expense::find()
                ->select([
                    'COALESCE(SUM(accounting_expense_operating_expense.staff_salary), 0) as staffSalaryTotal',
                    'COALESCE(SUM(accounting_expense_operating_expense.cash_pf), 0) as cashPfTotal',
                    'COALESCE(SUM(accounting_expense_operating_expense.rent), 0) as rentTotal',
                    'COALESCE(SUM(accounting_expense_operating_expense.utilities), 0) as utilitiesTotal',
                    'COALESCE(SUM(accounting_expense_operating_expense.equipment_and_labor), 0) as equipmentAndLaborTotal',
                    'COALESCE(SUM(accounting_expense_operating_expense.bir_and_docs), 0) as birAndDocsTotal',
                    'COALESCE(SUM(accounting_expense_operating_expense.marketing), 0) as marketingTotal',
                    'COALESCE(SUM(
                                accounting_expense_operating_expense.staff_salary + 
                                accounting_expense_operating_expense.cash_pf + 
                                accounting_expense_operating_expense.rent + 
                                accounting_expense_operating_expense.utilities + 
                                accounting_expense_operating_expense.equipment_and_labor + 
                                accounting_expense_operating_expense.bir_and_docs + 
                                accounting_expense_operating_expense.marketing 
                            ), 0) as expenseTotal',
                ])
                ->leftJoin('accounting_expense_operating_expense','accounting_expense_operating_expense.id = accounting_expense.expense_id and accounting_expense.expense_type_id = 5')
                ->leftJoin('accounting_season','accounting_season.id = accounting_expense.season_id');

        if(!empty($date))
        {
            $expense = $expense->andWhere(['between', 'accounting_expense.datetime', $date[0][0].' 00:00:00', $date[0][1].' 23:59:59']);
        }

        if(!empty($branch_program_id))
        {
            $expense = $expense->andWhere(['in', 'accounting_season.branch_program_id', $branch_program_id]);
        }

        if(!empty($amount_type_id))
        {
            $expense = $expense->andWhere(['in', 'accounting_expense.amount_type', $amount_type_id]);
        }

        if(!empty($charge_to_id))
        {
            $expense = $expense->andWhere(['in', 'accounting_expense_operating_expense.charge_to', $charge_to_id]);
        }

        $expense = $expense
                    ->asArray()
                    ->one();

        $data = [];
        $data[0]['name'] = 'Staff Salary';
        $data[0]['y'] = $expense['expenseTotal'] > 0 ? floatval(($expense['staffSalaryTotal']/$expense['expenseTotal'])*100) : floatval(0);
        $data[1]['name'] = 'Cash PF';
        $data[1]['y'] = $expense['expenseTotal'] > 0 ? floatval(($expense['cashPfTotal']/$expense['expenseTotal'])*100) : floatval(0);
        $data[2]['name'] = 'Rent';
        $data[2]['y'] = $expense['expenseTotal'] > 0 ? floatval(($expense['rentTotal']/$expense['expenseTotal'])*100) : floatval(0);
        $data[3]['name'] = 'Utilities';
        $data[3]['y'] = $expense['expenseTotal'] > 0 ? floatval(($expense['utilitiesTotal']/$expense['expenseTotal'])*100) : floatval(0);
        $data[4]['name'] = 'Equipment and Labor';
        $data[4]['y'] = $expense['expenseTotal'] > 0 ? floatval(($expense['equipmentAndLaborTotal']/$expense['expenseTotal'])*100) : floatval(0);
        $data[5]['name'] = 'BIR and Docs';
        $data[5]['y'] = $expense['expenseTotal'] > 0 ? floatval(($expense['birAndDocsTotal']/$expense['expenseTotal'])*100) : floatval(0);
        $data[6]['name'] = 'Marketing';
        $data[6]['y'] = $expense['expenseTotal'] > 0 ? floatval(($expense['marketingTotal']/$expense['expenseTotal'])*100) : floatval(0);

        return $this->renderAjax('_operating-expense',[
            'data' => $data,
            'date' => $date,
            'expense' => $expense,
        ]);
    }
}
