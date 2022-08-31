<?php

namespace common\modules\pos\models;

use Yii;

/**
 * This is the model class for table "pos_season_due_date".
 *
 * @property int $id
 * @property int $season_id
 * @property string $due_date
 */
class PosSeasonDueDate extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pos_season_due_date';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['season_id'], 'integer'],
            [['due_date'], 'required'],
            [['due_date'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'season_id' => 'Season ID',
            'due_date' => 'Due Date',
        ];
    }
}
