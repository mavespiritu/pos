<?php

namespace common\modules\accounting\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\modules\accounting\models\Enrolment;

/**
 * EnrolmentSearch represents the model behind the search form of `common\modules\accounting\models\Enrolment`.
 */
class EnrolmentSearch extends Enrolment
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'season_id', 'code_id', 'student_id'], 'integer'],
            [['or'], 'safe'],
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
        $query = Enrolment::find();

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
            'code_id' => $this->code_id,
            'student_id' => $this->student_id,
            'amount' => $this->amount,
        ]);

        $query->andFilterWhere(['like', 'or', $this->or]);

        return $dataProvider;
    }
}
