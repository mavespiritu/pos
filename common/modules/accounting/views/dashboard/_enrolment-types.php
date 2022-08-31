<?php
	use miloschuman\highcharts\Highcharts;
	$chartId = 'chartCanvas2';
?>

<div id="<?= $chartId ?>" style="min-height:640px;"></div>
<?php 
	Highcharts::widget([
		'scripts' => [
			'modules/exporting',
		],
	   'options' => [
			'exporting' => ['enabled' => true],
			'chart' => ['renderTo'=>$chartId, 'type' => 'pie', 'plotBackgroundColor' => null, 'plotBorderWidth' => null, 'plotShadow' => null],
			'title' => ['text' => 'Count By Enrolment Types'],
			'tooltip' => ['pointFormat' => '{series.name}: <b>{point.percentage:.2f}%</b>'],
			'plotOptions' => ['pie' => ['allowPointSelect' => true, 'cursor' => 'pointer', 'dataLabels' => ['enabled' => false], 'showInLegend' => true]],  
			'series' => [['name' => 'Enrolment Types', 'colorByPoint' => true, 'data' => $seriesData]],
	   ]
	]);
?>