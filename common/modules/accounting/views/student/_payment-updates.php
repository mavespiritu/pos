<?php 
use yii\helpers\Html;
use frontend\assets\AppAsset;
$asset = AppAsset::register($this);

$width = ceil(100/14);
$packageAmount = 0;
$regularReviewPrice = 0;
$enhancementAmount = 0;
$totalTuitionFee = 0;
$discountAmount = 0;
$coachingAmount = 0;
$finalTuitionFee = 0;
$paymentsMade = 0;
$balanceAmount = 0;
?>
<h3 class="text-center">
	<?= Html::img($asset->baseUrl.'/images/logo-blue.png',['style' => 'height: 45px; width: 175px;']) ?>
</h3>
<h5 class="text-center">Toprank Integrated Systems<br>
						Accounting<br>
						Payment Updates</h5>

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
	</tbody>
</table>
<table class="table table-bordered table-condensed">
	<thead>
		<tr>
			<th>#</th>
			<th>ID NUMBER</th>
			<th>FULL NAME</th>
			<th>ENROLEE TYPE</th>
			<th>PACKAGE</th>
			<th>REGULAR REVIEW PRICE</th>
			<th>ENHANCEMENT FEE</th>
			<th>TOTAL TUITION</th>
			<th>DISCOUNT TYPE</th>
			<th>DISCOUNT FEE</th>
			<th>COACHING W/ ICONS</th>
			<th>FINAL TUITION FEE</th>
			<th>PAYMENTS MADE</th>
			<th>BALANCE</th>
			<th>BALANCE STATUS</th>
		</tr>
	</thead>
	<tbody>
		<?php if(!empty($data)){ ?>
			<?php $i = 1; ?>
			<?php foreach($data as $datum){ ?>
				<tr>
					<td style="text-align:center; width: 3%"><?= $i ?></td>
					<td style="text-align:center; width: <?= $width ?>%"><?= $datum['id_number'] ?></td>
					<td style="text-align:center; width: <?= $width ?>%"><?= $datum['lastName'].', '.$datum['first_name'].' '.$datum['middle_name'] ?></td>
					<td style="text-align:center; width: <?= $width ?>%"><?= $datum['enroleeTypeName'] ?></td>
					<td style="text-align:center; width: <?= $width ?>%"><?= $datum['packageName'] ?></td>
					<td style="text-align:center; width: <?= $width ?>%"><?= number_format($datum['packageAmount'], 2) ?></td>
					<td style="text-align:center; width: <?= $width ?>%"><?= number_format($datum['enhancementAmount'], 2) ?></td>
					<td style="text-align:center; width: <?= $width ?>%"><?= number_format($datum['packageAmount'] + $datum['enhancementAmount'], 2) ?></td>
					<td style="text-align:center; width: <?= $width ?>%"><?= $datum['discountType'] ?></td>
					<td style="text-align:center; width: <?= $width ?>%"><?= number_format($datum['discountAmount'], 2) ?></td>
					<td style="text-align:center; width: <?= $width ?>%"><?= number_format($datum['coachingAmount'], 2) ?></td>
					<td style="text-align:center; width: <?= $width ?>%"><?= number_format(($datum['packageAmount'] + $datum['enhancementAmount'] + $datum['coachingAmount']) - $datum['discountAmount'], 2)  ?></td>
					<td style="text-align:center; width: <?= $width ?>%"><?= number_format($datum['incomeEnrolmentsAmount'] + $datum['freebiesAmount'], 2)  ?></td>
					<td style="text-align:center; width: <?= $width ?>%"><?= $datum['balanceAmount'] > 0 ? '<font color=red>'.number_format($datum['balanceAmount'], 2).'</font>' : number_format($datum['balanceAmount'], 2) ?></td>
					<td style="text-align:center; width: <?= $width ?>%"><?= $datum['balanceStatus'] ?></td>
				</tr>
				<?php $i++; ?>
				<?php $packageAmount+=$datum['packageAmount']; ?>
				<?php $enhancementAmount+=$datum['enhancementAmount']; ?>
				<?php $totalTuitionFee+=$datum['packageAmount'] + $datum['enhancementAmount']; ?>
				<?php $discountAmount+=$datum['discountAmount']; ?>
				<?php $coachingAmount+=$datum['coachingAmount']; ?>
				<?php $finalTuitionFee+=($datum['packageAmount'] + $datum['enhancementAmount'] + $datum['coachingAmount']) - $datum['discountAmount'] ?>
				<?php $paymentsMade+=$datum['incomeEnrolmentsAmount'] + $datum['freebiesAmount']; ?>
				<?php $balanceAmount+=$datum['balanceAmount']; ?>
			<?php } ?>
		<?php }  ?>
		<tr>
			<td align=right colspan=5><b>TOTAL</b></td>
			<td style="text-align: center;"><?= number_format($packageAmount, 2) ?></td>
			<td style="text-align: center;"><?= number_format($enhancementAmount, 2) ?></td>
			<td style="text-align: center;"><?= number_format($totalTuitionFee, 2) ?></td>
			<td style="text-align: center;">&nbsp;</td>
			<td style="text-align: center;"><?= number_format($discountAmount, 2) ?></td>
			<td style="text-align: center;"><?= number_format($coachingAmount, 2) ?></td>
			<td style="text-align: center;"><?= number_format($finalTuitionFee, 2) ?></td>
			<td style="text-align: center;"><?= number_format($paymentsMade, 2) ?></td>
			<td style="text-align: center;"><?= $balanceAmount > 0 ? '<font color=red>'.number_format($balanceAmount, 2).'</font>' : number_format($balanceAmount, 2) ?></td>
			<td style="text-align: center;">&nbsp;</td>
		</tr>
	</tbody>
</table>