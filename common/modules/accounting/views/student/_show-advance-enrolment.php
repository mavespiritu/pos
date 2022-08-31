<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
?>

<div class="season-view">
    <h1>Advance Enrolment</h1>

    <h4><?= $student->id_number.' - '.$student->fullName ?></h4>
    <h3>Season Information</h3>
        <?= DetailView::widget([
            'model' => $season,
            'options' => ['class' => 'table table-bordered detail-view'],
            'attributes' => [
                'branchProgramName',
                [
                    'attribute' => 'name',
                    'value' => function($season){ return 'Season '.$season->name; }
                ],
                'start_date',
                'end_date',
            ],
        ]) ?>

    <h3>Initial Payment</h3>
    <?= DetailView::widget([
            'model' => $income,
            'options' => ['class' => 'table table-condensed detail-view'],
            'attributes' => [
                'or',
                'amount',
            ],
        ]) ?>
    <p><b>Encoded at: </b><?= $model->datetime ?></p>
</div>
