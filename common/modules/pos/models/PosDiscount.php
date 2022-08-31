<?php

namespace common\modules\pos\models;

use Yii;

/**
 * This is the model class for table "pos_discount".
 *
 * @property int $id
 * @property int $enrolment_id
 * @property int $discount_type_id
 * @property string $description
 * @property double $amount
 *
 * @property PosDiscountType $discountType
 * @property PosEnrolment $enrolment
 */
class PosDiscount extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pos_discount';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['enrolment_id', 'discount_type_id'], 'integer'],
            [['description', 'code_number'], 'string'],
            [['amount'], 'number'],
            [['discount_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => PosDiscountType::className(), 'targetAttribute' => ['discount_type_id' => 'id']],
            [['enrolment_id'], 'exist', 'skipOnError' => true, 'targetClass' => PosEnrolment::className(), 'targetAttribute' => ['enrolment_id' => 'id']],
            [['amount'], 'required', 'when' => function ($model) {
                    return $model->discount_type_id != '';
                }, 'whenClient' => "function (attribute, value) {
                return $('#posdiscount-discount_type_id').val() != '';
            }"],
            [['code_number'], 'required', 'when' => function ($model) {
                    return $model->discount_type_id == 5;
                }, 'whenClient' => "function (attribute, value) {
                return $('#posdiscount-discount_type_id').val() == 5;
            }"],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'enrolment_id' => 'Enrolment',
            'code_number' => 'GC Code',
            'discount_type_id' => 'Discount Type',
            'description' => 'Description',
            'amount' => 'Amount',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDiscountType()
    {
        return $this->hasOne(PosDiscountType::className(), ['id' => 'discount_type_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEnrolment()
    {
        return $this->hasOne(PosEnrolment::className(), ['id' => 'enrolment_id']);
    }
}
