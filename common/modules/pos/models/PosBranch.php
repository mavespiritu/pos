<?php

namespace common\modules\pos\models;

use Yii;

/**
 * This is the model class for table "pos_branch".
 *
 * @property int $id
 * @property string $code
 * @property string $title
 *
 * @property PosBranchProgram[] $posBranchPrograms
 */
class PosBranch extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pos_branch';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['code', 'title'], 'required'],
            [['code'], 'string', 'max' => 10],
            [['title'], 'string', 'max' => 100],
            [['code', 'title'], 'unique', 'message' => 'The values are existing already'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Code',
            'title' => 'Title',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPosBranchPrograms()
    {
        return $this->hasMany(PosBranchProgram::className(), ['branch_id' => 'id']);
    }
}
