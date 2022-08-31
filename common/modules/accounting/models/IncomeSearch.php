<?php

namespace common\modules\accounting\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\modules\accounting\models\Income;

/**
 * IncomeSearch represents the model behind the search form of `common\modules\accounting\models\Income`.
 */
class IncomeSearch extends Income
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'income_type_id', 'branch_id', 'income_id'], 'integer'],
            [['amount_type', 'datetime'], 'safe'],
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
        $query = Income::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
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
            'income_type_id' => $this->income_type_id,
            'branch_id' => $this->branch_id,
            'income_id' => $this->income_id,
            'datetime' => $this->datetime,
        ]);

        $query->andFilterWhere(['like', 'amount_type', $this->amount_type]);

        return $dataProvider;
    }
}
