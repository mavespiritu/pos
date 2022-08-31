<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yiister\gentelella\widgets\Panel;
/* @var $this yii\web\View */
/* @var $searchModel common\modules\accounting\models\BranchProgramEnhancementSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Enhancement Fees';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="branch-program-enhancement-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php Panel::begin(['header' => 'Branch - Program Enhancement']); ?>
        <?php if(Yii::$app->controller->action->id == 'update'){ ?>
            <?php Panel::begin(['header' => 'Branch - Program Enhancement Form']); ?>
                <?= $this->render('_form', ['model' => $model]) ?>
            <?php Panel::end(); ?>
        <?php } ?>

        <?php Panel::begin(['header' => 'Branch - Program Enhancement Form']); ?>
        <?php Pjax::begin(); ?>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],

                'branchProgramName',
                [
                    'attribute' => 'amount',
                    'value' => function($model){ return number_format($model->amount, 2); }
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{update}'
                ],
            ],
        ]); ?>
        <?php Pjax::end(); ?>
        <?php Panel::end(); ?>
    <?php Panel::end(); ?>
</div>
