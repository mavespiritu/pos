<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\modules\pos\models\PosAmountType */

$this->title = 'Update Payment Method';
$this->params['breadcrumbs'][] = ['label' => 'Payment Methods', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="pos-amount-type-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
