<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\Enhancement */

$this->title = 'Create Enhancement';
$this->params['breadcrumbs'][] = ['label' => 'Enhancements', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="enhancement-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
