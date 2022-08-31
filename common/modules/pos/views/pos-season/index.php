<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use kartik\select2\Select2;
/* @var $this yii\web\View */
/* @var $searchModel common\modules\pos\models\PosSeasonSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Seasons';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pos-season-index">

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Yii::$app->user->identity->userinfo->BRANCH_C == "" ? Html::a('Create Season', ['create'], ['class' => 'btn btn-success']) : '' ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'branchProgramName',
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'branch_program_id',
                    'data' => $branchPrograms,
                    'options' => ['placeholder' => 'Select One'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]),
            ],
            'title',
            'start_date',
            'end_date',
            [
                'attribute' => 'status',
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'status',
                    'data' => ['Active' => 'Active', 'Archived' => 'Archived', 'Inactive' => 'Inactive'],
                    'options' => ['placeholder' => 'Select One'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]),
            ],

            ['class' => 'yii\grid\ActionColumn', 'template' => Yii::$app->user->identity->userinfo->BRANCH_C != "" ? '{view}{update}' : '{view}{update}{delete}'],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>
