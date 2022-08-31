<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\modules\pos\models\PosProductType */

$this->title = 'Create Product Type';
$this->params['breadcrumbs'][] = ['label' => 'Product Types', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pos-product-type-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
