<?php

/*
 * This file is part of the Dektrium project.
 *
 * (c) Dektrium project <http://github.com/dektrium>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
/**
 * @var yii\web\View $this
 * @var dektrium\user\models\User $model
 * @var dektrium\user\Module $module
 */

$this->title = Yii::t('user', 'Sign up');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><?= Html::encode($this->title) ?></h3>
            </div>
            <div class="panel-body">
                <?php $form = ActiveForm::begin([
                    'id' => 'registration-form',
                    'enableAjaxValidation' => true,
                    'enableClientValidation' => false,
                ]); ?>
                <h4>User Details</h4>
                <div class="row">
                    <div class="col-md-12">
                        <?= $form->field($model, 'BRANCH_C')->widget(Select2::classname(), [
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
                    <div class="col-md-3">
                        <?= $form->field($model, 'FIRST_M')->textInput(['maxlength'=> true]); ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($model, 'MIDDLE_M')->textInput(['maxlength'=> true]); ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($model, 'LAST_M')->textInput(['maxlength'=> true]); ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($model, 'SUFFIX')->textInput(['maxlength'=> true]); ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <?= $form->field($model, 'MOBILEPHONE')->textInput(['maxlength'=> true]); ?>
                    </div>
                </div>
                <hr>
                <h4>Account Details</h4>
                <div class="row">
                    <div class="col-md-12">
                        <?= $form->field($model, 'email') ?>

                        <?= $form->field($model, 'username') ?>

                        <?php if ($module->enableGeneratingPassword == false): ?>
                            <?= $form->field($model, 'password')->passwordInput() ?>
                        <?php endif ?>
                    </div>
                </div>
                <?= Html::submitButton(Yii::t('user', 'Sign up'), ['class' => 'btn btn-success btn-block']) ?>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
        <p class="text-center">
            <?= Html::a(Yii::t('user', 'Already registered? Sign in!'), ['/user/security/login']) ?>
        </p>
    </div>
</div>
