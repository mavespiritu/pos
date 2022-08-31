<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\OperatingExpenseSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="operating-expense-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'cv_no') ?>

    <?= $form->field($model, 'particulars') ?>

    <?= $form->field($model, 'staff_salary') ?>

    <?= $form->field($model, 'cash_pf') ?>

    <?php // echo $form->field($model, 'rent') ?>

    <?php // echo $form->field($model, 'utilities') ?>

    <?php // echo $form->field($model, 'equipment_and_labor') ?>

    <?php // echo $form->field($model, 'bir_and_docs') ?>

    <?php // echo $form->field($model, 'marketing') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
