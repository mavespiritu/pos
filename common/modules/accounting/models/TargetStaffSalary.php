<?php

namespace common\modules\accounting\models;

use Yii;

/**
 * This is the model class for table "accounting_target_staff_salary".
 *
 * @property int $id
 * @property int $season_id
 * @property string $label
 * @property int $quantity
 * @property string $price
 *
 * @property AccountingSeason $season
 */
class TargetStaffSalary extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'accounting_target_staff_salary';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['quantity', 'unit_price'], 'required'],
            [['season_id', 'quantity'], 'integer'],
            [['label'], 'string'],
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
            'label' => 'Label',
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
}
