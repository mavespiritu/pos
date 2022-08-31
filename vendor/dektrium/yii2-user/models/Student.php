<?php

namespace common\modules\accounting\models;

use Yii;
use dektrium\user\models\User;
use dektrium\user\models\UserInfo;
/**
 * This is the model class for table "accounting_student".
 *
 * @property int $id
 * @property string $id_number
 * @property string $first_name
 * @property string $middle_name
 * @property string $last_name
 * @property int $school_id
 * @property int $year_graduated
 * @property string $permanent_address
 * @property string $contact_no
 * @property string $birthday
 * @property string $prc
 * @property string $email_address
 * @property string $status
 *
 * @property AccountingDiscount[] $accountingDiscounts
 * @property AccountingDropout[] $accountingDropouts
 * @property AccountingEnhancement[] $accountingEnhancements
 * @property AccountingIncomeEnrolment[] $accountingIncomeEnrolments
 * @property AccountingIncomeFreebiesAndIcons[] $accountingIncomeFreebiesAndIcons
 * @property AccountingPackageStudent[] $accountingPackageStudents
 * @property AccountingSchool $school
 * @property AccountingStudentProgram[] $accountingStudentPrograms
 * @property AccountingStudentTuition[] $accountingStudentTuitions
 * @property AccountingTransferee[] $accountingTransferees
 */
class Student extends \yii\db\ActiveRecord
{
    public $season_id;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'accounting_student';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_number', 'first_name', 'middle_name', 'last_name', 'school_id', 'year_graduated', 'contact_no', 'birthday', 'email_address', 'province_id', 'citymun_id'], 'required'],
            [['school_id', 'year_graduated'], 'integer'],
            [['permanent_address', 'status'], 'string'],
            [['email_address'], 'email'],
            [['birthday'], 'safe'],
            [['id_number'], 'string', 'max' => 15],
            [['first_name', 'middle_name', 'last_name', 'extension_name'], 'string', 'max' => 50],
            [['first_name'], 'match', 'pattern' => '/^[a-zA-Z\s]+$/','message' => 'First Name can only contain letters'],
            [['middle_name'], 'match', 'pattern' => '/^[a-zA-Z\s]+$/','message' => 'Middle Name can only contain letters'],
            [['last_name'], 'match', 'pattern' => '/^[a-zA-Z\s]+$/','message' => 'Last Name can only contain letters'],
            [['extension_name'], 'match', 'pattern' => '/^[a-zA-Z\s]+$/','message' => 'Extension Name can only contain letters'],
            [['contact_no'], 'string', 'max' => 11],
            [['prc'], 'string', 'max' => 30],
            [['school_id'], 'exist', 'skipOnError' => true, 'targetClass' => School::className(), 'targetAttribute' => ['school_id' => 'id']],
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
            'province_id' => 'Province',
            'citymun_id' => 'City/Municipality',
            'id_number' => 'Student ID Number',
            'first_name' => 'First Name',
            'middle_name' => 'Middle Name',
            'last_name' => 'Last Name',
            'extension_name' => 'Suffix',
            'school_id' => 'School',
            'year_graduated' => 'Year Graduated',
            'permanent_address' => 'Permanent Address',
            'contact_no' => 'Contact No',
            'birthday' => 'Birthday',
            'prc' => 'PRC Application Number',
            'email_address' => 'Email Address',
            'status' => 'Status',
            'season_id' => 'Season',
            'provinceName' => 'Province',
            'citymunName' => 'City/Municipality',
            'schoolName' => 'School',
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
    public function getProvince()
    {
        return $this->hasOne(Province::className(), ['province_c' => 'province_id']);
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
     * @return \yii\db\ActiveQuery
     */
    public function getCitymun(){
        return $this->hasOne(Citymun::className(), ['province_c' => 'province_id', 'citymun_c'=>'citymun_id']);
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
    
    public function getFullname()
    {
        return $this->first_name.' '.$this->middle_name.' '.$this->last_name.' '.$this->extension_name;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDiscounts()
    {
        return $this->hasMany(Discount::className(), ['student_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDropouts()
    {
        return $this->hasMany(Dropout::className(), ['student_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEnhancements()
    {
        return $this->hasMany(Enhancement::className(), ['student_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEnrolments()
    {
        return $this->hasMany(Enrolment::className(), ['student_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFreebieAndIcons()
    {
        return $this->hasMany(FreebieAndIcons::className(), ['student_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPackageStudents()
    {
        return $this->hasMany(PackageStudent::className(), ['student_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAdvanceEnrolments()
    {
        return $this->hasMany(AdvanceEnrolment::className(), ['student_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSchool()
    {
        return $this->hasOne(School::className(), ['id' => 'school_id']);
    }

    public function getSchoolName()
    {
        return $this->school ? $this->school->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStudentPrograms()
    {
        return $this->hasMany(StudentProgram::className(), ['student_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStudentBranchPrograms()
    {
        return $this->hasMany(StudentBranchProgram::className(), ['student_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStudentTuitions()
    {
        return $this->hasMany(StudentTuition::className(), ['student_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTransferees()
    {
        return $this->hasMany(Transferee::className(), ['student_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStudentEnroleeTypes()
    {
        return $this->hasMany(StudentEnroleeType::className(), ['student_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCoachings()
    {
        return $this->hasMany(Coaching::className(), ['student_id' => 'id']);
    }

    function generate($length)
    {
        $sets = [
            'abcdefghjkmnpqrstuvwxyz',
            'ABCDEFGHJKMNPQRSTUVWXYZ',
            '23456789',
        ];
        $all = '';
        $password = '';
        foreach ($sets as $set) {
            $password .= $set[array_rand(str_split($set))];
            $all .= $set;
        }

        $all = str_split($all);
        for ($i = 0; $i < $length - count($sets); $i++) {
            $password .= $all[array_rand($all)];
        }

        $password = str_shuffle($password);

        return $password;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($insert) {
            $password = $this->generate(8);
            $user = Yii::createObject([
                'class'    => User::className(),
                'scenario' => 'create',
                'username' => $this->id_number,
                'email'    => $this->email_address,
                'password' => $password,
                'password_hash' => Yii::$app->getSecurity()->generatePasswordHash($password),
                'auth_key' => Yii::$app->security->generateRandomString(),
                'confirmed_at' => strtotime(date("Y-m-d H:i:s")),
                'registration_ip' => '::1',
                'created_at' => strtotime(date("Y-m-d H:i:s")),
                'updated_at' => strtotime(date("Y-m-d H:i:s")),
                'flags' => 0,
                'last_login_at' => '',
            ]);

            if ($user->create()) {

                $findUser = User::findOne(['username' => $this->id_number]);

                if($findUser)
                {
                    $userInfo = UserInfo::findOne(['user_id' => $findUser->id]) ? UserInfo::findOne(['user_id' => $findUser->id]) : new UserInfo();
                    $userInfo->user_id = $findUser->id;
                    $userInfo->LAST_M = $this->last_name;
                    $userInfo->FIRST_M = $this->first_name;
                    $userInfo->MIDDLE_M = $this->middle_name;
                    $userInfo->SUFFIX = $this->extension_name;
                    $userInfo->BRANCH_C = Yii::$app->user->identity->userinfo->BRANCH_C;
                    $userInfo->MOBILEPHONE = $this->contact_no;
                    $userInfo->save();
                }

                $auth = Yii::$app->authManager;
                $item = $auth->getRole('Student');
                if($item){
                    $auth->assign($item, $findUser->id);
                }
                
            }
        }
    }
}
