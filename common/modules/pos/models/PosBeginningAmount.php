<?php

namespace common\modules\pos\models;

use Yii;

/**
 * This is the model class for table "pos_beginning_amount".
 *
 * @property int $id
 * @property int $season_id
 * @property string $type
 * @property string $account_date
 * @property string $datetime
 *
 * @property PosSeason $season
 */
class PosBeginningAmount extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pos_beginning_amount';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['amount'], 'required'],
            [['season_id'], 'integer'],
            [['type'], 'string'],
            [['account_date', 'datetime'], 'safe'],
            [['season_id'], 'exist', 'skipOnError' => true, 'targetClass' => PosSeason::className(), 'targetAttribute' => ['season_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'season_id' => 'Season ID',
            'type' => 'Type',
            'amount' => 'Amount',
            'account_date' => 'Account Date',
            'datetime' => 'Datetime',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSeason()
    {
        return $this->hasOne(PosSeason::className(), ['id' => 'season_id']);
    }
}
