<?php
	use yii\helpers\Html;
	use yii\widgets\ActiveForm;
	use kartik\select2\Select2;
	use yii\helpers\ArrayHelper;	
	use yii\web\JsExpression;
	use yii\helpers\Url;
?>
<?php $form = ActiveForm::begin(['id' => 'payment-form', 'method' => Url::to(['/accounting/student/search-payment-updates']), 'method' => 'post']); ?>
<div class="student-form">
	<?= $form->field($model, 'season_id')->widget(Select2::classname(), [
	    'data' => $seasons,
	    'options' => ['placeholder' => 'Select One','multiple' => false, 'id' => 'season_id2'],
	    'pluginOptions' => [
	        'allowClear' => true
	    ],
	])->label('Season'); ?>

	<?= $form->field($model, 'page_id')->widget(Select2::classname(), [
	    'data' => $pages,
	    'options' => ['placeholder' => 'Select One','multiple' => false, 'id' => 'page_id2'],
	    'pluginOptions' => [
	        'allowClear' => true
	    ],
	])->label('Page'); ?>
	<p><i class="fa fa-exclamation-circle"></i> If no pages on dropdown above, season has no enrolled students recorded</p>
	<div class="form-group pull-right">
		<?= Html::submitButton('Generate', ['class' => 'btn btn-success', 'id' => 'submit-button']) ?>
	</div>
</div>
<?php ActiveForm::end(); ?>
<?php
$this->registerJs('
    $("document").ready(function(){ 

        $("#season_id2").change(function(e) {
            const id = $(this).val();
            $.ajax({
                    url: "'.Url::to(['/accounting/student/page-list']).'?id="+ id ,
                    success: function (result) {
                        $("#page_id2").html("").select2({ data:result, theme:"krajee", width:"100%",placeholder:"Select One", allowClear: true,});
                        $("#page_id2").select2("val","");
                    }
                });
        });
    });
');
?>