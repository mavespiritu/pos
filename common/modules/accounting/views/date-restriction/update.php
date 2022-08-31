<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\DateRestriction */

$this->title = 'Update Date Restriction: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Date Restrictions', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="date-restriction-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
