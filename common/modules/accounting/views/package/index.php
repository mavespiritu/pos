<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yiister\gentelella\widgets\Panel;
/* @var $this yii\web\View */
/* @var $searchModel common\modules\accounting\models\PackageSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Packages';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="package-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php Panel::begin(['header' => 'Packages']); ?>
        <?php Panel::begin(['header' => 'Package Form']); ?>
        <?= $this->render('_form', [
            'model' => $model,
            'branchPrograms' => $branchPrograms,
            'seasons' => $seasons,
            'packageTypes' => $packageTypes,
            'freebies' => $freebies,
            'packageFreebies' => $packageFreebies
        ]); ?>
        <?php Panel::end(); ?>

        <?php Panel::begin(['header' => 'Package List']); ?>
        <?php Pjax::begin(); ?>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],

                'branchProgramName',
                'seasonName',
                'packageTypeName',
                'tier',
                'code',
                [
                    'attribute' => 'amount',
                    'value' => function($model){ return number_format($model->amount, 2); }
                ],

                [
                    'class' => 'yii\grid\ActionColumn',
                ],
            ],
        ]); ?>
        <?php Pjax::end(); ?>
        <?php Panel::end(); ?>
    <?php Panel::end(); ?>
</div>
