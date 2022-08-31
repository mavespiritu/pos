<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel common\modules\accounting\models\IncomeCodeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Income Codes';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="income-code-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?= $this->render('_form', [
        'model' => $model,
        'incomeTypes' => $incomeTypes
    ]); ?>

    <?php Pjax::begin(); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'incomeTypeName',
            'name',
            'description',

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update}{delete}'
            ],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>
