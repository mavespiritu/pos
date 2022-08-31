<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use common\modules\accounting\models\BudgetProposal;
use yiister\gentelella\widgets\Panel;
use yii\helpers\ArrayHelper;
/* @var $this yii\web\View */
/* @var $searchModel common\modules\accounting\models\BudgetProposalSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Budget Proposals';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="budget-proposal-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php Panel::begin(['header' => 'Requests']); ?>
    <div class="row">
        <div class="col-md-12">
            <?php Pjax::begin() ?>
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'showFooter' => true,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],

                    'branchName',
                    'branchProgramName',
                    'codeName',
                    'budgetProposalTypeName',
                    'requestedAmount',
                    'approvedAmount',
                    'datetime',
                    'approval_status',

                    [
                        'format' => 'raw',
                        'value' => function($model){
                            $content = '';

                            $user_info = Yii::$app->user->identity->userinfo;
                            $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
                            $rolenames =  ArrayHelper::map($roles, 'name','name');

                            if($model->approval_status == 'For Approval'){
                                if(in_array('TopManagement',$rolenames)){
                                    $content.= Html::a('<i class="glyphicon glyphicon-eye-open"></i>',['/accounting/budget-proposal/particular', 'id' => $model->id]);
                                }else if(in_array('AreaManager',$rolenames)){
                                    $content.= Html::a('<i class="glyphicon glyphicon-eye-open"></i>',['/accounting/budget-proposal/particular', 'id' => $model->id]);
                                    $content.= Html::a('<i class="glyphicon glyphicon-pencil"></i>',['/accounting/budget-proposal/update', 'id' => $model->id],[
                                    'data' => [
                                            'method' => 'post',
                                        ]]);
                                    $content.= Html::a('<i class="glyphicon glyphicon-trash"></i>',['/accounting/budget-proposal/delete', 'id' => $model->id],[
                                        'data' => [
                                                'confirm' => 'Are you sure you want to delete this item?',
                                                'method' => 'post',
                                            ]
                                    ]);
                                }else if(in_array('AccountingStaff',$rolenames)){
                                    $content.= Html::a('<i class="glyphicon glyphicon-eye-open"></i>',['/accounting/budget-proposal/particular', 'id' => $model->id]);
                                    $content.= Html::a('<i class="glyphicon glyphicon-pencil"></i>',['/accounting/budget-proposal/update', 'id' => $model->id],[
                                    'data' => [
                                            'method' => 'post',
                                        ]]);
                                    $content.= Html::a('<i class="glyphicon glyphicon-trash"></i>',['/accounting/budget-proposal/delete', 'id' => $model->id],[
                                        'data' => [
                                                'confirm' => 'Are you sure you want to delete this item?',
                                                'method' => 'post',
                                            ]
                                    ]);
                                }
                            }else{
                                if(in_array('TopManagement',$rolenames)){
                                    $content.= Html::a('<i class="glyphicon glyphicon-eye-open"></i>',['/accounting/budget-proposal/particular', 'id' => $model->id]);
                                }else if(in_array('AreaManager',$rolenames)){
                                    $content.= Html::a('<i class="glyphicon glyphicon-eye-open"></i>',['/accounting/budget-proposal/particular', 'id' => $model->id]);
                                }else if(in_array('AccountingStaff',$rolenames)){
                                    $content.= Html::a('<i class="glyphicon glyphicon-eye-open"></i>',['/accounting/budget-proposal/particular', 'id' => $model->id]);
                                }
                            }

                            return $content;
                        }
                    ],
                ],
            ]); ?>
            <?php Pjax::end() ?>
        </div>
    </div>
</div>
