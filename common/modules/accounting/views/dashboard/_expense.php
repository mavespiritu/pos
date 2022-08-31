<?php
	use miloschuman\highcharts\Highcharts;
	$chartId = 'chartCanvas6';

	$totalOne = 0;
	$totalTwo = 0;
?>
<div class="row">
	<div class="col-md-5 col-xs-12">
		<div class="row">
			<div class="col-md-12 col-xs-12">
				<div style="min-height:557px; max-height:567px; overflow: auto;">
				<table class="striped display" id="expense-table">
					<thead>
						<tr>
							<th>Date</th>
							<th class="text-center">Petty Expenses</th>
							<th class="text-center">Photocopy Expenses</th>
							<th class="text-center">Other Expenses</th>
							<th class="text-center">Bank Deposits</th>
							<th class="text-center">Operating Expenses</th>
						</tr>
					</thead>
					<tbody>
						<?php if(!empty($expense)){ ?>
							<?php foreach($expense as $i){ ?>
								<tr>
									<td><?= $i['date'] ?></td>
									<td align="right"><?= $i['pettyExpenseTotal'] > 0 ? number_format($i['pettyExpenseTotal'], 2) : '-' ?></td>
									<td align="right"><?= $i['photocopyExpenseTotal'] > 0 ? number_format($i['photocopyExpenseTotal'], 2) : '-' ?></td>
									<td align="right"><?= $i['otherExpenseTotal'] > 0 ? number_format($i['otherExpenseTotal'], 2) : '-' ?></td>
									<td align="right"><?= $i['bankDepositTotal'] > 0 ? number_format($i['bankDepositTotal'], 2) : '-' ?></td>
									<td align="right"><?= $i['operatingExpenseTotal'] > 0 ? number_format($i['operatingExpenseTotal'], 2) : '-' ?></td>
								</tr>
							<?php } ?>
						<?php } ?>
					</tbody>
					<tfooter>
						<tr>
							<td align="right"><b>TOTAL</b></td>
							<td align="right"><b><?= number_format($expenseOneTotal, 2) ?></b></td>
							<td align="right"><b><?= number_format($expenseTwoTotal, 2) ?></b></td>
							<td align="right"><b><?= number_format($expenseThreeTotal, 2) ?></b></td>
							<td align="right"><b><?= number_format($expenseFourTotal, 2) ?></b></td>
							<td align="right"><b><?= number_format($expenseFiveTotal, 2) ?></b></td>
						</tr>
					</tfooter>
				</table>
				</div>
			</div>
		</div>
	</div>
	<div class="col-md-7 col-xs-12">
		<div id="<?= $chartId ?>" style="min-height:557px; min-width: 100%;"></div>
		<br>
		<div class="row">
			<div class="col-md-2 col-xs-12">
				<p class="text-center"><b>Petty Expenses</b></p>
				<h2 class="text-center">P <?= number_format($expenseOneTotal, 2) ?></h2>
			</div>
			<div class="col-md-2 col-xs-12">
				<p class="text-center"><b>Photocopy Expenses</b></p>
				<h2 class="text-center">P <?= number_format($expenseTwoTotal, 2) ?></h2>
			</div>
			<div class="col-md-2 col-xs-12">
				<p class="text-center"><b>Other Expenses</b></p>
				<h2 class="text-center">P <?= number_format($expenseThreeTotal, 2) ?></h2>
			</div>
			<div class="col-md-2 col-xs-12">
				<p class="text-center"><b>Bank Deposits</b></p>
				<h2 class="text-center">P <?= number_format($expenseFourTotal, 2) ?></h2>
			</div>
			<div class="col-md-2 col-xs-12">
				<p class="text-center"><b>Operating Expenses</b></p>
				<h2 class="text-center">P <?= number_format($expenseFiveTotal, 2) ?></h2>
			</div>
		</div>
	</div>
</div>
<?php 
	Highcharts::widget([
		'scripts' => [
			'modules/exporting',
		],
	   'options' => [
			'exporting' => ['enabled' => true],
			'chart' => ['renderTo'=>$chartId, 'type' => 'spline'],
			'title' => ['text' => 'Expense Summary'],
			'subtitle' => ['text' => date("F j, Y", strtotime($date[0][0])).' to '.date("F j, Y", strtotime($date[0][1]))],
			'xAxis' => ['type' => 'datetime', 'dateTimeLabelFormats' => ['day' => '%e. %b'], 'title' => ['text' => 'Date']],
			'yAxis' => ['title' => ['text' => 'Total in PHP'], 'min' => 0],
			'tooltip' => ['headerFormat' => '<b>{series.name}</b><br>', 'pointFormat' => '{point.x:%b %e, %Y}: {point.y: ,.2f}'],
			'plotOptions' => ['spline' => ['marker' => ['enabled' => true]]],	
			'colors' => ['#7CB5EC', '#434348', '#90ED7D', '#F7A35C', '#C9302C'],
			'series' => $data,
	   ]
	]);
?>
<?php
        $script = '
            $( document ).ready(function() {
            	$("#expense-table").DataTable({
            		"order": [[ 0, "desc" ]],
            		"dom": "Bfrtip",
			        "buttons": [
			           	"csv", "excel", "pdf", "print"
			        ]
            	});
            });
';
$this->registerJs($script);
   
?>