<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\web\View;
/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\School */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="school-form">

    <?php $form = ActiveForm::begin(['id'=>'new-school-form', 'enableClientValidation'=>true,]); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'location')->textarea(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
    <?php
	  $script = '
	    $("#new-school-form").on("beforeSubmit", function(e) {
	      e.preventDefault();
	      var form = $(this);
	      var formData = form.serialize();
	      $.ajax({
	        url: form.attr("action"),
	        type: form.attr("method"),
	        data: formData,
	        success: function (data) {
	          $("#genericModal").modal("toggle");
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
</div>
