<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yiister\gentelella\widgets\Panel;
/* @var $this yii\web\View */
/* @var $searchModel common\modules\accounting\models\SeasonOrListSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Season ORs';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="season-or-list-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php Panel::begin(['header' => 'Season ORs']); ?>
    <div class="row">
        <div class="col-md-4">
            <?php Panel::begin(['header' => 'Season OR Form']); ?>
                <?= $this->render('_form', [
                    'model' => $model,
                    'seasons' => $seasons,
                ]); ?>
            <?php Panel::end(); ?>
        </div>
        <div class="col-md-8">
            <?php Panel::begin(['header' => 'Season OR List']); ?>
                <?php Pjax::begin(); ?>
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'columns' => [

                        'seasonName',
                        'or_start',
                        'no_of_pieces',

                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{delete}'
                        ],
                    ],
                ]); ?>
                <?php Pjax::end(); ?>
            <?php Panel::end(); ?>
        </div>
    </div>   
    <?php Panel::end(); ?> 
</div>
