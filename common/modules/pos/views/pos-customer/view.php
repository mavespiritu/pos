<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\modules\pos\models\PosCustomer */

$this->title = 'Customer Profile';
$this->params['breadcrumbs'][] = ['label' => 'Customers', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pos-customer-view">

    <h1><?= Html::encode($model->fullName) ?>
        <p class="pull-right">
            <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary', 'data' => ['method' => 'post']]) ?>
            <?= Html::a('Delete', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'Are you sure you want to delete this item?',
                    'method' => 'post',
                ],
            ]) ?>
        </p>
    </h1>

    <div class="row">
        <div class="col-md-4 col-xs-12">
            <h4>Basic Information</h4>
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    //'id',
                    'id_number',
                    'provinceName',
                    'citymunName',
                    'schoolName',
                    'first_name',
                    'middle_name',
                    'last_name',
                    'ext_name',
                    'year_graduated',
                    'address:ntext',
                    'contact_no',
                    'birthday',
                    'prc:ntext',
                    'email_address:email',
                ],
            ]) ?>
        </div>
        <div class="col-md-8 col-xs-12">
            <h4>Transaction History</h4>
            <?php if($model->enrolments){ ?>
                <?php foreach($model->enrolments as $enrolment){ ?>
                    <div class="box box-danger">
                        <div class="box-header with-border">
                            <p style="font-size: 14px;"><b><?= $enrolment->seasonName ?></b></p>
                        </div>
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-4 col-xs-12">
                                    <h4>Enrolment Details</h4>
                                    <?= DetailView::widget([
                                        'model' => $enrolment,
                                        'attributes' => [
                                            'enrolmentTypeName',
                                            'productName',
                                            [
                                                'attribute' => 'totalDue',
                                                'format' => 'raw',
                                                'value' => function($model){
                                                    return number_format($model->totalDue, 2);
                                                }
                                            ],
                                            [
                                                'attribute' => 'amountPaid',
                                                'format' => 'raw',
                                                'value' => function($model){
                                                    return number_format($model->amountPaid, 2);
                                                }
                                            ],
                                            [
                                                'attribute' => 'balance',
                                                'format' => 'raw',
                                                'value' => function($model){
                                                    return number_format($model->balance, 2);
                                                }
                                            ],
                                            [
                                                'attribute' => 'status',
                                                'format' => 'raw'
                                            ],
                                        ],
                                    ]) ?>
                                </div>
                                <div class="col-md-8 col-xs-12">
                                    <h4>Payment History</h4>
                                    <table class="table table-striped table-hover table-condensed table-bordered">
                                        <thead>
                                            <tr>
                                                <th>OR No.</th>
                                                <th>AR No.</th>
                                                <th>Amount Paid</th>
                                                <th>Amount Type</th>
                                                <th>Invoice Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if($enrolment->getIncomeItems()->orderBy(['id' => SORT_DESC])->all()){ ?>
                                                <?php foreach($enrolment->getIncomeItems()->orderBy(['id' => SORT_DESC])->all() as $item){ ?>
                                                    <tr>
                                                        <td><?= $item->income->official_receipt_id ?></td>
                                                        <td><?= $item->income->ar_number ?></td>
                                                        <td><?= number_format($item->amount, 2) ?></td>
                                                        <td><?= $item->income->amountTypeName ?></td>
                                                        <td><?= $item->income->invoice_date ?></td>
                                                    </tr>
                                                <?php } ?>
                                            <?php }else{ ?>
                                                <tr>
                                                    <td colspan=5>No payment recorded.</td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            <?php } ?>
        </div>
    </div>
</div>

