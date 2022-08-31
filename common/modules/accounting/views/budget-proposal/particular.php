<?php

use yii\helpers\Html;
use yiister\gentelella\widgets\Panel;
use yii\widgets\DetailView;
/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\BudgetProposal */

$this->title = 'Step 2: Encode Budget Proposal Sheet';
$this->params['breadcrumbs'][] = ['label' => 'Budget Proposals', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="budget-proposal-create">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php Panel::begin(['header' => 'Budget Proposal Sheet']); ?>
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

		            <div class="row">
		            	<div class="col-md-6">
		            		<?= ($model->approval_status == "For Approval" || $model->approval_status == "Revisions Needed") && Yii::$app->user->can('createBudgetProposal') ? Html::a('Edit Request', ['/accounting/budget-proposal/update', 'id' => $model->id],['class' => 'btn btn-success btn-block']) : '' ?>
		            	</div>
		            	<div class="col-md-6">
		            		<?= $model->approval_status == "For Approval" && Yii::$app->user->can('createBudgetProposal') ? Html::a('Delete Request', ['/accounting/budget-proposal/delete', 'id' => $model->id],
		            			[
		            				'class' => 'btn btn-danger btn-block',
	            					'data' => [
		                                'confirm' => 'Are you sure you want to delete this item?',
		                                'method' => 'post',
		                            ]
				                ]) : '' ?>
		            	</div>
		            </div>
	            <?php Panel::end(); ?>
	            <?php Panel::begin(['header' => 'Liquidation']); ?>
	            	<?php $total = 0; ?>
	            	<p>
				    	<?php if(Yii::$app->user->can('liquidateBudgetProposal')){ ?>
				    		<?= Html::a('Liquidate Expenses',['/accounting/budget-proposal/liquidate', 'id' => $model->id],['class' => 'btn btn-success btn-block']) ?>
				    	<?php }else{ ?>
				    		<?=  Html::a('View Liquidation',['/accounting/budget-proposal/liquidate', 'id' => $model->id],['class' => 'btn btn-success btn-block']) ?>
				    	<?php } ?>
				    </p>
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
    					<p class="pull-right"><?= Html::a('Go Back to Budget Proposal List',['/accounting/budget-proposal/'],['class' => 'btn btn-success']) ?></p>
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
	            <p class="pull-right">
			    	<?php if(Yii::$app->user->can('createBudgetProposal')){ ?>
			    		<?= $model->budgetProposalTypeName == 'Professional Fee' ? Html::a('Attach PF Request',['/accounting/budget-proposal/pf-request', 'id' => $model->id],['class' => 'btn btn-info']) : '' ?>
			    	<?php }else{ ?>
			    		<?= $model->budgetProposalTypeName == 'Professional Fee' ? Html::a('View PF Request',['/accounting/budget-proposal/pf-request', 'id' => $model->id],['class' => 'btn btn-info']) : '' ?>
			    	<?php } ?>
			    </p>
	            <?php if(Yii::$app->user->can('createBudgetProposal')){ ?>
	            	<?php if($model->approval_status != 'Approved'){ ?>
		    			<?php Panel::begin(['header' => 'Add/Edit Particulars']); ?>
			    			<?= $this->render('_particular', [
						        'model' => $model,
						        'particular' => $particular,
						        'particularCodes' => $particularCodes
						    ]) ?>
				    	<?php Panel::end(); ?>
				    <?php } ?>
			    <?php } ?>
			    <?php if(Yii::$app->user->can('approveBudgetProposal')){ ?>
	    			<?php Panel::begin(['header' => 'Review Budget Proposal']); ?>
		    			<?= $this->render('_approval', [
					        'model' => $model,
					        'incomeModel' => $incomeModel,
            				'branchTransferModel' => $branchTransferModel,
            				'expenseModel' => $expenseModel,
            				'branches' => $branches,
            				'branchPrograms' => $branchPrograms,
					    ]) ?>
			    	<?php Panel::end(); ?>
			    <?php } ?>
			    <?php Panel::begin(['header' => 'Particulars']); ?>
				    <table class="table table-bordered table-responsive table-striped table-hover">
		                <thead>
		                    <tr>
		                        <th>Code</th>
		                        <th>Proposed Date</th>
		                        <th>Particulars</th>
		                        <th>Amount</th>
		                        <th>Date Needed</th>
		                        <th>Approval Status</th>
		                        <th>&nbsp;</th>
		                        <th>&nbsp;</th>
		                    </tr>
		                </thead>
		                <tbody>
		                	<?php if($model->particulars){ ?>
		                		<?php $total = 0; ?>
		                		<?php foreach($model->particulars as $particular){ ?>
		                			<tr class="<?= $particular->approval_status == 'Revisions Needed' ? 'table-danger' : '' ?>">
		                				<td><?= $particular->particularCode->name.' - '.$particular->particularCode->description ?></td>
		                				<td><?= $particular->proposed_date ?></td>
		                				<td><?= $particular->particular ?></td>
		                				<td><?= number_format($particular->amount, 2) ?></td>
		                				<td><?= $particular->date_needed ?></td>
		                				<td><?= $particular->approval_status ?></td>
		                				<td align=center <?= Yii::$app->user->can('approveBudgetProposal') ? 'style="width: 15%"' : 'width=5' ?>><?= ($particular->approval_status == 'For Approval' || $particular->approval_status == 'Revisions Needed') && Yii::$app->user->can('createBudgetProposal') ? Html::a('<i class="glyphicon glyphicon-pencil"></i>',['/accounting/budget-proposal/particular-update', 'id' => $particular->id]) : '' ?>
		                					<?= Yii::$app->user->can('approveBudgetProposal') ? Html::a('<i class="fa fa-thumbs-up"></i> Approve',['/accounting/budget-proposal/particular-approve', 'id' => $particular->id],[
		                					'data' => [
				                                'method' => 'post',
				                            ]]).' |': '' ?>
		                					<?= Yii::$app->user->can('approveBudgetProposal') ? Html::a('<i class="fa fa-thumbs-down"></i> Disapprove',['/accounting/budget-proposal/particular-disapprove', 'id' => $particular->id],[
		                					'data' => [
				                                'method' => 'post',
				                            ]]) : '' ?>
		                				</td>
		                				<td width=5><?= $particular->approval_status == 'For Approval' && Yii::$app->user->can('createBudgetProposal') ? Html::a('<i class="glyphicon glyphicon-trash"></i>',['/accounting/budget-proposal/particular-delete', 'id' => $particular->id],
		                					[
		                					'data' => [
				                                'confirm' => 'Are you sure you want to delete this item?',
				                                'method' => 'post',
				                            ]]) : '' ?>
				                            </td>
		                			</tr>
		                			<?php $total+=$particular->amount; ?>
		                		<?php } ?>
		                		<tr>
		                			<td colspan=3 align=right><b>TOTAL</b></td>
		                			<td colspan=5><b><?= number_format($total, 2) ?></b></td>
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
