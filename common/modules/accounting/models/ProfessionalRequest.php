<?php

namespace common\modules\accounting\models;

use Yii;
use dektrium\user\models\UserInfo;
/**
 * This is the model class for table "accounting_professional_request".
 *
 * @property int $id
 * @property int $user_id
 * @property string $start_date
 * @property string $end_date
 * @property string $period_covered
 * @property string $bank
 * @property string $account_name
 * @property string $account_number
 * @property string $approval_status
 *
 * @property AccountingProfessionalRequestDetail[] $accountingProfessionalRequestDetails
 */
class ProfessionalRequest extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'accounting_professional_request';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['start_date', 'period_covered', 'bank', 'account_name', 'account_number'], 'required'],
            [['user_id'], 'integer'],
            [['start_date', 'end_date', 'datetime'], 'safe'],
            [['period_covered', 'bank', 'account_name', 'account_number', 'approval_status'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'start_date' => 'Date',
            'end_date' => 'End Date',
            'period_covered' => 'Period Covered',
            'bank' => 'Bank',
            'account_name' => 'Account Name',
            'account_number' => 'Account Number',
            'approval_status' => 'Approval Status',
            'datetime' => 'Datetime',
            'requester' => 'Requester',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProfessionalRequestDetails()
    {
        return $this->hasMany(ProfessionalRequestDetail::className(), ['professional_request_id' => 'id']);
    }

    public function getUserInfo()
    {
        return $this->hasOne(UserInfo::className(), ['user_id' => 'user_id']);
    }

    public function getRequester()
    {
        $requester = $this->userInfo;

        return $requester ? $requester->fullName : '';
    }
}
