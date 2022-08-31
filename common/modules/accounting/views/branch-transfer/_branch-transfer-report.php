<?php 
use yii\helpers\Html;
use frontend\assets\AppAsset;
$asset = AppAsset::register($this);

$width = ceil(100/8);
$totalAmount = 0;

?>
<h3 class="text-center">
	<?= Html::img($asset->baseUrl.'/images/logo-blue.png',['style' => 'height: 45px; width: 175px;']) ?>
</h3>
<h5 class="text-center">Toprank Integrated Systems<br>
						Accounting<br>
						Branch Transfers Report</h5>

<table class="table table-bordered table-condensed table-hover table-responsive" style="width: 100%;">
	<tbody>
		<tr>
			<td><b>Branch</b></td>
			<td align=right><?= $branchProgram->branch->name ?></td>
		</tr>
		<tr>
			<td><b>Program</b></td>
			<td align=right><?= $branchProgram->program->name ?></td>
		</tr>
		<tr>
			<td><b>Frequency</b></td>
			<td align=right><?= $postData['frequency_id'] ?></td>
		</tr>
		<tr>
			<td><b>Date Covered</b></td>
			<td align=right>
				<?php if($postData['frequency_id'] == 'Yearly'){ ?>
					<?= $dates['year'] ?>
				<?php }else if($postData['frequency_id'] == 'Cut Off'){ ?>
					<?= $dates['start'].' - '.$dates['end'] ?>
				<?php }else if($postData['frequency_id'] == 'Monthly'){ ?>
					<?= date('F', strtotime($dates['month'])).' '.$dates['year'] ?>
				<?php }else if($postData['frequency_id'] == 'Daily'){ ?>
					<?= $dates['year'].'-'.$dates['month'].'-'.$dates['day'] ?>
				<?php } ?>
			</td>
		</tr>
	</tbody>
</table>
<table class="table table-bordered table-condensed">
	<thead>
		<tr>
			<th>#</th>
			<th>CHARGED TO</th>
			<th>AMOUNT</th>
			<th>AMOUNT SOURCE</th>
			<th>DATE/TIME</th>
		</tr>
	</thead>
	<tbody>
		<?php if(!empty($data)){ ?>
			<?php $i = 1; ?>
			<?php foreach($data as $datum){ ?>
				<tr>
					<td style="text-align:center; width: 3%"><?= $i ?></td>
					<td style="text-align:center; width: <?= $width ?>%"><?= $datum['charged_to'] ?></td>
					<td style="text-align:center; width: <?= $width ?>%"><?= number_format($datum['amount'], 2) ?></td>
					<td style="text-align:center; width: <?= $width ?>%"><?= $datum['amount_source'] ?></td>
					<td style="text-align:center; width: <?= $width ?>%"><?= $datum['datetime'] ?></td>
				</tr>
				<?php $i++; ?>
				<?php $totalAmount+=$datum['amount']; ?>
			<?php } ?>
		<?php }  ?>
		<tr>
			<td align=right colspan=2><b>TOTAL</b></td>
			<td style="text-align: center;"><?= number_format($totalAmount, 2) ?></td>
			<td style="text-align: center;">&nbsp;</td>
			<td style="text-align: center;">&nbsp;</td>
		</tr>
	</tbody>
</table>
