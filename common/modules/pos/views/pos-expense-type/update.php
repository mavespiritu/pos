<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\modules\pos\models\PosExpenseType */

$this->title = 'Update Expense Type';
$this->params['breadcrumbs'][] = ['label' => 'Expense Types', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="pos-expense-type-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
