<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\modules\pos\models\PosEnrolment */

$this->title = 'Enrolment Details';
$this->params['breadcrumbs'][] = ['label' => 'Enrolments', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pos-enrolment-view">

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
    <div class="row">
    <div class="col-md-4 col-xs-12">
        <h4>Enrolment Details</h4>
        <?= DetailView::widget([
            'model' => $model,
            'attributes' => [
                'seasonName',
                'customerName',
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
                'enrolment_date'
            ],
        ]) ?>
        <?php if($model->dropout){ ?>
            <h4>Dropout Details</h4>
            <?= DetailView::widget([
                'model' => $model->dropout,
                'attributes' => [
                    'status',
                    'remarks',
                    'droppedBy',
                    'date_processed',
                ],
            ]) ?>
        <?php } ?>
        <div class="row">
            <div class="col-md-6 col-xs-12">
                <?= Html::a('Transfer', ['transfer', 'id' => $model->id], ['class' => 'btn btn-info btn-block', 'data' => ['method' => 'post']]) ?>
            </div>
            <div class="col-md-6 col-xs-12">
                <?= $model->dropout ? Html::a('Undo the Drop/Refund', ['undo-drop', 'id' => $model->id], ['class' => 'btn btn-info btn-warning btn-block', 'data' => ['method' => 'post']]) : Html::a('Drop/Refund', ['drop', 'id' => $model->id], ['class' => 'btn btn-info btn-warning btn-block', 'data' => ['method' => 'post']]) ?>
            </div>
        </div>
    </div>
    <div class="col-md-8 col-xs-12">
        <?php if($model->dropout){ ?>
            <div class="alert alert-danger">
                <h4><i class="icon fa fa-ban"></i> This enrolment is dropped</h4>
                The enrolment has been dropped by <?= $model->dropout->droppedBy ?> last <?= date("F j, Y", strtotime($model->dropout->date_processed)) ?>
              </div>
        <?php } ?>
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
                <?php if($model->getIncomeItems()->orderBy(['id' => SORT_DESC])->all()){ ?>
                    <?php foreach($model->getIncomeItems()->orderBy(['id' => SORT_DESC])->all() as $item){ ?>
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
