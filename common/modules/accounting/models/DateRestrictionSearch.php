<?php

namespace common\modules\accounting\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\modules\accounting\models\DateRestriction;

/**
 * DateRestrictionSearch represents the model behind the search form of `common\modules\accounting\models\DateRestriction`.
 */
class DateRestrictionSearch extends DateRestriction
{
    public $branchName;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'branch_id'], 'integer'],
            [['branchName', 'allow','start_date', 'end_date', 'open_type'], 'safe'],
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
        $query = DateRestriction::find();

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
                'allow',
                'start_date',
                'end_date',
                'open_type',
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
            'allow' => $this->allow,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
        ]);

        $query->andFilterWhere(['like', 'start_date', $this->start_date])
              ->andFilterWhere(['like', 'end_date', $this->end_date])
              ->andFilterWhere(['like', 'allow', $this->allow])
              ->andFilterWhere(['like', 'open_type', $this->open_type])
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
