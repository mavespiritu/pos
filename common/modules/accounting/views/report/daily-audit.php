<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yiister\gentelella\widgets\Panel;
use yii\web\JsExpression;
use kartik\form\ActiveForm;
use dosamigos\datepicker\DatePicker;
use yii\helpers\Url;
use kartik\select2\Select2;
use kartik\daterange\DateRangePicker;
/* @var $this yii\web\View */
/* @var $searchModel common\modules\accounting\models\AuditSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Daily Audit Summary Report';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="audit-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php Panel::begin(['header' => 'Daily Audit']); ?>
    <div class="row">
        <div class="col-md-3">
            <?php Panel::begin(['header' => 'Search Filter']); ?>
            <?php $form = ActiveForm::begin([
                'action' => ['index'],
                'method' => 'get',
            ]); ?>

                <?php 
                    echo $form->field($model, 'branch_program_id')->widget(Select2::classname(), [
                        'data' => $branchPrograms,
                        'options' => ['placeholder' => 'Select One','multiple' => false,'class'=>'branch-program-select'],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ])->label('Branch - Program');

                    ?>

                <?= $form->field($model, 'datetime')->widget(DatePicker::className(), [
                        'model' => $model,
                        'attribute' => 'datetime',
                        'template' => '{addon}{input}',
                            'clientOptions' => [
                                'autoclose' => true,
                                'format' => 'yyyy-mm-dd',
                                'endDate' => date("Y-m-d"),
                            ]
                ])->label('Date Inside Cut-Off'); ?>
            <br>
            <div class="row">
                <div class="col-md-6">
                    <?= Html::button('Show Record',['class' => 'btn btn-primary btn-block', 'id' => 'audit-button', 'onclick' => '
                        (function(){ 
                            var datefield = $("#audit-datetime").val();
                            var branchProgram = $("#audit-branch_program_id").val();
            
                            if(datefield != "" && branchProgram !="")
                            {
                                $.ajax({
                                    url: "'.Url::to(['/accounting/report/generate-daily-audit']).'?id=" + datefield + "&branchProgram=" + branchProgram,
                                    success: function (data) { 
                                        $("#daily-audit-information").empty();
                                        $("#daily-audit-information").hide();
                                        $("#daily-audit-information").fadeIn();
                                        $("#daily-audit-information").html(data);
                                    }
                                });  
                            }else{
                                alert("Date or Branch-Program must not be empty");
                            }
                        })();
                    ']) ?>
                </div>
                <div class="col-md-6">
                    <?= Html::a('Clear',['/accounting/report/daily-audit'],['class' => 'btn btn-default btn-block']) ?>
                </div>
            </div>
            
            <?php ActiveForm::end() ?>
            <?php Panel::end() ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div id="daily-audit-information"></div>
        </div>
    </div>
            
    <?php Panel::end() ?>
    
</div>
