<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\select2\Select2;
use yii\web\JsExpression;
use yii\helpers\Url;
use yii\widgets\MaskedInput;
use dosamigos\datepicker\DatePicker;
use yii\web\View;
/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\IncomeEnrolment */
/* @var $form yii\widgets\ActiveForm */

$url = \yii\helpers\Url::to(['student-list']);
?>

<div class="income-enrolment-form">
    <?php $form = ActiveForm::begin([
        'options' => ['class' => 'disable-submit-buttons'],
        'layout' => 'horizontal',
        'fieldConfig' => [
            'horizontalCssClasses' => [
                'wrapper' => 'col-sm-9',
            ],
        ],
    ]); ?>

    <div class="row">
        <div class="col-md-6 col-xs-12">
            <?= $form->field($model, 'season_id')->widget(Select2::classname(), [
                'data' => $seasons,
                'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'season-select'],
                'pluginOptions' => [
                    'allowClear' =>  true,
                ],
                ]);
            ?>

            <?= $form->field($model, 'student_id')->widget(Select2::classname(), [
                    'value' => $model->student_id,
                    'initValueText' => empty($model->student_id) ? '' : $model->studentName, // set the initial display text
                    'options' => ['id' => 'student_id'],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'minimumInputLength' => 3,
                        'language' => [
                            'errorLoading' => new JsExpression("function () { return 'Waiting for results...'; }"),
                        ],
                        'ajax' => [
                            'url' => $url,
                            'dataType' => 'json',
                            'data' => new JsExpression('function(params) { return {q:params.term}; }'),
                        ],
                        'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                        'templateResult' => new JsExpression('function(student) { return student.name; }'),
                        'templateSelection' => new JsExpression('function (student) { return student.text == "" ? student.name : student.text; }'),
                    ],
                ]); 
            ?>

            <?= $form->field($model, 'pr')->textInput(['maxlength' => true, 'disabled' => 'disabled']) ?>

            <?= $form->field($model, 'ar_no')->textInput(['maxlength' => true]) ?>

            <?= Html::hiddenInput('or_no', '', ['id' => 'or_no']) ?>  

            <?= $form->field($model, 'code_id')->widget(Select2::classname(), [
                'data' => $incomeCodes,
                'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'code-select'],
                'pluginOptions' => [
                    'allowClear' =>  true,
                ],
                ]);
            ?>
        </div>
        <div class="col-md-6 col-xs-12">
            <?= $form->field($model, 'amount')->widget(MaskedInput::classname(), [
                'clientOptions' => [
                    'alias' =>  'decimal',
                    'autoGroup' => true
                ],
            ]) ?>

            <?= $form->field($incomeModel, 'amount_type')->dropdownList(['Cash' => 'Cash', 'Check' => 'Check', 'Bank Deposit' => 'Bank Deposit', 'Credit/Debit Card' => 'Credit/Debit Card']); ?>

            <?= $form->field($incomeModel, 'transaction_number')->textInput(['maxlength' => true]) ?>

            <?php if($dateRestriction){ ?>
                <?php if($dateRestriction->allow == "Yes" && $dateRestriction->open_type == "Income"){ ?>
                    <?= $form->field($incomeModel, 'datetime')->widget(DatePicker::className(), [
                        'model' => $incomeModel,
                        'template' => '{addon}{input}',
                            'clientOptions' => [
                                'autoclose' => true,
                                'format' => 'yyyy-mm-dd',
                                'startDate' => $dateRestriction->start_date,
                                'endDate' => $dateRestriction->end_date,
                            ]
                        ])->label('Date of Transaction'); ?>
                    <?php if((date("Y-m-d", strtotime($incomeModel->datetime)) >= $dateRestriction->start_date) && (date("Y-m-d", strtotime($incomeModel->datetime)) <= $dateRestriction->end_date)){ ?>
                        <p class="pull-right"><input type="checkbox" id="income-datenow" name="Income[dateNow]"> Use current date and time instead</p>
                    <?php }else{ ?>
                        <p class="pull-right"><input type="checkbox" id="income-datenow" name="Income[dateNow]" checked> Use current date and time instead</p>
                    <?php } ?>
                    <br>
                    <br>
                <?php } ?>
            <?php } ?>

            <div class="form-group pull-right">
                <?= Html::submitButton('Save', ['class' => 'btn btn-success', 'data' => ['disabled-text' => 'Please Wait']]) ?>
                <?= Html::a('Clear',['/accounting/income-enrolment/'],['class' => 'btn btn-default']) ?>
            </div>
        </div>
    </div>

    

    

    <div class="form-group">
        <div class="row">
            <div class="col-md-6">
                <div id="buttonGroup"></div>
            </div>
            <div class="col-md-6">
                
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>
<?php
    if(Yii::$app->controller->action->id != 'update'){
        $script = '
          $( document ).ready(function() {
                if($("#income-datenow").is(":checked")){
                    $("#income-datetime").prop("disabled", true);
                    $("#income-datenow").prop("value", "1");
                }else{
                    $("#income-datetime").prop("disabled", false);
                    $("#income-datenow").prop("value", "0");
                }
    
                $("#income-datenow").on("change", function(){
                    if($(this).is(":checked")){
                        $("#income-datetime").prop("disabled", true);
                        $("#income-datenow").prop("value", "1");
                    }else{
                        $("#income-datetime").prop("disabled", false);
                        $("#income-datenow").prop("value", "0");
                    }
                });
                if($("#income-amount_type").val() == "Cash")
                {
                    $("#income-transaction_number").prop("readonly", true);
                }else{
                    $("#income-transaction_number").prop("readonly", false);
                }
    
                $("#income-amount_type").on("change", function(){
                    if($("#income-amount_type").val() == "Cash")
                    {
                        $("#income-transaction_number").prop("readonly", true);
                    }else{
                        $("#income-transaction_number").prop("readonly", false);
                    }
                });
    
                $("#freebieandicon-season_id").on("change", function(){ 
                    $.ajax({
                        url: "'.Url::to(['/accounting/student/take-or']).'?id=" + $("#freebieandicon-season_id").val(),
                        success: function (data) { 
                            $("#or_no").val(data);
                            $("#freebieandicon-pr").val(data);
                        }
                    });
                });
            });
          ';    
    }else{
       $script = '
          $( document ).ready(function() {
                if($("#income-datenow").is(":checked")){
                    $("#income-datetime").prop("disabled", true);
                    $("#income-datenow").prop("value", "1");
                }else{
                    $("#income-datetime").prop("disabled", false);
                    $("#income-datenow").prop("value", "0");
                }
    
                $("#income-datenow").on("change", function(){
                    if($(this).is(":checked")){
                        $("#income-datetime").prop("disabled", true);
                        $("#income-datenow").prop("value", "1");
                    }else{
                        $("#income-datetime").prop("disabled", false);
                        $("#income-datenow").prop("value", "0");
                    }
                });
                if($("#income-amount_type").val() == "Cash")
                {
                    $("#income-transaction_number").prop("readonly", true);
                }else{
                    $("#income-transaction_number").prop("readonly", false);
                }
    
                $("#income-amount_type").on("change", function(){
                    if($("#income-amount_type").val() == "Cash")
                    {
                        $("#income-transaction_number").prop("readonly", true);
                    }else{
                        $("#income-transaction_number").prop("readonly", false);
                    }
                });
            });
          ';
    }
      $this->registerJs($script, View::POS_END);
    ?>
