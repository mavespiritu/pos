<?php
	use miloschuman\highcharts\Highcharts;
	$chartId = 'chartCanvas11';
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
					<td>Food</td>
					<td align="right"><?= number_format($expense['foodTotal'], 2) ?></td>
				</tr>
				<tr>
					<td>Supplies</td>
					<td align="right"><?= number_format($expense['suppliesTotal'], 2) ?></td>
				</tr>
				<tr>
					<td>Load</td>
					<td align="right"><?= number_format($expense['loadTotal'], 2) ?></td>
				</tr>
				<tr>
					<td>Fare</td>
					<td align="right"><?= number_format($expense['fareTotal'], 2) ?></td>
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
			'title' => ['text' => 'Petty Expenses By Type'],
			'tooltip' => ['pointFormat' => '{series.name}: <b>{point.percentage:.2f}%</b>'],
			'plotOptions' => ['pie' => ['allowPointSelect' => true, 'cursor' => 'pointer', 'dataLabels' => ['enabled' => false], 'showInLegend' => true]],  
			'series' => [['name' => 'Types', 'colorByPoint' => true, 'data' => $data]],
	   ]
	]);
?>