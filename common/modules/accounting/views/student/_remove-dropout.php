<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\web\View;
/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\Dropout */
/* @var $form yii\widgets\ActiveForm */
?>
<h3>Remove Dropout Form</h3><br>

<div class="dropout-form">

    <?php $form = ActiveForm::begin(['id'=>'remove-dropout-form', 'enableClientValidation'=>true,]); ?>

    <p>You are about to remove the student from dropouts. Below is the information saved from student's dropping form:</p>
    <p><b>Student: </b><br><?= $model->id_number.' - '.$model->fullName ?></p>
    <p><b>Season: </b><br><?= $season->seasonName ?></p>
    <p><b>Reasons For Dropping: </b><br><?= Html::encode($dropoutModel->reason) ?></p>
    <p><b>Date Dropped: </b><br><?= $dropoutModel->drop_date ?></p>
    <p><b>Authorized By: </b><br><?= Html::encode($dropoutModel->authorized_by) ?></p>
    <br>
    <br>
    <div class="form-group pull-right">
        <?= Html::submitButton('Remove Now', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
    <?php
      $script = '
        $("#remove-dropout-form").on("beforeSubmit", function(e) {
          e.preventDefault();
          var form = $(this);
          var formData = form.serialize();
          $.ajax({
            url: form.attr("action"),
            type: form.attr("method"),
            data: formData,
            success: function (data) {
              alert("Student is successfully remove from dropouts");
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
