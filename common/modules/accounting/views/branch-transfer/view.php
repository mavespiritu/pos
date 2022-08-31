<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yiister\gentelella\widgets\Panel;
/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\BranchTransfer */

$this->title = 'Branch Transfer Details';
$this->params['breadcrumbs'][] = ['label' => 'Branch Transfers', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="branch-transfer-view">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php Panel::begin(['header' => 'Branch Transfer Details']); ?>
    <p class="pull-right"><?= Html::a('<i class="glyphicon glyphicon-backward"></i> Get Back', ['/accounting/branch-transfer'], ['class' => 'btn btn-primary']) ?></p>
    <br>
    <br>
    <p>This branch transfer is used to fund budget proposal from <?= $budgetProposal->branchName != '' ? $budgetProposal->branchName.' Branch' : $budgetProposal->branchProgramName.' Branch Program' ?>. See more details below: </p>
    <h3>Branch Transfer Details</h3>
    <div class="row">
        <div class="col-md-6">
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'branchName',
                    'branchProgramName',
                    'approvedAmount',
                    'amount_source',
                    'datetime',
                ],
            ]) ?>

            <h3>Budget Proposal Details</h3>
            <?= DetailView::widget([
                'model' => $budgetProposal,
                'attributes' => [
                    'branchName',
                    'branchProgramName',
                    'codeName',
                    'budgetProposalTypeName',
                    'approvedAmount',
                ],
            ]) ?>
        </div>
        <div class="col-md-6">
            
        </div>
    </div>
    <p>This budget proposal has been approved by the management.</p>
    <?php Panel::end(); ?>

</div>
