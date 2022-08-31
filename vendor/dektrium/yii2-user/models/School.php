<?php

namespace dektrium\user\models;

use Yii;

/**
 * This is the model class for table "accounting_school".
 *
 * @property int $id
 * @property int $branch_id
 * @property string $name
 * @property string $location
 *
 * @property AccountingBranch $branch
 * @property AccountingStudent[] $accountingStudents
 */
class School extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'accounting_school';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'location'], 'required'],
            [['branch_id'], 'integer'],
            [['name', 'location'], 'string'],
            [['branch_id'], 'exist', 'skipOnError' => true, 'targetClass' => Branch::className(), 'targetAttribute' => ['branch_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'branch_id' => 'Branch ID',
            'name' => 'Name',
            'location' => 'Location',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBranch()
    {
        return $this->hasOne(Branch::className(), ['id' => 'branch_id']);
    }
}
