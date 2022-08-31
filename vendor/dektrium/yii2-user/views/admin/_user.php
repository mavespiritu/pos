<?php
use kartik\select2\Select2;
/*
 * This file is part of the Dektrium project.
 *
 * (c) Dektrium project <http://github.com/dektrium>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

/**
 * @var yii\widgets\ActiveForm $form
 * @var dektrium\user\models\User $user
 */
?>
	<h4>User Details</h4>
	<div class="row">
		<div class="col-md-12">
			<?= $form->field($userinfo, 'BRANCH_C')->widget(Select2::classname(), [
                'data' => $branches,
                'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'branch-select'],
                'pluginOptions' => [
                    'allowClear' => true
                ],
            ]); ?>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<?= $form->field($userinfo, 'SCHOOL_C')->widget(Select2::classname(), [
                'data' => $schools,
                'options' => ['placeholder' => 'Select One','multiple' => false, 'class'=>'school-select'],
                'pluginOptions' => [
                    'allowClear' => true
                ],
            ]); ?>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<?= $form->field($userinfo, 'FIRST_M')->textInput(['maxlength'=> true]); ?>

			<?= $form->field($userinfo, 'MIDDLE_M')->textInput(['maxlength'=> true]); ?>

			<?= $form->field($userinfo, 'LAST_M')->textInput(['maxlength'=> true]); ?>

			<?= $form->field($userinfo, 'SUFFIX')->textInput(['maxlength'=> true]); ?>

			<?= $form->field($userinfo, 'MOBILEPHONE')->textInput(['maxlength'=> true]); ?>
		</div>
	</div>
<hr>
    <h4>Account Details</h4>

    <div class="row">
    	<div class="col-md-12">
			<?= $form->field($user, 'email')->textInput(['maxlength' => 255]) ?>

			<?= $form->field($user, 'username')->textInput(['maxlength' => 255]) ?>

			<?= $form->field($user, 'password')->passwordInput() ?>
		</div>
	</div>
