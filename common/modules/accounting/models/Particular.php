<?php

namespace common\modules\accounting\models;

use Yii;

/**
 * This is the model class for table "accounting_budget_proposal_particular".
 *
 * @property int $id
 * @property int $budget_proposal_id
 * @property int $particular_code_id
 * @property string $proposed_date
 * @property string $particular
 * @property string $amount
 * @property string $date_needed
 * @property string $approval_status
 *
 * @property AccountingIncomeBudgetProposal $budgetProposal
 * @property AccountingParticularCode $particularCode
 */
class Particular extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'accounting_budget_proposal_particular';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['particular_code_id', 'proposed_date', 'particular', 'amount', 'date_needed'], 'required'],
            [['budget_proposal_id', 'particular_code_id'], 'integer'],
            [['proposed_date', 'date_needed'], 'safe'],
            [['particular', 'approval_status'], 'string'],
            [['amount'], 'number'],
            [['budget_proposal_id'], 'exist', 'skipOnError' => true, 'targetClass' => budgetProposal::className(), 'targetAttribute' => ['budget_proposal_id' => 'id']],
            [['particular_code_id'], 'exist', 'skipOnError' => true, 'targetClass' => ParticularCode::className(), 'targetAttribute' => ['particular_code_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'budget_proposal_id' => 'Budget Proposal ID',
            'particular_code_id' => 'Code',
            'proposed_date' => 'Proposed Date',
            'particular' => 'Particular',
            'amount' => 'Amount',
            'date_needed' => 'Date Needed',
            'approval_status' => 'Approval Status',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBudgetProposal()
    {
        return $this->hasOne(BudgetProposal::className(), ['id' => 'budget_proposal_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParticularCode()
    {
        return $this->hasOne(ParticularCode::className(), ['id' => 'particular_code_id']);
    }
}
