<?php
	use miloschuman\highcharts\Highcharts;
	$chartId = 'chartCanvas9';

	$no_of_seasons = 0;
	$no_of_students = 0;
	$incomeOneTotal = 0;
	$incomeTwoTotal = 0;
	$expenseOneTotal = 0;
	$expenseTwoTotal = 0;
	$expenseThreeTotal = 0;
	$expenseFourTotal = 0;
	$expenseFiveTotal = 0;
	$netIncome = 0;
?>

<?php 
	Highcharts::widget([
		'scripts' => [
			'modules/exporting',
			//'modules/drilldown',
		],
	   'options' => [
			'exporting' => ['enabled' => true],
			'chart' => ['renderTo' => $chartId, 'type' => 'bar'],
			'title' => ['text' => 'Net Income By Branch Program'],
			'subtitle' => ['text' => date("F j, Y", strtotime($incomeDate[0][0])).' to '.date("F j, Y", strtotime($incomeDate[0][1]))],
			'xAxis' => ['categories' => $branchProgramNames],
			'yAxis' => ['title' => ['text' => 'Total']],
			'series' => [
				['name' => 'Enrolments', 'data' => $enrolmentData, 'stack' => 'income'],
				['name' => 'Freebies and Icons', 'data' => $freebieData, 'stack' => 'income'],
				['name' => 'Petty Expense', 'data' => $pettyExpenseData, 'stack' => 'expense'],
				['name' => 'Photocopy Expense', 'data' => $photocopyExpenseData, 'stack' => 'expense'],
				['name' => 'Other Expense', 'data' => $otherExpenseData, 'stack' => 'expense'],
				['name' => 'Bank Deposits', 'data' => $bankDepositData, 'stack' => 'expense'],
				['name' => 'Operating Expense', 'data' => $operatingExpenseData, 'stack' => 'expense'],
				['name' => 'Net Income', 'data' => $netIncomeData, 'stack' => 'net-income'],
			]
	   ]
	]);
?>
<div class="row">
	<div class="col-md-12 col-xs-12">
		<div style="min-height: 587px; max-height: 587px;">
			<div id="<?= $chartId ?>" style="min-height: 587px; max-height: 587px; "></div>
		</div>
		<div style="min-height: 144px; max-height: 144px;">
			<table class="striped display" id="branch-program-summary-table">
				<thead>
					<tr>
						<th class="text-center">Branch - Program</th>
						<th class="text-center">No. of Seasons</th>
						<th class="text-center">No. of Students</th>
						<th class="text-center">Enrolments</th>
						<th class="text-center">Freebies and Icons</th>
						<th class="text-center">Petty Expenses</th>
						<th class="text-center">Photocopy Expenses</th>
						<th class="text-center">Other Expenses</th>
						<th class="text-center">Bank Deposits</th>
						<th class="text-center">Operating Expenses</th>
						<th class="text-center">Net Income</th>
					</tr>
				</thead>
				<tbody>
					<?php if(!empty($branchPrograms)){ ?>
						<?php foreach($branchPrograms as $branchProgram){ ?>
							<tr>
								<td><?= strtoupper($branchProgram['branchProgramName']) ?></td>
								<td align="right"><?= $branchProgram['no_of_seasons'] > 0 ? number_format($branchProgram['no_of_seasons'], 0) : '-' ?></td>
								<td align="right"><?= $branchProgram['no_of_students'] > 0 ? number_format($branchProgram['no_of_students'], 0) : '-' ?></td>
								<td align="right"><?= $branchProgram['incomeOneTotal'] > 0 ? number_format($branchProgram['incomeOneTotal'], 2) : '-' ?></td>
								<td align="right"><?= $branchProgram['incomeTwoTotal'] > 0 ? number_format($branchProgram['incomeTwoTotal'], 2) : '-' ?></td>
								<td align="right"><?= $branchProgram['expenseOneTotal'] > 0 ? number_format($branchProgram['expenseOneTotal'], 2) : '-' ?></td>
								<td align="right"><?= $branchProgram['expenseTwoTotal'] > 0 ? number_format($branchProgram['expenseTwoTotal'], 2) : '-' ?></td>
								<td align="right"><?= $branchProgram['expenseThreeTotal'] > 0 ? number_format($branchProgram['expenseThreeTotal'], 2) : '-' ?></td>
								<td align="right"><?= $branchProgram['expenseFourTotal'] > 0 ? number_format($branchProgram['expenseFourTotal'], 2) : '-' ?></td>
								<td align="right"><?= $branchProgram['expenseFiveTotal'] > 0 ? number_format($branchProgram['expenseFiveTotal'], 2) : '-' ?></td>
								<td align="right"><b><?= number_format(($branchProgram['incomeOneTotal'] + $branchProgram['incomeTwoTotal']) - ($branchProgram['expenseOneTotal'] + $branchProgram['expenseTwoTotal'] + $branchProgram['expenseThreeTotal'] + $branchProgram['expenseFourTotal'] + $branchProgram['expenseFiveTotal']), 2) ?><b></td>
							</tr>
							<?php $no_of_seasons+=$branchProgram['no_of_seasons']; ?>
							<?php $no_of_students+=$branchProgram['no_of_students']; ?>
							<?php $incomeOneTotal+=$branchProgram['incomeOneTotal']; ?>
							<?php $incomeTwoTotal+=$branchProgram['incomeTwoTotal']; ?>
							<?php $expenseOneTotal+=$branchProgram['expenseOneTotal']; ?>
							<?php $expenseTwoTotal+=$branchProgram['expenseTwoTotal']; ?>
							<?php $expenseThreeTotal+=$branchProgram['expenseThreeTotal']; ?>
							<?php $expenseFourTotal+=$branchProgram['expenseFourTotal']; ?>
							<?php $expenseFiveTotal+=$branchProgram['expenseFiveTotal']; ?>
							<?php $netIncome+=($branchProgram['incomeOneTotal'] + $branchProgram['incomeTwoTotal']) - ($branchProgram['expenseOneTotal'] + $branchProgram['expenseTwoTotal'] + $branchProgram['expenseThreeTotal'] + $branchProgram['expenseFourTotal'] + $branchProgram['expenseFiveTotal']); ?>
						<?php } ?>
					<?php } ?>
				</tbody>
				<tfooter>
					<tr>
						<td align="right"><b>TOTAL</b></td>
						<td align="right"><b><?= number_format($no_of_seasons, 0) ?></b></td>
						<td align="right"><b><?= number_format($no_of_students, 0) ?></b></td>
						<td align="right"><b><?= number_format($incomeOneTotal, 2) ?></b></td>
						<td align="right"><b><?= number_format($incomeTwoTotal, 2) ?></b></td>
						<td align="right"><b><?= number_format($expenseOneTotal, 2) ?></b></td>
						<td align="right"><b><?= number_format($expenseTwoTotal, 2) ?></b></td>
						<td align="right"><b><?= number_format($expenseThreeTotal, 2) ?></b></td>
						<td align="right"><b><?= number_format($expenseFourTotal, 2) ?></b></td>
						<td align="right"><b><?= number_format($expenseFiveTotal, 2) ?></b></td>
						<td align="right"><b><?= number_format($netIncome, 2) ?></b></td>
					</tr>
				</tfooter>
			</table>
		</div>
	</div>
</div>
<?php
        $script = '
            $( document ).ready(function() {
            	$("#branch-program-summary-table").DataTable({
            		"order": [[ 0, "asc" ]],
            		"dom": "Bfrtip",
			        "buttons": [
			           	"csv", "excel", "pdf", "print"
			        ]
            	});
            });
';
$this->registerJs($script);
   
?>