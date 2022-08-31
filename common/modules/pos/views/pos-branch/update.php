<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\modules\pos\models\PosBranch */

$this->title = 'Update Branch';
$this->params['breadcrumbs'][] = ['label' => 'Branches', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="pos-branch-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
