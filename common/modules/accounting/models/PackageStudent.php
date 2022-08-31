<?php

namespace common\modules\accounting\models;

use Yii;

/**
 * This is the model class for table "accounting_package_student".
 *
 * @property int $id
 * @property int $season_id
 * @property int $student_id
 * @property int $package_id
 * @property string $amount
 *
 * @property AccountingPackage $package
 * @property AccountingSeason $season
 * @property AccountingStudent $student
 * @property AccountingStudentTuition[] $accountingStudentTuitions
 */
class PackageStudent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'accounting_package_student';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['package_id'], 'required'],
            [['season_id', 'student_id', 'package_id'], 'integer'],
            [['amount'], 'number'],
            [['package_id'], 'exist', 'skipOnError' => true, 'targetClass' => Package::className(), 'targetAttribute' => ['package_id' => 'id']],
            [['season_id'], 'exist', 'skipOnError' => true, 'targetClass' => Season::className(), 'targetAttribute' => ['season_id' => 'id']],
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
            'package_id' => 'Package',
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
    public function getPackage()
    {
        return $this->hasOne(Package::className(), ['id' => 'package_id']);
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
    public function getStudent()
    {
        return $this->hasOne(Student::className(), ['id' => 'student_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStudentTuitions()
    {
        return $this->hasMany(StudentTuition::className(), ['package_student_id' => 'id']);
    }
}
