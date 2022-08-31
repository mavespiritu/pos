<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\PhotocopyExpense */

$this->title = 'Update Photocopy Expense: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Photocopy Expenses', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="photocopy-expense-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
