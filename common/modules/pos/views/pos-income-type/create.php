<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\modules\pos\models\PosIncomeType */

$this->title = 'Create Income Type';
$this->params['breadcrumbs'][] = ['label' => 'Income Types', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pos-income-type-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
