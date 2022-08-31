<?php

namespace common\modules\accounting\models;

use Yii;

/**
 * This is the model class for table "accounting_target_income".
 *
 * @property int $id
 * @property int $season_id
 * @property int $enrolee_type_id
 * @property int $quantity
 * @property string $price
 *
 * @property AccountingSeason $season
 */
class TargetIncome extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'accounting_target_income';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['season_id', 'quantity', 'unit_price'], 'required'],
            [['season_id', 'enrolee_type_id', 'quantity'], 'integer'],
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
            'enrolee_type_id' => 'Enrolee Type',
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
    public function getEnroleeType()
    {
        return $this->hasOne(EnroleeType::className(), ['id' => 'enrolee_type_id']);
    }
}
