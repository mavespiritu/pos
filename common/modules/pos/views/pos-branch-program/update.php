<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\modules\pos\models\PosBranchProgram */

$this->title = 'Update Branch - Program';
$this->params['breadcrumbs'][] = ['label' => 'Branch - Programs', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="pos-branch-program-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'branches' => $branches, 
        'programs' => $programs, 
    ]) ?>

</div>
