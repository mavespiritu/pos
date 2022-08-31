<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\modules\pos\models\PosEnrolment */
?>
<p>Customer Information:</p>
<h4><b><?= $model->fullName ?></b></h4>
<br>
<p>
	<?= $model->school->branch->title ?>
<br><?= $model->schoolName ?>
<br><?= $model->contact_no ?>
<br><?= $model->email_address ?></p>
