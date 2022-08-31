<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\OperatingExpense */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Operating Expenses', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="operating-expense-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'cv_no',
            'particulars:ntext',
            'staff_salary',
            'cash_pf',
            'rent',
            'utilities',
            'equipment_and_labor',
            'bir_and_docs',
            'marketing',
        ],
    ]) ?>

</div>
