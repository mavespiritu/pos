<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;
use yiister\gentelella\widgets\Panel;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\widgets\MaskedInput;
use dosamigos\datepicker\DatePicker;
use kartik\select2\Select2;
use yii\web\JsExpression;
use yii\helpers\Url;
/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\ProfessionalRequest */

$this->title = 'Professional Request Details';
$this->params['breadcrumbs'][] = ['label' => 'Professional Requests', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$user_info = Yii::$app->user->identity->userinfo;
$roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
$rolenames =  ArrayHelper::map($roles, 'name','name');

?>
<div class="professional-request-view">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php Panel::begin(['header' => 'Professional Request Details']); ?>
    <div class="row">
         <div class="col-md-12 col-xs-12">
             <div class="pull-right"><?= Html::a('Back to Request List',['/accounting/professional-request'],['class' => 'btn btn-success']) ?></div>
         </div>
    </div>
    <div class="row">
        <div class="col-md-3 col-xs-12">
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    [
                        'attribute' => 'Date',
                        'header' => 'Date',
                        'value' => function($model){
                            return $model->start_date.' - '.$model->end_date;
                        }
                    ],
                    'period_covered',
                    'bank:ntext',
                    'account_name:ntext',
                    'account_number:ntext',
                    'approval_status',
                ],
            ]) ?>
            <?php if(in_array('TopManagement',$rolenames)){ ?>
            <div class="row">
                <div class="col-md-6">
                    <?php $form = ActiveForm::begin(['action' => 'approve']); ?>
                        <?= Html::activeHiddenInput($model, 'id', ['value' => $model->id]) ?>
                        <?= Html::submitButton('Approve Request',['class' => 'btn btn-success btn-block', 
                            'data' => [
                                'confirm' => 'Are you sure you want to approve the request?'
                                ]
                        ]) ?>
                    <?php ActiveForm::end(); ?>
                </div>
                <div class="col-md-6">
                    <?php $form = ActiveForm::begin(['action' => 'decline']); ?>
                        <?= Html::activeHiddenInput($model, 'id', ['value' => $model->id]) ?>
                        <?= Html::submitButton('Decline',['class' => 'btn btn-danger btn-block', 
                            'data' => [
                                'confirm' => 'Are you sure you want to decline the request?'
                                ]
                        ]) ?>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
            <?php } ?>
        </div>
        <div class="col-md-9 col-xs-12">
            <?php if(in_array('Professional',$rolenames)){ ?>
                <h2>Add/Edit Particular</h2>
                <?php $form = ActiveForm::begin(); ?>
                <div class="row">
                    <div class="col-md-4 col-xs-12">
                        <?= $form->field($detailModel, "date")->widget(DatePicker::className(), [
                            'model' => $detailModel,
                            'attribute' => 'date',
                            'template' => '{addon}{input}',
                                'clientOptions' => [
                                    'autoclose' => true,
                                    'format' => 'yyyy-mm-dd',
                                    'startDate' => $model->start_date,
                                    'endDate' => $model->end_date,
                                ]
                                ]); ?>
                    </div>
                    <div class="col-md-4 col-xs-12">
                        <?= $form->field($detailModel, 'branch_program_id')->widget(Select2::classname(), [
                            'data' => $branchPrograms,
                            'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'branch-program-select'],
                            'pluginOptions' => [
                                'allowClear' =>  true,
                            ],
                            ]);
                        ?>
                    </div>
                    <div class="col-md-4 col-xs-12">
                        <?= $form->field($detailModel, 'school_id')->widget(Select2::classname(), [
                                'value' => $detailModel->school_id,
                                'initValueText' => empty($detailModel->school_id) ? '' : $detailModel->school->name,
                                'options' => ['placeholder' => 'Search school', 'id' => 'school_id'],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                    'minimumInputLength' => 3,
                                    'language' => [
                                        'errorLoading' => new JsExpression("function () { return 'Waiting for results...'; }"),
                                    ],
                                    'ajax' => [
                                        'url' => Url::to(['/accounting/professional-request/school-list']),
                                        'dataType' => 'json',
                                        'data' => new JsExpression('function(params) { return {q:params.term}; }')
                                    ],
                                    'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                                    'templateResult' => new JsExpression('function(student) { return student.name; }'),
                                    'templateSelection' => new JsExpression('function (student) { return student.text == "" ? student.name : student.text; }'),
                                ],
                            ])->label('School'); 
                            ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 col-xs-12">
                        <?= $form->field($detailModel, 'number_of_hours')->widget(MaskedInput::classname(), [
                            'clientOptions' => [
                                'alias' =>  'decimal',
                                'autoGroup' => true
                            ],
                        ]) ?>
                    </div>
                    <div class="col-md-4 col-xs-12">
                        <?= $form->field($detailModel, 'concept')->textInput(['maxlength' => true]) ?>
                    </div>
                    <div class="col-md-4 col-xs-12">
                        <?= $form->field($detailModel, 'remarks')->dropdownList(['Complete' => 'Complete', 'For Make Up Lecture' => 'For Make Up Lecture']) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="pull-right">
                            <?= Html::submitButton('Save',['class' => 'btn btn-success']) ?>
                            <?= Html::a('Cancel',['/accounting/professional-request/view', 'id' => $model->id],['class' => 'btn btn-danger']); ?>
                        </div>
                    </div>
                </div>
                <?php ActiveForm::end(); ?>
            <?php } ?>
            <h2>Particulars</h2>
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],

                    'date',
                    'number_of_hours',
                    'branchProgramName',
                    'schoolName',
                    'concept',
                    'remarks',
                    [
                        'format' => 'raw',
                        'value' => function($model) use ($rolenames){
                            $content = '';

                            if(in_array('Professional',$rolenames))
                            {
                                $content.= Html::a('<i class="glyphicon glyphicon-pencil"></i>',['/accounting/professional-request/update-detail', 'id' => $model->id]);
                                $content.= Html::a('<i class="glyphicon glyphicon-trash"></i>',['/accounting/professional-request/delete-detail', 'id' => $model->id],['data' => ['method' => 'post','confirm' => 'Are you sure you want to delete this item?']]);
                            }

                            return $content;
                        }
                    ],
                ],
            ]); ?>
        </div>
    </div>
    <?php Panel::end(); ?>
</div>
