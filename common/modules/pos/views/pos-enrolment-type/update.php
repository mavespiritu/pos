<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\modules\pos\models\PosEnrolmentType */

$this->title = 'Update Enrolment Type';
$this->params['breadcrumbs'][] = ['label' => 'Enrolment Types', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="pos-enrolment-type-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
