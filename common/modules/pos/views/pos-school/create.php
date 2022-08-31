<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\modules\pos\models\PosSchool */

$this->title = 'Create School';
$this->params['breadcrumbs'][] = ['label' => 'Schools', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pos-school-create">

    <?= $this->render('_form', [
        'model' => $model,
        'branches' => $branches,
    ]) ?>

</div>
