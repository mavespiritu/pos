<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\modules\pos\models\PosAccount */

$this->title = 'Update Account';
$this->params['breadcrumbs'][] = ['label' => 'Accounts', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="pos-account-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
