<?php

namespace common\modules\accounting\models;

use Yii;

/**
 * This is the model class for table "accounting_archive_season".
 *
 * @property int $id
 * @property int $season_id
 * @property int $user_id
 * @property string $datetime
 */
class ArchiveSeason extends \yii\db\ActiveRecord
{
    public $branch_program_id;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'accounting_archive_season';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['branch_program_id', 'season_id'], 'required'],
            [['datetime'], 'safe'],
            [['season_id'], 'exist', 'skipOnError' => true, 'targetClass' => Season::className(), 'targetAttribute' => ['season_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'branch_program_id' => 'Branch - Program',
            'season_id' => 'Season',
            'user_id' => 'User ID',
            'datetime' => 'Datetime',
        ];
    }
}
