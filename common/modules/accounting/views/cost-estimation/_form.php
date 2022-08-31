<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\ActiveField;
use kartik\select2\Select2;
use yii\web\JsExpression;
use yii\helpers\Url;
use yii\widgets\MaskedInput;
use dosamigos\datepicker\DatePicker;
use yii\web\View;
/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\IncomeEnrolment */
/* @var $form yii\widgets\ActiveForm */

?>

<div class="cost-estimation-form">
    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'season_id')->widget(Select2::classname(), [
        'data' => $seasons,
        'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'season-select'],
        'pluginOptions' => [
            'allowClear' =>  true,
        ],
        ]);
    ?>

    <div class="row">
        <div class="col-md-12 col-xs-12">
            <table class="table table-bordered table-condensed table-hover">
                <tbody>
                    <tr>
                        <th colspan=2>Total Gross</th>
                        <td colspan=2 align=right><b><div id="total-gross">0.00</div></b></td>
                    </tr>
                    <tr>
                        <th colspan=2>Expected Number of Students</th>
                        <td colspan=2 align=right><b><div id="expected-no-student">0</div></b></td>
                    </tr>
                    <?php if($enroleeTypes){ ?>
                        <?php $i =0; ?>
                        <?php foreach($enroleeTypes as $enroleeType){ ?>
                            <tr>
                                <td><?= $enroleeType->name ?></td>
                                <td><?= $form->field($incomeModels[$i], "[$i]quantity")->textInput(['type' => 'number', 'min' => 0, 'max' => '999', ])->label(false) ?></td>
                                <td><?= $form->field($incomeModels[$i], "[$i]unit_price")->widget(MaskedInput::classname(), [
                                    'clientOptions' => [
                                        'alias' =>  'decimal',
                                        'autoGroup' => true
                                    ],
                                    'options' => [
                                        
                                        'class' => 'form-control'
                                    ],
                                ])->label(false) ?></td>
                                <td align=right><div id="targetincome-<?= $i ?>-total">0.00</div></td>
                            </tr>
                            <?php $i++; ?>
                        <?php } ?>
                    <?php } ?>
                    <tr><td colspan=4>&nbsp;</td></tr>
                    <tr>
                        <td>Tax For Government</td>
                        <td>Less</td>
                        <td><p class="pull-right">/1.12*0.12</p></td>
                        <td align=right><div id="targettax-total">0.00</div></td>
                    </tr>
                    <tr><td colspan=4>&nbsp;</td></tr>
                    <tr style="background: #F7F7F7;">
                        <th colspan=3>Total Gross Income</th>
                        <td align=right><b><div id="total-gross-income">0.00</div></b></td>
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
                    <?php if(!empty($programModels)){ ?>
                        <?php foreach($programModels as $key => $programModel){ ?>
                            <tr>
                                <td><?= $programModel->label ?></td>
                                <td><?= $form->field($programModel, "[$key]quantity")->textInput(['type' => 'number', 'min' => 0, 'max' => '999', ])->label(false) ?></td>
                                <td><?= $form->field($programModel, "[$key]unit_price")->widget(MaskedInput::classname(), [
                                    'clientOptions' => [
                                        'alias' =>  'decimal',
                                        'autoGroup' => true
                                    ],
                                    'options' => [
                                        
                                        'class' => 'form-control'
                                    ],
                                ])->label(false) ?></td>
                                <td align=right><div id="targetprogram-<?= $key ?>-total">0.00</div></td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                    <tr style="background: #F7F7F7;">
                        <td align=right><b>Total</b></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td align=right><b><div id="targetprogram-total">0.00</div></b></td>
                    </tr>
                    <tr>
                        <td><b>Venue Rentals</b></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    <?php if(!empty($venueRentalModels)){ ?>
                        <?php foreach($venueRentalModels as $key => $venueRentalModel){ ?>
                            <tr>
                                <td><?= $venueRentalModel->label ?></td>
                                <td><?= $form->field($venueRentalModel, "[$key]quantity")->textInput(['type' => 'number', 'min' => 0, 'max' => '999', ])->label(false) ?></td>
                                <td><?= $form->field($venueRentalModel, "[$key]unit_price")->widget(MaskedInput::classname(), [
                                    'clientOptions' => [
                                        'alias' =>  'decimal',
                                        'autoGroup' => true
                                    ],
                                    'options' => [
                                        
                                        'class' => 'form-control'
                                    ],
                                ])->label(false) ?></td>
                                <td align=right><div id="targetvenuerental-<?= $key ?>-total">0.00</div></td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                    <tr style="background: #F7F7F7;">
                        <td align=right><b>Total</b></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td align=right><b><div id="targetvenuerental-total">0.00</div></b></td>
                    </tr>
                    <tr>
                        <th>Freebies</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                    </tr>
                    <?php if(!empty($freebies)){ ?>
                        <?php $i =0; ?>
                        <?php foreach($freebies as $freebie){ ?>
                            <tr>
                                <td><?= $freebie->name ?></td>
                                <td><?= $form->field($freebieModels[$i], "[$i]quantity")->textInput(['type' => 'number', 'min' => 0, 'max' => '999', ])->label(false) ?></td>
                                <td><?= $form->field($freebieModels[$i], "[$i]unit_price")->widget(MaskedInput::classname(), [
                                    'clientOptions' => [
                                        'alias' =>  'decimal',
                                        'autoGroup' => true
                                    ],
                                    'options' => [
                                        
                                        'class' => 'form-control'
                                    ],
                                ])->label(false) ?></td>
                                <td align=right><div id="targetfreebie-<?= $i ?>-total">0.00</div></td>
                            </tr>
                            <?php $i++; ?>
                        <?php } ?>
                    <?php } ?>
                    <tr style="background: #F7F7F7;">
                        <td align=right><b>Total</b></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td align=right><b><div id="targetfreebie-total">0.00</div></b></td>
                    </tr>
                    <tr>
                        <td><b>Review Materials</b></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>Estimated Budget</td>
                    </tr>
                    <?php if(!empty($venueRentalModels)){ ?>
                        <?php foreach($reviewModels as $key => $reviewModel){ ?>
                            <tr>
                                <td><?= $reviewModel->label ?></td>
                                <td><?= $form->field($reviewModel, "[$key]quantity")->textInput(['type' => 'number', 'min' => 0, 'max' => '999', ])->label(false) ?></td>
                                <td><?= $form->field($reviewModel, "[$key]unit_price")->widget(MaskedInput::classname(), [
                                    'clientOptions' => [
                                        'alias' =>  'decimal',
                                        'autoGroup' => true
                                    ],
                                    'options' => [
                                        
                                        'class' => 'form-control'
                                    ],
                                ])->label(false) ?></td>
                                <td align=right><div id="targetreview-<?= $key ?>-total">0.00</div></td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                    <tr style="background: #F7F7F7;">
                        <td align=right><b>Total</b></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td align=right><b><div id="targetreview-total">0.00</div></b></td>
                    </tr>
                    <tr>
                        <td><b>Food</b></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    <?php if(!empty($foodModels)){ ?>
                        <?php foreach($foodModels as $key => $foodModel){ ?>
                            <tr>
                                <td><?= $foodModel->label ?></td>
                                <td><?= $form->field($foodModel, "[$key]quantity")->textInput(['type' => 'number', 'min' => 0, 'max' => '999', ])->label(false) ?></td>
                                <td><?= $form->field($foodModel, "[$key]unit_price")->widget(MaskedInput::classname(), [
                                    'clientOptions' => [
                                        'alias' =>  'decimal',
                                        'autoGroup' => true
                                    ],
                                    'options' => [
                                        
                                        'class' => 'form-control'
                                    ],
                                ])->label(false) ?></td>
                                <td align=right><div id="targetfood-<?= $key ?>-total">0.00</div></td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                    <tr style="background: #F7F7F7;">
                        <td align=right><b>Total</b></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td align=right><b><div id="targetfood-total">0.00</div></b></td>
                    </tr>
                    <tr>
                        <td><b>Transportation</b></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>Estimated Budget</td>
                    </tr>
                    <?php if(!empty($transportationModels)){ ?>
                        <?php foreach($transportationModels as $key => $transportationModel){ ?>
                            <tr>
                                <td><?= $transportationModel->label ?></td>
                                <td><?= $form->field($transportationModel, "[$key]quantity")->textInput(['type' => 'number', 'min' => 0, 'max' => '999', ])->label(false) ?></td>
                                <td><?= $form->field($transportationModel, "[$key]unit_price")->widget(MaskedInput::classname(), [
                                    'clientOptions' => [
                                        'alias' =>  'decimal',
                                        'autoGroup' => true
                                    ],
                                    'options' => [
                                        
                                        'class' => 'form-control'
                                    ],
                                ])->label(false) ?></td>
                                <td align=right><div id="targettransportation-<?= $key ?>-total">0.00</div></td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                    <tr style="background: #F7F7F7;">
                        <td align=right><b>Total</b></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td align=right><b><div id="targettransportation-total">0.00</div></b></td>
                    </tr>
                    <tr>
                        <td><b>Staff Salary</b></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    <?php if(!empty($staffSalaryModels)){ ?>
                        <?php foreach($staffSalaryModels as $key => $staffSalaryModel){ ?>
                            <tr>
                                <td><?= $staffSalaryModel->label ?></td>
                                <td><?= $form->field($staffSalaryModel, "[$key]quantity")->textInput(['type' => 'number', 'min' => 0, 'max' => '999', ])->label(false) ?></td>
                                <td><?= $form->field($staffSalaryModel, "[$key]unit_price")->widget(MaskedInput::classname(), [
                                    'clientOptions' => [
                                        'alias' =>  'decimal',
                                        'autoGroup' => true
                                    ],
                                    'options' => [
                                        
                                        'class' => 'form-control'
                                    ],
                                ])->label(false) ?></td>
                                <td align=right><div id="targetstaffsalary-<?= $key ?>-total">0.00</div></td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                    <tr style="background: #F7F7F7;">
                        <td align=right><b>Total</b></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td align=right><b><div id="targetstaffsalary-total">0.00</div></b></td>
                    </tr>
                    <tr>
                        <td><b>Rebate</b></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    <?php if(!empty($rebateModels)){ ?>
                        <?php foreach($rebateModels as $key => $rebateModel){ ?>
                            <tr>
                                <td><?= $rebateModel->label ?></td>
                                <td><?= $form->field($rebateModel, "[$key]quantity")->textInput(['type' => 'number', 'min' => 0, 'max' => '999', ])->label(false) ?></td>
                                <td><?= $form->field($rebateModel, "[$key]unit_price")->widget(MaskedInput::classname(), [
                                    'clientOptions' => [
                                        'alias' =>  'decimal',
                                        'autoGroup' => true
                                    ],
                                    'options' => [
                                        
                                        'class' => 'form-control'
                                    ],
                                ])->label(false) ?></td>
                                <td align=right><div id="targetrebate-<?= $key ?>-total">0.00</div></td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                    <tr style="background: #F7F7F7;">
                        <td align=right><b>Total</b></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td align=right><b><div id="targetrebate-total">0.00</div></b></td>
                    </tr>
                    <tr>
                        <td><b>Utilities/month</b></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    <?php if(!empty($utilityModels)){ ?>
                        <?php foreach($utilityModels as $key => $utilityModel){ ?>
                            <tr>
                                <td><?= $utilityModel->label ?></td>
                                <td><?= $form->field($utilityModel, "[$key]quantity")->textInput(['type' => 'number', 'min' => 0, 'max' => '999', ])->label(false) ?></td>
                                <td><?= $form->field($utilityModel, "[$key]unit_price")->widget(MaskedInput::classname(), [
                                    'clientOptions' => [
                                        'alias' =>  'decimal',
                                        'autoGroup' => true
                                    ],
                                    'options' => [
                                        
                                        'class' => 'form-control'
                                    ],
                                ])->label(false) ?></td>
                                <td align=right><div id="targetutility-<?= $key ?>-total">0.00</div></td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                    <tr style="background: #F7F7F7;">
                        <td align=right><b>Total</b></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td align=right><b><div id="targetutility-total">0.00</div></b></td>
                    </tr>
                    <tr>
                        <td><b>Toprank Academic Activities</b></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    <?php if(!empty($academicModels)){ ?>
                        <?php foreach($academicModels as $key => $academicModel){ ?>
                            <tr>
                                <td><?= $academicModel->label ?></td>
                                <td><?= $form->field($academicModel, "[$key]quantity")->textInput(['type' => 'number', 'min' => 0, 'max' => '999', ])->label(false) ?></td>
                                <td><?= $form->field($academicModel, "[$key]unit_price")->widget(MaskedInput::classname(), [
                                    'clientOptions' => [
                                        'alias' =>  'decimal',
                                        'autoGroup' => true
                                    ],
                                    'options' => [
                                        
                                        'class' => 'form-control'
                                    ],
                                ])->label(false) ?></td>
                                <td align=right><div id="targetacademic-<?= $key ?>-total">0.00</div></td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                    <tr style="background: #F7F7F7;">
                        <td align=right><b>Total</b></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td align=right><b><div id="targetacademic-total">0.00</div></b></td>
                    </tr>
                    <?php if(!empty($emergencyFundModels)){ ?>
                        <?php foreach($emergencyFundModels as $key => $emergencyFundModel){ ?>
                            <tr>
                                <td><?= $emergencyFundModel->label ?></td>
                                <td><?= $form->field($emergencyFundModel, "[$key]quantity")->textInput(['type' => 'number', 'min' => 0, 'max' => '999', ])->label(false) ?></td>
                                <td><?= $form->field($emergencyFundModel, "[$key]unit_price")->widget(MaskedInput::classname(), [
                                    'clientOptions' => [
                                        'alias' =>  'decimal',
                                        'autoGroup' => true
                                    ],
                                    'options' => [
                                        
                                        'class' => 'form-control'
                                    ],
                                ])->label(false) ?></td>
                                <td align=right><div id="targetemergencyfund-<?= $key ?>-total">0.00</div></td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                    <tr style="background: #F7F7F7;">
                        <td align=right><b>Total</b></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td align=right><b><div id="targetemergencyfund-total">0.00</div></b></td>
                    </tr>
                    <tr><td colspan=4>&nbsp;</td></tr>
                    <tr style="background: #F7F7F7;">
                        <td><b>Total Expenses</b></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td align=right><b><div id="total-expenses">0.00</div></b></td>
                    </tr>
                    <tr><td colspan=4>&nbsp;</td></tr>
                    <tr>
                        <td align=right><b>Total Gross</b></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td align=right><b><div id="final-total-gross">0.00</div></b></td>
                    </tr>
                    <tr>
                        <td align=right><b>Total Expenses</b></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td align=right><b><div id="final-total-expenses">0.00</div></b></td>
                    </tr>
                    <tr style="background: #F7F7F7;">
                        <td align=right><b>Expected Income</b></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td align=right><b><div id="expected-income">0.00</div></b></td>
                    </tr>
                    <tr><td colspan=4>&nbsp;</td></tr>
                    <?php if(!empty($royaltyFeeModels)){ ?>
                        <?php foreach($royaltyFeeModels as $key => $royaltyFeeModel){ ?>
                            <tr>
                                <td><b>Royalty Fee</b></td>
                                <td>&nbsp;</td>
                                <td><?= $form->field($royaltyFeeModel, "[$key]percentage")->widget(MaskedInput::classname(), [
                                    'clientOptions' => [
                                        'alias' =>  'decimal',
                                        'autoGroup' => true
                                    ],
                                    'options' => [
                                        
                                        'class' => 'form-control'
                                    ],
                                ])->label(false) ?></td>
                                <td align=right><div id="targetroyaltyfee-<?= $key ?>-total">0.00</div></td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                    <tr style="background: #F7F7F7;">
                        <td align=right><b>NET INCOME</b></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td align=right><b><div id="net-income">0.00</div></b></td>
                    </tr>
                </tbody>
            </table>
            <p class="pull-right"><?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?></p>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>
<?php
        $script = '
            $( document ).ready(function() {

                function getTotal(quantity, unit_price)
                {
                    var total = parseInt(quantity) * parseFloat(unit_price);

                    return total.toFixed(2);
                }

                function compute()
                {
                    var student = 0;
                    var gross = 0;
                    var tax = 0;
                    var program = 0;
                    var venue = 0;
                    var freebie = 0;
                    var review = 0;
                    var food = 0;
                    var transportation = 0;
                    var staffsalary = 0;
                    var rebate = 0;
                    var utility = 0;
                    var academic = 0;
                    var emergency = 0;
                    var royalty = 0;
                    var final_gross = 0;
                    var final_expense = 0;
                    var expected_income = 0;
                    var net_income = 0;

                    for(var i = 0; i < '.count($incomeModels).'; i++)
                    {
                        student += parseInt($("#targetincome-"+ i +"-quantity").val());

                        var total = getTotal($("#targetincome-"+ i +"-quantity").val(), $("#targetincome-"+ i +"-unit_price").val());
                        $("#targetincome-"+ i +"-total").html(total);

                        gross = parseFloat(gross) + parseFloat(total);
                    }

                    $("#expected-no-student").html(student);
                    $("#total-gross").html(gross.toFixed(2));

                    tax = (gross/1.12)*0.12;
                    $("#targettax-total").html(tax.toFixed(2));

                    final_gross = parseFloat(gross) - parseFloat(tax);

                    $("#total-gross-income").html(final_gross.toFixed(2));

                    for(var i = 0; i < '.count($programModels).'; i++)
                    {
                        var total = getTotal($("#targetprogram-"+ i +"-quantity").val(), $("#targetprogram-"+ i +"-unit_price").val());
                        $("#targetprogram-"+ i +"-total").html(total);

                        program = parseFloat(program) + parseFloat(total);
                    }

                    $("#targetprogram-total").html(program.toFixed(2));

                    for(var i = 0; i < '.count($venueRentalModels).'; i++)
                    {
                        var total = getTotal($("#targetvenuerental-"+ i +"-quantity").val(), $("#targetvenuerental-"+ i +"-unit_price").val());
                        $("#targetvenuerental-"+ i +"-total").html(total);

                        venue = parseFloat(venue) + parseFloat(total);
                    }

                    $("#targetvenuerental-total").html(venue.toFixed(2));

                    for(var i = 0; i < '.count($freebieModels).'; i++)
                    {
                        var total = getTotal($("#targetfreebie-"+ i +"-quantity").val(), $("#targetfreebie-"+ i +"-unit_price").val());
                        $("#targetfreebie-"+ i +"-total").html(total);

                        freebie = parseFloat(freebie) + parseFloat(total);
                    }

                    $("#targetfreebie-total").html(freebie.toFixed(2));

                    for(var i = 0; i < '.count($reviewModels).'; i++)
                    {
                        var total = getTotal($("#targetreview-"+ i +"-quantity").val(), $("#targetreview-"+ i +"-unit_price").val());
                        $("#targetreview-"+ i +"-total").html(total);

                        review = parseFloat(review) + parseFloat(total);
                    }

                    $("#targetreview-total").html(review.toFixed(2));

                    for(var i = 0; i < '.count($foodModels).'; i++)
                    {
                        var total = getTotal($("#targetfood-"+ i +"-quantity").val(), $("#targetfood-"+ i +"-unit_price").val());
                        $("#targetfood-"+ i +"-total").html(total);

                        food = parseFloat(food) + parseFloat(total);
                    }

                    $("#targetfood-total").html(food.toFixed(2));

                    for(var i = 0; i < '.count($transportationModels).'; i++)
                    {
                        var total = getTotal($("#targettransportation-"+ i +"-quantity").val(), $("#targettransportation-"+ i +"-unit_price").val());
                        $("#targettransportation-"+ i +"-total").html(total);

                        transportation = parseFloat(transportation) + parseFloat(total);
                    }

                    $("#targettransportation-total").html(transportation.toFixed(2));

                    for(var i = 0; i < '.count($staffSalaryModels).'; i++)
                    {
                        var total = getTotal($("#targetstaffsalary-"+ i +"-quantity").val(), $("#targetstaffsalary-"+ i +"-unit_price").val());
                        $("#targetstaffsalary-"+ i +"-total").html(total);

                        staffsalary = parseFloat(staffsalary) + parseFloat(total);
                    }

                    $("#targetstaffsalary-total").html(staffsalary.toFixed(2));

                    for(var i = 0; i < '.count($rebateModels).'; i++)
                    {
                        var total = getTotal($("#targetrebate-"+ i +"-quantity").val(), $("#targetrebate-"+ i +"-unit_price").val());
                        $("#targetrebate-"+ i +"-total").html(total);

                        rebate = parseFloat(rebate) + parseFloat(total);
                    }

                    $("#targetrebate-total").html(rebate.toFixed(2));

                    for(var i = 0; i < '.count($utilityModels).'; i++)
                    {
                        var total = getTotal($("#targetutility-"+ i +"-quantity").val(), $("#targetutility-"+ i +"-unit_price").val());
                        $("#targetutility-"+ i +"-total").html(total);

                        utility = parseFloat(utility) + parseFloat(total);
                    }

                    $("#targetutility-total").html(utility.toFixed(2));

                    for(var i = 0; i < '.count($academicModels).'; i++)
                    {
                        var total = getTotal($("#targetacademic-"+ i +"-quantity").val(), $("#targetacademic-"+ i +"-unit_price").val());
                        $("#targetacademic-"+ i +"-total").html(total);

                        academic = parseFloat(academic) + parseFloat(total);
                    }

                    $("#targetacademic-total").html(academic.toFixed(2));

                    for(var i = 0; i < '.count($emergencyFundModels).'; i++)
                    {
                        var total = getTotal($("#targetemergencyfund-"+ i +"-quantity").val(), $("#targetemergencyfund-"+ i +"-unit_price").val());
                        $("#targetemergencyfund-"+ i +"-total").html(total);

                        emergency = parseFloat(emergency) + parseFloat(total);
                    }

                    $("#targetemergencyfund-total").html(emergency.toFixed(2));

                    $("#final-total-gross").html(final_gross.toFixed(2));

                    final_expense = parseFloat(program) + parseFloat(venue) + parseFloat(freebie) + parseFloat(review) + parseFloat(food) + parseFloat(transportation) + parseFloat(staffsalary) + parseFloat(rebate) + parseFloat(utility) + parseFloat(academic) + + parseFloat(emergency);

                    $("#total-expenses").html(final_expense.toFixed(2));
                    $("#final-total-expenses").html(final_expense.toFixed(2));

                    expected_income = parseFloat(final_gross) - parseFloat(final_expense);
                    $("#expected-income").html(expected_income.toFixed(2));

                    for(var i = 0; i < '.count($royaltyFeeModels).'; i++)
                    {
                        var total = parseFloat($("#targetroyaltyfee-"+ i +"-percentage").val()) * expected_income;
                        $("#targetroyaltyfee-"+ i +"-total").html(total.toFixed(2));

                        royalty = parseFloat(royalty) + parseFloat(total);
                    }

                    net_income = parseFloat(expected_income) - parseFloat(royalty);
                    
                    $("#net-income").html(net_income.toFixed(2));
                }

                var student = 0;
                var gross = 0;
                var tax = 0;
                var program = 0;
                var venue = 0;
                var freebie = 0;
                var review = 0;
                var food = 0;
                var transportation = 0;
                var staffsalary = 0;
                var rebate = 0;
                var utility = 0;
                var academic = 0;
                var royalty = 0;
                var emergency = 0;
                var final_gross = 0;
                var final_expense = 0;
                var expected_income = 0;
                var net_income = 0;

                for(var i = 0; i < '.count($incomeModels).'; i++)
                {
                    student += parseInt($("#targetincome-"+ i +"-quantity").val());

                    var total = getTotal($("#targetincome-"+ i +"-quantity").val(), $("#targetincome-"+ i +"-unit_price").val());
                    $("#targetincome-"+ i +"-total").html(total);

                    gross = parseFloat(gross) + parseFloat(total);

                    $("#targetincome-"+ i +"-quantity").on("keyup", function(e){
                        compute();
                    });

                    $("#targetincome-"+ i +"-unit_price").on("keyup", function(e){
                        compute();
                    });
                }

                $("#expected-no-student").html(student);
                $("#total-gross").html(gross);

                tax = (gross/1.12)*0.12;
                $("#targettax-total").html(tax.toFixed(2));
                final_gross = parseFloat(gross) - parseFloat(tax);
                $("#total-gross-income").html(final_gross.toFixed(2));

                for(var i = 0; i < '.count($programModels).'; i++)
                {
                    var total = getTotal($("#targetprogram-"+ i +"-quantity").val(), $("#targetprogram-"+ i +"-unit_price").val());
                    $("#targetprogram-"+ i +"-total").html(total);

                    program = parseFloat(program) + parseFloat(total);

                    $("#targetprogram-"+ i +"-quantity").on("keyup", function(e){
                        compute();
                    });

                    $("#targetprogram-"+ i +"-unit_price").on("keyup", function(e){
                        compute();
                    });
                }

                $("#targetprogram-total").html(program.toFixed(2));

                for(var i = 0; i < '.count($venueRentalModels).'; i++)
                {
                    var total = getTotal($("#targetvenuerental-"+ i +"-quantity").val(), $("#targetvenuerental-"+ i +"-unit_price").val());
                    $("#targetvenuerental-"+ i +"-total").html(total);

                    venue = parseFloat(venue) + parseFloat(total);

                    $("#targetvenuerental-"+ i +"-quantity").on("keyup", function(e){
                        compute();
                    });

                    $("#targetvenuerental-"+ i +"-unit_price").on("keyup", function(e){
                        compute();
                    });
                }

                $("#targetvenuerental-total").html(venue.toFixed(2));

                for(var i = 0; i < '.count($freebieModels).'; i++)
                {
                    var total = getTotal($("#targetfreebie-"+ i +"-quantity").val(), $("#targetfreebie-"+ i +"-unit_price").val());
                    $("#targetfreebie-"+ i +"-total").html(total);

                    freebie = parseFloat(freebie) + parseFloat(total);

                    $("#targetfreebie-"+ i +"-quantity").on("keyup", function(e){
                        compute();
                    });

                    $("#targetfreebie-"+ i +"-unit_price").on("keyup", function(e){
                        compute();
                    });
                }

                $("#targetfreebie-total").html(freebie.toFixed(2));

                for(var i = 0; i < '.count($reviewModels).'; i++)
                {
                    var total = getTotal($("#targetreview-"+ i +"-quantity").val(), $("#targetreview-"+ i +"-unit_price").val());
                    $("#targetreview-"+ i +"-total").html(total);

                    review = parseFloat(review) + parseFloat(total);

                    $("#targetreview-"+ i +"-quantity").on("keyup", function(e){
                        compute();
                    });

                    $("#targetreview-"+ i +"-unit_price").on("keyup", function(e){
                        compute();
                    });
                }

                $("#targetreview-total").html(review.toFixed(2));

                for(var i = 0; i < '.count($foodModels).'; i++)
                {
                    var total = getTotal($("#targetfood-"+ i +"-quantity").val(), $("#targetfood-"+ i +"-unit_price").val());
                    $("#targetfood-"+ i +"-total").html(total);

                    food = parseFloat(food) + parseFloat(total);

                    $("#targetfood-"+ i +"-quantity").on("keyup", function(e){
                        compute();
                    });

                    $("#targetfood-"+ i +"-unit_price").on("keyup", function(e){
                        compute();
                    });
                }

                $("#targetfood-total").html(food.toFixed(2));

                for(var i = 0; i < '.count($transportationModels).'; i++)
                {
                    var total = getTotal($("#targettransportation-"+ i +"-quantity").val(), $("#targettransportation-"+ i +"-unit_price").val());
                    $("#targettransportation-"+ i +"-total").html(total);

                    transportation = parseFloat(transportation) + parseFloat(total);

                    $("#targettransportation-"+ i +"-quantity").on("keyup", function(e){
                        compute();
                    });

                    $("#targettransportation-"+ i +"-unit_price").on("keyup", function(e){
                        compute();
                    });
                }

                $("#targettransportation-total").html(transportation.toFixed(2));

                for(var i = 0; i < '.count($staffSalaryModels).'; i++)
                {
                    var total = getTotal($("#targetstaffsalary-"+ i +"-quantity").val(), $("#targetstaffsalary-"+ i +"-unit_price").val());
                    $("#targetstaffsalary-"+ i +"-total").html(total);

                    staffsalary = parseFloat(staffsalary) + parseFloat(total);

                    $("#targetstaffsalary-"+ i +"-quantity").on("keyup", function(e){
                        compute();
                    });

                    $("#targetstaffsalary-"+ i +"-unit_price").on("keyup", function(e){
                        compute();
                    });
                }

                $("#targetstaffsalary-total").html(staffsalary.toFixed(2));

                for(var i = 0; i < '.count($rebateModels).'; i++)
                {
                    var total = getTotal($("#targetrebate-"+ i +"-quantity").val(), $("#targetrebate-"+ i +"-unit_price").val());
                    $("#targetrebate-"+ i +"-total").html(total);

                    rebate = parseFloat(rebate) + parseFloat(total);

                    $("#targetrebate-"+ i +"-quantity").on("keyup", function(e){
                        compute();
                    });

                    $("#targetrebate-"+ i +"-unit_price").on("keyup", function(e){
                        compute();
                    });
                }

                $("#targetrebate-total").html(rebate.toFixed(2));

                for(var i = 0; i < '.count($utilityModels).'; i++)
                {
                    var total = getTotal($("#targetutility-"+ i +"-quantity").val(), $("#targetutility-"+ i +"-unit_price").val());
                    $("#targetutility-"+ i +"-total").html(total);

                    utility = parseFloat(utility) + parseFloat(total);

                    $("#targetutility-"+ i +"-quantity").on("keyup", function(e){
                        compute();
                    });

                    $("#targetutility-"+ i +"-unit_price").on("keyup", function(e){
                        compute();
                    });
                }

                $("#targetutility-total").html(utility.toFixed(2));

                for(var i = 0; i < '.count($academicModels).'; i++)
                {
                    var total = getTotal($("#targetacademic-"+ i +"-quantity").val(), $("#targetacademic-"+ i +"-unit_price").val());
                    $("#targetacademic-"+ i +"-total").html(total);

                    academic = parseFloat(academic) + parseFloat(total);

                    $("#targetacademic-"+ i +"-quantity").on("keyup", function(e){
                        compute();
                    });

                    $("#targetacademic-"+ i +"-unit_price").on("keyup", function(e){
                        compute();
                    });
                }

                $("#targetacademic-total").html(academic.toFixed(2));

                for(var i = 0; i < '.count($emergencyFundModels).'; i++)
                {
                    var total = getTotal($("#targetemergencyfund-"+ i +"-quantity").val(), $("#targetemergencyfund-"+ i +"-unit_price").val());
                    $("#targetemergencyfund-"+ i +"-total").html(total);

                    emergency = parseFloat(emergency) + parseFloat(total);

                    $("#targetemergencyfund-"+ i +"-quantity").on("keyup", function(e){
                        compute();
                    });

                    $("#targetemergencyfund-"+ i +"-unit_price").on("keyup", function(e){
                        compute();
                    });
                }

                $("#targetemergencyfund-total").html(emergency.toFixed(2));

                $("#final-total-gross").html(final_gross.toFixed(2));

                final_expense = parseFloat(program) + parseFloat(venue) + parseFloat(freebie) + parseFloat(review) + parseFloat(food) + parseFloat(transportation) + parseFloat(staffsalary) + parseFloat(rebate) + parseFloat(utility) + parseFloat(academic) + + parseFloat(emergency);

                $("#total-expenses").html(final_expense.toFixed(2));
                $("#final-total-expenses").html(final_expense.toFixed(2));

                expected_income = parseFloat(final_gross) - parseFloat(final_expense);
                $("#expected-income").html(expected_income.toFixed(2));

                for(var i = 0; i < '.count($royaltyFeeModels).'; i++)
                {
                    var total = parseFloat($("#targetroyaltyfee-"+ i +"-percentage").val()) * parseFloat(expected_income);
                    $("#targetroyaltyfee-"+ i +"-total").html(total.toFixed(2));

                    royalty = parseFloat(royalty) + parseFloat(total);

                    $("#targetroyaltyfee-"+ i +"-percentage").on("keyup", function(e){
                        compute();
                    });
                }

                net_income = parseFloat(expected_income) - parseFloat(royalty);
                    
                $("#net-income").html(net_income.toFixed(2));
            });
';
$this->registerJs($script);
   
?>