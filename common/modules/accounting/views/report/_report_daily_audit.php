<?php 
use yiister\gentelella\widgets\Panel;
use yii\helpers\Html;
use frontend\assets\AppAsset;
$asset = AppAsset::register($this);

$bCoh = 0;
$bCob = 0;
$eCoh = 0;
$eCob = 0;
?>

<h3 class="text-center"><?= Html::img($asset->baseUrl.'/images/logo-blue.png',['style' => 'height: 90px; width: 350px;']) ?></h3>
<h4 class="text-center">Toprank Integrated Systems</h4>
<h4 class="text-center">Accounting</h4>
<h4 class="text-center">Daily Audit Summary Report</h4>
<br>
<br>
<?php $bCoh += $beginningCoh ? $beginningCoh->cash_on_hand : 0; ?>
<?php $bCob+= $beginningCoh ? $beginningCoh->cash_on_bank : 0; ?>
<?php if(!empty($dates)){ ?>
	<?php foreach($dates as $date){ ?>
		<?php $incomeTotal = 0; ?>
		<?php $incomeNonCashTotal = 0; ?>
		<?php $expenseTotal = 0; ?>
		<?php $expenseNonCashTotal = 0; ?>
		<?php $denTotal = 0; ?>
		<p><b>DATE:&nbsp;&nbsp;&nbsp;</b><?= $date ?></p>
		<p><b>BEGINNING COH:&nbsp;&nbsp;&nbsp;</b><?= number_format($bCoh,2) ?></p>
		<table style="width: 100%;">
			<tr>
				<td style="width: 33.33%;" valign="top">
					<table class="table table-bordered table-condensed table-hover table-responsive">
						<thead>
							<tr>
								<td align=center><b>INCOME</b></td>
								<td align=center><b>AMOUNT</b></td>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>Enrolments</td>
								<td align=right>
									<?php if(isset($data['incomeEnrolments']['Cash'][$date])){ ?>
										<?= number_format($data['incomeEnrolments']['Cash'][$date]['total'], 2) ?>
										<?php $incomeTotal +=  $data['incomeEnrolments']['Cash'][$date]['total']; ?>
									<?php }else{ ?>
										<?= number_format(0, 2) ?>
									<?php } ?>
								</td>
							</tr>
							<tr>
								<td>Freebies and Icons</td>
								<td align=right>
									<?php if(isset($data['freebies']['Cash'][$date])){ ?>
										<?= number_format($data['freebies']['Cash'][$date]['total'], 2) ?>
										<?php $incomeTotal +=  $data['freebies']['Cash'][$date]['total']; ?>
									<?php }else{ ?>
										<?= number_format(0, 2) ?>
									<?php } ?>
								</td>
							</tr>
							<tr>
								<td>BP and Others</td>
								<td align=right>
									<?php if(isset($data['budgetProposals']['Cash'][$date])){ ?>
										<?= number_format($data['budgetProposals']['Cash'][$date]['total'], 2) ?>
										<?php $incomeTotal +=  $data['budgetProposals']['Cash'][$date]['total']; ?>
									<?php }else{ ?>
										<?= number_format(0, 2) ?>
									<?php } ?>
								</td>
							</tr>
							<tr>
								<td align=right><b>TOTAL</b></td>
								<td align=right><b><?= number_format($incomeTotal, 2) ?></b></td>
							</tr>
						</tbody>
					</table>
				</td>
				<td style="width: 33.33%;" valign="top">
					<table class="table table-bordered table-condensed table-hover table-responsive">
						<thead>
							<tr>
								<td align=center><b>EXPENSES</b></td>
								<td align=center><b>AMOUNT</b></td>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>Petty Expenses</td>
								<td align=right>
									<?php if(isset($data['pettyExpenses']['Cash'][$date])){ ?>
										<?= number_format($data['pettyExpenses']['Cash'][$date]['total'], 2) ?>
										<?php $expenseTotal +=  $data['pettyExpenses']['Cash'][$date]['total']; ?>
									<?php }else{ ?>
										<?= number_format(0, 2) ?>
									<?php } ?>
								</td>
							</tr>
							<tr>
								<td>Photocopy</td>
								<td align=right>
									<?php if(isset($data['photocopyExpenses']['Cash'][$date])){ ?>
										<?= number_format($data['photocopyExpenses']['Cash'][$date]['total'], 2) ?>
										<?php $expenseTotal +=  $data['photocopyExpenses']['Cash'][$date]['total']; ?>
									<?php }else{ ?>
										<?= number_format(0, 2) ?>
									<?php } ?>
								</td>
							</tr>
							<tr>
								<td>Bank Deposits</td>
								<td align=right>
									<?php if(isset($data['bankDeposits']['Cash'][$date])){ ?>
										<?= number_format($data['bankDeposits']['Cash'][$date]['total'], 2) ?>
										<?php $expenseTotal +=  $data['bankDeposits']['Cash'][$date]['total']; ?>
									<?php }else{ ?>
										<?= number_format(0, 2) ?>
									<?php } ?>
								</td>
							</tr>
							<tr>
								<td>Operating</td>
								<td align=right>
									<?php if(isset($data['operatingExpenses']['Cash'][$date])){ ?>
										<?= number_format($data['operatingExpenses']['Cash'][$date]['total'], 2) ?>
										<?php $expenseTotal +=  $data['operatingExpenses']['Cash'][$date]['total']; ?>
									<?php }else{ ?>
										<?= number_format(0, 2) ?>
									<?php } ?>
								</td>
							</tr>
							<tr>
								<td>Others</td>
								<td align=right>
									<?php if(isset($data['otherExpenses']['Cash'][$date])){ ?>
										<?= number_format($data['otherExpenses']['Cash'][$date]['total'], 2) ?>
										<?php $expenseTotal +=  $data['otherExpenses']['Cash'][$date]['total']; ?>
									<?php }else{ ?>
										<?= number_format(0, 2) ?>
									<?php } ?>
								</td>
							</tr>
							<tr>
								<td>Branch Transfers</td>
								<td align=right>
									<?php if(isset($data['branchTransfers']['Cash'][$date])){ ?>
										<?= number_format($data['branchTransfers']['Cash'][$date]['total'], 2) ?>
										<?php $expenseTotal +=  $data['branchTransfers']['Cash'][$date]['total']; ?>
									<?php }else{ ?>
										<?= number_format(0, 2) ?>
									<?php } ?>
								</td>
							</tr>
							<tr>
								<td align=right><b>TOTAL</b></td>
								<td align=right><b><?= number_format($expenseTotal, 2) ?></b></td>
							</tr>
						</tbody>
					</table>
				</td>
				<td style="width: 33.33%;" valign="top">
					<table class="table table-bordered table-condensed table-hover table-responsive">
						<thead>
							<tr>
								<td align=center><b>DENOMINATION</b></td>
								<td align=center><b>COUNT</b></td>
								<td align=center><b>TOTAL</b></td>
							</tr>
						</thead>
						<tbody>
							<?php if(!empty($denominations)){ ?>
								<?php foreach($denominations as $denomination){ ?>
									<tr>
										<td align=center><?= $denomination['denomination'] ?></td>
										<td align=center><?= $denomination['value'] ?></td>
										<td align=right><?= number_format($denomination['denomination']*$denomination['value'],2) ?></td>
									</tr>
									<?php $denTotal += ($denomination['denomination']*$denomination['value']); ?>
								<?php } ?>
							<?php } ?>
							<tr>
								<td align=right colspan=2><b>TOTAL</b></td>
								<td align=right><b><?= number_format($denTotal, 2) ?></b></td>
							</tr>
							<tr>
								<td align=right colspan=2><b>DISCREPANCY</b></td>
								<td align=right><b><?= number_format($denTotal - (($bCoh + $incomeTotal) - $expenseTotal), 2) ?></b></td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
		</table>
		<?php $eCoh = ($bCoh + $incomeTotal) - $expenseTotal; ?>
		<?php $bCoh = $eCoh; ?>
		<p><b>ENDING COH:&nbsp;&nbsp;&nbsp;</b><?= number_format($eCoh,2) ?></p>
		<p><b>BEGINNING COB:&nbsp;&nbsp;&nbsp;</b><?= number_format($bCob,2) ?></p>
		<table style="width: 100%;">
			<tr>
				<td style="width: 33.33%;" valign="top">
					<table class="table table-bordered table-condensed table-hover table-responsive">
						<thead>
							<tr>
								<td align=center><b>INCOME</b></td>
								<td align=center><b>AMOUNT</b></td>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>Enrolments</td>
								<td align=right>
									<?php if(isset($data['incomeEnrolments']['Non-Cash'][$date])){ ?>
										<?= number_format($data['incomeEnrolments']['Non-Cash'][$date]['total'], 2) ?>
										<?php $incomeNonCashTotal +=  $data['incomeEnrolments']['Non-Cash'][$date]['total']; ?>
									<?php }else{ ?>
										<?= number_format(0, 2) ?>
									<?php } ?>
								</td>
							</tr>
							<tr>
								<td>Freebies and Icons</td>
								<td align=right>
									<?php if(isset($data['freebies']['Non-Cash'][$date])){ ?>
										<?= number_format($data['freebies']['Non-Cash'][$date]['total'], 2) ?>
										<?php $incomeNonCashTotal +=  $data['freebies']['Non-Cash'][$date]['total']; ?>
									<?php }else{ ?>
										<?= number_format(0, 2) ?>
									<?php } ?>
								</td>
							</tr>
							<tr>
								<td>BP and Others</td>
								<td align=right>
									<?php if(isset($data['budgetProposals']['Non-Cash'][$date])){ ?>
										<?= number_format($data['budgetProposals']['Non-Cash'][$date]['total'], 2) ?>
										<?php $incomeNonCashTotal +=  $data['budgetProposals']['Non-Cash'][$date]['total']; ?>
									<?php }else{ ?>
										<?= number_format(0, 2) ?>
									<?php } ?>
								</td>
							</tr>
							<tr>
								<td align=right><b>TOTAL</b></td>
								<td align=right><b><?= number_format($incomeNonCashTotal, 2) ?></b></td>
							</tr>
						</tbody>
					</table>
				</td>
				<td style="width: 33.33%;" valign="top">
					<table class="table table-bordered table-condensed table-hover table-responsive">
						<thead>
							<tr>
								<td align=center><b>EXPENSES</b></td>
								<td align=center><b>AMOUNT</b></td>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>Petty Expenses</td>
								<td align=right>
									<?php if(isset($data['pettyExpenses']['Non-Cash'][$date])){ ?>
										<?= number_format($data['pettyExpenses']['Non-Cash'][$date]['total'], 2) ?>
										<?php $expenseNonCashTotal +=  $data['pettyExpenses']['Non-Cash'][$date]['total']; ?>
									<?php }else{ ?>
										<?= number_format(0, 2) ?>
									<?php } ?>
								</td>
							</tr>
							<tr>
								<td>Photocopy</td>
								<td align=right>
									<?php if(isset($data['photocopyExpenses']['Non-Cash'][$date])){ ?>
										<?= number_format($data['photocopyExpenses']['Non-Cash'][$date]['total'], 2) ?>
										<?php $expenseNonCashTotal +=  $data['photocopyExpenses']['Non-Cash'][$date]['total']; ?>
									<?php }else{ ?>
										<?= number_format(0, 2) ?>
									<?php } ?>
								</td>
							</tr>
							<tr>
								<td>Bank Deposits</td>
								<td align=right>
									<?php if(isset($data['bankDeposits']['Non-Cash'][$date])){ ?>
										<?= number_format($data['bankDeposits']['Non-Cash'][$date]['total'], 2) ?>
										<?php $expenseNonCashTotal +=  $data['bankDeposits']['Non-Cash'][$date]['total']; ?>
									<?php }else{ ?>
										<?= number_format(0, 2) ?>
									<?php } ?>
								</td>
							</tr>
							<tr>
								<td>Operating</td>
								<td align=right>
									<?php if(isset($data['operatingExpenses']['Non-Cash'][$date])){ ?>
										<?= number_format($data['operatingExpenses']['Non-Cash'][$date]['total'], 2) ?>
										<?php $expenseNonCashTotal +=  $data['operatingExpenses']['Non-Cash'][$date]['total']; ?>
									<?php }else{ ?>
										<?= number_format(0, 2) ?>
									<?php } ?>
								</td>
							</tr>
							<tr>
								<td>Others</td>
								<td align=right>
									<?php if(isset($data['otherExpenses']['Non-Cash'][$date])){ ?>
										<?= number_format($data['otherExpenses']['Non-Cash'][$date]['total'], 2) ?>
										<?php $expenseNonCashTotal +=  $data['otherExpenses']['Non-Cash'][$date]['total']; ?>
									<?php }else{ ?>
										<?= number_format(0, 2) ?>
									<?php } ?>
								</td>
							</tr>
							<tr>
								<td>Branch Transfers</td>
								<td align=right>
									<?php if(isset($data['branchTransfers']['Non-Cash'][$date])){ ?>
										<?= number_format($data['branchTransfers']['Non-Cash'][$date]['total'], 2) ?>
										<?php $expenseNonCashTotal +=  $data['branchTransfers']['Non-Cash'][$date]['total']; ?>
									<?php }else{ ?>
										<?= number_format(0, 2) ?>
									<?php } ?>
								</td>
							</tr>
							<tr>
								<td align=right><b>TOTAL</b></td>
								<td align=right><b><?= number_format($expenseNonCashTotal, 2) ?></b></td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
		</table>
		<?php $eCob = ($bCob + $incomeNonCashTotal) - $expenseNonCashTotal; ?>
		<?php $bCob = $eCob; ?>
		<p><b>ENDING COB:&nbsp;&nbsp;&nbsp;</b><?= number_format($eCob,2) ?></p>
		<hr>
	<?php } ?>
<?php } ?>