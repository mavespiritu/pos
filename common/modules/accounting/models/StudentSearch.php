<?php

namespace common\modules\accounting\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\modules\accounting\models\Student;
use yii\helpers\ArrayHelper;
/**
 * StudentSearch represents the model behind the search form of `common\modules\accounting\models\Student`.
 */
class StudentSearch extends Student
{
    public $fullName;
    public $provinceName;
    public $citymunName;
    public $schoolName;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'school_id', 'year_graduated'], 'integer'],
            [['id_number', 'first_name', 'middle_name', 'last_name', 'extension_name', 'permanent_address', 'contact_no', 'birthday', 'prc', 'email_address', 'status', 'fullName', 'provinceName', 'citymunName', 'schoolName'], 'safe'],
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
            $query = $access ? $access->branch_program_id!= '' ? Student::find()
                ->joinWith('school')
                ->joinWith('province')
                ->joinWith('citymun')
                ->leftJoin(['branchProgram' => '(SELECT
                                                    accounting_branch_program.id as branchProgramId,
                                                    accounting_student_branch_program.id as id,
                                                    accounting_student_branch_program.student_id,
                                                    accounting_student_branch_program.branch_id,
                                                    accounting_student_branch_program.program_id
                                                from
                                                    accounting_student_branch_program
                                                left join (
                                                    SELECT max(id) as id from accounting_student_branch_program group by student_id
                                                ) maxID on maxID.id = accounting_student_branch_program.id
                                                left join accounting_branch_program on accounting_branch_program.branch_id = accounting_student_branch_program.branch_id and accounting_branch_program.program_id = accounting_student_branch_program.program_id 
                                                where accounting_student_branch_program.id in (maxID.id)
                    )'], 'branchProgram.student_id = accounting_student.id')
                ->andWhere(['accounting_student.status' => 'Active'])
                ->andWhere(['branchProgram.branchProgramId' => $access->branch_program_id])
                ->orderBy(['accounting_student.id' => SORT_DESC]) :
                Student::find()
                ->joinWith('school')
                ->joinWith('province')
                ->joinWith('citymun')
                ->leftJoin(['branchProgram' => '(SELECT
                                                   accounting_branch_program.id as branchProgramId,
                                                    accounting_student_branch_program.id as id,
                                                    accounting_student_branch_program.student_id,
                                                    accounting_student_branch_program.branch_id,
                                                    accounting_student_branch_program.program_id
                                                from
                                                    accounting_student_branch_program
                                                left join (
                                                    SELECT max(id) as id from accounting_student_branch_program group by student_id
                                                ) maxID on maxID.id = accounting_student_branch_program.id
                                                left join accounting_branch_program on accounting_branch_program.branch_id = accounting_student_branch_program.branch_id and accounting_branch_program.program_id = accounting_student_branch_program.program_id 
                                                where accounting_student_branch_program.id in (maxID.id)
                    )'], 'branchProgram.student_id = accounting_student.id')
                ->andWhere(['accounting_student.status' => 'Active'])
                ->orderBy(['accounting_student.id' => SORT_DESC]) :
                Student::find()
                ->joinWith('school')
                ->joinWith('province')
                ->joinWith('citymun')
                ->leftJoin(['branchProgram' => '(SELECT
                                                   accounting_branch_program.id as branchProgramId,
                                                    accounting_student_branch_program.id as id,
                                                    accounting_student_branch_program.student_id,
                                                    accounting_student_branch_program.branch_id,
                                                    accounting_student_branch_program.program_id
                                                from
                                                    accounting_student_branch_program
                                                left join (
                                                    SELECT max(id) as id from accounting_student_branch_program group by student_id
                                                ) maxID on maxID.id = accounting_student_branch_program.id
                                                left join accounting_branch_program on accounting_branch_program.branch_id = accounting_student_branch_program.branch_id and accounting_branch_program.program_id = accounting_student_branch_program.program_id 
                                                where accounting_student_branch_program.id in (maxID.id)
                    )'], 'branchProgram.student_id = accounting_student.id')
                ->andWhere(['accounting_student.status' => 'Active'])
                ->orderBy(['accounting_student.id' => SORT_DESC])
                ;
        }else{
            $query = $access ? $access->branch_program_id!= '' ? Student::find()
                ->joinWith('school')
                ->joinWith('province')
                ->joinWith('citymun')
                ->leftJoin(['branchProgram' => '(SELECT
                                                    accounting_branch_program.id as branchProgramId,
                                                    accounting_student_branch_program.id as id,
                                                    accounting_student_branch_program.student_id,
                                                    accounting_student_branch_program.branch_id,
                                                    accounting_student_branch_program.program_id
                                                from
                                                    accounting_student_branch_program
                                                left join (
                                                    SELECT max(id) as id from accounting_student_branch_program group by student_id
                                                ) maxID on maxID.id = accounting_student_branch_program.id
                                                left join accounting_branch_program on accounting_branch_program.branch_id = accounting_student_branch_program.branch_id and accounting_branch_program.program_id = accounting_student_branch_program.program_id 
                                                where accounting_student_branch_program.id in (maxID.id)
                    )'], 'branchProgram.student_id = accounting_student.id')
                ->andWhere(['accounting_student.status' => 'Active'])
                ->andWhere(['branchProgram.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                ->andWhere(['branchProgram.branchProgramId' => $access->branch_program_id])
                ->orderBy(['accounting_student.id' => SORT_DESC]) :
                Student::find()
                ->joinWith('school')
                ->joinWith('province')
                ->joinWith('citymun')
                ->leftJoin(['branchProgram' => '(SELECT
                                                    accounting_branch_program.id as branchProgramId,
                                                    accounting_student_branch_program.id as id,
                                                    accounting_student_branch_program.student_id,
                                                    accounting_student_branch_program.branch_id,
                                                    accounting_student_branch_program.program_id
                                                from
                                                    accounting_student_branch_program
                                                left join (
                                                    SELECT max(id) as id from accounting_student_branch_program group by student_id
                                                ) maxID on maxID.id = accounting_student_branch_program.id
                                                left join accounting_branch_program on accounting_branch_program.branch_id = accounting_student_branch_program.branch_id and accounting_branch_program.program_id = accounting_student_branch_program.program_id 
                                                where accounting_student_branch_program.id in (maxID.id)
                    )'], 'branchProgram.student_id = accounting_student.id')
                ->andWhere(['accounting_student.status' => 'Active'])
                ->andWhere(['branchProgram.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                ->orderBy(['accounting_student.id' => SORT_DESC]) :
                Student::find()
                ->joinWith('school')
                ->joinWith('province')
                ->joinWith('citymun')
                ->leftJoin(['branchProgram' => '(SELECT
                                                    accounting_branch_program.id as branchProgramId,
                                                    accounting_student_branch_program.id as id,
                                                    accounting_student_branch_program.student_id,
                                                    accounting_student_branch_program.branch_id,
                                                    accounting_student_branch_program.program_id
                                                from
                                                    accounting_student_branch_program
                                                left join (
                                                    SELECT max(id) as id from accounting_student_branch_program group by student_id
                                                ) maxID on maxID.id = accounting_student_branch_program.id
                                                left join accounting_branch_program on accounting_branch_program.branch_id = accounting_student_branch_program.branch_id and accounting_branch_program.program_id = accounting_student_branch_program.program_id 
                                                where accounting_student_branch_program.id in (maxID.id)
                    )'], 'branchProgram.student_id = accounting_student.id')
                ->andWhere(['accounting_student.status' => 'Active'])
                ->andWhere(['branchProgram.branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])
                ->orderBy(['accounting_student.id' => SORT_DESC])
                ;
        }
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->setSort([
            'attributes' => [
                'id_number',
                'fullName' => [
                    'asc' => ['concat(accounting_student.id_number," - ",accounting_student.first_name," ",accounting_student.middle_name," ",accounting_student.last_name," ",accounting_student.extension_name)' => SORT_ASC],
                    'desc' => ['concat(accounting_student.id_number," - ",accounting_student.first_name," ",accounting_student.middle_name," ",accounting_student.last_name," ",accounting_student.extension_name)' => SORT_DESC],
                ],
                'provinceName' => [
                    'asc' => ['tblprovince.province_m' => SORT_ASC],
                    'desc' => ['tblprovince.province_m' => SORT_DESC],
                ],
                'citymunName' => [
                    'asc' => ['tblcitymun.citymun_m' => SORT_ASC],
                    'desc' => ['tblcitymun.citymun_m' => SORT_DESC],
                ],
                'permanent_address',
                'schoolName' => [
                    'asc' => ['accounting_school.name' => SORT_ASC],
                    'desc' => ['accounting_school.name' => SORT_DESC],
                ],
                'year_graduated',
                'contact_no',
                'email_address'
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
            'school_id' => $this->school_id,
            'year_graduated' => $this->year_graduated,
            'birthday' => $this->birthday,
        ]);

        $query->andFilterWhere(['like', 'id_number', $this->id_number])
            ->andFilterWhere(['like', 'first_name', $this->first_name])
            ->andFilterWhere(['like', 'middle_name', $this->middle_name])
            ->andFilterWhere(['like', 'last_name', $this->last_name])
            ->andFilterWhere(['like', 'extension_name', $this->extension_name])
            ->andFilterWhere(['like', 'permanent_address', $this->permanent_address])
            ->andFilterWhere(['like', 'contact_no', $this->contact_no])
            ->andFilterWhere(['like', 'prc', $this->prc])
            ->andFilterWhere(['like', 'email_address', $this->email_address])
            ->andFilterWhere(['like', 'tblprovince.province_m', $this->provinceName])
            ->andFilterWhere(['like', 'tblcitymun.citymun_m', $this->citymunName])
            ->andFilterWhere(['like', 'accounting_school.name', $this->schoolName])
            ->andFilterWhere(['like', 'concat(accounting_student.id_number," - ",accounting_student.first_name," ",accounting_student.middle_name," ",accounting_student.last_name," ",accounting_student.extension_name)', $this->fullName])
            ->andFilterWhere(['like', 'status', $this->status]);

        return $dataProvider;
    }
}
