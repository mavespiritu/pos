<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\MaskedInput;
use yii\web\JsExpression;
use yii\helpers\Url;
use yiister\gentelella\widgets\Panel;
/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\Audit */
/* @var $form yii\widgets\ActiveForm */
?>
<?php 
	function colorEnrolees($percent)
	{
		$color = '';
		if($percent < 50)
		{
			$color = 'danger';
		}else{
			$color = 'success';
		}

		return $color;
	}

	function colorExpenses($percent)
	{
		$color = '';
		if($percent < 75)
		{
			$color = 'success';
		}else{
			$color = 'danger';
		}

		return $color;
	}
?>
<?php Panel::begin(); ?>
<div class="audit-form">
	<?php $form = ActiveForm::begin(['enableClientValidation' => true, 'id' => 'audit-form']); ?>
	<h3>Daily Auditing Form</h3><hr>
	<div class="row">
		<div class="col-md-6">
			<table style="width: 100%;">
				<tr>
					<td style="width: 20%;">Branch - Program:</td>
					<td><b><?= $branchProgram->branchProgramName ?></b></td>
				</tr>
				<tr>
					<td style="width: 20%;">Season:</td>
					<td><b><?= $season->seasonName ?></b></td>
				</tr>
				<tr>
					<td style="width: 20%;">Date:</td>
					<td><b><?= $id ?></b></td>
				</tr>
				<tr>
					<td>Cut-Off:</td>
					<td><b><?= $cutoff['start'].' - '.$cutoff['end'] ?></b></td>
				</tr>
			</table>
		</div>
	</div>
	<br>
	<div class="row">
		<div class="col-md-6">
			<?php Panel::begin(['header' => 'Cash On Hand']); ?>
				<table style="width: 100%">
					<tr>
						<td>Beginning Cash On Hand:</td>
						<td align=right>
							<?php if($id == $cutoff['start']){ ?>
								<?= $form->field($beginningcoh, 'cash_on_hand')->widget(MaskedInput::classname(), [
								        'clientOptions' => [
								            'alias' =>  'decimal',
								            'autoGroup' => true
								        ],
								    ])->label('<br>') ?>
							<?php }else{ ?>
								<?= $beginning_coh_cash > 0 ? '<b>'.number_format($beginning_coh_cash, 2).'</b>' : '<b><font color="red">'.number_format($beginning_coh_cash, 2).'</font></b>' ?>
							<?php } ?>
						</td>
					</tr>
				</table>
				<br>
				<table class="table table-bordered table-condensed jambo_table">
					<thead>
						<tr>
							<th>INCOME</th>
							<th>AMOUNT</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>Enrolments</td>
							<td align=right><?= number_format($dataOnCashStartToEndDay['incomeEnrolmentTotal'], 2) ?></td>
						</tr>
						<tr>
							<td>Freebies and Icons</td>
							<td align=right><?= number_format($dataOnCashStartToEndDay['freebiesTotal'], 2) ?></td>
						</tr>
						<tr>
							<td>BP and Others</td>
							<td align=right><?= number_format($dataOnCashStartToEndDay['budgetProposalTotal'], 2) ?></td>
						</tr>
					</tbody>
					<tfoot>
						<tr>
							<td align=right><b>TOTAL</b></td>
							<td align=right><b><?= number_format($total_income_cash, 2)?></b></td>
						</tr>
					</tfoot>
				</table>
				<br>
				<table class="table table-bordered table-condensed jambo_table">
					<thead>
						<tr>
							<th>EXPENSES</th>
							<th>AMOUNT</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>Petty Expenses</td>
							<td align=right><?= number_format($dataOnCashStartToEndDay['pettyExpenseTotal'], 2) ?></td>
						</tr>
						<tr>
							<td>Photocopy Expenses</td>
							<td align=right><?= number_format($dataOnCashStartToEndDay['photocopyExpenseTotal'], 2) ?></td>
						</tr>
						<tr>
							<td>Operating Expenses</td>
							<td align=right><?= number_format($dataOnCashStartToEndDay['otherExpenseTotal'], 2) ?></td>
						</tr>
						<tr>
							<td>Other Expenses</td>
							<td align=right><?= number_format($dataOnCashStartToEndDay['operatingExpenseTotal'], 2) ?></td>
						</tr>
						<tr>
							<td>Branch Transfers</td>
							<td align=right><?= number_format($dataOnCashStartToEndDay['branchTransferTotal'], 2) ?></td>
						</tr>
					</tbody>
					<tfoot>
						<tr>
							<td align=right><b>TOTAL</b></td>
							<td align=right><b><?= number_format($total_expenses_cash - $dataOnCashStartToEndDay['bankDepositsTotal'], 2)?></b></td>
						</tr>
					</tfoot>
				</table>

				<table class="table table-bordered table-condensed jambo_table">
					<thead>
						<tr>
							<th>BANK DEPOSITS</th>
							<th>AMOUNT</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>Bank Deposits</td>
							<td align=right><?= number_format($dataOnCashStartToEndDay['bankDepositsTotal'], 2) ?></td>
						</tr>

					</tbody>
					<tfoot>
						<tr>
							<td align=right><b>TOTAL</b></td>
							<td align=right><b><?= number_format($dataOnCashStartToEndDay['bankDepositsTotal'], 2) ?></b></td>
						</tr>
					</tfoot>
				</table>

				<table style="width: 100%">
					<tr>
						<td>Ending Cash On Hand:</td>
						<td align=right><?= $ending_coh_cash > 0 ? '<b>'.number_format($ending_coh_cash, 2).'</b>' : '<b><font color="red">'.number_format($ending_coh_cash, 2).'</font></b>' ?></td>
					</tr>
				</table>
			<?php Panel::end(); ?>
			<?php Panel::begin(['header' => 'Cash On Bank']); ?>
				<table style="width: 100%">
					<tr>
						<td>Beginning Cash On Bank:</td>
						<td align=right>
							<?php if($id == $cutoff['start']){ ?>
								<?= $form->field($beginningcoh, 'cash_on_bank')->widget(MaskedInput::classname(), [
								        'clientOptions' => [
								            'alias' =>  'decimal',
								            'autoGroup' => true
								        ],
								    ])->label('<br>') ?>
							<?php }else{ ?>
								<?= $beginning_coh_bank > 0 ? '<b>'.number_format($beginning_coh_bank, 2).'</b>' : '<b><font color="red">'.number_format($beginning_coh_bank, 2).'</font></b>' ?>
							<?php } ?>
						</td>
					</tr>
				</table>
				<br>
				<table class="table table-bordered table-condensed jambo_table">
					<thead>
						<tr>
							<th>INCOME</th>
							<th>AMOUNT</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>Enrolments</td>
							<td align=right><?= number_format($dataOnBankStartToEndDay['incomeEnrolmentTotal'], 2) ?></td>
						</tr>
						<tr>
							<td>Freebies and Icons</td>
							<td align=right><?= number_format($dataOnBankStartToEndDay['freebiesTotal'], 2) ?></td>
						</tr>
						<tr>
							<td>BP and Others</td>
							<td align=right><?= number_format($dataOnBankStartToEndDay['budgetProposalTotal'], 2) ?></td>
						</tr>
					</tbody>
					<tfoot>
						<tr>
							<td align=right><b>TOTAL</b></td>
							<td align=right><b><?= number_format($total_income_bank, 2)?></b></td>
						</tr>
					</tfoot>
				</table>
				<br>
				<table class="table table-bordered table-condensed jambo_table">
					<thead>
						<tr>
							<th>EXPENSES</th>
							<th>AMOUNT</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>Petty Expenses</td>
							<td align=right><?= number_format($dataOnBankStartToEndDay['pettyExpenseTotal'], 2) ?></td>
						</tr>
						<tr>
							<td>Photocopy Expenses</td>
							<td align=right><?= number_format($dataOnBankStartToEndDay['photocopyExpenseTotal'], 2) ?></td>
						</tr>
						<tr>
							<td>Operating Expenses</td>
							<td align=right><?= number_format($dataOnBankStartToEndDay['otherExpenseTotal'], 2) ?></td>
						</tr>
						<tr>
							<td>Other Expenses</td>
							<td align=right><?= number_format($dataOnBankStartToEndDay['operatingExpenseTotal'], 2) ?></td>
						</tr>
						<tr>
							<td>Branch Transfers</td>
							<td align=right><?= number_format($dataOnBankStartToEndDay['branchTransferTotal'], 2) ?></td>
						</tr>
					</tbody>
					<tfoot>
						<tr>
							<td align=right><b>TOTAL</b></td>
							<td align=right><b><?= number_format($total_expenses_bank - $dataOnBankStartToEndDay['bankDepositsTotal'], 2)?></b></td>
						</tr>
					</tfoot>
				</table>

				<table class="table table-bordered table-condensed jambo_table">
					<thead>
						<tr>
							<th>BANK DEPOSITS</th>
							<th>AMOUNT</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>Bank Deposits</td>
							<td align=right><?= number_format($dataOnBankStartToEndDay['bankDepositsTotal'], 2) ?></td>
						</tr>

					</tbody>
					<tfoot>
						<tr>
							<td align=right><b>TOTAL</b></td>
							<td align=right><b><?= number_format($dataOnBankStartToEndDay['bankDepositsTotal'], 2) ?></b></td>
						</tr>
					</tfoot>
				</table>

				<table style="width: 100%">
					<tr>
						<td>Ending Cash On Bank:</td>
						<td align=right><?= $ending_coh_bank > 0 ? '<b>'.number_format($ending_coh_bank, 2).'</b>' : '<b><font color="red">'.number_format($ending_coh_bank, 2).'</font></b>' ?></td>
					</tr>
				</table>
			<?php Panel::end(); ?>
		</div>
		<div class="col-md-6">
			<?php Panel::begin(['header' => 'Input Form']); ?>
				<table class="table table-bordered table-condensed jambo_table">
					<thead>
						<tr>
							<th><center>DENOMINATION</center></th>
							<th><center>COUNT</center></th>
							<th><center>TOTAL</center></th>
						</tr>
					</thead>
					<tbody>
						<?php if(!empty($audits)){ ?>
							<?php foreach($audits as $key => $audit){ ?>
								<tr>
									<td align=center style="width: 30%;">
										<?= $denominations[$key]['denomination'] ?>
										<?= Html::hiddenInput('denomination-'.$key, $denominations[$key]['denomination'], ['id' => 'denomination-'.$key]) ?>		
									</td>
									<td style="width: 30%;"><?= $form->field($audit, "[$key]total")->widget(MaskedInput::classname(), [
									        'clientOptions' => [
									            'alias' =>  'decimal',
									            'autoGroup' => true
									        ],
									    ])->label(false) ?>
	   								</td>
									<td align=right style="width: 40%;">
										<div id="raw-total-<?= $key ?>"></div>
										<?= Html::hiddenInput('raw-'.$key, '', ['id' => 'raw-'.$key]) ?>
									</td>
								</tr>
							<?php } ?>
						<?php } ?>
						<tr>
							<td align=right colspan=2><b>TOTAL</b></td>
							<td align=right>
								<div id="total"></div>
								<?= Html::hiddenInput('hidden-total', '', ['id' => 'hidden-total']) ?>
							</td>
						</tr>
						<tr>
							<td align=right colspan=2><b>DISCREPANCY</b></td>
							<td align=right>
								<div id="discrepancy"></div>
								<?= Html::hiddenInput('discrepancy-total', '', ['id' => 'discrepancy-total']) ?>
							</td>
						</tr>
					</tbody>
				</table>
			<?php Panel::end(); ?>
			<div class="form-group">
                <?= Html::submitButton('Save', ['class' => 'btn btn-block btn-success']) ?>
            </div>
		</div>
	</div>
    <?php ActiveForm::end(); ?>
</div>
<?php Panel::end(); ?>
<?php
	$script = '
        $( document ).ready(function() {
        	$.ajax({
                url: "'.Url::to(['/accounting/audit/audit-summary']).'?id='.$id.'&branchProgram='.$branchProgram->id.'&season='.$season->id.'",
                success: function (data) { 
                    $("#audit-summary").empty();
                    $("#audit-summary").hide();
                    $("#audit-summary").fadeIn();
                    $("#audit-summary").html(data);
                }
            });

        	var total = 0;
        	var discrepancy = 0;
        	for(i = 0; i < '.count($denominations).'; i++)
        	{
        		if($("#audit-"+ i +"-total").val() == "")
	        	{
	        		$("#raw-total-"+ i).html("0.00");
	        		$("#raw-"+ i).val("0.00");
	        		$("#audit-"+ i +"-total").val("0");

	        		total += parseFloat($("#raw-" + i).val());
	        	}else{
	        		$("#raw-total-" + i).html((parseFloat($("#audit-"+ i +"-total").val()) * parseFloat($("#denomination-" + i).val())).toFixed(2));
	        		$("#raw-" + i).val((parseFloat($("#audit-"+ i +"-total").val()) * parseFloat($("#denomination-" + i).val())).toFixed(2));

	        		total += parseFloat($("#raw-" + i).val());
	        	}
        	}

        	$("#audit-0-total").on("keyup", function(){ 
        		if($("#audit-0-total").val() == "")
	        	{
	        		$("#audit-0-total").val(0);	
	        	}
        		$("#raw-0").val(parseFloat($("#denomination-0").val())*parseFloat($("#audit-0-total").val()));
        		$("#raw-total-0").html(parseFloat($("#denomination-0").val())*parseFloat($("#audit-0-total").val()));

        		total = parseFloat($("#raw-0").val()) + parseFloat($("#raw-1").val()) + parseFloat($("#raw-2").val()) + parseFloat($("#raw-3").val()) + parseFloat($("#raw-4").val()) + parseFloat($("#raw-5").val()) + parseFloat($("#raw-6").val()) + parseFloat($("#raw-7").val()) + parseFloat($("#raw-8").val()) + parseFloat($("#raw-9").val()) + parseFloat($("#raw-10").val());
        		$("#total").html(total.toFixed(2));
		        discrepancy = (total - parseFloat('.$ending_coh_cash.')).toFixed(2);
		        $("#discrepancy-total").val(discrepancy);
		        $("#discrepancy").html(discrepancy);
        	});

        	$("#audit-1-total").on("keyup", function(){ 
        		if($("#audit-1-total").val() == "")
	        	{
	        		$("#audit-1-total").val(0);	
	        	}
        		$("#raw-1").val(parseFloat($("#denomination-1").val())*parseFloat($("#audit-1-total").val()));
        		$("#raw-total-1").html(parseFloat($("#denomination-1").val())*parseFloat($("#audit-1-total").val()));

        		total = parseFloat($("#raw-0").val()) + parseFloat($("#raw-1").val()) + parseFloat($("#raw-2").val()) + parseFloat($("#raw-3").val()) + parseFloat($("#raw-4").val()) + parseFloat($("#raw-5").val()) + parseFloat($("#raw-6").val()) + parseFloat($("#raw-7").val()) + parseFloat($("#raw-8").val()) + parseFloat($("#raw-9").val()) + parseFloat($("#raw-10").val());
        		$("#total").html(total.toFixed(2));
		        discrepancy = (total - parseFloat('.$ending_coh_cash.')).toFixed(2);
		        $("#discrepancy-total").val(discrepancy);
		        $("#discrepancy").html(discrepancy);
        	});

        	$("#audit-2-total").on("keyup", function(){ 
        		if($("#audit-2-total").val() == "")
	        	{
	        		$("#audit-2-total").val(0);	
	        	}
        		$("#raw-2").val(parseFloat($("#denomination-2").val())*parseFloat($("#audit-2-total").val()));
        		$("#raw-total-2").html(parseFloat($("#denomination-2").val())*parseFloat($("#audit-2-total").val()));

        		total = parseFloat($("#raw-0").val()) + parseFloat($("#raw-1").val()) + parseFloat($("#raw-2").val()) + parseFloat($("#raw-3").val()) + parseFloat($("#raw-4").val()) + parseFloat($("#raw-5").val()) + parseFloat($("#raw-6").val()) + parseFloat($("#raw-7").val()) + parseFloat($("#raw-8").val()) + parseFloat($("#raw-9").val()) + parseFloat($("#raw-10").val());
        		$("#total").html(total.toFixed(2));
		        discrepancy = (total - parseFloat('.$ending_coh_cash.')).toFixed(2);
		        $("#discrepancy-total").val(discrepancy);
		        $("#discrepancy").html(discrepancy);
        	});

        	$("#audit-3-total").on("keyup", function(){ 
        		if($("#audit-3-total").val() == "")
	        	{
	        		$("#audit-3-total").val(0);	
	        	}
        		$("#raw-3").val(parseFloat($("#denomination-3").val())*parseFloat($("#audit-3-total").val()));
        		$("#raw-total-3").html(parseFloat($("#denomination-3").val())*parseFloat($("#audit-3-total").val()));

        		total = parseFloat($("#raw-0").val()) + parseFloat($("#raw-1").val()) + parseFloat($("#raw-2").val()) + parseFloat($("#raw-3").val()) + parseFloat($("#raw-4").val()) + parseFloat($("#raw-5").val()) + parseFloat($("#raw-6").val()) + parseFloat($("#raw-7").val()) + parseFloat($("#raw-8").val()) + parseFloat($("#raw-9").val()) + parseFloat($("#raw-10").val());
        		$("#total").html(total.toFixed(2));
		        discrepancy = (total - parseFloat('.$ending_coh_cash.')).toFixed(2);
		        $("#discrepancy-total").val(discrepancy);
		        $("#discrepancy").html(discrepancy);
        	});

        	$("#audit-4-total").on("keyup", function(){ 
        		if($("#audit-4-total").val() == "")
	        	{
	        		$("#audit-4-total").val(0);	
	        	}
        		$("#raw-4").val(parseFloat($("#denomination-4").val())*parseFloat($("#audit-4-total").val()));
        		$("#raw-total-4").html(parseFloat($("#denomination-4").val())*parseFloat($("#audit-4-total").val()));

        		total = parseFloat($("#raw-0").val()) + parseFloat($("#raw-1").val()) + parseFloat($("#raw-2").val()) + parseFloat($("#raw-3").val()) + parseFloat($("#raw-4").val()) + parseFloat($("#raw-5").val()) + parseFloat($("#raw-6").val()) + parseFloat($("#raw-7").val()) + parseFloat($("#raw-8").val()) + parseFloat($("#raw-9").val()) + parseFloat($("#raw-10").val());
        		$("#total").html(total.toFixed(2));
		        discrepancy = (total - parseFloat('.$ending_coh_cash.')).toFixed(2);
		        $("#discrepancy-total").val(discrepancy);
		        $("#discrepancy").html(discrepancy);
        	});

        	$("#audit-5-total").on("keyup", function(){ 
        		if($("#audit-5-total").val() == "")
	        	{
	        		$("#audit-5-total").val(0);	
	        	}
        		$("#raw-5").val(parseFloat($("#denomination-5").val())*parseFloat($("#audit-5-total").val()));
        		$("#raw-total-5").html(parseFloat($("#denomination-5").val())*parseFloat($("#audit-5-total").val()));

        		total = parseFloat($("#raw-0").val()) + parseFloat($("#raw-1").val()) + parseFloat($("#raw-2").val()) + parseFloat($("#raw-3").val()) + parseFloat($("#raw-4").val()) + parseFloat($("#raw-5").val()) + parseFloat($("#raw-6").val()) + parseFloat($("#raw-7").val()) + parseFloat($("#raw-8").val()) + parseFloat($("#raw-9").val()) + parseFloat($("#raw-10").val());
        		$("#total").html(total.toFixed(2));
		        discrepancy = (total - parseFloat('.$ending_coh_cash.')).toFixed(2);
		        $("#discrepancy-total").val(discrepancy);
		        $("#discrepancy").html(discrepancy);
        	});

        	$("#audit-6-total").on("keyup", function(){ 
        		if($("#audit-6-total").val() == "")
	        	{
	        		$("#audit-6-total").val(0);	
	        	}
        		$("#raw-6").val(parseFloat($("#denomination-6").val())*parseFloat($("#audit-6-total").val()));
        		$("#raw-total-6").html(parseFloat($("#denomination-6").val())*parseFloat($("#audit-6-total").val()));

        		total = parseFloat($("#raw-0").val()) + parseFloat($("#raw-1").val()) + parseFloat($("#raw-2").val()) + parseFloat($("#raw-3").val()) + parseFloat($("#raw-4").val()) + parseFloat($("#raw-5").val()) + parseFloat($("#raw-6").val()) + parseFloat($("#raw-7").val()) + parseFloat($("#raw-8").val()) + parseFloat($("#raw-9").val()) + parseFloat($("#raw-10").val());
        		$("#total").html(total.toFixed(2));
		        discrepancy = (total - parseFloat('.$ending_coh_cash.')).toFixed(2);
		        $("#discrepancy-total").val(discrepancy);
		        $("#discrepancy").html(discrepancy);
        	});

        	$("#audit-7-total").on("keyup", function(){ 
        		if($("#audit-7-total").val() == "")
	        	{
	        		$("#audit-7-total").val(0);	
	        	}
        		$("#raw-7").val(parseFloat($("#denomination-7").val())*parseFloat($("#audit-7-total").val()));
        		$("#raw-total-7").html(parseFloat($("#denomination-7").val())*parseFloat($("#audit-7-total").val()));

        		total = parseFloat($("#raw-0").val()) + parseFloat($("#raw-1").val()) + parseFloat($("#raw-2").val()) + parseFloat($("#raw-3").val()) + parseFloat($("#raw-4").val()) + parseFloat($("#raw-5").val()) + parseFloat($("#raw-6").val()) + parseFloat($("#raw-7").val()) + parseFloat($("#raw-8").val()) + parseFloat($("#raw-9").val()) + parseFloat($("#raw-10").val());
        		$("#total").html(total.toFixed(2));
		        discrepancy = (total - parseFloat('.$ending_coh_cash.')).toFixed(2);
		        $("#discrepancy-total").val(discrepancy);
		        $("#discrepancy").html(discrepancy);
        	});

        	$("#audit-8-total").on("keyup", function(){ 
        		if($("#audit-8-total").val() == "")
	        	{
	        		$("#audit-8-total").val(0);	
	        	}
        		$("#raw-8").val(parseFloat($("#denomination-8").val())*parseFloat($("#audit-8-total").val()));
        		$("#raw-total-8").html(parseFloat($("#denomination-8").val())*parseFloat($("#audit-8-total").val()));

        		total = parseFloat($("#raw-0").val()) + parseFloat($("#raw-1").val()) + parseFloat($("#raw-2").val()) + parseFloat($("#raw-3").val()) + parseFloat($("#raw-4").val()) + parseFloat($("#raw-5").val()) + parseFloat($("#raw-6").val()) + parseFloat($("#raw-7").val()) + parseFloat($("#raw-8").val()) + parseFloat($("#raw-9").val()) + parseFloat($("#raw-10").val());
        		$("#total").html(total.toFixed(2));
		        discrepancy = (total - parseFloat('.$ending_coh_cash.')).toFixed(2);
		        $("#discrepancy-total").val(discrepancy);
		        $("#discrepancy").html(discrepancy);
        	});

        	$("#audit-9-total").on("keyup", function(){
        		if($("#audit-9-total").val() == "")
	        	{
	        		$("#audit-9-total").val(0);	
	        	} 
        		$("#raw-9").val(parseFloat($("#denomination-9").val())*parseFloat($("#audit-9-total").val()));
        		$("#raw-total-9").html(parseFloat($("#denomination-9").val())*parseFloat($("#audit-9-total").val()));

        		total = parseFloat($("#raw-0").val()) + parseFloat($("#raw-1").val()) + parseFloat($("#raw-2").val()) + parseFloat($("#raw-3").val()) + parseFloat($("#raw-4").val()) + parseFloat($("#raw-5").val()) + parseFloat($("#raw-6").val()) + parseFloat($("#raw-7").val()) + parseFloat($("#raw-8").val()) + parseFloat($("#raw-9").val()) + parseFloat($("#raw-10").val());
        		$("#total").html(total.toFixed(2));
		        discrepancy = (total - parseFloat('.$ending_coh_cash.')).toFixed(2);
		        $("#discrepancy-total").val(discrepancy);
		        $("#discrepancy").html(discrepancy);
        	});

        	$("#audit-10-total").on("keyup", function(){ 
        		if($("#audit-10-total").val() == "")
	        	{
	        		$("#audit-10-total").val(0);	
	        	}
        		$("#raw-10").val(parseFloat($("#denomination-10").val())*parseFloat($("#audit-10-total").val()));
        		$("#raw-total-10").html(parseFloat($("#denomination-10").val())*parseFloat($("#audit-10-total").val()));

        		total = parseFloat($("#raw-0").val()) + parseFloat($("#raw-1").val()) + parseFloat($("#raw-2").val()) + parseFloat($("#raw-3").val()) + parseFloat($("#raw-4").val()) + parseFloat($("#raw-5").val()) + parseFloat($("#raw-6").val()) + parseFloat($("#raw-7").val()) + parseFloat($("#raw-8").val()) + parseFloat($("#raw-9").val()) + parseFloat($("#raw-10").val());
        		$("#total").html(total.toFixed(2));
		        discrepancy = (total - parseFloat('.$ending_coh_cash.')).toFixed(2);
		        $("#discrepancy-total").val(discrepancy);
		        $("#discrepancy").html(discrepancy);
        	});

	        $("#total").html(total.toFixed(2));
	        discrepancy = (total - parseFloat('.$ending_coh_cash.')).toFixed(2);
	        $("#discrepancy-total").val(discrepancy);
	        $("#discrepancy").html(discrepancy);
	        });
   ';
$this->registerJs($script);
?>