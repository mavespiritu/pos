<?php

namespace common\modules\pos\models;

use Yii;

/**
 * This is the model class for table "pos_income_item".
 *
 * @property int $id
 * @property int $income_id
 * @property int $season_id
 * @property int $product_id
 * @property int $income_type_id
 * @property int $account_id
 * @property int $customer_id
 * @property string $description
 * @property int $quantity
 * @property double $amount
 * @property int $amount_type_id
 * @property string $transaction_no
 * @property string $datetime
 *
 * @property PosDiscount[] $posDiscounts
 * @property PosAccount $account
 * @property PosAmountType $amountType
 * @property PosCustomer $customer
 * @property PosIncome $income
 * @property PosIncomeType $incomeType
 * @property PosProduct $product
 * @property PosSeason $season
 */
class PosIncomeItem extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pos_income_item';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['product_id', 'amount', 'amount_type_id'], 'required'],
            [['income_id', 'season_id', 'product_id', 'income_type_id', 'account_id', 'customer_id', 'quantity', 'amount_type_id'], 'integer'],
            [['description', 'transaction_no'], 'string'],
            [['amount'], 'number'],
            [['datetime'], 'safe'],
            [['account_id'], 'exist', 'skipOnError' => true, 'targetClass' => PosAccount::className(), 'targetAttribute' => ['account_id' => 'id']],
            [['amount_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => PosAmountType::className(), 'targetAttribute' => ['amount_type_id' => 'id']],
            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => PosCustomer::className(), 'targetAttribute' => ['customer_id' => 'id']],
            [['income_id'], 'exist', 'skipOnError' => true, 'targetClass' => PosIncome::className(), 'targetAttribute' => ['income_id' => 'id']],
            [['income_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => PosIncomeType::className(), 'targetAttribute' => ['income_type_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => PosProduct::className(), 'targetAttribute' => ['product_id' => 'id']],
            [['season_id'], 'exist', 'skipOnError' => true, 'targetClass' => PosSeason::className(), 'targetAttribute' => ['season_id' => 'id']],
            [['transaction_no'], 'required', 'when' => function ($model) {
                    return $model->amount_type_id != 1;
                }, 'whenClient' => "function (attribute, value) {
                return $('#posincomeitem-amount_type_id').val() != 1;
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
            'income_id' => 'Income',
            'season_id' => 'Season',
            'product_id' => 'Product',
            'income_type_id' => 'Category',
            'account_id' => 'Account',
            'customer_id' => 'Customer',
            'description' => 'Description',
            'quantity' => 'Quantity',
            'amount' => 'Amount',
            'amount_type_id' => 'Amount Type',
            'transaction_no' => 'Transaction No',
            'datetime' => 'Datetime',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPosDiscounts()
    {
        return $this->hasMany(PosDiscount::className(), ['income_item_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(PosAccount::className(), ['id' => 'account_id']);
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
    public function getCustomer()
    {
        return $this->hasOne(PosCustomer::className(), ['id' => 'customer_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIncome()
    {
        return $this->hasOne(PosIncome::className(), ['id' => 'income_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIncomeType()
    {
        return $this->hasOne(PosIncomeType::className(), ['id' => 'income_type_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(PosProduct::className(), ['id' => 'product_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSeason()
    {
        return $this->hasOne(PosSeason::className(), ['id' => 'season_id']);
    }
}
