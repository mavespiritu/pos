<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yiister\gentelella\widgets\Panel;
/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\Package */

$this->title = $model->code;
$this->params['breadcrumbs'][] = ['label' => 'Packages', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="package-view">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php Panel::begin(['header' => 'Package Information']); ?>
    <h3>Package
        <span class="pull-right">
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
        </span>
    </h3>
    <br>
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'branchName',
            'packageTypeName',
            'tier',
            'code',
            'amount',
        ],
    ]) ?>
    <h3>Freebies</h3>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Freebie</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
        <?php if($model->packageFreebies){ ?>
            <?php foreach($model->packageFreebies as $freebie){ ?>
                <tr>
                    <td><?= $freebie->freebie->name ?></td>
                    <td><?= number_format($freebie->amount, 2) ?></td>
                </tr>
            <?php } ?>
        <?php } ?>
        </tbody> 
    </table>
    <?php Panel::end(); ?>
</div>
