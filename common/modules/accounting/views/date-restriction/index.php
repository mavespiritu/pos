<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yiister\gentelella\widgets\Panel;
/* @var $this yii\web\View */
/* @var $searchModel common\modules\accounting\models\DateRestrictionSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Date Restrictions';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="date-restriction-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php Panel::begin(['header' => 'Date Restriction']); ?>
        <?php if(Yii::$app->controller->action->id == 'update'){ ?>
            <?php Panel::begin(['header' => 'Date Restriction Form']); ?>
                <?= $this->render('_form', ['model' => $model]) ?>
            <?php Panel::end(); ?>
        <?php } ?>
        <?php Panel::begin(['header' => 'Date Restriction List']); ?>
            <?php Pjax::begin(); ?>
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],

                    'branchName',
                    'allow',
                    'start_date',
                    'end_date',
                    'open_type',

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
