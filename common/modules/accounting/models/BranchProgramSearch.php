<?php

namespace common\modules\accounting\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\modules\accounting\models\BranchProgram;

/**
 * BranchProgramSearch represents the model behind the search form of `common\modules\accounting\models\BranchProgram`.
 */
class BranchProgramSearch extends BranchProgram
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
        $query = BranchProgram::find();

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
                'programName' => [
                    'asc' => ['accounting_program.name' => SORT_ASC],
                    'desc' => ['accounting_program.name' => SORT_DESC],
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

        $query->andFilterWhere(['like', 'accounting_program.name', $this->programName])
              ->andFilterWhere(['like', 'accounting_branch.name', $this->branchName]);

        if($params){
            $query->joinWith(['branch' => function ($q) {
                if($this->branchName!=""){
                $q->where('accounting_branch.name LIKE "%' . $this->branchName . '%"');
                }
            }]);
        
        
            $query->joinWith(['program' => function ($q) {
                if($this->programName!=""){
                $q->where('accounting_program.name LIKE "%' . $this->programName . '%"');
                 }
            }]);
        }

        return $dataProvider;
    }
}
