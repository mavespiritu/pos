<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\modules\pos\models\PosExpenseType */

$this->title = 'Create Expense Type';
$this->params['breadcrumbs'][] = ['label' => 'Expense Types', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pos-expense-type-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
