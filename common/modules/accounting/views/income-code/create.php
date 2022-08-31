<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\IncomeCode */

$this->title = 'Create Income Code';
$this->params['breadcrumbs'][] = ['label' => 'Income Codes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="income-code-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
