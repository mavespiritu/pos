<?php

namespace common\modules\pos\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\modules\pos\models\PosIncome;

/**
 * PosIncomeSearch represents the model behind the search form of `common\modules\pos\models\PosIncome`.
 */
class PosIncomeSearch extends PosIncome
{
    public $product_id;
    public $amount;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'season_id', 'customer_id', 'official_receipt_id', 'account_id', 'amount_type_id', 'product_id'], 'integer'],
            [['ar_number', 'invoice_date', 'payment_due', 'datetime', 'status', 'amount'], 'safe'],
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
        $query = Yii::$app->user->identity->userinfo->BRANCH_C != "" ? PosIncome::find()
        ->joinWith('customer')
        ->joinWith('season')
        ->joinWith('season.branchProgram')
        ->joinWith('season.branchProgram.branch')
        ->joinWith('season.branchProgram.program')
        ->joinWith('incomeItem.product')
        ->andWhere(['pos_branch.id' => Yii::$app->user->identity->userinfo->BRANCH_C])
        ->orderBy(['pos_income.id' => SORT_DESC]) :  PosIncome::find()
        ->joinWith('customer')
        ->joinWith('season')
        ->joinWith('season.branchProgram')
        ->joinWith('season.branchProgram.branch')
        ->joinWith('season.branchProgram.program')
        ->joinWith('incomeItem.product')
        ->orderBy(['pos_income.id' => SORT_DESC]);

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
                'customerName' => [
                    'asc' => ['concat(pos_customer.first_name," ",pos_customer.middle_name," ",pos_customer.last_name," ",pos_customer.ext_name)' => SORT_ASC],
                    'desc' => ['concat(pos_customer.first_name," ",pos_customer.middle_name," ",pos_customer.last_name," ",pos_customer.ext_name)' => SORT_DESC],
                ],
                'productName' => [
                    'asc' => ['concat(pos_product.title," - ",pos_product.amount)' => SORT_ASC],
                    'desc' => ['concat(pos_product.title," - ",pos_product.amount)' => SORT_DESC],
                ],
                'official_receipt_id',
                'invoice_date',
                'status'
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
            'pos_income.season_id' => $this->season_id,
            'pos_income.customer_id' => $this->customer_id,
            'pos_product.id' => $this->product_id,
            'pos_income.account_id' => $this->account_id,
            'pos_income.amount_type_id' => $this->amount_type_id,
            'invoice_date' => $this->invoice_date,
            'payment_due' => $this->payment_due,
            'datetime' => $this->datetime,
            'pos_income.status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'ar_number', $this->ar_number])
              ->andFilterWhere(['like', 'official_receipt_id', $this->official_receipt_id])
              ->andFilterWhere(['like', '(pos_income_item.quantity * pos_income_item.amount)', $this->amount]);

        return $dataProvider;
    }
}
