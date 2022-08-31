<?php

use yii\helpers\Html;
use yiister\gentelella\widgets\Panel;
/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\BudgetProposal */

$this->title = 'Update Budget Proposal';
$this->params['breadcrumbs'][] = ['label' => 'Budget Proposals', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="budget-proposal-update">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php Panel::begin(['header' => 'Request Details']); ?>
	    <?= $this->render('_form', [
	        'model' => $model,
	        'branchPrograms' => $branchPrograms,
	        'budgetProposalTypes' => $budgetProposalTypes,
	    ]) ?>
    <?php Panel::end(); ?>

</div>
