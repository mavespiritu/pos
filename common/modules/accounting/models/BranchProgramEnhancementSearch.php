<?php

namespace common\modules\accounting\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\modules\accounting\models\BranchProgramEnhancement;

/**
 * BranchProgramEnhancementSearch represents the model behind the search form of `common\modules\accounting\models\BranchProgramEnhancement`.
 */
class BranchProgramEnhancementSearch extends BranchProgramEnhancement
{
    public $branchProgramName;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'branch_program_id'], 'integer'],
            [['amount'], 'number'],
            [['branchProgramName'], 'safe'],
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
        $query = BranchProgramEnhancement::find()
                ->joinWith('branchProgram')
                ->joinWith('branchProgram.branch')
                ->joinWith('branchProgram.program')
                ->where(['accounting_branch.id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                ;

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->setSort([
            'attributes' => [
                'branchProgramName' => [
                    'asc' => ['concat(accounting_branch.name," - ",accounting_program.name)' => SORT_ASC],
                    'desc' => ['concat(accounting_branch.name," - ",accounting_program.name)' => SORT_DESC],
                ],
                'amount'
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
            'amount' => $this->amount,
        ]);

        $query->andFilterWhere(['like', 'concat(accounting_branch.name," - ",accounting_program.name)', $this->branchProgramName]);

        return $dataProvider;
    }
}
