<?php

namespace common\modules\accounting\models;

use Yii;

/**
 * This is the model class for table "accounting_expense_branch_transfer".
 *
 * @property int $id
 * @property int $from_branch_program_id
 * @property int $to_branch_program_id
 * @property string $particulars
 * @property string $amount
 * @property string $remarks
 */
class BranchTransfer extends \yii\db\ActiveRecord
{
    public $grantee;
    public $branch_programs_id;
    public $frequency_id;
    public $page_id;
    public $date_id;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'accounting_expense_branch_transfer';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['branch_programs_id', 'frequency_id', 'date_id', 'page_id'], 'required', 'on' => 'searchBranchTransfer'],
            [['grantee', 'amount_source'], 'required', 'on' => 'approveBudgetProposal'],
            [['branch_id', 'branch_program_id', 'budget_proposal_id'], 'integer'],
            [['amount_source'], 'string'],
            [['amount'], 'number'],
            [['branch_id'], 'required', 'when' => function ($model) {
                    return $model->grantee == 'Branch';
                }, 'whenClient' => "function (attribute, value) {
                return $('#branchtransfer-grantee').val() == 'Branch';
            }"],
            [['branch_program_id'], 'required', 'when' => function ($model) {
                    return $model->grantee == 'Branch - Program';
                }, 'whenClient' => "function (attribute, value) {
                return $('#branchtransfer-grantee').val() == 'Branch - Program';
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
            'grantee' => 'Charge Fund To',
            'branch_id' => 'Charged to Branch',
            'branch_program_id' => 'Charged to Branch - Program',
            'branchName' => 'Charged to Branch',
            'branchProgramName' => 'Charged to Branch - Program',
            'budget_proposal_id' => 'Budget Proposal',
            'amount' => 'Amount',
            'approvedAmount' => 'Amount',
            'amount_source' => 'Source of Fund',
            'amountType' => 'Amount Type',
            'datetime' => 'Date Of Transaction',
            'frequency_id' => 'Frequency',
            'date_id' => 'Date',
            'page_id' => 'Page',
            'branch_programs_id' => 'Branch Program',
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

    public function getBudgetProposal()
    {
        return $this->hasOne(BudgetProposal::className(), ['id' => 'budget_proposal_id']);
    }

    public function getBranch()
    {
        return $this->hasOne(Branch::className(), ['id' => 'branch_id']);
    }

    public function getBranchName()
    {
        return $this->branch ? $this->branch->name : '';
    }

    public function getBranchProgram()
    {
        return $this->hasOne(BranchProgram::className(), ['id' => 'branch_program_id']);
    }
    
    public function getBranchProgramName()
    {
        return $this->branchProgram ? $this->branchProgram->branchProgramName : '';
    }

    public function getExpense()
    {
        return $this->hasOne(Expense::className(), ['expense_id' => 'id'])->where(['accounting_expense.expense_type_id' => '6']);
    }

    public function getAmountType()
    {
        $expense = $this->getExpense()->where(['expense_type_id' => '6'])->one();

        return  $expense ? $expense->amount_type : '';
    }

    public function getDatetime()
    {
        $expense = $this->getExpense()->where(['expense_type_id' => '6'])->one();
        
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

    public function getApprovedAmount()
    {
        $amount = Particular::find()->select(['sum(amount) as total'])->where(['budget_proposal_id' => $this->budget_proposal_id, 'approval_status' => 'Approved'])->asArray()->one();
        $total = 0;
        if(!empty($amount))
        {
            $total += $amount['total'];
        }

        return $total;
    }
}
