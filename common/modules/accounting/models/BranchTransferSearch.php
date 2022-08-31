<?php

namespace common\modules\accounting\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use common\modules\accounting\models\BranchTransfer;

/**
 * BranchTransferSearch represents the model behind the search form of `common\modules\accounting\models\BranchTransfer`.
 */
class BranchTransferSearch extends BranchTransfer
{
    public $branchName;
    public $branchProgramName;
    public $amountType;
    public $datetime;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'branch_id', 'branch_program_id'], 'integer'],
            [['amount_source','branchName', 'branchProgramName', 'amountType', 'datetime'], 'safe'],
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

        if(in_array('TopManagement',$rolenames)){
            $query = $access ? $access->branch_program_id!= '' ? BranchTransfer::find()
                ->joinWith('expense')
                ->joinWith('branch')
                ->joinWith('branchProgram')
                ->joinWith('branchProgram.branch fromBranch')
                ->joinWith('branchProgram.program fromProgram')
                ->andWhere(['accounting_expense.expense_type_id' => '6'])
                ->andWhere(['accounting_expense_branch_transfer.branch_program_id' => $access->branch_program_id])
                ->orderBy(['accounting_expense.datetime' => SORT_DESC]) :
                BranchTransfer::find()
                ->joinWith('expense')
                ->joinWith('branch')
                ->joinWith('branchProgram')
                ->joinWith('branchProgram.branch fromBranch')
                ->joinWith('branchProgram.program fromProgram')
                ->andWhere(['accounting_expense.expense_type_id' => '6'])
                ->orderBy(['accounting_expense.datetime' => SORT_DESC]) :
                BranchTransfer::find()
                ->joinWith('expense')
                ->joinWith('branch')
                ->joinWith('branchProgram')
                ->joinWith('branchProgram.branch fromBranch')
                ->joinWith('branchProgram.program fromProgram')
                ->andWhere(['accounting_expense.expense_type_id' => '6'])
                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                ;
        }else{
            $query = $access ? $access->branch_program_id!= '' ? BranchTransfer::find()
                ->joinWith('expense')
                ->joinWith('branch')
                ->joinWith('branchProgram')
                ->joinWith('branchProgram.branch fromBranch')
                ->joinWith('branchProgram.program fromProgram')
                ->andWhere(['accounting_expense.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                ->andWhere(['accounting_expense_branch_transfer.branch_program_id' => $access->branch_program_id])
                ->andWhere(['accounting_expense.expense_type_id' => '6'])
                ->orderBy(['accounting_expense.datetime' => SORT_DESC]) :
                BranchTransfer::find()
                ->joinWith('expense')
                ->joinWith('branch')
                ->joinWith('branchProgram')
                ->joinWith('branchProgram.branch fromBranch')
                ->joinWith('branchProgram.program fromProgram')
                ->andWhere(['accounting_expense.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                ->andWhere(['accounting_expense.expense_type_id' => '6'])
                ->orderBy(['accounting_expense.datetime' => SORT_DESC]) :
                BranchTransfer::find()
                ->joinWith('expense')
                ->joinWith('branch')
                ->joinWith('branchProgram')
                ->joinWith('branchProgram.branch fromBranch')
                ->joinWith('branchProgram.program fromProgram')
                ->andWhere(['accounting_expense.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                ->andWhere(['accounting_expense.expense_type_id' => '6'])
                ->orderBy(['accounting_expense.datetime' => SORT_DESC])
                ;
        }

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
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
                'amount',
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
            'branch_id' => $this->branch_id,
            'branch_program_id' => $this->branch_program_id,
            'amount' => $this->amount,
        ]);

        $query->andFilterWhere(['like', 'amount_source', $this->amount_source])
              ->andFilterWhere(['like', 'accounting_branch.name', $this->branchName])
              ->andFilterWhere(['like', 'concat(fromBranch.name," - ",fromProgram.name)', $this->branchProgramName])
              ->andFilterWhere(['like', 'accounting_expense.amount_type', $this->amountType])
              ->andFilterWhere(['like', 'accounting_expense.datetime', $this->datetime])
        ;

        return $dataProvider;
    }
}
