<?php

namespace common\modules\accounting\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\modules\accounting\models\Dropout;
use yii\helpers\ArrayHelper;
/**
 * DropoutSearch represents the model behind the search form of `common\modules\accounting\models\Dropout`.
 */
class DropoutSearch extends Dropout
{
    public $seasonName;
    public $studentName;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'season_id', 'student_id'], 'integer'],
            [['drop_date', 'reason', 'authorized_by', 'seasonName', 'studentName'], 'safe'],
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

        if(in_array('TopManagement',$rolenames)){
            $query = Dropout::find()
                ->joinWith('season')
                ->joinWith('season.branchProgram')
                ->joinWith('season.branchProgram.branch')
                ->joinWith('season.branchProgram.program')
                ->joinWith('student')
                ->orderBy(['drop_date' => SORT_DESC])
                ;
        }else if(in_array('AreaManager',$rolenames)){
            $query = Dropout::find()
                ->joinWith('season')
                ->joinWith('season.branchProgram')
                ->joinWith('season.branchProgram.branch')
                ->joinWith('season.branchProgram.program')
                ->joinWith('student')
                ->where(['accounting_branch.id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                ->orderBy(['drop_date' => SORT_DESC])
                ;

        }else if(in_array('EnrolmentStaff',$rolenames)){
            $query = Dropout::find()
                ->joinWith('season')
                ->joinWith('season.branchProgram')
                ->joinWith('season.branchProgram.branch')
                ->joinWith('season.branchProgram.program')
                ->joinWith('student')
                ->where(['accounting_branch.id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                ->orderBy(['drop_date' => SORT_DESC])
                ;

        }else if(in_array('AccountingStaff',$rolenames)){
            $query = Dropout::find()
                ->joinWith('season')
                ->joinWith('season.branchProgram')
                ->joinWith('season.branchProgram.branch')
                ->joinWith('season.branchProgram.program')
                ->joinWith('student')
                ->where(['accounting_branch.id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                ->orderBy(['drop_date' => SORT_DESC])
                ;
        }else if(in_array('SchoolBased',$rolenames)){
            $query = Dropout::find()
                ->joinWith('season')
                ->joinWith('season.branchProgram')
                ->joinWith('season.branchProgram.branch')
                ->joinWith('season.branchProgram.program')
                ->joinWith('student')
                ->where(['accounting_student.school_id' => Yii::$app->user->identity->userinfo->SCHOOL_C])
                ->orderBy(['drop_date' => SORT_DESC])
                ;
        }

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
                'studentName' => [
                    'asc' => ['concat(accounting_student.id_number," - ",accounting_student.first_name," ",accounting_student.middle_name," ",accounting_student.last_name)' => SORT_ASC],
                    'desc' => ['concat(accounting_student.id_number," - ",accounting_student.first_name," ",accounting_student.middle_name," ",accounting_student.last_name)' => SORT_DESC],
                ],
                'drop_date',
                'reason',
                'authorized_by',
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
            'student_id' => $this->student_id,
            'drop_date' => $this->drop_date,
        ]);

        $query->andFilterWhere(['like', 'reason', $this->reason])
            ->andFilterWhere(['like', 'authorized_by', $this->authorized_by]);

        $query->andFilterWhere(['like', 'concat(accounting_branch.name," - ",accounting_program.name," - SEASON ",accounting_season.name)', $this->seasonName])
              ->andFilterWhere(['like', 'concat(accounting_student.id_number," - ",accounting_student.first_name," ",accounting_student.middle_name," ",accounting_student.last_name)', $this->studentName])
        ;

        return $dataProvider;
    }
}
