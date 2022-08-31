<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\modules\pos\models\PosVendor */

$this->title = 'Create Vendor';
$this->params['breadcrumbs'][] = ['label' => 'Vendors', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pos-vendor-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
