<?php

namespace common\modules\accounting\models;

use Yii;

/**
 * This is the model class for table "accounting_income_code".
 *
 * @property int $id
 * @property int $income_type_id
 * @property string $name
 * @property string $description
 *
 * @property AccountingIncomeBudgetProposal[] $accountingIncomeBudgetProposals
 * @property AccountingIncomeType $incomeType
 * @property AccountingIncomeEnrolment[] $accountingIncomeEnrolments
 * @property AccountingIncomeFreebiesAndIcons[] $accountingIncomeFreebiesAndIcons
 */
class IncomeCode extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'accounting_income_code';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['income_type_id', 'name', 'description'], 'required'],
            [['income_type_id'], 'integer'],
            [['name'], 'string', 'max' => 10],
            [['description'], 'string', 'max' => 250],
            [['income_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => IncomeType::className(), 'targetAttribute' => ['income_type_id' => 'id']],
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
            'income_type_id' => 'Income Type',
            'name' => 'Code',
            'description' => 'Description',
            'incomeTypeName' => 'Income Type'
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
    public function getBudgetProposals()
    {
        return $this->hasMany(BudgetProposal::className(), ['code_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIncomeType()
    {
        return $this->hasOne(IncomeType::className(), ['id' => 'income_type_id']);
    }

    public function getIncomeTypeName()
    {
        return $this->incomeType ? $this->incomeType->name : '';
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEnrolments()
    {
        return $this->hasMany(Enrolment::className(), ['code_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFreebieAndIcons()
    {
        return $this->hasMany(AccountingIncomeFreebieAndIcon::className(), ['code_id' => 'id']);
    }
}
