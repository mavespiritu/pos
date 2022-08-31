<?php

namespace common\modules\accounting\models;

use Yii;

/**
 * This is the model class for table "accounting_expense_bank_deposit".
 *
 * @property int $id
 * @property string $bank
 * @property string $account_no
 * @property string $transaction_no
 * @property string $deposited_by
 * @property string $remarks
 * @property string $amount
 */
class BankDeposit extends \yii\db\ActiveRecord
{
    public $seasons_id;
    public $frequency_id;
    public $page_id;
    public $date_id;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'accounting_expense_bank_deposit';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['seasons_id', 'frequency_id', 'date_id', 'page_id'], 'required', 'on' => 'searchBankDeposit'],
            [['bank', 'account_no', 'transaction_no', 'deposited_by', 'amount'], 'required'],
            [['bank', 'remarks'], 'string'],
            [['amount'], 'number'],
            [['account_no', 'transaction_no', 'deposited_by'], 'string', 'max' => 250],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'bank' => 'Bank',
            'account_no' => 'Account No.',
            'transaction_no' => 'Transaction No.',
            'deposited_by' => 'Deposited By',
            'remarks' => 'Remarks',
            'amount' => 'Amount',
            'seasonName' => 'Season',
            'frequency_id' => 'Frequency',
            'date_id' => 'Date',
            'page_id' => 'Page',
            'seasons_id' => 'Season',
        ];
    }

    public function behaviors()
    {
        return [
            'bedezign\yii2\audit\AuditTrailBehavior'
        ];
    }

    public function getHiddenFormTokenField() {
        $token = \Yii::$app->getSecurity()->generateRandomString();
        $token = str_replace('+', '.', base64_encode($token));

        \Yii::$app->session->set(\Yii::$app->params['form_token_param'], $token);;
        return Html::hiddenInput(\Yii::$app->params['form_token_param'], $token);
    }

    public function getExpense()
    {
        return $this->hasOne(Expense::className(), ['expense_id' => 'id'])->where(['accounting_expense.expense_type_id' => '4']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSeason()
    {
        return $this->hasOne(Season::className(), ['id' => 'season_id'])->via('expense');        
    }

    public function getSeasonName()
    {
        return $this->season ? $this->season->seasonName : '';
    }

    public function getAmountType()
    {
        $expense = $this->getExpense()->where(['expense_type_id' => '4'])->one();

        return  $expense ? $expense->amount_type : '';
    }

    public function getDatetime()
    {
        $expense = $this->getExpense()->where(['expense_type_id' => '4'])->one();
        
        return  $expense ? $expense->datetime : '';
    }

    public static function getTotal($provider, $fieldName)
    {
        $total = 0;

        foreach ($provider as $item) {
            $total += $item[$fieldName];
        }

        return number_format($total, 2);
    }
}
