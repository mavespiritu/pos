<?php
	use miloschuman\highcharts\Highcharts;
	use yiister\gentelella\widgets\Panel;
	$chartId = 'chartCanvas1';
?>
<?php Panel::begin(); ?>
	<div id="<?=$chartId; ?>" style="min-height:540px;"></div>
<?php Panel::end(); ?>
<?php
	Highcharts::widget([
		'scripts' => [
			'modules/drilldown',
			'modules/exporting',
		],
	   'options' => [
			'exporting' => ['enabled' => true],
			'chart' => ['renderTo'=>$chartId, 'type'=>'column', 'zoomType'=>'x'],
			'title' => ['text' => 'Students Enrolment'],
			'subtitle' => ['text' => 'Source: Toprank Integrated Systems: Accounting'],
			'xAxis' => ['categories' => $categoriesData],
			'yAxis' => ['min' => 0, 'title' => ['text' => 'Number of Students'], 'labels' => ['overflow' => 'justify']],
			'legend'=>['reverse' => true ],
			'plotOptions' => ['bar' => ['dataLabels' => ['enabled' => true]]],		  
			'series' => $seriesData,
			'tooltip' => ['shared'=>true],
	   ]
	]);
?>