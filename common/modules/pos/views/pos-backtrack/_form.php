<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use dosamigos\datepicker\DatePicker;
/* @var $this yii\web\View */
/* @var $model common\modules\pos\models\PosBacktrack */
/* @var $form yii\widgets\ActiveForm */
?>
<?php $seasonsurl = \yii\helpers\Url::to(['/pos/pos-backtrack/season-list']); ?>
<div class="pos-backtrack-form">

    <?php $form = ActiveForm::begin([
        'options' => ['class' => 'disable-submit-buttons'],
    ]); ?>
    <br>

    <?= $form->field($model, 'branch_id')->widget(Select2::classname(), [
        'data' => $branches,
        'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'branch-select'],
        'pluginOptions' => [
            'allowClear' =>  true,
        ],
        ]);
    ?>

    <?= $form->field($model, 'date_from')->widget(DatePicker::classname(), [
        'options' => ['placeholder' => 'Enter start date'],
        'clientOptions' => [
            'autoclose' => true,
            'format' => 'yyyy-mm-dd'
        ],
        'clientEvents' => [
            'changeDate' => "function(e) {
                const dateReceived = $('#posbacktrack-date_from');
                const dateActed = $('#posbacktrack-date_to');
                dateActed.val('');
                dateActed.datepicker('destroy');
                dateActed.datepicker({
                    startDate: dateReceived.val(),
                    format: 'yyyy-mm-dd',
                    autoclose: true,
                });
            }",
        ]
    ]); ?>

    <?= $form->field($model, 'date_to')->widget(DatePicker::classname(), [
        'options' => ['placeholder' => 'Enter end date'],
        'clientOptions' => [
            'autoclose' => true,
            'format' => 'yyyy-mm-dd',
            'startDate' => $model->date_from
        ],
    ]); ?>

    <?= $form->field($model, 'field')->dropDownList([ 'Income' => 'Income', 'Expense' => 'Expense', ], ['prompt' => '']) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success', 'data' => ['disabled-text' => 'Please Wait']]) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
