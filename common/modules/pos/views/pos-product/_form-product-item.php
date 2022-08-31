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
        <div class="col-md-4 col-xs-12">
            <?= $form->field($productItemModel, 'item_id')->widget(Select2::classname(), [
                'data' => $items,
                'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'item-select'],
                'pluginOptions' => [
                    'allowClear' =>  true,
                ],
                ]);
            ?>
        </div>
        <div class="col-md-4 col-xs-12">
            <?= $form->field($productItemModel, 'amount')->widget(MaskedInput::classname(), [
                'clientOptions' => [
                    'alias' =>  'decimal',
                    'autoGroup' => true
                ],
            ]) ?>
        </div>
        <div class="col-md-4 col-xs-12">
            <div class="form-group">
                <br>
                <label>&nbsp;</label>
                <?= Html::submitButton('Save', ['class' => 'btn btn-success', 'data' => ['disabled-text' => 'Please Wait']]) ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
