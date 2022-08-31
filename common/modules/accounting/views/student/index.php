<?php

use yii\helpers\Html;
use yii\grid\GridView;
use kartik\select2\Select2; // or kartik\select2\Select2
use yii\web\JsExpression;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yiister\gentelella\widgets\Panel;
/* @var $this yii\web\View */
/* @var $searchModel common\modules\accounting\models\StudentSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Enrolment';
$this->params['breadcrumbs'][] = $this->title;

$url = \yii\helpers\Url::to(['student-list']);
?>
<div class="student-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    <?php Panel::begin(['header' => 'Select Details']); ?>

    <p class="text-right"><?= Html::a('Register New Student',['/accounting/student/create'],['class' => 'btn btn-success']) ?></p>
    
    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>


        <?= $form->field($model, 'season_id')->widget(Select2::classname(), [
            'data' => $seasons,
            'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'season-select'],
            'pluginOptions' => [
                'allowClear' =>  true,
            ],
            ]);
        ?>

        <?= $form->field($model, 'id')->widget(Select2::classname(), [
            'initValueText' => '', // set the initial display text
            'options' => ['placeholder' => 'Search for student', 'id' => 'student_id'],
            'pluginOptions' => [
                'allowClear' => true,
                'minimumInputLength' => 3,
                'language' => [
                    'errorLoading' => new JsExpression("function () { return 'Waiting for results...'; }"),
                ],
                'ajax' => [
                    'url' => $url,
                    'dataType' => 'json',
                    'data' => new JsExpression('function(params) { return {q:params.term}; }')
                ],
                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                'templateResult' => new JsExpression('function(student) { return student.name; }'),
                'templateSelection' => new JsExpression('function (student) { return student.name; }'),
            ],
        ])->label('Student'); 
        ?>
    <br>
    <div class="form-group pull-right">
        <?= Html::button('Show Records',['class' => 'btn btn-primary', 'id' => 'enroll-button', 'onclick' => '
            (function(){ 
                var season_id = $("#student-season_id").val();
                var student_id = $("#student_id").val();
                
                if(season_id != "")
                {
                    if(student_id != "")
                    {
                        $.ajax({
                            url: "'.Url::to(['/accounting/student/enroll']).'?id=" + student_id + "&season_id=" + season_id,
                            success: function (data) { 
                                $("#student-information").empty();
                                $("#student-information").hide();
                                $("#student-information").fadeIn();
                                $("#student-information").html(data);
                            }
                        });  
                    }else{
                        alert("Select one student");
                    }
                }else{
                    alert("Select one season");
                }
            })();
        ']) ?>

        <?= Html::a('Clear',['/accounting/student/'],['class' => 'btn btn-default']) ?>
    </div>
    
    <?php ActiveForm::end(); ?>

    <?php Panel::end(); ?>
    <div id="student-information"></div>
</div>