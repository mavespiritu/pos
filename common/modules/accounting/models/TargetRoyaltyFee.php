<?php

namespace common\modules\accounting\models;

use Yii;

/**
 * This is the model class for table "accounting_target_royalty_fee".
 *
 * @property int $id
 * @property int $season_id
 * @property string $percentage
 *
 * @property AccountingSeason $season
 */
class TargetRoyaltyFee extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'accounting_target_royalty_fee';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['percentage'], 'required'],
            [['season_id'], 'integer'],
            [['percentage'], 'number'],
            [['season_id'], 'exist', 'skipOnError' => true, 'targetClass' => Season::className(), 'targetAttribute' => ['season_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'season_id' => 'Season',
            'percentage' => 'Percentage',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSeason()
    {
        return $this->hasOne(Season::className(), ['id' => 'season_id']);
    }
}
