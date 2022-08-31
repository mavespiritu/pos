<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\modules\pos\models\PosVendor */

$this->title = 'Update Vendor';
$this->params['breadcrumbs'][] = ['label' => 'Vendors', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="pos-vendor-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
