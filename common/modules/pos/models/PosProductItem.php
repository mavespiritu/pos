<?php

namespace common\modules\pos\models;

use Yii;

/**
 * This is the model class for table "pos_product_item".
 *
 * @property int $id
 * @property int $product_id
 * @property int $item_id
 * @property double $amount
 *
 * @property PosItem $item
 * @property PosProduct $product
 */
class PosProductItem extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pos_product_item';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['item_id', 'amount'], 'required'],
            [['product_id', 'item_id'], 'integer'],
            [['amount'], 'number'],
            [['item_id'], 'exist', 'skipOnError' => true, 'targetClass' => PosItem::className(), 'targetAttribute' => ['item_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => PosProduct::className(), 'targetAttribute' => ['product_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_id' => 'Product',
            'item_id' => 'Item',
            'amount' => 'Amount',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItem()
    {
        return $this->hasOne(PosItem::className(), ['id' => 'item_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(PosProduct::className(), ['id' => 'product_id']);
    }
}
