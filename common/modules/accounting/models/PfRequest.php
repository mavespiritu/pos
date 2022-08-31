<?php

namespace common\modules\accounting\models;

use Yii;

/**
 * This is the model class for table "accounting_pf_request".
 *
 * @property int $id
 * @property int $budget_proposal_id
 * @property string $lecture_date
 * @property string $name
 * @property string $concept
 * @property string $time
 * @property string $rate_per_hour
 * @property string $allowance
 *
 * @property AccountingIncomeBudgetProposal $budgetProposal
 */
class PfRequest extends \yii\db\ActiveRecord
{
    public $end_time;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'accounting_pf_request';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['lecture_date', 'name', 'concept', 'time', 'end_time', 'rate_per_hour'], 'required'],
            [['budget_proposal_id'], 'integer'],
            [['lecture_date'], 'safe'],
            [['time'], 'string'],
            [['rate_per_hour', 'allowance'], 'number'],
            [['name', 'concept'], 'string', 'max' => 250],
            [['budget_proposal_id'], 'exist', 'skipOnError' => true, 'targetClass' => BudgetProposal::className(), 'targetAttribute' => ['budget_proposal_id' => 'id']],
            ['time', 'time', 'timestampAttribute' => 'time'],
            ['end_time', 'time', 'timestampAttribute' => 'end_time'],
            ['time', 'compare', 'compareAttribute' => 'end_time', 'operator' => '>=', 'enableClientValidation' => true, 'message' => 'Start time must be lesser than end time'],
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
            'lecture_date' => 'Lecture Date',
            'name' => 'Name',
            'concept' => 'Concept',
            'time' => 'Start Time',
            'rate_per_hour' => 'Rate Per Hour',
            'allowance' => 'Allowance',
            'end_time' => 'End Time'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBudgetProposal()
    {
        return $this->hasOne(BudgetProposal::className(), ['id' => 'budget_proposal_id']);
    }

    public function getHours()
    {
        $time = explode(" - ", $this->time);
        $time = strtotime($time[1]) - strtotime($time[0]) - 3600;
        $difference = round(abs($time) / 3600,2);

        return $difference;
    }
}
