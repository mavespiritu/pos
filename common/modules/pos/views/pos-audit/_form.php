<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\MaskedInput;
/* @var $this yii\web\View */
/* @var $model common\modules\pos\models\PosAudit */
/* @var $form yii\widgets\ActiveForm */

?>

<div class="pos-audit-form">

    <?php $form = ActiveForm::begin(); ?>
    <div class="box box-danger">
        <div class="box-header with-border">
            <p style="font-size: 14px;">
                SEASON: <b><?= $season != 0 ? $selectedSeason->seasonName : 'ALL' ?></b><br>
                DATE: <b><?= $date ?></b><br>
                CUTOFF: <b><?= $cutoff['start'].' - '.$cutoff['end'] ?></b>
            </p>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-6 col-xs-12">
                    <?= $season != 0 ? $date == $cutoff['start'] ? $form->field($beginningAmountModel['CASH'], "[CASH]amount")->widget(MaskedInput::classname(), [
                        'clientOptions' => [
                            'alias' =>  'decimal',
                            'autoGroup' => true
                        ],
                    ])->label('Starting Amount (CASH)') : '' : '' ?>
                </div>
                <div class="col-md-6 col-xs-12">
                    <?= $season != 0 ? $date == $cutoff['start'] ? $form->field($beginningAmountModel['NON-CASH'], "[NON-CASH]amount")->widget(MaskedInput::classname(), [
                        'clientOptions' => [
                            'alias' =>  'decimal',
                            'autoGroup' => true
                        ],
                    ])->label('Starting Amount (NON-CASH)') : '' : '' ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 col-xs-12">
                    <h4>Transaction History</h4>
                    <?php if(!empty($data)){ ?>
                        <?php foreach($data as $key => $datum){ ?>
                            <?php $incomeTodayTotal = 0; ?>
                            <?php $expenseTodayTotal = 0; ?>
                            <div class="box box-danger">
                                <div class="box-header with-border">
                                    <p style="font-size: 14px;" class="text-center"><b><?= $key ?> BASIS</b></p>
                                </div>
                                <div class="box-body">
                                    <p><b>Beginning Amount (<?= $key ?>): 
                                        <?php $totalIncome = 0; ?>
                                        <?php $totalExpense = 0; ?>
                                        <?php $totalIncome = isset($data[$key]['Yesterday']['Income']['total']) ? $data[$key]['Yesterday']['Income']['total'] : 0; ?>
                                        <?php $totalExpense = isset($data[$key]['Yesterday']['Expenses']['total']) ? $data[$key]['Yesterday']['Expenses']['total'] : 0; ?>
                                        <?php $startingAmount = ($data[$key]['BeginningAmount'] + $totalIncome) - $totalExpense; ?>
                                        <?= $startingAmount <= 0 ? '<span class="pull-right" style="font-size: 20px; color: red;">'.number_format($startingAmount, 2).'</span>' : '<span class="pull-right" style="font-size: 20px;">'.number_format($startingAmount, 2).'</span>'; ?>
                                    </b></p><br>
                                    <p class="text-center"><b>INCOME SUMMARY</b></p>
                                    <table class="table table-condensed table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>CATEGORY</th>
                                                <th>TOTAL</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if(isset($datum['Today']['Income'])){ ?>
                                                <?php if(!empty($datum['Today']['Income'])){ ?>
                                                    <?php foreach($datum['Today']['Income'] as $dt){ ?>
                                                        <tr>
                                                            <td><?= $dt['incomeType'] ?></td>
                                                            <td align=right><?= number_format($dt['total'], 2) ?></td>
                                                        </tr>
                                                        <?php $incomeTodayTotal+= $dt['total'] ?>
                                                    <?php } ?>
                                                <?php }else{ ?>
                                                    <tr>
                                                        <td colspan=2>No income recorded.</td>
                                                    </tr>
                                                <?php } ?>
                                            <?php }else{ ?>
                                                <tr>
                                                    <td colspan=2>No income recorded.</td>
                                                </tr>
                                            <?php } ?>
                                            <tr>
                                                <td align=right><b>TOTAL</b></td>
                                                <td align=right><b><?= number_format($incomeTodayTotal, 2) ?></b></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <br>
                                    <br>
                                    <br>
                                    <p class="text-center"><b>EXPENSE SUMMARY</b></p>
                                    <table class="table table-condensed table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>CATEGORY</th>
                                                <th>TOTAL</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if(isset($datum['Today']['Expenses'])){ ?>
                                                <?php if(!empty($datum['Today']['Expenses'])){ ?>
                                                    <?php foreach($datum['Today']['Expenses'] as $dt){ ?>
                                                        <tr>
                                                            <td><?= $dt['vendor'] ?></td>
                                                            <td align=right><?= number_format($dt['total'], 2) ?></td>
                                                        </tr>
                                                        <?php $expenseTodayTotal += $dt['total'] ?>
                                                    <?php } ?>
                                                <?php }else{ ?>
                                                    <tr>
                                                        <td colspan=2>No expense recorded.</td>
                                                    </tr>
                                                <?php } ?>
                                            <?php }else{ ?>
                                                <tr>
                                                    <td colspan=2>No expense recorded.</td>
                                                </tr>
                                            <?php } ?>
                                            <tr>
                                                <td align=right><b>TOTAL</b></td>
                                                <td align=right><b><?= number_format($expenseTodayTotal, 2) ?></b></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <br>
                                    <p><b>Ending Amount (<?= $key ?>): 
                                        <?php $endingAmount = ($startingAmount + $incomeTodayTotal) - $expenseTodayTotal; ?> 
                                        <?= $endingAmount <= 0 ? '<span class="pull-right" style="font-size: 20px; color: red;">'.number_format($endingAmount, 2).'</span>' : '<span class="pull-right" style="font-size: 20px;">'.number_format($endingAmount, 2).'</span>'; ?>
                                    </b></p><br>
                                </div>
                            </div>
                        <?php } ?>
                    <?php }else{ ?>
                        <p>No transactions made. You can do better.</p>
                    <?php } ?>
                </div>
                <?php if($season != 0){ ?>
                    <div class="col-md-6 col-xs-12">
                        <h4>Auditing Form</h4>
                        <table class="table table-condensed table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Denomination</th>
                                    <th>Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($denominations){ ?>
                                    <?php foreach($denominations as $key => $denomination){ ?>
                                        <tr>
                                            <td align=center><?= $denomination->title ?></td>
                                            <td><?= $form->field($models[$key], "[$key]total")->widget(MaskedInput::classname(), [
                                                'clientOptions' => [
                                                    'alias' =>  'decimal',
                                                    'autoGroup' => true
                                                ],
                                            ])->label(false) ?>

                                            <?= $form->field($models[$key], "[$key]denomination_id")->hiddenInput(['value' => $denomination->id])->label(false); ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                <?php } ?>
                            </tbody>
                        </table>
                        <div class="form-group pull-right">
                            <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

</div>
