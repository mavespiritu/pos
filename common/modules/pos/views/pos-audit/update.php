<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\modules\pos\models\PosAudit */

$this->title = 'Update Pos Audit: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Pos Audits', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="pos-audit-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
