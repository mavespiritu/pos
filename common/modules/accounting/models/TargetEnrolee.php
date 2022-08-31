<?php

namespace common\modules\accounting\models;

use Yii;

/**
 * This is the model class for table "accounting_target_enrolee".
 *
 * @property int $id
 * @property int $branch_id
 * @property string $month
 * @property int $no_of_enrolee
 *
 * @property AccountingBranch $branch
 */
class TargetEnrolee extends \yii\db\ActiveRecord
{
    public $year;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'accounting_target_enrolee';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['branch_id', 'month', 'year', 'no_of_enrolee'], 'required'],
            [['branch_id', 'no_of_enrolee'], 'integer'],
            [['month'], 'safe'],
            [['branch_id'], 'exist', 'skipOnError' => true, 'targetClass' => Branch::className(), 'targetAttribute' => ['branch_id' => 'id']],
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
            'branch_id' => 'Branch',
            'branchName' => 'Branch',
            'month' => 'Month',
            'no_of_enrolee' => 'No. Of Enrollees',
            'year' => 'Year'
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
    public function getBranch()
    {
        return $this->hasOne(Branch::className(), ['id' => 'branch_id']);
    }

    public function getBranchName()
    {
        return $this->branch ? $this->branch->name : '';
    }
}
