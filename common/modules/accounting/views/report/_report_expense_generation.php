<?php 
use yiister\gentelella\widgets\Panel;
use yii\helpers\Html;
use frontend\assets\AppAsset;
$asset = AppAsset::register($this);
?>

<h3 class="text-center"><?= Html::img($asset->baseUrl.'/images/logo-blue.png',['style' => 'height: 90px; width: 350px;']) ?></h3>
<h4 class="text-center">Toprank Integrated Systems</h4>
<h4 class="text-center">Accounting</h4>
<h4 class="text-center">Daily Expense Report</h4>
<br>
<br>
<table class="table table-bordered table-condensed table-hover table-responsive" style="width: 50%;">
	<tbody>
		<tr>
			<td><b>Branch - Program</b></td>
			<td align=right><?= $selectedBranchProgram['name'] ?></td>
		</tr>
		<tr>
			<td><b>Season</b></td>
			<td align=right><?= 'SEASON '.$selectedSeason->name ?></td>
		</tr>
		<tr>
			<td><b>Cut-Off</b></td>
			<td align=right><?= $cutoff['start'].' - '.$cutoff['end'] ?></td>
		</tr>
	</tbody>
</table>


<?php if(!empty($dates)){ ?>
	<?php foreach($dates as $date){ ?>
		<p><b>DATE:&nbsp;&nbsp;&nbsp;</b><?= $date ?></p>
		<table class="table table-bordered table-condensed table-hover table-responsive">
			<thead>
				<tr>
					<td colspan=8 align=center><b>PETTY EXPENSES</b></td>
				</tr>
				<tr>
					<td align=center><b>PCV #</b></td>
					<td align=center><b>PARTICULARS</b></td>
					<td align=center><b>FOOD</b></td>
					<td align=center><b>SUPPLIES</b></td>
					<td align=center><b>LOAD</b></td>
					<td align=center><b>FARE</b></td>
					<td align=center><b>CHARGED TO</b></td>
					<td align=center><b>TYPE</b></td>
				</tr>
			</thead>
			<tbody>
				<?php $pettyTotal = 0; ?>
				<?php $pettyOne = 0; ?>
				<?php $pettyTwo = 0; ?>
				<?php $pettyThree = 0; ?>
				<?php $pettyFour = 0; ?>
				<?php if(!empty($pettyExpenses)){ ?>
					<?php foreach($pettyExpenses as $petty){ ?>
						<?php if($petty['date'] == $date){ ?>
							<tr>
								<td><?= $petty['pcv_no'] ?></td>
								<td><?= $petty['particulars'] ?></td>
								<td align=right><?= number_format($petty['food'], 2) ?></td>
								<td align=right><?= number_format($petty['supplies'], 2) ?></td>
								<td align=right><?= number_format($petty['load'], 2) ?></td>
								<td align=right><?= number_format($petty['fare'], 2) ?></td>
								<td><?= $petty['charge_to'] ?></td>
								<td><?= $petty['amountType'] ?></td>
							</tr>
							<?php $pettyOne+=$petty['food']; ?>
							<?php $pettyTwo+=$petty['supplies']; ?>
							<?php $pettyThree+=$petty['load']; ?>
							<?php $pettyFour+=$petty['fare']; ?>
						<?php } ?>
					<?php } ?>
				<?php } ?>
				<?php $pettyTotal = $pettyOne + $pettyTwo + $pettyThree + $pettyFour; ?>
				<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td colspan=2 align=right>SUBTOTAL</td>
					<td align=right><b><?= number_format($pettyOne, 2) ?></b></td>
					<td align=right><b><?= number_format($pettyTwo, 2) ?></b></td>
					<td align=right><b><?= number_format($pettyThree, 2) ?></b></td>
					<td align=right><b><?= number_format($pettyFour, 2) ?></b></td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td colspan=2 align=right>TOTAL PETTY EXPENSES</td>
					<td colspan=6><b><?= number_format($pettyTotal, 2) ?></b></td>
				</tr>
			</tbody>
		</table>
		<table class="table table-bordered table-condensed table-hover table-responsive">
			<thead>
				<tr>
					<td colspan=8 align=center><b>PHOTOCOPY EXPENSES</b></td>
				</tr>
				<tr>
					<td align=center><b>CV #</b></td>
					<td align=center><b>SUBJECT & NO. OF ITEMS</b></td>
					<td align=center><b># OF PAGES</b></td>
					<td align=center><b># OF PIECES</b></td>
					<td align=center><b>AMOUNT PER PAGE</b></td>
					<td align=center><b>TOTAL AMOUNT</b></td>
					<td align=center><b>CHARGED TO</b></td>
					<td align=center><b>TYPE</b></td>
				</tr>
			</thead>
			<tbody>
				<?php $photocopyTotal = 0; ?>
				<?php $photocopyOne = 0; ?>
				<?php if(!empty($photocopyExpenses)){ ?>
					<?php foreach($photocopyExpenses as $photocopy){ ?>
						<?php if($petty['date'] == $date){ ?>
							<tr>
								<td><?= $photocopy['cv_no'] ?></td>
								<td><?= $photocopy['subject'] ?></td>
								<td align=right><?= number_format($photocopy['no_of_pages'], 0) ?></td>
								<td align=right><?= number_format($photocopy['no_of_pieces'], 0) ?></td>
								<td align=right><?= number_format($photocopy['amount_per_page'], 2) ?></td>
								<td align=right><?= number_format($photocopy['total_amount'], 2) ?></td>
								<td><?= $photocopy['charge_to'] ?></td>
								<td><?= $photocopy['amountType'] ?></td>
							</tr>
							<?php $photocopyOne+=$photocopy['total_amount']; ?>
							<?php $photocopyTotal = $photocopyOne; ?>
						<?php } ?>
					<?php } ?>
				<?php } ?>
				<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td colspan=5 align=right>TOTAL</td>
					<td align=right><b><?= number_format($photocopyTotal, 2) ?></b></td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
			</tbody>
		</table>
		<table class="table table-bordered table-condensed table-hover table-responsive">
			<thead>
				<tr>
					<td colspan=5 align=center><b>OTHER EXPENSES</b></td>
				</tr>
				<tr>
					<td align=center><b>CV #</b></td>
					<td align=center><b>PARTICULARS</b></td>
					<td align=center><b>AMOUNT</b></td>
					<td align=center><b>CHARGED TO</b></td>
					<td align=center><b>TYPE</b></td>
				</tr>
			</thead>
			<tbody>
				<?php $otherTotal = 0; ?>
				<?php $otherOne = 0; ?>
				<?php if(!empty($otherExpenses)){ ?>
					<?php foreach($otherExpenses as $other){ ?>
						<?php if($other['date'] == $date){ ?>
							<tr>
								<td><?= $other['cv_no'] ?></td>
								<td><?= $other['particulars'] ?></td>
								<td align=right><?= number_format($other['amount'], 2) ?></td>
								<td><?= $other['charge_to'] ?></td>
								<td><?= $other['amountType'] ?></td>
							</tr>
							<?php $otherOne+=$other['amount']; ?>
							<?php $otherTotal = $otherOne; ?>
						<?php } ?>
					<?php } ?>
				<?php } ?>
				<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td colspan=2 align=right>TOTAL</td>
					<td align=right><b><?= number_format($otherTotal, 2) ?></b></td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
			</tbody>
		</table>
		<table class="table table-bordered table-condensed table-hover table-responsive">
			<thead>
				<tr>
					<td colspan=8 align=center><b>BANK DEPOSIT</b></td>
				</tr>
				<tr>
					<td align=center><b>BANK</b></td>
					<td align=center><b>ACCOUNT NO.</b></td>
					<td align=center><b>TRANSACTION NO.</b></td>
					<td align=center><b>DEPOSITED BY</b></td>
					<td align=center><b>REMARKS</b></td>
					<td align=center><b>AMOUNT</b></td>
					<td align=center><b>TYPE</b></td>
				</tr>
			</thead>
			<tbody>
				<?php $bankTotal = 0; ?>
				<?php $bankOne = 0; ?>
				<?php if(!empty($bankDeposits)){ ?>
					<?php foreach($bankDeposits as $bank){ ?>
						<?php if($bank['date'] == $date){ ?>
							<tr>
								<td><?= $bank['bank'] ?></td>
								<td><?= $bank['account_no'] ?></td>
								<td><?= $bank['transaction_no'] ?></td>
								<td><?= $bank['deposited_by'] ?></td>
								<td><?= $bank['remarks'] ?></td>
								<td align=right><?= number_format($bank['amount'], 2) ?></td>
								<td><?= $bank['amountType'] ?></td>
							</tr>
							<?php $bankOne+=$bank['amount']; ?>
						<?php } ?>
					<?php } ?>
				<?php } ?>
				<?php $bankTotal = $bankOne; ?>
				<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td colspan=5 align=right>TOTAL</td>
					<td align=right><b><?= number_format($bankTotal, 2) ?></b></td>
					<td>&nbsp;</td>
				</tr>
			</tbody>
		</table>
		<table class="table table-bordered table-condensed table-hover table-responsive">
			<thead>
				<tr>
					<td colspan=11 align=center><b>OPERATING EXPENSES</b></td>
				</tr>
				<tr>
					<td align=center><b>CV #</b></td>
					<td align=center><b>PARTICULARS</b></td>
					<td align=center><b>STAFF SALARY</b></td>
					<td align=center><b>CASH PF</b></td>
					<td align=center><b>RENT</b></td>
					<td align=center><b>UTILITIES</b></td>
					<td align=center><b>EQUIPMENT AND LABOR</b></td>
					<td align=center><b>BIR AND DOCS</b></td>
					<td align=center><b>MARKETING</b></td>
					<td align=center><b>CHARGED TO</b></td>
					<td align=center><b>TYPE</b></td>
				</tr>
			</thead>
			<tbody>
				<?php $operatingTotal = 0; ?>
				<?php $operatingOne = 0; ?>
				<?php $operatingTwo = 0; ?>
				<?php $operatingThree = 0; ?>
				<?php $operatingFour = 0; ?>
				<?php $operatingFive = 0; ?>
				<?php $operatingSix = 0; ?>
				<?php $operatingSeven = 0; ?>
				<?php if(!empty($operatingExpenses)){ ?>
					<?php foreach($operatingExpenses as $operating){ ?>
						<?php if($operating['date'] == $date){ ?>
							<tr>
								<td><?= $operating['cv_no'] ?></td>
								<td><?= $operating['particulars'] ?></td>
								<td align=right><?= number_format($operating['staff_salary'], 2) ?></td>
								<td align=right><?= number_format($operating['cash_pf'], 2) ?></td>
								<td align=right><?= number_format($operating['rent'], 2) ?></td>
								<td align=right><?= number_format($operating['utilities'], 2) ?></td>
								<td align=right><?= number_format($operating['equipment_and_labor'], 2) ?></td>
								<td align=right><?= number_format($operating['bir_and_docs'], 2) ?></td>
								<td align=right><?= number_format($operating['marketing'], 2) ?></td>
								<td><?= $operating['charge_to'] ?></td>
								<td><?= $operating['amountType'] ?></td>
							</tr>
							<?php $operatingOne+=$operating['staff_salary']; ?>
							<?php $operatingTwo+=$operating['cash_pf']; ?>
							<?php $operatingThree+=$operating['rent']; ?>
							<?php $operatingFour+=$operating['utilities']; ?>
							<?php $operatingFive+=$operating['equipment_and_labor']; ?>
							<?php $operatingSix+=$operating['bir_and_docs']; ?>
							<?php $operatingSeven+=$operating['marketing']; ?>
						<?php } ?>
					<?php } ?>
				<?php } ?>
				<?php $operatingTotal = $operatingOne + $operatingTwo + $operatingThree + $operatingFour + $operatingFive + $operatingSix + $operatingSeven; ?>
				<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td colspan=2 align=right>SUBTOTAL</td>
					<td align=right><b><?= number_format($operatingOne, 2) ?></b></td>
					<td align=right><b><?= number_format($operatingTwo, 2) ?></b></td>
					<td align=right><b><?= number_format($operatingThree, 2) ?></b></td>
					<td align=right><b><?= number_format($operatingFour, 2) ?></b></td>
					<td align=right><b><?= number_format($operatingFive, 2) ?></b></td>
					<td align=right><b><?= number_format($operatingSix, 2) ?></b></td>
					<td align=right><b><?= number_format($operatingSeven, 2) ?></b></td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td colspan=2 align=right>TOTAL OPERATING EXPENSES</td>
					<td colspan=9><b><?= number_format($operatingTotal, 2) ?></b></td>
				</tr>
			</tbody>
		</table>
		<p>TOTAL DAILY EXPENSE:&nbsp;&nbsp;&nbsp;<b><u><?= number_format($pettyTotal + $photocopyTotal + $otherTotal + $bankTotal + $operatingTotal, 2) ?></u></b></p>
		<hr>
	<?php } ?>
<?php } ?>