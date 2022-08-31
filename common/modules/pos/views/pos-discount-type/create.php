<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\modules\pos\models\PosDiscountType */

$this->title = 'Create Discount Type';
$this->params['breadcrumbs'][] = ['label' => 'Discount Types', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pos-discount-type-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
