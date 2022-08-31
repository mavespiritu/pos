<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\PettyExpenseSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="petty-expense-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'pcv_no') ?>

    <?= $form->field($model, 'particulars') ?>

    <?= $form->field($model, 'food') ?>

    <?= $form->field($model, 'supplies') ?>

    <?php // echo $form->field($model, 'load') ?>

    <?php // echo $form->field($model, 'fare') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
