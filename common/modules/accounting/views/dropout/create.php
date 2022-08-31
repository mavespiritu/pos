<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\Dropout */

$this->title = 'Create Dropout';
$this->params['breadcrumbs'][] = ['label' => 'Dropouts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="dropout-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
