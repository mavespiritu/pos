<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\Denomination */

$this->title = 'Create Denomination';
$this->params['breadcrumbs'][] = ['label' => 'Denominations', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="denomination-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
