<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use dosamigos\datepicker\DatePicker;
use yii\web\JsExpression;
use yii\widgets\MaskedInput;
use yii\web\View;
/* @var $this yii\web\View */
/* @var $model common\modules\pos\models\PosIncome */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="pos-income-form">

    <?php $form = ActiveForm::begin([
    'options' => ['class' => 'disable-submit-buttons'],
        /*'layout' => 'horizontal',
        'fieldConfig' => [
            'horizontalCssClasses' => [
                'wrapper' => 'col-sm-9',
            ],
        ],*/
    ]); ?>

    <div class="row">
        <div class="col-md-2 col-xs-12">&nbsp;</div>
        <div class="col-md-8">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <p style="font-size: 20px;">New Income</p>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-5 col-xs-12">
                            <?php 
                                $customersurl = \yii\helpers\Url::to(['/pos/pos-income/customer-list']);
                                $productsurl = \yii\helpers\Url::to(['/pos/pos-income/product-list']);
                                echo $form->field($model, 'season_id')->widget(Select2::classname(), [
                                    'data' => $seasons,
                                    'options' => ['placeholder' => 'Select One','multiple' => false,'class'=>'season-select'],
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                        'disabled' => Yii::$app->controller->action->id != 'update' ? false : true,
                                    ],
                                    'pluginEvents'=>[
                                        'select2:select'=>'
                                            function(){
                                                var vals = this.value;
                                                $.ajax({
                                                    url: "'.$customersurl.'",
                                                    data: {season:vals}
                                                    
                                                }).done(function(result) {
                                                    var h;
                                                    $(".customer-select").html("").select2({ data:result, theme:"krajee", width:"100%",placeholder:"Select One",
                                                        allowClear: true,});
                                                    $(".customer-select").select2("val","");
                                                });

                                                $.ajax({
                                                    url: "'.$productsurl.'",
                                                    data: {season:vals}
                                                    
                                                }).done(function(result) {
                                                    var h;
                                                    $(".product-select").html("").select2({ data:result, theme:"krajee", width:"100%",placeholder:"Select One",
                                                        allowClear: true,});
                                                    $(".product-select").select2("val","");
                                                });
                                            }'
                                    ]
                                ]);
                            ?>

                            <?= $form->field($model, 'customer_id')->widget(Select2::classname(), [
                                'data' => $customers,
                                'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'customer-select'],
                                'pluginOptions' => [
                                    'allowClear' =>  true,
                                ],
                                ]);
                            ?>

                            <?= $form->field($incomeItemModel, 'product_id')->widget(Select2::classname(), [
                                'data' => $products,
                                'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'product-select'],
                                'pluginOptions' => [
                                    'allowClear' =>  true,
                                ],
                                ]);
                            ?>

                            <?= $form->field($incomeItemModel, 'amount')->widget(MaskedInput::classname(), [
                                'clientOptions' => [
                                    'alias' =>  'decimal',
                                    'autoGroup' => true
                                ],
                            ]) ?>

                            <?= $form->field($incomeItemModel, 'amount_type_id')->widget(Select2::classname(), [
                                'data' => $amountTypes,
                                'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'amount-type-select'],
                                'pluginOptions' => [
                                    'allowClear' =>  true,
                                ],
                                ])->label('Paid as:');
                            ?>

                            <?= $form->field($incomeItemModel, 'transaction_no')->textInput() ?>

                        </div>
                        <div class="col-md-7 col-xs-12">
                            <div class="row">
                                <div class="col-md-4 col-xs-12">&nbsp;</div>
                                <div class="col-md-8 col-xs--12">
                                    <?= $form->field($model, 'official_receipt_id')->textInput(['disabled' => 'disabled']) ?>
                                    
                                    <?= $form->field($model, 'ar_number')->textInput() ?>

                                    <div id="dateselector"></div>
                                    
                                    <?= is_null($backtrack) ? $form->field($model, 'invoice_date')->widget(DatePicker::classname(), [
                                        'options' => ['placeholder' => 'Enter date', 'disabled' => true, 'value' => Yii::$app->controller->action->id != 'update' ?date("Y-m-d") : $model->invoice_date],
                                        'clientOptions' => [
                                            'autoclose' => true,
                                            'format' => 'yyyy-mm-dd'
                                        ],
                                    ]) : $form->field($model, 'invoice_date')->widget(DatePicker::classname(), [
                                        'options' => ['placeholder' => 'Enter date'],
                                        'clientOptions' => [
                                            'autoclose' => true,
                                            'format' => 'yyyy-mm-dd',
                                            'startDate' => $backtrack->date_from,
                                            'endDate' => $backtrack->date_to,
                                        ],
                                    ]) ?>

                                    <?= $form->field($model, 'payment_due')->widget(DatePicker::classname(), [
                                        'options' => ['placeholder' => 'Enter date'],
                                        'clientOptions' => [
                                            'autoclose' => true,
                                            'format' => 'yyyy-mm-dd',
                                            'startDate' => date("Y-m-d")
                                        ],
                                    ]); ?>

                                    <?= $form->field($model, 'account_id')->widget(Select2::classname(), [
                                        'data' => $accounts,
                                        'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'account-select'],
                                        'pluginOptions' => [
                                            'allowClear' =>  true,
                                        ],
                                        ])->label('Account to:');
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
        
                    <div class="form-group pull-right">
                        <?= Html::submitButton('Save', ['class' => 'btn btn-success', 'data' => ['disabled-text' => 'Please Wait']]) ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-xs-12">&nbsp;</div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
<?php
    if(Yii::$app->controller->action->id != 'update'){
        $script = '
            $( document ).ready(function() {
                $("#posincome-season_id").on("change", function(){ 
                    $.ajax({
                        url: "'.Url::to(['/pos/pos-income/generate-receipt']).'?season=" + $("#posincome-season_id").val(),
                        success: function (data) { 
                            $("#posincome-official_receipt_id").val(data);
                        }
                    });
                });
            });
      ';
      $this->registerJs($script, View::POS_END);
    }
?>