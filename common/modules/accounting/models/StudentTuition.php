<?php

namespace common\modules\accounting\models;

use Yii;

/**
 * This is the model class for table "accounting_student_tuition".
 *
 * @property int $id
 * @property int $season_id
 * @property int $student_id
 * @property int $package_student_id
 * @property int $discount_id
 * @property int $enhancement_id
 * @property string $datetime
 *
 * @property AccountingDiscount $discount
 * @property AccountingEnhancement $enhancement
 * @property AccountingPackageStudent $packageStudent
 * @property AccountingSeason $season
 * @property AccountingStudent $student
 */
class StudentTuition extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'accounting_student_tuition';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['season_id', 'student_id', 'package_student_id', 'discount_id', 'enhancement_id'], 'integer'],
            [['datetime'], 'safe'],
            [['discount_id'], 'exist', 'skipOnError' => true, 'targetClass' => Discount::className(), 'targetAttribute' => ['discount_id' => 'id']],
            [['enhancement_id'], 'exist', 'skipOnError' => true, 'targetClass' => Enhancement::className(), 'targetAttribute' => ['enhancement_id' => 'id']],
            [['package_student_id'], 'exist', 'skipOnError' => true, 'targetClass' => PackageStudent::className(), 'targetAttribute' => ['package_student_id' => 'id']],
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
            'season_id' => 'Season ID',
            'student_id' => 'Student ID',
            'package_student_id' => 'Package Student ID',
            'discount_id' => 'Discount ID',
            'enhancement_id' => 'Enhancement ID',
            'datetime' => 'Datetime',
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
    public function getDiscount()
    {
        return $this->hasOne(Discount::className(), ['id' => 'discount_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEnhancement()
    {
        return $this->hasOne(Enhancement::className(), ['id' => 'enhancement_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPackageStudent()
    {
        return $this->hasOne(PackageStudent::className(), ['id' => 'package_student_id']);
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
}
