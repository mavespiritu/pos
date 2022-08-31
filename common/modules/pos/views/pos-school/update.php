<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\modules\pos\models\PosSchool */

$this->title = 'Update School';
$this->params['breadcrumbs'][] = ['label' => 'Schools', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="pos-school-update">

    <?= $this->render('_form', [
        'model' => $model,
        'branches' => $branches,
    ]) ?>

</div>
