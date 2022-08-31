<?php

namespace common\modules\accounting\models;

use Yii;

/**
 * This is the model class for table "accounting_denomination".
 *
 * @property int $id
 * @property string $denomination
 *
 * @property AccountingAudit[] $accountingAudits
 */
class Denomination extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'accounting_denomination';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['denomination'], 'string', 'max' => 50],
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
            'denomination' => 'Denomination',
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
    public function getAudits()
    {
        return $this->hasMany(Audit::className(), ['denomination_id' => 'id']);
    }
}
