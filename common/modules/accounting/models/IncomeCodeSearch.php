<?php

namespace common\modules\accounting\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\modules\accounting\models\IncomeCode;

/**
 * IncomeCodeSearch represents the model behind the search form of `common\modules\accounting\models\IncomeCode`.
 */
class IncomeCodeSearch extends IncomeCode
{
    public $incomeTypeName;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'income_type_id'], 'integer'],
            [['name', 'description', 'incomeTypeName'], 'safe'],
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
        $query = IncomeCode::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->setSort([
            'attributes' => [
                'incomeTypeName' => [
                    'asc' => ['accounting_income_type.name' => SORT_ASC],
                    'desc' => ['accounting_income_type.name' => SORT_DESC],
                ],
                'name',
                'description'
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
            'income_type_id' => $this->income_type_id,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
              ->andFilterWhere(['like', 'description', $this->description]);

        if($params){
            $query->joinWith(['incomeType' => function ($q) {
                if($this->incomeTypeName!=""){
                $q->where('accounting_income_type.name LIKE "%' . $this->incomeTypeName . '%"');
                }
            }]);
        }

        return $dataProvider;
    }
}
