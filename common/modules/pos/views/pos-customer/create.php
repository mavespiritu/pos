<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\modules\pos\models\PosCustomer */

$this->title = 'Create Customer';
$this->params['breadcrumbs'][] = ['label' => 'Customers', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pos-customer-create">

    <?= $this->render('_form', [
        'model' => $model,
        'provinces' => $provinces,
        'citymuns' => $citymuns,
    ]) ?>

</div>
