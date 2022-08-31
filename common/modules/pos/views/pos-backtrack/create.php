<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\modules\pos\models\PosBacktrack */

$this->title = 'Create Backtrack';
$this->params['breadcrumbs'][] = ['label' => 'Backtrack', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pos-backtrack-create">

    <?= $this->render('_form', [
        'model' => $model,
        'branches' => $branches,
    ]) ?>

</div>
