<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use dosamigos\datepicker\DateRangePicker;
/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\Season */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="season-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'branch_program_id')->widget(Select2::classname(), [
        'data' => $branchPrograms,
        'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'branch-program-select'],
        'pluginOptions' => [
            'allowClear' =>  true,
        ],
        ]);
    ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'start_date')->widget(DateRangePicker::className(), [
        'attributeTo' => 'end_date', 
        'form' => $form, // best for correct client validation
        'language' => 'en',
        'size' => 'md',
        'clientOptions' => [
            'autoclose' => true,
            'format' => 'yyyy-mm-dd',
            'clearBtn' => true
        ]
        ])->label('Effectivity');?>

    <?= $form->field($model, 'or_start')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'no_of_pieces')->textInput(['maxlength' => true, 'type' => 'number', 'min' => '1']) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
        <?= Html::a('Clear',['/accounting/season/'],['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
