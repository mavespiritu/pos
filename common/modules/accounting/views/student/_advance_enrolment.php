<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\select2\Select2; // or kartik\select2\Select2
use yii\web\JsExpression;
use dosamigos\datepicker\DatePicker;
use yii\widgets\MaskedInput;
use yii\bootstrap\Modal;
use yii\web\View;
/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\Student */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="advance-enrolment-form">

    <?php $form = ActiveForm::begin(['id'=>'advance-enrolment-form', 'enableClientValidation'=>true]); ?>
    <h3>Advance Enrolment Details</h3><hr>
    <div class="row">
        <div class="col-md-12">
            <?= $form->field($advanceEnrolmentModel, 'season_id')->widget(Select2::classname(), [
                'data' => $seasons,
                'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'season-select'],
                'pluginOptions' => [
                    'allowClear' =>  true,
                ],
                ]);
            ?>
            <?= $form->field($incomeEnrolmentModel, 'code_id')->widget(Select2::classname(), [
                'data' => $incomeCodes,
                'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'income-code-select'],
                'pluginOptions' => [
                    'allowClear' =>  true,
                ],
                ]);
            ?>

            <?= $form->field($incomeEnrolmentModel, 'or')->textInput(['maxlength' => true, 'value' => $or ? $or->or : 'No available OR', 'disabled' => 'disabled']) ?>

            <?= $form->field($incomeEnrolmentModel, 'amount')->widget(MaskedInput::classname(), [
                'clientOptions' => [
                    'alias' =>  'decimal',
                    'removeMaskOnSubmit' => false,
                    'autoGroup' => true
                ],
            ]) ?>

            <?= $form->field($incomeModel, 'amount_type')->radioList(['Cash' => 'Cash', 'Check' => 'Check']); ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <div class="row">
                    <div class="col-md-12"><?= Html::submitButton('Save Details', ['class' => 'btn btn-success btn-block']) ?></div>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>
<?php
      $script = '
        $("#advance-enrolment-form").on("beforeSubmit", function(e) {
          e.preventDefault();
          var form = $(this);
          var formData = form.serialize();
          $.ajax({
            url: form.attr("action"),
            type: form.attr("method"),
            data: formData,
            success: function (data) {
            alert("Advance Enrolment has been saved.");
            $("#genericModal").close();
            },
            error: function () {
              alert("Something went wrong");
            }
          });
          return false;
        });
      ';
      $this->registerJs($script, View::POS_END);
    ?>



