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
                    <p style="font-size: 20px;">New Expense</p>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-5 col-xs-12">
                            <?= $form->field($model, 'season_id')->widget(Select2::classname(), [
                                    'data' => $seasons,
                                    'options' => ['placeholder' => 'Select One','multiple' => false,'class'=>'season-select'],
                                    'pluginOptions' => [
                                        'allowClear' => true
                                    ],
                                ]);
                            ?>

                            <?= $form->field($model, 'vendor_id')->widget(Select2::classname(), [
                                'data' => $vendors,
                                'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'vendor-select'],
                                'pluginOptions' => [
                                    'allowClear' =>  true,
                                ],
                                ]);
                            ?>


                            <?= $form->field($model, 'amount_type_id')->widget(Select2::classname(), [
                                'data' => $amountTypes,
                                'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'amount-type-select'],
                                'pluginOptions' => [
                                    'allowClear' =>  true,
                                ],
                                ])->label('Paid as:');
                            ?>

                            <?= $form->field($model, 'transaction_no')->textInput() ?>

                        </div>
                        <div class="col-md-7 col-xs-12">
                            <div class="row">
                                <div class="col-md-4 col-xs-12">&nbsp;</div>
                                <div class="col-md-8 col-xs--12">
                                    
                                    <?= $form->field($model, 'voucher_no')->textInput() ?>

                                    <div id="dateselector"></div>
                                    
                                    <?= is_null($backtrack) ? $form->field($model, 'expense_date')->widget(DatePicker::classname(), [
                                        'options' => ['placeholder' => 'Enter date', 'disabled' => true, 'value' => Yii::$app->controller->action->id != 'update' ? date("Y-m-d") : $model->expense_date],
                                        'clientOptions' => [
                                            'autoclose' => true,
                                            'format' => 'yyyy-mm-dd'
                                        ],
                                    ]) : $form->field($model, 'expense_date')->widget(DatePicker::classname(), [
                                        'options' => ['placeholder' => 'Enter date'],
                                        'clientOptions' => [
                                            'autoclose' => true,
                                            'format' => 'yyyy-mm-dd',
                                            'startDate' => $backtrack->date_from,
                                            'endDate' => $backtrack->date_to,
                                        ],
                                    ]) ?>

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
