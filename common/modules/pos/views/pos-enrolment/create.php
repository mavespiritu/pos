<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\modules\pos\models\PosEnrolment */

$this->title = 'Create Enrolment';
$this->params['breadcrumbs'][] = ['label' => 'Enrolment', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pos-enrolment-create">

    <?= $this->render('_form', [
        'model' => $model,
        'discountModel' => $discountModel,
        'seasons' => $seasons,
        'products' => $products,
        'enrolmentTypes' => $enrolmentTypes,
        'discountTypes' => $discountTypes,
    ]) ?>

</div>
