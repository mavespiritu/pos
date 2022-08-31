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

$this->title = 'Monthly Summary Report';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="audit-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php Panel::begin(['header' => 'Monthly Summary']); ?>
    <div class="row">
        <div class="col-md-3">
            <?php Panel::begin(['header' => 'Search Filter']); ?>
            <?php $form = ActiveForm::begin([
                'action' => ['index'],
                'method' => 'get',
            ]); ?>

                <?php 
                    $seasonsurl = \yii\helpers\Url::to(['/accounting/audit/season-list']);
                    echo $form->field($model, 'branch_program_id')->widget(Select2::classname(), [
                        'data' => $branchPrograms,
                        'options' => ['placeholder' => 'Select One','multiple' => false,'class'=>'branch-program-select'],
                        'pluginOptions' => [
                            'allowClear' => true
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
                    ])->label('Branch - Program');

                    ?>

                <?= $form->field($model, 'season_id')->widget(Select2::classname(), [
                    'data' => $seasons,
                    'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'season-select'],
                    'pluginOptions' => [
                        'allowClear' =>  true,
                    ],
                    ]);
                ?>

                <?= $form->field($model, 'datetime', [
                        'options'=>['class'=>'drp-container form-group']
                    ])->widget(DateRangePicker::classname(), [
                        'useWithAddon'=>true,
                        'presetDropdown'=>true,
                        'hideInput'=>true
                    ]); ?>
            <br>
            <div class="row">
                <div class="col-md-6">
                    <?= Html::button('Show Record',['class' => 'btn btn-primary btn-block', 'id' => 'audit-button', 'onclick' => '
                        (function(){ 
                            var datefield = $("#audit-datetime").val();
                            var branchProgram = $("#audit-branch_program_id").val();
                            var season = $("#audit-season_id").val();
            
                            if(datefield != "" && season !="")
                            {
                                $.ajax({
                                    url: "'.Url::to(['/accounting/report/generate-monthly-summary']).'?id=" + datefield + "&branchProgram=" + branchProgram + "&season=" + season,
                                    success: function (data) { 
                                        $("#monthly-summary").empty();
                                        $("#monthly-summary").hide();
                                        $("#monthly-summary").fadeIn();
                                        $("#monthly-summary").html(data);
                                    }
                                });  
                            }else{
                                alert("Date or season must not be empty");
                            }
                        })();
                    ']) ?>
                </div>
                <div class="col-md-6">
                    <?= Html::a('Clear',['/accounting/report/monthly-summary'],['class' => 'btn btn-default btn-block']) ?>
                </div>
            </div>
            
            <?php ActiveForm::end() ?>
            <?php Panel::end() ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div id="monthly-summary"></div>
        </div>
    </div>
            
    <?php Panel::end() ?>
    
</div>
