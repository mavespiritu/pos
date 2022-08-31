<?php

namespace common\modules\pos\models;

use Yii;

/**
 * This is the model class for table "pos_product_type".
 *
 * @property int $id
 * @property string $title
 * @property string $description
 *
 * @property PosProduct[] $posProducts
 */
class PosProductType extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pos_product_type';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'description'], 'required'],
            [['description'], 'string'],
            [['title'], 'string', 'max' => 100],
            [['title'], 'unique', 'message' => 'The value is existing already'],
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
    public function getPosProducts()
    {
        return $this->hasMany(PosProduct::className(), ['product_type_id' => 'id']);
    }
}
