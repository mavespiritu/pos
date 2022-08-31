<?php

namespace dektrium\user\models;

use Yii;

/**
 * This is the model class for table "user_info".
 *
 * @property integer $user_id
 * @property integer $EMP_N
 * @property string $LAST_M
 * @property string $FIRST_M
 * @property string $MIDDLE_M
 * @property string $SUFFIX
 * @property string $BIRTH_D
 * @property string $SEX_C
 * @property integer $OFFICE_C
 * @property integer $DIVISION_C
 * @property integer $SECTION_C
 * @property integer $POSITION_C
 * @property integer $DESIGNATION
 * @property string $REGION_C
 * @property string $PROVINCE_C
 * @property string $CITYMUN_C
 * @property string $MOBILEPHONE
 * @property string $LANDPHONE
 * @property string $FAX_NO
 * @property string $EMAIL
 * @property string $PHOTO
 * @property string $ALTER_EMAIL
 *
 * @property Tbldesignation $dESIGNATION
 * @property Tbldilgposition $pOSITIONC
 * @property Tbloffice $oFFICEC
 * @property Tblprovince $pROVINCEC
 * @property Tblregion $rEGIONC
 * @property User $user
 */
class UserInfo extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_info';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['LAST_M', 'FIRST_M', 'MIDDLE_M', 'BRANCH_C'], 'required'],
            [['BRANCH_C', 'SCHOOL_C', 'STUDENT_C'], 'integer'],
            [['BIRTH_D','EMP_N'], 'safe'],
            [['LAST_M', 'FIRST_M', 'MIDDLE_M', 'SUFFIX'], 'string', 'max' => 50],
            [['MOBILEPHONE'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'User ID',
            'LAST_M' => 'Last  Name',
            'FIRST_M' => 'First  Name',
            'MIDDLE_M' => 'Middle  Name',
            'SUFFIX' => 'Extension Name',
            'BRANCH_C' => 'Branch',
            'SCHOOL_C' => 'School',
            'STUDENT_C' => 'Student',
            'MOBILEPHONE' => 'Mobile No.',
        ];
    }


    /**
     * @return Full name of User
     */

    public function getFullName(){
        return $this->FIRST_M." ".$this->MIDDLE_M." ".$this->LAST_M." ".$this->SUFFIX;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBranch()
    {
        return $this->hasOne(Branch::className(), ['id' => 'BRANCH_C']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSchool()
    {
        return $this->hasOne(School::className(), ['id' => 'SCHOOL_C']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStudent()
    {
        return $this->hasOne(School::className(), ['id' => 'STUDENT_C']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
