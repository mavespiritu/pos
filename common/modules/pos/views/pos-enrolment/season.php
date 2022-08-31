<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\modules\pos\models\PosEnrolment */
?>
<div class="pos-enrolment-view">

    <p><b>Season Details</b></p>
    <div class="box box-solid">
        <div class="box-body">
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'branchProgramName',
                    'seasonTitle',
                    'start_date',
                    'end_date',
                ],
            ]) ?>
        </div>
    </div>

</div>
