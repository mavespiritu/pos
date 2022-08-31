<?php

use yii\helpers\Html;
use yiister\gentelella\widgets\Panel;
use yii\widgets\DetailView;
/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\BudgetProposal */

$this->title = 'Step 3: Liquidate Expenses';
$this->params['breadcrumbs'][] = ['label' => 'Budget Proposals', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Step 2: Encode Budget Proposal Sheet', 'url' => ['/accounting/budget-proposal/particular', 'id' => $model->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="budget-proposal-create">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php Panel::begin(['header' => 'Liquidation Form']); ?>
    	<div class="row">
    		<div class="col-md-3">
    			<?php Panel::begin(['header' => 'Request Details']); ?>
	    			<?= DetailView::widget([
		                'model' => $model,
		                'attributes' => [
		                    'branchName',
		                    'branchProgramName',
		                    'codeName',
		                    'budgetProposalTypeName',
		                    'datetime',
		                    'approval_status',
		                    'remarks'
		                ],
		            ]) ?>
	            <?php Panel::end(); ?>
	            <?php Panel::begin(['header' => 'Liquidation']); ?>
	            	<?php $total = 0; ?>
	            	<p>Approved Amount: <span class="pull-right"><b><?= number_format($approvedAmount['total'], 2) ?></b></span></p>
	            	<table class="table table-bordered table-responsive table-hover">
	            		<tbody>
	            			<tr>
	            				<th colspan=2>Petty Expenses</th>
	            			</tr>
	            			<?php if(!empty($liquidationSummary)){ ?>
		            			<?php foreach($liquidationSummary as $summary){ ?>
		            				<?php if($summary['expense_type_id'] == 1){ ?>
		            					<tr>
		            						<td align=right><?= $summary['name'] ?></td>
		            						<td align=right><?= number_format($summary['total'], 2) ?></td>
		            					</tr>
		            				<?php } ?>
		            				<?php $total+=$summary['total'] ?>
		            			<?php } ?>
	            				<?php foreach($liquidationSummary as $summary){ ?>
		            				<?php if($summary['expense_type_id'] == 2){ ?>
		            					<tr>
		            						<td align=right><?= $summary['name'] ?></td>
		            						<td align=right><?= number_format($summary['total'], 2) ?></td>
		            					</tr>
		            				<?php } ?>
		            			<?php } ?>
		            			<tr>
		            				<th colspan=2>Operating Expenses</th>
		            			</tr>
		            			<?php foreach($liquidationSummary as $summary){ ?>
		            				<?php if($summary['expense_type_id'] == 5){ ?>
		            					<tr>
		            						<td align=right><?= $summary['name'] ?></td>
		            						<td align=right><?= number_format($summary['total'], 2) ?></td>
		            					</tr>
		            				<?php } ?>
		            			<?php } ?>
		            			<?php foreach($liquidationSummary as $summary){ ?>
		            				<?php if($summary['expense_type_id'] == 3){ ?>
		            					<tr>
		            						<td align=right><?= $summary['name'] ?></td>
		            						<td align=right><?= number_format($summary['total'], 2) ?></td>
		            					</tr>
		            				<?php } ?>
		            			<?php } ?>
		            		<?php } ?>
		            		<tr>
	            				<td align=right><b>TOTAL</b></td>
	            				<td align=right><?= number_format($total, 2) ?></td>
	            			</tr>
	            		</tbody>
	            	</table>
	            	<p>Remaining Amount: <span class="pull-right"><b><?= number_format($approvedAmount['total'] - $total, 2) ?></b></span></p>
	            <?php Panel::end(); ?>
    		</div>
    		<div class="col-md-9">
    			<div class="row">
    				<div class="col-md-12">
    					<p class="pull-right"><?= Html::a('Go Back to Budget Proposal Sheet',['/accounting/budget-proposal/particular', 'id' => $model->id],['class' => 'btn btn-success']) ?></p>
    				</div>
    			</div>
    			<div class="row">
	                <div class="col-md-4">
	                    <p>Requested Amount: <span class="pull-right"><b><?= number_format($requestedAmount['total'], 2) ?></b></span></p>
	                </div>
	                <div class="col-md-4">
	                    <p>Approved Amount: <span class="pull-right"><b><?= number_format($approvedAmount['total'], 2) ?></b></span></p>
	                </div>
	                <div class="col-md-4">
	                    <p>Percent Approved: <span class="pull-right"><b><?= number_format($percentApproved, 2) ?>%</b></span></p>
	                </div>
	            </div>
	            <div class="row">
	                <div class="col-md-4">
	                    <p>Amount Liquidated: <span class="pull-right"><b><?= number_format($liquidatedAmount['total'], 2) ?></b></span></p>
	                </div>
	                <div class="col-md-4">
	                    <p>Liquidation Percentage: <span class="pull-right"><b><?= number_format($liquidationPercentage, 2) ?>%</b></span></p>
	                </div>
	                <div class="col-md-4">
	                    <p>Unliquidated Amount: <span class="pull-right"><b><?= number_format($unliquidatedAmount, 2) ?></b></span></p>
	                </div>
	            </div>
	            <?php if(Yii::$app->user->can('liquidateBudgetProposal')){ ?>
	    			<?php Panel::begin(['header' => 'Add/Edit Particulars']); ?>
		    			<?= $this->render('_liquidation', [
					        'model' => $model,
					        'liquidation' => $liquidation,
					        'liquidationCategories' => $liquidationCategories
					    ]) ?>
				    <?php Panel::end(); ?>
				<?php } ?>
			    <?php Panel::begin(['header' => 'Particulars']); ?>
				    <table class="table table-bordered table-responsive table-striped table-hover">
		                <thead>
		                    <tr>
		                        <th>Category</th>
		                        <th>Date</th>
		                        <th>Particulars</th>
		                        <th>Amount</th>
		                        <th>&nbsp;</th>
		                        <th>&nbsp;</th>
		                    </tr>
		                </thead>
		                <tbody>
		                	<?php if($model->liquidations){ ?>
		                		<?php $total = 0; ?>
		                		<?php foreach($model->liquidations as $liquidation){ ?>
		                			<tr>
		                				<td><?= $liquidation->liquidationCategory->name ?></td>
		                				<td><?= $liquidation->date ?></td>
		                				<td><?= $liquidation->particulars ?></td>
		                				<td><?= number_format($liquidation->amount, 2) ?></td>
		                				<td width=5><?= Yii::$app->user->can('liquidateBudgetProposal') ? Html::a('<i class="glyphicon glyphicon-pencil"></i>',['/accounting/budget-proposal/liquidation-update', 'id' => $liquidation->id]) : '' ?></td>
		                				<td width=5><?= Yii::$app->user->can('liquidateBudgetProposal') ? Html::a('<i class="glyphicon glyphicon-trash"></i>',['/accounting/budget-proposal/liquidation-delete', 'id' => $liquidation->id],
		                					[
		                					'data' => [
				                                'confirm' => 'Are you sure you want to delete this item?',
				                                'method' => 'post',
				                            ]]) : '' ?></td>
		                			</tr>
		                			<?php $total+=$liquidation->amount; ?>
		                		<?php } ?>
		                		<tr>
		                			<td colspan=3 align=right><b>TOTAL</b></td>
		                			<td><b><?= number_format($total, 2) ?></b></td>
		                			<td colspan=2>&nbsp;</td>
		                		</tr>
		                	<?php }else{ ?>
		                		<tr>
		                			<td colspan=8>No results found</td>
		                		</tr>
		                	<?php } ?>
		                </tbody>
		            </table>
	            <?php Panel::end(); ?>
    		</div>
    	</div>
    <?php Panel::end(); ?>
</div>
