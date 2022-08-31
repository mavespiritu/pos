<?php

use yii\helpers\Html;
use yiister\gentelella\widgets\Panel;

/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\Income */

$this->title = 'Cost Estimation Details';
$this->params['breadcrumbs'][] = ['label' => 'Cost Estimation', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="cost-estimation-view">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php Panel::begin(['header' => $model->seasonName]); ?>
        <p class="pull-right">
            <?= Html::a('Go Back',['/accounting/cost-estimation/'],['class' => 'btn btn-success']) ?>
            <?= $model->netIncome == 0 ? Html::a('Create Estimation',['/accounting/cost-estimation/create'],['class' => 'btn btn-success']) : Html::a('Update',['/accounting/cost-estimation/update', 'id' => $model->id],['class' => 'btn btn-warning']) ?>
        </p>
        <br>
	    <div class="row">
        <div class="col-md-12 col-xs-12">
            <table class="table table-bordered table-condensed table-hover">
                <tbody>
                    <tr>
                        <th colspan=2>Total Gross</th>
                        <td colspan=2 align=right><b><?= number_format($model->totalGross, 2) ?></b></td>
                    </tr>
                    <tr>
                        <th colspan=2>Expected Number of Students</th>
                        <td colspan=2 align=right><b><?= $model->totalStudents ?></b></td>
                    </tr>
                    <?php if($model->targetIncomes){ ?>
                        <?php foreach($model->targetIncomes as $targetIncome){ ?>
                            <tr>
                                <td><?= $targetIncome->enroleeType->name ?></td>
                                <td><?= $targetIncome->quantity ?></td>
                                <td><?= number_format($targetIncome->unit_price, 2) ?></td>
                                <td align=right><?= number_format($targetIncome->quantity * $targetIncome->unit_price, 2) ?></td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                    <tr><td colspan=4>&nbsp;</td></tr>
                    <tr>
                        <td>Tax For Government</td>
                        <td>Less</td>
                        <td><p class="pull-right">/1.12*0.12</p></td>
                        <td align=right><?= number_format(($model->totalGross/1.12)*0.12, 2) ?></td>
                    </tr>
                    <tr><td colspan=4>&nbsp;</td></tr>
                    <tr style="background: #F7F7F7;">
                        <th colspan=3>Total Gross Income</th>
                        <td align=right><b><?= number_format($model->totalGrossIncome, 2) ?></b></td>
                    </tr>
                    <tr><td colspan=4>&nbsp;</td></tr>
                    <tr>
                        <th>Part I<br>(For Partners/Area Managers)</th>
                        <td>&nbsp;</td>
                        <th colspan=2>Part II<br>(For Accounting/Audit)</th>
                    </tr>
                    <tr>
                        <th>Length of Program</th>
                        <th>&nbsp;</th>
                        <td>Unit Price/ Per Hour</td>
                        <td>Total</td>
                    </tr>
                    <?php if($model->targetPrograms){ ?>
                        <?php foreach($model->targetPrograms as $targetProgram){ ?>
                            <tr>
                                <td><?= $targetProgram->label ?></td>
                                <td><?= $targetProgram->quantity ?></td>
                                <td><?= number_format($targetProgram->unit_price, 2) ?></td>
                                <td align=right><?= number_format($targetProgram->quantity * $targetProgram->unit_price, 2) ?></td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                    <tr style="background: #F7F7F7;">
                        <td align=right><b>Total</b></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td align=right><b><?= number_format($model->totalPrograms, 2) ?></b></td>
                    </tr>
                    <tr>
                        <td><b>Venue Rentals</b></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    <?php if($model->targetVenueRentals){ ?>
                        <?php foreach($model->targetVenueRentals as $targetVenueRental){ ?>
                            <tr>
                                <td><?= $targetVenueRental->label ?></td>
                                <td><?= $targetVenueRental->quantity ?></td>
                                <td><?= number_format($targetVenueRental->unit_price, 2) ?></td>
                                <td align=right><?= number_format($targetVenueRental->quantity * $targetVenueRental->unit_price, 2) ?></td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                    <tr style="background: #F7F7F7;">
                        <td align=right><b>Total</b></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td align=right><b><?= number_format($model->totalVenueRentals, 2) ?></b></td>
                    </tr>
                    <tr>
                        <th>Freebies</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                    </tr>
                    <?php if($model->targetFreebies){ ?>
                        <?php foreach($model->targetFreebies as $targetFreebie){ ?>
                            <tr>
                                <td><?= $targetFreebie->freebie->name ?></td>
                                <td><?= $targetFreebie->quantity ?></td>
                                <td><?= number_format($targetFreebie->unit_price, 2) ?></td>
                                <td align=right><?= number_format($targetFreebie->quantity * $targetFreebie->unit_price, 2) ?></td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                    <tr style="background: #F7F7F7;">
                        <td align=right><b>Total</b></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td align=right><b><?= number_format($model->totalFreebies, 2) ?></b></td>
                    </tr>
                    <tr>
                        <td><b>Review Materials</b></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>Estimated Budget</td>
                    </tr>
                    <?php if($model->targetReviews){ ?>
                        <?php foreach($model->targetReviews as $targetReview){ ?>
                            <tr>
                                <td><?= $targetReview->label ?></td>
                                <td><?= $targetReview->quantity ?></td>
                                <td><?= number_format($targetReview->unit_price, 2) ?></td>
                                <td align=right><?= number_format($targetReview->quantity * $targetReview->unit_price, 2) ?></td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                    <tr style="background: #F7F7F7;">
                        <td align=right><b>Total</b></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td align=right><b><?= number_format($model->totalReviews, 2) ?></b></td>
                    </tr>
                    <tr>
                        <td><b>Food</b></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    <?php if($model->targetFoods){ ?>
                        <?php foreach($model->targetFoods as $targetFood){ ?>
                            <tr>
                                <td><?= $targetFood->label ?></td>
                                <td><?= $targetFood->quantity ?></td>
                                <td><?= number_format($targetFood->unit_price, 2) ?></td>
                                <td align=right><?= number_format($targetFood->quantity * $targetFood->unit_price, 2) ?></td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                    <tr style="background: #F7F7F7;">
                        <td align=right><b>Total</b></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td align=right><b><?= number_format($model->totalFoods, 2) ?></b></td>
                    </tr>
                    <tr>
                        <td><b>Transportation</b></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>Estimated Budget</td>
                    </tr>
                    <?php if($model->targetTransportations){ ?>
                        <?php foreach($model->targetTransportations as $targetTransportation){ ?>
                            <tr>
                                <td><?= $targetTransportation->label ?></td>
                                <td><?= $targetTransportation->quantity ?></td>
                                <td><?= number_format($targetTransportation->unit_price, 2) ?></td>
                                <td align=right><?= number_format($targetTransportation->quantity * $targetTransportation->unit_price, 2) ?></td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                    <tr style="background: #F7F7F7;">
                        <td align=right><b>Total</b></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td align=right><b><?= number_format($model->totalTransportations, 2) ?></b></td>
                    </tr>
                    <tr>
                        <td><b>Staff Salary</b></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    <?php if($model->targetStaffSalaries){ ?>
                        <?php foreach($model->targetStaffSalaries as $targetStaffSalary){ ?>
                            <tr>
                                <td><?= $targetStaffSalary->label ?></td>
                                <td><?= $targetStaffSalary->quantity ?></td>
                                <td><?= number_format($targetStaffSalary->unit_price, 2) ?></td>
                                <td align=right><?= number_format($targetStaffSalary->quantity * $targetStaffSalary->unit_price, 2) ?></td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                    <tr style="background: #F7F7F7;">
                        <td align=right><b>Total</b></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td align=right><b><?= number_format($model->totalStaffSalaries, 2) ?></b></td>
                    </tr>
                    <tr>
                        <td><b>Rebate</b></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    <?php if($model->targetRebates){ ?>
                        <?php foreach($model->targetRebates as $targetRebate){ ?>
                            <tr>
                                <td><?= $targetRebate->label ?></td>
                                <td><?= $targetRebate->quantity ?></td>
                                <td><?= number_format($targetRebate->unit_price, 2) ?></td>
                                <td align=right><?= number_format($targetRebate->quantity * $targetRebate->unit_price, 2) ?></td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                    <tr style="background: #F7F7F7;">
                        <td align=right><b>Total</b></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td align=right><b><?= number_format($model->totalRebates, 2) ?></b></td>
                    </tr>
                    <tr>
                        <td><b>Utilities/month</b></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    <?php if($model->targetUtilities){ ?>
                        <?php foreach($model->targetUtilities as $targetUtility){ ?>
                            <tr>
                                <td><?= $targetUtility->label ?></td>
                                <td><?= $targetUtility->quantity ?></td>
                                <td><?= number_format($targetUtility->unit_price, 2) ?></td>
                                <td align=right><?= number_format($targetUtility->quantity * $targetUtility->unit_price, 2) ?></td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                    <tr style="background: #F7F7F7;">
                        <td align=right><b>Total</b></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td align=right><b><?= number_format($model->totalUtilities, 2) ?></b></td>
                    </tr>
                    <tr>
                        <td><b>Toprank Academic Activities</b></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    <?php if($model->targetAcademics){ ?>
                        <?php foreach($model->targetAcademics as $targetAcademic){ ?>
                            <tr>
                                <td><?= $targetAcademic->label ?></td>
                                <td><?= $targetAcademic->quantity ?></td>
                                <td><?= number_format($targetAcademic->unit_price, 2) ?></td>
                                <td align=right><?= number_format($targetAcademic->quantity * $targetAcademic->unit_price, 2) ?></td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                    <tr style="background: #F7F7F7;">
                        <td align=right><b>Total</b></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td align=right><b><?= number_format($model->totalAcademics, 2) ?></b></td>
                    </tr>
                    <?php if($model->targetEmergencyFunds){ ?>
                        <?php foreach($model->targetEmergencyFunds as $targetEmergency){ ?>
                            <tr>
                                <td><?= $targetEmergency->label ?></td>
                                <td><?= $targetEmergency->quantity ?></td>
                                <td><?= number_format($targetEmergency->unit_price, 2) ?></td>
                                <td align=right><?= number_format($targetEmergency->quantity * $targetEmergency->unit_price, 2) ?></td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                    <tr style="background: #F7F7F7;">
                        <td align=right><b>Total</b></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td align=right><b><?= number_format($model->totalEmergencyFunds, 2) ?></b></td>
                    </tr>
                    <tr><td colspan=4>&nbsp;</td></tr>
                    <tr style="background: #F7F7F7;">
                        <td><b>Total Expenses</b></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td align=right><b><?= number_format($model->totalExpenses, 2) ?></b></td>
                    </tr>
                    <tr><td colspan=4>&nbsp;</td></tr>
                    <tr>
                        <td align=right><b>Total Gross</b></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td align=right><b><?= number_format($model->totalGrossIncome, 2) ?></b></td>
                    </tr>
                    <tr>
                        <td align=right><b>Total Expenses</b></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td align=right><b><?= number_format($model->totalExpenses, 2) ?></b></td>
                    </tr>
                    <tr style="background: #F7F7F7;">
                        <td align=right><b>Expected Income</b></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td align=right><b><?= number_format($model->expectedIncome, 2) ?></b></td>
                    </tr>
                    <tr><td colspan=4>&nbsp;</td></tr>
                    <?php if($model->targetRoyaltyFees){ ?>
                        <?php foreach($model->targetRoyaltyFees as $targetRoyaltyFee){ ?>
                            <tr>
                                <td><b>Royalty Fee</b></td>
                                <td>&nbsp;</td>
                                <td><?= $targetRoyaltyFee->percentage ?></td>
                                <td align=right><?= number_format($model->expectedIncome * $targetRoyaltyFee->percentage, 2) ?></td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                    <tr style="background: #F7F7F7;">
                        <td align=right><b>NET INCOME</b></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td align=right><b><?= number_format($model->netIncome, 2) ?></b></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <?php Panel::end(); ?>
</div>
