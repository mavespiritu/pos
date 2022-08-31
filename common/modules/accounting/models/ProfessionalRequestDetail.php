<?php

namespace common\modules\accounting\models;

use Yii;

/**
 * This is the model class for table "accounting_professional_request_detail".
 *
 * @property int $id
 * @property int $professional_request_id
 * @property string $date
 * @property double $number_of_hours
 * @property int $branch_program_id
 * @property int $school_id
 * @property string $concept
 * @property string $remarks
 *
 * @property AccountingProfessionalRequest $professionalRequest
 */
class ProfessionalRequestDetail extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'accounting_professional_request_detail';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['date', 'number_of_hours', 'concept', 'remarks'], 'required'],
            [['professional_request_id', 'branch_program_id', 'school_id'], 'integer'],
            [['date'], 'safe'],
            [['number_of_hours'], 'number'],
            [['concept', 'remarks'], 'string'],
            [['professional_request_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProfessionalRequest::className(), 'targetAttribute' => ['professional_request_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'professional_request_id' => 'Professional Request ID',
            'date' => 'Date',
            'number_of_hours' => 'Number Of Hours',
            'branch_program_id' => 'Branch Program',
            'branchProgramName' => 'Branch Program',
            'school_id' => 'School',
            'schoolName' => 'School',
            'concept' => 'Concept',
            'remarks' => 'Remarks',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProfessionalRequest()
    {
        return $this->hasOne(ProfessionalRequest::className(), ['id' => 'professional_request_id']);
    }

    public function getBranchProgram()
    {
        return $this->hasOne(BranchProgram::className(), ['id' => 'branch_program_id']);
    }

    public function getBranchProgramName()
    {
        return $this->branchProgram ? $this->branchProgram->branchProgramName : '';
    }

    public function getSchool()
    {
        return $this->hasOne(School::className(), ['id' => 'school_id']);
    }

    public function getSchoolName()
    {
        return $this->school ? $this->school->name : '';
    }
}
