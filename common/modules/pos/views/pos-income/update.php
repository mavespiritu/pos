<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\modules\pos\models\PosIncome */

$this->title = 'Update Income';
$this->params['breadcrumbs'][] = ['label' => 'Income', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="pos-income-update">

    <?= $this->render('_form', [
        'model' => $model,
        'incomeItemModel' => $incomeItemModel,
        'backtrack' => $backtrack,
        'seasons' => $seasons,
        'customers' => $customers,
        'products' => $products,
        'amountTypes' => $amountTypes,
        'accounts' => $accounts,
    ]) ?>

</div>
