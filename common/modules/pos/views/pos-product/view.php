<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\modules\pos\models\PosProduct */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pos-product-view">

    <h1><?= Html::encode($this->title) ?></h1>

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
            <h4>Product Details</h4>
             <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'seasonName',
                    'productTypeName',
                    'title',
                    'description:ntext',
                    [
                        'attribute' => 'amount',
                        'value' => function($model){ return number_format($model->amount, 2); }
                    ],
                ],
            ]) ?>
        </div>
        <div class="col-md-8 col-xs-12">
            <h4>Included Item/s</h4>
            <?= $this->render('_form-product-item', [
                'productItemModel' => $productItemModel,
                'items' => $items
            ]); ?>
            <table class="table table-condensed table-striped table-hover table-bordered">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Amount</th>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($productItems){ ?>
                        <?php foreach($productItems as $item){ ?>
                            <tr>
                                <td><?= $item->item->title ?></td>
                                <td><?= number_format($item->amount, 2) ?></td>
                                <td><?= Html::a('<i class="glyphicon glyphicon-pencil"></i>', ['/pos/pos-product/update-product-item', 'id' => $item->id]) ?>
                                <?= Html::a('<i class="glyphicon glyphicon-trash"></i>', ['/pos/pos-product/delete-product-item', 'id' => $item->id],[
                                        'data' => [
                                                'confirm' => 'Are you sure you want to delete this item?',
                                                'method' => 'post',
                                            ]
                                    ]) ?></td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>


</div>
