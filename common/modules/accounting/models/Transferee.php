<?php

namespace common\modules\accounting\models;

use Yii;

/**
 * This is the model class for table "accounting_transferee".
 *
 * @property int $id
 * @property int $student_id
 * @property int $branch_id
 * @property int $season_id
 *
 * @property AccountingBranch $branch
 * @property AccountingSeason $season
 * @property AccountingStudent $student
 */
class Transferee extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'accounting_transferee';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['to_season_id'], 'required'],
            [['student_id', 'from_branch_id', 'from_season_id', 'to_branch_id', 'to_season_id', 'from_program_id', 'to_program_id'], 'integer'],
            [['datetime'], 'safe'],
            [['from_branch_id'], 'exist', 'skipOnError' => true, 'targetClass' => Branch::className(), 'targetAttribute' => ['from_branch_id' => 'id']],
            [['to_branch_id'], 'exist', 'skipOnError' => true, 'targetClass' => Branch::className(), 'targetAttribute' => ['to_branch_id' => 'id']],
            [['from_season_id'], 'exist', 'skipOnError' => true, 'targetClass' => Season::className(), 'targetAttribute' => ['from_season_id' => 'id']],
            [['to_season_id'], 'exist', 'skipOnError' => true, 'targetClass' => Season::className(), 'targetAttribute' => ['to_season_id' => 'id']],
            [['student_id'], 'exist', 'skipOnError' => true, 'targetClass' => Student::className(), 'targetAttribute' => ['student_id' => 'id']],
            [['from_program_id'], 'exist', 'skipOnError' => true, 'targetClass' => Program::className(), 'targetAttribute' => ['from_program_id' => 'id']],
            [['to_program_id'], 'exist', 'skipOnError' => true, 'targetClass' => Program::className(), 'targetAttribute' => ['to_program_id' => 'id']],
        ];
    }

    public function behaviors()
    {
        return [
            'bedezign\yii2\audit\AuditTrailBehavior'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'student_id' => 'Student',
            'from_branch_id' => 'From Branch',
            'fromBranchName' => 'From Branch',
            'from_season_id' => 'From Season',
            'fromSeasonName' => 'From Season',
            'to_branch_id' => 'To Branch',
            'toBranchName' => 'To Branch',
            'to_season_id' => 'To Season',
            'toSeasonName' => 'To Season',
            'from_program_id' => 'From Program',
            'fromProgramName' => 'From Program',
            'to_program_id' => 'To Program',
            'toProgramName' => 'To Program',
            'datetime' => 'Date Transferred',
        ];
    }

    public function getHiddenFormTokenField() {
        $token = \Yii::$app->getSecurity()->generateRandomString();
        $token = str_replace('+', '.', base64_encode($token));

        \Yii::$app->session->set(\Yii::$app->params['form_token_param'], $token);;
        return Html::hiddenInput(\Yii::$app->params['form_token_param'], $token);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFromBranch()
    {
        return $this->hasOne(Branch::className(), ['id' => 'from_branch_id'])->from(['b1' => Branch::tableName()]);
    }

    public function getFromBranchName()
    {
        return $this->fromBranch ? $this->fromBranch->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFromProgram()
    {
        return $this->hasOne(Program::className(), ['id' => 'from_program_id'])->from(['p1' => Program::tableName()]);
    }

    public function getFromProgramName()
    {
        return $this->fromProgram ? $this->fromProgram->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFromSeason()
    {
        return $this->hasOne(Season::className(), ['id' => 'from_season_id'])->from(['s1' => Season::tableName()]);
    }

    public function getFromSeasonName()
    {
        return $this->fromSeason ? $this->fromSeason->seasonName : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getToBranch()
    {
        return $this->hasOne(Branch::className(), ['id' => 'to_branch_id'])->from(['b2' => Branch::tableName()]);
    }

    public function getToBranchName()
    {
        return $this->toBranch ? $this->toBranch->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getToProgram()
    {
        return $this->hasOne(Program::className(), ['id' => 'to_program_id'])->from(['p2' => Program::tableName()]);
    }

    public function getToProgramName()
    {
        return $this->toProgram ? $this->toProgram->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getToSeason()
    {
        return $this->hasOne(Season::className(), ['id' => 'to_season_id'])->from(['s2' => Season::tableName()]);
    }

    public function getToSeasonName()
    {
        return $this->toSeason ? $this->toSeason->seasonName : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStudent()
    {
        return $this->hasOne(Student::className(), ['id' => 'student_id']);
    }

    public function getStudentName()
    {
        return $this->student ? $this->student->id_number.' - '.$this->student->fullname : '';
    }

    public function getToEnroleeType()
    {
        return $this->hasOne(StudentEnroleeType::className(), ['student_id' => 'student_id', 'season_id' => 'to_season_id']);
    }
}
