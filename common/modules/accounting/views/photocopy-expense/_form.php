<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\widgets\MaskedInput;
use dosamigos\datepicker\DatePicker;
use kartik\select2\Select2;
use yii\web\View;
/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\PhotocopyExpense */
/* @var $form yii\widgets\ActiveForm */
$charges = array('Program' => 'Program','Area Manager' => 'Area Manager','Admin' => 'Admin','Icon' => 'Icon');
?>

<div class="photocopy-expense-form">

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

            <?= $form->field($model, 'subject')->textarea(['rows' => 6]) ?>

            <?= $form->field($model, 'no_of_pages')->textInput(['type' => 'number', 'min' => 0]) ?>
        </div>
        <div class="col-md-6 col-xs-12">
            <?= $form->field($model, 'no_of_pieces')->textInput(['type' => 'number', 'min' => 0]) ?>
            
            <?= $form->field($model, 'amount_per_page')->widget(MaskedInput::classname(), [
                'clientOptions' => [
                    'alias' =>  'decimal',
                    'autoGroup' => true
                ],
            ]) ?>

            <?= $form->field($model, 'total_amount')->widget(MaskedInput::classname(), [
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
                <?= Html::a('Clear',['/accounting/photocopy-expense/'],['class' => 'btn btn-default']) ?>
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
        });
        
      ';
      $this->registerJs($script, View::POS_END);
    ?>