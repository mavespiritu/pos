<?php

namespace common\modules\pos\models;

use Yii;

/**
 * This is the model class for table "pos_item".
 *
 * @property int $id
 * @property string $title
 * @property string $description
 *
 * @property PosProductItem[] $posProductItems
 */
class PosItem extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pos_item';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'description'], 'required'],
            [['title'], 'unique', 'message' => 'The values are existing already'],
            [['description'], 'string'],
            [['title'], 'string', 'max' => 100],
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
    public function getPosProductItems()
    {
        return $this->hasMany(PosProductItem::className(), ['item_id' => 'id']);
    }
}
