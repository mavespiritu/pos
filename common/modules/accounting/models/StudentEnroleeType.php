<?php

namespace common\modules\accounting\models;

use Yii;

/**
 * This is the model class for table "accounting_student_enrolee_type".
 *
 * @property int $id
 * @property int $season_id
 * @property int $student_id
 * @property int $enrolee_type_id
 *
 * @property AccountingSeason $season
 * @property AccountingEnroleeType $enroleeType
 * @property AccountingStudent $student
 */
class StudentEnroleeType extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'accounting_student_enrolee_type';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['enrolee_type_id', 'season_id'], 'required'],
            [['enrolee_type_id'], 'integer'],
            [['season_id'], 'exist', 'skipOnError' => true, 'targetClass' => Season::className(), 'targetAttribute' => ['season_id' => 'id']],
            [['enrolee_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => EnroleeType::className(), 'targetAttribute' => ['enrolee_type_id' => 'id']],
            [['student_id'], 'exist', 'skipOnError' => true, 'targetClass' => Student::className(), 'targetAttribute' => ['student_id' => 'id']],
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
            'season_id' => 'Season',
            'student_id' => 'Student',
            'enrolee_type_id' => 'Enrolee Type',
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
    public function getSeason()
    {
        return $this->hasOne(Season::className(), ['id' => 'season_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEnroleeType()
    {
        return $this->hasOne(EnroleeType::className(), ['id' => 'enrolee_type_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStudent()
    {
        return $this->hasOne(Student::className(), ['id' => 'student_id']);
    }
}
