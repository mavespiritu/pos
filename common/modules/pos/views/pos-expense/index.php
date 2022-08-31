<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use kartik\select2\Select2;
/* @var $this yii\web\View */
/* @var $searchModel common\modules\pos\models\PosExpenseSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Expenses';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pos-expense-index">

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Expense', ['create'], ['class' => 'btn btn-success']) ?>
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
                ]),
            ],
            [
                'attribute' => 'vendorName',
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'vendor_id',
                    'data' => $vendors,
                    'options' => ['placeholder' => 'Select One'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]),
            ],
            'voucher_no',
            [
                'attribute' => 'totalAmount',
                'value' => function($model){ return number_format($model->totalAmount, 2); }
            ],
            [
                'attribute' => 'accountName',
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'account_id',
                    'data' => $accounts,
                    'options' => ['placeholder' => 'Select One'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]),
            ],
            [
                'attribute' => 'amountTypeName',
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'amount_type_id',
                    'data' => $amountTypes,
                    'options' => ['placeholder' => 'Select One'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]),
            ],
            'expense_date',
            //'datetime',
            //'status',

            ['class' => 'yii\grid\ActionColumn', 'template' => '{view}{update}{delete}'],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>
