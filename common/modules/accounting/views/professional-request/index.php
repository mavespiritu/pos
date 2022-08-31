<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yiister\gentelella\widgets\Panel;
use yii\helpers\ArrayHelper;
/* @var $this yii\web\View */
/* @var $searchModel common\modules\accounting\models\ProfessionalRequestSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
$user_info = Yii::$app->user->identity->userinfo;
$roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
$rolenames =  ArrayHelper::map($roles, 'name','name');

$this->title = in_array('TopManagement',$rolenames)? 'Prof Requests' : 'My Requests';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="professional-request-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php Panel::begin(['header' => 'Requests']); ?>
        <div class="row">
        <?php if(in_array('TopManagement',$rolenames)){ ?>
            <div class="col-md-12">
        <?php }else{ ?>
            <div class="col-md-3">
                <?php Panel::begin(['header' => 'Add Request']) ?>
                <?= $this->render('_form', [
                    'model' => $model,
                ]); ?>
                <?php Panel::end() ?>
            </div>
            <div class="col-md-9">
        <?php } ?>
            <h2>Request List</h2>
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'showFooter' => true,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    'requester',
                    'start_date',
                    'end_date',
                    'period_covered',
                    'bank',
                    'account_name',
                    'account_number',
                    'approval_status',
                    'datetime',
                    [
                        'format' => 'raw',
                        'value' => function($model) use ($rolenames){
                            $content = Html::a('<i class="glyphicon glyphicon-eye-open"></i>',['/accounting/professional-request/view', 'id' => $model->id]);

                            if($model->approval_status!='Approved' && in_array('Professional',$rolenames))
                            {
                                $content.= Html::a('<i class="glyphicon glyphicon-pencil"></i>',['/accounting/professional-request/update', 'id' => $model->id]);
                                $content.= Html::a('<i class="glyphicon glyphicon-trash"></i>',['/accounting/professional-request/delete', 'id' => $model->id],['data' => ['method' => 'post','confirm' => 'Are you sure you want to delete this item?']]);
                            }

                            return $content;
                        }
                    ],
                ],
            ]); ?>
        </div>
    <?php Panel::end(); ?>
</div>
