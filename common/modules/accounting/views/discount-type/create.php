<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\DiscountType */

$this->title = 'Create Discount Type';
$this->params['breadcrumbs'][] = ['label' => 'Discount Types', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="discount-type-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
