<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\modules\pos\models\PosAudit */

$this->title = 'Create Pos Audit';
$this->params['breadcrumbs'][] = ['label' => 'Pos Audits', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pos-audit-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
