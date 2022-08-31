<?php

namespace common\modules\pos\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\modules\pos\models\PosOfficialReceipt;

/**
 * PosOfficialReceiptSearch represents the model behind the search form of `common\modules\pos\models\PosOfficialReceipt`.
 */
class PosOfficialReceiptSearch extends PosOfficialReceipt
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'season_id'], 'integer'],
            [['start_number', 'last_number', 'date_filed', 'datetime'], 'safe'],
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
        $query = PosOfficialReceipt::find();

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
        ]);

        $query->andFilterWhere(['like', 'start_number', $this->start_number])
            ->andFilterWhere(['like', 'last_number', $this->last_number])
            ->andFilterWhere(['like', 'date_filed', $this->date_filed]);

        return $dataProvider;
    }
}
