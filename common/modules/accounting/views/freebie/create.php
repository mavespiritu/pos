<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\Freebie */

$this->title = 'Create Freebie';
$this->params['breadcrumbs'][] = ['label' => 'Freebies', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="freebie-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
