<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\modules\pos\models\PosEnrolment */
?>
<div class="pos-enrolment-view">

    <p><b>Product Details</b></p>
    <div class="box box-solid">
        <div class="box-body">
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'title',
                    'description',
                    'productTypeName',
                    'incomeTypeName',
                    ['attribute' => 'amount', 'value' => function($data){ return number_format($data->amount, 2); }]
                ],
            ]) ?>
            <br>
            <br>
            <p><b>Item/s included</b></p>
            <?php if($model->productItems){ ?>
                <ul>
                <?php foreach($model->productItems as $item){ ?>
                    <li><?= $item->item->title ?> - <?= number_format($item->amount, 2) ?></li>
                <?php } ?>
                </ul>
            <?php } ?>
        </div>
    </div>
</div>
