<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\modules\pos\models\PosProduct */

$this->title = 'Create Product';
$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pos-product-create">

    <?= $this->render('_form', [
        'model' => $model,
        'seasons' => $seasons,
        'incomeTypes' => $incomeTypes,
        'productTypes' => $productTypes,
    ]) ?>

</div>
