<?php
	use miloschuman\highcharts\Highcharts;
	$chartId = 'chartCanvas12';
?>

<div id="<?= $chartId ?>" style="min-height:487px;"></div>
<div class="row">
	<div class="col-md-12 col-xs-12">
		<table class="table table-bordered table-condensed table-striped">
			<thead>
				<tr>
					<th>Type</th>
					<td class="text-center"><b>Total</b></td>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>Staff Salary</td>
					<td align="right"><?= number_format($expense['staffSalaryTotal'], 2) ?></td>
				</tr>
				<tr>
					<td>Cash PF</td>
					<td align="right"><?= number_format($expense['cashPfTotal'], 2) ?></td>
				</tr>
				<tr>
					<td>Rent</td>
					<td align="right"><?= number_format($expense['rentTotal'], 2) ?></td>
				</tr>
				<tr>
					<td>Utilities</td>
					<td align="right"><?= number_format($expense['utilitiesTotal'], 2) ?></td>
				</tr>
				<tr>
					<td>Equipment and Labor</td>
					<td align="right"><?= number_format($expense['equipmentAndLaborTotal'], 2) ?></td>
				</tr>
				<tr>
					<td>BIR and Docs</td>
					<td align="right"><?= number_format($expense['birAndDocsTotal'], 2) ?></td>
				</tr>
				<tr>
					<td>Marketing</td>
					<td align="right"><?= number_format($expense['marketingTotal'], 2) ?></td>
				</tr>
				<tr>
					<td><b>Total</b></td>
					<td align="right"><b>P <?= number_format($expense['expenseTotal'], 2) ?></b></td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
<?php 
	Highcharts::widget([
		'scripts' => [
			'modules/exporting',
		],
	   'options' => [
			'exporting' => ['enabled' => true],
			'chart' => ['renderTo'=>$chartId, 'type' => 'pie', 'plotBackgroundColor' => null, 'plotBorderWidth' => null, 'plotShadow' => null],
			'title' => ['text' => 'Operating Expenses By Type'],
			'tooltip' => ['pointFormat' => '{series.name}: <b>{point.percentage:.2f}%</b>'],
			'plotOptions' => ['pie' => ['allowPointSelect' => true, 'cursor' => 'pointer', 'dataLabels' => ['enabled' => false], 'showInLegend' => true]],  
			'series' => [['name' => 'Types', 'colorByPoint' => true, 'data' => $data]],
	   ]
	]);
?>