<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use kartik\select2\Select2;
/* @var $this yii\web\View */
/* @var $searchModel common\modules\pos\models\PosBranchProgramSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Branch - Programs';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pos-branch-program-index">

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Branch - Program', ['create'], ['class' => 'btn btn-success']) ?>
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
            [
                'attribute' => 'programName',
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'program_id',
                    'data' => $programs,
                    'options' => ['placeholder' => 'Select One'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]),
            ],

            ['class' => 'yii\grid\ActionColumn', 'template' => '{update}{delete}'],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>
