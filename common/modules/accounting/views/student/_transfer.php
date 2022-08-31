<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\web\View;
use kartik\select2\Select2;
use yii\helpers\Url;
/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\Dropout */
/* @var $form yii\widgets\ActiveForm */
?>
<h3>Transfer Student Form</h3><br>

<div class="dropout-form">

    <?php $form = ActiveForm::begin(['id'=>'transfer-form', 'enableClientValidation'=>true,]); ?>

    <?= $form->field($model, 'to_season_id')->widget(Select2::classname(), [
        'data' => $seasons,
        'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'season-select'],
        'pluginOptions' => [
            'allowClear' =>  true,
        ],
        ])->label('Transfer to:');
    ?>
    <div id="season-information"></div>
    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
    <?php
      $script = '
      $( document ).ready(function() {
        $("#transferee-to_season_id").on("change", function(){ 
          $.ajax({
              url: "'.Url::to(['/accounting/student/show-season']).'?id=" + $("#transferee-to_season_id").val(),
              success: function (data) { 
                  $("#season-information").empty();
                  $("#season-information").hide();
                  $("#season-information").fadeIn();
                  $("#season-information").html(data);

              }
          });
        });
        $("#transfer-form").on("beforeSubmit", function(e) {
          e.preventDefault();
          var form = $(this);
          var formData = form.serialize();
          $.ajax({
            url: form.attr("action"),
            type: form.attr("method"),
            data: formData,
            success: function (data) {
              alert("Student is successfully transferred");
              location.reload();
            },
            error: function () {
              alert("Something went wrong");
            }
          });
          return false;
        });
      });
      ';
      $this->registerJs($script, View::POS_END);
    ?>
</div>
