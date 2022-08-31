<?php

namespace common\modules\accounting\models;

use Yii;

/**
 * This is the model class for table "accounting_income_enrolment".
 *
 * @property int $id
 * @property int $season_id
 * @property string $or
 * @property int $code_id
 * @property int $student_id
 * @property string $amount
 *
 * @property AccountingSeason $season
 * @property AccountingIncomeCode $code
 * @property AccountingStudent $student
 */
class Enrolment extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'accounting_income_enrolment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['season_id', 'code_id', 'student_id'], 'integer'],
            [['amount'], 'number'],
            [['or'], 'string', 'max' => 250],
            [['season_id'], 'exist', 'skipOnError' => true, 'targetClass' => Season::className(), 'targetAttribute' => ['season_id' => 'id']],
            [['code_id'], 'exist', 'skipOnError' => true, 'targetClass' => IncomeCode::className(), 'targetAttribute' => ['code_id' => 'id']],
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
            'season_id' => 'Season ID',
            'or' => 'Or',
            'code_id' => 'Code ID',
            'student_id' => 'Student ID',
            'amount' => 'Amount',
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
    public function getIncomeCode()
    {
        return $this->hasOne(IncomeCode::className(), ['id' => 'code_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStudent()
    {
        return $this->hasOne(Student::className(), ['id' => 'student_id']);
    }
}
