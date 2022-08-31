<?php 
use yiister\gentelella\widgets\Panel;
use yii\helpers\Html;
use frontend\assets\AppAsset;
$asset = AppAsset::register($this);

$incomeTotal = 0;
$expenseTotal = 0;
$enrolmentTotal = 0;
$freebieTotal = 0;
$budgetProposalTotal = 0;
$foodTotal = 0;
$supplyTotal = 0;
$loadTotal = 0;
$fareTotal = 0;
$photocopyTotal = 0;
$staffSalaryTotal = 0;
$cashPfTotal = 0;
$rentTotal = 0;
$utilitiesTotal = 0;
$equipmentAndLaborTotal = 0;
$bir_and_docsTotal = 0;
$marketingTotal = 0;
$branchTransferTotal = 0;
$otherTotal = 0;
$bankDepositTotal = 0; 
$incomeTotal2 = 0;
$expenseTotal2 = 0;
$enrolmentTotal2 = 0;
$freebieTotal2 = 0;
$budgetProposalTotal2 = 0;
$foodTotal2 = 0;
$supplyTotal2 = 0;
$loadTotal2 = 0;
$fareTotal2 = 0;
$photocopyTotal2 = 0;
$staffSalaryTotal2 = 0;
$cashPfTotal2 = 0;
$rentTotal2 = 0;
$utilitiesTotal2 = 0;
$equipmentAndLaborTotal2 = 0;
$bir_and_docsTotal2 = 0;
$marketingTotal2 = 0;
$branchTransferTotal2 = 0;
$otherTotal2 = 0;
$bankDepositTotal2 = 0; 
?>
<?php Panel::begin(); ?>
<span class="pull-right">
	<?php if($branchProgramName == 'All Branch Programs'){ ?>
		<?= Html::a('<i class="fa fa-file-pdf-o"></i>&nbsp;Generate Report', ['/accounting/audit/generate-icon', 'id' => $id],['class' => 'btn btn-primary']) ?>
	<?php }else{ ?>
		<?= $seasonName == '' ? Html::a('<i class="fa fa-file-pdf-o"></i>&nbsp;Generate Report', ['/accounting/audit/generate-icon', 'id' => $id, 'branch_program_id' => $branchProgram->id],['class' => 'btn btn-primary']) : Html::a('<i class="fa fa-file-pdf-o"></i>&nbsp;Generate Report', ['/accounting/audit/generate-icon', 'id' => $id, 'branch_program_id' => $branchProgram->id, 'season' => $season->id],['class' => 'btn btn-primary']) ?>
	<?php } ?>
</span>
<br>
<br>
<h3 class="text-center">
	<?= Html::img($asset->baseUrl.'/images/logo-blue.png',['style' => 'height: 90px; width: 350px;']) ?>
</h3>
<h4 class="text-center">Toprank Integrated Systems</h4>
<h4 class="text-center">Accounting</h4>
<h4 class="text-center">Cut-Off Summary Report: Icon</h4>

<table class="table table-bordered table-condensed table-striped table-hovered" style="width: 100%;">
	<tbody>
		<tr>
			<td><b>Branch - Program</b></td>
			<td align=right><?= $branchProgramName ?></td>
		</tr>
		<tr>
			<td><b>Season</b></td>
			<td align=right><?= $seasonName ?></td>
		</tr>
		<tr>
			<td><b>Cut-Off</b></td>
			<td align=right><?= $cutoff['start'].' - '.$cutoff['end'] ?></td>
		</tr>
	</tbody>
</table>	

<?php Panel::begin(['header' => 'Cash']); ?>
<table class="table table-bordered table-condensed table-striped table-hovered">
	<thead>
		<tr>
			<th>Date</th>
			<th>Details</th>
			<?php foreach($dates as $date){ ?>
				<th><?= date("M j", strtotime($date)) ?></th>
			<?php } ?>
			<th>TOTAL</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td colspan=<?= 3+count($dates) ?>>INCOME</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>Enrolments</td>
			<?php foreach($dates as $date){ ?>
				<?php if(isset($data['incomes']['enrolments']['Cash'][$date])){ ?>
					<td><?= '<b>'.number_format($data['incomes']['enrolments']['Cash'][$date]['total'], 2).'</b>' ?></td>
					<?php $totals['incomes'][$date]+=$data['incomes']['enrolments']['Cash'][$date]['total']; ?>
					<?php $enrolmentTotal+=$data['incomes']['enrolments']['Cash'][$date]['total']; ?>
				<?php }else{ ?>
					<td><?= number_format(0, 2) ?></td>
				<?php } ?>
			<?php } ?>
			<td align=right><?= '<b>'.number_format($enrolmentTotal, 2).'</b>' ?></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>Freebies</td>
			<?php foreach($dates as $date){ ?>
				<?php if(isset($data['incomes']['freebies']['Cash'][$date])){ ?>
					<td><?= '<b>'.number_format($data['incomes']['freebies']['Cash'][$date]['total'], 2).'</b>' ?></td>
					<?php $totals['incomes'][$date]+=$data['incomes']['freebies']['Cash'][$date]['total']; ?>
					<?php $freebieTotal+=$data['incomes']['freebies']['Cash'][$date]['total']; ?>
				<?php }else{ ?>
					<td><?= number_format(0, 2) ?></td>
				<?php } ?>
			<?php } ?>
			<td align=right><?= '<b>'.number_format($freebieTotal, 2).'</b>' ?></td>
		</tr>
		<tr>
			<td colspan=2><b>TOTAL INCOME</b></td>
			<?php foreach($dates as $date){ ?>
				<td><?= '<b>'.number_format($totals['incomes'][$date], 2).'</b>' ?></td>
				<?php $incomeTotal+=$totals['incomes'][$date]; ?>
			<?php } ?>
			<td align=right><?= '<b>'.number_format($incomeTotal, 2).'</b>' ?></td>
		</tr>
		<tr>
			<td colspan=<?= 3+count($dates) ?>>EXPENSES</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>PETTY</td>
			<?php foreach($dates as $date){ ?>
				<td>&nbsp;</td>
			<?php } ?>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td align=right>Food</td>
			<?php foreach($dates as $date){ ?>
				<?php if(isset($data['expenses']['pettyExpenses']['Cash'][$date])){ ?>
					<td><?= '<b>'.number_format($data['expenses']['pettyExpenses']['Cash'][$date]['foodTotal'], 2).'</b>' ?></td>
					<?php $totals['expenses'][$date]+=$data['expenses']['pettyExpenses']['Cash'][$date]['foodTotal']; ?>
					<?php $foodTotal+=$data['expenses']['pettyExpenses']['Cash'][$date]['foodTotal']; ?>
				<?php }else{ ?>
					<td><?= number_format(0, 2) ?></td>
				<?php } ?>
			<?php } ?>
			<td align=right><?= '<b>'.number_format($foodTotal, 2).'</b>' ?></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td align=right>Supplies</td>
			<?php foreach($dates as $date){ ?>
				<?php if(isset($data['expenses']['pettyExpenses']['Cash'][$date])){ ?>
					<td><?= '<b>'.number_format($data['expenses']['pettyExpenses']['Cash'][$date]['supplyTotal'], 2).'</b>' ?></td>
					<?php $totals['expenses'][$date]+=$data['expenses']['pettyExpenses']['Cash'][$date]['supplyTotal']; ?>
					<?php $supplyTotal+=$data['expenses']['pettyExpenses']['Cash'][$date]['supplyTotal']; ?>
				<?php }else{ ?>
					<td><?= number_format(0, 2) ?></td>
				<?php } ?>
			<?php } ?>
			<td align=right><?= '<b>'.number_format($supplyTotal, 2).'</b>' ?></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td align=right>Load</td>
			<?php foreach($dates as $date){ ?>
				<?php if(isset($data['expenses']['pettyExpenses']['Cash'][$date])){ ?>
					<td><?= '<b>'.number_format($data['expenses']['pettyExpenses']['Cash'][$date]['loadTotal'], 2).'</b>' ?></td>
					<?php $totals['expenses'][$date]+=$data['expenses']['pettyExpenses']['Cash'][$date]['loadTotal']; ?>
					<?php $loadTotal+=$data['expenses']['pettyExpenses']['Cash'][$date]['loadTotal']; ?>
				<?php }else{ ?>
					<td><?= number_format(0, 2) ?></td>
				<?php } ?>
			<?php } ?>
			<td align=right><?= '<b>'.number_format($loadTotal, 2).'</b>' ?></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td align=right>Fare</td>
			<?php foreach($dates as $date){ ?>
				<?php if(isset($data['expenses']['pettyExpenses']['Cash'][$date])){ ?>
					<td><?= '<b>'.number_format($data['expenses']['pettyExpenses']['Cash'][$date]['fareTotal'], 2).'</b>' ?></td>
					<?php $totals['expenses'][$date]+=$data['expenses']['pettyExpenses']['Cash'][$date]['fareTotal']; ?>
					<?php $fareTotal+=$data['expenses']['pettyExpenses']['Cash'][$date]['fareTotal']; ?>
				<?php }else{ ?>
					<td><?= number_format(0, 2) ?></td>
				<?php } ?>
			<?php } ?>
			<td align=right><?= '<b>'.number_format($fareTotal, 2).'</b>' ?></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>PHOTOCOPY</td>
			<?php foreach($dates as $date){ ?>
				<?php if(isset($data['expenses']['photocopyExpenses']['Cash'][$date])){ ?>
					<td><?= '<b>'.number_format($data['expenses']['photocopyExpenses']['Cash'][$date]['total'], 2).'</b>' ?></td>
					<?php $totals['expenses'][$date]+=$data['expenses']['photocopyExpenses']['Cash'][$date]['total']; ?>
					<?php $photocopyTotal+=$data['expenses']['photocopyExpenses']['Cash'][$date]['total']; ?>
				<?php }else{ ?>
					<td><?= number_format(0, 2) ?></td>
				<?php } ?>
			<?php } ?>
			<td align=right><?= '<b>'.number_format($photocopyTotal, 2).'</b>' ?></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>OPERATING</td>
			<?php foreach($dates as $date){ ?>
				<td>&nbsp;</td>
			<?php } ?>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td align=right>Staff Salary</td>
			<?php foreach($dates as $date){ ?>
				<?php if(isset($data['expenses']['operatingExpenses']['Cash'][$date])){ ?>
					<td><?= '<b>'.number_format($data['expenses']['operatingExpenses']['Cash'][$date]['staffSalaryTotal'], 2).'</b>' ?></td>
					<?php $totals['expenses'][$date]+=$data['expenses']['operatingExpenses']['Cash'][$date]['staffSalaryTotal']; ?>
					<?php $staffSalaryTotal+=$data['expenses']['operatingExpenses']['Cash'][$date]['staffSalaryTotal']; ?>
				<?php }else{ ?>
					<td><?= number_format(0, 2) ?></td>
				<?php } ?>
			<?php } ?>
			<td align=right><?= '<b>'.number_format($staffSalaryTotal, 2).'</b>' ?></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td align=right>Cash PF</td>
			<?php foreach($dates as $date){ ?>
				<?php if(isset($data['expenses']['operatingExpenses']['Cash'][$date])){ ?>
					<td><?= '<b>'.number_format($data['expenses']['operatingExpenses']['Cash'][$date]['cashPfTotal'], 2).'</b>' ?></td>
					<?php $totals['expenses'][$date]+=$data['expenses']['operatingExpenses']['Cash'][$date]['cashPfTotal']; ?>
					<?php $cashPfTotal+=$data['expenses']['operatingExpenses']['Cash'][$date]['cashPfTotal']; ?>
				<?php }else{ ?>
					<td><?= number_format(0, 2) ?></td>
				<?php } ?>
			<?php } ?>
			<td align=right><?= '<b>'.number_format($cashPfTotal, 2).'</b>' ?></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td align=right>Rent</td>
			<?php foreach($dates as $date){ ?>
				<?php if(isset($data['expenses']['operatingExpenses']['Cash'][$date])){ ?>
					<td><?= '<b>'.number_format($data['expenses']['operatingExpenses']['Cash'][$date]['rentTotal'], 2).'</b>' ?></td>
					<?php $totals['expenses'][$date]+=$data['expenses']['operatingExpenses']['Cash'][$date]['rentTotal']; ?>
					<?php $rentTotal+=$data['expenses']['operatingExpenses']['Cash'][$date]['rentTotal']; ?>
				<?php }else{ ?>
					<td><?= number_format(0, 2) ?></td>
				<?php } ?>
			<?php } ?>
			<td align=right><?= '<b>'.number_format($rentTotal, 2).'</b>' ?></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td align=right>Utilities/Bills</td>
			<?php foreach($dates as $date){ ?>
				<?php if(isset($data['expenses']['operatingExpenses']['Cash'][$date])){ ?>
					<td><?= '<b>'.number_format($data['expenses']['operatingExpenses']['Cash'][$date]['utilitiesTotal'], 2).'</b>' ?></td>
					<?php $totals['expenses'][$date]+=$data['expenses']['operatingExpenses']['Cash'][$date]['utilitiesTotal']; ?>
					<?php $utilitiesTotal+=$data['expenses']['operatingExpenses']['Cash'][$date]['utilitiesTotal']; ?>
				<?php }else{ ?>
					<td><?= number_format(0, 2) ?></td>
				<?php } ?>
			<?php } ?>
			<td align=right><?= '<b>'.number_format($utilitiesTotal, 2).'</b>' ?></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td align=right>Equipment and Labor</td>
			<?php foreach($dates as $date){ ?>
				<?php if(isset($data['expenses']['operatingExpenses']['Cash'][$date])){ ?>
					<td><?= '<b>'.number_format($data['expenses']['operatingExpenses']['Cash'][$date]['equipmentAndLaborTotal'], 2).'</b>' ?></td>
					<?php $totals['expenses'][$date]+=$data['expenses']['operatingExpenses']['Cash'][$date]['equipmentAndLaborTotal']; ?>
					<?php $equipmentAndLaborTotal+=$data['expenses']['operatingExpenses']['Cash'][$date]['equipmentAndLaborTotal']; ?>
				<?php }else{ ?>
					<td><?= number_format(0, 2) ?></td>
				<?php } ?>
			<?php } ?>
			<td align=right><?= '<b>'.number_format($equipmentAndLaborTotal, 2).'</b>' ?></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td align=right>BIR and Docs</td>
			<?php foreach($dates as $date){ ?>
				<?php if(isset($data['expenses']['operatingExpenses']['Cash'][$date])){ ?>
					<td><?= '<b>'.number_format($data['expenses']['operatingExpenses']['Cash'][$date]['bir_and_docsTotal'], 2).'</b>' ?></td>
					<?php $totals['expenses'][$date]+=$data['expenses']['operatingExpenses']['Cash'][$date]['bir_and_docsTotal']; ?>
					<?php $bir_and_docsTotal+=$data['expenses']['operatingExpenses']['Cash'][$date]['bir_and_docsTotal']; ?>
				<?php }else{ ?>
					<td><?= number_format(0, 2) ?></td>
				<?php } ?>
			<?php } ?>
			<td align=right><?= '<b>'.number_format($bir_and_docsTotal, 2).'</b>' ?></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td align=right>Marketing</td>
			<?php foreach($dates as $date){ ?>
				<?php if(isset($data['expenses']['operatingExpenses']['Cash'][$date])){ ?>
					<td><?= '<b>'.number_format($data['expenses']['operatingExpenses']['Cash'][$date]['marketingTotal'], 2).'</b>' ?></td>
					<?php $totals['expenses'][$date]+=$data['expenses']['operatingExpenses']['Cash'][$date]['marketingTotal']; ?>
					<?php $marketingTotal+=$data['expenses']['operatingExpenses']['Cash'][$date]['marketingTotal']; ?>
				<?php }else{ ?>
					<td><?= number_format(0, 2) ?></td>
				<?php } ?>
			<?php } ?>
			<td align=right><?= '<b>'.number_format($marketingTotal, 2).'</b>' ?></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>OTHER</td>
			<?php foreach($dates as $date){ ?>
				<?php if(isset($data['expenses']['otherExpenses']['Cash'][$date])){ ?>
					<td><?= '<b>'.number_format($data['expenses']['otherExpenses']['Cash'][$date]['total'], 2).'</b>' ?></td>
					<?php $totals['expenses'][$date]+=$data['expenses']['otherExpenses']['Cash'][$date]['total']; ?>
					<?php $otherTotal+=$data['expenses']['otherExpenses']['Cash'][$date]['total']; ?>
				<?php }else{ ?>
					<td><?= number_format(0, 2) ?></td>
				<?php } ?>
			<?php } ?>
			<td align=right><?= '<b>'.number_format($otherTotal, 2).'</b>' ?></td>
		</tr>
		<tr>
			<td colspan=2><b>TOTAL EXPENSES</b></td>
			<?php foreach($dates as $date){ ?>
				<td><?= '<b>'.number_format($totals['expenses'][$date], 2).'</b>' ?></td>
				<?php $expenseTotal+=$totals['expenses'][$date]; ?>
			<?php } ?>
			<td align=right><?= '<b>'.number_format($expenseTotal, 2).'</b>' ?></td>
		</tr>
	</tbody>
</table>
<p><b>Cut-Off Report</b></p>
<table class="table table-bordered table-condensed table-striped table-hovered" style="width: 100%;">
	<tbody>
		<tr>
			<td><b>Gross Income (Cash)</b></td>
			<td align=right><?= $incomeTotal >= 0 ? number_format($incomeTotal, 2) : '<font color=red>'.number_format($incomeTotal, 2).'</font>' ?></td>
		</tr>
		<tr>
			<td><b>Total Expenses (Cash)</b></td>
			<td align=right><?= $expenseTotal >= 0 ? number_format($expenseTotal, 2) : '<font color=red>'.number_format($expenseTotal, 2).'</font>' ?></td>
		</tr>
	</tbody>
</table>
<?php Panel::end(); ?>
<?php Panel::begin(['header' => 'Non-Cash']); ?>
<table class="table table-bordered table-condensed table-striped table-hovered">
	<thead>
		<tr>
			<th>Date</th>
			<th>Details</th>
			<?php foreach($dates as $date){ ?>
				<th><?= date("M j", strtotime($date)) ?></th>
			<?php } ?>
			<th>TOTAL</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td colspan=<?= 3+count($dates) ?>>INCOME</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>Enrolments</td>
			<?php foreach($dates as $date){ ?>
				<?php if(isset($data['incomes']['enrolments']['Non-Cash'][$date])){ ?>
					<td><?= '<b>'.number_format($data['incomes']['enrolments']['Non-Cash'][$date]['total'], 2).'</b>' ?></td>
					<?php $totals2['incomes'][$date]+=$data['incomes']['enrolments']['Non-Cash'][$date]['total']; ?>
					<?php $enrolmentTotal2+=$data['incomes']['enrolments']['Non-Cash'][$date]['total']; ?>
				<?php }else{ ?>
					<td><?= number_format(0, 2) ?></td>
				<?php } ?>
			<?php } ?>
			<td align=right><?= '<b>'.number_format($enrolmentTotal2, 2).'</b>' ?></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>Freebies</td>
			<?php foreach($dates as $date){ ?>
				<?php if(isset($data['incomes']['freebies']['Non-Cash'][$date])){ ?>
					<td><?= '<b>'.number_format($data['incomes']['freebies']['Non-Cash'][$date]['total'], 2).'</b>' ?></td>
					<?php $totals2['incomes'][$date]+=$data['incomes']['freebies']['Non-Cash'][$date]['total']; ?>
					<?php $freebieTotal2+=$data['incomes']['freebies']['Non-Cash'][$date]['total']; ?>
				<?php }else{ ?>
					<td><?= number_format(0, 2) ?></td>
				<?php } ?>
			<?php } ?>
			<td align=right><?= '<b>'.number_format($freebieTotal2, 2).'</b>' ?></td>
		</tr>
		<tr>
			<td colspan=2><b>TOTAL INCOME</b></td>
			<?php foreach($dates as $date){ ?>
				<td><?= '<b>'.number_format($totals2['incomes'][$date], 2).'</b>' ?></td>
				<?php $incomeTotal2+=$totals2['incomes'][$date]; ?>
			<?php } ?>
			<td align=right><?= '<b>'.number_format($incomeTotal2, 2).'</b>' ?></td>
		</tr>
		<tr>
			<td colspan=<?= 3+count($dates) ?>>EXPENSES</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>PETTY</td>
			<?php foreach($dates as $date){ ?>
				<td>&nbsp;</td>
			<?php } ?>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td align=right>Food</td>
			<?php foreach($dates as $date){ ?>
				<?php if(isset($data['expenses']['pettyExpenses']['Non-Cash'][$date])){ ?>
					<td><?= '<b>'.number_format($data['expenses']['pettyExpenses']['Non-Cash'][$date]['foodTotal'], 2).'</b>' ?></td>
					<?php $totals2['expenses'][$date]+=$data['expenses']['pettyExpenses']['Non-Cash'][$date]['foodTotal']; ?>
					<?php $foodTotal2+=$data['expenses']['pettyExpenses']['Non-Cash'][$date]['foodTotal']; ?>
				<?php }else{ ?>
					<td><?= number_format(0, 2) ?></td>
				<?php } ?>
			<?php } ?>
			<td align=right><?= '<b>'.number_format($foodTotal2, 2).'</b>' ?></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td align=right>Supplies</td>
			<?php foreach($dates as $date){ ?>
				<?php if(isset($data['expenses']['pettyExpenses'][$date])){ ?>
					<td><?= '<b>'.number_format($data['expenses']['pettyExpenses'][$date]['supplyTotal'], 2).'</b>' ?></td>
					<?php $totals2['expenses'][$date]+=$data['expenses']['pettyExpenses'][$date]['supplyTotal']; ?>
					<?php $supplyTotal2+=$data['expenses']['pettyExpenses'][$date]['supplyTotal']; ?>
				<?php }else{ ?>
					<td><?= number_format(0, 2) ?></td>
				<?php } ?>
			<?php } ?>
			<td align=right><?= '<b>'.number_format($supplyTotal2, 2).'</b>' ?></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td align=right>Load</td>
			<?php foreach($dates as $date){ ?>
				<?php if(isset($data['expenses']['pettyExpenses']['Non-Cash'][$date])){ ?>
					<td><?= '<b>'.number_format($data['expenses']['pettyExpenses']['Non-Cash'][$date]['loadTotal'], 2).'</b>' ?></td>
					<?php $totals2['expenses'][$date]+=$data['expenses']['pettyExpenses']['Non-Cash'][$date]['loadTotal']; ?>
					<?php $loadTotal2+=$data['expenses']['pettyExpenses']['Non-Cash'][$date]['loadTotal']; ?>
				<?php }else{ ?>
					<td><?= number_format(0, 2) ?></td>
				<?php } ?>
			<?php } ?>
			<td align=right><?= '<b>'.number_format($loadTotal2, 2).'</b>' ?></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td align=right>Fare</td>
			<?php foreach($dates as $date){ ?>
				<?php if(isset($data['expenses']['pettyExpenses']['Non-Cash'][$date])){ ?>
					<td><?= '<b>'.number_format($data['expenses']['pettyExpenses']['Non-Cash'][$date]['fareTotal'], 2).'</b>' ?></td>
					<?php $totals2['expenses'][$date]+=$data['expenses']['pettyExpenses']['Non-Cash'][$date]['fareTotal']; ?>
					<?php $fareTotal2+=$data['expenses']['pettyExpenses'][$date]['fareTotal']; ?>
				<?php }else{ ?>
					<td><?= number_format(0, 2) ?></td>
				<?php } ?>
			<?php } ?>
			<td align=right><?= '<b>'.number_format($fareTotal2, 2).'</b>' ?></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>PHOTOCOPY</td>
			<?php foreach($dates as $date){ ?>
				<?php if(isset($data['expenses']['photocopyExpenses']['Non-Cash'][$date])){ ?>
					<td><?= '<b>'.number_format($data['expenses']['photocopyExpenses']['Non-Cash'][$date]['total'], 2).'</b>' ?></td>
					<?php $totals2['expenses'][$date]+=$data['expenses']['photocopyExpenses']['Non-Cash'][$date]['total']; ?>
					<?php $photocopyTotal2+=$data['expenses']['photocopyExpenses']['Non-Cash'][$date]['total']; ?>
				<?php }else{ ?>
					<td><?= number_format(0, 2) ?></td>
				<?php } ?>
			<?php } ?>
			<td align=right><?= '<b>'.number_format($photocopyTotal2, 2).'</b>' ?></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>OPERATING</td>
			<?php foreach($dates as $date){ ?>
				<td>&nbsp;</td>
			<?php } ?>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td align=right>Staff Salary</td>
			<?php foreach($dates as $date){ ?>
				<?php if(isset($data['expenses']['operatingExpenses']['Non-Cash'][$date])){ ?>
					<td><?= '<b>'.number_format($data['expenses']['operatingExpenses']['Non-Cash'][$date]['staffSalaryTotal'], 2).'</b>' ?></td>
					<?php $totals2['expenses'][$date]+=$data['expenses']['operatingExpenses']['Non-Cash'][$date]['staffSalaryTotal']; ?>
					<?php $staffSalaryTotal2+=$data['expenses']['operatingExpenses']['Non-Cash'][$date]['staffSalaryTotal']; ?>
				<?php }else{ ?>
					<td><?= number_format(0, 2) ?></td>
				<?php } ?>
			<?php } ?>
			<td align=right><?= '<b>'.number_format($staffSalaryTotal2, 2).'</b>' ?></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td align=right>Cash PF</td>
			<?php foreach($dates as $date){ ?>
				<?php if(isset($data['expenses']['operatingExpenses']['Non-Cash'][$date])){ ?>
					<td><?= '<b>'.number_format($data['expenses']['operatingExpenses']['Non-Cash'][$date]['cashPfTotal'], 2).'</b>' ?></td>
					<?php $totals2['expenses'][$date]+=$data['expenses']['operatingExpenses']['Non-Cash'][$date]['cashPfTotal']; ?>
					<?php $cashPfTotal2+=$data['expenses']['operatingExpenses']['Non-Cash'][$date]['cashPfTotal']; ?>
				<?php }else{ ?>
					<td><?= number_format(0, 2) ?></td>
				<?php } ?>
			<?php } ?>
			<td align=right><?= '<b>'.number_format($cashPfTotal2, 2).'</b>' ?></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td align=right>Rent</td>
			<?php foreach($dates as $date){ ?>
				<?php if(isset($data['expenses']['operatingExpenses']['Non-Cash'][$date])){ ?>
					<td><?= '<b>'.number_format($data['expenses']['operatingExpenses']['Non-Cash'][$date]['rentTotal'], 2).'</b>' ?></td>
					<?php $totals2['expenses'][$date]+=$data['expenses']['operatingExpenses']['Non-Cash'][$date]['rentTotal']; ?>
					<?php $rentTotal2+=$data['expenses']['operatingExpenses']['Non-Cash'][$date]['rentTotal']; ?>
				<?php }else{ ?>
					<td><?= number_format(0, 2) ?></td>
				<?php } ?>
			<?php } ?>
			<td align=right><?= '<b>'.number_format($rentTotal2, 2).'</b>' ?></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td align=right>Utilities/Bills</td>
			<?php foreach($dates as $date){ ?>
				<?php if(isset($data['expenses']['operatingExpenses']['Non-Cash'][$date])){ ?>
					<td><?= '<b>'.number_format($data['expenses']['operatingExpenses']['Non-Cash'][$date]['utilitiesTotal'], 2).'</b>' ?></td>
					<?php $totals2['expenses'][$date]+=$data['expenses']['operatingExpenses']['Non-Cash'][$date]['utilitiesTotal']; ?>
					<?php $utilitiesTotal2+=$data['expenses']['operatingExpenses']['Non-Cash'][$date]['utilitiesTotal']; ?>
				<?php }else{ ?>
					<td><?= number_format(0, 2) ?></td>
				<?php } ?>
			<?php } ?>
			<td align=right><?= '<b>'.number_format($utilitiesTotal2, 2).'</b>' ?></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td align=right>Equipment and Labor</td>
			<?php foreach($dates as $date){ ?>
				<?php if(isset($data['expenses']['operatingExpenses'][$date])){ ?>
					<td><?= '<b>'.number_format($data['expenses']['operatingExpenses']['Non-Cash'][$date]['equipmentAndLaborTotal'], 2).'</b>' ?></td>
					<?php $totals2['expenses'][$date]+=$data['expenses']['operatingExpenses']['Non-Cash'][$date]['equipmentAndLaborTotal']; ?>
					<?php $equipmentAndLaborTotal2+=$data['expenses']['operatingExpenses']['Non-Cash'][$date]['equipmentAndLaborTotal']; ?>
				<?php }else{ ?>
					<td><?= number_format(0, 2) ?></td>
				<?php } ?>
			<?php } ?>
			<td align=right><?= '<b>'.number_format($equipmentAndLaborTotal2, 2).'</b>' ?></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td align=right>BIR and Docs</td>
			<?php foreach($dates as $date){ ?>
				<?php if(isset($data['expenses']['operatingExpenses']['Non-Cash'][$date])){ ?>
					<td><?= '<b>'.number_format($data['expenses']['operatingExpenses']['Non-Cash'][$date]['bir_and_docsTotal'], 2).'</b>' ?></td>
					<?php $totals2['expenses'][$date]+=$data['expenses']['operatingExpenses']['Non-Cash'][$date]['bir_and_docsTotal']; ?>
					<?php $bir_and_docsTotal2+=$data['expenses']['operatingExpenses']['Non-Cash'][$date]['bir_and_docsTotal']; ?>
				<?php }else{ ?>
					<td><?= number_format(0, 2) ?></td>
				<?php } ?>
			<?php } ?>
			<td align=right><?= '<b>'.number_format($bir_and_docsTotal2, 2).'</b>' ?></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td align=right>Marketing</td>
			<?php foreach($dates as $date){ ?>
				<?php if(isset($data['expenses']['operatingExpenses']['Non-Cash'][$date])){ ?>
					<td><?= '<b>'.number_format($data['expenses']['operatingExpenses']['Non-Cash'][$date]['marketingTotal'], 2).'</b>' ?></td>
					<?php $totals2['expenses'][$date]+=$data['expenses']['operatingExpenses']['Non-Cash'][$date]['marketingTotal']; ?>
					<?php $marketingTotal2+=$data['expenses']['operatingExpenses']['Non-Cash'][$date]['marketingTotal']; ?>
				<?php }else{ ?>
					<td><?= number_format(0, 2) ?></td>
				<?php } ?>
			<?php } ?>
			<td align=right><?= '<b>'.number_format($marketingTotal2, 2).'</b>' ?></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>OTHER</td>
			<?php foreach($dates as $date){ ?>
				<?php if(isset($data['expenses']['otherExpenses']['Non-Cash'][$date])){ ?>
					<td><?= '<b>'.number_format($data['expenses']['otherExpenses']['Non-Cash'][$date]['total'], 2).'</b>' ?></td>
					<?php $totals2['expenses'][$date]+=$data['expenses']['otherExpenses']['Non-Cash'][$date]['total']; ?>
					<?php $otherTotal2+=$data['expenses']['otherExpenses']['Non-Cash'][$date]['total']; ?>
				<?php }else{ ?>
					<td><?= number_format(0, 2) ?></td>
				<?php } ?>
			<?php } ?>
			<td align=right><?= '<b>'.number_format($otherTotal2, 2).'</b>' ?></td>
		</tr>
		<tr>
			<td colspan=2><b>TOTAL EXPENSES</b></td>
			<?php foreach($dates as $date){ ?>
				<td><?= '<b>'.number_format($totals2['expenses'][$date], 2).'</b>' ?></td>
				<?php $expenseTotal2+=$totals2['expenses'][$date]; ?>
			<?php } ?>
			<td align=right><?= '<b>'.number_format($expenseTotal2, 2).'</b>' ?></td>
		</tr>
	</tbody>
</table>
<p><b>Cut-Off Report</b></p>
<table class="table table-bordered table-condensed table-striped table-hovered" style="width: 100%;">
	<tbody>
		<tr>
			<td><b>Gross Income (Non-Cash)</b></td>
			<td align=right><?= $incomeTotal2 >= 0 ? number_format($incomeTotal2, 2) : '<font color=red>'.number_format($incomeTotal2, 2).'</font>' ?></td>
		</tr>
		<tr>
			<td><b>Total Expenses (Non-Cash)</b></td>
			<td align=right><?= $expenseTotal2 >= 0 ? number_format($expenseTotal2, 2) : '<font color=red>'.number_format($expenseTotal2, 2).'</font>' ?></td>
		</tr>
	</tbody>
</table>
<?php Panel::end(); ?>
<br>
<p><b>Overall Summary</b></p>
<div class="row">
	<div class="col-md-12">
		<table class="table table-bordered table-condensed table-striped table-hovered">
			<tbody>
				<tr>
					<td><b>Gross Income</b></td>
					<td align=right><?= ($incomeTotal + $incomeTotal2) >= 0 ? number_format($incomeTotal + $incomeTotal2, 2) : '<font color=red>'.number_format($incomeTotal + $incomeTotal2, 2).'</font>' ?></td>
				</tr>
				<tr>
					<td><b>Total Expenses</b></td>
					<td align=right><?= ($expenseTotal + $expenseTotal2) >= 0 ? number_format($expenseTotal + $expenseTotal2, 2) : '<font color=red>'.number_format($expenseTotal + $expenseTotal2, 2).'</font>' ?></td>
				</tr>
				<tr>
					<td><b>Net Income</b></td>
					<td align=right><?= ($incomeTotal + $incomeTotal2) - ($expenseTotal + $expenseTotal2) >= 0 ? number_format(($incomeTotal + $incomeTotal2) - ($expenseTotal + $expenseTotal2), 2) : '<font color=red>'.number_format(($incomeTotal + $incomeTotal2) - ($expenseTotal + $expenseTotal2), 2).'</font>' ?></td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
<?php Panel::end(); ?>