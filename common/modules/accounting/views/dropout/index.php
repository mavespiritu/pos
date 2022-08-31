<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yiister\gentelella\widgets\Panel;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel common\modules\accounting\models\DropoutSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Dropouts';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="dropout-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php Panel::begin(['header' => 'Dropout List']) ?>
    <?php Pjax::begin() ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'seasonName',
            'studentName',
            'drop_date',
            [
                'attribute' => 'reason',
                'value' => function($model){ return Html::encode($model->reason); },
            ],
            'authorized_by',

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{delete}'
            ],
        ],
    ]); ?>
    <?php Pjax::end() ?>
    <?php Panel::end() ?>
</div>
