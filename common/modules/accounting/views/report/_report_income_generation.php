<?php 
use yiister\gentelella\widgets\Panel;
use yii\helpers\Html;
use frontend\assets\AppAsset;
$asset = AppAsset::register($this);
?>

<h3 class="text-center"><?= Html::img($asset->baseUrl.'/images/logo-blue.png',['style' => 'height: 90px; width: 350px;']) ?></h3>
<h4 class="text-center">Toprank Integrated Systems</h4>
<h4 class="text-center">Accounting</h4>
<h4 class="text-center">Daily Income Report</h4>
<br>
<br>
<table style="width:100%;">
	<tr>
		<td>
			<table class="table table-bordered table-condensed table-hover table-responsive" style="width: 70%;">
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
					<tr>
						<td><b>Beginning COH & COB</b></td>
						<td align=right><?= $beginningCoh ?  number_format(($beginningCoh->cash_on_hand + $beginningCoh->cash_on_bank), 2) : number_format(0, 2) ?></td>
					</tr>
				</tbody>
			</table>
		</td>
		<td>
			<p><b>CODES</b></p>
			<?php if(!empty($incomeCodes)){ ?>
				<table style="width:100%;">
					<tr>
						<td>
							<table style="width: 100%;">
								<?php for($i=0; $i<floor(count($incomeCodes)/2); $i++){ ?>
										<tr>
											<td><?= $incomeCodes[$i]['name'] ?></td>
											<td><?= $incomeCodes[$i]['description'] ?></td>
										</tr>
								<?php } ?>
							</table>
						</td>
						<td>
							<table style="width: 100%;">
								<?php for($i=floor(count($incomeCodes)/2)+1; $i<count($incomeCodes); $i++){ ?>
										<tr>
											<td><?= $incomeCodes[$i]['name'] ?></td>
											<td><?= $incomeCodes[$i]['description'] ?></td>
										</tr>
								<?php } ?>
							</table>
						</td>
					</tr>
				</table>
			<?php } ?>
		</td>
	</tr>
</table>
<?php if(!empty($dates)){ ?>
	<?php foreach($dates as $date){ ?>
		<p><b>DATE:&nbsp;&nbsp;&nbsp;</b><?= $date ?></p>
		<table class="table table-bordered table-condensed table-hover table-responsive">
			<thead>
				<tr>
					<td colspan=5 align=center><b>ENROLMENTS</b></td>
				</tr>
				<tr>
					<td align=center><b>O.R.</b></td>
					<td align=center><b>CODE</b></td>
					<td align=center><b>NAME</b></td>
					<td align=center><b>AMOUNT</b></td>
					<td align=center><b>TYPE</b></td>
				</tr>
			</thead>
			<tbody>
				<?php $totalOne = 0; ?>
				<?php if(!empty($incomeEnrolments)){ ?>
					<?php foreach($incomeEnrolments as $income){ ?>
						<?php if($income['date'] == $date){ ?>
							<tr>
								<td><?= $income['or_no'] ?></td>
								<td><?= $income['codeName'] ?></td>
								<td><?= $income['studentName'] ?></td>
								<td align=right><?= number_format($income['amount'], 2) ?></td>
								<td><?= $income['amountType'] ?></td>
							</tr>
							<?php $totalOne+=$income['amount']; ?>
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
					<td colspan=3 align=right>TOTAL CASH-IN</td>
					<td align=right><b><?= number_format($totalOne, 2) ?></b></td>
					<td>&nbsp;</td>
				</tr>
			</tbody>
		</table>
		<table class="table table-bordered table-condensed table-hover table-responsive">
			<thead>
				<tr>
					<td colspan=5 align=center><b>FREEBIES AND ICONS Fees</b></td>
				</tr>
				<tr>
					<td align=center><b>P.R.</b></td>
					<td align=center><b>CODE</b></td>
					<td align=center><b>NAME</b></td>
					<td align=center><b>AMOUNT</b></td>
					<td align=center><b>TYPE</b></td>
				</tr>
			</thead>
			<tbody>
				<?php $totalTwo = 0; ?>
				<?php if(!empty($freebies)){ ?>
					<?php foreach($freebies as $freebie){ ?>
						<?php if($freebie['date'] == $date){ ?>
							<tr>
								<td><?= $freebie['pr'] ?></td>
								<td><?= $freebie['codeName'] ?></td>
								<td><?= $freebie['studentName'] ?></td>
								<td align=right><?= number_format($freebie['amount'], 2) ?></td>
								<td><?= $income['amountType'] ?></td>
							</tr>
							<?php $totalTwo+=$freebie['amount']; ?>
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
					<td colspan=3 align=right>TOTAL CASH-IN</td>
					<td align=right><b><?= number_format($totalTwo, 2) ?></b></td>
					<td>&nbsp;</td>
				</tr>
			</tbody>
		</table>
		<table class="table table-bordered table-condensed table-hover table-responsive">
			<thead>
				<tr>
					<td colspan=4 align=center><b>BUDGET PROPOSALS</b></td>
				</tr>
				<tr>
					<td align=center><b>CODE</b></td>
					<td align=center><b>DETAILS</b></td>
					<td align=center><b>AMOUNT</b></td>
					<td align=center><b>TYPE</b></td>
				</tr>
			</thead>
			<tbody>
				<?php $totalThree = 0; ?>
				<?php if(!empty($budgetProposals)){ ?>
					<?php foreach($budgetProposals as $budgetProposal){ ?>
						<?php if($budgetProposal['date'] == $date){ ?>
							<tr>
								<td><?= $budgetProposal['codeName'] ?></td>
								<td><?= $budgetProposal['detail'] ?></td>
								<td align=right><?= number_format($budgetProposal['total'], 2) ?></td>
								<td><?= $budgetProposal['amountType'] ?></td>
							</tr>
							<?php $totalThree+=$budgetProposal['total']; ?>
						<?php } ?>
					<?php } ?>
				<?php } ?>
				<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td colspan=2 align=right>TOTAL CASH-IN</td>
					<td align=right><b><?= number_format($totalThree, 2) ?></b></td>
					<td>&nbsp;</td>
				</tr>
			</tbody>
		</table>
		<p>TOTAL DAILY INCOME:&nbsp;&nbsp;&nbsp;<b><u><?= number_format($totalOne + $totalTwo + $totalThree,2)?></u></b></p>
		<hr>
	<?php } ?>
<?php } ?>