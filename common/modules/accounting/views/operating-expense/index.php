<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yiister\gentelella\widgets\Panel;
use common\modules\accounting\models\OperatingExpense;
use yii\widgets\Pjax;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
/* @var $this yii\web\View */
/* @var $searchModel common\modules\accounting\models\OperatingExpenseSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Operating Expenses';
$this->params['breadcrumbs'][] = 'Daily Expenses';
$this->params['breadcrumbs'][] = $this->title;

$user_info = Yii::$app->user->identity->userinfo;
$roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
$rolenames =  ArrayHelper::map($roles, 'name','name');
?>
<div class="operating-expense-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php Panel::begin(['header' => 'Expense Details']); ?>
    
        <?php if(in_array('TopManagement',$rolenames)){ ?>
            
        <?php }else{ ?>
            
                <?php Panel::begin(['header' => 'Add Payment']) ?>
                <?= $this->render('_form', [
                    'model' => $model,
                    'seasons' => $seasons,
                    'expenseModel' => $expenseModel,
                    'dateRestriction' => $dateRestriction
                ]); ?>
                <?php Panel::end() ?>
            
        <?php } ?>
            <?php Panel::begin(['header' => 'Expense Records']); ?>
            <div>
                <i class="fa fa-exclamation-circle"></i> Reports are divided into number of pages depending on the number of records to optimize downloading.</p>
                <span class="pull-right">
                    <?= Html::button('Generate Report',['class' => 'btn btn-success', 'id' => 'operating-expense-button']) ?>
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
                    'tableOptions' => ['class' => 'table table-striped table-condensed table-hover'],
                    'showFooter' => true,
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        [
                            'format' => 'raw',
                            'value' => function($model){
                                $content = '';

                                if(Yii::$app->user->can('deleteOperatingExpense'))
                                {
                                    $content.= Html::a('<i class="glyphicon glyphicon-trash"></i>',['/accounting/operating-expense/delete', 'id' => $model->id],['data' => ['method' => 'post','confirm' => 'Are you sure you want to delete this item?']]);
                                }

                                return $content;
                            }
                        ],
                        'seasonName',
                        'cv_no',
                        'particulars:ntext',
                        [
                            'attribute' => 'staff_salary',
                            'footer' => OperatingExpense::getTotal($dataProvider->models, 'staff_salary'),       
                        ],
                        [
                            'attribute' => 'cash_pf',
                            'footer' => OperatingExpense::getTotal($dataProvider->models, 'cash_pf'),       
                        ],
                        [
                            'attribute' => 'rent',
                            'footer' => OperatingExpense::getTotal($dataProvider->models, 'rent'),       
                        ],
                        [
                            'attribute' => 'utilities',
                            'footer' => OperatingExpense::getTotal($dataProvider->models, 'utilities'),       
                        ],
                        [
                            'attribute' => 'equipment_and_labor',
                            'footer' => OperatingExpense::getTotal($dataProvider->models, 'equipment_and_labor'),       
                        ],
                        [
                            'attribute' => 'bir_and_docs',
                            'footer' => OperatingExpense::getTotal($dataProvider->models, 'bir_and_docs'),       
                        ],
                        [
                            'attribute' => 'marketing',
                            'footer' => OperatingExpense::getTotal($dataProvider->models, 'marketing'),       
                        ],
                        'charge_to',
                        'datetime',
                        
                    ],
                ]); ?>  
            <?php }else{ ?>
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'tableOptions' => ['class' => 'table table-striped table-condensed table-hover'],
                    'showFooter' => true,
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        [
                            'format' => 'raw',
                            'value' => function($model) use ($dateRestriction){
                                $content = '';

                                if(Yii::$app->user->can('updateOperatingExpense'))
                                {
                                    if(date("Y-m-d", strtotime($model->datetime)) == date("Y-m-d"))
                                    {
                                        $content.= Html::a('<i class="glyphicon glyphicon-pencil"></i>',['/accounting/operating-expense/update', 'id' => $model->id],['data' => ['method' => 'post']]);
                                    }else{
                                        if($dateRestriction){
                                            if($dateRestriction->allow == "Yes" && $dateRestriction->open_type == "Expenses"){
                                                if((date("Y-m-d", strtotime($model->datetime)) >= $dateRestriction->start_date) && (date("Y-m-d", strtotime($model->datetime)) <= $dateRestriction->end_date))
                                                {
                                                    $content.= Html::a('<i class="glyphicon glyphicon-pencil"></i>',['/accounting/operating-expense/update', 'id' => $model->id],['data' => ['method' => 'post']]);
                                                }
                                            }
                                        }
                                    }
                                }

                                if(Yii::$app->user->can('deleteOperatingExpense'))
                                {
                                    if(date("Y-m-d", strtotime($model->datetime)) == date("Y-m-d"))
                                    {
                                        $content.= Html::a('<i class="glyphicon glyphicon-trash"></i>',['/accounting/operating-expense/delete', 'id' => $model->id],['data' => ['method' => 'post','confirm' => 'Are you sure you want to delete this item?']]);
                                    }else{
                                        if($dateRestriction){
                                            if($dateRestriction->allow == "Yes" && $dateRestriction->open_type == "Expenses"){
                                                if((date("Y-m-d", strtotime($model->datetime)) >= $dateRestriction->start_date) && (date("Y-m-d", strtotime($model->datetime)) <= $dateRestriction->end_date))
                                                {
                                                    $content.= Html::a('<i class="glyphicon glyphicon-trash"></i>',['/accounting/operating-expense/delete', 'id' => $model->id],['data' => ['method' => 'post','confirm' => 'Are you sure you want to delete this item?']]);
                                                }
                                            }
                                        }
                                    }
                                }

                                return $content;
                            }
                        ],
                        'seasonName',
                        'cv_no',
                        'particulars:ntext',
                        [
                            'attribute' => 'staff_salary',
                            'footer' => OperatingExpense::getTotal($dataProvider->models, 'staff_salary'),       
                        ],
                        [
                            'attribute' => 'cash_pf',
                            'footer' => OperatingExpense::getTotal($dataProvider->models, 'cash_pf'),       
                        ],
                        [
                            'attribute' => 'rent',
                            'footer' => OperatingExpense::getTotal($dataProvider->models, 'rent'),       
                        ],
                        [
                            'attribute' => 'utilities',
                            'footer' => OperatingExpense::getTotal($dataProvider->models, 'utilities'),       
                        ],
                        [
                            'attribute' => 'equipment_and_labor',
                            'footer' => OperatingExpense::getTotal($dataProvider->models, 'equipment_and_labor'),       
                        ],
                        [
                            'attribute' => 'bir_and_docs',
                            'footer' => OperatingExpense::getTotal($dataProvider->models, 'bir_and_docs'),       
                        ],
                        [
                            'attribute' => 'marketing',
                            'footer' => OperatingExpense::getTotal($dataProvider->models, 'marketing'),       
                        ],
                        'charge_to',
                        'datetime',
                        
                    ],
                ]); ?>  
            <?php } ?>
            <?php Pjax::end(); ?>
            <?php Panel::end(); ?>
            <?php Panel::end(); ?>
        </div>
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
            $("#operating-expense-button").click(function(){
                var url = "'.Url::to(['/accounting/operating-expense/search']).'";
                $.post( url, function( data ) {
                  $( "#modalContents").html( data );
                });  

                $("#modal").modal("show");
            });
        });
';
$this->registerJs($script);