<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yiister\gentelella\widgets\Panel;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;
use dosamigos\datepicker\DatePicker;
use yii\helpers\Url;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $searchModel common\modules\accounting\models\AuditSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Daily Audit';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="audit-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php Panel::begin(['header' => 'Daily Auditing Record']); ?>
    <div class="row">
        <div class="col-md-3 col-xs-12">
            <?php Panel::begin(['header' => 'Search Filter']); ?>
            <?php $form = ActiveForm::begin([
                'action' => ['index'],
                'method' => 'get',
            ]); ?>
                <?php $seasonsurl = \yii\helpers\Url::to(['/accounting/audit/season-list']); ?>

                <?= $form->field($model, 'branch_program_id')->widget(Select2::classname(), [
                    'data' => $branchPrograms,
                    'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'archive-branch-program-select'],
                    'pluginOptions' => [
                        'allowClear' =>  true,
                    ],
                    'pluginEvents'=>[
                        'select2:select'=>'
                            function(){
                                var vals = this.value;
                                $.ajax({
                                    url: "'.$seasonsurl.'",
                                    data: {id:vals}
                                    
                                }).done(function(result) {
                                    var h;
                                    $(".season-select").html("").select2({ data:result, theme:"krajee", width:"100%",placeholder:"Select One", allowClear: true,});
                                    $(".season-select").select2("val","");
                                });
                            }'

                    ]
                    ]);
                ?>

                <?= $form->field($model, 'season_id')->widget(Select2::classname(), [
                    'data' => [],
                    'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'season-select'],
                    'pluginOptions' => [
                        'allowClear' =>  true,
                    ],
                    ]);
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
                 ])->label('Date'); ?>
            <br>
            <div class="row">
                <div class="col-md-6 col-xs-12">
                    <?= Html::button('Show Record',['class' => 'btn btn-primary btn-block', 'id' => 'audit-button', 'onclick' => '
                        (function(){ 
                            var datefield = $("#audit-datetime").val();
                            var branchProgram = $("#audit-branch_program_id").val();
                            var season = $("#audit-season_id").val();
            
                            if((datefield != "") && (branchProgram != "") && (season != ""))
                            {
                                $.ajax({
                                    url: "'.Url::to(['/accounting/audit/audit']).'?id=" + datefield + "&branchProgram=" + branchProgram + "&season=" + season,
                                    success: function (data) { 
                                        $("#audit-information").empty();
                                        $("#audit-information").hide();
                                        $("#audit-information").fadeIn();
                                        $("#audit-information").html(data);
                                    }
                                });  
                            }else{
                                alert("Fields must not be empty");
                            }
                        })();
                    ']) ?>
                </div>
                <div class="col-md-6 col-xs-12">
                    <?= Html::a('Clear',['/accounting/audit/'],['class' => 'btn btn-default btn-block']) ?>
                </div>
            </div>
            
            <?php ActiveForm::end() ?>
            <?php Panel::end() ?>
        </div>
        <div class="col-md-9 col-xs-12">
            <div id="audit-summary"></div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 col-xs-12">
            <div id="audit-information"></div>
        </div>
    </div>
            
    <?php Panel::end() ?>
    
</div>
