<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\Transferee */

$this->title = 'Create Transferee';
$this->params['breadcrumbs'][] = ['label' => 'Transferees', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="transferee-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
