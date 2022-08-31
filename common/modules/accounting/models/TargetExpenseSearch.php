<?php

namespace common\modules\accounting\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\modules\accounting\models\TargetExpense;

/**
 * TargetExpenseSearch represents the model behind the search form of `common\modules\accounting\models\TargetExpense`.
 */
class TargetExpenseSearch extends TargetExpense
{
    public $branchName;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'branch_id'], 'integer'],
            [['branchName', 'month'], 'safe'],
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
        $query = TargetExpense::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->setSort([
            'attributes' => [
                'branchName' => [
                    'asc' => ['accounting_branch.name' => SORT_ASC],
                    'desc' => ['accounting_branch.name' => SORT_DESC],
                ],
                'month',
                'amount'
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
            'amount' => $this->amount,
        ]);

        $query->andFilterWhere(['like', 'month', $this->month])
              ->andFilterWhere(['like', 'accounting_branch.name', $this->branchName]);

        if($params){
            $query->joinWith(['branch' => function ($q) {
                if($this->branchName!=""){
                $q->where('accounting_branch.name LIKE "%' . $this->branchName . '%"');
                }
            }]);
        }

        return $dataProvider;
    }
}
