<?php

namespace common\modules\pos\models;

use Yii;

/**
 * This is the model class for table "pos_account".
 *
 * @property int $id
 * @property string $title
 * @property string $description
 *
 * @property PosExpense[] $posExpenses
 * @property PosExpenseItem[] $posExpenseItems
 * @property PosIncomeItem[] $posIncomeItems
 */
class PosAccount extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pos_account';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'description'], 'required'],
            [['title'], 'unique', 'message' => 'The value is existing already'],
            [['description'], 'string'],
            [['title'], 'string', 'max' => 200],
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
    public function getPosExpenses()
    {
        return $this->hasMany(PosExpense::className(), ['account_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPosExpenseItems()
    {
        return $this->hasMany(PosExpenseItem::className(), ['account_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPosIncomeItems()
    {
        return $this->hasMany(PosIncomeItem::className(), ['account_id' => 'id']);
    }
}
