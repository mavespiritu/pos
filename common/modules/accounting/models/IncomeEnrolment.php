<?php

namespace common\modules\accounting\models;

use Yii;

/**
 * This is the model class for table "accounting_income_enrolment".
 *
 * @property int $id
 * @property int $season_id
 * @property string $or
 * @property int $code_id
 * @property int $student_id
 * @property string $amount
 *
 * @property AccountingSeason $season
 * @property AccountingIncomeCode $code
 * @property AccountingStudent $student
 */
class IncomeEnrolment extends \yii\db\ActiveRecord
{
    public $seasons_id;
    public $frequency_id;
    public $page_id;
    public $date_id;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'accounting_income_enrolment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['seasons_id', 'frequency_id', 'date_id', 'page_id'], 'required', 'on' => 'searchIncomeEnrolment'],
            [['season_id', 'student_id', 'code_id', 'or_no', 'ar_no', 'amount'], 'required'],
            [['season_id', 'code_id', 'student_id'], 'integer'],
            [['amount'], 'number'],
            [['or_no', 'ar_no'], 'string', 'max' => 250],
            [['season_id'], 'exist', 'skipOnError' => true, 'targetClass' => Season::className(), 'targetAttribute' => ['season_id' => 'id']],
            [['code_id'], 'exist', 'skipOnError' => true, 'targetClass' => IncomeCode::className(), 'targetAttribute' => ['code_id' => 'id']],
            [['student_id'], 'exist', 'skipOnError' => true, 'targetClass' => Student::className(), 'targetAttribute' => ['student_id' => 'id']],
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
            'season_id' => 'Season',
            'seasonName' => 'Season',
            'or_no' => 'OR No.',
            'ar_no' => 'AR No.',
            'code_id' => 'Code',
            'codeName' => 'Code',
            'student_id' => 'Student',
            'studentName' => 'Student',
            'amount' => 'Amount',
            'amountType' => 'Amount Type',
            'datetime' => 'Date Of Transaction',
            'frequency_id' => 'Frequency',
            'date_id' => 'Date',
            'page_id' => 'Page',
            'seasons_id' => 'Season',
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
    public function getSeason()
    {
        return $this->hasOne(Season::className(), ['id' => 'season_id']);
    }

    public function getSeasonName()
    {
        return $this->season ? $this->season->seasonName : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCode()
    {
        return $this->hasOne(IncomeCode::className(), ['id' => 'code_id']);
    }

    public function getCodeName()
    {
        return $this->code? $this->code->name.' - '.$this->code->description : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStudent()
    {
        return $this->hasOne(Student::className(), ['id' => 'student_id']);
    }

    public function getStudentName()
    {
        return $this->student ? $this->student->id_number.' - '.$this->student->fullname : '';
    }

    public function getIncome()
    {
        return $this->hasOne(Income::className(), ['income_id' => 'id'])->where(['accounting_income.income_type_id' => '1']);
    }

    public function getAmountType()
    {
        $income = $this->getIncome()->where(['income_type_id' => '1'])->one();

        return  $income ? $income->amount_type : '';
    }

    public function getDatetime()
    {
        $income = $this->getIncome()->where(['income_type_id' => '1'])->one();
        
        return  $income ? $income->datetime : '';
    }

    public function getTransactionNumber()
    {
        $income = $this->getIncome()->where(['income_type_id' => '1'])->one();
        
        return  $income ? $income->transaction_number : '';
    }

    public static function getTotal($provider, $fieldName)
    {
        $total = 0;

        foreach ($provider as $item) {
            $total += $item[$fieldName];
        }

        return number_format($total, 2);
    }
}
