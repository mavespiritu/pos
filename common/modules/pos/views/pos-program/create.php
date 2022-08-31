<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\modules\pos\models\PosProgram */

$this->title = 'Create Program';
$this->params['breadcrumbs'][] = ['label' => 'Programs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pos-program-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
