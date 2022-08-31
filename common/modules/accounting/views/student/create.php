<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\Student */

$this->title = 'Register New Student';
$this->params['breadcrumbs'][] = ['label' => 'Students', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="student-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'seasons' => $seasons,
	    'discountModel' => $discountModel,
	    'enroleeTypeModel' => $enroleeTypeModel,
	    'packageStudentModel' => $packageStudentModel,
	    'studentTuitionModel' => $studentTuitionModel,
	    'enhancementModel' => $enhancementModel,
	    'coachingModel' => $coachingModel,
	    'incomeEnrolmentModel' => $incomeEnrolmentModel,
	    'incomeModel' => $incomeModel,
	    'seasons' => $seasons,
	    'enroleeTypes' => $enroleeTypes,
	    'packages' => $packages,
	    'discountTypes' => $discountTypes,
	    'coachingPackages' => $coachingPackages,
	    'incomeCodes' => $incomeCodes,
	    'provinces' => $provinces,
	    'citymuns' => $citymuns,
    ]) ?>

</div>
