<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use common\modules\accounting\models\IncomeEnrolment;
use yiister\gentelella\widgets\Panel;
use yii\widgets\Pjax;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
/* @var $this yii\web\View */
/* @var $searchModel common\modules\accounting\models\IncomeEnrolmentSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Enrolments';
$this->params['breadcrumbs'][] = 'Daily Income';
$this->params['breadcrumbs'][] = $this->title;

$user_info = Yii::$app->user->identity->userinfo;
$roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
$rolenames =  ArrayHelper::map($roles, 'name','name');
?>
<div class="income-enrolment-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php Panel::begin(['header' => 'Payments']); ?>
        <?php if(in_array('TopManagement',$rolenames)){ ?>
            
        <?php }else{ ?>
            <?php Panel::begin(['header' => 'Add Payment']) ?>
            <?= $this->render('_form', [
                'model' => $model,
                'incomeModel' => $incomeModel,
                'seasons' => $seasons,
                'incomeCodes' => $incomeCodes,
                'dateRestriction' => $dateRestriction
            ]); ?>
            <?php Panel::end() ?>
        <?php } ?>
        <?php Panel::begin(['header' => 'Payment Records']) ?>     
            <div>
                <i class="fa fa-exclamation-circle"></i> Reports are divided into number of pages depending on the number of records to optimize downloading.</p>
                <span class="pull-right">
                    <?= Html::button('Generate Report',['class' => 'btn btn-success', 'id' => 'income-enrolment-button']) ?>
                </span>
                <span class="clearfix"></span>
            </div>
            <br>
            <div class="row">
                <div class="col-md-4">
                    <p>Overall Total: <span class="pull-right"><b><?= !empty($overall) ? number_format($overall[0]['total'], 2) : number_format(0, 2) ?></b></span></p>
                </div>
                <div class="col-md-4">
                    <p>Cash Total: <span class="pull-right"><b><?= !empty($overallCash) ? number_format($overallCash[0]['total'], 2) : number_format(0, 2) ?></b></span></p>
                </div>
                <div class="col-md-4">
                    <p>Non-Cash Total: <span class="pull-right"><b><?= !empty($overallCheck) ? number_format($overallCheck[0]['total'], 2) : number_format(0, 2) ?></b></span></p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <p>Overall Total (Today): <span class="pull-right"><b><?= !empty($overallToday) ? number_format($overallToday[0]['total'], 2) : number_format(0, 2) ?></b></span></p>
                </div>
                <div class="col-md-4">
                    <p>Cash Total (Today): <span class="pull-right"><b><?= !empty($overallCashToday) ? number_format($overallCashToday[0]['total'], 2) : number_format(0, 2) ?></b></span></p>
                </div>
                <div class="col-md-4">
                    <p>Non-Cash Total (Today): <span class="pull-right"><b><?= !empty($overallCheckToday) ? number_format($overallCheckToday[0]['total'], 2) : number_format(0, 2) ?></b></span></p>
                </div>
            </div>
            <?php Pjax::begin() ?>
            <?php if(in_array('TopManagement',$rolenames)){ ?>
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'showFooter' => true,
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        [
                            'format' => 'raw',
                            'value' => function($model){
                                $content = '';

                                if(Yii::$app->user->can('deleteIncomeEnrolment'))
                                {
                                    $content.= Html::a('<i class="glyphicon glyphicon-trash"></i>',['/accounting/income-enrolment/delete', 'id' => $model->id],['data' => ['method' => 'post','confirm' => 'Are you sure you want to delete this item?']]);
                                }

                                return $content;
                            }
                        ],
                        'seasonName',
                        'studentName',
                        'or_no',
                        'ar_no',
                        'codeName',
                        [
                            'attribute' => 'amount',
                            'footer' => IncomeEnrolment::getTotal($dataProvider->models, 'amount'),       
                        ],
                        'amountType',
                        'transactionNumber',
                        'datetime',
                    ],
                ]); ?>
            <?php }else{ ?>
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'showFooter' => true,
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        [
                            'format' => 'raw',
                            'value' => function($model) use ($dateRestriction){
                                $content = '';

                                if(Yii::$app->user->can('updateIncomeEnrolment'))
                                {
                                    if(date("Y-m-d", strtotime($model->datetime)) == date("Y-m-d"))
                                    {
                                        $content.= Html::a('<i class="glyphicon glyphicon-pencil"></i>',['/accounting/income-enrolment/update', 'id' => $model->id],['data' => ['method' => 'post']]);
                                    }else{
                                        if($dateRestriction){
                                            if($dateRestriction->allow == "Yes" && $dateRestriction->open_type == "Income"){
                                                if((date("Y-m-d", strtotime($model->datetime)) >= $dateRestriction->start_date) && (date("Y-m-d", strtotime($model->datetime)) <= $dateRestriction->end_date))
                                                {
                                                    $content.= Html::a('<i class="glyphicon glyphicon-pencil"></i>',['/accounting/income-enrolment/update', 'id' => $model->id],['data' => ['method' => 'post']]);
                                                }
                                            }
                                        }
                                    }
                                }

                                if(Yii::$app->user->can('deleteIncomeEnrolment'))
                                {
                                    if(date("Y-m-d", strtotime($model->datetime)) == date("Y-m-d"))
                                    {
                                        $content.= Html::a('<i class="glyphicon glyphicon-trash"></i>',['/accounting/income-enrolment/delete', 'id' => $model->id],['data' => ['method' => 'post','confirm' => 'Are you sure you want to delete this item?']]);
                                    }
                                }

                                return $content;
                            }
                        ],
                        'seasonName',
                        'studentName',
                        'or_no',
                        'ar_no',
                        'codeName',
                        [
                            'attribute' => 'amount',
                            'footer' => IncomeEnrolment::getTotal($dataProvider->models, 'amount'),       
                        ],
                        'amountType',
                        'transactionNumber',
                        'datetime',
                    ],
                ]); ?>
            <?php } ?>
            <?php Pjax::end(); ?>
            <?php Panel::end(); ?>
        <?php Panel::end() ?> 
    </div>
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
            $("#income-enrolment-button").click(function(){
                var url = "'.Url::to(['/accounting/income-enrolment/search']).'";
                $.post( url, function( data ) {
                  $( "#modalContents").html( data );
                });  

                $("#modal").modal("show");
            });
        });
';
$this->registerJs($script);