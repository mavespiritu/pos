<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\BranchProgram */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="branch-program-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'branch_id')->widget(Select2::classname(), [
        'data' => $branches,
        'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'branch-select'],
        'pluginOptions' => [
            'allowClear' =>  true,
        ],
        ]);
    ?>

    <?= $form->field($model, 'program_id')->widget(Select2::classname(), [
        'data' => $programs,
        'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'program-select'],
        'pluginOptions' => [
            'allowClear' =>  true,
        ],
        ]);
    ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
        <?= Html::a('Clear',['/accounting/branch-program/'],['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
