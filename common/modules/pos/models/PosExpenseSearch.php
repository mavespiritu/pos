<?php

namespace common\modules\pos\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\modules\pos\models\PosExpense;

/**
 * PosExpenseSearch represents the model behind the search form of `common\modules\pos\models\PosExpense`.
 */
class PosExpenseSearch extends PosExpense
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'season_id', 'vendor_id', 'account_id', 'amount_type_id'], 'integer'],
            [['voucher_no', 'transaction_no', 'expense_date', 'datetime', 'status'], 'safe'],
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
        $query = Yii::$app->user->identity->userinfo->BRANCH_C != "" ? PosExpense::find()
        ->joinWith('season')
        ->joinWith('vendor')
        ->joinWith('account')
        ->joinWith('amountType')
        ->joinWith('season.branchProgram')
        ->joinWith('season.branchProgram.branch')
        ->joinWith('season.branchProgram.program')
        ->andWhere(['pos_branch.id' => Yii::$app->user->identity->userinfo->BRANCH_C])
        ->orderBy(['pos_expense.id' => SORT_DESC]) :  PosExpense::find()
        ->joinWith('season')
        ->joinWith('vendor')
        ->joinWith('account')
        ->joinWith('amountType')
        ->joinWith('season.branchProgram')
        ->joinWith('season.branchProgram.branch')
        ->joinWith('season.branchProgram.program')
        ->orderBy(['pos_expense.id' => SORT_DESC]);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->setSort([
            'attributes' => [
                'id_number',
                'seasonName' => [
                    'asc' => ['concat(pos_branch.title," - ",pos_program.title," - SEASON "," ",pos_season.title)' => SORT_ASC],
                    'desc' => ['concat(pos_branch.title," - ",pos_program.title," - SEASON "," ",pos_season.title)' => SORT_DESC],
                ],
                'vendorName' => [
                    'asc' => ['pos_vendor.title' => SORT_ASC],
                    'desc' => ['pos_vendor.title' => SORT_DESC],
                ],
                'accountName' => [
                    'asc' => ['pos_account.title' => SORT_ASC],
                    'desc' => ['pos_account.title' => SORT_DESC],
                ],
                'amountTypeName' => [
                    'asc' => ['pos_amount_type.title' => SORT_ASC],
                    'desc' => ['pos_amount_type.title' => SORT_DESC],
                ],
                'expense_date',
                'voucher_no'
                //'status'
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
            'pos_expense.id' => $this->id,
            'pos_expense.season_id' => $this->season_id,
            'pos_expense.vendor_id' => $this->vendor_id,
            'pos_expense.account_id' => $this->account_id,
            'pos_expense.amount_type_id' => $this->amount_type_id,
            'pos_expense.expense_date' => $this->expense_date,
            'pos_expense.datetime' => $this->datetime,
        ]);

        $query->andFilterWhere(['like', 'pos_expense.voucher_no', $this->voucher_no])
            ->andFilterWhere(['like', 'pos_expense.transaction_no', $this->transaction_no])
            ->andFilterWhere(['like', 'pos_expense.status', $this->status]);

        return $dataProvider;
    }
}
