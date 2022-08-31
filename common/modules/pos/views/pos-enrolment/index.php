<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use kartik\select2\Select2;
/* @var $this yii\web\View */
/* @var $searchModel common\modules\pos\models\PosEnrolmentSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Enrolment';
$this->params['breadcrumbs'][] = $this->title;

$customersurl = \yii\helpers\Url::to(['/pos/pos-income/customer-list']);
$productsurl = \yii\helpers\Url::to(['/pos/pos-enrolment/product-list']);
?>
<div class="pos-enrolment-index">

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Enrolment', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'status',
                'format' => 'raw'
            ],
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
                                    $("#posenrolmentsearch-customer_id").html("").select2({ data:result, theme:"krajee", width:"100%",placeholder:"Select One",
                                        allowClear: true,});
                                    $("#posenrolmentsearch-customer_id").select2("val","");
                                });

                                $.ajax({
                                    url: "'.$productsurl.'",
                                    data: {season:vals}
                                    
                                }).done(function(result) {
                                    var h;
                                    $("#posenrolmentsearch-product_id").html("").select2({ data:result, theme:"krajee", width:"100%",placeholder:"Select One",
                                        allowClear: true,});
                                    $("#posenrolmentsearch-product_id").select2("val","");
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
            [
                'attribute' => 'enrolmentTypeName',
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'enrolment_type_id',
                    'data' => $enrolmentTypes,
                    'options' => ['placeholder' => 'Select One'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]),
            ],
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
            'enrolment_date',
            ['class' => 'yii\grid\ActionColumn', 'template' => '{view}{update}{delete}'],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>
