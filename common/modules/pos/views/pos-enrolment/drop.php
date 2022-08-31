<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\widgets\ActiveForm;
use dosamigos\datepicker\DatePicker;
use kartik\select2\Select2;
/* @var $this yii\web\View */
/* @var $model common\modules\pos\models\PosEnrolment */

$this->title = 'Enrolment Details';
$this->params['breadcrumbs'][] = ['label' => 'Enrolments', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pos-enrolment-view">

    <p class="pull-right">
        <?= Html::a('Go Back to Enrolment Details', ['view', 'id' => $enrolmentModel->id], ['class' => 'btn btn-primary']) ?>
    </p>
    <div class="row">
    <div class="col-md-4 col-xs-12">
        <h4>Enrolment Details</h4>
        <?= DetailView::widget([
            'model' => $enrolmentModel,
            'attributes' => [
                'seasonName',
                'customerName',
                'enrolmentTypeName',
                'productName',
                [
                    'attribute' => 'totalDue',
                    'format' => 'raw',
                    'value' => function($enrolmentModel){
                        return number_format($enrolmentModel->totalDue, 2);
                    }
                ],
                [
                    'attribute' => 'amountPaid',
                    'format' => 'raw',
                    'value' => function($enrolmentModel){
                        return number_format($enrolmentModel->amountPaid, 2);
                    }
                ],
                [
                    'attribute' => 'balance',
                    'format' => 'raw',
                    'value' => function($enrolmentModel){
                        return number_format($enrolmentModel->balance, 2);
                    }
                ],
                [
                    'attribute' => 'status',
                    'format' => 'raw'
                ],
                'enrolment_date'
            ],
        ]) ?>
    </div>
    <div class="col-md-8 col-xs-12">
        <h4>Dropout Form</h4>
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'status')->widget(Select2::classname(), [
                'data' => ['Dropped' => 'Dropped', 'Unofficially Dropped' => 'Unofficially Dropped', 'Refunded' => 'Refunded'],
                'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'status-select'],
                'pluginOptions' => [
                    'allowClear' =>  true,
                ],
                ]);
            ?>

        <?= $form->field($model, 'remarks')->textArea(['rows' => 2])->label('Reason for Dropping') ?>

        <?= $form->field($model, 'date_processed')->widget(DatePicker::classname(), [
                'options' => ['placeholder' => 'Select Date'],
                'clientOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd',
                    'endDate' => date("Y-m-d")
                ],
            ]); ?>

        <div class="form-group">
                <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
            </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>
