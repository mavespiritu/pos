<?php

namespace common\modules\pos\models;

use Yii;

/**
 * This is the model class for table "pos_season".
 *
 * @property int $id
 * @property int $branch_program_id
 * @property string $title
 * @property string $start_date
 * @property string $end_date
 * @property string $status
 *
 * @property PosAudit[] $posAudits
 * @property PosBacktrack[] $posBacktracks
 * @property PosBeginningAmount[] $posBeginningAmounts
 * @property PosCustomerTransfer[] $posCustomerTransfers
 * @property PosCustomerTransfer[] $posCustomerTransfers0
 * @property PosExpense[] $posExpenses
 * @property PosExpenseItem[] $posExpenseItems
 * @property PosIncome[] $posIncomes
 * @property PosIncomeItem[] $posIncomeItems
 * @property PosOfficialReceipt[] $posOfficialReceipts
 * @property PosProduct[] $posProducts
 * @property PosBranchProgram $branchProgram
 */
class PosSeason extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pos_season';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['branch_program_id', 'title', 'start_date', 'end_date', 'status'], 'required'],
            [['branch_program_id'], 'integer'],
            [['start_date', 'end_date'], 'safe'],
            [['status'], 'string'],
            [['title'], 'string', 'max' => 100],
            [['branch_program_id'], 'exist', 'skipOnError' => true, 'targetClass' => PosBranchProgram::className(), 'targetAttribute' => ['branch_program_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'branch_program_id' => 'Branch - Program',
            'title' => 'Title',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'status' => 'Status',
            'branchProgramName' => 'Branch - Program',
            'seasonTitle' => 'Title',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPosAudits()
    {
        return $this->hasMany(PosAudit::className(), ['season_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPosBacktracks()
    {
        return $this->hasMany(PosBacktrack::className(), ['season_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPosBeginningAmounts()
    {
        return $this->hasMany(PosBeginningAmount::className(), ['season_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFromTransfers()
    {
        return $this->hasMany(PosCustomerTransfer::className(), ['from_season_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPosCustomerTransfers0()
    {
        return $this->hasMany(PosCustomerTransfer::className(), ['to_season_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPosExpenses()
    {
        return $this->hasMany(PosExpense::className(), ['season_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPosExpenseItems()
    {
        return $this->hasMany(PosExpenseItem::className(), ['season_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPosIncomes()
    {
        return $this->hasMany(PosIncome::className(), ['season_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPosIncomeItems()
    {
        return $this->hasMany(PosIncomeItem::className(), ['season_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPosOfficialReceipts()
    {
        return $this->hasMany(PosOfficialReceipt::className(), ['season_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPosProducts()
    {
        return $this->hasMany(PosProduct::className(), ['season_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBranchProgram()
    {
        return $this->hasOne(PosBranchProgram::className(), ['id' => 'branch_program_id']);
    }

    public function getBranchProgramName()
    {
        return $this->branchProgram ? $this->branchProgram->branchProgramName : '';
    }

    public function getSeasonName()
    {
        return $this->branchProgramName.' - SEASON '.$this->title;
    }

    public function getSeasonTitle()
    {
        return 'SEASON '.$this->title;
    }
}
