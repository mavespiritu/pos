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
						Other Expense Report</h5>

<table class="table table-bordered table-condensed table-hover table-responsive" style="width: 100%;">
	<tbody>
		<tr>
			<td><b>Branch</b></td>
			<td align=right><?= $season->branchProgram->branch->name ?></td>
		</tr>
		<tr>
			<td><b>Program</b></td>
			<td align=right><?= $season->branchProgram->program->name ?></td>
		</tr>
		<tr>
			<td><b>Season</b></td>
			<td align=right><?= $season->seasonName ?></td>
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
			<th>CV NO.</th>
			<th>PARTICULARS</th>
			<th>AMOUNT</th>
			<th>CHARGE TO</th>
			<th>AMOUNT TYPE</th>
			<th>TRANSACTION NUMBER</th>
			<th>DATE/TIME</th>
		</tr>
	</thead>
	<tbody>
		<?php if(!empty($data)){ ?>
			<?php $i = 1; ?>
			<?php foreach($data as $datum){ ?>
				<tr>
					<td style="text-align:center; width: 3%"><?= $i ?></td>
					<td style="text-align:center; width: <?= $width ?>%"><?= $datum['cv_no'] ?></td>
					<td style="text-align:center; width: <?= $width ?>%"><?= $datum['particulars'] ?></td>
					<td style="text-align:center; width: <?= $width ?>%"><?= number_format($datum['amount'], 2) ?></td>
					<td style="text-align:center; width: <?= $width ?>%"><?= $datum['charge_to'] ?></td>
					<td style="text-align:center; width: <?= $width ?>%"><?= $datum['amount_type'] ?></td>
					<td style="text-align:center; width: <?= $width ?>%"><?= $datum['transaction_number'] ?></td>
					<td style="text-align:center; width: <?= $width ?>%"><?= $datum['datetime'] ?></td>
				</tr>
				<?php $i++; ?>
				<?php $totalAmount+=$datum['amount']; ?>
			<?php } ?>
		<?php }  ?>
		<tr>
			<td align=right colspan=3><b>TOTAL</b></td>
			<td style="text-align: center;"><?= number_format($totalAmount, 2) ?></td>
			<td style="text-align: center;">&nbsp;</td>
			<td style="text-align: center;">&nbsp;</td>
			<td style="text-align: center;">&nbsp;</td>
			<td style="text-align: center;">&nbsp;</td>
		</tr>
	</tbody>
</table>
