<?php

namespace common\modules\accounting\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\modules\accounting\models\ProfessionalRequest;

/**
 * ProfessionalRequestSearch represents the model behind the search form of `common\modules\accounting\models\ProfessionalRequest`.
 */
class ProfessionalRequestSearch extends ProfessionalRequest
{
    public $requester; 
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'user_id'], 'integer'],
            [['start_date', 'end_date', 'period_covered', 'bank', 'account_name', 'account_number', 'approval_status', 'datetime','requester'], 'safe'],
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
        $query = ProfessionalRequest::find()
        ->joinWith('userInfo');

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->setSort([
            'attributes' => [
                'start_date',
                'end_date',
                'period_covered',
                'bank',
                'account_name',
                'account_number',
                'approval_status',
                'requester' => [
                    'asc' => ['concat(user_info.FIRST_M," ",user_info.MIDDLE_M," ",user_info.LAST_M," ",user_info.SUFFIX)' => SORT_ASC],
                    'desc' => ['concat(user_info.FIRST_M," ",user_info.MIDDLE_M," ",user_info.LAST_M," ",user_info.SUFFIX)' => SORT_DESC],
                ],
                'datetime'
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
            'accounting_professional_request.user_id' => $this->user_id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
        ]);

        $query->andFilterWhere(['like', 'period_covered', $this->period_covered])
            ->andFilterWhere(['like', 'bank', $this->bank])
            ->andFilterWhere(['like', 'account_name', $this->account_name])
            ->andFilterWhere(['like', 'account_number', $this->account_number])
            ->andFilterWhere(['like', 'approval_status', $this->approval_status]);

        $query->andFilterWhere(['like', 'concat(user_info.FIRST_M," ",user_info.MIDDLE_M," ",user_info.LAST_M," ",user_info.SUFFIX)', $this->requester]);

        return $dataProvider;
    }
}
