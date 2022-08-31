<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\widgets\MaskedInput;
use yii\web\View;
use dosamigos\datepicker\DatePicker;
use kartik\time\TimePicker;
/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\BudgetProposal */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="budget-proposal-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-sm-12">
            <table class="table" style="border: none;">
                <thead>
                    <tr>
                        <th>Lecture Date</th>
                        <th>Name</th>
                        <th>Concept</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Rate Per Hour</th>
                        <th>Transpo Allowance</th>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?= $form->field($pfRequest, "lecture_date")->widget(DatePicker::className(), [
                                'model' => $pfRequest,
                                'attribute' => 'date',
                                'template' => '{addon}{input}',
                                    'clientOptions' => [
                                        'autoclose' => true,
                                        'format' => 'yyyy-mm-dd',
                                    ]
                                    ])->label(false); ?></td>
                        <td><?= $form->field($pfRequest, "name")->textInput(['maxlength' => true])->label(false); ?></td>
                        <td><?= $form->field($pfRequest, "concept")->textInput(['maxlength' => true])->label(false); ?></td>
                        <td><?= $form->field($pfRequest, 'time')->widget(TimePicker::classname(), ['options' => ['value' => '08:00 AM']])->label(false) ?></td>
                        <td><?= $form->field($pfRequest, 'end_time')->widget(TimePicker::classname(), ['options' => ['value' => '05:00 PM'], 'pluginOptions' => ['startTime' => $pfRequest->time]])->label(false) ?></td>
                        <td><?= $form->field($pfRequest, "rate_per_hour")->widget(MaskedInput::classname(), [
                            'clientOptions' => [
                                'alias' =>  'decimal',
                                'autoGroup' => true,
                            ],
                        ])->label(false); ?></td>
                        <td><?= $form->field($pfRequest, "allowance")->widget(MaskedInput::classname(), [
                            'clientOptions' => [
                                'alias' =>  'decimal',
                                'autoGroup' => true,
                            ],
                        ])->label(false); ?></td>
                        <td width=5 style="text-align: center;"><?= Html::submitButton('<i class="fa fa-plus"></i>',['class' => 'btn btn-success btn-sm']) ?></td>
                    </tr>
                </tbody>
            </table>
        </div> 
    </div>

    <?php ActiveForm::end(); ?>

</div>

