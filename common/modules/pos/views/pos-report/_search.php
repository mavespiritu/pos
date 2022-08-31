<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use dosamigos\datepicker\DatePicker;
/* @var $this yii\web\View */
/* @var $model common\modules\pos\models\PosEnrolmentSearch */
/* @var $form yii\widgets\ActiveForm */
$seasonsurl = \yii\helpers\Url::to(['/pos/pos-report/season-list']);
?>

<div class="pos-enrolment-search">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'branch_program_id')->widget(Select2::classname(), [
        'data' => ['0' => 'ALL'] + $branchPrograms,
        'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'branch-program-select'],
        'pluginOptions' => [
            'allowClear' =>  true,
        ],
        'pluginEvents'=>[
            'select2:select'=>'
                function(){
                    var vals = this.value;
                    $.ajax({
                        url: "'.$seasonsurl.'",
                        data: {id:vals}
                        
                    }).done(function(result) {
                        var h;
                        $(".season-select").html("").select2({ data:result, theme:"krajee", width:"100%",placeholder:"Select One", allowClear: true,});
                        $(".season-select").select2("val","");
                    });
                }'

        ]
        ]);
    ?>

    <?= $form->field($model, 'search_season_id')->widget(Select2::classname(), [
        'data' => $seasons,
        'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'season-select'],
        'pluginOptions' => [
            'allowClear' =>  true,
        ],
        ]);
    ?>

    <?= $form->field($model, 'from_date')->widget(DatePicker::classname(), [
        'options' => ['placeholder' => 'Enter start date'],
        'clientOptions' => [
            'autoclose' => true,
            'format' => 'yyyy-mm-dd',
        ],
        'clientEvents' => [
            'changeDate' => "function(e) {
                const dateReceived = $('#posenrolment-from_date');
                const dateActed = $('#posenrolment-to_date');
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

    <?= $form->field($model, 'to_date')->widget(DatePicker::classname(), [
        'options' => ['placeholder' => 'Enter end date'],
        'clientOptions' => [
            'autoclose' => true,
            'format' => 'yyyy-mm-dd',
            'startDate' => $model->from_date,
        ],
    ]); ?>

    <div class="form-group pull-right">
        <?= Html::submitButton('Generate', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
