<?php

namespace common\modules\accounting\models;

use Yii;

/**
 * This is the model class for table "accounting_audit_beginning_coh".
 *
 * @property int $id
 * @property int $branch_id
 * @property string $amount
 * @property string $datetime
 *
 * @property AccountingBranch $branch
 */
class BeginningCoh extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'accounting_audit_beginning_coh';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['cash_on_hand', 'cash_on_bank'], 'required'],
            [['branch_program_id', 'season_id'], 'integer'],
            [['datetime'], 'safe'],
            [['branch_program_id'], 'exist', 'skipOnError' => true, 'targetClass' => BranchProgram::className(), 'targetAttribute' => ['branch_program_id' => 'id']],
            [['season_id'], 'exist', 'skipOnError' => true, 'targetClass' => Season::className(), 'targetAttribute' => ['season_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'branch_program_id' => 'Branch Program',
            'season_id' => 'Season',
            'cash_on_hand' => 'Cash On Hand',
            'cash_on_bank' => 'Cash On Bank',
            'datetime' => 'Datetime',
        ];
    }

    public function behaviors()
    {
        return [
            'bedezign\yii2\audit\AuditTrailBehavior'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBranchProgram()
    {
        return $this->hasOne(BranchProgram::className(), ['id' => 'branch_program_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSeason()
    {
        return $this->hasOne(Season::className(), ['id' => 'season_id']);
    }
}
