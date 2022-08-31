<?php
use yii\helpers\Html;
use yiister\gentelella\widgets\Panel;
$finalTuition = ($studentTuition['packageAmount'] + $studentTuition['enhancementAmount']) - $studentTuition['discountAmount'];
$paymentTotal = 0;
?>
<div class="balance-view">
	<?php Panel::begin(['header' => 'Tuition']); ?>
	<table class="table table-bordered table-responsive">
		<tr>
			<td>Regular Review Price</td>
			<td align="right"><?= number_format($studentTuition['packageAmount'], 2) ?></td>
		</tr>
		<tr>
			<td>Enhancement Price</td>
			<td align="right"><?= number_format($studentTuition['enhancementAmount'], 2) ?></td>
		</tr>
		<tr>
			<td>Total Tuition</td>
			<td align="right"><?= number_format(($studentTuition['packageAmount'] + $studentTuition['enhancementAmount']), 2) ?></td>
		</tr>
		<tr>
			<td>Coaching With Icons</td>
			<td align="right"><?= number_format($coaching['amount'], 2) ?></td>
		</tr>
		<tr>
			<td align="right">Discount</td>
			<td align="right"><?= number_format($studentTuition['discountAmount'], 2) ?></td>
		</tr>
		<tr>
			<th>Final Tuition Fee</th>
			<td align="right"><b><?= number_format($finalTuition, 2) ?></b></td>
		</tr>
		<?php if(!empty($coaching)){ ?>
			<tr>
				<th>Final Tuition Fee (with Coaching Icons)</th>
				<td align="right"><b><?= number_format($finalTuition + $coaching['amount'], 2) ?></b></td>
			</tr>
		<?php } ?>
	</table>
	<?php Panel::end(); ?>
	<?php Panel::begin(['header' => 'Payments and Balances']); ?>
	<table class="table table-bordered table-responsive">
		<tr>
			<th>OR/PR</th>
			<th>Date Paid</th>
			<th>Code</th>
			<th>Type</th>
			<th>Amount</th>
		</tr>
		<?php if(!empty($payments)){ ?>
			<?php foreach($payments as $payment){ ?>
				<tr>
					<td><?= $payment['or_no'] ?></td>
					<td><?= $payment['datetime'] ?></td>
					<td><?= $payment['code'] ?></td>
					<td><?= $payment['amount_type'] ?></td>
					<td align="right"><?= number_format($payment['amount'], 2) ?></td>
				</tr>
				<?php $paymentTotal += $payment['amount']; ?>
			<?php } ?>
		<?php } ?>
		<tr>
			<td align="right" colspan=4><b>Total Payments Made:</b></td>
			<td align="right"><b><?= $paymentTotal < 0 ? '<font style="color: red">'.number_format($paymentTotal, 2).'</font>' : number_format($paymentTotal, 2) ?></b></td>
		</tr>
		<tr>
			<td align="right" colspan=4><b>Unpaid Balance:</b></td>
			<td align="right"><b><?= ($finalTuition + $coaching['amount'] - $paymentTotal) > 0 ? '<font style="color: red">'.number_format(($finalTuition + $coaching['amount'] - $paymentTotal), 2).'</font>' : number_format(($finalTuition + $coaching['amount'] - $paymentTotal), 2) ?></b></td>
		</tr>
		<tr>
			<td align="right" colspan=4><b>Balance Status:</b></td>
			<td align="right"><b><?= ($finalTuition + $coaching['amount'] - $paymentTotal) > 0 ? '<font style="color: red">With Balance</font>' : 'Cleared' ?></b></td>
		</tr>
	</table>
	<?php Panel::end(); ?>
</div>