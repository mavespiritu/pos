<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\PackageType */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="package-type-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'enrolee_type_id')->widget(Select2::classname(), [
        'data' => $enroleeTypes,
        'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'enrolee-type-select'],
        'pluginOptions' => [
            'allowClear' =>  true,
        ],
        ]);
    ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
        <?= Html::a('Clear',['/accounting/package-type/'],['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
