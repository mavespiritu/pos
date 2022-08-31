<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\MaskedInput;
/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\BranchProgramEnhancement */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="branch-program-enhancement-form">

    <?php $form = ActiveForm::begin(); ?>

    <p><strong>Branch - Program: </strong><u><?= $model->branchProgramName ?></u></p>

    <?= $form->field($model, 'amount')->widget(MaskedInput::classname(), [
        'clientOptions' => [
            'alias' =>  'decimal',
            /*'groupSeparator' => ',',*/
            'autoGroup' => true
        ],
    ]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
        <?= Html::a('Clear',['/accounting/branch-program-enhancement/'],['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
