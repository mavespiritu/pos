<?php
use yiister\gentelella\widgets\Panel;

?>
<?php Panel::begin(); ?>
	<p class="text-center"><b>As of <?= date("F j, Y") ?></b></p>
	<div class="row tile_count">
		<div class="col-md-4 tile_stats_count">
			<span class="count_top">Cash On Hand</span>
			<div class="count"><?= number_format($totalCash, 2) ?></div>
		</div>
		<div class="col-md-4 tile_stats_count">
			<span class="count_top">Target Net Income</span>
			<div class="count"><?= number_format($season->netIncome, 2) ?></div>
		</div>
		<div class="col-md-4 tile_stats_count">
			<span class="count_top">Net Income %</span>
			<div class="count"><?= $season->netIncome > 0 ? number_format(($ending_coh_cash + $ending_coh_bank)/$season->netIncome, 2).'%' : number_format(0, 2).'%' ?></div>
		</div>
	</div>
	<div class="row tile_count">
		<div class="col-md-4 tile_stats_count">
			<span class="count_top">Cash On Bank</span>
			<div class="count"><?= number_format($totalBank, 2) ?></div>
		</div>
		<div class="col-md-4 tile_stats_count">
			<span class="count_top">Target Expenses</span>
			<div class="count"><?= number_format($season->totalExpenses, 2) ?></div>
		</div>
		<div class="col-md-4 tile_stats_count">
			<span class="count_top">Target Expense %</span>
			<div class="count"><?= $season->totalExpenses > 0 ? number_format((($total_expenses_cash - $dataOnCashStartToEndDay['bankDepositsTotal']) + ($total_expenses_bank - $dataOnBankStartToEndDay['bankDepositsTotal']))/$season->totalExpenses, 2).'%' : number_format(0, 2).'%' ?></div>
		</div>
	</div>
<?php Panel::end(); ?>