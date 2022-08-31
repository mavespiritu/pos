<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\modules\pos\models\PosDiscountType */

$this->title = 'Update Discount Type';
$this->params['breadcrumbs'][] = ['label' => 'Discount Types', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="pos-discount-type-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
