<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\IncomeCode */

$this->title = 'Update Income Code: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Income Codes', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="income-code-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
