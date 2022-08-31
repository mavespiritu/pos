<?php

namespace common\modules\accounting\models;

use Yii;

/**
 * This is the model class for table "accounting_discount".
 *
 * @property int $id
 * @property int $discount_type_id
 * @property int $season_id
 * @property int $student_id
 * @property string $amount
 * @property string $remarks
 *
 * @property AccountingDiscountType $discountType
 * @property AccountingSeason $season
 * @property AccountingStudent $student
 * @property AccountingStudentTuition[] $accountingStudentTuitions
 */
class Discount extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'accounting_discount';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['amount'], 'required', 'when' => function ($model) {
                    return $model->discount_type_id != '';
                }, 'whenClient' => "function (attribute, value) {
                return $('#discount-discount_type_id').val() != '';
            }"],
            [['code_number'], 'required', 'when' => function ($model) {
                    return $model->discount_type_id == 5;
                }, 'whenClient' => "function (attribute, value) {
                return $('#discount-discount_type_id').val() == 5;
            }"],
            [['discount_type_id', 'season_id', 'student_id'], 'integer'],
            [['amount'], 'number'],
            [['remarks'], 'string'],
            [['code_number'], 'unique', 'message' => 'The code number has already been taken.'],
            [['discount_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => DiscountType::className(), 'targetAttribute' => ['discount_type_id' => 'id']],
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
            'discount_type_id' => 'Discount Type',
            'season_id' => 'Season',
            'student_id' => 'Student',
            'code_number' => 'Code Number',
            'amount' => 'Amount',
            'remarks' => 'Remarks',
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
    public function getDiscountType()
    {
        return $this->hasOne(DiscountType::className(), ['id' => 'discount_type_id']);
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
        return $this->hasMany(StudentTuition::className(), ['discount_id' => 'id']);
    }
}
