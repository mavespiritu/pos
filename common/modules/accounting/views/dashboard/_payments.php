<?php
	use miloschuman\highcharts\Highcharts;
	$chartId = 'chartCanvas1';
?>

<div id="<?= $chartId ?>" style="min-height:340px;"></div>
<?php 
	Highcharts::widget([
		'scripts' => [
			'modules/exporting',
		],
	   'options' => [
			'exporting' => ['enabled' => true],
			'chart' => ['renderTo'=>$chartId, 'type' => 'bar'],
			'title' => ['text' => 'Payment Summary'],
			'subtitle' => ['text' => 'Source: Toprank Integrated Systems: Accounting'],
			'xAxis' => ['categories' => $seriesLabels, 'title' => ['text' => null]],
			'yAxis' => ['min' => 0, 'title' => ['text' => 'Total(in PHP)', 'align' => 'high'], 'labels' => ['overflow' => 'justify']],
			'tooltip' => ['shared' => true,],
			'plotOptions' => ['bar' => ['dataLabels' => ['enabled' => true]]],	
			'legend'=> ['reverse' => true],	  
			'series' => $seriesData,
	   ]
	]);
?>