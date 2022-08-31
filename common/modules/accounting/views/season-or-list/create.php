<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\SeasonOrList */

$this->title = 'Create Season Or List';
$this->params['breadcrumbs'][] = ['label' => 'Season Or Lists', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="season-or-list-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
