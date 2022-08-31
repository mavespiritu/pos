<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\BranchTransfer */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="branch-transfer-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'from_branch_program_id')->textInput() ?>

    <?= $form->field($model, 'to_branch_program_id')->textInput() ?>

    <?= $form->field($model, 'particulars')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'amount')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'remarks')->textarea(['rows' => 6]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
