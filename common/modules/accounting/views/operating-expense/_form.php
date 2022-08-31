<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\widgets\MaskedInput;
use dosamigos\datepicker\DatePicker;
use kartik\select2\Select2;
use yii\web\View;
/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\OperatingExpense */
/* @var $form yii\widgets\ActiveForm */
$charges = array('Program' => 'Program','Area Manager' => 'Area Manager','Admin' => 'Admin','Icon' => 'Icon');
?>

<div class="operating-expense-form">

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
            <?= $form->field($expenseModel, 'season_id')->widget(Select2::classname(), [
                'data' => $seasons,
                'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'season-select'],
                'pluginOptions' => [
                    'allowClear' =>  true,
                ],
                ]);
            ?>

            <?= $form->field($model, 'cv_no')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'particulars')->textarea(['rows' => 3]) ?>

            <?= $form->field($model, 'staff_salary')->widget(MaskedInput::classname(), [
                'clientOptions' => [
                    'alias' =>  'decimal',
                    'autoGroup' => true
                ],
            ]) ?>

            <?= $form->field($model, 'cash_pf')->widget(MaskedInput::classname(), [
                'clientOptions' => [
                    'alias' =>  'decimal',
                    'autoGroup' => true
                ],
            ]) ?>

            <?= $form->field($model, 'rent')->widget(MaskedInput::classname(), [
                'clientOptions' => [
                    'alias' =>  'decimal',
                    'autoGroup' => true
                ],
            ]) ?>
        </div>
        <div class="col-md-6 col-xs-12">
            <?= $form->field($model, 'utilities')->widget(MaskedInput::classname(), [
                'clientOptions' => [
                    'alias' =>  'decimal',
                    'autoGroup' => true
                ],
            ]) ?>

            <?= $form->field($model, 'equipment_and_labor')->widget(MaskedInput::classname(), [
                'clientOptions' => [
                    'alias' =>  'decimal',
                    'autoGroup' => true
                ],
            ]) ?>

            <?= $form->field($model, 'bir_and_docs')->widget(MaskedInput::classname(), [
                'clientOptions' => [
                    'alias' =>  'decimal',
                    'autoGroup' => true
                ],
            ]) ?>

            <?= $form->field($model, 'marketing')->widget(MaskedInput::classname(), [
                'clientOptions' => [
                    'alias' =>  'decimal',
                    'autoGroup' => true
                ],
            ]) ?>

            <?= $form->field($model, 'charge_to')->widget(Select2::classname(), [
                'data' => $charges,
                'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'charges-select'],
                'pluginOptions' => [
                    'allowClear' =>  true,
                ],
                ]);
            ?>

            <?= $form->field($expenseModel, 'amount_type')->dropdownList(['Cash' => 'Cash', 'Check' => 'Check', 'Bank Deposit' => 'Bank Deposit', 'Credit/Debit Card' => 'Credit/Debit Card']); ?>

            <?= $form->field($expenseModel, 'transaction_number')->textInput(['maxlength' => true]) ?>

            <?php if($dateRestriction){ ?>
                <?php if($dateRestriction->allow == "Yes" && $dateRestriction->open_type == "Expenses"){ ?>
                    <?= $form->field($expenseModel, 'datetime')->widget(DatePicker::className(), [
                        'model' => $expenseModel,
                        'template' => '{addon}{input}',
                            'clientOptions' => [
                                'autoclose' => true,
                                'format' => 'yyyy-mm-dd',
                                'startDate' => $dateRestriction->start_date,
                                'endDate' => $dateRestriction->end_date,
                            ]
                        ])->label('Date of Transaction'); ?>
                    <?php if((date("Y-m-d", strtotime($expenseModel->datetime)) >= $dateRestriction->start_date) && (date("Y-m-d", strtotime($expenseModel->datetime)) <= $dateRestriction->end_date)){ ?>
                        <p class="pull-right"><input type="checkbox" id="expense-datenow" name="Expense[dateNow]"> Use current date and time instead</p>
                    <?php }else{ ?>
                        <p class="pull-right"><input type="checkbox" id="expense-datenow" name="Expense[dateNow]" checked> Use current date and time instead</p>
                    <?php } ?>
                    <br>
                    <br>
                <?php } ?>
            <?php } ?>

            <div class="form-group pull-right">
                <?= Html::submitButton('Save', ['class' => 'btn btn-success', 'data' => ['disabled-text' => 'Please Wait']]) ?>
                <?= Html::a('Clear',['/accounting/operating-expense/'],['class' => 'btn btn-default']) ?>
            </div>
        </div>
    </div>
    
    <?php ActiveForm::end(); ?>

</div>
<?php
      $script = '
        $( document ).ready(function() {
            if($("#expense-datenow").is(":checked")){
                    $("#expense-datetime").prop("disabled", true);
                    $("#expense-datenow").prop("value", "1");
                }else{
                    $("#expense-datetime").prop("disabled", false);
                    $("#expense-datenow").prop("value", "0");
                }

            $("#expense-datenow").on("change", function(){
                if($(this).is(":checked")){
                    $("#expense-datetime").prop("disabled", true);
                    $("#expense-datenow").prop("value", "1");
                }else{
                    $("#expense-datetime").prop("disabled", false);
                    $("#expense-datenow").prop("value", "0");
                }
            });
            
            if($("#expense-amount_type").val() == "Cash")
            {
                $("#expense-transaction_number").prop("readonly", true);
            }else{
                $("#expense-transaction_number").prop("readonly", false);
            }

            $("#expense-amount_type").on("change", function(){
                if($("#expense-amount_type").val() == "Cash")
                {
                    $("#expense-transaction_number").prop("readonly", true);
                }else{
                    $("#expense-transaction_number").prop("readonly", false);
                }
            });

            if($("#operatingexpense-staff_salary").val() == "")
            {
                $("#operatingexpense-staff_salary").val("0");
            }

            if($("#operatingexpense-cash_pf").val() == "")
            {
                $("#operatingexpense-cash_pf").val("0");
            }

            if($("#operatingexpense-rent").val() == "")
            {
                $("#operatingexpense-rent").val("0");
            }

            if($("#operatingexpense-utilities").val() == "")
            {
                $("#operatingexpense-utilities").val("0");
            }

            if($("#operatingexpense-equipment_and_labor").val() == "")
            {
                $("#operatingexpense-equipment_and_labor").val("0");
            }

            if($("#operatingexpense-bir_and_docs").val() == "")
            {
                $("#operatingexpense-bir_and_docs").val("0");
            }

            if($("#operatingexpense-marketing").val() == "")
            {
                $("#operatingexpense-marketing").val("0");
            }

            $("#operatingexpense-staff_salary").on("keyup", function(){
                if($("#operatingexpense-staff_salary").val() == "")
                {
                    $("#operatingexpense-staff_salary").val("0");
                }
            });

            $("#operatingexpense-cash_pf").on("keyup", function(){
                if($("#operatingexpense-cash_pf").val() == "")
                {
                    $("#operatingexpense-cash_pf").val("0");
                }
            });

            $("#operatingexpense-rent").on("keyup", function(){
                if($("#operatingexpense-rent").val() == "")
                {
                    $("#operatingexpense-rent").val("0");
                }
            });

            $("#operatingexpense-utilities").on("keyup", function(){
                if($("#operatingexpense-utilities").val() == "")
                {
                    $("#operatingexpense-utilities").val("0");
                }
            });

            $("#operatingexpense-equipment_and_labor").on("keyup", function(){
                if($("#operatingexpense-equipment_and_labor").val() == "")
                {
                    $("#operatingexpense-equipment_and_labor").val("0");
                }
            });

            $("#operatingexpense-equipment_and_labor").on("keyup", function(){
                if($("#operatingexpense-equipment_and_labor").val() == "")
                {
                    $("#operatingexpense-equipment_and_labor").val("0");
                }
            });

            $("#operatingexpense-bir_and_docs").on("keyup", function(){
                if($("#operatingexpense-bir_and_docs").val() == "")
                {
                    $("#operatingexpense-bir_and_docs").val("0");
                }
            });

            $("#operatingexpense-marketing").on("keyup", function(){
                if($("#operatingexpense-marketing").val() == "")
                {
                    $("#operatingexpense-marketing").val("0");
                }
            });
        });
        
      ';
      $this->registerJs($script, View::POS_END);
    ?>