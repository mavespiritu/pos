<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\PackageType */

$this->title = 'Create Package Type';
$this->params['breadcrumbs'][] = ['label' => 'Package Types', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="package-type-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
