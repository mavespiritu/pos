<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\modules\pos\models\PosBranchProgram */

$this->title = 'Create Branch - Program';
$this->params['breadcrumbs'][] = ['label' => 'Branch - Programs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pos-branch-program-create">

    <?= $this->render('_form', [
        'model' => $model,
        'branches' => $branches, 
        'programs' => $programs, 
    ]) ?>

</div>
