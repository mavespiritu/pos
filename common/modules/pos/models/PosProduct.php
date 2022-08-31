<?php

namespace common\modules\pos\models;

use Yii;

/**
 * This is the model class for table "pos_product".
 *
 * @property int $id
 * @property int $season_id
 * @property int $enrolment_type_id
 * @property int $product_type_id
 * @property string $title
 * @property string $description
 * @property double $amount
 *
 * @property PosIncomeItem[] $posIncomeItems
 * @property PosEnrolmentType $enrolmentType
 * @property PosProductType $productType
 * @property PosSeason $season
 */
class PosProduct extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pos_product';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['season_id', 'income_type_id', 'product_type_id', 'title', 'description', 'amount'], 'required'],
            [['season_id', 'enrolment_type_id', 'product_type_id', 'income_type_id'], 'integer'],
            [['description'], 'string'],
            [['amount'], 'number'],
            [['title'], 'string', 'max' => 100],
            [['enrolment_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => PosEnrolmentType::className(), 'targetAttribute' => ['enrolment_type_id' => 'id']],
            [['product_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => PosProductType::className(), 'targetAttribute' => ['product_type_id' => 'id']],
            [['season_id'], 'exist', 'skipOnError' => true, 'targetClass' => PosSeason::className(), 'targetAttribute' => ['season_id' => 'id']],
            [['income_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => PosIncomeType::className(), 'targetAttribute' => ['income_type_id' => 'id']],
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
            'enrolment_type_id' => 'Enrolment Type',
            'product_type_id' => 'Product Type',
            'income_type_id' => 'Income Type',
            'title' => 'Title',
            'description' => 'Description',
            'amount' => 'Amount',
            'seasonName' => 'Season',
            'productTypeName' => 'Product Type',
            'incomeTypeName' => 'Income Type',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPosIncomeItems()
    {
        return $this->hasMany(PosIncomeItem::className(), ['product_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductItems()
    {
        return $this->hasMany(PosProductItem::className(), ['product_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEnrolmentType()
    {
        return $this->hasOne(PosEnrolmentType::className(), ['id' => 'enrolment_type_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductType()
    {
        return $this->hasOne(PosProductType::className(), ['id' => 'product_type_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIncomeType()
    {
        return $this->hasOne(PosIncomeType::className(), ['id' => 'income_type_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSeason()
    {
        return $this->hasOne(PosSeason::className(), ['id' => 'season_id']);
    }

    public function getSeasonName()
    {
        return $this->season ? $this->season->branchProgramName.' - SEASON '.$this->season->title : '';
    }

    public function getProductTypeName()
    {
        return $this->productType ? $this->productType->title : '';
    }

    public function getIncomeTypeName()
    {
        return $this->incomeType ? $this->incomeType->title : '';
    }
}
