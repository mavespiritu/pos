<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\BranchProgram */

$this->title = 'Create Branch Program';
$this->params['breadcrumbs'][] = ['label' => 'Branch Programs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="branch-program-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
