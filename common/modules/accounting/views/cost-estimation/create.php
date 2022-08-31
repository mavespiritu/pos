<?php

use yii\helpers\Html;
use yiister\gentelella\widgets\Panel;

/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\Income */

$this->title = 'Create Cost Estimation';
$this->params['breadcrumbs'][] = ['label' => 'Cost Estimation', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="cost-estimation-create">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php Panel::begin(['header' => 'Cost Estimation Form']); ?>
        <p class="pull-right"><?= Html::a('Go Back',['/accounting/cost-estimation/'],['class' => 'btn btn-success']) ?></p>
        <br>
	    <?= $this->render('_form', [
	    	'model' => $model,
	        'seasons' => $seasons,
        	'enroleeTypes' => $enroleeTypes,
        	'freebies' => $freebies,
        	'incomeModels' => $incomeModels,
        	'taxModels' => $taxModels,
        	'programModels' => $programModels,
        	'venueRentalModels' => $venueRentalModels,
        	'freebieModels' => $freebieModels,
        	'reviewModels' => $reviewModels,
        	'foodModels' => $foodModels,
        	'transportationModels' => $transportationModels,
        	'staffSalaryModels' => $staffSalaryModels,
        	'rebateModels' => $rebateModels,
        	'utilityModels' => $utilityModels,
        	'academicModels' => $academicModels,
        	'emergencyFundModels' => $emergencyFundModels,
        	'royaltyFeeModels' => $royaltyFeeModels,
	    ]) ?>
    <?php Panel::end(); ?>
</div>
