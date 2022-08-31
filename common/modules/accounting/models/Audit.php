<?php

namespace common\modules\accounting\models;

use Yii;

/**
 * This is the model class for table "accounting_audit".
 *
 * @property int $id
 * @property int $branch_id
 * @property int $denomination_id
 * @property int $total
 * @property string $datetime
 *
 * @property AccountingBranch $branch
 * @property AccountingDenomination $denomination
 */
class Audit extends \yii\db\ActiveRecord
{
    public $season_id;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'accounting_audit';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['total'], 'required'],
            [['branch_program_id', 'season_id', 'denomination_id', 'total'], 'integer'],
            [['datetime'], 'safe'],
            [['branch_program_id'], 'exist', 'skipOnError' => true, 'targetClass' => BranchProgram::className(), 'targetAttribute' => ['branch_program_id' => 'id']],
            [['season_id'], 'exist', 'skipOnError' => true, 'targetClass' => Season::className(), 'targetAttribute' => ['season_id' => 'id']],
            [['denomination_id'], 'exist', 'skipOnError' => true, 'targetClass' => Denomination::className(), 'targetAttribute' => ['denomination_id' => 'id']],
        ];
    }

    public function behaviors()
    {
        return [
            'bedezign\yii2\audit\AuditTrailBehavior'
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
            'season_id' => 'Season',
            'denomination_id' => 'Denomination ID',
            'total' => 'Total',
            'datetime' => 'Datetime',
        ];
    }

    public function getHiddenFormTokenField() {
        $token = \Yii::$app->getSecurity()->generateRandomString();
        $token = str_replace('+', '.', base64_encode($token));

        \Yii::$app->session->set(\Yii::$app->params['form_token_param'], $token);;
        return Html::hiddenInput(\Yii::$app->params['form_token_param'], $token);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBranchProgram()
    {
        return $this->hasOne(BranchProgram::className(), ['id' => 'branch_program_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSeason()
    {
        return $this->hasOne(Season::className(), ['id' => 'season_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDenomination()
    {
        return $this->hasOne(Denomination::className(), ['id' => 'denomination_id']);
    }
}
