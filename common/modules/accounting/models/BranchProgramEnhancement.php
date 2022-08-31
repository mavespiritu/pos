<?php

namespace common\modules\accounting\models;

use Yii;

/**
 * This is the model class for table "accounting_branch_program_enhancement".
 *
 * @property int $id
 * @property int $branch_program_id
 * @property string $amount
 *
 * @property AccountingBranchProgram $branchProgram
 */
class BranchProgramEnhancement extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'accounting_branch_program_enhancement';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['branch_program_id', 'amount'], 'required'],
            [['branch_program_id'], 'integer'],
            [['amount'], 'number'],
            [['branch_program_id'], 'exist', 'skipOnError' => true, 'targetClass' => BranchProgram::className(), 'targetAttribute' => ['branch_program_id' => 'id']],
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
            'amount' => 'Amount',
            'branchProgramName' => 'Branch - Program',
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBranchProgram()
    {
        return $this->hasOne(BranchProgram::className(), ['id' => 'branch_program_id']);
    }

    public function getBranch()
    {
        $branchProgram = BranchProgram::find()
                        ->select([
                          'accounting_branch_program.id as id', 
                          'accounting_branch.id as branch_id', 
                          'accounting_program.id as program_id', 
                          ])
                        ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                        ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                        ->where(['accounting_branch_program.id' => $this->branch_program_id])
                        ->asArray()
                        ->one();

        return $this->hasOne(Branch::className(), ['id' => $branchProgram['branch_id']]);
    }

    public function getProgram()
    {
        $branchProgram = BranchProgram::find()
                        ->select([
                          'accounting_branch_program.id as id', 
                          'accounting_branch.id as branch_id', 
                          'accounting_program.id as program_id', 
                          ])
                        ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                        ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                        ->where(['accounting_branch_program.id' => $this->branch_program_id])
                        ->asArray()
                        ->one();

        return $this->hasOne(Program::className(), ['id' => $branchProgram['program_id']]);
    }

    public function getBranchProgramName()
    {
        $branchProgram = BranchProgram::find()
                          ->select([
                            'accounting_branch_program.id as id', 
                            'concat(accounting_branch.name," - ",accounting_program.name) as name', 
                            ])
                          ->leftJoin('accounting_branch', 'accounting_branch.id = accounting_branch_program.branch_id')
                          ->leftJoin('accounting_program', 'accounting_program.id = accounting_branch_program.program_id')
                          ->where(['accounting_branch_program.id' => $this->branch_program_id])
                          ->asArray()
                          ->one();

        return $branchProgram ? $branchProgram['name'] : ''; 
    }
}
