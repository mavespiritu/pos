<?php

namespace common\modules\accounting\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\modules\accounting\models\ProfessionalRequestDetail;

/**
 * ProfessionalRequestDetailSearch represents the model behind the search form of `common\modules\accounting\models\ProfessionalRequestDetail`.
 */
class ProfessionalRequestDetailSearch extends ProfessionalRequestDetail
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'professional_request_id', 'branch_program_id', 'school_id'], 'integer'],
            [['date', 'concept', 'remarks'], 'safe'],
            [['number_of_hours'], 'number'],
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
        $query = ProfessionalRequestDetail::find();

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
            'professional_request_id' => $this->professional_request_id,
            'date' => $this->date,
            'number_of_hours' => $this->number_of_hours,
            'branch_program_id' => $this->branch_program_id,
            'school_id' => $this->school_id,
        ]);

        $query->andFilterWhere(['like', 'concept', $this->concept])
            ->andFilterWhere(['like', 'remarks', $this->remarks]);

        return $dataProvider;
    }
}
