<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\TargetEnrolee */

$this->title = 'Create Target Enrolee';
$this->params['breadcrumbs'][] = ['label' => 'Target Enrolees', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="target-enrolee-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
