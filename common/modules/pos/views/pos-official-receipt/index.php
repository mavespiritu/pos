<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use kartik\select2\Select2;
/* @var $this yii\web\View */
/* @var $searchModel common\modules\pos\models\PosOfficialReceiptSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Official Receipts';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pos-official-receipt-index">

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Official Receipt', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'seasonName',
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'season_id',
                    'data' => $seasons,
                    'options' => ['placeholder' => 'Select One'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]),
            ],
            'start_number',
            'last_number',
            'date_filed',

            ['class' => 'yii\grid\ActionColumn', 'template' => '{update}{delete}'],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>
