<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yiister\gentelella\widgets\Panel;
use yii\widgets\ActiveForm;
use yii\widgets\MaskedInput;
/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\BudgetProposal */

$this->title = 'Budget Proposal Approval';
$this->params['breadcrumbs'][] = ['label' => 'Budget Proposals', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="budget-proposal-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php Panel::begin(['header' => 'Budget Proposal Approval Form']); ?>
    <p class="pull-right"><?= Html::a('<i class="glyphicon glyphicon-backward"></i> Go Back', ['/accounting/budget-proposal'], ['class' => 'btn btn-primary']) ?></p>

    <div class="row">
        <div class="col-md-6">
            <h3>Request for Budget Details</h3>
            <p>Budget Proposal Income will be added to <?= $model->toBranchProgramName ?> program, while Branch Transfer Expense will be charged to <?= $model->fromBranchProgramName ?> program with the amount of PhP <?= number_format($model->amount, 2) ?>. See the request details below: </p>
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'fromBranchProgramName',
                    'toBranchProgramName',
                    'codeName',
                    'budgetProposalTypeName',
                    'details:ntext',
                    'amount',
                    'amountType',
                    'transactionNumber',
                    'datetime',
                    'approval_status',
                ],
            ]) ?>

            <div class="form-group">
                <div class="row">
                    <div class="col-md-8">
                        <?php $form = ActiveForm::begin(['action' => 'approve']); ?>

                        <?= Html::activeHiddenInput($model, 'id', ['value' => $model->id]) ?>

                        <?= $form->field($model, 'amount')->widget(MaskedInput::classname(), [
                            'clientOptions' => [
                                'alias' =>  'decimal',
                                'autoGroup' => true
                            ],
                        ])->label('Enter Final Amount') ?>

                        <div class="form-group pull-left">
                        <?= Html::submitButton('Approve Request',['class' => 'btn btn-success', 
                            'data' => [
                                    'confirm' => 'Are you sure you want to approve the budget request?'
                                    ]
                            ]) ?>
                        </div>

                        <?php ActiveForm::end(); ?>

                    </div>
                    <div class="col-md-4">
                        <?php $form = ActiveForm::begin(['action' => 'decline']); ?>

                            <?= Html::activeHiddenInput($model, 'id', ['value' => $model->id]) ?>
                            <div class="row">&nbsp;</div>
                            <div class="row">&nbsp;</div>
                            <div class="row">&nbsp;</div>
                            <div style="margin-top: 12px"></div>
                            <div class="form-group pull-right">
                            <?= Html::submitButton('Decline Request',['class' => 'btn btn-danger', 
                                'data' => [
                                    'confirm' => 'Are you sure you want to decline the budget request?'
                                ]]) ?>
                            </div>

                         <?php ActiveForm::end(); ?>
                    </div>
                </div>

            </div>
            
        </div>
    </div>

    <?php Panel::end() ?>
    
</div>
