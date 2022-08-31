<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\EnroleeType */

$this->title = 'Create Enrolee Type';
$this->params['breadcrumbs'][] = ['label' => 'Enrolee Types', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="enrolee-type-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
