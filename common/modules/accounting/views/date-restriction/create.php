<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\DateRestriction */

$this->title = 'Create Date Restriction';
$this->params['breadcrumbs'][] = ['label' => 'Date Restrictions', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="date-restriction-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
