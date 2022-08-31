<?php

namespace common\modules\pos\models;

use Yii;

/**
 * This is the model class for table "pos_school".
 *
 * @property int $id
 * @property int $branch_id
 * @property string $title
 * @property string $address
 *
 * @property PosCustomer[] $posCustomers
 * @property PosBranch $branch
 */
class PosSchool extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pos_school';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['branch_id', 'title', 'address'], 'required'],
            [['branch_id'], 'integer'],
            [['address'], 'string'],
            [['title'], 'string', 'max' => 100],
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
            'title' => 'Title',
            'address' => 'Address',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPosCustomers()
    {
        return $this->hasMany(PosCustomer::className(), ['school_id' => 'id']);
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
