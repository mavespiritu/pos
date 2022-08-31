<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\widgets\MaskedInput;
use yii\web\View;
use yii\helpers\Url;
/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\BudgetProposal */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="budget-proposal-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'approval_status')->widget(Select2::classname(), [
                'data' => ['Approved' => 'Approved', 'Disapproved' => 'Disapproved', 'Revisions Needed' => 'Revisions Needed'],
                'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'grantee-select'],
                'pluginOptions' => [
                    'allowClear' =>  true,
                ]]);
            ?>

            <?= $form->field($model, 'remarks')->textArea(['maxlength' => true, 'rows' => '5']) ?>  
        </div>
        <div class="col-md-4">
            <?= $form->field($branchTransferModel, 'grantee')->widget(Select2::classname(), [
                'data' => ['Branch' => 'Branch', 'Branch - Program' => 'Branch - Program'],
                'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'grantee-select'],
                'pluginOptions' => [
                    'allowClear' =>  true,
                ],
                'pluginEvents' => [
                    'select2:select'=>'
                        function(){
                            var vals = this.value;
                            if(vals == "Branch")
                            {
                                $(".branch-program-select").val("").trigger("change");
                            }
                        }'
                ],
                ]);
            ?>

            <?= $form->field($branchTransferModel, 'branch_id')->widget(Select2::classname(), [
                'data' => $branches,
                'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'branch-select'],
                'pluginOptions' => [
                    'allowClear' =>  true,
                ],
                'pluginEvents' => [
                    'select2:select'=>'
                        function(){
                            var vals = this.value;
                            $(".amount-source-select").val("").trigger("change");
                        }'
                ],
                ]);
            ?>

            <?= $form->field($branchTransferModel, 'branch_program_id')->widget(Select2::classname(), [
                'data' => $branchPrograms,
                'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'branch-program-select'],
                'pluginOptions' => [
                    'allowClear' =>  true,
                ],
                'pluginEvents' => [
                    'select2:select'=>'
                        function(){
                            var vals = this.value;
                            $(".amount-source-select").val("").trigger("change");
                        }'
                ],
                ]);
            ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($branchTransferModel, 'amount_source')->widget(Select2::classname(), [
                'data' => ['Cash On Hand' => 'Cash On Hand', 'Cash On Bank' => 'Cash On Bank'],
                'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'amount-source-select'],
                'pluginOptions' => [
                    'allowClear' =>  true,
                ]]);
            ?>

            <?= $form->field($incomeModel, 'amount_type')->widget(Select2::classname(), [
                'data' => ['Cash' => 'Cash', 'Bank Deposit' => 'Bank Deposit'],
                'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'amount-type-select'],
                'pluginOptions' => [
                    'allowClear' =>  true,
                ]]);
            ?>

            <div id="cash-balance"></div>
        </div>
    </div>
       
    <div class="form-group">
        <div class="row">
            <div class="col-md-12">
                <div class="pull-right"><?= Html::submitButton('Save and Proceed', ['class' => 'btn btn-success btn-block']) ?></div>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
<?php
  $script = '
    $( document ).ready(function() {
        if($("#budgetproposal-approval_status").val() == "Approved")
        {
            $("#branchtransfer-grantee").prop("disabled", false);
            $("#branchtransfer-branch_id").prop("disabled", false);
            $("#branchtransfer-branch_program_id").prop("disabled", false);
            $("#branchtransfer-amount_source").prop("disabled", false);
            $("#income-amount_type").prop("disabled", false);
        }else{
            $("#branchtransfer-grantee").prop("disabled", true);
            $("#branchtransfer-branch_id").prop("disabled", true);
            $("#branchtransfer-branch_program_id").prop("disabled", true);
            $("#branchtransfer-amount_source").prop("disabled", true);
            $("#income-amount_type").prop("disabled", true);
        }

        $("#budgetproposal-approval_status").on("change", function(){
            if($("#budgetproposal-approval_status").val() == "Approved")
            {
                $("#branchtransfer-grantee").prop("disabled", false);
                $("#branchtransfer-branch_id").prop("disabled", true);
                $("#branchtransfer-branch_program_id").prop("disabled", true);
                $("#branchtransfer-amount_source").prop("disabled", true);
                $("#income-amount_type").prop("disabled", true);
            }else{
                $("#branchtransfer-grantee").prop("disabled", true);
                $("#branchtransfer-branch_id").prop("disabled", true);
                $("#branchtransfer-branch_program_id").prop("disabled", true);
                $("#branchtransfer-amount_source").prop("disabled", true);
                $("#income-amount_type").prop("disabled", true);
            }
        });

        if($("#branchtransfer-grantee").val() != "")
        {
            if($("#branchtransfer-grantee").val() == "Branch")
            {
                $("#branchtransfer-branch_id").prop("disabled", false);
                $("#branchtransfer-branch_program_id").prop("disabled", true);
                $("#branchtransfer-branch_program_id").val("").trigger("change");
                $("#branchtransfer-amount_source").prop("disabled", false);
                $("#income-amount_type").prop("disabled", false);
            }else{
                $("#branchtransfer-branch_id").prop("disabled", true);
                $("#branchtransfer-branch_id").val("").trigger("change");
                $("#branchtransfer-branch_program_id").prop("disabled", false);
                $("#branchtransfer-amount_source").prop("disabled", false);
                $("#income-amount_type").prop("disabled", false);
            }
        }

        $("#branchtransfer-grantee").on("change", function(){
            if($("#branchtransfer-grantee").val() == "Branch")
            {
                $("#branchtransfer-branch_id").prop("disabled", false);
                $("#branchtransfer-branch_program_id").prop("disabled", true);
                $("#branchtransfer-branch_program_id").val("").trigger("change");
                $("#branchtransfer-amount_source").prop("disabled", false);
                $("#income-amount_type").prop("disabled", false);
            }else{
                $("#branchtransfer-branch_id").prop("disabled", true);
                $("#branchtransfer-branch_id").val("").trigger("change");
                $("#branchtransfer-branch_program_id").prop("disabled", false);
                $("#branchtransfer-amount_source").prop("disabled", false);
                $("#income-amount_type").prop("disabled", false);
            }
        });

        if($("#branchtransfer-amount_source").val() != "")
        {
            $.ajax({
            url: "'.Url::to(['/accounting/budget-proposal/show-balance']).'?grantee=" + $("#branchtransfer-grantee").val() +"&branch_id=" + $("#branchtransfer-branch_id").val() + "&branch_program_id=" + $("#branchtransfer-branch_program_id").val() + "&amount_source=" + $("#branchtransfer-amount_source").val(),
            success: function (data) { 
                $("#cash-balance").empty();
                $("#cash-balance").hide();
                $("#cash-balance").fadeIn();
                $("#cash-balance").html(data);
            }
        });
        }

        $("#branchtransfer-amount_source").on("change", function(){
            if($("#branchtransfer-amount_source").val() != "")
            {
                $.ajax({
                url: "'.Url::to(['/accounting/budget-proposal/show-balance']).'?grantee=" + $("#branchtransfer-grantee").val() +"&branch_id=" + $("#branchtransfer-branch_id").val() + "&branch_program_id=" + $("#branchtransfer-branch_program_id").val() + "&amount_source=" + $("#branchtransfer-amount_source").val(),
                success: function (data) { 
                    $("#cash-balance").empty();
                    $("#cash-balance").hide();
                    $("#cash-balance").fadeIn();
                    $("#cash-balance").html(data);
                }
            });
            }
        });
    });
    
  ';
  $this->registerJs($script, View::POS_END);
?>