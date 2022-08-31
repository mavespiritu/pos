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
<div class="" role="tabpanel" data-example-id="togglable-tabs">
	<ul id="myTab" class="nav nav-tabs bar_tabs" role="tablist">
		<li role="presentation" class="active"><a href="#overall" role="tab" id="overall-tab" data-toggle="tab" aria-expanded="false">Overall</a></li>
		<li role="presentation" class=""><a href="#cash" role="tab" id="cash-tab" data-toggle="tab" aria-expanded="true">Cash</a></li>
		<li role="presentation" class=""><a href="#non-cash" id="non-cash-tab" role="tab" data-toggle="tab" aria-expanded="true">Non-Cash</a></li>
	</ul>
	<div id="myTabContent" class="tab-content">
    	<div role="tabpanel" class="tab-pane fade active in" id="overall" aria-labelledby="overall-tab">
    		<div class="pull-right">
				<?= Html::a('Generate Report',['/accounting/home/generate-overall', 'date' => json_encode($date)],['class' => 'btn btn-success']) ?>
			</div>
			<div class="clearfix"></div>
			<br>
			<h4>Dates Applied: <?= date('F j, Y', strtotime($date[0][0])) ?> - <?= date('F j, Y', strtotime($date[0][1])) ?></h4>
    		<div style="min-height: 500px; overflow: auto; max-height: 500px;">
				<table class="table table-responsive table-bordered table-condensed" id="overall-table">
					<thead>
						<tr>
							<th rowspan="2">Branch - Program</th>
							<th colspan="5"><center>Target</center></th>
							<th colspan="8"><center>Actual (Cash + Non-Cash)</center></th>
						</tr>
						<tr>
							<th>Gross</th>
							<th>Gross Income</th>
							<th>Expenses</th>
							<th>Expected Income</th>
							<th>Net Income</th>
							<th>Beginning COH</th>
							<th>Gross Income</th>
							<th>GI (%)</th>
							<th>Bank Deposits</th>
							<th>Total Expenses</th>
							<th>TE (%)</th>
							<th>Ending COH</th>
							<th>NI (%)</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<?php if(!empty($auditSummary)){ ?>
								<?php foreach($auditSummary as $summary){ ?>
									<?php if($summary['netIncomeTotal'] != 0){ ?>
										<tr style="background: #F7F7F7;">
											<td><b><?= $summary['name'] ?></b></td>
											<td align=right><b><?= number_format($summary['targetGross'], 2) ?></b></td>
											<td align=right><b><?= number_format($summary['targetGrossIncome'], 2) ?></b></td>
											<td align=right><b><?= number_format($summary['targetExpenses'], 2) ?></b></td>
											<td align=right><b><?= number_format($summary['targetExpectedIncome'], 2) ?></b></td>
											<td align=right><b><?= number_format($summary['targetNetIncome'], 2) ?></b></td>
											<td align=right><b><?= number_format($summary['beginningCoh'] + $summary['beginningCob'], 2) ?></b></td>
											<td align=right><b><?= number_format($summary['incomeTotal'], 2) ?></b></td>
											<td align=right><b><?= $summary['targetGrossIncome'] > 0 ? number_format(($summary['incomeTotal']/$summary['targetGrossIncome'])*100, 2) : '0.00' ?></b></td>
											<td align=right><b><?= number_format($summary['bankDepositsTotal'], 2) ?></b></td>
											<td align=right><b><?= number_format($summary['expenseTotal'], 2) ?></b></td>
											<td align=right><b><?= $summary['targetExpenses'] > 0 ? number_format(($summary['expenseTotal']/$summary['targetExpenses'])*100, 2) : '0.00' ?></b></td>
											<td align=right><b><?= number_format($summary['netIncomeTotal'], 2) >= 0 ? number_format($summary['netIncomeTotal'], 2) : '<font color=red>'.number_format($summary['netIncomeTotal'], 2).'</font>'?></b></td>
											<td align=right><b><?= $summary['targetNetIncome'] > 0 ? number_format(($summary['netIncomeTotal']/$summary['targetNetIncome'])*100, 2) : '0.00' ?></b></td>
										</tr>
										<?php if(!empty($auditSummarySeason)){ ?>
											<?php foreach($auditSummarySeason as $summarySeason){ ?>
												<?php if($summary['id'] == $summarySeason['branch_program_id']){ ?>
													<tr>
														<td align=right><?= $summarySeason['name'] ?></td>
														<td align=right><?= number_format($summarySeason['targetGross'], 2) ?></td>
														<td align=right><?= number_format($summarySeason['targetGrossIncome'], 2) ?></td>
														<td align=right><?= number_format($summarySeason['targetExpenses'], 2) ?></td>
														<td align=right><?= number_format($summarySeason['targetExpectedIncome'], 2) ?></td>
														<td align=right><?= number_format($summarySeason['targetNetIncome'], 2) ?></td>
														<td align=right><?= number_format($summarySeason['beginningCoh'] + $summarySeason['beginningCob'], 2) ?></td>
														<td align=right><?= number_format($summarySeason['incomeTotal'], 2) ?></td>
														<td align=right><b><?= $summarySeason['targetGrossIncome'] > 0 ? number_format(($summarySeason['incomeTotal']/$summarySeason['targetGrossIncome'])*100, 2) : '0.00' ?></b></td>
														<td align=right><?= number_format($summarySeason['bankDepositsTotal'], 2) ?></td>
														<td align=right><?= number_format($summarySeason['expenseTotal'], 2) ?></td>
														<td align=right><b><?= $summarySeason['targetExpenses'] > 0 ? number_format(($summarySeason['expenseTotal']/$summarySeason['targetExpenses'])*100, 2) : '0.00' ?></b></td>
														<td align=right><?= number_format($summarySeason['netIncomeTotal'], 2) >= 0 ? number_format($summarySeason['netIncomeTotal'], 2) : '<font color=red>'.number_format($summarySeason['netIncomeTotal'], 2).'</font>'?></td>
														<td align=right><b><?= $summarySeason['targetNetIncome'] > 0 ? number_format(($summarySeason['netIncomeTotal']/$summarySeason['targetNetIncome'])*100, 2) : '0.00' ?></b></td>
													</tr>
												<?php } ?>
											<?php } ?>
										<?php } ?>
										
										<?php $targetGrossTotal+=$summary['targetGross']; ?>
										<?php $targetGrossIncomeTotal+=$summary['targetGrossIncome']; ?>
										<?php $targetExpensesTotal+=$summary['targetExpenses']; ?>
										<?php $targetExpectedIncomeTotal+=$summary['targetExpectedIncome']; ?>
										<?php $targetNetIncomeTotal+=$summary['targetNetIncome']; ?>
										<?php $totalbeginning += $summary['beginningCoh'] + $summary['beginningCob']; ?>
										<?php $totalincomeTotal+=$summary['incomeTotal']; ?>
										<?php $totalbankDepositsTotal+=$summary['bankDepositsTotal']; ?>
										<?php $totalexpenseTotal+=$summary['expenseTotal']; ?>
										<?php $totalNetIncomeTotal+=$summary['netIncomeTotal']; ?>
									<?php } continue; ?>
								<?php } ?>
							<?php } ?>
						</tr>
						<tr>
							<td align=right><b>TOTAL</b></td>
							<td align=right><b><?= number_format($targetGrossTotal, 2) ?></b></td>
							<td align=right><b><?= number_format($targetGrossIncomeTotal, 2) ?></b></td>
							<td align=right><b><?= number_format($targetExpensesTotal, 2) ?></b></td>
							<td align=right><b><?= number_format($targetExpectedIncomeTotal, 2) ?></b></td>
							<td align=right><b><?= number_format($targetNetIncomeTotal, 2) ?></b></td>
							<td align=right><b><?= number_format($totalbeginning, 2) ?></b></td>
							<td align=right><b><?= number_format($totalincomeTotal, 2) ?></b></td>
							<td align=right><b><?= $targetGrossIncomeTotal > 0 ? number_format(($totalincomeTotal/$targetGrossIncomeTotal)*100, 2) : '0.00' ?></b></td>
							<td align=right><b><?= number_format($totalbankDepositsTotal, 2) ?></b></td>
							<td align=right><b><?= number_format($totalexpenseTotal, 2) ?></b></td>
							<td align=right><b><?= $targetExpensesTotal > 0 ? number_format(($totalexpenseTotal/$targetExpensesTotal)*100, 2) : '0.00' ?></b></td>
							<td align=right><b><?= number_format($totalNetIncomeTotal, 2) >= 0 ? number_format($totalNetIncomeTotal, 2) : '<font color=red>'.number_format($totalNetIncomeTotal, 2).'</font>'?></b></td>
							<td align=right><b><?= $targetNetIncomeTotal > 0 ? number_format(($totalNetIncomeTotal/$targetNetIncomeTotal)*100, 2) : '0.00' ?></b></td>
						</tr>
					</tbody>
				</table>
				<p><b>GI</b> - Gross Income</p>
				<p><b>TE</b> - Total Expenses</p>
				<p><b>NI</b> - Net Income</p>
			</div>
    	</div>
    	<div role="tabpanel" class="tab-pane fade" id="cash" aria-labelledby="cash-tab">
    		<div class="pull-right">
				<?= Html::a('Generate Report',['/accounting/home/generate-audit-cash', 'date' => json_encode($date)],['class' => 'btn btn-success']) ?>
			</div>
			<div class="clearfix"></div>
			<br>
			<h4>Dates Applied: <?= date('F j, Y', strtotime($date[0][0])) ?> - <?= date('F j, Y', strtotime($date[0][1])) ?></h4>
			<div style="min-height: 500px; overflow: auto; max-height: 500px;">
				<table class="table table-responsive table-bordered table-condensed" id="cash-table">
					<thead>
						<tr>
							<th rowspan=2>Branch - Program</th>
							<th colspan=5><center>Actual</center></th>
						</tr>
						<tr>	
							<th>Beginning COH</th>
							<th>Gross Income</th>
							<th>Bank Deposits</th>
							<th>Total Expenses</th>
							<th>Ending COH</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<?php if(!empty($auditSummaryCash)){ ?>
								<?php foreach($auditSummaryCash as $summary){ ?>
									<?php if($summary['netIncomeTotal'] != 0){ ?>
										<tr style="background: #F7F7F7;">
											<td style="width: 30%;"><b><?= $summary['name'] ?></b></td>
											<td align=right><b><?= number_format($summary['beginningCoh'], 2) ?></b></td>
											<td align=right><b><?= number_format($summary['incomeTotal'], 2) ?></b></td>
											<td align=right><b><?= number_format($summary['bankDepositsTotal'], 2) ?></b></td>
											<td align=right><b><?= number_format($summary['expenseTotal'], 2) ?></b></td>
											<td align=right><b><?= number_format($summary['netIncomeTotal'], 2) >= 0 ? number_format($summary['netIncomeTotal'], 2) : '<font color=red>'.number_format($summary['netIncomeTotal'], 2).'</font>'?></b></td>
										</tr>
										<?php if(!empty($auditSummaryCashSeason)){ ?>
											<?php foreach($auditSummaryCashSeason as $summarySeason){ ?>
												<?php if($summary['id'] == $summarySeason['branch_program_id']){ ?>
													<tr>
														<td align=right><?= $summarySeason['name'] ?></td>
														<td align=right><?= number_format($summarySeason['beginningCoh'], 2) ?></td>
														<td align=right><?= number_format($summarySeason['incomeTotal'], 2) ?></td>
														<td align=right><?= number_format($summarySeason['bankDepositsTotal'], 2) ?></td>
														<td align=right><?= number_format($summarySeason['expenseTotal'], 2) ?></td>
														<td align=right><?= number_format($summarySeason['netIncomeTotal'], 2) >= 0 ? number_format($summarySeason['netIncomeTotal'], 2) : '<font color=red>'.number_format($summarySeason['netIncomeTotal'], 2).'</font>'?></td>
													</tr>
												<?php } ?>
											<?php } ?>
										<?php } ?>	
										<?php $totalbeginningCoh+=$summary['beginningCoh']; ?>
										<?php $totalincomeCashTotal+=$summary['incomeTotal']; ?>
										<?php $totalbankDepositsCashTotal+=$summary['bankDepositsTotal']; ?>
										<?php $totalexpenseCashTotal+=$summary['expenseTotal']; ?>
										<?php $totalNetIncomeCashTotal+=$summary['netIncomeTotal']; ?>
									<?php } continue; ?>
								<?php } ?>
							<?php } ?>
						</tr>
						<tr>
							<td align=right><b>TOTAL</b></td>
							<td align=right><b><?= number_format($totalbeginningCoh, 2) ?></b></td>
							<td align=right><b><?= number_format($totalincomeCashTotal, 2) ?></b></td>
							<td align=right><b><?= number_format($totalbankDepositsCashTotal, 2) ?></b></td>
							<td align=right><b><?= number_format($totalexpenseCashTotal, 2) ?></b></td>
							<td align=right><b><?= number_format($totalNetIncomeCashTotal, 2) >= 0 ? number_format($totalNetIncomeCashTotal, 2) : '<font color=red>'.number_format($totalNetIncomeCashTotal, 2).'</font>'?></b></td>
						</tr>
					</tbody>
				</table>
			</div>
    	</div>
    	<div role="tabpanel" class="tab-pane fade" id="non-cash" aria-labelledby="non-cash-tab">
    		<div class="pull-right">
				<?= Html::a('Generate Report',['/accounting/home/generate-audit-non-cash', 'date' => json_encode($date)],['class' => 'btn btn-success']) ?>
			</div>
			<div class="clearfix"></div>
			<br>
			<h4>Dates Applied: <?= date('F j, Y', strtotime($date[0][0])) ?> - <?= date('F j, Y', strtotime($date[0][1])) ?></h4>
			<div style="min-height: 500px; overflow: auto; max-height: 500px;">
				<table class="table table-responsive table-bordered table-condensed" id="non-cash-table">
					<thead>
						<tr>
							<th rowspan=2>Branch - Program</th>
							<th colspan=5><center>Actual</center></th>
						</tr>
						<tr>
							<th>Beginning COB</th>
							<th>Gross Income</th>
							<th>Bank Deposits</th>
							<th>Total Expenses</th>
							<th>Ending COB</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<?php if(!empty($auditSummaryNonCash)){ ?>
								<?php foreach($auditSummaryNonCash as $summary){ ?>
									<?php if($summary['netIncomeTotal'] != 0){ ?>
										<tr style="background: #F7F7F7;">
											<td style="width: 30%;"><b><?= $summary['name'] ?></b></td>
											<td align=right><b><?= number_format($summary['beginningCob'], 2) ?></b></td>
											<td align=right><b><?= number_format($summary['incomeTotal'], 2) ?></b></td>
											<td align=right><b><?= number_format($summary['bankDepositsTotal'], 2) ?></b></td>
											<td align=right><b><?= number_format($summary['expenseTotal'], 2) ?></b></td>
											<td align=right><b><?= number_format($summary['netIncomeTotal'], 2) >= 0 ? number_format($summary['netIncomeTotal'], 2) : '<font color=red>'.number_format($summary['netIncomeTotal'], 2).'</font>'?></b></td>
										</tr>
										<?php if(!empty($auditSummaryNonCashSeason)){ ?>
											<?php foreach($auditSummaryNonCashSeason as $summarySeason){ ?>
												<?php if($summary['id'] == $summarySeason['branch_program_id']){ ?>
													<tr>
														<td align=right><?= $summarySeason['name'] ?></td>
														<td align=right><?= number_format($summarySeason['beginningCob'], 2) ?></td>
														<td align=right><?= number_format($summarySeason['incomeTotal'], 2) ?></td>
														<td align=right><?= number_format($summarySeason['bankDepositsTotal'], 2) ?></td>
														<td align=right><?= number_format($summarySeason['expenseTotal'], 2) ?></td>
														<td align=right><?= number_format($summarySeason['netIncomeTotal'], 2) >= 0 ? number_format($summarySeason['netIncomeTotal'], 2) : '<font color=red>'.number_format($summarySeason['netIncomeTotal'], 2).'</font>'?></td>
													</tr>
												<?php } ?>
											<?php } ?>
										<?php } ?>	
										<?php $totalbeginningCob+=$summary['beginningCob']; ?>
										<?php $totalincomeNonCashTotal+=$summary['incomeTotal']; ?>
										<?php $totalbankDepositsNonCashTotal+=$summary['bankDepositsTotal']; ?>
										<?php $totalexpenseNonCashTotal+=$summary['expenseTotal']; ?>
										<?php $totalNetIncomeNonCashTotal+=$summary['netIncomeTotal']; ?>
									<?php } continue; ?>
								<?php } ?>
							<?php } ?>
						</tr>
						<tr>
							<td align=right><b>TOTAL</b></td>
							<td align=right><b><?= number_format($totalbeginningCob, 2) ?></b></td>
							<td align=right><b><?= number_format($totalincomeNonCashTotal, 2) ?></b></td>
							<td align=right><b><?= number_format($totalbankDepositsNonCashTotal, 2) ?></b></td>
							<td align=right><b><?= number_format($totalexpenseNonCashTotal, 2) ?></b></td>
							<td align=right><b><?= number_format($totalNetIncomeNonCashTotal, 2) >= 0 ? number_format($totalNetIncomeNonCashTotal, 2) : '<font color=red>'.number_format($totalNetIncomeNonCashTotal, 2).'</font>'?></b></td>
						</tr>
					</tbody>
				</table>
			</div>
    	</div>
    </div>
</div>