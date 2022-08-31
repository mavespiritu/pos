<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yiister\gentelella\widgets\Panel;
?>

<div class="season-view">
    <?php Panel::begin(['header' => 'Season Details']); ?>
        <?= DetailView::widget([
            'model' => $model,
            'options' => ['class' => 'table table-bordered detail-view'],
            'attributes' => [
                'branchProgramName',
                [
                    'attribute' => 'name',
                    'value' => function($model){ return 'Season '.$model->name; }
                ],
                'start_date',
                'end_date',
            ],
        ]) ?>
    <h4>Default Enhancement Fee</h4>
        <?= DetailView::widget([
            'model' => $enhancement,
            'options' => ['class' => 'table table-condensed detail-view'],
            'attributes' => [
                [
                    'attribute' => 'amount',
                    'value' => function($model){ return number_format($model->amount, 2); }
                ],
            ],
        ]) ?>
    <?php Panel::end(); ?>
</div>
<?php
        $script = '
            $( document ).ready(function() {
                if($("#enhancement-amount").val()== "" || $("#enhancement-amount").val()== 0)
                {
                    $("#enhancement-amount").val('.$enhancement->amount.');
                    $("#enhancement_amount").val('.$enhancement->amount.');

                    $("#total-tuition").html(parseFloat($("#regular_review_price").val())+parseFloat($("#enhancement-amount").val()));
                    $("#total_tuition").val(parseFloat($("#regular_review_price").val())+parseFloat($("#enhancement-amount").val()));

                    $("#final-tuition").html(parseFloat($("#regular_review_price").val())+parseFloat($("#enhancement-amount").val()));
                    $("#final_tuition").val(parseFloat($("#regular_review_price").val())+parseFloat($("#enhancement-amount").val()));
                }else{

                    $("#total-tuition").html(parseFloat($("#regular_review_price").val())+parseFloat($("#enhancement-amount").val()));
                    $("#total_tuition").val(parseFloat($("#regular_review_price").val())+parseFloat($("#enhancement-amount").val()));

                    $("#final-tuition").html(parseFloat($("#regular_review_price").val())+parseFloat($("#enhancement-amount").val()));
                    $("#final_tuition").val(parseFloat($("#regular_review_price").val())+parseFloat($("#enhancement-amount").val()));
                }

                

                $("#student-id_number").val("'.date('y', strtotime($model->start_date)).'-'.$model->name.'-'.$model->branchProgram->branch->code.'-'.$lastNo.'");
            });
';
$this->registerJs($script);
   
?>