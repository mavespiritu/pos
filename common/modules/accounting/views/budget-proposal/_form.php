<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\widgets\MaskedInput;
use yii\web\View;
/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\BudgetProposal */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="budget-proposal-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'grantee')->widget(Select2::classname(), [
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

            <?= $form->field($model, 'branch_program_id')->widget(Select2::classname(), [
                'data' => $branchPrograms,
                'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'branch-program-select'],
                'pluginOptions' => [
                    'allowClear' =>  true,
                ],
                ]);
            ?>

            <?= $form->field($model, 'budget_proposal_type_id')->widget(Select2::classname(), [
                'data' => $budgetProposalTypes,
                'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'type-select'],
                'pluginOptions' => [
                    'allowClear' =>  true,
                ],
                ]);
            ?>

            <?= $form->field($model, 'other_type')->textInput(['maxlength' => true])->label('If Others, Please Specify') ?>  
        </div>
    </div>
    <br>
    <div class="form-group">
        <div class="row">
            <div class="col-md-4">
                <div id="buttonGroup"><?= Html::submitButton('Save and Proceed', ['class' => 'btn btn-success btn-block']) ?></div>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
      $script = '
        $( document ).ready(function() {
            if($("#budgetproposal-budget_proposal_type_id").val() == "")
            {
                $("#budgetproposal-other_type").prop("readonly", true);

            }else if($("#budgetproposal-budget_proposal_type_id").val() == "18")
            {
                $("#budgetproposal-other_type").prop("readonly", false);
            }else{
                $("#budgetproposal-other_type").prop("readonly", true);
            }

            $("#budgetproposal-budget_proposal_type_id").on("change", function(){
                if($("#budgetproposal-budget_proposal_type_id").val() == "18")
                {
                    $("#budgetproposal-other_type").prop("readonly", false);
                }else{
                    $("#budgetproposal-other_type").prop("readonly", true);
                }
            });

            if($("#budgetproposal-grantee").val() == "")
                {
                    $("#budgetproposal-branch_program_id").prop("disabled", true);
                }else{
                    $("#budgetproposal-branch_program_id").prop("disabled", false);
                }

            $("#budgetproposal-grantee").on("change", function(){
                if($("#budgetproposal-grantee").val() == "Branch")
                {
                    $("#budgetproposal-branch_program_id").prop("disabled", true);
                }else{
                    $("#budgetproposal-branch_program_id").prop("disabled", false);
                }
            });
        });
      ';
      $this->registerJs($script, View::POS_END);
    ?>
