<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\modules\pos\models\PosIncomeType */

$this->title = 'Update Income Type';
$this->params['breadcrumbs'][] = ['label' => 'Income Types', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="pos-income-type-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
