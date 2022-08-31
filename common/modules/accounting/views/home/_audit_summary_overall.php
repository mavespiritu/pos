<?php 
use yii\helpers\Html;
use frontend\assets\AppAsset;
$asset = AppAsset::register($this);

$width = ceil(100/8);

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
<h3 class="text-center">
	<?= Html::img($asset->baseUrl.'/images/logo-blue.png',['style' => 'height: 45px; width: 175px;']) ?>
</h3>
<h5 class="text-center">Toprank Integrated Systems<br>
						Accounting<br>
						Audit Summary Report: Overall
</h5>

<table class="table table-bordered table-condensed table-hover table-responsive" style="width: 30%;">
	<tbody>
		<tr>
			<td><b>Date Covered</b></td>
			<td><?= empty($date) ? 'ALL' : $date[0][0].' - '.$date[0][1] ?></td>
		</tr>
	</tbody>
</table>
<table class="table table-bordered table-condensed">
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
									<td>&nbsp;</td>
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
