<?php 
use yiister\gentelella\widgets\Panel;
use yii\helpers\Html;
use frontend\assets\AppAsset;
$asset = AppAsset::register($this);

$incomeEnrolmentTotal = 0;
$freebieTotal = 0;
$foodTotal = 0;
$suppliesTotal = 0;
$loadTotal = 0;
$fareTotal = 0;
$photocopyTotal = 0;
$otherTotal = 0;
$bankDepositsTotal = 0;
$staffSalaryTotal = 0;
$cashPfTotal = 0;
$rentTotal = 0;
$utilitiesTotal = 0;
$equipmentAndLaborTotal = 0;
$birAndDocsTotal = 0;
$marketingTotal = 0;
$total = [];

$incomeEnrolmentTotal2 = 0;
$freebieTotal2 = 0;
$foodTotal2 = 0;
$suppliesTotal2 = 0;
$loadTotal2 = 0;
$fareTotal2 = 0;
$photocopyTotal2 = 0;
$otherTotal2 = 0;
$bankDepositsTotal2 = 0;
$staffSalaryTotal2 = 0;
$cashPfTotal2 = 0;
$rentTotal2 = 0;
$utilitiesTotal2 = 0;
$equipmentAndLaborTotal2 = 0;
$birAndDocsTotal2 = 0;
$marketingTotal2 = 0;
$total2 = [];
?>

<?php Panel::begin(); ?>
<span class="pull-right">
	<?= Html::a('<i class="fa fa-file-pdf-o"></i>&nbsp;Generate Report', ['/accounting/report/extract-monthly-summary', 'id' => $id, 'season' => $season, 'branchProgram' => $branchProgram],['class' => 'btn btn-primary']) ?>
</span>
<h3 class="text-center"><?= Html::img($asset->baseUrl.'/images/logo-blue.png',['style' => 'height: 90px; width: 350px;']) ?></h3>
<h4 class="text-center">Toprank Integrated Systems</h4>
<h4 class="text-center">Accounting</h4>
<h4 class="text-center">Monthly Summary Report</h4>
<br>
<br>
<div class="row">
	<div class="col-md-6 col-xs-12">
		<table class="table table-bordered table-condensed table-hover table-responsive" style="width: 70%;">
			<tbody>
				<tr>
					<td><b>Branch - Program</b></td>
					<td align=right><?= $selectedBranchProgram['name'] ?></td>
				</tr>
				<tr>
					<td><b>Season</b></td>
					<td align=right><?= 'SEASON '.$selectedSeason->name ?></td>
				</tr>
				<tr>
					<td><b>Dates Covered</b></td>
					<td align=right><?= $dates[0].' - '.$dates[1] ?></td>
				</tr>
			</tbody>
		</table>
	</div>
</div>

<?php Panel::begin(['header' => 'Cash']); ?>
<div style="overflow-x: auto;">
	<table class="table table-bordered table-condensed table-striped table-hovered table-responsive">
		<thead>
			<tr>
				<th>Date</th>
				<th>Details</th>
				<?php foreach($cutoffs as $cutoff){ ?>
					<th>
						<?php if(date("Y",strtotime($cutoff['start'])) == date("Y",strtotime($cutoff['end']))){ ?>
							<?php if(date("m",strtotime($cutoff['start'])) == date("m",strtotime($cutoff['end']))){ ?>
								<?= date("M d",strtotime($cutoff['start'])).' - '.date("d, Y",strtotime($cutoff['end'])) ?>
							<?php }else{ ?>
								<?= date("M d",strtotime($cutoff['start'])).' - '.date("M d, Y",strtotime($cutoff['end'])) ?>
							<?php } ?>
						<?php }else{ ?>
							<?= date("M d, Y",strtotime($cutoff['start'])).' - '.date("M d, Y",strtotime($cutoff['end'])) ?>
						<?php } ?>	
					</th>
				<?php } ?>
				<th>TOTAL</th>
			</tr>
		</thead>
		<tbody>
			<tr><td colspan=<?= 3+count($cutoffs) ?>>INCOME</td></tr>
			<tr>
				<td>&nbsp;</td>
				<td>Enrolments</td>
				<?php foreach($cutoffs as $cutoff){ ?>
					<?php $total['income'][$cutoff['start'].' - '.$cutoff['end']] = 0; ?>
					<?php $total['expense'][$cutoff['start'].' - '.$cutoff['end']] = 0; ?>
					<?php if(!empty($cashData)){ ?>
						<?php if(isset($cashData['incomeEnrolments'])){ ?>
							<?php if(isset($cashData['incomeEnrolments'][$cutoff['start'].' - '.$cutoff['end']])){ ?>
								<td align="right"><?= number_format($cashData['incomeEnrolments'][$cutoff['start'].' - '.$cutoff['end']], 2) ?></td>
								<?php $incomeEnrolmentTotal+=$cashData['incomeEnrolments'][$cutoff['start'].' - '.$cutoff['end']]; ?>
								<?php $total['income'][$cutoff['start'].' - '.$cutoff['end']]+=$cashData['incomeEnrolments'][$cutoff['start'].' - '.$cutoff['end']]; ?>
							<?php }else{ ?>
								<td align="right">0.00</td>
							<?php } ?>
						<?php }else{ ?>
								<td align="right">0.00</td>
						<?php } ?>
					<?php }else{ ?>
							<td align="right">0.00</td>
					<?php } ?>
				<?php } ?>
				<td align="right"><?= number_format($incomeEnrolmentTotal, 2) ?></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>Freebies</td>
				<?php foreach($cutoffs as $cutoff){ ?>
					<?php if(!empty($cashData)){ ?>
						<?php if(isset($cashData['freebies'])){ ?>
							<?php if(isset($cashData['freebies'][$cutoff['start'].' - '.$cutoff['end']])){ ?>
								<td align="right"><?= number_format($cashData['freebies'][$cutoff['start'].' - '.$cutoff['end']], 2) ?></td>
								<?php $freebieTotal+=$cashData['freebies'][$cutoff['start'].' - '.$cutoff['end']]; ?>
								<?php $total['income'][$cutoff['start'].' - '.$cutoff['end']]+=$cashData['freebies'][$cutoff['start'].' - '.$cutoff['end']]; ?>
							<?php }else{ ?>
								<td align="right">0.00</td>
							<?php } ?>
						<?php }else{ ?>
								<td align="right">0.00</td>
						<?php } ?>
					<?php }else{ ?>
							<td align="right">0.00</td>
					<?php } ?>
				<?php } ?>
				<td align="right"><?= number_format($freebieTotal, 2) ?></td>
			</tr>
			<tr>
				<td colspan=2><b>TOTAL INCOME</b></td>
				<?php foreach($cutoffs as $cutoff){ ?>
					<td align="right"><?= '<b>'.number_format($total['income'][$cutoff['start'].' - '.$cutoff['end']], 2).'</b>' ?></td>
				<?php } ?>
				<td align="right"><?= '<b>'.number_format($incomeEnrolmentTotal + $freebieTotal, 2).'</b>' ?></td>
			</tr>
			<tr>
				<td colspan=<?= 3+count($cutoffs) ?>>EXPENSES</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>PETTY</td>
				<?php foreach($cutoffs as $cutoff){ ?>
					<td>&nbsp;</td>
				<?php } ?>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td align=right>Food</td>
				<?php foreach($cutoffs as $cutoff){ ?>
					<?php if(!empty($cashData)){ ?>
						<?php if(isset($cashData['pettyExpenses'])){ ?>
							<?php if(isset($cashData['pettyExpenses'][$cutoff['start'].' - '.$cutoff['end']])){ ?>
								<td align="right"><?= number_format($cashData['pettyExpenses'][$cutoff['start'].' - '.$cutoff['end']]['food'], 2) ?></td>
								<?php $foodTotal+=$cashData['pettyExpenses'][$cutoff['start'].' - '.$cutoff['end']]['food']; ?>
								<?php $total['expense'][$cutoff['start'].' - '.$cutoff['end']]+=$cashData['pettyExpenses'][$cutoff['start'].' - '.$cutoff['end']]['food']; ?>
							<?php }else{ ?>
								<td align="right">0.00</td>
							<?php } ?>
						<?php }else{ ?>
								<td align="right">0.00</td>
						<?php } ?>
					<?php }else{ ?>
							<td align="right">0.00</td>
					<?php } ?>
				<?php } ?>
				<td align="right"><?= number_format($foodTotal, 2) ?></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td align=right>Supplies</td>
				<?php foreach($cutoffs as $cutoff){ ?>
					<?php if(!empty($cashData)){ ?>
						<?php if(isset($cashData['pettyExpenses'])){ ?>
							<?php if(isset($cashData['pettyExpenses'][$cutoff['start'].' - '.$cutoff['end']])){ ?>
								<td align="right"><?= number_format($cashData['pettyExpenses'][$cutoff['start'].' - '.$cutoff['end']]['supplies'], 2) ?></td>
								<?php $suppliesTotal+=$cashData['pettyExpenses'][$cutoff['start'].' - '.$cutoff['end']]['supplies']; ?>
								<?php $total['expense'][$cutoff['start'].' - '.$cutoff['end']]+=$cashData['pettyExpenses'][$cutoff['start'].' - '.$cutoff['end']]['supplies']; ?>
							<?php }else{ ?>
								<td align="right">0.00</td>
							<?php } ?>
						<?php }else{ ?>
								<td align="right">0.00</td>
						<?php } ?>
					<?php }else{ ?>
							<td align="right">0.00</td>
					<?php } ?>
				<?php } ?>
				<td align="right"><?= number_format($suppliesTotal, 2) ?></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td align=right>Load</td>
				<?php foreach($cutoffs as $cutoff){ ?>
					<?php if(!empty($cashData)){ ?>
						<?php if(isset($cashData['pettyExpenses'])){ ?>
							<?php if(isset($cashData['pettyExpenses'][$cutoff['start'].' - '.$cutoff['end']])){ ?>
								<td align="right"><?= number_format($cashData['pettyExpenses'][$cutoff['start'].' - '.$cutoff['end']]['load'], 2) ?></td>
								<?php $loadTotal+=$cashData['pettyExpenses'][$cutoff['start'].' - '.$cutoff['end']]['load']; ?>
								<?php $total['expense'][$cutoff['start'].' - '.$cutoff['end']]+=$cashData['pettyExpenses'][$cutoff['start'].' - '.$cutoff['end']]['load']; ?>
							<?php }else{ ?>
								<td align="right">0.00</td>
							<?php } ?>
						<?php }else{ ?>
								<td align="right">0.00</td>
						<?php } ?>
					<?php }else{ ?>
							<td align="right">0.00</td>
					<?php } ?>
				<?php } ?>
				<td align="right"><?= number_format($loadTotal, 2) ?></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td align=right>Fare</td>
				<?php foreach($cutoffs as $cutoff){ ?>
					<?php if(!empty($cashData)){ ?>
						<?php if(isset($cashData['pettyExpenses'])){ ?>
							<?php if(isset($cashData['pettyExpenses'][$cutoff['start'].' - '.$cutoff['end']])){ ?>
								<td align="right"><?= number_format($cashData['pettyExpenses'][$cutoff['start'].' - '.$cutoff['end']]['fare'], 2) ?></td>
								<?php $fareTotal+=$cashData['pettyExpenses'][$cutoff['start'].' - '.$cutoff['end']]['fare']; ?>
								<?php $total['expense'][$cutoff['start'].' - '.$cutoff['end']]+=$cashData['pettyExpenses'][$cutoff['start'].' - '.$cutoff['end']]['fare']; ?>
							<?php }else{ ?>
								<td align="right">0.00</td>
							<?php } ?>
						<?php }else{ ?>
								<td align="right">0.00</td>
						<?php } ?>
					<?php }else{ ?>
							<td align="right">0.00</td>
					<?php } ?>
				<?php } ?>
				<td align="right"><?= number_format($fareTotal, 2) ?></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>PHOTOCOPY</td>
				<?php foreach($cutoffs as $cutoff){ ?>
					<?php if(!empty($cashData)){ ?>
						<?php if(isset($cashData['photocopyExpenses'])){ ?>
							<?php if(isset($cashData['photocopyExpenses'][$cutoff['start'].' - '.$cutoff['end']])){ ?>
								<td align="right"><?= number_format($cashData['photocopyExpenses'][$cutoff['start'].' - '.$cutoff['end']], 2) ?></td>
								<?php $photocopyTotal+=$cashData['photocopyExpenses'][$cutoff['start'].' - '.$cutoff['end']]; ?>
								<?php $total['expense'][$cutoff['start'].' - '.$cutoff['end']]+=$cashData['photocopyExpenses'][$cutoff['start'].' - '.$cutoff['end']]; ?>
							<?php }else{ ?>
								<td align="right">0.00</td>
							<?php } ?>
						<?php }else{ ?>
								<td align="right">0.00</td>
						<?php } ?>
					<?php }else{ ?>
							<td align="right">0.00</td>
					<?php } ?>
				<?php } ?>
				<td align="right"><?= number_format($photocopyTotal, 2) ?></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>BANK DEPOSITS</td>
				<?php foreach($cutoffs as $cutoff){ ?>
					<?php if(!empty($cashData)){ ?>
						<?php if(isset($cashData['bankDeposits'])){ ?>
							<?php if(isset($cashData['bankDeposits'][$cutoff['start'].' - '.$cutoff['end']])){ ?>
								<td align="right"><?= number_format($cashData['bankDeposits'][$cutoff['start'].' - '.$cutoff['end']], 2) ?></td>
								<?php $bankDepositsTotal+=$cashData['bankDeposits'][$cutoff['start'].' - '.$cutoff['end']]; ?>
								<?php $total['expense'][$cutoff['start'].' - '.$cutoff['end']]+=$cashData['bankDeposits'][$cutoff['start'].' - '.$cutoff['end']]; ?>
							<?php }else{ ?>
								<td align="right">0.00</td>
							<?php } ?>
						<?php }else{ ?>
								<td align="right">0.00</td>
						<?php } ?>
					<?php }else{ ?>
							<td align="right">0.00</td>
					<?php } ?>
				<?php } ?>
				<td align="right"><?= number_format($bankDepositsTotal, 2) ?></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>OPERATING</td>
				<?php foreach($cutoffs as $cutoff){ ?>
					<td>&nbsp;</td>
				<?php } ?>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td align=right>Staff Salary</td>
				<?php foreach($cutoffs as $cutoff){ ?>
					<?php if(!empty($cashData)){ ?>
						<?php if(isset($cashData['operatingExpenses'])){ ?>
							<?php if(isset($cashData['operatingExpenses'][$cutoff['start'].' - '.$cutoff['end']])){ ?>
								<td align="right"><?= number_format($cashData['operatingExpenses'][$cutoff['start'].' - '.$cutoff['end']]['staffSalary'], 2) ?></td>
								<?php $staffSalaryTotal+=$cashData['operatingExpenses'][$cutoff['start'].' - '.$cutoff['end']]['staffSalary']; ?>
								<?php $total['expense'][$cutoff['start'].' - '.$cutoff['end']]+=$cashData['operatingExpenses'][$cutoff['start'].' - '.$cutoff['end']]['staffSalary']; ?>
							<?php }else{ ?>
								<td align="right">0.00</td>
							<?php } ?>
						<?php }else{ ?>
								<td align="right">0.00</td>
						<?php } ?>
					<?php }else{ ?>
							<td align="right">0.00</td>
					<?php } ?>
				<?php } ?>
				<td align="right"><?= number_format($staffSalaryTotal, 2) ?></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td align=right>Cash PF</td>
				<?php foreach($cutoffs as $cutoff){ ?>
					<?php if(!empty($cashData)){ ?>
						<?php if(isset($cashData['operatingExpenses'])){ ?>
							<?php if(isset($cashData['operatingExpenses'][$cutoff['start'].' - '.$cutoff['end']])){ ?>
								<td align="right"><?= number_format($cashData['operatingExpenses'][$cutoff['start'].' - '.$cutoff['end']]['cashPf'], 2) ?></td>
								<?php $cashPfTotal+=$cashData['operatingExpenses'][$cutoff['start'].' - '.$cutoff['end']]['cashPf']; ?>
								<?php $total['expense'][$cutoff['start'].' - '.$cutoff['end']]+=$cashData['operatingExpenses'][$cutoff['start'].' - '.$cutoff['end']]['cashPf']; ?>
							<?php }else{ ?>
								<td align="right">0.00</td>
							<?php } ?>
						<?php }else{ ?>
								<td align="right">0.00</td>
						<?php } ?>
					<?php }else{ ?>
							<td align="right">0.00</td>
					<?php } ?>
				<?php } ?>
				<td align="right"><?= number_format($cashPfTotal, 2) ?></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td align=right>Rent</td>
				<?php foreach($cutoffs as $cutoff){ ?>
					<?php if(!empty($cashData)){ ?>
						<?php if(isset($cashData['operatingExpenses'])){ ?>
							<?php if(isset($cashData['operatingExpenses'][$cutoff['start'].' - '.$cutoff['end']])){ ?>
								<td align="right"><?= number_format($cashData['operatingExpenses'][$cutoff['start'].' - '.$cutoff['end']]['rent'], 2) ?></td>
								<?php $rentTotal+=$cashData['operatingExpenses'][$cutoff['start'].' - '.$cutoff['end']]['rent']; ?>
								<?php $total['expense'][$cutoff['start'].' - '.$cutoff['end']]+=$cashData['operatingExpenses'][$cutoff['start'].' - '.$cutoff['end']]['rent']; ?>
							<?php }else{ ?>
								<td align="right">0.00</td>
							<?php } ?>
						<?php }else{ ?>
								<td align="right">0.00</td>
						<?php } ?>
					<?php }else{ ?>
							<td align="right">0.00</td>
					<?php } ?>
				<?php } ?>
				<td align="right"><?= number_format($rentTotal, 2) ?></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td align=right>Utilities</td>
				<?php foreach($cutoffs as $cutoff){ ?>
					<?php if(!empty($cashData)){ ?>
						<?php if(isset($cashData['operatingExpenses'])){ ?>
							<?php if(isset($cashData['operatingExpenses'][$cutoff['start'].' - '.$cutoff['end']])){ ?>
								<td align="right"><?= number_format($cashData['operatingExpenses'][$cutoff['start'].' - '.$cutoff['end']]['utilities'], 2) ?></td>
								<?php $utilitiesTotal+=$cashData['operatingExpenses'][$cutoff['start'].' - '.$cutoff['end']]['utilities']; ?>
								<?php $total['expense'][$cutoff['start'].' - '.$cutoff['end']]+=$cashData['operatingExpenses'][$cutoff['start'].' - '.$cutoff['end']]['utilities']; ?>
							<?php }else{ ?>
								<td align="right">0.00</td>
							<?php } ?>
						<?php }else{ ?>
								<td align="right">0.00</td>
						<?php } ?>
					<?php }else{ ?>
							<td align="right">0.00</td>
					<?php } ?>
				<?php } ?>
				<td align="right"><?= number_format($utilitiesTotal, 2) ?></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td align=right>Equipment and Labor</td>
				<?php foreach($cutoffs as $cutoff){ ?>
					<?php if(!empty($cashData)){ ?>
						<?php if(isset($cashData['operatingExpenses'])){ ?>
							<?php if(isset($cashData['operatingExpenses'][$cutoff['start'].' - '.$cutoff['end']])){ ?>
								<td align=right><?= number_format($cashData['operatingExpenses'][$cutoff['start'].' - '.$cutoff['end']]['equipmentAndLabor'], 2) ?></td>
								<?php $equipmentAndLaborTotal+=$cashData['operatingExpenses'][$cutoff['start'].' - '.$cutoff['end']]['equipmentAndLabor']; ?>
								<?php $total['expense'][$cutoff['start'].' - '.$cutoff['end']]+=$cashData['operatingExpenses'][$cutoff['start'].' - '.$cutoff['end']]['equipmentAndLabor']; ?>
							<?php }else{ ?>
								<td align=right>0.00</td>
							<?php } ?>
						<?php }else{ ?>
								<td align=right>0.00</td>
						<?php } ?>
					<?php }else{ ?>
							<td align=right>0.00</td>
					<?php } ?>
				<?php } ?>
				<td align="right"><?= number_format($equipmentAndLaborTotal, 2) ?></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td align=right>BIR and Docs</td>
				<?php foreach($cutoffs as $cutoff){ ?>
					<?php if(!empty($cashData)){ ?>
						<?php if(isset($cashData['operatingExpenses'])){ ?>
							<?php if(isset($cashData['operatingExpenses'][$cutoff['start'].' - '.$cutoff['end']])){ ?>
								<td align="right"><?= number_format($cashData['operatingExpenses'][$cutoff['start'].' - '.$cutoff['end']]['birAndDocs'], 2) ?></td>
								<?php $birAndDocsTotal+=$cashData['operatingExpenses'][$cutoff['start'].' - '.$cutoff['end']]['birAndDocs']; ?>
								<?php $total['expense'][$cutoff['start'].' - '.$cutoff['end']]+=$cashData['operatingExpenses'][$cutoff['start'].' - '.$cutoff['end']]['birAndDocs']; ?>
							<?php }else{ ?>
								<td align="right">0.00</td>
							<?php } ?>
						<?php }else{ ?>
								<td align="right">0.00</td>
						<?php } ?>
					<?php }else{ ?>
							<td align="right">0.00</td>
					<?php } ?>
				<?php } ?>
				<td align="right"><?= number_format($birAndDocsTotal, 2) ?></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td align=right>Marketing</td>
				<?php foreach($cutoffs as $cutoff){ ?>
					<?php if(!empty($cashData)){ ?>
						<?php if(isset($cashData['operatingExpenses'])){ ?>
							<?php if(isset($cashData['operatingExpenses'][$cutoff['start'].' - '.$cutoff['end']])){ ?>
								<td align="right"><?= number_format($cashData['operatingExpenses'][$cutoff['start'].' - '.$cutoff['end']]['marketing'], 2) ?></td>
								<?php $marketingTotal+=$cashData['operatingExpenses'][$cutoff['start'].' - '.$cutoff['end']]['marketing']; ?>
								<?php $total['expense'][$cutoff['start'].' - '.$cutoff['end']]+=$cashData['operatingExpenses'][$cutoff['start'].' - '.$cutoff['end']]['marketing']; ?>
							<?php }else{ ?>
								<td align="right">0.00</td>
							<?php } ?>
						<?php }else{ ?>
								<td align="right">0.00</td>
						<?php } ?>
					<?php }else{ ?>
							<td align="right">0.00</td>
					<?php } ?>
				<?php } ?>
				<td align="right"><?= number_format($marketingTotal, 2) ?></td>
			</tr>
			<tr>
				<td colspan=2><b>TOTAL EXPENSES</b></td>
				<?php foreach($cutoffs as $cutoff){ ?>
					<td align=right><?= '<b>'.number_format($total['expense'][$cutoff['start'].' - '.$cutoff['end']], 2).'</b>' ?></td>
				<?php } ?>
				<td align=right><?= '<b>'.number_format($foodTotal + $suppliesTotal + $loadTotal + $fareTotal + $photocopyTotal + $otherTotal + $bankDepositsTotal + $otherTotal + $staffSalaryTotal + $cashPfTotal + $rentTotal + $utilitiesTotal + $equipmentAndLaborTotal +$birAndDocsTotal + $marketingTotal, 2).'</b>' ?></td>
			</tr>
		</tbody>
	</table>
	<table style="width: 20%;">
		<tr>
			<td style="width: 50%;"><b>Beginning COH:</b></td>
			<td align=right><?= number_format($beginningCash['beginning_coh'],2) ?></td>
		</tr>
		<tr>
			<td><b>Total Income:</b></td>
			<td align=right><?=  number_format($incomeEnrolmentTotal + $freebieTotal, 2) ?></td>
		</tr>
		<tr>
			<td><b>&nbsp;</b></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td><b>Total Expenses:</b></td>
			<td align=right><?= number_format($foodTotal + $suppliesTotal + $loadTotal + $fareTotal + $photocopyTotal + $otherTotal + $bankDepositsTotal + $otherTotal + $staffSalaryTotal + $cashPfTotal + $rentTotal + $utilitiesTotal + $equipmentAndLaborTotal +$birAndDocsTotal + $marketingTotal, 2) ?></td>
		</tr>
		<tr>
			<td><b>&nbsp;</b></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td style="width: 50%;"><b>Ending COH:</b></td>
			<td align=right><u><b><?= number_format(($beginningCash['beginning_coh'] + $incomeEnrolmentTotal + $freebieTotal) - ($foodTotal + $suppliesTotal + $loadTotal + $fareTotal + $photocopyTotal + $otherTotal + $bankDepositsTotal + $otherTotal + $staffSalaryTotal + $cashPfTotal + $rentTotal + $utilitiesTotal + $equipmentAndLaborTotal +$birAndDocsTotal + $marketingTotal),2) ?></b></u></td>
		</tr>
	</table>
	<hr>
</div>
<?php Panel::end(); ?>
<?php Panel::begin(['header' => 'Non-Cash']); ?>
<div style="overflow-x: auto;">
	<table class="table table-bordered table-condensed table-striped table-hovered table-responsive">
		<thead>
			<tr>
				<th>Date</th>
				<th>Details</th>
				<?php foreach($cutoffs as $cutoff){ ?>
					<th>
						<?php if(date("Y",strtotime($cutoff['start'])) == date("Y",strtotime($cutoff['end']))){ ?>
							<?php if(date("m",strtotime($cutoff['start'])) == date("m",strtotime($cutoff['end']))){ ?>
								<?= date("M d",strtotime($cutoff['start'])).' - '.date("d, Y",strtotime($cutoff['end'])) ?>
							<?php }else{ ?>
								<?= date("M d",strtotime($cutoff['start'])).' - '.date("M d, Y",strtotime($cutoff['end'])) ?>
							<?php } ?>
						<?php }else{ ?>
							<?= date("M d, Y",strtotime($cutoff['start'])).' - '.date("M d, Y",strtotime($cutoff['end'])) ?>
						<?php } ?>	
					</th>
				<?php } ?>
				<th>TOTAL</th>
			</tr>
		</thead>
		<tbody>
			<tr><td colspan=<?= 3+count($cutoffs) ?>>INCOME</td></tr>
			<tr>
				<td>&nbsp;</td>
				<td>Enrolments</td>
				<?php foreach($cutoffs as $cutoff){ ?>
					<?php $total2['income'][$cutoff['start'].' - '.$cutoff['end']] = 0; ?>
					<?php $total2['expense'][$cutoff['start'].' - '.$cutoff['end']] = 0; ?>
					<?php if(!empty($nonCashData)){ ?>
						<?php if(isset($nonCashData['incomeEnrolments'])){ ?>
							<?php if(isset($nonCashData['incomeEnrolments'][$cutoff['start'].' - '.$cutoff['end']])){ ?>
								<td align="right"><?= number_format($nonCashData['incomeEnrolments'][$cutoff['start'].' - '.$cutoff['end']], 2) ?></td>
								<?php $incomeEnrolmentTotal2+=$nonCashData['incomeEnrolments'][$cutoff['start'].' - '.$cutoff['end']]; ?>
								<?php $total2['income'][$cutoff['start'].' - '.$cutoff['end']]+=$nonCashData['incomeEnrolments'][$cutoff['start'].' - '.$cutoff['end']]; ?>
							<?php }else{ ?>
								<td align="right">0.00</td>
							<?php } ?>
						<?php }else{ ?>
								<td align="right">0.00</td>
						<?php } ?>
					<?php }else{ ?>
							<td align="right">0.00</td>
					<?php } ?>
				<?php } ?>
				<td align="right"><?= number_format($incomeEnrolmentTotal2, 2) ?></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>Freebies</td>
				<?php foreach($cutoffs as $cutoff){ ?>
					<?php if(!empty($nonCashData)){ ?>
						<?php if(isset($nonCashData['freebies'])){ ?>
							<?php if(isset($nonCashData['freebies'][$cutoff['start'].' - '.$cutoff['end']])){ ?>
								<td align="right"><?= number_format($nonCashData['freebies'][$cutoff['start'].' - '.$cutoff['end']], 2) ?></td>
								<?php $freebieTotal2+=$nonCashData['freebies'][$cutoff['start'].' - '.$cutoff['end']]; ?>
								<?php $total2['income'][$cutoff['start'].' - '.$cutoff['end']]+=$nonCashData['freebies'][$cutoff['start'].' - '.$cutoff['end']]; ?>
							<?php }else{ ?>
								<td align="right">0.00</td>
							<?php } ?>
						<?php }else{ ?>
								<td align="right">0.00</td>
						<?php } ?>
					<?php }else{ ?>
							<td align="right">0.00</td>
					<?php } ?>
				<?php } ?>
				<td align="right"<?= number_format($freebieTotal2, 2) ?></td>
			</tr>
			<tr>
				<td colspan=2><b>TOTAL INCOME</b></td>
				<?php foreach($cutoffs as $cutoff){ ?>
					<td align="right"><?= '<b>'.number_format($total2['income'][$cutoff['start'].' - '.$cutoff['end']], 2).'</b>' ?></td>
				<?php } ?>
				<td align="right"><?= '<b>'.number_format($incomeEnrolmentTotal2 + $freebieTotal2, 2).'</b>' ?></td>
			</tr>
			<tr>
				<td colspan=<?= 3+count($cutoffs) ?>>EXPENSES</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>PETTY</td>
				<?php foreach($cutoffs as $cutoff){ ?>
					<td>&nbsp;</td>
				<?php } ?>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td align=right>Food</td>
				<?php foreach($cutoffs as $cutoff){ ?>
					<?php if(!empty($nonCashData)){ ?>
						<?php if(isset($nonCashData['pettyExpenses'])){ ?>
							<?php if(isset($nonCashData['pettyExpenses'][$cutoff['start'].' - '.$cutoff['end']])){ ?>
								<td align="right"><?= number_format($nonCashData['pettyExpenses'][$cutoff['start'].' - '.$cutoff['end']]['food'], 2) ?></td>
								<?php $foodTotal2+=$nonCashData['pettyExpenses'][$cutoff['start'].' - '.$cutoff['end']]['food']; ?>
								<?php $total2['expense'][$cutoff['start'].' - '.$cutoff['end']]+=$nonCashData['pettyExpenses'][$cutoff['start'].' - '.$cutoff['end']]['food']; ?>
							<?php }else{ ?>
								<td align="right">0.00</td>
							<?php } ?>
						<?php }else{ ?>
								<td align="right">0.00</td>
						<?php } ?>
					<?php }else{ ?>
							<td align="right">0.00</td>
					<?php } ?>
				<?php } ?>
				<td align="right"><?= number_format($foodTotal2, 2) ?></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td align=right>Supplies</td>
				<?php foreach($cutoffs as $cutoff){ ?>
					<?php if(!empty($nonCashData)){ ?>
						<?php if(isset($nonCashData['pettyExpenses'])){ ?>
							<?php if(isset($nonCashData['pettyExpenses'][$cutoff['start'].' - '.$cutoff['end']])){ ?>
								<td align="right"><?= number_format($nonCashData['pettyExpenses'][$cutoff['start'].' - '.$cutoff['end']]['supplies'], 2) ?></td>
								<?php $suppliesTotal2+=$nonCashData['pettyExpenses'][$cutoff['start'].' - '.$cutoff['end']]['supplies']; ?>
								<?php $total2['expense'][$cutoff['start'].' - '.$cutoff['end']]+=$nonCashData['pettyExpenses'][$cutoff['start'].' - '.$cutoff['end']]['supplies']; ?>
							<?php }else{ ?>
								<td align="right">0.00</td>
							<?php } ?>
						<?php }else{ ?>
								<td align="right">0.00</td>
						<?php } ?>
					<?php }else{ ?>
							<td align="right">0.00</td>
					<?php } ?>
				<?php } ?>
				<td align="right"><?= number_format($suppliesTotal2, 2) ?></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td align=right>Load</td>
				<?php foreach($cutoffs as $cutoff){ ?>
					<?php if(!empty($nonCashData)){ ?>
						<?php if(isset($nonCashData['pettyExpenses'])){ ?>
							<?php if(isset($nonCashData['pettyExpenses'][$cutoff['start'].' - '.$cutoff['end']])){ ?>
								<td align="right"><?= number_format($nonCashData['pettyExpenses'][$cutoff['start'].' - '.$cutoff['end']]['load'], 2) ?></td>
								<?php $loadTotal2+=$nonCashData['pettyExpenses'][$cutoff['start'].' - '.$cutoff['end']]['load']; ?>
								<?php $total2['expense'][$cutoff['start'].' - '.$cutoff['end']]+=$nonCashData['pettyExpenses'][$cutoff['start'].' - '.$cutoff['end']]['load']; ?>
							<?php }else{ ?>
								<td align="right">0.00</td>
							<?php } ?>
						<?php }else{ ?>
								<td align="right">0.00</td>
						<?php } ?>
					<?php }else{ ?>
							<td align="right">0.00</td>
					<?php } ?>
				<?php } ?>
				<td align="right"><?= number_format($loadTotal2, 2) ?></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td align=right>Fare</td>
				<?php foreach($cutoffs as $cutoff){ ?>
					<?php if(!empty($nonCashData)){ ?>
						<?php if(isset($nonCashData['pettyExpenses'])){ ?>
							<?php if(isset($nonCashData['pettyExpenses'][$cutoff['start'].' - '.$cutoff['end']])){ ?>
								<td align="right"><?= number_format($nonCashData['pettyExpenses'][$cutoff['start'].' - '.$cutoff['end']]['fare'], 2) ?></td>
								<?php $fareTotal2+=$nonCashData['pettyExpenses'][$cutoff['start'].' - '.$cutoff['end']]['fare']; ?>
								<?php $total2['expense'][$cutoff['start'].' - '.$cutoff['end']]+=$nonCashData['pettyExpenses'][$cutoff['start'].' - '.$cutoff['end']]['fare']; ?>
							<?php }else{ ?>
								<td align="right">0.00</td>
							<?php } ?>
						<?php }else{ ?>
								<td align="right">0.00</td>
						<?php } ?>
					<?php }else{ ?>
							<td align="right">0.00</td>
					<?php } ?>
				<?php } ?>
				<td align="right"><?= number_format($fareTotal2, 2) ?></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>PHOTOCOPY</td>
				<?php foreach($cutoffs as $cutoff){ ?>
					<?php if(!empty($nonCashData)){ ?>
						<?php if(isset($nonCashData['photocopyExpenses'])){ ?>
							<?php if(isset($nonCashData['photocopyExpenses'][$cutoff['start'].' - '.$cutoff['end']])){ ?>
								<td align="right"><?= number_format($nonCashData['photocopyExpenses'][$cutoff['start'].' - '.$cutoff['end']], 2) ?></td>
								<?php $photocopyTotal2+=$nonCashData['photocopyExpenses'][$cutoff['start'].' - '.$cutoff['end']]; ?>
								<?php $total2['expense'][$cutoff['start'].' - '.$cutoff['end']]+=$nonCashData['photocopyExpenses'][$cutoff['start'].' - '.$cutoff['end']]; ?>
							<?php }else{ ?>
								<td align="right">0.00</td>
							<?php } ?>
						<?php }else{ ?>
								<td align="right">0.00</td>
						<?php } ?>
					<?php }else{ ?>
							<td align="right">0.00</td>
					<?php } ?>
				<?php } ?>
				<td align="right"><?= number_format($photocopyTotal2, 2) ?></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>BANK DEPOSITS</td>
				<?php foreach($cutoffs as $cutoff){ ?>
					<?php if(!empty($nonCashData)){ ?>
						<?php if(isset($nonCashData['bankDeposits'])){ ?>
							<?php if(isset($nonCashData['bankDeposits'][$cutoff['start'].' - '.$cutoff['end']])){ ?>
								<td align="right"><?= number_format($nonCashData['bankDeposits'][$cutoff['start'].' - '.$cutoff['end']], 2) ?></td>
								<?php $bankDepositsTotal2+=$nonCashData['bankDeposits'][$cutoff['start'].' - '.$cutoff['end']]; ?>
								<?php $total2['expense'][$cutoff['start'].' - '.$cutoff['end']]+=$nonCashData['bankDeposits'][$cutoff['start'].' - '.$cutoff['end']]; ?>
							<?php }else{ ?>
								<td align="right">0.00</td>
							<?php } ?>
						<?php }else{ ?>
								<td align="right">0.00</td>
						<?php } ?>
					<?php }else{ ?>
							<td align="right">0.00</td>
					<?php } ?>
				<?php } ?>
				<td align="right"><?= number_format($bankDepositsTotal2, 2) ?></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>OPERATING</td>
				<?php foreach($cutoffs as $cutoff){ ?>
					<td>&nbsp;</td>
				<?php } ?>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td align=right>Staff Salary</td>
				<?php foreach($cutoffs as $cutoff){ ?>
					<?php if(!empty($nonCashData)){ ?>
						<?php if(isset($nonCashData['operatingExpenses'])){ ?>
							<?php if(isset($nonCashData['operatingExpenses'][$cutoff['start'].' - '.$cutoff['end']])){ ?>
								<td align="right"><?= number_format($nonCashData['operatingExpenses'][$cutoff['start'].' - '.$cutoff['end']]['staffSalary'], 2) ?></td>
								<?php $staffSalaryTotal2+=$nonCashData['operatingExpenses'][$cutoff['start'].' - '.$cutoff['end']]['staffSalary']; ?>
								<?php $total2['expense'][$cutoff['start'].' - '.$cutoff['end']]+=$nonCashData['operatingExpenses'][$cutoff['start'].' - '.$cutoff['end']]['staffSalary']; ?>
							<?php }else{ ?>
								<td align="right">0.00</td>
							<?php } ?>
						<?php }else{ ?>
								<td align="right">0.00</td>
						<?php } ?>
					<?php }else{ ?>
							<td align="right">0.00</td>
					<?php } ?>
				<?php } ?>
				<td align="right"><?= number_format($staffSalaryTotal2, 2) ?></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td align=right>Cash PF</td>
				<?php foreach($cutoffs as $cutoff){ ?>
					<?php if(!empty($nonCashData)){ ?>
						<?php if(isset($nonCashData['operatingExpenses'])){ ?>
							<?php if(isset($nonCashData['operatingExpenses'][$cutoff['start'].' - '.$cutoff['end']])){ ?>
								<td align="right"><?= number_format($nonCashData['operatingExpenses'][$cutoff['start'].' - '.$cutoff['end']]['cashPf'], 2) ?></td>
								<?php $cashPfTotal2+=$nonCashData['operatingExpenses'][$cutoff['start'].' - '.$cutoff['end']]['cashPf']; ?>
								<?php $total2['expense'][$cutoff['start'].' - '.$cutoff['end']]+=$nonCashData['operatingExpenses'][$cutoff['start'].' - '.$cutoff['end']]['cashPf']; ?>
							<?php }else{ ?>
								<td align="right">0.00</td>
							<?php } ?>
						<?php }else{ ?>
								<td align="right">0.00</td>
						<?php } ?>
					<?php }else{ ?>
							<td align="right">0.00</td>
					<?php } ?>
				<?php } ?>
				<td align="right"><?= number_format($cashPfTotal2, 2) ?></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td align=right>Rent</td>
				<?php foreach($cutoffs as $cutoff){ ?>
					<?php if(!empty($nonCashData)){ ?>
						<?php if(isset($nonCashData['operatingExpenses'])){ ?>
							<?php if(isset($nonCashData['operatingExpenses'][$cutoff['start'].' - '.$cutoff['end']])){ ?>
								<td align="right"><?= number_format($nonCashData['operatingExpenses'][$cutoff['start'].' - '.$cutoff['end']]['rent'], 2) ?></td>
								<?php $rentTotal2+=$nonCashData['operatingExpenses'][$cutoff['start'].' - '.$cutoff['end']]['rent']; ?>
								<?php $total2['expense'][$cutoff['start'].' - '.$cutoff['end']]+=$nonCashData['operatingExpenses'][$cutoff['start'].' - '.$cutoff['end']]['rent']; ?>
							<?php }else{ ?>
								<td align="right">0.00</td>
							<?php } ?>
						<?php }else{ ?>
								<td align="right">0.00</td>
						<?php } ?>
					<?php }else{ ?>
							<td align="right">0.00</td>
					<?php } ?>
				<?php } ?>
				<td align=right><?= number_format($rentTotal2, 2) ?></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td align=right>Utilities</td>
				<?php foreach($cutoffs as $cutoff){ ?>
					<?php if(!empty($nonCashData)){ ?>
						<?php if(isset($nonCashData['operatingExpenses'])){ ?>
							<?php if(isset($nonCashData['operatingExpenses'][$cutoff['start'].' - '.$cutoff['end']])){ ?>
								<td align=right><?= number_format($nonCashData['operatingExpenses'][$cutoff['start'].' - '.$cutoff['end']]['utilities'], 2) ?></td>
								<?php $utilitiesTotal2+=$nonCashData['operatingExpenses'][$cutoff['start'].' - '.$cutoff['end']]['utilities']; ?>
								<?php $total2['expense'][$cutoff['start'].' - '.$cutoff['end']]+=$nonCashData['operatingExpenses'][$cutoff['start'].' - '.$cutoff['end']]['utilities']; ?>
							<?php }else{ ?>
								<td align=right>0.00</td>
							<?php } ?>
						<?php }else{ ?>
								<td align=right>0.00</td>
						<?php } ?>
					<?php }else{ ?>
							<td align=right>0.00</td>
					<?php } ?>
				<?php } ?>
				<td align=right><?= number_format($utilitiesTotal2, 2) ?></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td align=right>Equipment and Labor</td>
				<?php foreach($cutoffs as $cutoff){ ?>
					<?php if(!empty($nonCashData)){ ?>
						<?php if(isset($nonCashData['operatingExpenses'])){ ?>
							<?php if(isset($nonCashData['operatingExpenses'][$cutoff['start'].' - '.$cutoff['end']])){ ?>
								<td align=right><?= number_format($nonCashData['operatingExpenses'][$cutoff['start'].' - '.$cutoff['end']]['equipmentAndLabor'], 2) ?></td>
								<?php $equipmentAndLaborTotal2+=$nonCashData['operatingExpenses'][$cutoff['start'].' - '.$cutoff['end']]['equipmentAndLabor']; ?>
								<?php $total2['expense'][$cutoff['start'].' - '.$cutoff['end']]+=$nonCashData['operatingExpenses'][$cutoff['start'].' - '.$cutoff['end']]['equipmentAndLabor']; ?>
							<?php }else{ ?>
								<td align=right>0.00</td>
							<?php } ?>
						<?php }else{ ?>
								<td align=right>0.00</td>
						<?php } ?>
					<?php }else{ ?>
							<td align=right>0.00</td>
					<?php } ?>
				<?php } ?>
				<td align=right><?= number_format($equipmentAndLaborTotal2, 2) ?></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td align=right>BIR and Docs</td>
				<?php foreach($cutoffs as $cutoff){ ?>
					<?php if(!empty($nonCashData)){ ?>
						<?php if(isset($nonCashData['operatingExpenses'])){ ?>
							<?php if(isset($nonCashData['operatingExpenses'][$cutoff['start'].' - '.$cutoff['end']])){ ?>
								<td align=right><?= number_format($nonCashData['operatingExpenses'][$cutoff['start'].' - '.$cutoff['end']]['birAndDocs'], 2) ?></td>
								<?php $birAndDocsTotal2+=$nonCashData['operatingExpenses'][$cutoff['start'].' - '.$cutoff['end']]['birAndDocs']; ?>
								<?php $total2['expense'][$cutoff['start'].' - '.$cutoff['end']]+=$nonCashData['operatingExpenses'][$cutoff['start'].' - '.$cutoff['end']]['birAndDocs']; ?>
							<?php }else{ ?>
								<td align=right>0.00</td>
							<?php } ?>
						<?php }else{ ?>
								<td align=right>0.00</td>
						<?php } ?>
					<?php }else{ ?>
							<td align=right>0.00</td>
					<?php } ?>
				<?php } ?>
				<td align=right><?= number_format($birAndDocsTotal2, 2) ?></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td align=right>Marketing</td>
				<?php foreach($cutoffs as $cutoff){ ?>
					<?php if(!empty($nonCashData)){ ?>
						<?php if(isset($nonCashData['operatingExpenses'])){ ?>
							<?php if(isset($nonCashData['operatingExpenses'][$cutoff['start'].' - '.$cutoff['end']])){ ?>
								<td align=right><?= number_format($nonCashData['operatingExpenses'][$cutoff['start'].' - '.$cutoff['end']]['marketing'], 2) ?></td>
								<?php $marketingTotal2+=$nonCashData['operatingExpenses'][$cutoff['start'].' - '.$cutoff['end']]['marketing']; ?>
								<?php $total2['expense'][$cutoff['start'].' - '.$cutoff['end']]+=$nonCashData['operatingExpenses'][$cutoff['start'].' - '.$cutoff['end']]['marketing']; ?>
							<?php }else{ ?>
								<td align=right>0.00</td>
							<?php } ?>
						<?php }else{ ?>
								<td align=right>0.00</td>
						<?php } ?>
					<?php }else{ ?>
							<td align=right>0.00</td>
					<?php } ?>
				<?php } ?>
				<td align=right><?= number_format($marketingTotal2, 2) ?></td>
			</tr>
			<tr>
				<td colspan=2><b>TOTAL EXPENSES</b></td>
				<?php foreach($cutoffs as $cutoff){ ?>
					<td align=right><?= '<b>'.number_format($total2['expense'][$cutoff['start'].' - '.$cutoff['end']], 2).'</b>' ?></td>
				<?php } ?>
				<td align=right><?= '<b>'.number_format($foodTotal2 + $suppliesTotal2 + $loadTotal2 + $fareTotal2 + $photocopyTotal2 + $otherTotal2 + $bankDepositsTotal2 + $otherTotal2 + $staffSalaryTotal2 + $cashPfTotal2 + $rentTotal2 + $utilitiesTotal2 + $equipmentAndLaborTotal2 +$birAndDocsTotal2 + $marketingTotal2, 2).'</b>' ?></td>
			</tr>
		</tbody>
	</table>
	<table style="width: 20%;">
		<tr>
			<td style="width: 50%;"><b>Beginning COB:</b></td>
			<td align=right><?= number_format($beginningCash['beginning_cob'],2) ?></td>
		</tr>
		<tr>
			<td><b>Total Income:</b></td>
			<td align=right><?=  number_format($incomeEnrolmentTotal2 + $freebieTotal2, 2) ?></td>
		</tr>
		<tr>
			<td><b>&nbsp;</b></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td><b>Total Expenses:</b></td>
			<td align=right><?= number_format($foodTotal2 + $suppliesTotal2 + $loadTotal2 + $fareTotal2 + $photocopyTotal2 + $otherTotal2 + $bankDepositsTotal2 + $otherTotal2 + $staffSalaryTotal2 + $cashPfTotal2 + $rentTotal2 + $utilitiesTotal2 + $equipmentAndLaborTotal2 + $birAndDocsTotal2 + $marketingTotal2, 2) ?></td>
		</tr>
		<tr>
			<td><b>&nbsp;</b></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td style="width: 50%;"><b>Ending COB:</b></td>
			<td align=right><u><b><?= number_format(($beginningCash['beginning_cob'] + $incomeEnrolmentTotal2 + $freebieTotal2) - ($foodTotal2 + $suppliesTotal2 + $loadTotal2 + $fareTotal2 + $photocopyTotal2 + $otherTotal2 + $bankDepositsTotal2 + $otherTotal2 + $staffSalaryTotal2 + $cashPfTotal2 + $rentTotal2 + $utilitiesTotal2 + $equipmentAndLaborTotal2 + $birAndDocsTotal2 + $marketingTotal2),2) ?></b></u></td>
		</tr>
	</table>
</div>
<?php Panel::end(); ?>