<?php

namespace common\modules\pos\models;

use Yii;

/**
 * This is the model class for table "pos_program".
 *
 * @property int $id
 * @property string $title
 *
 * @property PosBranchProgram[] $posBranchPrograms
 */
class PosProgram extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pos_program';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title'], 'unique', 'message' => 'The value is existing already'],
            [['title'], 'required'],
            [['title'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPosBranchPrograms()
    {
        return $this->hasMany(PosBranchProgram::className(), ['program_id' => 'id']);
    }
}
