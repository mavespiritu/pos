<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\widgets\MaskedInput;
use yii\web\View;
use dosamigos\datepicker\DatePicker;
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
                        <th>Code</th>
                        <th>Proposed Date</th>
                        <th>Particular</th>
                        <th>Amount</th>
                        <th>Date Needed</th>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="width:20%;"><?= $form->field($particular, 'particular_code_id')->widget(Select2::classname(), [
                            'data' => $particularCodes,
                            'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'particular-code-select'],
                            'pluginOptions' => [
                                'allowClear' =>  true,
                            ],
                            ])->label(false);
                        ?></td>
                        <td><?= $form->field($particular, "proposed_date")->widget(DatePicker::className(), [
                                'model' => $particular,
                                'attribute' => 'proposed_date',
                                'template' => '{addon}{input}',
                                    'clientOptions' => [
                                        'autoclose' => true,
                                        'format' => 'yyyy-mm-dd',
                                    ]
                                    ])->label(false); ?></td>
                        <td><?= $form->field($particular, "particular")->textInput(['maxlength' => true])->label(false); ?></td>
                        <td><?= $form->field($particular, "amount")->widget(MaskedInput::classname(), [
                            'clientOptions' => [
                                'alias' =>  'decimal',
                                'autoGroup' => true,
                            ],
                        ])->label(false); ?></td>
                        <td><?= $form->field($particular, "date_needed")->widget(DatePicker::className(), [
                                'model' => $particular,
                                'attribute' => 'date_needed',
                                'template' => '{addon}{input}',
                                    'clientOptions' => [
                                        'autoclose' => true,
                                        'format' => 'yyyy-mm-dd',
                                    ]
                                    ])->label(false); ?></td>
                        <td width=5 style="text-align: center;"><?= Html::submitButton('<i class="fa fa-plus"></i>',['class' => 'btn btn-success btn-sm']) ?></td>
                    </tr>
                </tbody>
            </table>
        </div> 
    </div>

    <?php ActiveForm::end(); ?>

</div>

