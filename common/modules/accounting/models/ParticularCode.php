<?php

namespace common\modules\accounting\models;

use Yii;

/**
 * This is the model class for table "accounting_particular_code".
 *
 * @property int $id
 * @property string $name
 * @property string $description
 *
 * @property AccountingBudgetProposalParticular[] $accountingBudgetProposalParticulars
 */
class ParticularCode extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'accounting_particular_code';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'string', 'max' => 5],
            [['description'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'description' => 'Description',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParticulars()
    {
        return $this->hasMany(Particular::className(), ['particular_code_id' => 'id']);
    }
}
