<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\modules\pos\models\PosSeason */

$this->title = 'Create Season';
$this->params['breadcrumbs'][] = ['label' => 'Seasons', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pos-season-create">

    <?= $this->render('_form', [
        'model' => $model,
        'branchPrograms' => $branchPrograms,
    ]) ?>

</div>
