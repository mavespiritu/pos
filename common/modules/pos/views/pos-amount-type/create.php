<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\modules\pos\models\PosAmountType */

$this->title = 'Create Payment Method';
$this->params['breadcrumbs'][] = ['label' => 'Payment Methods', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pos-amount-type-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
