<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use dosamigos\datepicker\DatePicker;
/* @var $this yii\web\View */
/* @var $model common\modules\pos\models\PosAuditSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="pos-audit-search">

    <?php $form = ActiveForm::begin([
        'id' => 'search-audit'
    ]); ?>

    <div class="row">
        <div class="col-md-3 col-xs-12">
            <?= $form->field($model, 'season_id')->widget(Select2::classname(), [
                'data' => Yii::$app->user->identity->userinfo->BRANCH_C == "" ? ["0" => 'ALL'] + $seasons : $seasons,
                'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'season-select'],
                'pluginOptions' => [
                    'allowClear' =>  true,
                ],
                ]);
            ?>
        </div>
        <div class="col-md-3 col-xs-12">
            <?= $form->field($model, 'audit_date')->widget(DatePicker::classname(), [
                'options' => ['placeholder' => 'Enter date'],
                'clientOptions' => [
                    'autoclose' => true,
                    'autoComplete' => 'off',
                    'format' => 'yyyy-mm-dd',
                    'endDate' => date("Y-m-d")
                ],
            ]); ?>
        </div>
        <div class="col-md-3 col-xs-12">
            <br>
            <label>&nbsp;</label>
            <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
<?php
        $script = '
            $( document ).ready(function() {
                $("#search-audit").on("beforeSubmit", function(e) {
                  e.preventDefault();
                  $.ajax({
                    url: "'.Url::to(['/pos/pos-audit/view']).'?season=" + $("#posaudit-season_id").val() + "&date=" + $("#posaudit-audit_date").val(),
                    success: function (data) { 
                        $("#audit-information").empty();
                        $("#audit-information").hide();
                        $("#audit-information").fadeIn();
                        $("#audit-information").html(data);
                    }
                });

                  return false;
                });
            });
';
$this->registerJs($script);
   
?>