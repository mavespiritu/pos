<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\BranchProgramEnhancement */

$this->title = 'Create Branch Program Enhancement';
$this->params['breadcrumbs'][] = ['label' => 'Branch Program Enhancements', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="branch-program-enhancement-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
