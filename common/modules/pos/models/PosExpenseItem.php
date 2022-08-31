<?php

namespace common\modules\pos\models;

use Yii;

/**
 * This is the model class for table "pos_expense_item".
 *
 * @property int $id
 * @property int $expense_id
 * @property int $season_id
 * @property int $expense_type_id
 * @property string $description
 * @property int $quantity
 * @property double $amount
 * @property int $amount_type_id
 * @property string $datetime
 *
 * @property PosAmountType $amountType
 * @property PosExpense $expense
 * @property PosExpenseType $expenseType
 * @property PosSeason $season
 */
class PosExpenseItem extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pos_expense_item';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['expense_type_id', 'quantity', 'description', 'amount'], 'required'],
            [['expense_id', 'season_id', 'expense_type_id', 'quantity', 'amount_type_id'], 'integer'],
            [['description'], 'string'],
            [['amount'], 'number'],
            [['datetime'], 'safe'],
            [['amount_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => PosAmountType::className(), 'targetAttribute' => ['amount_type_id' => 'id']],
            [['expense_id'], 'exist', 'skipOnError' => true, 'targetClass' => PosExpense::className(), 'targetAttribute' => ['expense_id' => 'id']],
            [['expense_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => PosExpenseType::className(), 'targetAttribute' => ['expense_type_id' => 'id']],
            [['season_id'], 'exist', 'skipOnError' => true, 'targetClass' => PosSeason::className(), 'targetAttribute' => ['season_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'expense_id' => 'Expense ID',
            'season_id' => 'Season ID',
            'expense_type_id' => 'Category',
            'description' => 'Description',
            'quantity' => 'Quantity',
            'amount' => 'Unit Price',
            'amount_type_id' => 'Amount Type ID',
            'datetime' => 'Datetime',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAmountType()
    {
        return $this->hasOne(PosAmountType::className(), ['id' => 'amount_type_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getExpense()
    {
        return $this->hasOne(PosExpense::className(), ['id' => 'expense_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getExpenseType()
    {
        return $this->hasOne(PosExpenseType::className(), ['id' => 'expense_type_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSeason()
    {
        return $this->hasOne(PosSeason::className(), ['id' => 'season_id']);
    }
}
