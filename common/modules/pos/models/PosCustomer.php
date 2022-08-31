<?php

namespace common\modules\pos\models;

use Yii;

/**
 * This is the model class for table "pos_customer".
 *
 * @property int $id
 * @property string $id_number
 * @property string $province_id
 * @property string $citymun_id
 * @property int $school_id
 * @property string $first_name
 * @property string $middle_name
 * @property string $last_name
 * @property string $ext_name
 * @property int $year_graduated
 * @property string $address
 * @property string $contact_no
 * @property string $birthday
 * @property string $prc
 * @property string $email_address
 *
 * @property PosSchool $school
 * @property PosCustomerDropout[] $posCustomerDropouts
 * @property PosCustomerTransfer[] $posCustomerTransfers
 * @property PosDiscount[] $posDiscounts
 * @property PosIncomeItem[] $posIncomeItems
 */
class PosCustomer extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pos_customer';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['first_name', 'last_name', 'school_id', 'year_graduated', 'contact_no', 'birthday', 'email_address', 'province_id', 'citymun_id'], 'required'],
            [['school_id', 'year_graduated'], 'integer'],
            [['address', 'prc'], 'string'],
            [['birthday'], 'safe'],
            [['id_number'], 'string', 'max' => 100],
            [['province_id', 'citymun_id'], 'string', 'max' => 3],
            [['first_name', 'middle_name', 'last_name', 'email_address'], 'string', 'max' => 50],
            [['first_name'], 'match', 'pattern' => '/^[a-zA-Z\s-]+$/','message' => 'First Name can only contain letters'],
            [['middle_name'], 'match', 'pattern' => '/^[a-zA-Z\s-]+$/','message' => 'Middle Name can only contain letters'],
            [['last_name'], 'match', 'pattern' => '/^[a-zA-Z\s-]+$/','message' => 'Last Name can only contain letters'],
            [['ext_name'], 'match', 'pattern' => '/^[a-zA-Z\s.]+$/','message' => 'Extension Name can only contain letters'],
            [['email_address'], 'unique', 'message' => 'The email address has already been taken.'],
            [['prc'], 'unique', 'message' => 'The prc application number has already been taken.'],
            [['ext_name'], 'string', 'max' => 10],
            [['contact_no'], 'string', 'max' => 11],
            [['school_id'], 'exist', 'skipOnError' => true, 'targetClass' => PosSchool::className(), 'targetAttribute' => ['school_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_number' => 'ID No.',
            'province_id' => 'Province',
            'citymun_id' => 'City/Municipality',
            'school_id' => 'School',
            'first_name' => 'First Name',
            'middle_name' => 'Middle Name',
            'last_name' => 'Last Name',
            'ext_name' => 'Suffix',
            'year_graduated' => 'Year Graduated',
            'address' => 'House No/Street',
            'contact_no' => 'Mobile No.',
            'birthday' => 'Birthday',
            'prc' => 'PRC License No.',
            'email_address' => 'Email Address',
            'provinceName' => 'Province',
            'citymunName' => 'City/Municipality',
            'schoolName' => 'School',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSchool()
    {
        return $this->hasOne(PosSchool::className(), ['id' => 'school_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPosCustomerDropouts()
    {
        return $this->hasMany(PosCustomerDropout::className(), ['customer_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEnrolments()
    {
        return $this->hasMany(PosEnrolment::className(), ['customer_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPosCustomerTransfers()
    {
        return $this->hasMany(PosCustomerTransfer::className(), ['customer_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPosDiscounts()
    {
        return $this->hasMany(PosDiscount::className(), ['customer_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPosIncomeItems()
    {
        return $this->hasMany(PosIncomeItem::className(), ['customer_id' => 'id']);
    }

    public function getFullName()
    {
        return $this->first_name.' '.$this->middle_name.' '.$this->last_name.' '.$this->ext_name;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProvince()
    {
        return $this->hasOne(Province::className(), ['province_c' => 'province_id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCitymun(){
        return $this->hasOne(Citymun::className(), ['province_c' => 'province_id', 'citymun_c'=>'citymun_id']);
    }

    /**
     * @return string | Province Name
     */
    public function getProvinceName()
    {
        if($this->province){
            return $this->province->province_m;
        }else{
            return "";
        }
    }
    /**
     * @return string | Citymun Name
     */
    public function getCitymunName(){
        if($this->citymun){
            return $this->citymun->citymun_m;
        }else{
            return "";
        }
    }
    public function getSchoolName()
    {
        return $this->school ? $this->school->title : '';
    }
}
