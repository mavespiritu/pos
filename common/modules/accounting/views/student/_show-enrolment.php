<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\select2\Select2; // or kartik\select2\Select2
use yii\web\JsExpression;
use dosamigos\datepicker\DatePicker;
use yii\widgets\MaskedInput;
use yii\bootstrap\Modal;
use yii\web\View;
use yiister\gentelella\widgets\Panel;
/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\Student */
/* @var $form yii\widgets\ActiveForm */
$paymentTotal = 0;
?>

<div class="enrolment-form">
    <?php Panel::begin(['header' => $model['seasonName']]) ?>
        
                <h2>Enrolment Details</h2>
                <table class="table table-responsive table-condensed table-bordered">
                    <tr>
                        <td><b>Enrolee Type</b></td>
                        <td><?= $model['enroleeTypeName'] ?></td>
                    </tr>
                    <tr>
                        <td><b>Package</b></td>
                        <td><?= $model['packageName'] ?></td>
                    </tr>
                    <tr>
                        <td><b>Discount Type</b></td>
                        <td><?= $model['discountType'] ?></td>
                    </tr>
                    <tr>
                        <td><b>Discount Fee</b></td>
                        <td><?= number_format($model['discountAmount'], 2) ?></td>
                    </tr>
                    <tr>
                        <td><b>Coaching Amount</b></td>
                        <td><?= number_format($model['coachingAmount'], 2) ?></td>
                    </tr>
                    <?php if($model['dropoutId'] != ''){ ?>
                        <tr>
                            <td><b>Date Dropped</b></td>
                            <td><?= $model['dropDate'] ?></td>
                        </tr>
                        <tr>
                            <td><b>Reason For Dropping</b></td>
                            <td><?= Html::encode($model['reason']) ?></td>
                        </tr>
                        <tr>
                            <td><b>Authorized By</b></td>
                            <td><?= $model['authorized_by'] ?></td>
                        </tr>
                    <?php } ?>
                </table>
        
                <h2>Tuition</h2>
                <table class="table table-bordered table-responsive">
                    <tr>
                        <td>Regular Review Price</td>
                        <td align="right"><?= number_format($model['packageAmount'], 2) ?></td>
                    </tr>
                    <tr>
                        <td>Enhancement Price</td>
                        <td align="right"><?= number_format($model['enhancementAmount'], 2) ?></td>
                    </tr>
                    <tr>
                        <td>Total Tuition</td>
                        <td align="right"><?= number_format(($model['packageAmount'] + $model['enhancementAmount']), 2) ?></td>
                    </tr>
                    <tr>
                        <td>Coaching With Icons</td>
                        <td align="right"><?= number_format($model['coachingAmount'], 2) ?></td>
                    </tr>
                    <tr>
                        <td align="right">Discount</td>
                        <td align="right"><?= number_format($model['discountAmount'], 2) ?></td>
                    </tr>
                    <tr>
                        <th>Final Tuition Fee</th>
                        <td align="right"><b><?= number_format($model['finalTuitionFee'] - $model['coachingAmount'], 2) ?></b></td>
                    </tr>
                    <?php if(!empty($coaching)){ ?>
                        <tr>
                            <th>Final Tuition Fee (with Coaching Icons)</th>
                            <td align="right"><b><?= number_format($model['finalTuitionFee'], 2) ?></b></td>
                        </tr>
                    <?php } ?>
                </table>

                <h2>Payments and Balances</h2>
                <table class="table table-bordered table-responsive">
                    <tr>
                        <th>OR/PR</th>
                        <th>Description</th>
                        <th>Amount Type</th>
                        <th>Date Paid</th>
                        <th>Amount</th>
                    </tr>
                    <?php if(!empty($payments)){ ?>
                        <?php foreach($payments as $payment){ ?>
                            <tr>
                                <td><?= $payment['or_no'] ?></td>
                                <td><?= $payment['code'] ?></td>
                                <td><?= $payment['amountType'] ?></td>
                                <td><?= $payment['datetime'] ?></td>
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
                        <td align="right"><b><?= ($model['finalTuitionFee'] - $paymentTotal) > 0 ? '<font style="color: red">'.number_format(($model['finalTuitionFee'] - $paymentTotal), 2).'</font>' : number_format(($model['finalTuitionFee'] - $paymentTotal), 2) ?></b></td>
                    </tr>
                    <tr>
                        <td align="right" colspan=4><b>Balance Status:</b></td>
                        <td align="right"><b><?= ($model['finalTuitionFee'] - $paymentTotal) > 0 ? '<font style="color: red">With Balance</font>' : 'Cleared' ?></b></td>
                    </tr>
                </table>
    <?php Panel::end() ?>
</div>




