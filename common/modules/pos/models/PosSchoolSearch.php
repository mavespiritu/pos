<?php

namespace common\modules\pos\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\modules\pos\models\PosSchool;

/**
 * PosSchoolSearch represents the model behind the search form of `common\modules\pos\models\PosSchool`.
 */
class PosSchoolSearch extends PosSchool
{
    public $branchName;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'branch_id'], 'integer'],
            [['title', 'address', 'branchName'], 'safe'],
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
        $query = Yii::$app->user->identity->userinfo->BRANCH_C != "" ? PosSchool::find()
        ->joinWith('branch')
        ->andWhere(['pos_branch.id' => Yii::$app->user->identity->userinfo->BRANCH_C]) : 
        PosSchool::find()
        ->joinWith('branch');

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
                'title',
                'address',
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
        ]);

        $query->andFilterWhere(['like', 'pos_school.title', $this->title])
            ->andFilterWhere(['like', 'address', $this->address])
            ->andFilterWhere(['like', 'pos_branch.title', $this->branchName]);

        return $dataProvider;
    }
}
