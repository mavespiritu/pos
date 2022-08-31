<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\widgets\MaskedInput;
use yii\web\JsExpression;
use dosamigos\datepicker\DatePicker;
/* @var $this yii\web\View */
/* @var $model common\modules\pos\models\PosEnrolment */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="pos-enrolment-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-4 col-xs-12">
            <h4>Enrolment Form</h4>

            <?= $form->field($model, 'enrolment_type_id')->widget(Select2::classname(), [
                'data' => $enrolmentTypes,
                'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'enrolment-type-select'],
                'pluginOptions' => [
                    'allowClear' =>  true,
                ],
                ]);
            ?>

            <?php 
                $productsurl = \yii\helpers\Url::to(['/pos/pos-enrolment/product-list']);
                echo $form->field($model, 'season_id')->widget(Select2::classname(), [
                    'data' => $seasons,
                    'options' => ['placeholder' => 'Select One','multiple' => false,'class'=>'season-select'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                    'pluginEvents'=>[
                        'select2:select'=>'
                            function(){
                                var vals = this.value;
                                $.ajax({
                                    url: "'.$productsurl.'",
                                    data: {season:vals}
                                    
                                }).done(function(result) {
                                    var h;
                                    $(".product-select").html("").select2({ data:result, theme:"krajee", width:"100%",placeholder:"Select One", allowClear: true,});
                                    $(".product-select").select2("val","");
                                });
                            }'

                    ]
                ]);
            ?>

            <?= $form->field($model, 'customer_id')->widget(Select2::classname(), [
                'value' => $model->customer_id,
                'initValueText' => empty($model->customer_id) ? '' : $model->customer->fullName,
                'options' => ['placeholder' => 'Search Customer', 'id' => 'posenrolment-customer_id'],
                'pluginOptions' => [
                    'allowClear' => true,
                    'minimumInputLength' => 3,
                    'language' => [
                        'errorLoading' => new JsExpression("function () { return 'Waiting for results...'; }"),
                    ],
                    'ajax' => [
                        'url' => Url::to(['/pos/pos-enrolment/customer-list']),
                        'dataType' => 'json',
                        'data' => new JsExpression('function(params) { return {q:params.term}; }')
                    ],
                    'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                    'templateResult' => new JsExpression('function(customer) { return customer.name; }'),
                    'templateSelection' => new JsExpression('function (customer) { return customer.text == "" ? customer.name : customer.text; }'),
                ],
            ]); 
            ?>

            <?= $form->field($model, 'product_id')->widget(Select2::classname(), [
                'data' => $products,
                'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'product-select'],
                'pluginOptions' => [
                    'allowClear' =>  true,
                ],
                ]);
            ?>

            <?= $form->field($model, 'enrolment_date')->widget(DatePicker::classname(), [
                'options' => ['placeholder' => 'Select Date'],
                'clientOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd',
                    'endDate' => date("Y-m-d")
                ],
            ]); ?>
            
            <div class="separator">Apply Discount</div>

            <?= $form->field($discountModel, 'discount_type_id')->widget(Select2::classname(), [
                'data' => $discountTypes,
                'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'discount-type-select'],
                'pluginOptions' => [
                    'allowClear' =>  true,
                ],
                ]);
            ?>

            <?= $form->field($discountModel, 'code_number')->textInput() ?>

            <?= $form->field($discountModel, 'amount')->widget(MaskedInput::classname(), [
                'clientOptions' => [
                    'alias' =>  'decimal',
                    'autoGroup' => true
                ],
            ]) ?>
            
            <?= $form->field($discountModel, 'description')->textArea(['rows' => 2])->label('Remarks') ?>

            <div class="form-group">
                <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
            </div>

        </div>
        <div class="col-md-8 col-xs-12">
            <h4>Enrolment Details</h4>
            <div class="row">
                <div class="col-md-4 col-xs-12">
                    <div id="season-information"></div>
                    <div id="product-information"></div>
                </div>
                <div class="col-md-8 col-xs-12">
                    <div id="customer-information"></div>
                </div>
            </div>
            
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>
<?php
        $script = '
            $( document ).ready(function() {
                if($("#posenrolment-season_id").val() != "")
                {
                    showSeason($("#posenrolment-season_id").val());
                }

                if($("#posenrolment-product_id").val() != "")
                {
                    showProduct($("#posenrolment-product_id").val());
                }
                
                if($("#posenrolment-customer_id").val() != "")
                {
                    showCustomer($("#posenrolment-customer_id").val()); 
                }
                

                if($("#posdiscount-discount_type_id").val()==""){
                    $("#posdiscount-code_number").prop("readonly", true);
                    $("#posdiscount-amount").prop("readonly", true);
                    $("#posdiscount-description").prop("readonly", true);

                }else{
                    if($("#posdiscount-discount_type_id").val()==5){
                        $("#posdiscount-code_number").prop("readonly", false);
                    }else{
                        $("#posdiscount-code_number").prop("readonly", true);
                    }
                    $("#posdiscount-amount").prop("readonly", false);
                    $("#posdiscount-description").prop("readonly", false);

                }

                

                $("#posdiscount-discount_type_id").on("change", function(){ 
                    if($("#posdiscount-discount_type_id").val()==""){
                        $("#posdiscount-code_number").prop("readonly", true);
                        $("#posdiscount-amount").prop("readonly", true);
                        $("#posdiscount-description").prop("readonly", true);
                    }else{
                        $("#posdiscount-code_number").prop("readonly", false);
                        $("#posdiscount-amount").prop("readonly", false);
                        $("#posdiscount-description").prop("readonly", false);

                    }

                    if($("#posdiscount-discount_type_id").val()==5){
                        $("#posdiscount-code_number").prop("readonly", false);
                    }else{
                        $("#posdiscount-code_number").prop("readonly", true);
                    }
                });

                $("#posenrolment-season_id").on("change", function(){ 
                    showSeason($("#posenrolment-season_id").val());
                });

                function showSeason(id)
                {
                    $.ajax({
                        url: "'.Url::to(['/pos/pos-enrolment/show-season']).'?id=" + id,
                        success: function (data) { 
                            $("#season-information").empty();
                            $("#season-information").hide();
                            $("#season-information").fadeIn();
                            $("#season-information").html(data);
                        }
                    });
                }

                $("#posenrolment-product_id").on("change", function(){ 
                    showProduct($("#posenrolment-product_id").val());
                });

                function showProduct(id)
                {
                    $.ajax({
                        url: "'.Url::to(['/pos/pos-enrolment/show-product']).'?id=" + id,
                        success: function (data) { 
                            $("#product-information").empty();
                            $("#product-information").hide();
                            $("#product-information").fadeIn();
                            $("#product-information").html(data);
                        }
                    });
                }

                $("#posenrolment-customer_id").on("change", function(){ 
                    showCustomer($("#posenrolment-customer_id").val()); 
                });

                function showCustomer(id)
                {
                    $.ajax({
                        url: "'.Url::to(['/pos/pos-enrolment/show-customer']).'?id=" + $("#posenrolment-customer_id").val(),
                        success: function (data) { 
                            $("#customer-information").empty();
                            $("#customer-information").hide();
                            $("#customer-information").fadeIn();
                            $("#customer-information").html(data);
                        }
                    });
                }
                
            });
';
$this->registerJs($script);
   
?>
