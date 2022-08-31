<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\modules\pos\models\PosOfficialReceipt */

$this->title = 'Update Official Receipt';
$this->params['breadcrumbs'][] = ['label' => 'Official Receipts', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="pos-official-receipt-update">

    <?= $this->render('_form', [
        'model' => $model,
        'branchPrograms' => $branchPrograms,
        'seasons' => $seasons,
    ]) ?>

</div>
