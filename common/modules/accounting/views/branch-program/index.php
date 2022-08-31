<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yiister\gentelella\widgets\Panel;
/* @var $this yii\web\View */
/* @var $searchModel common\modules\accounting\models\BranchProgramSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Branch - Program Setup';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="branch-program-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php Panel::begin(['header' => 'Branch - Program Setup']); ?>
    <div class="row">
        <div class="col-md-4">
            <?php Panel::begin(['header' => 'Branch - Program Form']); ?>
                <?= $this->render('_form', [
                    'model' => $model,
                    'branches' => $branches,
                    'programs' => $programs,
                ]); ?>
            <?php Panel::end(); ?>
        </div>
        <div class="col-md-8">
            <?php Panel::begin(['header' => 'Branch - Program List']); ?>
                <?php Pjax::begin(); ?>
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'columns' => [

                        'branchName',
                        'programName',

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
