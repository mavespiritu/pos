<?php

namespace common\modules\accounting\models;

use Yii;

/**
 * This is the model class for table "tblregion".
 *
 * @property string $region_c
 * @property string $region_m
 * @property string $abbreviation
 * @property int $region_sort
 */
class Region extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tblregion';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['region_c', 'region_m', 'abbreviation'], 'required'],
            [['region_sort'], 'integer'],
            [['region_c'], 'string', 'max' => 2],
            [['region_m', 'abbreviation'], 'string', 'max' => 200],
            [['region_c'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'region_c' => 'Region C',
            'region_m' => 'Region M',
            'abbreviation' => 'Abbreviation',
            'region_sort' => 'Region Sort',
        ];
    }
}
