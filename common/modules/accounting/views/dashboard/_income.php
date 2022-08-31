<?php
	use miloschuman\highcharts\Highcharts;
	$chartId = 'chartCanvas5';

	$totalOne = 0;
	$totalTwo = 0;
?>
<div class="row">
	<div class="col-md-8 col-xs-12">
		<div id="<?= $chartId ?>" style="min-height:557px; min-width: 100%;"></div>
		<br>
		<div class="row">
			<div class="col-md-6 col-xs-12">
				<p class="text-center"><b>Enrolments</b></p>
				<h2 class="text-center">P <?= number_format($incomeOneTotal, 2) ?></h2>
			</div>
			<div class="col-md-6 col-xs-12">
				<p class="text-center"><b>Freebies and Icons</b></p>
				<h2 class="text-center">P <?= number_format($incomeTwoTotal, 2) ?></h2>
			</div>
		</div>
	</div>
	<div class="col-md-4 col-xs-12">
		<div class="row">
			<div class="col-md-12 col-xs-12">
				<div style="min-height:557px; max-height:567px; overflow: auto;">
				<table class="striped display" id="income-table" style="width: 100%">
					<thead>
						<tr>
							<th>Date</th>
							<th class="text-center">Enrolments</th>
							<th class="text-center">Freebies and Icons</th>
						</tr>
					</thead>
					<tbody>
						<?php if(!empty($income)){ ?>
							<?php foreach($income as $i){ ?>
								<tr>
									<td><?= $i['dt'] ?></td>
									<td align="right"><?= $i['incomeOneTotal'] > 0 ? number_format($i['incomeOneTotal'], 2) : '-' ?></td>
									<td align="right"><?= $i['incomeTwoTotal'] > 0 ? number_format($i['incomeTwoTotal'], 2) : '-' ?></td>
								</tr>
							<?php } ?>
						<?php } ?>
					</tbody>
					<tfooter>
						<tr>
							<td align="right"><b>TOTAL</b></td>
							<td align="right"><b><?= number_format($incomeOneTotal, 2) ?></b></td>
							<td align="right"><b><?= number_format($incomeTwoTotal, 2) ?></b></td>
						</tr>
					</tfooter>
				</table>
				</div>
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
			'title' => ['text' => 'Income Summary'],
			'subtitle' => ['text' => date("F j, Y", strtotime($date[0][0])).' to '.date("F j, Y", strtotime($date[0][1]))],
			'xAxis' => ['type' => 'datetime', 'dateTimeLabelFormats' => ['day' => '%e. %b'], 'title' => ['text' => 'Date']],
			'yAxis' => ['title' => ['text' => 'Total in PHP'], 'min' => 0],
			'tooltip' => ['headerFormat' => '<b>{series.name}</b><br>', 'pointFormat' => '{point.x:%b %e, %Y}: {point.y: ,.2f}'],
			'plotOptions' => ['spline' => ['marker' => ['enabled' => true]]],	
			'colors' => ['#7CB5EC', '#434348', '#90ED7D', '#F7A35C', '#8085E9'],
			'series' => $data,
	   ]
	]);
?>
<?php
        $script = '
            $( document ).ready(function() {
            	$("#income-table").DataTable({
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