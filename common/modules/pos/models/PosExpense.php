<?php

namespace common\modules\pos\models;

use Yii;

/**
 * This is the model class for table "pos_expense".
 *
 * @property int $id
 * @property int $season_id
 * @property int $vendor_id
 * @property int $account_id
 * @property int $amount_type_id
 * @property string $transaction_no
 * @property string $expense_date
 * @property string $datetime
 * @property string $status
 *
 * @property PosAccount $account
 * @property PosAmountType $amountType
 * @property PosSeason $season
 * @property PosVendor $vendor
 * @property PosExpenseItem[] $posExpenseItems
 */
class PosExpense extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pos_expense';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['season_id', 'vendor_id', 'account_id', 'amount_type_id', 'voucher_no'], 'required'],
            [['season_id', 'vendor_id', 'account_id', 'amount_type_id'], 'integer'],
            [['expense_date', 'datetime'], 'safe'],
            [['status'], 'string'],
            [['transaction_no'], 'string', 'max' => 100],
            [['account_id'], 'exist', 'skipOnError' => true, 'targetClass' => PosAccount::className(), 'targetAttribute' => ['account_id' => 'id']],
            [['amount_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => PosAmountType::className(), 'targetAttribute' => ['amount_type_id' => 'id']],
            [['season_id'], 'exist', 'skipOnError' => true, 'targetClass' => PosSeason::className(), 'targetAttribute' => ['season_id' => 'id']],
            [['vendor_id'], 'exist', 'skipOnError' => true, 'targetClass' => PosVendor::className(), 'targetAttribute' => ['vendor_id' => 'id']],
            [['transaction_no'], 'required', 'when' => function ($model) {
                    return $model->amount_type_id != 1;
                }, 'whenClient' => "function (attribute, value) {
                return $('#posexpense-amount_type_id').val() != 1;
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
            'season_id' => 'Season',
            'seasonName' => 'Season',
            'vendor_id' => 'Vendor',
            'vendorName' => 'Vendor',
            'account_id' => 'Account',
            'accountName' => 'Account',
            'amount_type_id' => 'Amount Type',
            'amountTypeName' => 'Amount Type',
            'voucher_no' => 'Voucher No.',
            'transaction_no' => 'Transaction No.',
            'expense_date' => 'Expense Date',
            'datetime' => 'Datetime',
            'status' => 'Status',
            'totalAmount' => 'Total Amount',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(PosAccount::className(), ['id' => 'account_id']);
    }

    public function getAccountName()
    {
        return $this->account ? $this->account->title : '';
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
    public function getSeason()
    {
        return $this->hasOne(PosSeason::className(), ['id' => 'season_id']);
    }

    public function getSeasonName()
    {
        return $this->season ? $this->season->seasonName : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVendor()
    {
        return $this->hasOne(PosVendor::className(), ['id' => 'vendor_id']);
    }

    public function getVendorName()
    {
        return $this->vendor ? $this->vendor->title : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getExpenseItems()
    {
        return $this->hasMany(PosExpenseItem::className(), ['expense_id' => 'id']);
    }

    public function getTotalAmount()
    {
        $items = $this->expenseItems;
        $total = 0;

        if($items)
        {
            foreach($items as $item)
            {
                $total += ($item->quantity * $item->amount);
            }
        }

        return $total;
    }
}
