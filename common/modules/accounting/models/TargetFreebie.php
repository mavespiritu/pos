<?php

namespace common\modules\accounting\models;

use Yii;

/**
 * This is the model class for table "accounting_target_freebie".
 *
 * @property int $id
 * @property int $season_id
 * @property int $freebie_id
 * @property string $other_label
 * @property int $quantity
 * @property string $unit_price
 *
 * @property AccountingSeason $season
 */
class TargetFreebie extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'accounting_target_freebie';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['quantity', 'unit_price'], 'required'],
            [['season_id', 'freebie_id', 'quantity'], 'integer'],
            [['other_label'], 'string'],
            [['unit_price'], 'number'],
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
            'freebie_id' => 'Freebie',
            'other_label' => 'Other Label',
            'quantity' => 'Quantity',
            'unit_price' => 'Unit Price',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSeason()
    {
        return $this->hasOne(Season::className(), ['id' => 'season_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFreebie()
    {
        return $this->hasOne(Freebie::className(), ['id' => 'freebie_id']);
    }
}
