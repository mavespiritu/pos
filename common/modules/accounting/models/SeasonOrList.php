<?php

namespace common\modules\accounting\models;

use Yii;

/**
 * This is the model class for table "accounting_season_or_list".
 *
 * @property int $id
 * @property int $season_id
 * @property string $or_start
 * @property int $no_of_pieces
 *
 * @property AccountingSeason $season
 */
class SeasonOrList extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'accounting_season_or_list';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['season_id', 'no_of_pieces', 'or_start'], 'required'],
            [['season_id', 'no_of_pieces'], 'integer'],
            [['or_start'], 'string', 'max' => 100],
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
            'season_id' => 'Season',
            'seasonName' => 'Season',
            'or_start' => 'OR (Start Number)',
            'no_of_pieces' => 'No. Of Pieces',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSeason()
    {
        return $this->hasOne(Season::className(), ['id' => 'season_id']);
    }

    public function getSeasonName()
    {
        return $this->season ? $this->season->seasonName : '';
    }
}
