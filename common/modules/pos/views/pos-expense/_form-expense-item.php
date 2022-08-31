<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\widgets\MaskedInput;
/* @var $this yii\web\View */
/* @var $model common\modules\pos\models\PosProduct */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="pos-product-form">

    <?php $form = ActiveForm::begin([
        'options' => ['class' => 'disable-submit-buttons'],
    ]); ?>

    <div class="row">
        <div class="col-md-3 col-xs-12">
            <?= $form->field($expenseItemModel, 'expense_type_id')->widget(Select2::classname(), [
                'data' => $expenseTypes,
                'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'expense-type-select'],
                'pluginOptions' => [
                    'allowClear' =>  true,
                ],
                ]);
            ?>
        </div>
        <div class="col-md-3 col-xs-12">
            <?= $form->field($expenseItemModel, 'description')->textInput() ?>
        </div>
        <div class="col-md-3 col-xs-12">
            <?= $form->field($expenseItemModel, 'quantity')->textInput(['type' => 'number', 'min' => 1]) ?>
        </div>
        <div class="col-md-3 col-xs-12">
            <?= $form->field($expenseItemModel, 'amount')->widget(MaskedInput::classname(), [
                'clientOptions' => [
                    'alias' =>  'decimal',
                    'autoGroup' => true
                ],
            ]) ?>
        </div>
    </div>
    <div class="form-group pull-right">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success', 'data' => ['disabled-text' => 'Please Wait']]) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
