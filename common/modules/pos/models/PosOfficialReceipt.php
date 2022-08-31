<?php

namespace common\modules\pos\models;

use Yii;

/**
 * This is the model class for table "pos_official_receipt".
 *
 * @property int $id
 * @property int $season_id
 * @property string $start_number
 * @property resource $last_number
 * @property string $date_filed
 * @property string $datetime
 *
 * @property PosIncome[] $posIncomes
 * @property PosSeason $season
 */
class PosOfficialReceipt extends \yii\db\ActiveRecord
{
    public $branch_program_id;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pos_official_receipt';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['branch_program_id', 'season_id', 'start_number', 'last_number', 'date_filed'], 'required'],
            [['season_id'], 'integer'],
            [['date_filed', 'datetime'], 'safe'],
            [['start_number', 'last_number'], 'string', 'max' => 10],
            [['start_number'], 'match', 'pattern' => '/^[0-9]+$/','message' => 'Start number must only contain numbers'],
            [['last_number'], 'match', 'pattern' => '/^[0-9]+$/','message' => 'Last number must only contain numbers'],
            [['season_id'], 'exist', 'skipOnError' => true, 'targetClass' => PosSeason::className(), 'targetAttribute' => ['season_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'season_id' => 'Season',
            'seasonName' => 'Season',
            'start_number' => 'Start Number',
            'last_number' => 'Last Number',
            'date_filed' => 'Date Filed',
            'datetime' => 'Datetime',
            'branch_program_id' => 'Branch - Program'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPosIncomes()
    {
        return $this->hasMany(PosIncome::className(), ['official_receipt_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSeason()
    {
        return $this->hasOne(PosSeason::className(), ['id' => 'season_id']);
    }

    public function getSeasonName()
    {
        return $this->season ? $this->season->branchProgramName.' - SEASON '.$this->season->title : '';
    }
}
