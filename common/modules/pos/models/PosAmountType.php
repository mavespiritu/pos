<?php

namespace common\modules\pos\models;

use Yii;

/**
 * This is the model class for table "pos_amount_type".
 *
 * @property int $id
 * @property string $title
 *
 * @property PosBeginningAmount[] $posBeginningAmounts
 * @property PosExpenseItem[] $posExpenseItems
 * @property PosIncomeItem[] $posIncomeItems
 */
class PosAmountType extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pos_amount_type';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'description', 'type'], 'required'],
            [['title', 'description', 'type'], 'string', 'max' => 100],
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
            'type' => 'Type',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPosBeginningAmounts()
    {
        return $this->hasMany(PosBeginningAmount::className(), ['amount_type_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPosExpenseItems()
    {
        return $this->hasMany(PosExpenseItem::className(), ['amount_type_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPosIncomeItems()
    {
        return $this->hasMany(PosIncomeItem::className(), ['amount_type_id' => 'id']);
    }
}
