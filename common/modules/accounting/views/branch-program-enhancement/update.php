<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\BranchProgramEnhancement */

$this->title = 'Update Branch Program Enhancement: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Branch Program Enhancements', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="branch-program-enhancement-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
