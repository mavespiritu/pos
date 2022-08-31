<?php

namespace common\modules\pos\models;

use Yii;

/**
 * This is the model class for table "pos_income".
 *
 * @property int $id
 * @property int $season_id
 * @property int $official_receipt_id
 * @property string $ar_number
 * @property string $invoice_date
 * @property string $payment_due
 * @property string $datetime
 *
 * @property PosOfficialReceipt $officialReceipt
 * @property PosSeason $season
 * @property PosIncomeItem[] $posIncomeItems
 */
class PosIncome extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pos_income';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['official_receipt_id', 'payment_due', 'season_id', 'customer_id', 'account_id'], 'required'],
            [['season_id', 'customer_id', 'account_id', 'amount_type_id'], 'integer'],
            [['invoice_date', 'payment_due', 'status', 'datetime', 'official_receipt_id'], 'safe'],
            [['ar_number'], 'string', 'max' => 100],
            [['season_id'], 'exist', 'skipOnError' => true, 'targetClass' => PosSeason::className(), 'targetAttribute' => ['season_id' => 'id']],
            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => PosCustomer::className(), 'targetAttribute' => ['customer_id' => 'id']],
            [['account_id'], 'exist', 'skipOnError' => true, 'targetClass' => PosAccount::className(), 'targetAttribute' => ['account_id' => 'id']],
            [['amount_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => PosAmountType::className(), 'targetAttribute' => ['amount_type_id' => 'id']],
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
            'customer_id' => 'Customer',
            'official_receipt_id' => 'Official Receipt No.',
            'account_id' => 'Account',
            'amount_type_id' => 'Amount Type',
            'ar_number' => 'Acknowledgement Receipt No.',
            'invoice_date' => 'Invoice Date',
            'payment_due' => 'Payment Due',
            'datetime' => 'Datetime',
            'status' => 'Status',
            'customerName' => 'Customer',
            'seasonName' => 'Season',
            'productName' => 'Product',
            'amount' => 'Total Amount',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSeason()
    {
        return $this->hasOne(PosSeason::className(), ['id' => 'season_id']);
    }

    public function getSeasonName()
    {
        return $this->season ? $this->season->branchProgramName.' - SEASON '.$this->season->title : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(PosCustomer::className(), ['id' => 'customer_id']);
    }

    public function getCustomerName()
    {
        return $this->customer ? $this->customer->fullName : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAmountType()
    {
        return $this->hasOne(PosAmountType::className(), ['id' => 'amount_type_id']);
    }

    public function getAmountTypeName()
    {
        return $this->amountType ? $this->amountType->title : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(PosAmountTyoe::className(), ['id' => 'account_id']);
    }

    public function getAccountName()
    {
        return $this->account ? $this->account->title : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIncomeItem()
    {
        return $this->hasOne(PosIncomeItem::className(), ['income_id' => 'id']);
    }

    public function getProductName()
    {
        return $this->incomeItem ? $this->incomeItem->product->title.' - '.$this->incomeItem->product->amount : '';
    }

    public function getAmount()
    {
        return $this->incomeItem ? ($this->incomeItem->quantity * $this->incomeItem->amount) : '';
    }
}
