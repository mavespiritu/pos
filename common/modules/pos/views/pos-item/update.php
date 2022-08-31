<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\modules\pos\models\PosItem */

$this->title = 'Update Item';
$this->params['breadcrumbs'][] = ['label' => 'Items', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="pos-item-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
