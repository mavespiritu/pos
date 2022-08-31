<?php

namespace common\modules\pos\models;

use Yii;

/**
 * This is the model class for table "pos_branch_program".
 *
 * @property int $id
 * @property int $branch_id
 * @property int $program_id
 *
 * @property PosBranch $branch
 * @property PosProgram $program
 * @property PosSeason[] $posSeasons
 * @property PosUserAccess[] $posUserAccesses
 */
class PosBranchProgram extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pos_branch_program';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['branch_id', 'program_id'], 'required'],
            [['branch_id', 'program_id'], 'integer'],
            [['branch_id'], 'exist', 'skipOnError' => true, 'targetClass' => PosBranch::className(), 'targetAttribute' => ['branch_id' => 'id']],
            [['program_id'], 'exist', 'skipOnError' => true, 'targetClass' => PosProgram::className(), 'targetAttribute' => ['program_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'branch_id' => 'Branch',
            'program_id' => 'Program',
            'branchName' => 'Branch',
            'programName' => 'Program',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBranch()
    {
        return $this->hasOne(PosBranch::className(), ['id' => 'branch_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProgram()
    {
        return $this->hasOne(PosProgram::className(), ['id' => 'program_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPosSeasons()
    {
        return $this->hasMany(PosSeason::className(), ['branch_program_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPosUserAccesses()
    {
        return $this->hasMany(PosUserAccess::className(), ['branch_program_id' => 'id']);
    }

    public function getBranchName()
    {
        return $this->branch ? $this->branch->title : '';
    }

    public function getProgramName()
    {
        return $this->program ? $this->program->title : '';
    }

    public function getBranchProgramName()
    {
        return $this->branchName.' - '.$this->programName;
    }
}
