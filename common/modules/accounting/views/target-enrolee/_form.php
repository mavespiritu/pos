<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use kartik\money\MaskMoney;
/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\TargetEnrolee */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="target-enrolee-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'branch_id')->widget(Select2::classname(), [
        'data' => $branches,
        'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'branch-select'],
        'pluginOptions' => [
            'allowClear' =>  true,
        ],
        ]);
    ?>
    <div class="row">
    	<div class="col-md-6">
    		<?= $form->field($model, 'month')->widget(Select2::classname(), [
		        'data' => $months,
		        'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'month-select'],
		        'pluginOptions' => [
		            'allowClear' =>  true,
		        ],
		        ])->label('Month');
		    ?>
    	</div>
    	<div class="col-md-6">
    		<?= $form->field($model, 'year')->textInput(['type' => 'number', 'min' => (date('Y')), 'value' => Yii::$app->controller->action->id == 'update'? $model->year : date('Y')]) ?>
    	</div>
    </div>

    <?= $form->field($model, 'no_of_enrolee')->textInput(['type' => 'number', 'min' => 0]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
        <?= Html::a('Clear',['/accounting/target-enrolee/'],['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
