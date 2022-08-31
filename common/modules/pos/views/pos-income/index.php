<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use kartik\select2\Select2;
/* @var $this yii\web\View */
/* @var $searchModel common\modules\pos\models\PosIncomeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Income';
$this->params['breadcrumbs'][] = $this->title;
$customersurl = \yii\helpers\Url::to(['/pos/pos-income/customer-list']);
$productsurl = \yii\helpers\Url::to(['/pos/pos-income/product-list']);
?>
<div class="pos-income-index">

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Income', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            //'id',
            [
                'attribute' => 'seasonName',
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'season_id',
                    'data' => $seasons,
                    'options' => ['placeholder' => 'Select One'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                    'pluginEvents'=>[
                        'select2:select'=>'
                            function(){
                                var vals = this.value;
                                $.ajax({
                                    url: "'.$customersurl.'",
                                    data: {season:vals}
                                    
                                }).done(function(result) {
                                    var h;
                                    $("#posincomesearch-customer_id").html("").select2({ data:result, theme:"krajee", width:"100%",placeholder:"Select One",
                                        allowClear: true,});
                                    $("#posincomesearch-customer_id").select2("val","");
                                });

                                $.ajax({
                                    url: "'.$productsurl.'",
                                    data: {season:vals}
                                    
                                }).done(function(result) {
                                    var h;
                                    $("#posincomesearch-product_id").html("").select2({ data:result, theme:"krajee", width:"100%",placeholder:"Select One",
                                        allowClear: true,});
                                    $("#posincomesearch-product_id").select2("val","");
                                });
                            }'
                        ],
                    ]),
            ],
            [
                'attribute' => 'customerName',
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'customer_id',
                    'data' => $customers,
                    'options' => ['placeholder' => 'Select One'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]),
            ],
            [
                'attribute' => 'productName',
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'product_id',
                    'data' => $products,
                    'options' => ['placeholder' => 'Select One'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]),
            ],
            'official_receipt_id',
            [
                'attribute' => 'amount',
                'value' => function($model){ return number_format($model->amount, 2); }
            ],
            'invoice_date',
            [
                'attribute' => 'status',
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'status',
                    'data' => ['Active' => 'Active', 'Void' => 'Void'],
                    'options' => ['placeholder' => 'Select One'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]),
            ],
            [
                'format' => 'raw',
                'value' => function($model){
                    $content = '';
                    $content.= Html::a('<i class="glyphicon glyphicon-pencil"></i>',['/pos/pos-income/update', 'id' => $model->id],['data' => ['method' => 'post']]);
                    $content.= '&nbsp;&nbsp;&nbsp;';
                    $content.= Yii::$app->user->identity->userinfo->BRANCH_C == "" ? $model->status == 'Active' ? 
                        Html::a('<i class="fa fa-thumbs-o-down"></i>',['/pos/pos-income/void', 'id' => $model->id],['data' => ['method' => 'post', 'confirm' => 'Are you sure you want to void this item?']]) : 
                        Html::a('<i class="fa fa-thumbs-o-up"></i>',['/pos/pos-income/activate', 'id' => $model->id],['data' => ['method' => 'post', 'confirm' => 'Are you sure you want to re-activate this item?']]) : '';

                    return $content;
                }
            ],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>
