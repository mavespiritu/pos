<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\daterange\DateRangePicker;
use yii\widgets\ActiveField;
/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\ProfessionalRequest */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="professional-request-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'start_date', [
        'addon'=>['prepend'=>['content'=>'<i class="fa fa-calendar"></i>']],
        'options'=>['class'=>'drp-container form-group']
    ])->widget(DateRangePicker::classname(), [
        'useWithAddon'=>true
    ]); ?>

    <?= $form->field($model, 'period_covered')->dropDownList([ '1-15' => '1-15', '16-30' => '16-30', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'bank')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'account_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'account_number')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
