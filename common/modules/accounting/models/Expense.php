<?php

namespace common\modules\accounting\models;

use Yii;

/**
 * This is the model class for table "accounting_expense".
 *
 * @property int $id
 * @property int $expense_type_id
 * @property int $branch_id
 * @property int $expense_id
 * @property string $amount_type
 * @property string $datetime
 *
 * @property AccountingBranch $branch
 * @property AccountingExpenseType $expenseType
 */
class Expense extends \yii\db\ActiveRecord
{
    public $dateNow;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'accounting_expense';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['transaction_number'], 'required', 'when' => function ($model) {
                    return $model->amount_type != 'Cash';
                }, 'whenClient' => "function (attribute, value) {
                return $('#expense-amount_type').val() == value;
            }"],
            [['amount_type', 'datetime', 'season_id'], 'required'],
            [['expense_type_id', 'branch_id', 'program_id', 'expense_id'], 'integer'],
            [['amount_type'], 'string'],
            [['datetime', 'dateNow'], 'safe'],
            [['branch_id'], 'exist', 'skipOnError' => true, 'targetClass' => Branch::className(), 'targetAttribute' => ['branch_id' => 'id']],
            [['program_id'], 'exist', 'skipOnError' => true, 'targetClass' => Program::className(), 'targetAttribute' => ['program_id' => 'id']],
            [['season_id'], 'exist', 'skipOnError' => true, 'targetClass' => Season::className(), 'targetAttribute' => ['season_id' => 'id']],
            [['expense_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => ExpenseType::className(), 'targetAttribute' => ['expense_type_id' => 'id']],
            [['datetime'], 'required', 'when' => function ($model) {
                    return $model->dateNow == '0';
                }, 'whenClient' => "function (attribute, value) {
                return $('#expense-datenow').val() == '0';
            }"],
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
            'expense_type_id' => 'Expense Type ID',
            'branch_id' => 'Branch ID',
            'program_id' => 'Program ID',
            'season_id' => 'Season',
            'expense_id' => 'Expense ID',
            'amount_type' => 'Spend amount from',
            'transaction_number' => 'Transaction Number',
            'datetime' => 'Date of Transaction',
            'dateNow' => 'Date'
        ];
    }

    public function getHiddenFormTokenField() {
        $token = \Yii::$app->getSecurity()->generateRandomString();
        $token = str_replace('+', '.', base64_encode($token));

        \Yii::$app->session->set(\Yii::$app->params['form_token_param'], $token);;
        return Html::hiddenInput(\Yii::$app->params['form_token_param'], $token);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBranch()
    {
        return $this->hasOne(Branch::className(), ['id' => 'branch_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProgram()
    {
        return $this->hasOne(Program::className(), ['id' => 'program_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSeason()
    {
        return $this->hasOne(Season::className(), ['id' => 'season_id']);
    }

    public function getSeasonName()
    {
        return $this->season ? $this->season->seasonName : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getExpenseType()
    {
        return $this->hasOne(ExpenseType::className(), ['id' => 'expense_type_id']);
    }
}
