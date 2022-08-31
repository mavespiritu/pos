<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use dosamigos\datepicker\DateRangePicker;
/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\DateRestriction */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="date-restriction-form">

    <?php $form = ActiveForm::begin(); ?>

    <p><strong>Branch: </strong><u><?= $model->branchName ?></u></p>

    <?= $form->field($model, 'allow')->dropdownList(['Yes' => 'Yes', 'No' => 'No']) ?>

    <?= $form->field($model, 'start_date')->widget(DateRangePicker::className(), [
	    'attributeTo' => 'end_date', 
	    'form' => $form, // best for correct client validation
	    'language' => 'en',
	    'size' => 'md',
	    'clientOptions' => [
	        'autoclose' => true,
	        'format' => 'yyyy-mm-dd',
	        'clearBtn' => true
	    ]
		])->label('Effectivity');?>

	<?= $form->field($model, 'open_type')->dropdownList(['Income' => 'Income', 'Expenses' => 'Expenses']) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
        <?= Html::a('Clear',['/accounting/date-restriction/'],['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
