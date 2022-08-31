<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yiister\gentelella\widgets\Panel;
?>

<div class="season-view">
	<?php Panel::begin(['header' => 'Package Details']); ?>
    <?= DetailView::widget([
        'model' => $model,
        'options' => ['class' => 'table table-bordered detail-view'],
        'attributes' => [
            'branchName',
            'packageTypeName',
            'tier',
            'code',
            'amount',
        ],
    ]) ?>
    <h4>Freebies Included</h4>
    <table class="table table-bordered detail-view">
        <thead>
            <tr>
                <th>Freebie</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
        <?php if($model->packageFreebies){ ?>
            <?php foreach($model->packageFreebies as $freebie){ ?>
                <tr>
                    <td><?= $freebie->freebie->name ?></td>
                    <td><?= number_format($freebie->amount, 2) ?></td>
                </tr>
            <?php } ?>
        <?php } ?>
        </tbody> 
    </table>
    <?php Panel::end(); ?>
</div>
<?php
        $script = '
            $( document ).ready(function() {
                $("#regular-review-price").html('.$model->amount.');
                $("#regular_review_price").val('.$model->amount.');

                $("#total-tuition").html('.(int)$model->amount.'+parseFloat($("#enhancement-amount").val()));
                $("#total_tuition").val('.(int)$model->amount.'+parseFloat($("#enhancement-amount").val()));

                $("#final-tuition").html(('.(int)$model->amount.'+parseFloat($("#enhancement-amount").val())) - parseFloat($("#discount_amount").val()));
                $("#final_tuition").html(('.(int)$model->amount.'+parseFloat($("#enhancement-amount").val())) - parseFloat($("#discount_amount").val()));
            });
';
$this->registerJs($script);
   
?>