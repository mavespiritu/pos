<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\Student */

$this->title = 'Update Student Information';
$this->params['breadcrumbs'][] = ['label' => 'Students', 'url' => ['list']];
$this->params['breadcrumbs'][] = 'Update Student Information';
?>
<div class="student-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_update', [
        'model' => $model,
        'provinces' => $provinces,
        'citymuns' => $citymuns,
    ]) ?>

</div>
