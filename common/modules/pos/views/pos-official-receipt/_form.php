<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use dosamigos\datepicker\DatePicker;
/* @var $this yii\web\View */
/* @var $model common\modules\pos\models\PosOfficialReceipt */
/* @var $form yii\widgets\ActiveForm */
?>
<?php $seasonsurl = \yii\helpers\Url::to(['/pos/pos-official-receipt/season-list']); ?>
<div class="pos-official-receipt-form">

    <?php $form = ActiveForm::begin([
        'options' => ['class' => 'disable-submit-buttons'],
    ]); ?>

    <?= $form->field($model, 'branch_program_id')->widget(Select2::classname(), [
        'data' => $branchPrograms,
        'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'branch-program-select'],
        'pluginOptions' => [
            'allowClear' =>  true,
        ],
        'pluginEvents'=>[
            'select2:select'=>'
                function(){
                    var vals = this.value;
                    $.ajax({
                        url: "'.$seasonsurl.'",
                        data: {id:vals}
                        
                    }).done(function(result) {
                        var h;
                        $(".season-select").html("").select2({ data:result, theme:"krajee", width:"100%",placeholder:"Select One", allowClear: true,});
                        $(".season-select").select2("val","");
                    });
                }'

        ]
        ]);
    ?>

    <?= $form->field($model, 'season_id')->widget(Select2::classname(), [
        'data' => $seasons,
        'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'season-select'],
        'pluginOptions' => [
            'allowClear' =>  true,
        ],
        ]);
    ?>

    <?= $form->field($model, 'start_number')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'last_number')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'date_filed')->widget(DatePicker::classname(), [
        'options' => ['placeholder' => 'Enter date'],
        'clientOptions' => [
            'autoclose' => true,
            'format' => 'yyyy-mm-dd',
        ],
    ]); ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success', 'data' => ['disabled-text' => 'Please Wait']]) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
