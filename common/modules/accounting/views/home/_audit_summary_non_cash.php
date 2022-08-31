<?php 
use yii\helpers\Html;
use frontend\assets\AppAsset;
$asset = AppAsset::register($this);

$width = ceil(100/8);
$totalbeginningCob = 0;
$totalincomeNonCashTotal = 0;
$totalbankDepositsNonCashTotal = 0;
$totalexpenseNonCashTotal = 0;
$totalNetIncomeNonCashTotal = 0;

?>
<h3 class="text-center">
	<?= Html::img($asset->baseUrl.'/images/logo-blue.png',['style' => 'height: 45px; width: 175px;']) ?>
</h3>
<h5 class="text-center">Toprank Integrated Systems<br>
						Accounting<br>
						Audit Summary Report: Non-Cash
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
									<td align=right><?= number_format($summary['beginningCob'], 2) ?></td>
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
