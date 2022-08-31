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
                        <th>Category</th>
                        <th>Date</th>
                        <th>Particulars</th>
                        <th>Amount</th>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="width:20%;"><?= $form->field($liquidation, 'category_id')->widget(Select2::classname(), [
                            'data' => $liquidationCategories,
                            'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'liquidation-category-select'],
                            'pluginOptions' => [
                                'allowClear' =>  true,
                            ],
                            ])->label(false);
                        ?></td>
                        <td><?= $form->field($liquidation, "date")->widget(DatePicker::className(), [
                                'model' => $liquidation,
                                'attribute' => 'date',
                                'template' => '{addon}{input}',
                                    'clientOptions' => [
                                        'autoclose' => true,
                                        'format' => 'yyyy-mm-dd',
                                    ]
                                    ])->label(false); ?></td>
                        <td><?= $form->field($liquidation, "particulars")->textInput(['maxlength' => true])->label(false); ?></td>
                        <td><?= $form->field($liquidation, "amount")->widget(MaskedInput::classname(), [
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

