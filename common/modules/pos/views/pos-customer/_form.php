<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use kartik\select2\Select2; // or kartik\select2\Select2
use yii\web\JsExpression;
use dosamigos\datepicker\DatePicker;
use yii\widgets\MaskedInput;
/* @var $this yii\web\View */
/* @var $model common\modules\pos\models\PosCustomer */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="pos-customer-form">

    <?php $form = ActiveForm::begin([
        'options' => ['class' => 'disable-submit-buttons'],
    ]); ?>

    <div class="row">
        <div class="col-md-3 col-xs-12">
            <?= $form->field($model, 'first_name')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-3 col-xs-12">
            <?= $form->field($model, 'middle_name')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-3 col-xs-12">
            <?= $form->field($model, 'last_name')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-3 col-xs-12">
            <?= $form->field($model, 'ext_name')->textInput(['maxlength' => true]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 col-xs-12">
            <?php 
                $citymunsurl = \yii\helpers\Url::to(['/pos/pos-customer/citymun-list']);
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
                ]);
            ?>
        </div>
        <div class="col-md-4 col-xs-12">
            <?= $form->field($model, 'citymun_id')->widget(Select2::classname(), [
                    'data' => $citymuns,
                    'options' => ['placeholder' => 'Select City/Municipality','multiple' => false,'class'=>'citymun-select'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]);
            ?>
        </div>
        <div class="col-md-4 col-xs-12">
            <?= $form->field($model, 'address')->textInput(['maxlength' => true]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 col-xs-12">
            <?= $form->field($model, 'birthday')->widget(DatePicker::classname(), [
                'options' => ['placeholder' => 'Enter date'],
                'clientOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd',
                ],
            ]); ?>
        </div>
        <div class="col-md-4 col-xs-12">
            <?= $form->field($model, 'contact_no')->widget(MaskedInput::className(),[
                'clientOptions' => ['alias' => '99999999999']
            ]);?>
        </div>
        <div class="col-md-4 col-xs-12">
            <?= $form->field($model, 'email_address')->widget(MaskedInput::className(),[
                'clientOptions' => ['alias' => 'email']
            ]);?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 col-xs-12">
            <?= $form->field($model, 'school_id')->widget(Select2::classname(), [
                'value' => $model->school_id,
                'initValueText' => empty($model->school_id) ? '' : $model->school->title.' ('.$model->school->address.')',
                'options' => ['placeholder' => 'Search School', 'id' => 'school_id'],
                'pluginOptions' => [
                    'allowClear' => true,
                    'minimumInputLength' => 3,
                    'language' => [
                        'errorLoading' => new JsExpression("function () { return 'Waiting for results...'; }"),
                    ],
                    'ajax' => [
                        'url' => Url::to(['/pos/pos-customer/school-list']),
                        'dataType' => 'json',
                        'data' => new JsExpression('function(params) { return {q:params.term}; }')
                    ],
                    'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                    'templateResult' => new JsExpression('function(school) { return school.title; }'),
                    'templateSelection' => new JsExpression('function (school) { return school.text == "" ? school.title : school.text; }'),
                ],
            ]); 
            ?>
        </div>
        <div class="col-md-4 col-xs-12">
            <?= $form->field($model, 'year_graduated')->widget(MaskedInput::className(),[
                'clientOptions' => ['alias' => '9999']
            ]);?>
        </div>
        <div class="col-md-4 col-xs-12">
            <?= $form->field($model, 'prc')->textInput(['maxlength' => true]) ?>
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success', 'data' => ['disabled-text' => 'Please Wait']]) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
