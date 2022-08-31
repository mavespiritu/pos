<?php

namespace common\modules\accounting\models;

use Yii;

/**
 * This is the model class for table "accounting_notification".
 *
 * @property int $id
 * @property int $branch_id
 * @property string $model
 * @property int $model_id
 * @property string $message
 * @property string $datetime
 *
 * @property AccountingBranch $branch
 */
class Notification extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'accounting_notification';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['branch_id', 'model_id'], 'integer'],
            [['message'], 'string'],
            [['datetime'], 'safe'],
            [['model'], 'string', 'max' => 250],
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
            'model' => 'Model',
            'model_id' => 'Model ID',
            'message' => 'Message',
            'datetime' => 'Datetime',
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
