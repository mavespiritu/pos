<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\IncomeCode */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="income-code-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'income_type_id')->widget(Select2::classname(), [
        'data' => $incomeTypes,
        'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'income-type-select'],
        'pluginOptions' => [
            'allowClear' =>  true,
        ],
        ]);
    ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'description')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
        <?= Html::a('Clear',['/accounting/income-code/'],['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
