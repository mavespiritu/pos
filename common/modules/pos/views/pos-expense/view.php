<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\modules\pos\models\PosExpense */

$this->title = 'Voucher No: '.$model->voucher_no;
$this->params['breadcrumbs'][] = ['label' => 'Expenses', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pos-expense-view">

    <p>
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
            <h4>Expense Details</h4>
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    //'id',
                    'seasonName',
                    'vendorName',
                    'voucher_no',
                    [
                        'attribute' => 'totalAmount',
                        'value' => function($model){ return number_format($model->totalAmount, 2); }
                    ],
                    'accountName',
                    'amountTypeName',
                    'transaction_no',
                    'expense_date',
                    //'datetime',
                ],
            ]) ?>
        </div>
        <div class="col-md-8 col-xs-12">
            <h4>Particulars</h4>
            <?= $this->render('_form-expense-item', [
                'expenseItemModel' => $expenseItemModel,
                'expenseTypes' => $expenseTypes
            ]); ?>
            <table class="table table-condensed table-striped table-hover table-bordered">
                <thead>
                    <tr>
                        <th>Expense Type</th>
                        <th>Description</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($expenseItems){ ?>
                        <?php foreach($expenseItems as $item){ ?>
                            <tr>
                                <td><?= $item->expenseType->title ?></td>
                                <td><?= $item->description ?></td>
                                <td><?= $item->quantity ?></td>
                                <td><?= number_format($item->amount, 2) ?></td>
                                <td><?= Html::a('<i class="glyphicon glyphicon-pencil"></i>', ['/pos/pos-expense/update-expense-item', 'id' => $item->id]) ?>
                                <?= Html::a('<i class="glyphicon glyphicon-trash"></i>', ['/pos/pos-expense/delete-expense-item', 'id' => $item->id],[
                                        'data' => [
                                                'confirm' => 'Are you sure you want to delete this item?',
                                                'method' => 'post',
                                            ]
                                    ]) ?></td>
                            </tr>
                        <?php } ?>
                    <?php }else{ ?>
                        <tr>
                            <td colspan="5">No particulars found.</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
