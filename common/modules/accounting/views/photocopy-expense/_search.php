<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\PhotocopyExpenseSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="photocopy-expense-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'cv_no') ?>

    <?= $form->field($model, 'subject') ?>

    <?= $form->field($model, 'no_of_pages') ?>

    <?= $form->field($model, 'no_of_pieces') ?>

    <?php // echo $form->field($model, 'amount_per_page') ?>

    <?php // echo $form->field($model, 'total_amount') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
