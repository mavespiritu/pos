<?php

namespace common\modules\accounting\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\modules\accounting\models\Season;
use yii\helpers\ArrayHelper;
/**
 * SeasonSearch represents the model behind the search form of `common\modules\accounting\models\Season`.
 */
class SeasonSearch extends Season
{
    public $branchProgramName;
    public $seasonName;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'branch_program_id', 'no_of_pieces'], 'integer'],
            [['name', 'start_date', 'end_date', 'or_start','branchProgramName', 'seasonName'], 'safe'],
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
        $user_info = Yii::$app->user->identity->userinfo;
        $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
        $rolenames =  ArrayHelper::map($roles, 'name','name');

        $access = AccessProgram::findOne(['user_id' => Yii::$app->user->identity->userinfo->user_id]);

        if(in_array('TopManagement',$rolenames)){
            $query = $access ? $access->branch_program_id!= '' ? Season::find()
                ->joinWith('branchProgram')
                ->joinWith('branchProgram.branch')
                ->joinWith('branchProgram.program')
                ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id]) :
                 Season::find()
                ->joinWith('branchProgram')
                ->joinWith('branchProgram.branch')
                ->joinWith('branchProgram.program') :
                 Season::find()
                ->joinWith('branchProgram')
                ->joinWith('branchProgram.branch')
                ->joinWith('branchProgram.program')
                ;
        }else{
            $query = $access ? $access->branch_program_id!= '' ? Season::find()
                ->joinWith('branchProgram')
                ->joinWith('branchProgram.branch')
                ->joinWith('branchProgram.program')
                ->andWhere(['accounting_season.branch_program_id' => $access->branch_program_id])
                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C]) :
                 Season::find()
                ->joinWith('branchProgram')
                ->joinWith('branchProgram.branch')
                ->joinWith('branchProgram.program')
                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C]) :
                 Season::find()
                ->joinWith('branchProgram')
                ->joinWith('branchProgram.branch')
                ->joinWith('branchProgram.program')
                ->andWhere(['accounting_branch_program.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                ;
        }

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
                'seasonName' => [
                    'asc' => ['concat(accounting_branch.name," - ",accounting_program.name," SEASON ",accounting_season.name)' => SORT_ASC],
                    'desc' => ['concat(accounting_branch.name," - ",accounting_program.name," SEASON ",accounting_season.name)' => SORT_DESC],
                ],
                'accounting_season.name',
                'start_date',
                'end_date',
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
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'or_start' => $this->or_start,
            'no_of_pieces' => $this->no_of_pieces,
        ]);

        $query->andFilterWhere([
            'id' => $this->id,
            'accounting_season.name' => $this->name,
        ]);

        $query->andFilterWhere(['like', 'concat(accounting_branch.name," - ",accounting_program.name)', $this->branchProgramName])
              ->andFilterWhere(['like', 'concat(accounting_branch.name," - ",accounting_program.name," SEASON ",accounting_season.name)', $this->seasonName]);

        return $dataProvider;
    }
}
