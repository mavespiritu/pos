<?php
	use miloschuman\highcharts\Highcharts;
	$chartId = 'chartCanvas3';
?>

<div id="<?= $chartId ?>" style="min-height:350px;"></div>
<?php 
	Highcharts::widget([
		'scripts' => [
			'modules/exporting',
			//'modules/drilldown',
		],
	   'options' => [
			'exporting' => ['enabled' => true],
			'chart' => ['renderTo' => $chartId, 'type' => 'column'],
			'title' => ['text' => 'Enrolment By Package Types'],
			'subtitle' => ['text' => 'Source: Toprank Integrated Systems: Accounting'],
			'xAxis' => ['type' => 'category'],
			'yAxis' => ['title' => ['text' => 'Total Count']],
			'legend'=> ['enabled' => false],
			'plotOptions' => ['series' => ['borderWidth' => 0, 'dataLabels' => ['enabled' => true, 'format' => '{point.y: 1f}']]],
			'tooltip' => ['headerFormat' => '<span style="font-size:11px">{series.name}</span><br>', 'pointFormat' => '<span style="color:{point.color}">{point.name}</span>: <b>{point.y: 1f}</b><br/>'],	  
			'series' => [['name' => 'Package Types', 'colorByPoint' => true, 'data' => $series]]
	   ]
	]);
?>