<?php

namespace common\modules\pos\models;

use Yii;

/**
 * This is the model class for table "pos_vendor".
 *
 * @property int $id
 * @property string $title
 * @property string $description
 *
 * @property PosExpense[] $posExpenses
 */
class PosVendor extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pos_vendor';
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
    public function getPosExpenses()
    {
        return $this->hasMany(PosExpense::className(), ['vendor_id' => 'id']);
    }
}
