<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\modules\pos\models\PosEnrolment */

$this->title = 'Update Enrolment';
$this->params['breadcrumbs'][] = ['label' => 'Enrolment', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="pos-enrolment-update">

    <?= $this->render('_form', [
        'model' => $model,
        'discountModel' => $discountModel,
        'seasons' => $seasons,
        'products' => $products,
        'enrolmentTypes' => $enrolmentTypes,
        'discountTypes' => $discountTypes,
    ]) ?>

</div>
