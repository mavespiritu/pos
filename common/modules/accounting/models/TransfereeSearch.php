<?php

namespace common\modules\accounting\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\modules\accounting\models\Transferee;

/**
 * TransfereeSearch represents the model behind the search form of `common\modules\accounting\models\Transferee`.
 */
class TransfereeSearch extends Transferee
{
    public $fromBranchName;
    public $fromSeasonName;
    public $toBranchName;
    public $toSeasonName;
    public $studentName;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'student_id', 'from_branch_id', 'from_season_id', 'to_branch_id', 'to_season_id'], 'integer'],
            [['fromBranchName', 'fromSeasonName', 'toBranchName', 'toSeasonName', 'studentName'], 'safe'],
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
        $query = Transferee::find()
                ->joinWith('fromBranch')
                ->joinWith('fromSeason')
                ->joinWith('fromProgram')
                ->joinWith('toBranch')
                ->joinWith('toSeason')
                ->joinWith('toProgram')
                ->joinWith('student')
                ->joinWith('toEnroleeType')
                ->where(['is', 'accounting_student_enrolee_type.student_id', null])
                ->andWhere(['is', 'accounting_student_enrolee_type.season_id', null])
                ->andWhere(['b2.id' => Yii::$app->user->identity->userinfo->BRANCH_C])
        ;

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->setSort([
            'attributes' => [
                'fromSeasonName' => [
                    'asc' => ['concat(b1.name," - ",p1.name," - SEASON ",s1.name)' => SORT_ASC],
                    'desc' => ['concat(b1.name," - ",p1.name," - SEASON ",s1.name)' => SORT_DESC],
                ],
                'toSeasonName' => [
                    'asc' => ['concat(b2.name," - ",p2.name," - SEASON ",s2.name)' => SORT_ASC],
                    'desc' => ['concat(b2.name," - ",p2.name," - SEASON ",s2.name)' => SORT_DESC],
                ],
                'studentName' => [
                    'asc' => ['concat(accounting_student.id_number," - ",accounting_student.first_name," ",accounting_student.middle_name," ",accounting_student.last_name)' => SORT_ASC],
                    'desc' => ['concat(accounting_student.id_number," - ",accounting_student.first_name," ",accounting_student.middle_name," ",accounting_student.last_name)' => SORT_DESC],
                ],
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
            'student_id' => $this->student_id,
            'from_branch_id' => $this->from_branch_id,
            'from_season_id' => $this->from_season_id,
            'to_branch_id' => $this->to_branch_id,
            'to_season_id' => $this->to_season_id,
        ]);

        $query
              ->andFilterWhere(['like', 'concat(b1.name," - ",p1.name," - SEASON ",s1.name)', $this->fromSeasonName])
              ->andFilterWhere(['like', 'concat(b2.name," - ",p2.name," - SEASON ",s2.name)', $this->toSeasonName])
              ->andFilterWhere(['like', 'concat(accounting_student.id_number," - ",accounting_student.first_name," ",accounting_student.middle_name," ",accounting_student.last_name)', $this->studentName])
        ;

        return $dataProvider;
    }
}
