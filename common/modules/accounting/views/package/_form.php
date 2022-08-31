<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\widgets\MaskedInput;
/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\Package */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="package-form">

    <?php $form = ActiveForm::begin([
        'options' => ['class' => 'disable-submit-buttons'],
    ]); ?>

    <div class="row">
        <div class="col-md-6">
            <div class="row">
                <div class="col-md-4 col-xs-12">
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
                </div>

                <div class="col-md-4 col-xs-12">
                    <?= $form->field($model, 'season_id')->widget(Select2::classname(), [
                        'data' => $seasons,
                        'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'season-select'],
                        'pluginOptions' => [
                            'allowClear' =>  true,
                        ],
                        ]);
                    ?>
                </div>

                <div class="col-md-4 col-xs-12">
                    <?= $form->field($model, 'package_type_id')->widget(Select2::classname(), [
                        'data' => $packageTypes,
                        'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'package-type-select'],
                        'pluginOptions' => [
                            'allowClear' =>  true,
                        ],
                        ]);
                    ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <?= $form->field($model, 'tier')->textInput() ?>

                    <?= $form->field($model, 'code')->textInput(['maxlength' => true]) ?>

                    <?= $form->field($model, 'amount')->widget(MaskedInput::classname(), [
                        'clientOptions' => [
                            'alias' =>  'decimal',
                            /*'groupSeparator' => ',',*/
                            'autoGroup' => true
                        ],
                    ])->label('Total Amount')
                    ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <?php if($freebies){ ?>
                <?php foreach($freebies as $key => $freebie){ ?>
                    <?= $form->field($packageFreebies[$key], "[$key]freebie_id")->hiddenInput(['value'=>$freebie->id])->label(false) ?>
                    <?= $form->field($packageFreebies[$key], "[$key]amount")->widget(MaskedInput::classname(), [
                        'clientOptions' => [
                            'alias' =>  'decimal',
                            /*'groupSeparator' => ',',*/
                            'autoGroup' => true
                        ],
                    ])->label($freebie['name'])
                    ?>
                <?php } ?>
            <?php } ?>
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success', 'data' => ['disabled-text' => 'Please Wait']]) ?>
        <?= Html::a('Clear',['/accounting/package/'],['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
