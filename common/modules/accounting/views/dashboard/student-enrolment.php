<?php
	use frontend\assets\AppAsset;
	use yii\helpers\Html;
	use kartik\grid\GridView;
	use yii\widgets\Pjax;
	use yiister\gentelella\widgets\Panel;
	use yii\web\JsExpression;
	use yii\helpers\Url;
	use yii\helpers\ArrayHelper;
	use yii\bootstrap\ActiveForm;
	use kartik\select2\Select2;
    use kartik\daterange\DateRangePicker;

	$this->title = 'Dashboard';
	$this->params['breadcrumbs'][] = $this->title;
	$this->params['breadcrumbs'][] = ['label' => 'Student Enrolment', 'url' => ['/accounting/dashboard/student-enrolment']];

	$asset = AppAsset::register($this);
?>

<div class="student-enrolment-index">
	<h1><?= Html::encode($this->title) ?></h1>
	<div class="row">
		<div class="col-md-12 col-xs-12">
			<?php Panel::begin(['header' => 'Student Enrolment']); ?>
				<div class="row">
					<div class="col-md-12 col-xs-12">
						<div id="active-filter">
						
						</div>
					</div>
					<div class="col-md-12 col-xs-12">
						<?php Panel::begin(['header' => 'Search Filters', 'expandable' => true,]); ?>
							<?php $form = ActiveForm::begin(['id' => 'search-filter', 'method' => 'get']); ?>
								<div class="row">
									<div class="col-md-3 col-xs-12">
										<?php echo '<label class="control-label">Date Range</label>';
                                            echo '<div class="drp-container">';
                                            echo DateRangePicker::widget([
                                                'name'=>'date',
                                                'presetDropdown'=>true,
                                                'hideInput'=>true
                                            ]);
                                            echo '</div>'; ?>
									</div>
									<div class="col-md-3 col-xs-12">
										<label>Branch - Program</label>
										<?php
											echo Select2::widget([
												'name' => 'branch_program_id',
												'data' => ArrayHelper::map($branchPrograms, 'id', 'name'),
												'options' => ['multiple' => true]
											]);
										?>
									</div>
									<div class="col-md-3 col-xs-12">
										<div class="row">
											<div class="col-md-4 col-xs-4">
												<label>&nbsp;</label>
												<?= Html::submitButton('Apply Filters', ['class' => 'btn btn-primary btn-block']) ?>
											</div>
											<div class="col-md-4 col-xs-4">
												<label>&nbsp;</label>
												<?= Html::a('Refresh',['/accounting/dashboard/student-enrolment'],['class' => 'btn btn-success btn-block']) ?>
											</div>
											<div class="col-md-4 col-xs-4">
												<label>&nbsp;</label>
												<a class="collapse-link btn btn-danger btn-block">Cancel</a>
											</div>
										</div>
									</div>
								</div>
							<?php ActiveForm::end(); ?>	
						<?php Panel::end(); ?>
					</div>
					<div class="col-md-12 col-xs-12">
						<div class="row">
							<div class="col-md-5 col-xs-12">
								<?php Panel::begin(); ?>
									<p class="text-center"><b>Enrolment By Province</b></p>
									<div id="by-province" style="max-height: 687px; min-height: 687px; overflow: auto;"></div>
								<?php Panel::end(); ?>
								<?php Panel::begin(); ?>
									<p class="text-center"><b>Discounts Availed</b></p>
									<div id="discount" style="min-height: 310px; max-height: 310px; overflow: auto;"></div>
								<?php Panel::end(); ?>
							</div>
							<div class="col-md-7 col-xs-12">
								<div class="row">
									<div class="col-md-12 col-xs-12">
										<?php Panel::begin(); ?>
											<p class="text-center"><b>Payments</b></p>
											<div id="payments" style="min-height: 350px; max-height: 350px; overflow: auto;"></div>
										<?php Panel::end(); ?>
									</div>
								</div>
								<div class="row">
									<div class="col-md-6 col-xs-12">
										<?php Panel::begin(); ?>
											<p class="text-center"><b>Enhancements Availed</b></p>
											<div id="enhancement" style="min-height: 253px; max-height: 253px; overflow: auto;"></div>
										<?php Panel::end(); ?>
										<?php Panel::begin(); ?>
											<p class="text-center"><b>Coaching Availed</b></p>
											<div id="coaching" style="min-height: 314px; max-height: 314px; overflow: auto;"></div>
										<?php Panel::end(); ?>
									</div>
									<div class="col-md-6 col-xs-12">
										<?php Panel::begin(); ?>
									<p class="text-center"><b>By Enrolment Types</b></p>
									<div id="enrolment-types" style="min-height: 640px; max-height: 640px; overflow: auto;"></div>
								<?php Panel::end(); ?>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-5 col-xs-12">
								<?php Panel::begin(); ?>
									<p class="text-center"><b>Enrolment By Season</b></p>
									<div id="season" style="min-height: 687px; max-height: 687px; overflow: auto;"></div>
								<?php Panel::end(); ?>
								<?php Panel::begin(); ?>
									<p class="text-center"><b>Enrolment By School</b></p>
									<div id="school" style="min-height: 687px; max-height: 687px; overflow: auto;"></div>
								<?php Panel::end(); ?>
							</div>
							<div class="col-md-7 col-xs-12">
								<?php Panel::begin(); ?>
									<p class="text-center"><b>Enrolment By Package</b></p>
									<div id="package" style="min-height: 687px; max-height: 687px; overflow: auto;"></div>
								<?php Panel::end(); ?>
								<?php Panel::begin(); ?>
									<p class="text-center"><b>Enrolment By Package Types</b></p>
									<div id="package-type" style="min-height: 350px; max-height: 350px; overflow: auto;"></div>
								<?php Panel::end(); ?>
							</div>
						</div>
					</div>
				</div>
			<?php Panel::end(); ?>
		</div>
	</div>
</div>
<?php
        $script = '
        	function loadFilters(data)
        	{
        		$.ajax({
                    url: "'.Url::to(['/accounting/dashboard/filter']).'?params=" + data,
                    beforeSend: function(){
                    	$("#active-filter").html("<p class=\"text-center\"><i class=\"fa fa-spinner fa-spin\"></i></p>");
                    },
                    success: function (data) { 
                        $("#active-filter").empty();
                        $("#active-filter").hide();
                        $("#active-filter").fadeIn();
                        $("#active-filter").html(data);
                    }
                });
        	}

        	function byProvince(data)
        	{
        		$.ajax({
                    url: "'.Url::to(['/accounting/dashboard/by-province']).'?params=" + data,
                    beforeSend: function(){
                    	$("#by-province").html("<p class=\"text-center\" style=\"padding-top: 270px;\"><i class=\"fa fa-spinner fa-spin\"></i></p>");
                    },
                    success: function (data) { 
                        $("#by-province").empty();
                        $("#by-province").hide();
                        $("#by-province").fadeIn();
                        $("#by-province").html(data);
                    }
                });
        	}

        	function payment(data)
        	{
        		$.ajax({
                    url: "'.Url::to(['/accounting/dashboard/payment']).'?params=" + data,
                    beforeSend: function(){
                    	$("#payments").html("<p class=\"text-center\" style=\"padding-top: 120px;\"><i class=\"fa fa-spinner fa-spin\"></i></p>");
                    },
                    success: function (data) { 
                        $("#payments").empty();
                        $("#payments").hide();
                        $("#payments").fadeIn();
                        $("#payments").html(data);
                    }
                });
        	}

        	function enrolmentTypes(data)
        	{
        		$.ajax({
                    url: "'.Url::to(['/accounting/dashboard/enrolment-types']).'?params=" + data,
                    beforeSend: function(){
                    	$("#enrolment-types").html("<p class=\"text-center\" style=\"padding-top: 120px;\"><i class=\"fa fa-spinner fa-spin\"></i></p>");
                    },
                    success: function (data) { 
                        $("#enrolment-types").empty();
                        $("#enrolment-types").hide();
                        $("#enrolment-types").fadeIn();
                        $("#enrolment-types").html(data);
                    }
                });
        	}

        	function discount(data)
        	{
        		$.ajax({
                    url: "'.Url::to(['/accounting/dashboard/discount']).'?params=" + data,
                    beforeSend: function(){
                    	$("#discount").html("<p class=\"text-center\" style=\"padding-top: 120px;\"><i class=\"fa fa-spinner fa-spin\"></i></p>");
                    },
                    success: function (data) { 
                        $("#discount").empty();
                        $("#discount").hide();
                        $("#discount").fadeIn();
                        $("#discount").html(data);
                    }
                });
        	}

        	function enhancement(data)
        	{
        		$.ajax({
                    url: "'.Url::to(['/accounting/dashboard/enhancement']).'?params=" + data,
                    beforeSend: function(){
                    	$("#enhancement").html("<p class=\"text-center\" style=\"padding-top: 120px;\"><i class=\"fa fa-spinner fa-spin\"></i></p>");
                    },
                    success: function (data) { 
                        $("#enhancement").empty();
                        $("#enhancement").hide();
                        $("#enhancement").fadeIn();
                        $("#enhancement").html(data);
                    }
                });
        	}

        	function packageType(data)
        	{
        		$.ajax({
                    url: "'.Url::to(['/accounting/dashboard/package-type']).'?params=" + data,
                    beforeSend: function(){
                    	$("#package-type").html("<p class=\"text-center\" style=\"padding-top: 120px;\"><i class=\"fa fa-spinner fa-spin\"></i></p>");
                    },
                    success: function (data) { 
                        $("#package-type").empty();
                        $("#package-type").hide();
                        $("#package-type").fadeIn();
                        $("#package-type").html(data);
                    }
                });
        	}

        	function season(data)
        	{
        		$.ajax({
                    url: "'.Url::to(['/accounting/dashboard/season']).'?params=" + data,
                    beforeSend: function(){
                    	$("#season").html("<p class=\"text-center\" style=\"padding-top: 270px;\"><i class=\"fa fa-spinner fa-spin\"></i></p>");
                    },
                    success: function (data) { 
                        $("#season").empty();
                        $("#season").hide();
                        $("#season").fadeIn();
                        $("#season").html(data);
                    }
                });
        	}

        	function school(data)
        	{
        		$.ajax({
                    url: "'.Url::to(['/accounting/dashboard/school']).'?params=" + data,
                    beforeSend: function(){
                    	$("#school").html("<p class=\"text-center\" style=\"padding-top: 270px;\"><i class=\"fa fa-spinner fa-spin\"></i></p>");
                    },
                    success: function (data) { 
                        $("#school").empty();
                        $("#school").hide();
                        $("#school").fadeIn();
                        $("#school").html(data);
                    }
                });
        	}

        	function package(data)
        	{
        		$.ajax({
                    url: "'.Url::to(['/accounting/dashboard/package']).'?params=" + data,
                    beforeSend: function(){
                    	$("#package").html("<p class=\"text-center\" style=\"padding-top: 270px;\"><i class=\"fa fa-spinner fa-spin\"></i></p>");
                    },
                    success: function (data) { 
                        $("#package").empty();
                        $("#package").hide();
                        $("#package").fadeIn();
                        $("#package").html(data);
                    }
                });
        	}

        	function coaching(data)
        	{
        		$.ajax({
                    url: "'.Url::to(['/accounting/dashboard/coaching']).'?params=" + data,
                    beforeSend: function(){
                    	$("#coaching").html("<p class=\"text-center\" style=\"padding-top: 120px;\"><i class=\"fa fa-spinner fa-spin\"></i></p>");
                    },
                    success: function (data) { 
                        $("#coaching").empty();
                        $("#coaching").hide();
                        $("#coaching").fadeIn();
                        $("#coaching").html(data);
                    }
                });
        	}

            $( document ).ready(function() {
            	$("#search-filter").on("beforeSubmit", function(e) {
			      e.preventDefault();
			      var form = $(this);
			      var formData = JSON.stringify(form.serializeArray());
			      $.ajax({
			        url: form.attr("action"),
			        type: form.attr("method"),
			        data: formData,
			        success: function (data) {
			        	loadFilters(formData);
			        	byProvince(formData);
			        	payment(formData);
			        	enrolmentTypes(formData);
			        	discount(formData);
			        	enhancement(formData);
			        	packageType(formData);
			        	school(formData);
			        	coaching(formData);
			        	package(formData);
			        	season(formData);
			        },
			        error: function () {
			          alert("Something went wrong");
			        }
			      });
			      return false;
			    });

            	loadFilters([]);
            	byProvince([]);
            	payment([]);
            	enrolmentTypes([]);
            	discount([]);
            	enhancement([]);
            	packageType([]);
            	school([]);
            	coaching([]);
            	package([]);
            	season([]);
            });
';
$this->registerJs($script);
   
?>