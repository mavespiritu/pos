<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yiister\gentelella\widgets\Panel;
/* @var $this yii\web\View */
/* @var $searchModel common\modules\accounting\models\SeasonSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Seasons';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="season-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php Panel::begin(['header' => 'Seasons']); ?>
    <div class="row">
        <div class="col-md-4">
            <?php Panel::begin(['header' => 'Season Form']); ?>
                <?= $this->render('_form', [
                    'model' => $model,
                    'branchPrograms' => $branchPrograms,
                ]); ?>  
            <?php Panel::end(); ?>
        </div>
        <div class="col-md-8">
            <?php Panel::begin(['header' => 'Season List']); ?>
                <?php Pjax::begin(); ?>
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'columns' => [

                        'branchProgramName',
                        'name',
                        'start_date',
                        'end_date',
                        'or_start',
                        'no_of_pieces',

                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{update}{delete}'
                        ],
                    ],
                ]); ?>
                <?php Pjax::end(); ?>
            <?php Panel::end(); ?>
        </div>
    </div>   
    <?php Panel::end(); ?> 
</div>
