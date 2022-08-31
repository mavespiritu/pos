<?php

namespace common\modules\accounting\models;

use Yii;

/**
 * This is the model class for table "accounting_expense_operating_expense".
 *
 * @property int $id
 * @property string $cv_no
 * @property string $particulars
 * @property string $staff_salary
 * @property string $cash_pf
 * @property string $rent
 * @property string $utilities
 * @property string $equipment_and_labor
 * @property string $bir_and_docs
 * @property string $marketing
 */
class OperatingExpense extends \yii\db\ActiveRecord
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
        return 'accounting_expense_operating_expense';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['seasons_id', 'frequency_id', 'date_id', 'page_id'], 'required', 'on' => 'searchOperatingExpense'],
            [['cv_no', 'particulars', 'charge_to'], 'required'],
            [['particulars'], 'string'],
            [['staff_salary', 'cash_pf', 'rent', 'utilities', 'equipment_and_labor', 'bir_and_docs', 'marketing'], 'number'],
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
            'particulars' => 'Particulars',
            'staff_salary' => 'Staff Salary',
            'cash_pf' => 'Cash PF',
            'rent' => 'Rent',
            'utilities' => 'Utilities',
            'equipment_and_labor' => 'Equipment And Labor',
            'bir_and_docs' => 'BIR And Docs',
            'marketing' => 'Marketing',
            'charge_to' => 'Charge Expenses To',
            'datetime' => 'Date of Transaction',
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
        return $this->hasOne(Expense::className(), ['expense_id' => 'id'])->where(['accounting_expense.expense_type_id' => '5']);
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
        $expense = $this->getExpense()->where(['expense_type_id' => '5'])->one();

        return  $expense ? $expense->amount_type : '';
    }

    public function getDatetime()
    {
        $expense = $this->getExpense()->where(['expense_type_id' => '5'])->one();
        
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
