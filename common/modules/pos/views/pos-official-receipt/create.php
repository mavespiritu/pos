<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\modules\pos\models\PosOfficialReceipt */

$this->title = 'Create Official Receipt';
$this->params['breadcrumbs'][] = ['label' => 'Official Receipts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pos-official-receipt-create">

    <?= $this->render('_form', [
        'model' => $model,
        'branchPrograms' => $branchPrograms,
        'seasons' => $seasons,
    ]) ?>

</div>
