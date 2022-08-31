<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\FreebieAndIcon */

$this->title = 'Create Freebie And Icon';
$this->params['breadcrumbs'][] = ['label' => 'Freebie And Icons', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="freebie-and-icon-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
