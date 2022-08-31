<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\IncomeEnrolment */

$this->title = 'Create Income Enrolment';
$this->params['breadcrumbs'][] = ['label' => 'Income Enrolments', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="income-enrolment-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
