<?php
	use yii\helpers\Html;
	use yii\widgets\ActiveForm;
	use kartik\select2\Select2;
	use yii\helpers\ArrayHelper;	
	use yii\web\JsExpression;
	use yii\helpers\Url;
	use frontend\assets\AppAsset;

	$asset = AppAsset::register($this);
?>

<div class="row">
	<div class="col-xs-12 col-md-3">
		<?=
        \yiister\gentelella\widgets\StatsTile::widget(
            [
                'icon' => 'users',
                'header' => 'Registered Students',
                'text' => Html::a('See More',['/accounting/student/list']),
                'number' => '7084',
            ]
        )
        ?>
	</div>
	<div class="col-xs-12 col-md-3">
		<?=
        \yiister\gentelella\widgets\StatsTile::widget(
            [
                'icon' => 'list-alt',
                'header' => 'Orders',
                'text' => 'All orders list',
                'number' => '7084',
            ]
        )
        ?>
	</div>
	<div class="col-xs-12 col-md-3">
		<?=
        \yiister\gentelella\widgets\StatsTile::widget(
            [
                'icon' => 'list-alt',
                'header' => 'Orders',
                'text' => 'All orders list',
                'number' => '7084',
            ]
        )
        ?>
	</div>
	<div class="col-xs-12 col-md-3">
		<?=
        \yiister\gentelella\widgets\StatsTile::widget(
            [
                'icon' => 'list-alt',
                'header' => 'Orders',
                'text' => 'All orders list',
                'number' => '7084',
            ]
        )
        ?>
	</div>
</div>
<div class="row">
	<div class="col-xs-12 col-md-2">
		<?php ActiveForm::begin(['action' => Url::to(['/accounting/dashboard/enrolment-chart']), 'id' => 'enrolment-form', 'method' => 'get']); ?>
		<label>Year</label>
		<?= Select2::widget([
			'id' => 'year_id',
			'name' => 'year_id',
			'value' => $year_id,
			'data' => ['' => 'ALL'] + ArrayHelper::map($years, 'year', 'year'),
			'options' => [],
			]);
		?>
		<br>
		<label>Province</label>
		<?= Select2::widget([
			'id' => 'province_id',
			'name' => 'province_id',
			'value' => $province_id,
			'data' => ['' => 'ALL'] + ArrayHelper::map($provinces, 'province_c', 'province_m'),
			'options' => [],
			]);
		?>
		<div class="form-group">
			<label>&nbsp;</label>
				<?= Html::submitButton('Generate', ['class' => 'btn btn-primary btn-block']) ?>
		</div>
		<?php ActiveForm::end(); ?>
	</div>
	<div class="col-xs-12 col-md-10">
		<div id="enrolment-chart"></div>
	</div>
</div>
<?php
        $script = '
            $( document ).ready(function() {
            	$("#enrolment-form").on("beforeSubmit", function(e) {
			      e.preventDefault();
			      var form = $(this);
			      var formData = form.serialize();
			      $.ajax({
			        url: form.attr("action"),
			        type: form.attr("method"),
			        data: formData,
			        beforeSend: function(){
                    	$("#enrolment-chart").html("<p class=text-center><img src='.$asset->baseUrl.'/images/spinner.gif /></p>");
                    },
			        success: function (data) {
			          	$("#enrolment-chart").empty();
                      	$("#enrolment-chart").hide();
                        $("#enrolment-chart").fadeIn();
                        $("#enrolment-chart").html(data);
			        },
			        error: function () {
			          alert("Something went wrong");
			        }
			      });
			      return false;
			    });

            	$.ajax({
                    url: "'.Url::to(['/accounting/dashboard/enrolment-chart']).'",
                    beforeSend: function(){
                    	$("#enrolment-chart").html("<p class=text-center><img src='.$asset->baseUrl.'/images/spinner.gif /></p>");
                    },
                    success: function (data) { 
                        $("#enrolment-chart").empty();
                        $("#enrolment-chart").hide();
                        $("#enrolment-chart").fadeIn();
                        $("#enrolment-chart").html(data);
                    }
                });
            });
';
$this->registerJs($script);
   
?>