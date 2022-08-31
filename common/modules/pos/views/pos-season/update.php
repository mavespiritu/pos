<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\modules\pos\models\PosSeason */

$this->title = 'Update Season';
$this->params['breadcrumbs'][] = ['label' => 'Seasons', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="pos-season-update">

    <?= $this->render('_form', [
        'model' => $model,
        'branchPrograms' => $branchPrograms,
    ]) ?>

</div>
