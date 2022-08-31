<?php

namespace common\modules\accounting\models;

use Yii;

/**
 * This is the model class for table "accounting_target_expense".
 *
 * @property int $id
 * @property int $branch_id
 * @property string $month
 * @property string $amount
 *
 * @property AccountingBranch $branch
 */
class TargetExpense extends \yii\db\ActiveRecord
{
    public $year;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'accounting_target_expense';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['branch_id', 'month', 'year', 'amount'], 'required'],
            [['branch_id'], 'integer'],
            [['month'], 'safe'],
            [['amount'], 'number'],
            [['branch_id'], 'exist', 'skipOnError' => true, 'targetClass' => Branch::className(), 'targetAttribute' => ['branch_id' => 'id']],
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
            'branch_id' => 'Branch',
            'branchName' => 'Branch',
            'month' => 'Month',
            'year' => 'Year',
            'amount' => 'Amount',
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

    public function getBranchName()
    {
        return $this->branch ? $this->branch->name : '';
    }
}
