<?php

namespace common\modules\accounting\models;

use Yii;

/**
 * This is the model class for table "accounting_target_tax".
 *
 * @property int $id
 * @property int $season_id
 * @property string $percentage_tax
 *
 * @property AccountingSeason $season
 */
class TargetTax extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'accounting_target_tax';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['percentage_tax'], 'required'],
            [['season_id'], 'integer'],
            [['percentage_tax'], 'number'],
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
            'percentage_tax' => 'Percentage Tax',
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
