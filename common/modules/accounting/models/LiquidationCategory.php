<?php

namespace common\modules\accounting\models;

use Yii;

/**
 * This is the model class for table "accounting_budget_proposal_liquidation_category".
 *
 * @property int $id
 * @property int $expense_type_id
 * @property string $name
 *
 * @property AccountingBudgetProposalLiquidation[] $accountingBudgetProposalLiquidations
 */
class LiquidationCategory extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'accounting_budget_proposal_liquidation_category';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['expense_type_id', 'name'], 'required'],
            [['expense_type_id'], 'integer'],
            [['name'], 'string', 'max' => 250],
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
            'name' => 'Name',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLiquidation()
    {
        return $this->hasMany(Liquidation::className(), ['category_id' => 'id']);
    }
}
