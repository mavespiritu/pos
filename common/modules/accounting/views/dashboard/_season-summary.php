<?php
	
	use miloschuman\highcharts\Highcharts;
	$chartId = 'chartCanvas10';

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
			'title' => ['text' => 'Net Income By Season'],
			'subtitle' => ['text' => date("F j, Y", strtotime($incomeDate[0][0])).' to '.date("F j, Y", strtotime($incomeDate[0][1]))],
			'xAxis' => ['categories' => $seasonNames],
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
			<table class="striped display" id="season-summary-table">
				<thead>
					<tr>
						<th class="text-center">Branch - Program</th>
						<th class="text-center">Season</th>
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
					<?php if(!empty($seasons)){ ?>
						<?php foreach($seasons as $season){ ?>
							<tr>
								<td><?= strtoupper($season['branchProgramName']) ?></td>
								<td><?= strtoupper($season['seasonName']) ?></td>
								<td align="right"><?= $season['no_of_students'] > 0 ? number_format($season['no_of_students'], 0) : '-' ?></td>
								<td align="right"><?= $season['incomeOneTotal'] > 0 ? number_format($season['incomeOneTotal'], 2) : '-' ?></td>
								<td align="right"><?= $season['incomeTwoTotal'] > 0 ? number_format($season['incomeTwoTotal'], 2) : '-' ?></td>
								<td align="right"><?= $season['expenseOneTotal'] > 0 ? number_format($season['expenseOneTotal'], 2) : '-' ?></td>
								<td align="right"><?= $season['expenseTwoTotal'] > 0 ? number_format($season['expenseTwoTotal'], 2) : '-' ?></td>
								<td align="right"><?= $season['expenseThreeTotal'] > 0 ? number_format($season['expenseThreeTotal'], 2) : '-' ?></td>
								<td align="right"><?= $season['expenseFourTotal'] > 0 ? number_format($season['expenseFourTotal'], 2) : '-' ?></td>
								<td align="right"><?= $season['expenseFiveTotal'] > 0 ? number_format($season['expenseFiveTotal'], 2) : '-' ?></td>
								<td align="right"><b><?= number_format(($season['incomeOneTotal'] + $season['incomeTwoTotal']) - ($season['expenseOneTotal'] + $season['expenseTwoTotal'] + $season['expenseThreeTotal'] + $season['expenseFourTotal'] + $season['expenseFiveTotal']), 2) ?><b></td>
							</tr>
							<?php $no_of_students+=$season['no_of_students']; ?>
							<?php $incomeOneTotal+=$season['incomeOneTotal']; ?>
							<?php $incomeTwoTotal+=$season['incomeTwoTotal']; ?>
							<?php $expenseOneTotal+=$season['expenseOneTotal']; ?>
							<?php $expenseTwoTotal+=$season['expenseTwoTotal']; ?>
							<?php $expenseThreeTotal+=$season['expenseThreeTotal']; ?>
							<?php $expenseFourTotal+=$season['expenseFourTotal']; ?>
							<?php $expenseFiveTotal+=$season['expenseFiveTotal']; ?>
							<?php $netIncome+=($season['incomeOneTotal'] + $season['incomeTwoTotal']) - ($season['expenseOneTotal'] + $season['expenseTwoTotal'] + $season['expenseThreeTotal'] + $season['expenseFourTotal'] + $season['expenseFiveTotal']); ?>
						<?php } ?>
					<?php } ?>
				</tbody>
				<tfooter>
					<tr>
						<td align="right" colspan=2><b>TOTAL</b></td>
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
            	$("#season-summary-table").DataTable({
            		"order": [[ 0, "asc" ],[ 1, "asc" ]],
            		"dom": "Bfrtip",
			        "buttons": [
			           	"csv", "excel", "pdf", "print"
			        ]
            	});
            });
';
$this->registerJs($script);
   
?>