<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use common\modules\accounting\models\BranchTransfer;
use yiister\gentelella\widgets\Panel;
use yii\helpers\Url;
/* @var $this yii\web\View */
/* @var $searchModel common\modules\accounting\models\BranchTransferSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Branch Transfers';
$this->params['breadcrumbs'][] = 'Daily Expenses';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="branch-transfer-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php Panel::begin(['header' => 'Branch Transfers']); ?>
    <div>
        <i class="fa fa-exclamation-circle"></i> Reports are divided into number of pages depending on the number of records to optimize downloading.</p>
        <span class="pull-right">
            <?= Html::button('Generate Report',['class' => 'btn btn-success', 'id' => 'branch-transfer-button']) ?>
        </span>
        <span class="clearfix"></span>
    </div>
    <br>
    <?php Pjax::begin() ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'showFooter' => true,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'branchName',
            'branchProgramName',
            [
                'attribute' => 'approvedAmount',
                'footer' => BranchTransfer::getTotal($dataProvider->models, 'approvedAmount'),       
            ],
            'amount_source',
            'datetime',
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view}'
            ],
        ],
    ]); ?>
    <?php Pjax::end() ?>
    <?php Panel::end() ?>
</div>
<div class="modal fade" id="modal" role="dialog">
    <div class="modal-dialog ">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4>Generate Report</h4>
            </div>
            <div class="modal-body">
                <div  id='modalContents'></div>
                
            </div>
        </div>
    </div>
</div> 
<?php
    $script = '
        $( document ).ready(function() {
            $("#branch-transfer-button").click(function(){
                var url = "'.Url::to(['/accounting/branch-transfer/search']).'";
                $.post( url, function( data ) {
                  $( "#modalContents").html( data );
                });  

                $("#modal").modal("show");
            });
        });
';
$this->registerJs($script);