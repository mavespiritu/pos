<?php

namespace common\modules\accounting\models;

use Yii;

/**
 * This is the model class for table "accounting_income_budget_proposal".
 *
 * @property int $id
 * @property int $from_branch_program_id
 * @property int $to_branch_program_id
 * @property int $code_id
 * @property string $details
 * @property string $amount
 *
 * @property AccountingIncomeCode $code
 */
class BudgetProposal extends \yii\db\ActiveRecord
{
    public $grantee;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'accounting_income_budget_proposal';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['budget_proposal_type_id', 'grantee'], 'required', 'on' => 'createBudgetProposal'],
            [['approval_status'], 'required', 'on' => 'approveBudgetProposal'],
            [['voucher_no'], 'required', 'on' => 'approveBudgetProposal'],
            [['branch_id', 'branch_program_id', 'code_id', 'budget_proposal_type_id'], 'integer'],
            [['other_type', 'approval_status', 'grantee', 'voucher_no'], 'string'],
            [['remarks', 'datetime'], 'safe'],
            [['branch_program_id'], 'required', 'when' => function ($model) {
                    return $model->grantee == 'Branch - Program';
                }, 'whenClient' => "function (attribute, value) {
                return $('#budgetproposal-grantee').val() == 'Branch - Program';
            }"],
            [['other_type'], 'required', 'when' => function ($model) {
                    return $model->budget_proposal_type_id == '18';
                }, 'whenClient' => "function (attribute, value) {
                return $('#budgetproposal-budget_proposal_type_id').val() == '18';
            }"],
            [['code_id'], 'exist', 'skipOnError' => true, 'targetClass' => IncomeCode::className(), 'targetAttribute' => ['code_id' => 'id']],
            [['budget_proposal_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => BudgetProposalType::className(), 'targetAttribute' => ['budget_proposal_type_id' => 'id']],
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
            'branch_id' => 'Released to Branch',
            'branchName' => 'Released to Branch',
            'branch_program_id' => 'Released to Branch - Program',
            'branchProgramName' => 'Released to Branch - Program',
            'releaseTo' => 'Released to',
            'code_id' => 'Code',
            'budget_proposal_type_id' => 'Type of Request',
            'other_type' => 'If Request Type is "Others",',
            'codeName' => 'Code',
            'budgetProposalTypeName' => 'Type of Request',
            'amountType' => 'Amount Type',
            'datetime' => 'Date of Transaction',
            'transactionNumber' => 'Transaction Number',
            'approval_status' => 'Approval Status',
            'grantee' => 'Release Fund To',
            'voucher_no' => 'Voucher No.',
            'renarks' => 'Remarks',
            'requestedAmount' => 'Requested Amount',
            'approvedAmount' => 'Approved Amount',
            'datetime' => 'Request Date'
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
    public function getCode()
    {
        return $this->hasOne(IncomeCode::className(), ['id' => 'code_id']);
    }

    public function getBudgetProposalType()
    {
        return $this->hasOne(BudgetProposalType::className(), ['id' => 'budget_proposal_type_id']);
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

    public function getReleaseTo()
    {
        if($this->branch_id != '')
        {
            $branch = Branch::findOne($this->branch_id);

            return $branch->name;

        }else{

            $bp = BranchProgram::findOne([$this->branch_program_id]);

            return $bp->branchProgramName;
        }
    }

    public function getReleaseToName()
    {

    }

    public function getCodeName()
    {
        return $this->code? $this->code->name.' - '.$this->code->description : '';
    }

    public function getBudgetProposalTypeName()
    {
        $type = '';

        if($this->budgetProposalType)
        {
            $type = $this->budgetProposalType->id == '18' ? $this->other_type : $this->budgetProposalType->name;
        }

        return $type;
    }

    public function getParticulars()
    {
        return $this->hasMany(Particular::className(), ['budget_proposal_id' => 'id']);
    }

    public function getLiquidations()
    {
        return $this->hasMany(Liquidation::className(), ['budget_proposal_id' => 'id']);
    }

    public function getPfRequests()
    {
        return $this->hasMany(PfRequest::className(), ['budget_proposal_id' => 'id']);
    }

    public function getLiquidatedAmount()
    {
        $amount = Liquidation::find()->select(['sum(amount) as total'])->where(['budget_proposal_id' => $this->id, 'approval_status' => 'Approved'])->asArray()->one();
        $total = 0;
        if(!empty($amount))
        {
            $total += $amount['total'];
        }

        return $total;
    }

    public function getRequestedAmount()
    {
        $amount = Particular::find()->select(['sum(amount) as total'])->where(['budget_proposal_id' => $this->id])->asArray()->one();
        $total = 0;
        if(!empty($amount))
        {
            $total += $amount['total'];
        }

        return $total;
    }

    public function getApprovedAmount()
    {
        $amount = Particular::find()->select(['sum(amount) as total'])->where(['budget_proposal_id' => $this->id, 'approval_status' => 'Approved'])->asArray()->one();
        $total = 0;
        if(!empty($amount))
        {
            $total += $amount['total'];
        }

        return $total;
    }

    public function getIncome()
    {
        return $this->hasOne(Income::className(), ['income_id' => 'id']);
    }

    public function getAmountType()
    {
        $income = $this->getIncome()->where(['income_type_id' => '3'])->one();

        return  $income ? $income->amount_type : '';
    }

    public function getDatetime()
    {
        $income = $this->getIncome()->where(['income_type_id' => '3'])->one();
        
        return  $income ? $income->datetime : '';
    }

    public function getTransactionNumber()
    {
        $income = $this->getIncome()->where(['income_type_id' => '3'])->one();
        
        return  $income ? $income->transaction_number : '';
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
