<?php

namespace common\modules\accounting\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use common\modules\accounting\models\IncomeEnrolment;

/**
 * IncomeEnrolmentSearch represents the model behind the search form of `common\modules\accounting\models\IncomeEnrolment`.
 */
class IncomeEnrolmentSearch extends IncomeEnrolment
{
    public $seasonName;
    public $codeName;
    public $studentName;
    public $amountType;
    public $datetime;
    public $transactionNumber;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'season_id', 'code_id', 'student_id'], 'integer'],
            [['or_no', 'ar_no', 'seasonName', 'codeName', 'studentName', 'amountType', 'datetime', 'transactionNumber'], 'safe'],
            [['amount'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $user_info = Yii::$app->user->identity->userinfo;
        $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
        $rolenames =  ArrayHelper::map($roles, 'name','name');

        $access = AccessProgram::findOne(['user_id' => Yii::$app->user->identity->userinfo->user_id]);
        $archivedSeasons = ArchiveSeason::find()->select(['season_id as id'])->asArray()->all();
        $archivedSeasons = ArrayHelper::map($archivedSeasons, 'id', 'id');

        if(in_array('TopManagement',$rolenames)){
            $query = $access ? $access->branch_program_id!= '' ? IncomeEnrolment::find()
                ->joinWith('income')
                ->joinWith('season')
                ->joinWith('season.branchProgram')
                ->joinWith('season.branchProgram.branch')
                ->joinWith('season.branchProgram.program')
                ->joinWith('code')
                ->joinWith('student')
                ->andWhere(['accounting_income.income_type_id' => '1'])
                ->andWhere(['not in', 'accounting_income_enrolment.season_id', $archivedSeasons])
                ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                ->orderBy(['accounting_income.datetime' => SORT_DESC]) :
                 IncomeEnrolment::find()
                ->joinWith('income')
                ->joinWith('season')
                ->joinWith('season.branchProgram')
                ->joinWith('season.branchProgram.branch')
                ->joinWith('season.branchProgram.program')
                ->joinWith('code')
                ->joinWith('student')
                ->andWhere(['accounting_income.income_type_id' => '1'])
                ->andWhere(['not in', 'accounting_income_enrolment.season_id', $archivedSeasons])
                ->orderBy(['accounting_income.datetime' => SORT_DESC]) : 
                IncomeEnrolment::find()
                ->joinWith('income')
                ->joinWith('season')
                ->joinWith('season.branchProgram')
                ->joinWith('season.branchProgram.branch')
                ->joinWith('season.branchProgram.program')
                ->joinWith('code')
                ->joinWith('student')
                ->andWhere(['accounting_income.income_type_id' => '1'])
                ->andWhere(['not in', 'accounting_income_enrolment.season_id', $archivedSeasons])
                ->orderBy(['accounting_income.datetime' => SORT_DESC])
                ;
        }else{
            $query = $access ? $access->branch_program_id!= '' ? IncomeEnrolment::find()
                ->joinWith('income')
                ->joinWith('season')
                ->joinWith('season.branchProgram')
                ->joinWith('season.branchProgram.branch')
                ->joinWith('season.branchProgram.program')
                ->joinWith('code')
                ->joinWith('student')
                ->andWhere(['accounting_income.income_type_id' => '1'])
                ->andWhere(['not in', 'accounting_income_enrolment.season_id', $archivedSeasons])
                ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                ->andWhere(['accounting_branch.id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                ->orderBy(['accounting_income.datetime' => SORT_DESC]) :
                 IncomeEnrolment::find()
                ->joinWith('income')
                ->joinWith('season')
                ->joinWith('season.branchProgram')
                ->joinWith('season.branchProgram.branch')
                ->joinWith('season.branchProgram.program')
                ->joinWith('code')
                ->joinWith('student')
                ->andWhere(['accounting_income.income_type_id' => '1'])
                ->andWhere(['not in', 'accounting_income_enrolment.season_id', $archivedSeasons])
                ->andWhere(['accounting_branch.id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                ->orderBy(['accounting_income.datetime' => SORT_DESC]) : 
                IncomeEnrolment::find()
                ->joinWith('income')
                ->joinWith('season')
                ->joinWith('season.branchProgram')
                ->joinWith('season.branchProgram.branch')
                ->joinWith('season.branchProgram.program')
                ->joinWith('code')
                ->joinWith('student')
                ->andWhere(['accounting_income.income_type_id' => '1'])
                ->andWhere(['not in', 'accounting_income_enrolment.season_id', $archivedSeasons])
                ->andWhere(['accounting_branch.id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                ->orderBy(['accounting_income.datetime' => SORT_DESC])
                ;
        }

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->setSort([
            'attributes' => [
                'seasonName' => [
                    'asc' => ['concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name)' => SORT_ASC],
                    'desc' => ['concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name)' => SORT_DESC],
                ],
                'or_no',
                'ar_no',
                'codeName' => [
                    'asc' => ['concat(accounting_income_code.name," - ",accounting_income_code.description)' => SORT_ASC],
                    'desc' => ['concat(accounting_income_code.name," - ",accounting_income_code.description)' => SORT_DESC],
                ],
                'studentName' => [
                    'asc' => ['concat(accounting_student.id_number," - ",accounting_student.first_name," ",accounting_student.middle_name," ",accounting_student.last_name)' => SORT_ASC],
                    'desc' => ['concat(accounting_student.id_number," - ",accounting_student.first_name," ",accounting_student.middle_name," ",accounting_student.last_name)' => SORT_DESC],
                ],
                'amount',
                'amountType' => [
                    'asc' => ['accounting_income.amount_type' => SORT_ASC],
                    'desc' => ['accounting_income.amount_type' => SORT_DESC],
                ],
                'transactionNumber' => [
                    'asc' => ['accounting_income.transaction_number' => SORT_ASC],
                    'desc' => ['accounting_income.transaction_number' => SORT_DESC],
                ],
                'datetime' => [
                    'asc' => ['accounting_income.datetime' => SORT_ASC],
                    'desc' => ['accounting_income.datetime' => SORT_DESC],
                ],
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'season_id' => $this->season_id,
            'code_id' => $this->code_id,
            'student_id' => $this->student_id,
            'amount' => $this->amount,
        ]);

        $query->andFilterWhere(['like', 'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name)', $this->seasonName])
              ->andFilterWhere(['like', 'or_no', $this->or_no])
              ->andFilterWhere(['like', 'ar_no', $this->or_no])
              ->andFilterWhere(['like', 'concat(accounting_income_code.name," - ",accounting_income_code.description)', $this->codeName])
              ->andFilterWhere(['like', 'concat(accounting_student.id_number," - ",accounting_student.first_name," ",accounting_student.middle_name," ",accounting_student.last_name)', $this->studentName])
              ->andFilterWhere(['like', 'accounting_income.amount_type', $this->amountType])
              ->andFilterWhere(['like', 'accounting_income.transaction_number', $this->transactionNumber])
              ->andFilterWhere(['like', 'accounting_income.datetime', $this->datetime])
        ;

        return $dataProvider;
    }
}
