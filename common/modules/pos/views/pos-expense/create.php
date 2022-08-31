<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\modules\pos\models\PosExpense */

$this->title = 'Create Expense';
$this->params['breadcrumbs'][] = ['label' => 'Expenses', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pos-expense-create">

    <?= $this->render('_form', [
        'model' => $model,
        'seasons' => $seasons,
        'backtrack' => $backtrack,
        'accounts' => $accounts,
        'vendors' => $vendors,
        'amountTypes' => $amountTypes,
    ]) ?>

</div>
