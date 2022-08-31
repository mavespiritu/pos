<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\OperatingExpense */

$this->title = 'Update Operating Expense: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Operating Expenses', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="operating-expense-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
