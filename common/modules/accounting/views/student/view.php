<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;
use yiister\gentelella\widgets\Panel;
use yii\helpers\Url;
/* @var $this yii\web\View */
/* @var $model common\modules\accounting\models\Student */

$this->title = 'Student Profile';
$this->params['breadcrumbs'][] = ['label' => 'Students', 'url' => ['list']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="student-view">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php Panel::begin(['header' => 'Student Details']); ?>
    <div class="row">
        <div class="col-md-12">
            <h1><?= $model->fullName ?>
                <span class="pull-right">
                    <?= Yii::$app->user->can('updateStudent') ? Html::a('Edit Information',['/accounting/student/update', 'id' => $model->id],['class' => 'btn btn-primary']) : '' ?>
                    <?= Yii::$app->user->can('deleteStudent') ? Html::a('Delete Information',['/accounting/student/delete', 'id' => $model->id],[
                        'class' => 'btn btn-danger',
                        'data' => [
                                'confirm' => 'Are you sure you want to delete this item?',
                                'method' => 'post',
                            ]
                    ]) : '' ?>
                </span>
            </h1>
            <p><i class="glyphicon glyphicon-envelope"></i> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $model->email_address ?><br>
               <i class="glyphicon glyphicon-earphone"></i> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $model->contact_no ?><br>
               <i class="glyphicon glyphicon-home"></i> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $model->permanent_address ?><br>
               <i class="glyphicon glyphicon-calendar"></i> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $model->birthday ?><br>
           </p>
        </div>
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-3">
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <div class="mail_list">
                                <div class="right">
                                    <p>ID Number</p>
                                    <h4><b><?= $model->id_number ?></b></h4>
                                </div>
                            </div>
                            <div class="mail_list">
                                <div class="right">
                                    <p>School</p>
                                    <h4><b><?= $model->schoolName ?></b></h4>
                                </div>
                            </div>
                            <div class="mail_list">
                                <div class="right">
                                    <p>Year Graduated</p>
                                    <h4><b><?= $model->year_graduated ?></b></h4>
                                </div>
                            </div>
                            <div class="mail_list">
                                <div class="right">
                                    <p>PRC Application Number</p>
                                    <h4><b><?= $model->prc ?></b></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-9">
                    <?php Panel::begin(['header' => 'Enrolment Records']); ?>
                    <table class="table table-striped jambo_table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Branch</th>
                                <th>Program</th>
                                <th>Season</th>
                                <th>Enrolee Type</th>
                                <th>Final Tuition Fee</th>
                                <th>Balance</th>
                                <th>Status</th>
                                <th>&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($enrolments)){ ?>
                                <?php $i = 1; ?>
                                <?php foreach($enrolments as $enrolment){ ?>
                                    <tr>
                                        <td><?= $i ?></td>
                                        <td><?= $enrolment['branchName'] ?></td>
                                        <td><?= $enrolment['programName'] ?></td>
                                        <td><?= $enrolment['seasonName'] ?></td>
                                        <td><?= $enrolment['enroleeTypeName'] ?></td>
                                        <td><?= number_format($enrolment['finalTuitionFee'], 2) ?></td>
                                        <td><?= number_format($enrolment['balanceAmount'], 2) ?></td>
                                        <td><?= $enrolment['balanceAmount'] > 0 ? '<font color=red>With Balance</font>' : 'Cleared' ?></td>
                                        <td><?= Html::button('View', ['class' => 'btn btn-xs btn-primary btn-block show-season-button', 'onclick' => '
                                                $.ajax({
                                                    url: "'.Url::to(['/accounting/student/show-enrolment', 'id' => $enrolment['id']]).'",
                                                    success : function(data){
                                                        $("#season-information").html(data);
                                                    }
                                                })
                                            ;']) ?></td>
                                    </tr>
                                    <?php $i++; ?>
                                <?php } ?>
                            <?php } ?>
                        </tbody>
                    </table>
                    <div id="season-information"></div>
                    <?php Panel::end(); ?>
                </div>
            </div>
        </div>
    </div>
    <?php Panel::end(); ?>
</div>
