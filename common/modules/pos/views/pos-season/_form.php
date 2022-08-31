<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use dosamigos\datepicker\DatePicker;
/* @var $this yii\web\View */
/* @var $model common\modules\pos\models\PosSeason */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="pos-season-form">

    <?php $form = ActiveForm::begin([
        'options' => ['class' => 'disable-submit-buttons'],
    ]); ?>
    <br>
    <?php if(Yii::$app->user->identity->userinfo->BRANCH_C == ""){ ?>

        <?= $form->field($model, 'branch_program_id')->widget(Select2::classname(), [
            'data' => $branchPrograms,
            'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'branch-program-select'],
            'pluginOptions' => [
                'allowClear' =>  true,
            ],
            ]);
        ?>

        <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'start_date')->widget(DatePicker::classname(), [
            'options' => ['placeholder' => 'Enter start date'],
            'clientOptions' => [
                'autoclose' => true,
                'format' => 'yyyy-mm-dd'
            ],
            'clientEvents' => [
                'changeDate' => "function(e) {
                    const dateReceived = $('#posseason-start_date');
                    const dateActed = $('#posseason-end_date');
                    dateActed.val('');
                    dateActed.datepicker('destroy');
                    dateActed.datepicker({
                        startDate: dateReceived.val(),
                        format: 'yyyy-mm-dd',
                        autoclose: true,
                    });
                }",
            ]
        ]); ?>

        <?= $form->field($model, 'end_date')->widget(DatePicker::classname(), [
            'options' => ['placeholder' => 'Enter end date'],
            'clientOptions' => [
                'autoclose' => true,
                'format' => 'yyyy-mm-dd',
                'startDate' => $model->start_date
            ],
        ]); ?>

    <?php } ?>

    <?= $form->field($model, 'status')->dropDownList([ 'Active' => 'Active', 'Archived' => 'Archived'], ['prompt' => '']) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success', 'data' => ['disabled-text' => 'Please Wait']]) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
