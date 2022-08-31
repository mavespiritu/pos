<?php

namespace common\modules\pos\models;

use Yii;

/**
 * This is the model class for table "pos_dropout".
 *
 * @property int $id
 * @property int $enrolment_id
 * @property int $processed_by
 * @property string $date_processed
 * @property string $remarks
 * @property string $datetime
 *
 * @property PosEnrolment $enrolment
 */
class PosDropout extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pos_dropout';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['status', 'remarks', 'date_processed'], 'required'],
            [['enrolment_id', 'processed_by'], 'integer'],
            [['date_processed', 'datetime'], 'safe'],
            [['remarks'], 'string'],
            [['enrolment_id'], 'exist', 'skipOnError' => true, 'targetClass' => PosEnrolment::className(), 'targetAttribute' => ['enrolment_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'enrolment_id' => 'Enrolment ID',
            'processed_by' => 'Processed By',
            'date_processed' => 'Date Processed',
            'remarks' => 'Remarks',
            'datetime' => 'Datetime',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEnrolment()
    {
        return $this->hasOne(PosEnrolment::className(), ['id' => 'enrolment_id']);
    }

    public function getDroppedBy()
    {
        $user = UserInfo::findOne($this->processed_by);

        return $user->FIRST_M.' '.$user->MIDDLE_M.' '.$user->LAST_M;
    }
}
