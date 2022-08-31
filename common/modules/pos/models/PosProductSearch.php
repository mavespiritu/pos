<?php

namespace common\modules\pos\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\modules\pos\models\PosProduct;

/**
 * PosProductSearch represents the model behind the search form of `common\modules\pos\models\PosProduct`.
 */
class PosProductSearch extends PosProduct
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'season_id', 'enrolment_type_id', 'product_type_id'], 'integer'],
            [['title', 'description'], 'safe'],
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
        $query = PosProduct::find();

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
            'season_id' => $this->season_id,
            'enrolment_type_id' => $this->enrolment_type_id,
            'product_type_id' => $this->product_type_id,
            'amount' => $this->amount,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'description', $this->description]);

        return $dataProvider;
    }
}
