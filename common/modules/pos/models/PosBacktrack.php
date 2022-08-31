<?php

namespace common\modules\pos\models;

use Yii;

/**
 * This is the model class for table "pos_backtrack".
 *
 * @property int $id
 * @property int $season_id
 * @property string $date_from
 * @property string $date_to
 * @property string $field
 * @property string $datetime
 *
 * @property PosSeason $season
 */
class PosBacktrack extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pos_backtrack';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['branch_id', 'date_from', 'date_to', 'field'], 'required'],
            [['branch_id'], 'integer'],
            [['date_from', 'date_to', 'datetime'], 'safe'],
            [['field'], 'string'],
            [['branch_id'], 'exist', 'skipOnError' => true, 'targetClass' => PosBranch::className(), 'targetAttribute' => ['branch_id' => 'id']],
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
            'branchName' => 'Branch',
            'date_from' => 'Date From',
            'date_to' => 'Date To',
            'field' => 'Field',
            'datetime' => 'Datetime',
            'branch_program_id' => 'Branch - Program'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBranch()
    {
        return $this->hasOne(PosBranch::className(), ['id' => 'branch_id']);
    }

    public function getBranchName()
    {
        return $this->branch ? $this->branch->title : '';
    }
}
