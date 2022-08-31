<?php

namespace common\modules\accounting\models;

use Yii;

/**
 * This is the model class for table "accounting_budget_proposal_type".
 *
 * @property int $id
 * @property string $name
 *
 * @property AccountingIncomeBudgetProposal[] $accountingIncomeBudgetProposals
 */
class BudgetProposalType extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'accounting_budget_proposal_type';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'string', 'max' => 250],
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
            'name' => 'Name',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccountingIncomeBudgetProposals()
    {
        return $this->hasMany(AccountingIncomeBudgetProposal::className(), ['budget_proposal_type_id' => 'id']);
    }
}
