<?php

namespace common\modules\pos\models;

use Yii;

/**
 * This is the model class for table "pos_discount_type".
 *
 * @property int $id
 * @property string $title
 *
 * @property PosDiscount[] $posDiscounts
 */
class PosDiscountType extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pos_discount_type';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'description'], 'required'],
            [['title', 'description'], 'string', 'max' => 100],
            [['title'], 'unique', 'message' => 'The value is existing already'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'description' => 'Description',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPosDiscounts()
    {
        return $this->hasMany(PosDiscount::className(), ['discount_type_id' => 'id']);
    }
}
