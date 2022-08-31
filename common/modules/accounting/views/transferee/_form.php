<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\select2\Select2; // or kartik\select2\Select2
use yii\web\JsExpression;
use dosamigos\datepicker\DatePicker;
use yii\widgets\MaskedInput;
use yii\bootstrap\Modal;
use yiister\gentelella\widgets\Panel;
/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\Transferee */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="transferee-form">

    <?php $form = ActiveForm::begin(); ?>

    <?php Panel::begin(['header' => 'Enrolment Details']) ?>

     <?= $form->field($newEnroleeTypeModel, 'enrolee_type_id')->widget(Select2::classname(), [
        'data' => $enroleeTypes,
        'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'enrolee-type-select'],
        'pluginOptions' => [
            'allowClear' =>  true,
        ],
        ]);
    ?>

    <?= $form->field($newPackageStudentModel, 'package_id')->widget(Select2::classname(), [
        'data' => $packages,
        'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'package-select'],
        'pluginOptions' => [
            'allowClear' =>  true,
        ],
        ]);
    ?>

    <?= $form->field($newEnhancementModel, 'amount')->widget(MaskedInput::classname(), [
        'clientOptions' => [
            'alias' =>  'decimal',
            /*'groupSeparator' => ',',*/
            'autoGroup' => true
        ],
    ])->label('Enhancement Fee') ?>
    <?= Html::hiddenInput('enhancement_amount', '0.00', ['id' => 'enhancement_amount']) ?>  

    <?= $form->field($newDiscountModel, 'discount_type_id')->widget(Select2::classname(), [
        'data' => $discountTypes,
        'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'discount-select'],
        'pluginOptions' => [
            'allowClear' =>  true,
        ],
        ]);
    ?>

    <?= $form->field($newDiscountModel, 'code_number' , ['enableAjaxValidation' => true])->textInput(['maxlength' => true])->label('Code Number (if discount is GC)') ?>

    <?= $form->field($newDiscountModel, 'amount')->widget(MaskedInput::classname(), [
        'clientOptions' => [
            'alias' =>  'decimal',
            'autoGroup' => true
        ],
    ])->label('Discount Fee') ?>
    <?= Html::hiddenInput('discount_amount', '0.00', ['id' => 'discount_amount']) ?>  

    <?= $form->field($newDiscountModel, 'remarks')->textInput(['maxlength' => true])->label('Remarks (if any)') ?>

    <?= $form->field($newCoachingModel, 'package_id')->widget(Select2::classname(), [
        'data' => $coachingPackages,
        'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'coaching-package-select'],
        'pluginOptions' => [
            'allowClear' =>  true,
        ],
        ])->label('Coaching Package');
    ?>

    <?= Html::hiddenInput('coaching_amount', '0.00', ['id' => 'coaching_amount']) ?>  

    <table style="width: 100%">
        <tr>
            <td><b>Regular Review Price:</b></td>
            <td align=right>
                <div id="regular-review-price"></div>
                <?= Html::hiddenInput('regular_review_price', '0.00', ['id' => 'regular_review_price']) ?>    
            </td>
        </tr>
        <tr>
            <td><b>Payments From Previous Season:</b></td>
            <td align=right>
                <div id="previous-season-amount"><?= number_format($paymentTotal, 0) ?></div>
                <?= Html::hiddenInput('previous_season_amount', $paymentTotal, ['id' => 'previous_season_amount']) ?>    
            </td>
        </tr>
        <tr>
            <td><b>Total Tuition:</b></td>
            <td align=right>
                <div id="total-tuition"></div>
                <?= Html::hiddenInput('total_tuition', '0.00', ['id' => 'total_tuition']) ?>
            </td>
        </tr>
        <tr>
            <td><b>Final Tuition Fee (Without Coaching):</b></td>
            <td align=right>
                <div id="final-tuition" style="font-weight: bolder;"></div>
                <?= Html::hiddenInput('final_tuition', '0.00', ['id' => 'final_tuition']) ?>
            </td>
        </tr>
    </table>
    <br>
    <?php /*Html::button('Advance Enrolment', ['value' => Url::to(['/accounting/student/advance-enrolment', 'id' => $model->id, 'season_id' => $season->id]), 'id' => 'add-advance-enrolment', 'class' => 'btn btn-primary btn-block', 'id' => 'advance-enrolment-button'])*/ ?>
    
    <br>
    <div id="advance-enrolments-form"></div>
    <div id="advance-enrolments-table"></div>
    <?php Panel::end() ?>

    <?php Panel::begin(['header' => 'Initial Payment']) ?>
    <div class="row">
        <div class="col-md-12">
            <?= $form->field($newIncomeEnrolmentModel, 'code_id')->widget(Select2::classname(), [
                'data' => $incomeCodes,
                'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'code-select'],
                'pluginOptions' => [
                    'allowClear' =>  true,
                ],
                ]);
            ?>
        </div>
        <div class="col-md-12">
            <?= $form->field($newIncomeEnrolmentModel, 'or_no')->textInput(['maxlength' => true, 'value' => $newIncomeEnrolmentModel->or_no!='' ? $newIncomeEnrolmentModel->or_no : $current_or, 'disabled' => 'disabled']) ?> 
        </div>
        <div class="col-md-12">
            <?= $form->field($newIncomeEnrolmentModel, 'ar_no')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-12">
            <?= $form->field($newIncomeEnrolmentModel, 'amount')->widget(MaskedInput::classname(), [
                'clientOptions' => [
                    'alias' =>  'decimal',
                    /*'groupSeparator' => ',',*/
                    'autoGroup' => true
                ],
            ]) ?>
        </div>
        <div class="col-md-12">
            <?= $form->field($newIncomeModel, 'amount_type')->dropdownList(['Cash' => 'Cash', 'Check' => 'Check', 'Bank Deposit' => 'Bank Deposit', 'Credit/Debit Card' => 'Credit/Debit Card']); ?>

            <?= $form->field($newIncomeModel, 'transaction_number')->textInput(['maxlength' => true]) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <?php if (!$newIncomeEnrolmentModel) {  ?>
                    <?= Html::submitButton('Update', ['class' => 'btn btn-block btn-success']) ?>
                <?php }else{ ?>
                    <?php if($orStatus == 1) { ?>
                        <?= Html::submitButton('Save', ['class' => 'btn btn-block btn-success', 'id' => 'submit-button', 'data' => [
                            'confirm' => 'If saved, student will be removed on transferred student list and considered as officially enrolled. Proceed?',
                            'method' => 'post',
                            ]
                        ]) ?>
                    <?php }else{ ?>
                        <p class="text text-danger"><i class="glyphicon glyphicon-info-sign"></i> No Available OR. Please request additional ORs to the management to save enrolment.</p>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>
    </div>
    <?php Panel::end() ?>

    <?php ActiveForm::end(); ?>

</div>
<?php
        $script = '
            $( document ).ready(function() {

                if($("#discount-discount_type_id").val()==""){
                    $("#discount-amount").prop("readonly", true);
                    $("#discount-remarks").prop("readonly", true);
                }else{
                    $("#discount-amount").prop("readonly", false);
                    $("#discount-remarks").prop("readonly", false);

                }

                if($("#discount-discount_type_id").val()==5){
                    $("#discount-code_number").prop("readonly", false);
                }else{
                    $("#discount-code_number").prop("readonly", true);
                }

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

                $("#discount-discount_type_id").on("change", function(){ 
                    if($("#discount-discount_type_id").val()==""){
                        $("#discount-amount").prop("readonly", true);
                        $("#discount-remarks").prop("readonly", true);
                    }else{
                        $("#discount-amount").prop("readonly", false);
                        $("#discount-remarks").prop("readonly", false);
                    }

                    if($("#discount-discount_type_id").val()==5){
                        $("#discount-code_number").prop("readonly", false);
                    }else{
                        $("#discount-code_number").prop("readonly", true);
                    }
                });

                $.ajax({
                    url: "'.Url::to(['/accounting/student/show-season']).'?id=" + '.$newSeason->id.',
                    success: function (data) { 
                        $("#season-information").empty();
                        $("#season-information").hide();
                        $("#season-information").fadeIn();
                        $("#season-information").html(data);

                    }
                });

                if($("#packagestudent-package_id").val() != "")
                {
                    $.ajax({
                        url: "'.Url::to(['/accounting/student/show-package']).'?id=" + $("#packagestudent-package_id").val(),
                        success: function (data) { 
                            $("#package-information").empty();
                            $("#package-information").hide();
                            $("#package-information").fadeIn();
                            $("#package-information").html(data);
                        }
                    });
                }

                if($("#coaching-package_id").val() != "")
                {
                    $.ajax({
                        url: "'.Url::to(['/accounting/student/show-coaching']).'?id=" + $("#coaching-package_id").val(),
                        success: function (data) { 
                            $("#coaching-information").empty();
                            $("#coaching-information").hide();
                            $("#coaching-information").fadeIn();
                            $("#coaching-information").html(data);
                        }
                    });
                }

                if($("#discount-amount").val() == "")
                {
                    $("#discount-amount").val("0");
                    $("#discount_amount").val($("#discount-amount").val());
                    $("#final-tuition").html($("#final_tuition").val());

                }else{
                    $("#discount_amount").val($("#discount-amount").val());
                    $("#final_tuition").val(
                        (
                        parseFloat($("#enhancement_amount").val())
                        +
                        parseFloat($("#regular_review_price").val())
                        ) 
                        - 
                        (
                        parseFloat($("#discount_amount").val()) 
                        +
                        parseFloat($("#previous_season_amount").val())
                        )
                    );
                    $("#final-tuition").html($("#final_tuition").val());
                }

                if($("#enhancement-amount").val() == "")
                {
                    $("#enhancement-amount").val("0");
                    $("#enhancement_amount").val($("#enhancement-amount").val());
                    $("#total_tuition").val(parseFloat($("#enhancement_amount").val())+parseFloat($("#regular_review_price").val()));
                    $("#total-tuition").html($("#total_tuition").val());

                    $("#final_tuition").val(
                        (
                        parseFloat($("#enhancement_amount").val())
                        +
                        parseFloat($("#regular_review_price").val())
                        ) 
                        - 
                        (
                        parseFloat($("#discount_amount").val()) 
                        +
                        parseFloat($("#previous_season_amount").val())
                        )
                    );
                    $("#final-tuition").html($("#final_tuition").val());
                }else{
                    $("#enhancement_amount").val($("#enhancement-amount").val());
                    $("#total_tuition").val(parseFloat($("#enhancement_amount").val())+parseFloat($("#regular_review_price").val()));
                    $("#total-tuition").html($("#total_tuition").val());

                    $("#final_tuition").val(
                        (
                        parseFloat($("#enhancement_amount").val())
                        +
                        parseFloat($("#regular_review_price").val())
                        ) 
                        - 
                        (
                        parseFloat($("#discount_amount").val()) 
                        +
                        parseFloat($("#previous_season_amount").val())
                        )
                    );
                    $("#final-tuition").html($("#final_tuition").val());
                }

                $("#packagestudent-package_id").on("change", function(){ 
                    $.ajax({
                        url: "'.Url::to(['/accounting/student/show-package']).'?id=" + $("#packagestudent-package_id").val(),
                        success: function (data) { 
                            $("#package-information").empty();
                            $("#package-information").hide();
                            $("#package-information").fadeIn();
                            $("#package-information").html(data);
                        }
                    });
                });

                $("#enhancement-amount").on("keyup", function(){ 
                    if($("#enhancement-amount").val() == "")
                    {
                        $("#enhancement-amount").val("0");
                        $("#enhancement_amount").val($("#enhancement-amount").val());
                        $("#total_tuition").val(parseFloat($("#enhancement_amount").val())+parseFloat($("#regular_review_price").val()));
                        $("#total-tuition").html($("#total_tuition").val());

                        $("#final_tuition").val(
                            (
                            parseFloat($("#enhancement_amount").val())
                            +
                            parseFloat($("#regular_review_price").val())
                            ) 
                            - 
                            (
                            parseFloat($("#discount_amount").val()) 
                            +
                            parseFloat($("#previous_season_amount").val())
                            )
                        );
                        $("#final-tuition").html($("#final_tuition").val());
                    }else{
                        $("#enhancement_amount").val($("#enhancement-amount").val());
                        $("#total_tuition").val(parseFloat($("#enhancement_amount").val())+parseFloat($("#regular_review_price").val()));
                        $("#total-tuition").html($("#total_tuition").val());

                        $("#final_tuition").val(
                            (
                            parseFloat($("#enhancement_amount").val())
                            +
                            parseFloat($("#regular_review_price").val())
                            ) 
                            - 
                            (
                            parseFloat($("#discount_amount").val()) 
                            +
                            parseFloat($("#previous_season_amount").val())
                            )
                        );
                        $("#final-tuition").html($("#final_tuition").val());
                    }
                    
                });

                $("#discount-amount").on("keyup", function(){ 
                    if($("#discount-amount").val() == "")
                    {
                        $("#discount-amount").val("0");
                        $("#discount_amount").val($("#discount-amount").val());

                        $("#final_tuition").val(
                            (
                            parseFloat($("#enhancement_amount").val())
                            +
                            parseFloat($("#regular_review_price").val())
                            ) 
                            - 
                            (
                            parseFloat($("#discount_amount").val()) 
                            +
                            parseFloat($("#previous_season_amount").val())
                            )
                        );
                        $("#final-tuition").html($("#final_tuition").val());
                    }else{
                        $("#discount_amount").val($("#discount-amount").val());

                        $("#final_tuition").val(
                            (
                            parseFloat($("#enhancement_amount").val())
                            +
                            parseFloat($("#regular_review_price").val())
                            ) 
                            - 
                            (
                            parseFloat($("#discount_amount").val()) 
                            +
                            parseFloat($("#previous_season_amount").val())
                            )
                        );
                        $("#final-tuition").html($("#final_tuition").val());
                    }
                });

                $("#coaching-package_id").on("change", function(){ 
                    $.ajax({
                        url: "'.Url::to(['/accounting/student/show-coaching']).'?id=" + $("#coaching-package_id").val(),
                        success: function (data) { 
                            $("#coaching-information").empty();
                            $("#coaching-information").hide();
                            $("#coaching-information").fadeIn();
                            $("#coaching-information").html(data);
                        }
                    });
                });
            });
';
$this->registerJs($script);
   
?>