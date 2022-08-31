<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\modules\pos\models\PosCustomer */

$this->title = 'Update Customer';
$this->params['breadcrumbs'][] = ['label' => 'Customers', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="pos-customer-update">

    <?= $this->render('_form', [
        'model' => $model,
        'provinces' => $provinces,
        'citymuns' => $citymuns,
    ]) ?>

</div>
