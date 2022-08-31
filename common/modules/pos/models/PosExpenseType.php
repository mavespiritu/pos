<?php

namespace common\modules\pos\models;

use Yii;

/**
 * This is the model class for table "pos_expense_type".
 *
 * @property int $id
 * @property string $title
 * @property string $description
 *
 * @property PosExpenseItem[] $posExpenseItems
 */
class PosExpenseType extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pos_expense_type';
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
    public function getPosExpenseItems()
    {
        return $this->hasMany(PosExpenseItem::className(), ['expense_type_id' => 'id']);
    }
}
