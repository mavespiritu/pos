<?php

use yii\helpers\Html;
use yiister\gentelella\widgets\Panel;
use yii\widgets\ListView;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\web\View;
use kartik\daterange\DateRangePicker;

$this->title = 'Home';
?>
<?php
    $roles = \Yii::$app->authManager->getRolesByUser(Yii::$app->user->id);
    if(empty($roles))
    {
        $user_role = ""; 
    }
    else
    {
       foreach($roles as $role)
        {
            $user_role = $role->name;
        }
    }

    $totalbeginning = 0;
    $totalbeginningCoh = 0;
    $totalbeginningCob = 0;

    $totalincomeCashTotal = 0;
    $totalincomeTotal = 0;
	$totalincomeNonCashTotal = 0;

	$totalbankDepositsTotal = 0;
	$totalbankDepositsNonCashTotal = 0;
	$totalbankDepositsCashTotal = 0;

	$totalexpenseTotal = 0;
	$totalexpenseCashTotal = 0;
	$totalexpenseNonCashTotal = 0;

	$totalNetIncomeTotal = 0;
	$totalNetIncomeCashTotal = 0;
	$totalNetIncomeNonCashTotal = 0;
	
	$totalGross = 0;
	$totalGrossIncome = 0;										
	$totalExpenses = 0;										
	$expectedIncome = 0;										
	$netIncome = 0;

	$targetGrossPerBP = 0;
	$targetGrossTotal = 0;
	$targetGrossIncomePerBP = 0;
	$targetGrossIncomeTotal = 0;
	$targetExpensesPerBP = 0;
	$targetExpensesTotal = 0;
	$targetExpectedIncomePerBP = 0;
	$targetExpectedIncomeTotal = 0;
	$targetNetIncomePerBP = 0;
	$targetNetIncomeTotal = 0;
?>
<h1><?= $this->title ?></h1>

<div class="home-index">
	<?php Panel::begin(['header' => 'Welcome to Toprank Integrated System!']); ?>
		<!-- <div class="row">
			<div class="col-md-12 col-xs-12">
				<?php Panel::begin(['header' => 'Cutoff Calendar']); ?>
		        <ul class="progressbar">
		        	<?php if(!empty($cutoffDates)){ ?>
		        		<?php foreach($cutoffDates as $date){ ?>
		        			<?php if($date == date('Y-m-d')){ ?>
		        				<li class="active"><b><?= date("M j", strtotime($date)) ?></b></li>
		        			<?php }else if($date < date('Y-m-d')){ ?>
		        				<li class="active"><?= date("M j", strtotime($date)) ?></li>
		        			<?php }else{ ?>
		        				<li><?= date("M j", strtotime($date)) ?></li>
		        			<?php } ?>
		        		<?php } ?>
		        	<?php } ?>
		        </ul>
		        <?php Panel::end(); ?>
			</div>
		</div>
		<br> -->
		<div class="row">
			<div class="col-md-4 col-xs-12">
				<?php Panel::begin(['header' => 'Set Your Access']); ?>
				<ul class="to_do">
					<li><p>Selecting your access will limit you on per program view of records. Scroll to the bottom of the page to check access type, branch and program you are accessing.</p></li>
				</ul>
			
				<?php $form = ActiveForm::begin(['options' => ['class' => 'disable-submit-buttons']]); ?>
					<?= $form->field($access, 'branch_program_id')->widget(Select2::classname(), [
				        'data' => ArrayHelper::map($branchPrograms, 'id', 'name'),
				        'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'branch-program-select'],
				        'pluginOptions' => [
				            'allowClear' =>  true,
				        ],
				        ])->label('I want to access:');
				    ?>
				<div class="form-group pull-left">
					<p><i class="fa fa-exclamation-circle"></i> Leave the dropdown empty and click "Proceed" button if you wish to view all programs.</p>
				</div>
				<div class="form-group pull-right">
			        <?= Html::submitButton('Proceed', ['class' => 'btn btn-success', 'data' => ['disabled-text' => 'Please Wait']]) ?>
			    </div>
			    <?php ActiveForm::end(); ?>
			    <?php Panel::end(); ?>
			</div>
			<div class="col-md-4 col-xs-12">
				<?php Panel::begin(['header' => 'Archive Season']); ?>
					<ul class="to_do">
						<li><p>Selected season will be archived together with its data.</p></li>
					</ul>
				
					<?php $form = ActiveForm::begin(['options' => ['class' => 'disable-submit-buttons']]); ?>
						<?php $seasonsurl = \yii\helpers\Url::to(['/accounting/audit/season-list']); ?>

						<?= $form->field($archive, 'branch_program_id')->widget(Select2::classname(), [
					        'data' => ArrayHelper::map($branchPrograms, 'id', 'name'),
					        'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'archive-branch-program-select'],
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
	                                        $(".archive-season-select").html("").select2({ data:result, theme:"krajee", width:"100%",placeholder:"Select One", allowClear: true,});
	                                        $(".archive-season-select").select2("val","");
	                                    });
	                                }'

	                        ]
					        ]);
					    ?>

		                <?= $form->field($archive, 'season_id')->widget(Select2::classname(), [
		                    'data' => $seasons,
		                    'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'archive-season-select'],
		                    'pluginOptions' => [
		                        'allowClear' =>  true,
		                    ],
		                    ]);
		                ?>
		                <br>
						<div class="form-group pull-right">
					        <?= Html::submitButton('Save', ['class' => 'btn btn-success', 'data' => ['disabled-text' => 'Please Wait']]) ?>
					    </div>
				    <?php ActiveForm::end(); ?>
			    <?php Panel::end(); ?>
				<?php /*Panel::begin(['header' => 'Get Started']);*/ ?>
					<!-- <p>We've assembled some links to get you started:</p>
					<div class="row">
						<?php if(!Yii::$app->user->isGuest && ($user_role == 'TopManagement')){ ?>
							<div class="col-md-4">
								<h4>Enrolment Records:</h4>
								<p>1. To check student records:<br>
									<i class="fa fa-users"></i> <?= Html::a('Students', ["/accounting/student/list"],['class' => 'a-link']) ?></p>

								<h4>Managing Content: </h4>
								<p>1. To add new branch:<br>
									<i class="fa fa-file"></i> <?= Html::a('Branch', ["/accounting/branch"],['class' => 'a-link']) ?></p>
								<p>2. To add new program:<br>
									<i class="fa fa-file"></i> <?= Html::a('Program', ["/accounting/program"],['class' => 'a-link']) ?></p>
								<p>3. To register program under a certain branch:<br>
									<i class="fa fa-file"></i> <?= Html::a('Branch-Program Setup', ["/accounting/branch-program"],['class' => 'a-link']) ?></p>
								<p>4. To add/edit and manage seasons:<br>
									<i class="fa fa-file"></i> <?= Html::a('Seasons', ["/accounting/season"],['class' => 'a-link']) ?></p>
								<p>5. To add/edit and manage packages:<br>
									<i class="fa fa-file"></i> <?= Html::a('Packages', ["/accounting/package"],['class' => 'a-link']) ?></p>
								<p>6. To enable/disable back tracking of records via date restriction:<br>
									<i class="fa fa-file"></i> <?= Html::a('Date Restriction', ["/accounting/date-restriction"],['class' => 'a-link']) ?></p>
								<p>7. To set default enhancement fee amount by branch-program:<br>
									<i class="fa fa-file"></i> <?= Html::a('Enhancement Fees', ["/accounting/branch-program-enhancement"],['class' => 'a-link']) ?></p>
								<p>8. To add monthly target of number of enrolees:<br>
									<i class="fa fa-file"></i> <?= Html::a('Target Enrolees', ["/accounting/target-enrolee"],['class' => 'a-link']) ?></p>
								<p>9. To add monthly target of number of expenses:<br>
									<i class="fa fa-file"></i> <?= Html::a('Target Expenses', ["/accounting/target-expense"],['class' => 'a-link']) ?></p>
							</div>
						<?php } ?>
						<?php if(!Yii::$app->user->isGuest && ($user_role == 'EnrolmentStaff' || $user_role == 'SchoolBased' || $user_role == 'AccountingStaff' || $user_role == 'AreaManager')){ ?>
							<div class="col-md-4">
								<h4>Enrolment Steps:</h4>
								<p>1. For new students:<br>
									<i class="glyphicon glyphicon-plus"></i> <?= Html::a('Register New Student', ["/accounting/student/create"],['class' => 'a-link']) ?></p>
								<p>2. To search enrolment record or enroll/transfer/drop old student: <br>
									<i class="fa fa-folder-o"></i> <?= Html::a('Enrolment', ["/accounting/student/"],['class' => 'a-link']) ?></p>
								<p>3. To check student records: <br>
									<i class="fa fa-users"></i> <?= Html::a('Students', ["/accounting/student/list"],['class' => 'a-link']) ?></p>
								<p>4. To look and enroll transferees: <br>
									<i class="fa fa-sign-out"></i> <?= Html::a('Transferees', ["/accounting/transferee"],['class' => 'a-link']) ?></p>
								<p>5. To look and remove dropouts: <br>
									<i class="fa fa-sign-out"></i> <?= Html::a('Dropouts', ["/accounting/dropout"],['class' => 'a-link']) ?></p>
							</div>
						<?php } ?>
						<?php if(!Yii::$app->user->isGuest && ($user_role == 'AccountingStaff' || $user_role == 'AreaManager')){ ?>
							<div class="col-md-4">
								<h4>Accounting Processes:</h4>
								<p>1. Record payment for enrolment balances:<br>
									<i class="fa fa-table"></i> <?= Html::a('Enrolments', ["/accounting/income-enrolment"],['class' => 'a-link']) ?></p>
								<p>2. Record payment for freebies and icons:<br>
									<i class="fa fa-table"></i> <?= Html::a('Freebies and Icons', ["/accounting/freebie-and-icon"],['class' => 'a-link']) ?></p>
								<p>3. Record different type of expenses:<br>
									<i class="fa fa-table"></i> <?= Html::a('Petty Expense', ["/accounting/petty-expense"],['class' => 'a-link']) ?><br>
									<i class="fa fa-table"></i> <?= Html::a('Photocopy Expense', ["/accounting/photocopy-expense"],['class' => 'a-link']) ?><br>
									<i class="fa fa-table"></i> <?= Html::a('Operating Expense', ["/accounting/operating-expense"],['class' => 'a-link']) ?><br>
									<i class="fa fa-table"></i> <?= Html::a('Other Expense', ["/accounting/other-expense"],['class' => 'a-link']) ?></p>
								<p>4. Look for branch transfer records:<br>
									<i class="fa fa-table"></i> <?= Html::a('Branch Transfer', ["/accounting/branch-transfer"],['class' => 'a-link']) ?></p>
								<p>5. Request for budget proposal:<br>
									<i class="fa fa-plus"></i> <?= Html::a('Create Request', ["/accounting/budget-proposal/create"],['class' => 'a-link']) ?></p>
								<p>6. Check list of budget proposals:<br>
									<i class="fa fa-file"></i> <?= Html::a('Requests', ["/accounting/budget-proposal"],['class' => 'a-link']) ?></p>
								<p>7. Record bank deposits:<br>
									<i class="fa fa-table"></i> <?= Html::a('Bank Deposit', ["/accounting/bank-deposit"],['class' => 'a-link']) ?></p>
							</div>
						<?php } ?>
						<?php if(!Yii::$app->user->isGuest && ($user_role == 'TopManagement')){ ?>
							<div class="col-md-4">
								<h4>Accounting Processes:</h4>
								<p>1. Approve and record budget proposals:<br>
									<i class="fa fa-table"></i> <?= Html::a('Budget Proposal', ["/accounting/budget-proposal"],['class' => 'a-link']) ?></p>
								<p>2. Add bundles of official receipts:<br>
									<i class="fa fa-plus"></i> <?= Html::a('Add OR', ["/accounting/season-or-list"],['class' => 'a-link']) ?></p>
							</div>
						<?php } ?>
						<?php if(!Yii::$app->user->isGuest && ($user_role == 'AreaManager' || $user_role == 'AccountingStaff' || $user_role == 'TopManagement')){ ?>
							<div class="col-md-4">
								<h4>Auditing Steps:</h4>
								<?php if(!Yii::$app->user->isGuest && ($user_role == 'AccountingStaff' || $user_role == 'AreaManager')){ ?>
								<p>1. To record cash on hand daily:<br>
									<i class="fa fa-table"></i> <?= Html::a('Daily Audit', ["/accounting/audit"],['class' => 'a-link']) ?></p>
								<?php } ?>

								<?php if(!Yii::$app->user->isGuest && ($user_role == 'AreaManager' || $user_role == 'AccountingStaff' || $user_role == 'TopManagement')){ ?>
									<p>1. To look for cut-off summary for program: <br>
										<i class="fa fa-bar-chart"></i> <?= Html::a('Cut-Off Summary: Program', ["/accounting/audit/cut-off-summary"],['class' => 'a-link']) ?></p>
									<p>2. To look for cut-off summary for icon: <br>
										<i class="fa fa-bar-chart"></i> <?= Html::a('Cut-Off Summary: Icon', ["/accounting/audit/cut-off-summary-icon"],['class' => 'a-link']) ?></p>
									<?php if(!Yii::$app->user->isGuest && ($user_role == 'TopManagement')){ ?>
										<h4>Managing User Accounts:</h4>
										<p>1. Manage user accounts, roles and permissions:<br>
											<i class="fa fa-users"></i> <?= Html::a('User Management', ["/user/admin"],['class' => 'a-link']) ?></p>
									<?php } ?>
								<?php } ?>
							</div>
						<?php } ?>
					</div> -->
				<?php /*Panel::end();*/ ?>
			</div>
			<div class="col-md-4 col-xs-12">
				<?php Panel::begin(['header' => 'My Tasks']); ?>
					<div style="max-height: 500px; overflow: auto;">
		                <?= ListView::widget([
			                  'dataProvider' => $dataProvider,
			                  'itemView' => function($model){
			                  	return '
					                      	<div class="">
					                      		<ul class="to_do">
													<li>'.Html::a('<p><i class="fa fa-exclamation-circle"></i> '.$model->message, ['/accounting/home/proceed', 'id' => $model->id]).'<span class="pull-right">'.Html::a('<i class="fa fa-close"></i>',['/accounting/home/delete/', 'id' => $model->id],[
															'data' => [
				                                                'method' => 'post',
				                                            ]
														]).'</span></p></li>
							                   	</ul>
					                      	</div>
					                      ';
			                  },
			                  'layout' => "<div class='text-info'>{pager}{summary}</div>\n{items}\n{pager}",
			                  'pager' => [
			                          'firstPageLabel' => 'First',
			                          'lastPageLabel'  => 'Last',
			                          'prevPageLabel' => '<i class="fa fa-backward"></i>',
			                          'nextPageLabel' => '<i class="fa fa-forward"></i>',
			                      ],
			              ]); ?>
					<?php Panel::end(); ?>
					</div>
				<!-- <?php Panel::begin(['header' => 'Available Reports']); ?>
					<p>Good Day!</p>
					<p>The following reports are now available online: </p>
					<ul>
						<li><?= Html::a('Student Information - Per Season',['/accounting/student/list'],['class' => 'a-link']) ?></li>
						<li><?= Html::a('Student Payment Updates - Per Season',['/accounting/student/list'],['class' => 'a-link']) ?></li>
						<?php if(!Yii::$app->user->isGuest && ($user_role == 'AreaManager' || $user_role == 'AccountingStaff' || $user_role == 'TopManagement')){ ?>
							<li><?= Html::a('Petty Expenses - Per Season (Yearly, Monthly, Per Cut Off, Daily)',['/accounting/petty-expense/'],['class' => 'a-link']) ?></li>
						<li><?= Html::a('Photocopy Expenses - Per Season (Yearly, Monthly, Per Cut Off, Daily)',['/accounting/photocopy-expense/'],['class' => 'a-link']) ?></li>
						<li><?= Html::a('Operating Expenses - Per Season (Yearly, Monthly, Per Cut Off, Daily)',['/accounting/operating-expense/'],['class' => 'a-link']) ?></li>
						<li><?= Html::a('Other Expenses - Per Season (Yearly, Monthly, Per Cut Off, Daily)',['/accounting/other-expense/'],['class' => 'a-link']) ?></li>
						<li><?= Html::a('Bank Deposits - Per Season (Yearly, Monthly, Per Cut Off, Daily)',['/accounting/bank-deposit/'],['class' => 'a-link']) ?></li>
						<li><?= Html::a('Branch Transfers - Per Branch Program (Yearly, Monthly, Per Cut Off, Daily)',['/accounting/branch-transfer/'],['class' => 'a-link']) ?></li>
						<li><?= Html::a('Cut Off Summary: Program',['/accounting/audit/cut-off-summary/'],['class' => 'a-link']) ?></li>
						<li><?= Html::a('Cut Off Summary: Icon',['/accounting/audit/cut-off-summary-icon/'],['class' => 'a-link']) ?></li>
						<?php } ?>
					</ul>
					<p>Additional reports will be posted soon.</p>
				<?php Panel::end(); ?> -->
			</div>
		</div>
		<div class="row">
			<div class="col-md-12 col-xs-12">
				<?php Panel::begin(['header' => 'Audit Summary']); ?>
					<?php $form = ActiveForm::begin(['id' => 'search-audit', 'method' => 'get']); ?>
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
								<div class="row">
									<div class="col-md-4 col-xs-4">
										<label>&nbsp;</label>
										<?= Html::submitButton('Apply Filters', ['class' => 'btn btn-primary btn-block']) ?>
									</div>
									<div class="col-md-4 col-xs-4">
										<label>&nbsp;</label>
										<?= Html::a('Refresh',['/accounting/home'],['class' => 'btn btn-info btn-block']) ?>
									</div>
								</div>
							</div>
						</div>
					<?php ActiveForm::end(); ?>	
					<br>
					<div id="auditSummary"></div>
				<?php Panel::end(); ?>
			</div>
		</div>
		<br>
		<div class="row">
			
			
		</div>
	<?php Panel::end(); ?>
</div>
<?php
  Modal::begin([
    'id' => 'genericModal',
    'size' => "modal-lg",
    'header' => '<div id="genericModalHeader"></div>'
  ]);
  echo '<div id="genericModalContent" style="min-height:480px"></div>';
  Modal::end();
?>
<style>
	.a-link{
		color: blue;
	}
	.progressbar {
	  margin: 0;
	  padding: 0;
	  counter-reset: step;
	}
	.progressbar li {
	  list-style-type: none;
	  width: <?= 100/count($cutoffDates) ?>%;
	  float: left;
	  font-size: 12px;
	  position: relative;
	  text-align: center;
	  text-transform: uppercase;
	  color: #7d7d7d;
	}
	.progressbar li:before {
	  width: 30px;
	  height: 30px;
	  content: counter(step);
	  counter-increment: step;
	  line-height: 30px;
	  border: 2px solid #7d7d7d;
	  display: block;
	  text-align: center;
	  margin: 0 auto 10px auto;
	  border-radius: 50%;
	  background-color: white;
	}
	.progressbar li:after {
	  width: 100%;
	  height: 2px;
	  content: '';
	  position: absolute;
	  background-color: #7d7d7d;
	  top: 15px;
	  left: -50%;
	  z-index: -1;
	}
	.progressbar li:first-child:after {
	  content: none;
	}
	.progressbar li.active {
	  color: orange;
	}
	.progressbar li.active:before {
	  border-color: orange;
	}
	.progressbar li.active + li:after {
	  background-color: #55b776;
	}
</style>
<?php
        $script = '
        	function loadData(data)
        	{
        		$.ajax({
                    url: "'.Url::to(['/accounting/home/audit-summary']).'?params=" + data,
                    beforeSend: function(){
                    	$("#auditSummary").html("<p class=\"text-center\"><i class=\"fa fa-spinner fa-spin\"></i></p>");
                    },
                    success: function (data) { 
                        $("#auditSummary").empty();
                        $("#auditSummary").hide();
                        $("#auditSummary").fadeIn();
                        $("#auditSummary").html(data);
                    }
                });
        	}

            $( document ).ready(function() {
            	$("#search-audit").on("beforeSubmit", function(e) {
			      e.preventDefault();
			      var form = $(this);
			      var formData = JSON.stringify(form.serializeArray());
			      $.ajax({
			        url: form.attr("action"),
			        type: form.attr("method"),
			        data: formData,
			        success: function (data) {
			        	loadData(formData);
			        },
			        error: function () {
			          alert("Something went wrong");
			        }
			      });
			      return false;
			    });

            	loadData([]);
            });
';
$this->registerJs($script);
   
?>