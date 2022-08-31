<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\modules\pos\models\PosBacktrack */

$this->title = 'Update Backtrack';
$this->params['breadcrumbs'][] = ['label' => 'Backtrack', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="pos-backtrack-update">

    <?= $this->render('_form', [
        'model' => $model,
        'branches' => $branches,
    ]) ?>

</div>
