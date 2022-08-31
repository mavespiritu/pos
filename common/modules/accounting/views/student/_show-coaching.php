<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yiister\gentelella\widgets\Panel;
?>

<div class="season-view">
	<?php Panel::begin(['header' => 'Coaching Package']); ?>
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
    <?php Panel::end(); ?>
</div>
<?php
        $script = '
            $( document ).ready(function() {
                $("#coaching_amount").val('.$model->amount.');
            });
';
$this->registerJs($script);
   
?>