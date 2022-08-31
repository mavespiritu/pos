<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\select2\Select2; // or kartik\select2\Select2
use yii\web\JsExpression;
use dosamigos\datepicker\DatePicker;
use yii\widgets\MaskedInput;
use yii\bootstrap\Modal;
use common\modules\accounting\models\School;
use yiister\gentelella\widgets\Panel;
use yii\helpers\ArrayHelper;
/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\Student */
/* @var $form yii\widgets\ActiveForm */
$user_info = Yii::$app->user->identity->userinfo;
$roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
$rolenames =  ArrayHelper::map($roles, 'name','name');
?>

<div class="student-form">

    <?php $form = ActiveForm::begin(); ?>
	    <?php Panel::begin(['header' => 'Student Information']); ?>
	    	<div class="pull-right"><?= Html::a('<i class="glyphicon glyphicon-backward"></i> Go Back', ['/accounting/student/list'], ['class' => 'btn btn-primary']) ?></div>
	    	<div class="clearfix"></div>
	        <div class="row">
	        	<div class="col-md-4">
	        		<?= $form->field($model, 'id_number', ['enableAjaxValidation' => true])->textInput(['maxlength' => true]) ?>
	        	</div>
	            <div class="col-md-4">
	                <?php 
	                    $citymunsurl = \yii\helpers\Url::to(['/accounting/student/citymun-list']);
	                    echo $form->field($model, 'province_id')->widget(Select2::classname(), [
	                        'data' => $provinces,
	                        'options' => ['placeholder' => 'Select Province','multiple' => false,'class'=>'province-select'],
	                        'pluginOptions' => [
	                            'allowClear' => true
	                        ],
	                        'pluginEvents'=>[
	                            'select2:select'=>'
	                                function(){
	                                    var vals = this.value;
	                                    $.ajax({
	                                        url: "'.$citymunsurl.'",
	                                        data: {province:vals}
	                                        
	                                    }).done(function(result) {
	                                        var h;
	                                        $(".citymun-select").html("").select2({ data:result, theme:"krajee", width:"100%",placeholder:"Select City/Municipality",
	                allowClear: true,});
	                                        $(".citymun-select").select2("val","");
	                                    });
	                                }'

	                        ]
	                    ])->label('Province');

	                    ?>
	            </div>
	            <div class="col-md-4">
	                <?= $form->field($model, 'citymun_id')->widget(Select2::classname(), [
	                    'data' => $citymuns,
	                    'options' => ['placeholder' => 'Select City/Municipality','multiple' => false,'class'=>'citymun-select'],
	                    'pluginOptions' => [
	                        'allowClear' => true
	                    ],
	                ])->label('City/Municipality');

	                ?>
	            </div>
	        </div>
	        <div class="row">
	            <div class="col-md-3">
	                <?= $form->field($model, 'first_name')->textInput(['maxlength' => true]) ?>
	            </div>
	            <div class="col-md-3">
	                <?= $form->field($model, 'middle_name')->textInput(['maxlength' => true]) ?>
	            </div>
	            <div class="col-md-3">
	                <?= $form->field($model, 'last_name')->textInput(['maxlength' => true]) ?>
	            </div>
	            <div class="col-md-3">
	                <?= $form->field($model, 'extension_name')->textInput(['maxlength' => true]) ?>
	            </div>
	        </div>
	        <div class="row">
	            <?php if(in_array('SchoolBased',$rolenames)){ ?>
                    <div class="col-md-12">
                        <?= $form->field($model, 'year_graduated')->widget(MaskedInput::className(),[
                            'clientOptions' => ['alias' => '9999']
                        ]);?>
                    </div>
                <?php } else{ ?>
                    <div class="col-md-9">
                       <?= $form->field($model, 'school_id')->widget(Select2::classname(), [
                            'value' => $model->school_id,
                            'initValueText' => empty($model->school_id) ? '' : $model->school->name,
                            'options' => ['placeholder' => 'Search schools', 'id' => 'school_id'],
                            'pluginOptions' => [
                                'allowClear' => true,
                                'minimumInputLength' => 3,
                                'language' => [
                                    'errorLoading' => new JsExpression("function () { return 'Waiting for results...'; }"),
                                ],
                                'ajax' => [
                                    'url' => Url::to(['/accounting/student/school-list']),
                                    'dataType' => 'json',
                                    'data' => new JsExpression('function(params) { return {q:params.term}; }')
                                ],
                                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                                'templateResult' => new JsExpression('function(student) { return student.name; }'),
                                'templateSelection' => new JsExpression('function (student) { return student.text == "" ? student.name : student.text; }'),
                            ],
                        ])->label('School ('.Html::button('Click here if school not on the list', ['value' => Url::to(['/accounting/school/create']), 'id' => 'add-school', 'style' => 'border: none; background: none; color: rgb(54, 88, 153);']).')'); 
                        ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($model, 'year_graduated')->widget(MaskedInput::className(),[
                            'clientOptions' => ['alias' => '9999']
                        ]);?>
                    </div>
                <?php } ?>
	        </div>
	        <div class="row">
	            <div class="col-md-12">
	                <?= $form->field($model, 'permanent_address')->textInput(['maxlength' => true]) ?>
	            </div>
	        </div>
	        <div class="row">
	            <div class="col-md-6">
	                <?= $form->field($model, 'birthday')->widget(MaskedInput::className(),[
	                    'clientOptions' => ['alias' => 'yyyy-mm-dd']
	                ]);?>
	            </div>
	            <div class="col-md-6">
	                <?= $form->field($model, 'prc')->textInput(['maxlength' => true]) ?>
	            </div>
	        </div>
	        <div class="row">
	            <div class="col-md-6">
	                <?= $form->field($model, 'contact_no')->widget(MaskedInput::className(),[
	                    'clientOptions' => ['alias' => '99999999999']
	                ]);?>
	            </div>
	            <div class="col-md-6">
	                <?= $form->field($model, 'email_address')->widget(MaskedInput::className(),[
	                    'clientOptions' => ['alias' => 'email']
	                ]);?>
	            </div>
	        </div>
	    <br>
	    <div class="form-group pull-right">
	        <?= Html::submitButton('Save Information', ['class' => 'btn btn-success']) ?>
	    </div>

	    <?php Panel::end(); ?>
	<?php ActiveForm::end() ?>
</div>