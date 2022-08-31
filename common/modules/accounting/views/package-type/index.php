<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel common\modules\accounting\models\PackageTypeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Package Types';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="package-type-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?= $this->render('_form', [
        'model' => $model,
        'enroleeTypes' => $enroleeTypes,
    ]); ?>
    <?php Pjax::begin() ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'enroleeTypeName',
            'name',

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update}{delete}'
            ],
        ],
    ]); ?>
    <?php Pjax::end() ?>
</div>
