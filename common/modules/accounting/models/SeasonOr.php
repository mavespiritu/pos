<?php

namespace common\modules\accounting\models;

use Yii;

/**
 * This is the model class for table "accounting_season_or".
 *
 * @property int $id
 * @property int $season_id
 * @property string $or
 *
 * @property AccountingSeason $season
 */
class SeasonOr extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'accounting_season_or';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['season_id'], 'integer'],
            [['or_no'], 'string', 'max' => 50],
            [['season_id'], 'exist', 'skipOnError' => true, 'targetClass' => Season::className(), 'targetAttribute' => ['season_id' => 'id']],
        ];
    }

    public function behaviors()
    {
        return [
            'bedezign\yii2\audit\AuditTrailBehavior'
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
            'or_no' => 'Or',
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
