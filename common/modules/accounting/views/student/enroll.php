<?php

use yii\helpers\Html;
use yiister\gentelella\widgets\Panel;

/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\Student */

$this->title = 'Enroll Student';
$this->params['breadcrumbs'][] = ['label' => 'Students', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="student-create">

    <?php Panel::begin(['header' => 'Enrolment Form']); ?>

    <?= $this->render('_enroll', [
        'model' => $model,
        'season' => $season,
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
        'current_or' => $current_or,
        'orStatus' => $orStatus,
        'tuition' => $tuition,
        'dropoutModel' => $dropoutModel,
        'dropout' => $dropout,
        'transfereeModel' => $transfereeModel,
        'transferee' => $transferee,
        'transferred' => $transferred
    ]) ?>
    <?php Panel::end(); ?>
</div>
