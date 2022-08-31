<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\modules\pos\models\PosProduct */

$this->title = 'Update Product';
$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="pos-product-update">

    <?= $this->render('_form', [
        'model' => $model,
        'seasons' => $seasons,
        'incomeTypes' => $incomeTypes,
        'productTypes' => $productTypes,
    ]) ?>

</div>
