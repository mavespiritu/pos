<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yiister\gentelella\widgets\Panel;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\select2\Select2; // or kartik\select2\Select2
use yii\web\JsExpression;
use dosamigos\datepicker\DatePicker;
use yii\widgets\MaskedInput;
/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\Transferee */

$this->title = $model->studentName;
$this->params['breadcrumbs'][] = ['label' => 'Transferees', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$paymentTotal = 0;
?>
<div class="transferee-view">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php Panel::begin(['header' => 'Transferred Student Details']); ?>
    <div class="row">
        <div class="col-md-4">
            <?php Panel::begin(['header' => 'Previous Enrolment Details']); ?>
            <table class="table table-bordered table-responsive">
                <tbody>
                    <tr>
                        <td><b>Previous Season</b></td>
                        <td><?= $model->fromSeasonName ?></td>
                    </tr>
                    <tr>
                        <td><b>Enrolee Type</b></td>
                        <td><?= $enroleeTypeModel ? $enroleeTypeModel->enroleeType->name : '' ?></td>
                    </tr>
                    <tr>
                        <td><b>Package</b></td>
                        <td><?= $packageStudentModel ? $packageStudentModel->package->code.' - '.$packageStudentModel->package->packageType->name.' - TIER '.$packageStudentModel->package->tier : '' ?></td>
                    </tr>
                    <tr>
                        <td><b>Coaching Package</b></td>
                        <td><?= $coachingModel ? $coachingModel->package->code.' - '.$coachingModel->package->packageType->name.' - TIER '.$coachingModel->package->tier : '' ?></td>
                    </tr>
                    <tr>
                        <td><b>Discount Type</b></td>
                        <td><?= $discountModel ? $discountModel->discountType->name : '' ?></td>
                    </tr>

                    <tr>
                        <td><b>Code Number</b></td>
                        <td><?= $discountModel ? $discountModel->code_number : '' ?></td>
                    </tr>
                </tbody>
            </table>
            <h2>Tuition</h2>
            <div class="x_title clearfix"></div>
            <table class="table table-bordered table-responsive">
                <tr>
                    <td>Regular Review Price</td>
                    <td align="right"><?= $studentTuitionModel ? number_format($studentTuitionModel->packageStudent->amount, 2) : number_format(0, 2) ?></td>
                </tr>
                <tr>
                    <td>Enhancement Price</td>
                    <td align="right"><?= $studentTuitionModel ? number_format($studentTuitionModel->enhancement->amount, 2) : number_format(0, 2) ?></td>
                </tr>
                <tr>
                    <td>Total Tuition</td>
                    <td align="right"><?= $studentTuitionModel ? number_format(($studentTuitionModel->packageStudent->amount + $studentTuitionModel->enhancement->amount), 2) : number_format(0, 2) ?></td>
                </tr>
                <tr>
                    <td>Coaching With Icons</td>
                    <td align="right"><?= $coachingModel ? number_format($coachingModel->amount, 2) : number_format(0, 2) ?></td>
                </tr>
                <tr>
                    <td align="right">Discount</td>
                    <td align="right"><?= $studentTuitionModel->discount_id != '' ? number_format($studentTuitionModel->discount->amount, 2) : number_format(0, 2) ?></td>
                </tr>
                <tr>
                    <th>Final Tuition Fee</th>
                    <td align="right"><b><?= number_format($finalTuition, 2) ?></b></td>
                </tr>
                <?php if(!empty($coachingModel)){ ?>
                    <tr>
                        <th>Final Tuition Fee (with Coaching Icons)</th>
                        <td align="right"><b><?= number_format($finalTuition + $coachingModel->amount, 2) ?></b></td>
                    </tr>
                <?php } ?>
            </table>
            <h2>Payments and Balances</h2>
            <div class="x_title clearfix"></div>
            <table class="table table-bordered table-responsive">
                <tr>
                    <th>OR/PR</th>
                    <th>Date Paid</th>
                    <th>Code</th>
                    <th>Type</th>
                    <th>Amount</th>
                </tr>
                <?php if(!empty($payments)){ ?>
                    <?php foreach($payments as $payment){ ?>
                        <tr>
                            <td><?= $payment['or_no'] ?></td>
                            <td><?= $payment['datetime'] ?></td>
                            <td><?= $payment['code'] ?></td>
                            <td><?= $payment['amount_type'] ?></td>
                            <td align="right"><?= number_format($payment['amount'], 2) ?></td>
                        </tr>
                        <?php $paymentTotal += $payment['amount']; ?>
                    <?php } ?>
                <?php } ?>
                <tr>
                    <td align="right" colspan=4><b>Total Payments Made:</b></td>
                    <td align="right"><b><?= $paymentTotal < 0 ? '<font style="color: red">'.number_format($paymentTotal, 2).'</font>' : number_format($paymentTotal, 2) ?></b></td>
                </tr>
                <tr>
                    <td align="right" colspan=4><b>Unpaid Balance:</b></td>
                    <td align="right"><b>
                        <?php if($coachingModel){ ?>
                            <?php if(($finalTuition + $coachingModel->amount - $paymentTotal) > 0){ ?>
                                <?= '<font style="color: red">'.number_format(($finalTuition + $coachingModel->amount - $paymentTotal), 2).'</font>' ?>
                            <?php }else{ ?>
                                <?= number_format(($finalTuition + $coachingModel->amount - $paymentTotal), 2) ?>
                            <?php } ?>
                        <?php }else{ ?>
                            <?php if(($finalTuition - $paymentTotal) > 0){ ?>
                                <?= '<font style="color: red">'.number_format(($finalTuition - $paymentTotal), 2).'</font>' ?>
                            <?php }else{ ?>
                                <?= number_format(($finalTuition - $paymentTotal), 2) ?>
                            <?php } ?>
                        <?php } ?>
                    </b></td>
                </tr>
                <tr>
                    <td align="right" colspan=4><b>Balance Status:</b></td>
                    <td align="right"><b>
                        <?php if($coachingModel){ ?>
                            <?php if(($finalTuition + $coachingModel->amount - $paymentTotal) > 0){ ?>
                                <?= '<font style="color: red">With Balance</font>' ?>
                            <?php }else{ ?>
                                <?= 'Cleared' ?>
                            <?php } ?>
                        <?php }else{ ?>
                            <?php if(($finalTuition - $paymentTotal) > 0){ ?>
                                <?= '<font style="color: red">With Balance</font>' ?>
                            <?php }else{ ?>
                                <?= 'Cleared' ?>
                            <?php } ?>
                        <?php } ?>
                    </b></td>
                </tr>
            </table>
            <?php Panel::end(); ?>
        </div>
        <div class="col-md-8">
            <?php Panel::begin(['header' => 'New Season Details']); ?>
                <div class="row">
                    <div class="col-md-6">
                        <?= $this->render('_form',[
                            'newSeason' => $newSeason,
                            'newDiscountModel' => $newDiscountModel,
                            'newEnroleeTypeModel' => $newEnroleeTypeModel,
                            'newPackageStudentModel' => $newPackageStudentModel,
                            'newStudentTuitionModel' => $newStudentTuitionModel,
                            'newEnhancementModel' => $newEnhancementModel,
                            'newCoachingModel' => $newCoachingModel,
                            'newIncomeEnrolmentModel' => $newIncomeEnrolmentModel,
                            'newIncomeModel' => $newIncomeModel,
                            'discountModel' => $discountModel,
                            'seasons' => $seasons,
                            'enroleeTypes' => $enroleeTypes,
                            'packages' => $packages,
                            'discountTypes' => $discountTypes,
                            'coachingPackages' => $coachingPackages,
                            'incomeCodes' => $incomeCodes,
                            'current_or' => $current_or,
                            'orStatus' => $orStatus,
                            'paymentTotal' => $paymentTotal
                        ])?>
                    </div>
                    <div class="col-md-6">
                        <div id="season-information"></div>
                        <div id="package-information"></div>
                        <div id="coaching-information"></div>
                    </div>
                </div>
            <?php Panel::end(); ?>
        </div>
    </div>
    <?php Panel::end(); ?>
</div>
