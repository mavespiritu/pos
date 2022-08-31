<?php

namespace common\modules\accounting\models;

use Yii;

/**
 * This is the model class for table "accounting_expense_photocopy_expense".
 *
 * @property int $id
 * @property string $cv_no
 * @property string $subject
 * @property int $no_of_pages
 * @property int $no_of_pieces
 * @property string $amount_per_page
 * @property string $total_amount
 */
class PhotocopyExpense extends \yii\db\ActiveRecord
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
        return 'accounting_expense_photocopy_expense';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['seasons_id', 'frequency_id', 'date_id', 'page_id'], 'required', 'on' => 'searchPhotocopyExpense'],
            [['cv_no', 'subject', 'total_amount', 'charge_to', 'amount_per_page', 'no_of_pages'], 'required'],
            [['subject'], 'string'],
            [['no_of_pages', 'no_of_pieces'], 'integer'],
            [['amount_per_page', 'total_amount'], 'number'],
            [['cv_no'], 'string', 'max' => 250],
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
            'cv_no' => 'CV No.',
            'subject' => 'Subject',
            'no_of_pages' => 'No Of Pages',
            'no_of_pieces' => 'No Of Pieces',
            'amount_per_page' => 'Amount Per Page',
            'total_amount' => 'Total Amount',
            'datetime' => 'Date of Transaction',
            'charge_to' => 'Charge Expenses To',
            'seasonName' => 'Season',
            'frequency_id' => 'Frequency',
            'date_id' => 'Date',
            'page_id' => 'Page',
            'seasons_id' => 'Season',
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
        return $this->hasOne(Expense::className(), ['expense_id' => 'id'])->where(['accounting_expense.expense_type_id' => '2']);
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
        $expense = $this->getExpense()->where(['expense_type_id' => '2'])->one();

        return  $expense ? $expense->amount_type : '';
    }

    public function getDatetime()
    {
        $expense = $this->getExpense()->where(['expense_type_id' => '2'])->one();
        
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
