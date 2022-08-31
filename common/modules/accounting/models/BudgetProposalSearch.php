<?php

namespace common\modules\accounting\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\modules\accounting\models\BudgetProposal;
use yii\helpers\ArrayHelper;

/**
 * BudgetProposalSearch represents the model behind the search form of `common\modules\accounting\models\BudgetProposal`.
 */
class BudgetProposalSearch extends BudgetProposal
{
    public $branchName;
    public $branchProgramName;
    public $codeName;
    public $budgetProposalTypeName;
    public $amountType;
    public $datetime;
    public $transactionNumber;
    public $amount;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['amount'], 'number'],
            [['id', 'branch_id', 'branch_program_id', 'code_id', 'budget_proposal_type_id'], 'integer'],
            [['branchName', 'codeName', 'branchProgramName', 'amountType', 'datetime','transactionNumber', 'approval_status', 'budgetProposalTypeName', 'other_type', 'datetime'], 'safe']
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

        if(in_array('TopManagement',$rolenames)){
            $query = $access ? $access->branch_program_id!= '' ? BudgetProposal::find()
                ->joinWith('income')
                ->joinWith('branch')
                ->joinWith('branchProgram')
                ->joinWith('branchProgram.branch fromBranch')
                ->joinWith('branchProgram.program fromProgram')
                ->joinWith('code')
                ->joinWith('budgetProposalType')
                ->andWhere(['accounting_income_budget_proposal.branch_program_id' => $access->branch_program_id]) :
                BudgetProposal::find()
                ->joinWith('income')
                ->joinWith('branch')
                ->joinWith('branchProgram')
                ->joinWith('branchProgram.branch fromBranch')
                ->joinWith('branchProgram.program fromProgram')
                ->joinWith('code')
                ->joinWith('budgetProposalType') :
                BudgetProposal::find()
                ->joinWith('income')
                ->joinWith('branch')
                ->joinWith('branchProgram')
                ->joinWith('branchProgram.branch fromBranch')
                ->joinWith('branchProgram.program fromProgram')
                ->joinWith('code')
                ->joinWith('budgetProposalType')
                ;
        }else{
            $query = $access ? $access->branch_program_id!= '' ? BudgetProposal::find()
                ->joinWith('income')
                ->joinWith('branch')
                ->joinWith('branchProgram')
                ->joinWith('branchProgram.branch fromBranch')
                ->joinWith('branchProgram.program fromProgram')
                ->joinWith('code')
                ->joinWith('budgetProposalType')
                ->andWhere(['fromBranch.id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                ->andWhere(['accounting_income_budget_proposal.branch_program_id' => $access->branch_program_id])
                ->orWhere(['accounting_branch.id' => Yii::$app->user->identity->userinfo->BRANCH_C]) : 
                BudgetProposal::find()
                ->joinWith('income')
                ->joinWith('branch')
                ->joinWith('branchProgram')
                ->joinWith('branchProgram.branch fromBranch')
                ->joinWith('branchProgram.program fromProgram')
                ->joinWith('code')
                ->joinWith('budgetProposalType')
                ->andWhere(['fromBranch.id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                ->orWhere(['accounting_branch.id' => Yii::$app->user->identity->userinfo->BRANCH_C]) :
                BudgetProposal::find()
                ->joinWith('income')
                ->joinWith('branch')
                ->joinWith('branchProgram')
                ->joinWith('branchProgram.branch fromBranch')
                ->joinWith('branchProgram.program fromProgram')
                ->joinWith('code')
                ->joinWith('budgetProposalType')
                ->andWhere(['fromBranch.id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                ->orWhere(['accounting_branch.id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                ;
        }
                

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query->distinct(),
        ]);

        $dataProvider->setSort([
            'attributes' => [
                'branchName' => [
                    'asc' => ['branch.name' => SORT_ASC],
                    'desc' => ['branch.name' => SORT_DESC],
                ],
                'branchProgramName' => [
                    'asc' => ['concat(fromBranch.name," - ",fromProgram.name)' => SORT_ASC],
                    'desc' => ['concat(fromBranch.name," - ",fromProgram.name)' => SORT_DESC],
                ],
                'codeName' => [
                    'asc' => ['concat(accounting_income_code.name," - ",accounting_income_code.description)' => SORT_ASC],
                    'desc' => ['concat(accounting_income_code.name," - ",accounting_income_code.description)' => SORT_DESC],
                ],
                'budgetProposalTypeName' => [
                    'asc' => ['accounting_budget_proposal_type.name' => SORT_ASC, 'accounting_income_budget_proposal.other_type' => SORT_ASC],
                    'desc' => ['accounting_budget_proposal_type.name' => SORT_DESC, 'accounting_income_budget_proposal.other_type' => SORT_DESC],
                ],
                'amountType' => [
                    'asc' => ['accounting_income.amount_type' => SORT_ASC],
                    'desc' => ['accounting_income.amount_type' => SORT_DESC],
                ],
                'datetime' => [
                    'asc' => ['accounting_income.datetime' => SORT_ASC],
                    'desc' => ['accounting_income.datetime' => SORT_DESC],
                ],
                'transactionNumber' => [
                    'asc' => ['accounting_income.transaction_number' => SORT_ASC],
                    'desc' => ['accounting_income.transaction_number' => SORT_DESC],
                ],
                'approval_status'
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
            'branch_id' => $this->branch_id,
            'branch_program_id' => $this->branch_program_id,
            'code_id' => $this->code_id,
            'budget_proposal_type_id' => $this->budget_proposal_type_id,
        ]);

        $query->andFilterWhere(['like', 'accounting_branch.name', $this->branchName])
              ->andFilterWhere(['like', 'concat(fromBranch.name," - ",fromProgram.name)', $this->branchProgramName])
              ->andFilterWhere(['like', 'concat(accounting_income_code.name," - ",accounting_income_code.description)', $this->codeName])
              ->andFilterWhere(['like', 'accounting_budget_proposal_type.name', $this->budgetProposalTypeName])
              ->andFilterWhere(['like', 'other_type', $this->other_type])
              ->andFilterWhere(['like', 'accounting_income.amount_type', $this->amountType])
              ->andFilterWhere(['like', 'accounting_income.datetime', $this->datetime])
              ->andFilterWhere(['like', 'accounting_income.transaction_number', $this->transactionNumber])
              ->andFilterWhere(['like', 'approval_status', $this->approval_status])
              ->andFilterWhere(['like', 'datetime', $this->datetime])
              ->andFilterWhere(['like', 'remarks', $this->remarks]);

        return $dataProvider;
    }
}
