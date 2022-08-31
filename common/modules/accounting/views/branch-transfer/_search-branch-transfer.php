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
<?php $form = ActiveForm::begin(['id' => 'branch-transfer-form', 'method' => Url::to(['/accounting/branch-transfer/search']),'method' => 'post']); ?>
<div class="student-form">
	<?= $form->field($model, 'branch_programs_id')->widget(Select2::classname(), [
	    'data' => $branchPrograms,
	    'options' => ['placeholder' => 'Select One','multiple' => false],
	    'pluginOptions' => [
	        'allowClear' => true
	    ],
	])->label('Branch Program'); ?>

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
	
	<p><i class="fa fa-exclamation-circle"></i> If no pages on dropdown above, branch and program has no branch transfers recorded</p>
	<div class="form-group pull-right">
		<?= Html::submitButton('Generate', ['class' => 'btn btn-success', 'id' => 'submit-button']) ?>
	</div>
</div>
<?php ActiveForm::end(); ?>
<?php
$this->registerJs('
    $("document").ready(function(){ 
    	$("#branchtransfer-branch_programs_id").change(function(e){
    		var season_id = $("#branchtransfer-seasons_id").val();
        	var frequency_id = $("#branchtransfer-frequency_id").val();
        	var date_id = $("#branchtransfer-date_id").val();

        	$.ajax({
                url: "'.Url::to(['/accounting/branch-transfer/page-list']).'?season_id="+ season_id +"&frequency_id="+ frequency_id +"&date_id="+ date_id,
                success: function (result) {

                    $("#branchtransfer-page_id").html("").select2({ data:result, theme:"krajee", width:"100%",placeholder:"Select One", allowClear: true,});
                    $("#branchtransfer-page_id").select2("val","");
                }
            });
    	});

    	$("#branchtransfer-frequency_id").change(function(e){
    		var season_id = $("#branchtransfer-seasons_id").val();
        	var frequency_id = $("#branchtransfer-frequency_id").val();
        	var date_id = $("#branchtransfer-date_id").val();

        	$.ajax({
                url: "'.Url::to(['/accounting/branch-transfer/page-list']).'?season_id="+ season_id +"&frequency_id="+ frequency_id +"&date_id="+ date_id,
                success: function (result) {

                    $("#branchtransfer-page_id").html("").select2({ data:result, theme:"krajee", width:"100%",placeholder:"Select One", allowClear: true,});
                    $("#branchtransfer-page_id").select2("val","");
                }
            });
    	});

    	$("#branchtransfer-date_id").change(function(e){
    		var season_id = $("#branchtransfer-seasons_id").val();
        	var frequency_id = $("#branchtransfer-frequency_id").val();
        	var date_id = $("#branchtransfer-date_id").val();

        	$.ajax({
                url: "'.Url::to(['/accounting/branch-transfer/page-list']).'?season_id="+ season_id +"&frequency_id="+ frequency_id +"&date_id="+ date_id,
                success: function (result) {

                    $("#branchtransfer-page_id").html("").select2({ data:result, theme:"krajee", width:"100%",placeholder:"Select One", allowClear: true,});
                    $("#branchtransfer-page_id").select2("val","");
                }
            });
    	});
    });
');
?>