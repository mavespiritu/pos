<?php
	use yiister\gentelella\widgets\Panel;
	use miloschuman\highcharts\Highcharts;
	$chartId = 'chartCanvas7';
	$chartId2 = 'chartCanvas8';
?>
<?php
	use yii\jui\ProgressBar;
	function number_format_short( $n, $precision = 1 ) {
	    if ($n < 900) {
	        // 0 - 900
	        $n_format = number_format($n, $precision);
	        $suffix = '';
	    } else if ($n < 900000) {
	        // 0.9k-850k
	        $n_format = number_format($n / 1000, $precision);
	        $suffix = 'K';
	    } else if ($n < 900000000) {
	        // 0.9m-850m
	        $n_format = number_format($n / 1000000, $precision);
	        $suffix = 'M';
	    } else if ($n < 900000000000) {
	        // 0.9b-850b
	        $n_format = number_format($n / 1000000000, $precision);
	        $suffix = 'B';
	    } else {
	        // 0.9t+
	        $n_format = number_format($n / 1000000000000, $precision);
	        $suffix = 'T';
	    }
	    
	    if ( $precision > 0 ) {
	        $dotzero = '.' . str_repeat( '0', $precision );
	        $n_format = str_replace( $dotzero, '', $n_format );
	    }
	    return $n_format . $suffix;
	}
?>
<div class="row">
	<div class="col-md-4 col-xs-12">
		<div id="<?= $chartId2 ?>" style="min-height:640px;"></div>
	</div>
	<div class="col-md-4 col-xs-12">
		<div class="row">
			<div class="col-md-12 col-xs-12">
				<div style="overflow: auto;">
					<?php Panel::begin(); ?>
						<p class="text-center"><b>Approved Amount</b></p>
						<div class="text-center"><h1 style="font-size: 70px;">P <?= number_format_short($approvedAmount['total']) ?></h1></div>
						<div class="text-center"><p style="font-size: 14px;"><b><?= number_format($approvedAmount['no_of_proposal'], 0) ?></b> budget proposal/s has been approved</p></div>
					<?php Panel::end(); ?>
				</div>
				<div style="overflow: auto;">
					<?php Panel::begin(); ?>
						<p class="text-center"><b>Liquidated Amount</b></p>
						<div class="text-center"><h1 style="font-size: 70px;">P <?= number_format_short($liquidatedAmount['total']) ?></h1></div>
						<div class="text-center"><p style="font-size: 14px;"><b><?= number_format($liquidatedAmount['no_of_proposal'], 0) ?></b> budget proposal/s has been liquidated</p></div>
					<?php Panel::end(); ?>
				</div>
				<div style="overflow: auto;">
					<?php Panel::begin(); ?>
						<p class="text-center"><b>Liquidation Rate</b></p>
						<div class="text-center"><h1 style="font-size: 70px;"><?= $approvedAmount['total'] > 0 ? number_format((($liquidatedAmount['total']/$approvedAmount['total'])*100), 2) : number_format(0, 2) ?>%</h1></div>
						<div class="text-center"><p style="font-size: 14px;"><b><?= $approvedAmount['no_of_proposal'] > 0 ? number_format((($liquidatedAmount['no_of_proposal']/$approvedAmount['no_of_proposal'])*100), 2) : number_format(0, 2) ?>%</b> of approved budget proposal/s has been liquidated</p></div>
					<?php Panel::end(); ?>
				</div>
			</div>
		</div>
	</div>
	<div class="col-md-4 col-xs-12">
		<div id="<?= $chartId ?>" style="min-height:640px;"></div>
	</div>
</div>
<?php 
	Highcharts::widget([
		'scripts' => [
			'modules/exporting',
		],
	   'options' => [
			'exporting' => ['enabled' => true],
			'chart' => ['renderTo'=>$chartId, 'type' => 'pie', 'plotBackgroundColor' => null, 'plotBorderWidth' => 0, 'plotShadow' => false],
			'title' => ['text' => 'Budget Proposals By Type<br><br><p class="text-center" style="font-size: 11px;">'.date("F j, Y", strtotime($date[0][0])).' to '.date("F j, Y", strtotime($date[0][1])).'</p>', 'align' => 'center', 'verticalAlign' => 'middle', 'y' => -150],
			'tooltip' => ['pointFormat' => '{series.name}: <b>{point.percentage:.2f}%</b>'],
			'plotOptions' => [
				'pie' => [
					'allowPointSelect' => true, 
					'cursor' => 'pointer', 
					'dataLabels' => ['enabled' => false], 
					'startAngle' => -90,
					'endAngle' => 90,
					'center' => ['50%', '75%'],
					'size' => '60%',
					'showInLegend' => true]
				],  
			'series' => [['type' => 'pie', 'name' => 'Budget Proposals By Type', 'innerSize' => '50%', 'data' => $byTypeData]]
	   ]
	]);
?>
<?php 
	Highcharts::widget([
		'scripts' => [
			'modules/exporting',
		],
	   'options' => [
			'exporting' => ['enabled' => true],
			'chart' => ['renderTo'=>$chartId2, 'type' => 'pie', 'plotBackgroundColor' => null, 'plotBorderWidth' => 0, 'plotShadow' => false],
			'title' => ['text' => 'Budget Proposals By Approval<br><br><p class="text-center" style="font-size: 11px;">'.date("F j, Y", strtotime($date[0][0])).' to '.date("F j, Y", strtotime($date[0][1])).'</p>', 'align' => 'center', 'verticalAlign' => 'middle', 'y' => -150],
			'tooltip' => ['pointFormat' => '{series.name}: <b>{point.percentage:.2f}%</b>'],
			'plotOptions' => [
				'pie' => [
					'allowPointSelect' => true, 
					'cursor' => 'pointer',  
					'dataLabels' => ['enabled' => false], 
					'startAngle' => -90,
					'endAngle' => 90,
					'center' => ['50%', '75%'],
					'size' => '80%',
					'showInLegend' => true]
				],  
			'series' => [['type' => 'pie', 'name' => 'Budget Proposals By Type', 'innerSize' => '50%', 'data' => $byApprovalData]],
	   ]
	]);
?>