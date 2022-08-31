<?php

namespace common\modules\accounting\models;

use Yii;

/**
 * This is the model class for table "accounting_package_freebie".
 *
 * @property int $id
 * @property int $package_id
 * @property int $freebie_id
 * @property string $amount
 *
 * @property AccountingPackage $package
 * @property AccountingFreebie $freebie
 */
class PackageFreebie extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'accounting_package_freebie';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['package_id','freebie_id', 'amount'], 'required'],
            [['package_id', 'freebie_id'], 'integer'],
            [['amount'], 'number'],
            [['package_id'], 'exist', 'skipOnError' => true, 'targetClass' => Package::className(), 'targetAttribute' => ['package_id' => 'id']],
            [['freebie_id'], 'exist', 'skipOnError' => true, 'targetClass' => Freebie::className(), 'targetAttribute' => ['freebie_id' => 'id']],
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
            'package_id' => 'Package ID',
            'freebie_id' => 'Freebie ID',
            'amount' => 'Amount',
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
    public function getPackage()
    {
        return $this->hasOne(Package::className(), ['id' => 'package_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFreebie()
    {
        return $this->hasOne(Freebie::className(), ['id' => 'freebie_id']);
    }
}
