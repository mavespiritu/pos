<?php

namespace common\modules\pos\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\modules\pos\models\PosBranchProgram;

/**
 * PosBranchProgramSearch represents the model behind the search form of `common\modules\pos\models\PosBranchProgram`.
 */
class PosBranchProgramSearch extends PosBranchProgram
{
    public $branchName;
    public $programName;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'branch_id', 'program_id'], 'integer'],
            [['branchName', 'programName'], 'safe'],
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
        $query = PosBranchProgram::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->setSort([
            'attributes' => [
                'branchName' => [
                    'asc' => ['pos_branch.title' => SORT_ASC],
                    'desc' => ['pos_branch.title' => SORT_DESC],
                ],
                'programName' => [
                    'asc' => ['pos_program.title' => SORT_ASC],
                    'desc' => ['pos_program.title' => SORT_DESC],
                ]
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
            'program_id' => $this->program_id,
        ]);

        if($params){
            $query->joinWith(['branch' => function ($q) {
                if($this->branchName!=""){
                $q->where('pos_branch.title LIKE "%' . $this->branchName . '%"');
                }
            }]);
        
        
            $query->joinWith(['program' => function ($q) {
                if($this->programName!=""){
                $q->where('pos_program.title LIKE "%' . $this->programName . '%"');
                 }
            }]);
        }

        return $dataProvider;
    }
}
