<?php

namespace common\modules\accounting\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\modules\accounting\models\TargetEnrolee;

/**
 * TargetEnroleeSearch represents the model behind the search form of `common\modules\accounting\models\TargetEnrolee`.
 */
class TargetEnroleeSearch extends TargetEnrolee
{
    public $branchName;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'branch_id', 'no_of_enrolee'], 'integer'],
            [['month', 'branchName'], 'safe'],
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
        $query = TargetEnrolee::find();

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
                'no_of_enrolee'
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
            'no_of_enrolee' => $this->no_of_enrolee,
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
