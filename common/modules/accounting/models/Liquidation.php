<?php

namespace common\modules\accounting\models;

use Yii;

/**
 * This is the model class for table "accounting_budget_proposal_liquidation".
 *
 * @property int $id
 * @property int $budget_proposal_id
 * @property string $date
 * @property int $category_id
 * @property string $particulars
 * @property string $amount
 * @property string $approval_status
 *
 * @property AccountingIncomeBudgetProposal $budgetProposal
 * @property AccountingBudgetProposalLiquidationCategory $category
 */
class Liquidation extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'accounting_budget_proposal_liquidation';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['category_id', 'date', 'particulars', 'amount'], 'required'],
            [['budget_proposal_id', 'category_id'], 'integer'],
            [['date'], 'safe'],
            [['particulars', 'approval_status'], 'string'],
            [['amount'], 'number'],
            [['budget_proposal_id'], 'exist', 'skipOnError' => true, 'targetClass' => BudgetProposal::className(), 'targetAttribute' => ['budget_proposal_id' => 'id']],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => LiquidationCategory::className(), 'targetAttribute' => ['category_id' => 'id']],
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
            'date' => 'Date',
            'category_id' => 'Category',
            'particulars' => 'Particulars',
            'amount' => 'Amount',
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
    public function getLiquidationCategory()
    {
        return $this->hasOne(LiquidationCategory::className(), ['id' => 'category_id']);
    }
}
