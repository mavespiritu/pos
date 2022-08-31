<?php

namespace common\modules\accounting\models;

use Yii;

/**
 * This is the model class for table "accounting_dropout".
 *
 * @property int $id
 * @property int $season_id
 * @property int $student_id
 * @property string $drop_date
 * @property string $reason
 * @property string $authorized_by
 *
 * @property AccountingSeason $season
 * @property AccountingStudent $student
 */
class Dropout extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'accounting_dropout';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['reason', 'authorized_by'], 'required'],
            [['season_id', 'student_id'], 'integer'],
            [['drop_date'], 'safe'],
            [['reason'], 'string'],
            [['authorized_by'], 'string', 'max' => 250],
            [['season_id'], 'exist', 'skipOnError' => true, 'targetClass' => Season::className(), 'targetAttribute' => ['season_id' => 'id']],
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
            'season_id' => 'Season ID',
            'seasonName' => 'Season',
            'student_id' => 'Student ID',
            'studentName' => 'Student',
            'drop_date' => 'Drop Date',
            'reason' => 'Reason',
            'authorized_by' => 'Authorized By',
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStudent()
    {
        return $this->hasOne(Student::className(), ['id' => 'student_id']);
    }

    public function getSeasonName()
    {
        return $this->season ? $this->season->seasonName : '';
    }
    
    public function getStudentName()
    {
        return $this->student ? $this->student->id_number.' - '.$this->student->fullname : '';
    }
}
