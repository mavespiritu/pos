<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\SeasonOrList */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="season-or-list-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'season_id')->widget(Select2::classname(), [
        'data' => $seasons,
        'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'season-select'],
        'pluginOptions' => [
            'allowClear' =>  true,
        ],
        ]);
    ?>

    <?= $form->field($model, 'or_start')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'no_of_pieces')->textInput(['maxlength' => true, 'type' => 'number', 'min' => '1']);?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
        <?= Html::a('Clear',['/accounting/season-or-list/'],['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
