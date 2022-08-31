<?php

namespace common\modules\accounting\models;

use Yii;

/**
 * This is the model class for table "accounting_package_type".
 *
 * @property int $id
 * @property int $enrolee_type_id
 * @property string $name
 *
 * @property AccountingPackage[] $accountingPackages
 * @property AccountingEnroleeType $enroleeType
 */
class PackageType extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'accounting_package_type';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['enrolee_type_id', 'name'], 'required'],
            [['enrolee_type_id'], 'integer'],
            [['name'], 'string', 'max' => 250],
            [['enrolee_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => EnroleeType::className(), 'targetAttribute' => ['enrolee_type_id' => 'id']],
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
            'enrolee_type_id' => 'Enrolee Type',
            'enroleeTypeName' => 'Enrolee Type',
            'name' => 'Name',
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
    public function getPackages()
    {
        return $this->hasMany(Package::className(), ['package_type_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEnroleeType()
    {
        return $this->hasOne(EnroleeType::className(), ['id' => 'enrolee_type_id']);
    }

    public function getEnroleeTypeName()
    {
        return $this->enroleeType ? $this->enroleeType->name : '';
    }
}
