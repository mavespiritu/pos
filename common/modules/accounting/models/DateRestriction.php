<?php

namespace common\modules\accounting\models;

use Yii;

/**
 * This is the model class for table "accounting_date_restriction".
 *
 * @property int $id
 * @property int $branch_id
 * @property string $allow
 * @property string $start_date
 * @property string $end_date
 *
 * @property AccountingBranch $branch
 */
class DateRestriction extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'accounting_date_restriction';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['branch_id','allow', 'open_type'], 'required'],
            [['start_date', 'end_date'], 'required', 'when' => function ($model) {
                return $model->allow == 'Yes';
            }, 'whenClient' => "function (attribute, value) {
                return $('#daterestriction-allow').val() == 'Yes';
            }"],
            [['branch_id'], 'integer'],
            [['allow'], 'string'],
            [['start_date', 'end_date', 'open_type'], 'safe'],
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
            'allow' => 'Allow',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'open_type' => 'Open Date Field to'
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
