<?php
	use yii\helpers\Html;
	use yii\widgets\ActiveForm;
	use kartik\select2\Select2;
	use yii\helpers\ArrayHelper;	
	use yii\web\JsExpression;
	use yii\helpers\Url;
	use dosamigos\datepicker\DatePicker;
	use frontend\assets\AppAsset;

	$asset = AppAsset::register($this);
?>
<?php $form = ActiveForm::begin(['id' => 'bank-deposit-form', 'method' => Url::to(['/accounting/bank-deposit/search']),'method' => 'post']); ?>
<div class="student-form">
	<?= $form->field($model, 'seasons_id')->widget(Select2::classname(), [
	    'data' => $seasons,
	    'options' => ['placeholder' => 'Select One','multiple' => false],
	    'pluginOptions' => [
	        'allowClear' => true
	    ],
	])->label('Season'); ?>

	<?= $form->field($model, 'frequency_id')->widget(Select2::classname(), [
	    'data' => $frequencies,
	    'options' => ['placeholder' => 'Select One','multiple' => false],
	    'pluginOptions' => [
	        'allowClear' => true
	    ],
	])->label('Frequency'); ?>

	<?= $form->field($model, 'date_id')->widget(DatePicker::className(), [
        'model' => $model,
        'template' => '{addon}{input}',
            'clientOptions' => [
                'autoclose' => true,
                'format' => 'yyyy-mm-dd',
            ]
        ])->label('Date'); ?>

	<?= $form->field($model, 'page_id')->widget(Select2::classname(), [
	    'data' => $pages,
	    'options' => ['placeholder' => 'Select One','multiple' => false],
	    'pluginOptions' => [
	        'allowClear' => true
	    ],
	])->label('Page'); ?>
	
	<p><i class="fa fa-exclamation-circle"></i> If no pages on dropdown above, season has no bank deposits recorded</p>
	<div class="form-group pull-right">
		<?= Html::submitButton('Generate', ['class' => 'btn btn-success', 'id' => 'submit-button']) ?>
	</div>
</div>
<?php ActiveForm::end(); ?>
<?php
$this->registerJs('
    $("document").ready(function(){ 
    	$("#bankdeposit-seasons_id").change(function(e){
    		var season_id = $("#bankdeposit-seasons_id").val();
        	var frequency_id = $("#bankdeposit-frequency_id").val();
        	var date_id = $("#bankdeposit-date_id").val();

        	$.ajax({
                url: "'.Url::to(['/accounting/bank-deposit/page-list']).'?season_id="+ season_id +"&frequency_id="+ frequency_id +"&date_id="+ date_id,
                success: function (result) {

                    $("#bankdeposit-page_id").html("").select2({ data:result, theme:"krajee", width:"100%",placeholder:"Select One", allowClear: true,});
                    $("#bankdeposit-page_id").select2("val","");
                }
            });
    	});

    	$("#bankdeposit-frequency_id").change(function(e){
    		var season_id = $("#bankdeposit-seasons_id").val();
        	var frequency_id = $("#bankdeposit-frequency_id").val();
        	var date_id = $("#bankdeposit-date_id").val();

        	$.ajax({
                url: "'.Url::to(['/accounting/bank-deposit/page-list']).'?season_id="+ season_id +"&frequency_id="+ frequency_id +"&date_id="+ date_id,
                success: function (result) {

                    $("#bankdeposit-page_id").html("").select2({ data:result, theme:"krajee", width:"100%",placeholder:"Select One", allowClear: true,});
                    $("#bankdeposit-page_id").select2("val","");
                }
            });
    	});

    	$("#bankdeposit-date_id").change(function(e){
    		var season_id = $("#bankdeposit-seasons_id").val();
        	var frequency_id = $("#bankdeposit-frequency_id").val();
        	var date_id = $("#bankdeposit-date_id").val();

        	$.ajax({
                url: "'.Url::to(['/accounting/bank-deposit/page-list']).'?season_id="+ season_id +"&frequency_id="+ frequency_id +"&date_id="+ date_id,
                success: function (result) {

                    $("#bankdeposit-page_id").html("").select2({ data:result, theme:"krajee", width:"100%",placeholder:"Select One", allowClear: true,});
                    $("#bankdeposit-page_id").select2("val","");
                }
            });
    	});
    });
');
?>