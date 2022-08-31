<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\PhotocopyExpense */

$this->title = 'Create Photocopy Expense';
$this->params['breadcrumbs'][] = ['label' => 'Photocopy Expenses', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="photocopy-expense-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
