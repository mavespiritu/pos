<?php

namespace common\modules\accounting\models;

use Yii;

/**
 * This is the model class for table "accounting_access_program".
 *
 * @property int $id
 * @property int $user_id
 * @property int $branch_program_id
 */
class AccessProgram extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'accounting_access_program';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'branch_program_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'branch_program_id' => 'Branch Program ID',
        ];
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBranchProgram()
    {
        return $this->hasOne(BranchProgram::className(), ['id' => 'branch_program_id']);
    }
}
