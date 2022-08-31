<?php

namespace common\modules\accounting\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use common\modules\accounting\models\OperatingExpense;

/**
 * OperatingExpenseSearch represents the model behind the search form of `common\modules\accounting\models\OperatingExpense`.
 */
class OperatingExpenseSearch extends OperatingExpense
{
    public $seasonName;
    public $amountType;
    public $datetime;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['cv_no', 'particulars', 'seasonName', 'amountType', 'datetime','charge_to'], 'safe'],
            [['staff_salary', 'cash_pf', 'rent', 'utilities', 'equipment_and_labor', 'bir_and_docs', 'marketing'], 'number'],
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
            $query = $access ? $access->branch_program_id!= '' ? OperatingExpense::find()
                ->joinWith('expense')
                ->joinWith('season')
                ->joinWith('season.branchProgram')
                ->joinWith('season.branchProgram.branch')
                ->joinWith('season.branchProgram.program')
                ->andWhere(['accounting_expense.expense_type_id' => '5'])
                ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                ->andWhere(['not in', 'accounting_expense.season_id', $archivedSeasons])
                ->orderBy(['accounting_expense.datetime' => SORT_DESC]) :
                OperatingExpense::find()
                ->joinWith('expense')
                ->joinWith('season')
                ->joinWith('season.branchProgram')
                ->joinWith('season.branchProgram.branch')
                ->joinWith('season.branchProgram.program')
                ->andWhere(['accounting_expense.expense_type_id' => '5'])
                ->andWhere(['not in', 'accounting_expense.season_id', $archivedSeasons])
                ->orderBy(['accounting_expense.datetime' => SORT_DESC]) :
                OperatingExpense::find()
                ->joinWith('expense')
                ->joinWith('season')
                ->joinWith('season.branchProgram')
                ->joinWith('season.branchProgram.branch')
                ->joinWith('season.branchProgram.program')
                ->andWhere(['accounting_expense.expense_type_id' => '5'])
                ->andWhere(['not in', 'accounting_expense.season_id', $archivedSeasons])
                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                ;
        }else{
            $query = $access ? $access->branch_program_id!= '' ? OperatingExpense::find()
                ->joinWith('expense')
                ->joinWith('season')
                ->joinWith('season.branchProgram')
                ->joinWith('season.branchProgram.branch')
                ->joinWith('season.branchProgram.program')
                ->where(['accounting_expense.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                ->andWhere(['accounting_expense.expense_type_id' => '5'])
                ->andWhere(['not in', 'accounting_expense.season_id', $archivedSeasons])
                ->orderBy(['accounting_expense.datetime' => SORT_DESC]) :
                OperatingExpense::find()
                ->joinWith('expense')
                ->joinWith('season')
                ->joinWith('season.branchProgram')
                ->joinWith('season.branchProgram.branch')
                ->joinWith('season.branchProgram.program')
                ->where(['accounting_expense.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                ->andWhere(['accounting_expense.expense_type_id' => '5'])
                ->andWhere(['not in', 'accounting_expense.season_id', $archivedSeasons])
                ->orderBy(['accounting_expense.datetime' => SORT_DESC]) :
                OperatingExpense::find()
                ->joinWith('expense')
                ->joinWith('season')
                ->joinWith('season.branchProgram')
                ->joinWith('season.branchProgram.branch')
                ->joinWith('season.branchProgram.program')
                ->where(['accounting_expense.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                ->andWhere(['accounting_expense.expense_type_id' => '5'])
                ->andWhere(['not in', 'accounting_expense.season_id', $archivedSeasons])
                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                ;
        }

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->setSort([
            'attributes' => [
                'cv_no',
                'particulars',
                'staff_salary',
                'cash_pf',
                'rent',
                'utilities',
                'equipment_and_labor',
                'utilities',
                'bir_and_docs',
                'marketing',
                'charge_to',
                'seasonName' => [
                    'asc' => ['concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name)' => SORT_ASC],
                    'desc' => ['concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name)' => SORT_DESC],
                ],
                'amountType' => [
                    'asc' => ['accounting_expense.amount_type' => SORT_ASC],
                    'desc' => ['accounting_expense.amount_type' => SORT_DESC],
                ],
                'datetime' => [
                    'asc' => ['accounting_expense.datetime' => SORT_ASC],
                    'desc' => ['accounting_expense.datetime' => SORT_DESC],
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
            'staff_salary' => $this->staff_salary,
            'cash_pf' => $this->cash_pf,
            'rent' => $this->rent,
            'utilities' => $this->utilities,
            'equipment_and_labor' => $this->equipment_and_labor,
            'bir_and_docs' => $this->bir_and_docs,
            'marketing' => $this->marketing,
        ]);

        $query->andFilterWhere(['like', 'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name)', $this->seasonName])
            ->andFilterWhere(['like', 'cv_no', $this->cv_no])
            ->andFilterWhere(['like', 'charge_to', $this->charge_to])
            ->andFilterWhere(['like', 'particulars', $this->particulars]);

        $query->andFilterWhere(['like', 'accounting_expense.amount_type', $this->amountType])
              ->andFilterWhere(['like', 'accounting_expense.datetime', $this->datetime])
        ;

        return $dataProvider;
    }
}
