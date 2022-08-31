<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use kartik\select2\Select2;
/* @var $this yii\web\View */
/* @var $searchModel common\modules\pos\models\PosBacktrackSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Backtrack';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pos-backtrack-index">

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Backtrack', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'branchName',
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'branch_id',
                    'data' => $branches,
                    'options' => ['placeholder' => 'Select One'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]),
            ],
            'date_from',
            'date_to',
            [
                'attribute' => 'field',
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'field',
                    'data' => ['Income' => 'Income', 'Expense' => 'Expense'],
                    'options' => ['placeholder' => 'Select One'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]),
            ],

            ['class' => 'yii\grid\ActionColumn', 'template' => '{update}'],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>
