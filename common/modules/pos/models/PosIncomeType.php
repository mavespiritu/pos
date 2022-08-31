<?php

namespace common\modules\pos\models;

use Yii;

/**
 * This is the model class for table "pos_income_type".
 *
 * @property int $id
 * @property string $title
 * @property string $description
 *
 * @property PosIncomeItem[] $posIncomeItems
 */
class PosIncomeType extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pos_income_type';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'description'], 'required'],
            [['description'], 'string'],
            [['title'], 'string', 'max' => 200],
            [['title'], 'unique', 'message' => 'The values are existing already'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'description' => 'Description',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPosIncomeItems()
    {
        return $this->hasMany(PosIncomeItem::className(), ['income_type_id' => 'id']);
    }
}
