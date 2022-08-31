<?php

namespace common\modules\pos\models;

use Yii;

/**
 * This is the model class for table "pos_audit".
 *
 * @property int $id
 * @property int $season_id
 * @property int $denomination_id
 * @property int $total
 * @property string $audit_date
 * @property string $datetime
 *
 * @property PosDenomination $denomination
 * @property PosSeason $season
 */
class PosAudit extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pos_audit';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['total'], 'required'],
            [['season_id', 'audit_date'], 'required', 'on' => 'searchAudit'],
            [['season_id', 'denomination_id', 'total'], 'integer'],
            [['audit_date', 'datetime'], 'safe'],
            [['denomination_id'], 'exist', 'skipOnError' => true, 'targetClass' => PosDenomination::className(), 'targetAttribute' => ['denomination_id' => 'id']],
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
            'denomination_id' => 'Denomination',
            'total' => 'Count',
            'audit_date' => 'Audit Date',
            'datetime' => 'Datetime',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDenomination()
    {
        return $this->hasOne(PosDenomination::className(), ['id' => 'denomination_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSeason()
    {
        return $this->hasOne(PosSeason::className(), ['id' => 'season_id']);
    }
}
