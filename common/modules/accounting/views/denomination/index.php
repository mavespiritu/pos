<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\modules\accounting\models\DenominationSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Denominations';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="denomination-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Denomination', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'denomination',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
