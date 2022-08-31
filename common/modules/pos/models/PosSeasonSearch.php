<?php

namespace common\modules\pos\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\modules\pos\models\PosSeason;

/**
 * PosSeasonSearch represents the model behind the search form of `common\modules\pos\models\PosSeason`.
 */
class PosSeasonSearch extends PosSeason
{
    public $branchProgramName;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'branch_program_id'], 'integer'],
            [['title', 'start_date', 'end_date', 'status', 'branchProgramName'], 'safe'],
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
        $query = Yii::$app->user->identity->userinfo->BRANCH_C != "" ? 
        PosSeason::find()
        ->joinWith('branchProgram')
        ->joinWith('branchProgram.branch')
        ->joinWith('branchProgram.program')
        ->andWhere(['pos_branch.id' => Yii::$app->user->identity->userinfo->BRANCH_C])
        ->orderBy(['pos_season.id' => SORT_DESC]) : 
        PosSeason::find()
        ->joinWith('branchProgram')
        ->joinWith('branchProgram.branch')
        ->joinWith('branchProgram.program')
        ->orderBy(['pos_season.id' => SORT_DESC]);


        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->setSort([
            'attributes' => [
                'branchProgramName' => [
                    'asc' => ['concat(pos_branch.title," - ",pos_program.title)' => SORT_ASC],
                    'desc' => ['concat(pos_branch.title," - ",pos_program.title)' => SORT_DESC],
                ],
                'title',
                'start_date',
                'end_date',
                'status',
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
            'branch_program_id' => $this->branch_program_id,
        ]);

        $query->andFilterWhere(['like', 'pos_season.title', $this->title])
            ->andFilterWhere(['like', 'pos_season.status', $this->status])
            ->andFilterWhere(['like', 'pos_season.start_date', $this->start_date])
            ->andFilterWhere(['like', 'pos_season.end_date', $this->end_date])
            ->andFilterWhere(['like', 'concat(pos_branch.title," - ",pos_program.title)', $this->branchProgramName]);

        return $dataProvider;
    }
}
