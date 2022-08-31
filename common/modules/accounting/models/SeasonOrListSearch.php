<?php

namespace common\modules\accounting\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\modules\accounting\models\SeasonOrList;

/**
 * SeasonOrListSearch represents the model behind the search form of `common\modules\accounting\models\SeasonOrList`.
 */
class SeasonOrListSearch extends SeasonOrList
{
    public $seasonName;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'season_id', 'no_of_pieces'], 'integer'],
            [['or_start', 'seasonName'], 'safe'],
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
        $query = SeasonOrList::find()
                ->joinWith('season')
                ->joinWith('season.branchProgram')
                ->joinWith('season.branchProgram.branch')
                ->joinWith('season.branchProgram.program')
                ;

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->setSort([
            'attributes' => [
                'seasonName' => [
                    'asc' => ['concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name)' => SORT_ASC],
                    'desc' => ['concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name)' => SORT_DESC],
                ],
                'or_start',
                'no_of_pieces'
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
            'season_id' => $this->season_id,
            'no_of_pieces' => $this->no_of_pieces,
        ]);

        $query->andFilterWhere(['like', 'or_start', $this->or_start]);

        $query->andFilterWhere(['like', 'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name)', $this->seasonName])
        ;

        return $dataProvider;
    }
}
