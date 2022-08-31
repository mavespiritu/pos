<?php

namespace common\modules\pos\models;

use Yii;

/**
 * This is the model class for table "pos_denomination".
 *
 * @property int $id
 * @property string $title
 *
 * @property PosAudit[] $posAudits
 */
class PosDenomination extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pos_denomination';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title'], 'string', 'max' => 10],
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
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPosAudits()
    {
        return $this->hasMany(PosAudit::className(), ['denomination_id' => 'id']);
    }
}
