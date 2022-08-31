<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yiister\gentelella\widgets\Panel;
/* @var $this yii\web\View */
/* @var $searchModel common\modules\accounting\models\TargetEnroleeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Target Enrollees';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="target-enrolee-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php Panel::begin(['header' => 'Target Enrolees']); ?>
    <div class="row">
        <div class="col-md-4">
            <?php Panel::begin(['header' => 'Target Enrolees Form']); ?>
                <?= $this->render('_form', [
                    'model' => $model,
                    'branches' => $branches,
                    'months' => $months,
                ]); ?>
            <?php Panel::end(); ?>
        </div>
        <div class="col-md-8">
            <?php Panel::begin(['header' => 'Target Enrolees List']); ?>
                <?php Pjax::begin(); ?>
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'columns' => [

                        'branchName',
                        'month',
                        'no_of_enrolee',

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
