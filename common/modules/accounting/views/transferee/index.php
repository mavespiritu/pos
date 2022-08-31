<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yiister\gentelella\widgets\Panel;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel common\modules\accounting\models\TransfereeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Transferees';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="transferee-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php Panel::begin(['header' => 'Transferees']) ?>
    <?php Pjax::begin() ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'studentName',
            'fromSeasonName',
            'toSeasonName',

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => "{view}"
            ],
        ],
    ]); ?>
    <?php Pjax::end() ?>
    <?php Panel::end() ?>
</div>
