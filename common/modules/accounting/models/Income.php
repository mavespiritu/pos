<?php

namespace common\modules\accounting\models;

use Yii;

/**
 * This is the model class for table "accounting_income".
 *
 * @property int $id
 * @property int $income_type_id
 * @property int $branch_id
 * @property int $income_id
 * @property string $amount_type
 * @property string $datetime
 *
 * @property AccountingBranch $branch
 * @property AccountingIncomeType $incomeType
 */
class Income extends \yii\db\ActiveRecord
{
    public $dateNow;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'accounting_income';
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
                return $('#income-amount_type').val() != 'Cash';
            }"],
            [['amount_type'], 'required'],
            [['income_type_id', 'branch_id', 'income_id'], 'integer'],
            [['amount_type'], 'string'],
            [['datetime', 'dateNow'], 'safe'],
            [['branch_id'], 'exist', 'skipOnError' => true, 'targetClass' => Branch::className(), 'targetAttribute' => ['branch_id' => 'id']],
            [['program_id'], 'exist', 'skipOnError' => true, 'targetClass' => Program::className(), 'targetAttribute' => ['program_id' => 'id']],
            [['income_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => IncomeType::className(), 'targetAttribute' => ['income_type_id' => 'id']],
            [['datetime'], 'required', 'when' => function ($model) {
                    return $model->dateNow == '0';
                }, 'whenClient' => "function (attribute, value) {
                return $('#income-datenow').val() == '0';
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
            'income_type_id' => 'Income Type ID',
            'branch_id' => 'Branch ID',
            'program_id' => 'Program ID',
            'income_id' => 'Income ID',
            'amount_type' => 'Received amount as',
            'datetime' => 'Date of Transaction',
            'transaction_number' => 'Transaction Number',
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
    public function getIncomeType()
    {
        return $this->hasOne(IncomeType::className(), ['id' => 'income_type_id']);
    }
}
