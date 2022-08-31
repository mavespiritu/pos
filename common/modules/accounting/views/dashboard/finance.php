<?php
	use frontend\assets\AppAsset;
	use yii\helpers\Html;
	use kartik\grid\GridView;
	use yii\widgets\Pjax;
	use yiister\gentelella\widgets\Panel;
	use yii\web\JsExpression;
	use yii\helpers\Url;
	use yii\helpers\ArrayHelper;
	use kartik\select2\Select2;
    use kartik\daterange\DateRangePicker;
    use kartik\form\ActiveForm;

	$this->title = 'Dashboard';
	$this->params['breadcrumbs'][] = $this->title;
	$this->params['breadcrumbs'][] = ['label' => 'Student Enrolment', 'url' => ['/accounting/dashboard/finance']];

	$asset = AppAsset::register($this);
?>

<div class="student-enrolment-index">
	<h1><?= Html::encode($this->title) ?></h1>
	<div class="row">
		<div class="col-md-12 col-xs-12">
			<?php Panel::begin(['header' => 'Finances']); ?>
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
                                        <label>Amount Type</label>
                                        <?php
                                            echo Select2::widget([
                                                'name' => 'amount_type_id',
                                                'data' => $amountTypes,
                                                'options' => ['multiple' => true]
                                            ]);
                                        ?>
                                    </div>
                                    <div class="col-md-3 col-xs-12">
                                        <label>Charged to</label>
                                        <?php
                                            echo Select2::widget([
                                                'name' => 'charge_to_id',
                                                'data' => $chargeTos,
                                                'options' => ['multiple' => true]
                                            ]);
                                        ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-10 col-xs-12">&nbsp;</div>
									<div class="col-md-2 col-xs-12">
										<div class="row">
											<div class="col-md-12 col-xs-12">
												<label>&nbsp;</label>
												<?= Html::submitButton('Apply Filters', ['class' => 'btn btn-primary btn-block btn-xs']) ?>
												<?= Html::a('Refresh',['/accounting/dashboard/finance'],['class' => 'btn btn-success btn-block btn-xs']) ?>
												<a class="collapse-link btn btn-danger btn-block btn-xs">Cancel</a>
											</div>
										</div>
									</div>
								</div>
							<?php ActiveForm::end(); ?>	
						<?php Panel::end(); ?>
					</div>
					<div class="col-md-12 col-xs-12">
						<div class="row">
							<div class="col-md-12 col-xs-12">
								<?php Panel::begin(); ?>
									<p class="text-center"><b>Income</b></p>
									<div id="income" style="max-height: 587px; min-height: 587px;"></div>
								<?php Panel::end(); ?>
                                <?php Panel::begin(); ?>
                                     <p class="text-center"><b>Branch Programs</b></p>
                                    <div id="branch-program-summary" style="max-height: 731px; min-height: 731px;"></div>
                                <?php Panel::end(); ?>
                                <?php Panel::begin(); ?>
                                    <p class="text-center"><b>Seasons</b></p>
                                    <div id="season-summary" style="max-height: 731px; min-height: 731px;"></div>
                                <?php Panel::end(); ?>
                                <?php Panel::begin(); ?>
                                    <p class="text-center"><b>Budget Proposals</b></p>
                                    <div id="budget-proposal" style="max-height: 587px; min-height: 587px;"></div>
                                <?php Panel::end(); ?>
                                <?php Panel::begin(); ?>
                                    <p class="text-center"><b>Expenses</b></p>
                                    <div id="expense" style="max-height: 587px; min-height: 587px;"></div>
                                <?php Panel::end(); ?>
							</div>
						</div>
                        <div class="row">
                            <div class="col-md-6 col-xs-12">
                                <?php Panel::begin(); ?>
                                    <p class="text-center"><b>Petty Expenses</b></p>
                                    <div id="petty-expense" style="max-height: 687px; min-height: 687px;"></div>
                                <?php Panel::end(); ?>
                            </div>
                            <div class="col-md-6 col-xs-12">
                                <?php Panel::begin(); ?>
                                    <p class="text-center"><b>Operating Expenses</b></p>
                                    <div id="operating-expense" style="max-height: 687px; min-height: 687px;"></div>
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
                    url: "'.Url::to(['/accounting/dashboard/finance-filter']).'?params=" + data,
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

        	function income(data)
        	{
        		$.ajax({
                    url: "'.Url::to(['/accounting/dashboard/income']).'?params=" + data,
                    beforeSend: function(){
                    	$("#income").html("<p class=\"text-center\" style=\"padding-top: 290px;\"><i class=\"fa fa-spinner fa-spin\"></i></p>");
                    },
                    success: function (data) { 
                        $("#income").empty();
                        $("#income").hide();
                        $("#income").fadeIn();
                        $("#income").html(data);
                    }
                });
        	}

            function expense(data)
            {
                $.ajax({
                    url: "'.Url::to(['/accounting/dashboard/expense']).'?params=" + data,
                    beforeSend: function(){
                        $("#expense").html("<p class=\"text-center\" style=\"padding-top: 290px;\"><i class=\"fa fa-spinner fa-spin\"></i></p>");
                    },
                    success: function (data) { 
                        $("#expense").empty();
                        $("#expense").hide();
                        $("#expense").fadeIn();
                        $("#expense").html(data);
                    }
                });
            }

            function branchProgramSummary(data)
            {
                $.ajax({
                    url: "'.Url::to(['/accounting/dashboard/branch-program-summary']).'?params=" + data,
                    beforeSend: function(){
                        $("#branch-program-summary").html("<p class=\"text-center\" style=\"padding-top: 380px;\"><i class=\"fa fa-spinner fa-spin\"></i></p>");
                    },
                    success: function (data) { 
                        $("#branch-program-summary").empty();
                        $("#branch-program-summary").hide();
                        $("#branch-program-summary").fadeIn();
                        $("#branch-program-summary").html(data);
                    }
                });
            }

            function seasonSummary(data)
            {
                $.ajax({
                    url: "'.Url::to(['/accounting/dashboard/season-summary']).'?params=" + data,
                    beforeSend: function(){
                        $("#season-summary").html("<p class=\"text-center\" style=\"padding-top: 380px;\"><i class=\"fa fa-spinner fa-spin\"></i></p>");
                    },
                    success: function (data) { 
                        $("#season-summary").empty();
                        $("#season-summary").hide();
                        $("#season-summary").fadeIn();
                        $("#season-summary").html(data);
                    }
                });
            }

            function budgetProposal(data)
            {
                $.ajax({
                    url: "'.Url::to(['/accounting/dashboard/budget-proposal']).'?params=" + data,
                    beforeSend: function(){
                        $("#budget-proposal").html("<p class=\"text-center\" style=\"padding-top: 290px;\"><i class=\"fa fa-spinner fa-spin\"></i></p>");
                    },
                    success: function (data) { 
                        $("#budget-proposal").empty();
                        $("#budget-proposal").hide();
                        $("#budget-proposal").fadeIn();
                        $("#budget-proposal").html(data);
                    }
                });
            }

            function pettyExpense(data)
            {
                $.ajax({
                    url: "'.Url::to(['/accounting/dashboard/petty-expense']).'?params=" + data,
                    beforeSend: function(){
                        $("#petty-expense").html("<p class=\"text-center\" style=\"padding-top: 390px;\"><i class=\"fa fa-spinner fa-spin\"></i></p>");
                    },
                    success: function (data) { 
                        $("#petty-expense").empty();
                        $("#petty-expense").hide();
                        $("#petty-expense").fadeIn();
                        $("#petty-expense").html(data);
                    }
                });
            }

            function operatingExpense(data)
            {
                $.ajax({
                    url: "'.Url::to(['/accounting/dashboard/operating-expense']).'?params=" + data,
                    beforeSend: function(){
                        $("#operating-expense").html("<p class=\"text-center\" style=\"padding-top: 290px;\"><i class=\"fa fa-spinner fa-spin\"></i></p>");
                    },
                    success: function (data) { 
                        $("#operating-expense").empty();
                        $("#operating-expense").hide();
                        $("#operating-expense").fadeIn();
                        $("#operating-expense").html(data);
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
                        income(formData);
                        expense(formData);
                        branchProgramSummary(formData);
                        seasonSummary(formData);
                        budgetProposal(formData);
                        pettyExpense(formData);
			        	operatingExpense(formData);
			        },
			        error: function () {
			          alert("Something went wrong");
			        }
			      });
			      return false;
			    });
                loadFilters([]);
                income([]);
                expense([]);
                branchProgramSummary([]);
                seasonSummary([]);
                budgetProposal([]);
                pettyExpense([]);
            	operatingExpense([]);
            });
';
$this->registerJs($script);
   
?>