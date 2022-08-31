<?php

namespace common\modules\pos\models;

use Yii;

/**
 * This is the model class for table "user_info".
 *
 * @property int $user_id
 * @property string $LAST_M
 * @property string $FIRST_M
 * @property string $MIDDLE_M
 * @property string $SUFFIX
 * @property int $BRANCH_C
 * @property int $SCHOOL_C
 * @property int $STUDENT_C
 * @property string $MOBILEPHONE
 *
 * @property User $user
 */
class UserInfo extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_info';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['BRANCH_C', 'SCHOOL_C', 'STUDENT_C'], 'integer'],
            [['LAST_M', 'FIRST_M', 'MIDDLE_M', 'SUFFIX'], 'string', 'max' => 255],
            [['MOBILEPHONE'], 'string', 'max' => 20],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'User ID',
            'LAST_M' => 'Last M',
            'FIRST_M' => 'First M',
            'MIDDLE_M' => 'Middle M',
            'SUFFIX' => 'Suffix',
            'BRANCH_C' => 'Branch C',
            'SCHOOL_C' => 'School C',
            'STUDENT_C' => 'Student C',
            'MOBILEPHONE' => 'Mobilephone',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
