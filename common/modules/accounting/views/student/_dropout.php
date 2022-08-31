<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\web\View;
/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\Dropout */
/* @var $form yii\widgets\ActiveForm */
?>
<h3>Drop Student Form</h3><br>

<div class="dropout-form">

    <?php $form = ActiveForm::begin(['id'=>'dropout-form', 'enableClientValidation'=>true,]); ?>

    <?= $form->field($dropoutModel, 'reason')->textarea(['rows' => 6])->label('Reason For Dropping') ?>

    <?= $form->field($dropoutModel, 'authorized_by')->textInput(['maxlength' => true, 'value' => Yii::$app->user->identity->userinfo->fullName]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
    <?php
      $script = '
        $("#dropout-form").on("beforeSubmit", function(e) {
          e.preventDefault();
          var form = $(this);
          var formData = form.serialize();
          $.ajax({
            url: form.attr("action"),
            type: form.attr("method"),
            data: formData,
            success: function (data) {
              alert("Student is successfully dropped");
              location.reload();
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
