<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\select2\Select2; // or kartik\select2\Select2
use yii\web\JsExpression;
use dosamigos\datepicker\DatePicker;
use yii\widgets\MaskedInput;
use yii\bootstrap\Modal;
use common\modules\accounting\models\School;
use yiister\gentelella\widgets\Panel;
use yii\helpers\ArrayHelper;
/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\Student */
/* @var $form yii\widgets\ActiveForm */

$user_info = Yii::$app->user->identity->userinfo;
$roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
$rolenames =  ArrayHelper::map($roles, 'name','name');

?>

<div class="student-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-4">
            <?php Panel::begin(['header' => 'Enrolment Information']); ?>
                 <?= $form->field($enroleeTypeModel, 'enrolee_type_id')->widget(Select2::classname(), [
                    'data' => $enroleeTypes,
                    'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'enrolee-type-select'],
                    'pluginOptions' => [
                        'allowClear' =>  true,
                    ],
                    ]);
                ?>

                <?= $form->field($enroleeTypeModel, 'season_id')->widget(Select2::classname(), [
                    'data' => $seasons,
                    'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'season-select'],
                    'pluginOptions' => [
                        'allowClear' =>  true,
                    ],
                    ]);
                ?>

                <?= $form->field($packageStudentModel, 'package_id')->widget(Select2::classname(), [
                    'data' => $packages,
                    'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'package-select'],
                    'pluginOptions' => [
                        'allowClear' =>  true,
                    ],
                    ]);
                ?>

                <?= $form->field($enhancementModel, 'amount')->widget(MaskedInput::classname(), [
                    'clientOptions' => [
                        'alias' =>  'decimal',
                        /*'groupSeparator' => ',',*/
                        'autoGroup' => true
                    ],
                ])->label('Enhancement Fee') ?>

                <?= Html::hiddenInput('enhancement_amount', '0.00', ['id' => 'enhancement_amount']) ?>  

                <?= $form->field($discountModel, 'discount_type_id')->widget(Select2::classname(), [
                    'data' => $discountTypes,
                    'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'discount-select'],
                    'pluginOptions' => [
                        'allowClear' =>  true,
                    ],
                    ]);
                ?>

                <?= $form->field($discountModel, 'code_number', ['enableAjaxValidation' => true])->textInput(['maxlength' => true])->label('Code Number (if discount is GC)') ?>

                <?= $form->field($discountModel, 'amount')->widget(MaskedInput::classname(), [
                    'clientOptions' => [
                        'alias' =>  'decimal',
                        'autoGroup' => true
                    ],
                ])->label('Discount Fee') ?>

                <?= Html::hiddenInput('discount_amount', '0.00', ['id' => 'discount_amount']) ?>  

                <?= $form->field($discountModel, 'remarks')->textInput(['maxlength' => true])->label('Remarks (if any)') ?>

                <?= $form->field($coachingModel, 'package_id')->widget(Select2::classname(), [
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
            <?php Panel::end(); ?>
            <br>
            <?php Panel::begin(['header' => 'Initial Payment']); ?>
                <div class="row">
                    <div class="col-md-12">
                        <?= $form->field($incomeEnrolmentModel, 'code_id')->widget(Select2::classname(), [
                            'data' => $incomeCodes,
                            'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'code-select'],
                            'pluginOptions' => [
                                'allowClear' =>  true,
                            ],
                            ]);
                        ?>
                    </div>
                    <div class="col-md-12">
                        <?= $form->field($incomeEnrolmentModel, 'or_no')->textInput(['maxlength' => true, 'disabled' => 'disabled']) ?>
                        <?= Html::hiddenInput('or_no', '', ['id' => 'or_no']) ?>  
                    </div>
                    <div class="col-md-12">
                        <?= $form->field($incomeEnrolmentModel, 'ar_no')->textInput(['maxlength' => true]) ?>
                    </div>
                    <div class="col-md-12">
                        <?= $form->field($incomeEnrolmentModel, 'amount')->widget(MaskedInput::classname(), [
                            'clientOptions' => [
                                'alias' =>  'decimal',
                                /*'groupSeparator' => ',',*/
                                'autoGroup' => true
                            ],
                        ]) ?>
                    </div>
                    <div class="col-md-12">
                        <?= $form->field($incomeModel, 'amount_type')->dropdownList(['Cash' => 'Cash', 'Check' => 'Check', 'Bank Deposit' => 'Bank Deposit', 'Credit/Debit Card' => 'Credit/Debit Card']); ?>

                        <?= $form->field($incomeModel, 'transaction_number')->textInput(['maxlength' => true]) ?>
                    </div>
                </div>
            <?php Panel::end(); ?>
        </div>
        <div class="col-md-8">
            <?php Panel::begin(['header' => 'Student Information']); ?>
            <div class="row">
                <div class="col-md-6">
                    <?php 
                        $citymunsurl = \yii\helpers\Url::to(['/accounting/student/citymun-list']);
                        echo $form->field($model, 'province_id')->widget(Select2::classname(), [
                            'data' => $provinces,
                            'options' => ['placeholder' => 'Select Province','multiple' => false,'class'=>'province-select'],
                            'pluginOptions' => [
                                'allowClear' => true
                            ],
                            'pluginEvents'=>[
                                'select2:select'=>'
                                    function(){
                                        var vals = this.value;
                                        $.ajax({
                                            url: "'.$citymunsurl.'",
                                            data: {province:vals}
                                            
                                        }).done(function(result) {
                                            var h;
                                            $(".citymun-select").html("").select2({ data:result, theme:"krajee", width:"100%",placeholder:"Select City/Municipality",
                    allowClear: true,});
                                            $(".citymun-select").select2("val","");
                                        });
                                    }'

                            ]
                        ])->label('Province');

                        ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'citymun_id')->widget(Select2::classname(), [
                        'data' => $citymuns,
                        'options' => ['placeholder' => 'Select City/Municipality','multiple' => false,'class'=>'citymun-select'],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ])->label('City/Municipality');

                    ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <?= $form->field($model, 'id_number', ['enableAjaxValidation' => true])->textInput(['maxlength' => true, 'value' => Yii::$app->controller->action->id == 'create' ? date("y").'-' : $model->id_number]) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <?= $form->field($model, 'first_name')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'middle_name')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'last_name')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($model, 'extension_name')->textInput(['maxlength' => true]) ?>
                </div>
            </div>
            <div class="row">
                <?php if(in_array('SchoolBased',$rolenames)){ ?>
                    <div class="col-md-12">
                        <?= $form->field($model, 'year_graduated')->widget(MaskedInput::className(),[
                            'clientOptions' => ['alias' => '9999']
                        ]);?>
                    </div>
                <?php } else{ ?>
                    <div class="col-md-9">
                       <?= $form->field($model, 'school_id')->widget(Select2::classname(), [
                            'value' => $model->school_id,
                            'initValueText' => empty($model->school_id) ? '' : $model->school->name,
                            'options' => ['placeholder' => 'Search schools', 'id' => 'school_id'],
                            'pluginOptions' => [
                                'allowClear' => true,
                                'minimumInputLength' => 3,
                                'language' => [
                                    'errorLoading' => new JsExpression("function () { return 'Waiting for results...'; }"),
                                ],
                                'ajax' => [
                                    'url' => Url::to(['/accounting/student/school-list']),
                                    'dataType' => 'json',
                                    'data' => new JsExpression('function(params) { return {q:params.term}; }')
                                ],
                                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                                'templateResult' => new JsExpression('function(student) { return student.name; }'),
                                'templateSelection' => new JsExpression('function (student) { return student.text == "" ? student.name : student.text; }'),
                            ],
                        ])->label('School ('.Html::button('Click here if school not on the list', ['value' => Url::to(['/accounting/school/create']), 'id' => 'add-school', 'style' => 'border: none; background: none; color: rgb(54, 88, 153);']).')'); 
                        ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($model, 'year_graduated')->widget(MaskedInput::className(),[
                            'clientOptions' => ['alias' => '9999']
                        ]);?>
                    </div>
                <?php } ?>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <?= $form->field($model, 'permanent_address')->textInput(['maxlength' => true]) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'birthday')->widget(MaskedInput::className(),[
                        'clientOptions' => ['alias' => 'yyyy-mm-dd']
                    ]);?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'prc', ['enableAjaxValidation' => true])->textInput(['maxlength' => true]) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'contact_no')->widget(MaskedInput::className(),[
                        'clientOptions' => ['alias' => '99999999999']
                    ]);?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'email_address', ['enableAjaxValidation' => true])->widget(MaskedInput::className(),[
                        'clientOptions' => ['alias' => 'email']
                    ]);?>
                </div>
            </div>
            <?php Panel::end(); ?>
            <div class="row">
                <div class="col-md-6">
                    <div id="season-information"></div>
                    <div id="coaching-information"></div>
                </div>
                <div class="col-md-6">
                    <div id="package-information"></div>
                </div>
            </div>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <?= Html::submitButton('Save Enrolment', ['class' => 'btn btn-block btn-success', 'id' => 'submit-button']) ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>
<?php
  Modal::begin([
    'id' => 'genericModal',
    'size' => "modal-lg",
    'header' => '<div id="genericModalHeader"></div>'
  ]);
  echo '<div id="genericModalContent"></div>';
  Modal::end();
?>
<?php
        $script = '
            $( document ).ready(function() {

                $("#add-school").click(function(){
                  $("#genericModal").modal("show").find("#genericModalContent").load($(this).attr("value"));
                });

                if($("#discount-discount_type_id").val()==""){
                    $("#discount-amount").prop("readonly", true);
                    $("#discount-remarks").prop("readonly", true);
                }else{
                    $("#discount-amount").prop("readonly", false);
                    $("#discount-remarks").prop("readonly", false);

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

                if($("#discount-discount_type_id").val()==5){
                    $("#discount-code_number").prop("readonly", false);
                }else{
                    $("#discount-code_number").prop("readonly", true);
                }

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

                /*$("#discount-code_number").on("keyup", function(){
                    if($("#discount-code_number").val().length > 3)
                    {
                        $.ajax({
                            url: "'.Url::to(['/accounting/student/check-gc']).'?code=" + $("#discount-code_number").val(),
                            success: function (data) { 
                                if(data == 1)
                                {
                                    alert("GC Code has been taken");
                                    $("#submit-button").hide();
                                }else{
                                    $("#submit-button").show();
                                }
                            }
                        });
                    }else{
                        $("#submit-button").show();
                    }
                });*/

                $("#studentenroleetype-season_id").on("change", function(){ 
                    $.ajax({
                        url: "'.Url::to(['/accounting/student/take-or']).'?id=" + $("#studentenroleetype-season_id").val(),
                        success: function (data) { 
                            $("#or_no").val(data);
                            $("#incomeenrolment-or_no").val(data);
                        }
                    });

                    $.ajax({
                        url: "'.Url::to(['/accounting/student/show-season']).'?id=" + $("#studentenroleetype-season_id").val(),
                        success: function (data) { 
                            $("#season-information").empty();
                            $("#season-information").hide();
                            $("#season-information").fadeIn();
                            $("#season-information").html(data);

                        }
                    });
                });

                

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

                if($("#discount-amount").val() == "")
                {
                    $("#discount-amount").val("0");
                    $("#discount_amount").val($("#discount-amount").val());
                    $("#final-tuition").html($("#final_tuition").val());

                }else{
                    $("#discount_amount").val($("#discount-amount").val());
                    $("#final_tuition").val((parseFloat($("#enhancement_amount").val())+parseFloat($("#regular_review_price").val())) - parseFloat($("#discount_amount").val()));
                    $("#final-tuition").html($("#final_tuition").val());
                }

                if($("#enhancement-amount").val() == "")
                {
                    $("#enhancement-amount").val("0");
                    $("#enhancement_amount").val($("#enhancement-amount").val());
                    $("#total_tuition").val(parseFloat($("#enhancement_amount").val())+parseFloat($("#regular_review_price").val()));
                    $("#total-tuition").html($("#total_tuition").val());

                    $("#final_tuition").val((parseFloat($("#enhancement_amount").val())+parseFloat($("#regular_review_price").val())) - parseFloat($("#discount_amount").val()));
                    $("#final-tuition").html($("#final_tuition").val());
                }else{
                    $("#enhancement_amount").val($("#enhancement-amount").val());
                    $("#total_tuition").val(parseFloat($("#enhancement_amount").val())+parseFloat($("#regular_review_price").val()));
                    $("#total-tuition").html($("#total_tuition").val());

                    $("#final_tuition").val((parseFloat($("#enhancement_amount").val())+parseFloat($("#regular_review_price").val())) - parseFloat($("#discount_amount").val()));
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

                        $("#final_tuition").val((parseFloat($("#enhancement_amount").val())+parseFloat($("#regular_review_price").val())) - parseFloat($("#discount_amount").val()));
                        $("#final-tuition").html($("#final_tuition").val());
                    }else{
                        $("#enhancement_amount").val($("#enhancement-amount").val());
                        $("#total_tuition").val(parseFloat($("#enhancement_amount").val())+parseFloat($("#regular_review_price").val()));
                        $("#total-tuition").html($("#total_tuition").val());

                        $("#final_tuition").val((parseFloat($("#enhancement_amount").val())+parseFloat($("#regular_review_price").val())) - parseFloat($("#discount_amount").val()));
                        $("#final-tuition").html($("#final_tuition").val());
                    }
                    
                });

                $("#discount-amount").on("keyup", function(){ 
                    if($("#discount-amount").val() == "")
                    {
                        $("#discount-amount").val("0");
                        $("#discount_amount").val($("#discount-amount").val());

                        $("#final_tuition").val((parseFloat($("#enhancement_amount").val())+parseFloat($("#regular_review_price").val())) - parseFloat($("#discount_amount").val()));
                        $("#final-tuition").html($("#final_tuition").val());
                    }else{
                        $("#discount_amount").val($("#discount-amount").val());

                        $("#final_tuition").val((parseFloat($("#enhancement_amount").val())+parseFloat($("#regular_review_price").val())) - parseFloat($("#discount_amount").val()));
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

