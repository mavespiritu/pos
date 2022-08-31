<?php

namespace common\modules\pos\models;

use Yii;

/**
 * This is the model class for table "pos_enrolment".
 *
 * @property int $id
 * @property int $season_id
 * @property int $customer_id
 * @property int $product_id
 * @property int $enrolment_type_id
 * @property string $datetime
 *
 * @property PosDiscount[] $posDiscounts
 * @property PosCustomer $customer
 * @property PosProduct $product
 * @property PosSeason $season
 */
class PosEnrolment extends \yii\db\ActiveRecord
{
    public $branch_program_id;
    public $search_season_id;
    public $from_date;
    public $to_date;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pos_enrolment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['branch_program_id', 'search_season_id', 'from_date', 'to_date'], 'required', 'on' => 'reportEnrolment'],
            [['season_id', 'customer_id', 'product_id', 'enrolment_type_id', 'enrolment_date'], 'required'],
            [['search_season_id', 'season_id', 'customer_id', 'product_id', 'enrolment_type_id'], 'integer'],
            [['datetime'], 'safe'],
            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => PosCustomer::className(), 'targetAttribute' => ['customer_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => PosProduct::className(), 'targetAttribute' => ['product_id' => 'id']],
            [['season_id'], 'exist', 'skipOnError' => true, 'targetClass' => PosSeason::className(), 'targetAttribute' => ['season_id' => 'id']],
            [['enrolment_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => PosEnrolmentType::className(), 'targetAttribute' => ['enrolment_type_id' => 'id']],
            [['season_id'], 'required', 'when' => function ($model) {
                    return $model->branch_program_id != 0;
                }, 'whenClient' => "function (attribute, value) {
                return $('#posenrolment-branch_program_id').val() != 0;
            }"],
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
            'customer_id' => 'Customer',
            'customerName' => 'Customer',
            'product_id' => 'Product',
            'productName' => 'Product',
            'enrolment_type_id' => 'Enrolment Type',
            'enrolmentTypeName' => 'Enrolment Type',
            'enrolment_date' => 'Date of Enrolment',
            'datetime' => 'Datetime',
            'totalDue' => 'Total Due',
            'amountPaid' => 'Amount Paid',
            'status' => 'Status',
            'balance' => 'Balance',
            'branch_program_id' => 'Branch - Program',
            'from_date' => 'From',
            'to_date' => 'To',
            'search_season_id' => 'Season'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDiscount()
    {
        return $this->hasOne(PosDiscount::className(), ['enrolment_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(PosCustomer::className(), ['id' => 'customer_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(PosProduct::className(), ['id' => 'product_id']);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDropout()
    {
        return $this->hasOne(PosDropout::className(), ['enrolment_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSeason()
    {
        return $this->hasOne(PosSeason::className(), ['id' => 'season_id']);
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
    public function getIncomeItems()
    {
        return $this->hasMany(PosIncomeItem::className(), ['customer_id' => 'customer_id', 'product_id' => 'product_id']);
    }

    public function getSeasonName()
    {
        return $this->season ? $this->season->branchProgramName.' - SEASON '.$this->season->title : '';
    }

    public function getCustomerName()
    {
        return $this->customer ? $this->customer->fullName : '';
    }

    public function getEnrolmentTypeName()
    {
        return $this->enrolmentType ? $this->enrolmentType->title : '';
    }

    public function getProductName()
    {
        return $this->product ? $this->product->title.' - '.$this->product->amount : '';
    }

    public function getTotalDue()
    {
        $productAmount = $this->product ? $this->product->amount : 0;
        $discountAmount = $this->discount ? $this->discount->amount : 0;
        
        return ($productAmount - $discountAmount);
    }

    public function getAmountPaid()
    {
        $incomeItems = $this->incomeItems;
        $total = 0;

        if($incomeItems)
        {
            foreach($incomeItems as $incomeItem)
            {
                $total += $incomeItem->amount;
            }
        }

        return $total;
    }

    public function getBalance()
    {
        return ($this->totalDue - $this->amountPaid);
    }

    public function getStatus()
    {
        $due = $this->totalDue;
        $balance = $this->balance;
        $span = '';

        if($this->dropout){
            $span = '<span class="badge bg-red">'.$this->dropout->status.'</span>';
        }else if($due == $balance)
        {
            $span = '<span class="badge bg-red">Unpaid</span>';
        }else if($balance != 0){
            $span = $this->totalDue > 0 ? '<span class="badge bg-blue">Partial - '.number_format(($this->balance/$this->totalDue)*100, 0).'%</span>' : '<span class="badge bg-blue">Partial - 0%</span>';
        }else if($balance == 0){
            $span = '<span class="badge bg-green">Full</span>';
        }

        return $span;
    }
}
