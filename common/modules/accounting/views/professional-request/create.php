<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\ProfessionalRequest */

$this->title = 'Create Professional Request';
$this->params['breadcrumbs'][] = ['label' => 'Professional Requests', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="professional-request-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
