<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yiister\gentelella\widgets\Panel;
use yii\bootstrap\ButtonDropdown;
use yii\bootstrap\Modal;
use yii\helpers\Url;
/* @var $this yii\web\View */
/* @var $searchModel common\modules\accounting\models\DenominationSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Students';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="denomination-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?php Panel::begin(['header' => 'Student List']); ?>
    <p><i class="fa fa-exclamation-circle"></i> Reports are divided into number of pages depending on the number of records to optimize downloading.</p>
    <span class="pull-right">
        <?= Html::button('Generate Student Information',['class' => 'btn btn-success', 'id' => 'student-information-button']) ?>
        <?= Html::button('Generate Payment Updates',['class' => 'btn btn-success', 'id' => 'payment-updates-button']) ?>
    </span>
    <span class="clearfix"></span>

    <?php /*$this->render('_search',[
        'model' => $model,
        'enroleeTypeModel' => $enroleeTypeModel,
        'seasons' => $seasons,
    ])*/ ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'format' => 'raw',
                'value' => function($model){
                    $data = '';
                    if(Yii::$app->user->can('viewStudent')){
                        $data.=Html::a('<i class="glyphicon glyphicon-eye-open"></i>', ['/accounting/student/view','id' => $model->id]);
                    }
                    if(Yii::$app->user->can('updateStudent')){
                        $data.=Html::a('<i class="glyphicon glyphicon-pencil"></i>', ['/accounting/student/update','id' => $model->id]);
                    }
                    if(Yii::$app->user->can('deleteStudent')){
                        $data.=Html::a('<i class="glyphicon glyphicon-trash"></i>', ['/accounting/student/delete','id' => $model->id],[
                            'data' => [
                                'confirm' => 'Are you sure you want to delete this item?',
                                'method' => 'post',
                            ]]);
                    }

                    return $data;  
                }
            ],

            'id_number',
            'fullName',
            'provinceName',
            'citymunName',
            'permanent_address',
            'schoolName',
            'year_graduated',
            'contact_no',
            'email_address',
        ],
    ]); ?>

    <?php Panel::end(); ?>
</div>
<div class="modal fade" id="modal2" role="dialog">
    <div class="modal-dialog ">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4>Generate Student Information</h4>
            </div>
            <div class="modal-body">
                <div  id='modalContents2'></div>
                
            </div>
        </div>
    </div>
</div> 
<div class="modal fade" id="modal3" role="dialog">
    <div class="modal-dialog ">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4>Generate Payments Update</h4>
            </div>
            <div class="modal-body">
                <div  id='modalContents3'></div>
                
            </div>
        </div>
    </div>
</div> 
<?php
    $script = '
        $( document ).ready(function() {
            $("#student-information-button").click(function(){
                var url = "'.Url::to(['/accounting/student/search-student-information']).'";
                $.post( url, function( data ) {
                  $("#modalContents2").html( data );
                });  

                $("#modal2").modal("show");
            });
            $("#payment-updates-button").click(function(){
                var url = "'.Url::to(['/accounting/student/search-payment-updates']).'";
                $.post( url, function( data ) {
                  $("#modalContents3").html( data );
                });  

                $("#modal3").modal("show");
            });
        });
';
$this->registerJs($script);

   
