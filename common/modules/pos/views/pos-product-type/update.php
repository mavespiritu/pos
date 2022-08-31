<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\modules\pos\models\PosProductType */

$this->title = 'Update Product Type';
$this->params['breadcrumbs'][] = ['label' => 'Product Types', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="pos-product-type-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
