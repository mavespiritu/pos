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
class Branch extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'branch';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['code', 'name'], 'required'],
            [['code', 'name'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Code',
            'name' => 'Name',
        ];
    }
}
