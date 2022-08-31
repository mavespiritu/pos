<?php

namespace common\modules\accounting\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\modules\accounting\models\PackageType;

/**
 * PackageTypeSearch represents the model behind the search form of `common\modules\accounting\models\PackageType`.
 */
class PackageTypeSearch extends PackageType
{
    public $enroleeTypeName;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'enrolee_type_id'], 'integer'],
            [['name', 'enroleeTypeName'], 'safe'],
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
        $query = PackageType::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->setSort([
            'attributes' => [
                'enroleeTypeName' => [
                    'asc' => ['accounting_enrolee_type.name' => SORT_ASC],
                    'desc' => ['accounting_enrolee_type..name' => SORT_DESC],
                ],
                'name'
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere(['like', 'accounting_enrolee_type.name', $this->enroleeTypeName])
              ->andFilterWhere(['like', 'name', $this->name]);

        if($params){
            $query->joinWith(['enroleeType' => function ($q) {
                if($this->enroleeTypeName!=""){
                $q->where('accounting_enrolee_type.name LIKE "%' . $this->enroleeTypeName . '%"');
                }
            }]);
        }

        $query->andFilterWhere(['like', 'name', $this->name]);

        return $dataProvider;
    }
}
