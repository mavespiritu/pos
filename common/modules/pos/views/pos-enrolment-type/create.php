<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\modules\pos\models\PosEnrolmentType */

$this->title = 'Create Enrolment Type';
$this->params['breadcrumbs'][] = ['label' => 'Enrolment Types', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pos-enrolment-type-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
