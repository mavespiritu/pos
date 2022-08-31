<?php 
use yii\helpers\Html;
use frontend\assets\AppAsset;
$asset = AppAsset::register($this);

$width = ceil(100/8);
$staffSalaryAmount = 0;
$cashPfAmount = 0;
$rentAmount = 0;
$utilitiesAmount = 0;
$equipmentAndLaborAmount = 0;
$birAndDocsAmount = 0;
$marketingAmount = 0;

?>
<h3 class="text-center">
	<?= Html::img($asset->baseUrl.'/images/logo-blue.png',['style' => 'height: 45px; width: 175px;']) ?>
</h3>
<h5 class="text-center">Toprank Integrated Systems<br>
						Accounting<br>
						Operating Expense Report</h5>

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
			<th>STAFF SALARY</th>
			<th>CASH PF</th>
			<th>RENT</th>
			<th>UTILITIES</th>
			<th>EQUIPMENT AND LABOR</th>
			<th>BIR AND DOCS</th>
			<th>MARKETING</th>
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
					<td style="text-align:center; width: <?= $width ?>%"><?= number_format($datum['staff_salary'], 2) ?></td>
					<td style="text-align:center; width: <?= $width ?>%"><?= number_format($datum['cash_pf'], 2) ?></td>
					<td style="text-align:center; width: <?= $width ?>%"><?= number_format($datum['rent'], 2) ?></td>
					<td style="text-align:center; width: <?= $width ?>%"><?= number_format($datum['utilities'], 2) ?></td>
					<td style="text-align:center; width: <?= $width ?>%"><?= number_format($datum['equipment_and_labor'], 2) ?></td>
					<td style="text-align:center; width: <?= $width ?>%"><?= number_format($datum['bir_and_docs'], 2) ?></td>
					<td style="text-align:center; width: <?= $width ?>%"><?= number_format($datum['marketing'], 2) ?></td>
					<td style="text-align:center; width: <?= $width ?>%"><?= $datum['charge_to'] ?></td>
					<td style="text-align:center; width: <?= $width ?>%"><?= $datum['amount_type'] ?></td>
					<td style="text-align:center; width: <?= $width ?>%"><?= $datum['transaction_number'] ?></td>
					<td style="text-align:center; width: <?= $width ?>%"><?= $datum['datetime'] ?></td>
				</tr>
				<?php $i++; ?>
				<?php $staffSalaryAmount+=$datum['staff_salary']; ?>
				<?php $cashPfAmount+=$datum['cash_pf']; ?>
				<?php $rentAmount+=$datum['rent']; ?>
				<?php $utilitiesAmount+=$datum['utilities']; ?>
				<?php $equipmentAndLaborAmount+=$datum['equipment_and_labor']; ?>
				<?php $birAndDocsAmount+=$datum['bir_and_docs']; ?>
				<?php $marketingAmount+=$datum['marketing']; ?>
			<?php } ?>
		<?php }  ?>
		<tr>
			<td align=right colspan=3><b>TOTAL</b></td>
			<td style="text-align: center;"><?= number_format($staffSalaryAmount, 2) ?></td>
			<td style="text-align: center;"><?= number_format($cashPfAmount, 2) ?></td>
			<td style="text-align: center;"><?= number_format($rentAmount, 2) ?></td>
			<td style="text-align: center;"><?= number_format($utilitiesAmount, 2) ?></td>
			<td style="text-align: center;"><?= number_format($equipmentAndLaborAmount, 2) ?></td>
			<td style="text-align: center;"><?= number_format($birAndDocsAmount, 2) ?></td>
			<td style="text-align: center;"><?= number_format($marketingAmount, 2) ?></td>
			<td style="text-align: center;">&nbsp;</td>
			<td style="text-align: center;">&nbsp;</td>
			<td style="text-align: center;">&nbsp;</td>
			<td style="text-align: center;">&nbsp;</td>
		</tr>
	</tbody>
</table>
