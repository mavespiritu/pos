<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\modules\pos\models\PosSeason */

$this->title = 'SEASON '.$model->title;
$this->params['breadcrumbs'][] = ['label' => 'Seasons', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pos-season-view">
    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <div class="row">
        <div class="col-md-4 col-xs-12">
            <h4>Season Details</h4>
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'branchProgramName',
                    'title',
                    'start_date',
                    'end_date',
                    'status',
                ],
            ]) ?>
        </div>
        <div class="col-md-8 col-xs-12">
            <h4>Due Dates</h4>
        </div>
    </div>
</div>
