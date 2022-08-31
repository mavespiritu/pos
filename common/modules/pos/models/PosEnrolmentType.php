<?php

namespace common\modules\pos\models;

use Yii;

/**
 * This is the model class for table "pos_enrolment_type".
 *
 * @property int $id
 * @property string $title
 * @property string $description
 *
 * @property PosProduct[] $posProducts
 */
class PosEnrolmentType extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pos_enrolment_type';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'description'], 'required'],
            [['title', 'description'], 'string', 'max' => 100],
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
        return $this->hasMany(PosProduct::className(), ['enrolment_type_id' => 'id']);
    }
}
