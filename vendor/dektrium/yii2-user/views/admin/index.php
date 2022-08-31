<?php

/*
 * This file is part of the Dektrium project.
 *
 * (c) Dektrium project <http://github.com/dektrium>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\Pjax;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yiister\gentelella\widgets\Panel;
/**
 * @var \yii\web\View $this
 * @var \yii\data\ActiveDataProvider $dataProvider
 * @var \dektrium\user\models\UserSearch $searchModel
 */

$this->title = Yii::t('user', 'Manage users');
$this->params['breadcrumbs'][] = $this->title;
?>

<?= $this->render('/_alert', ['module' => Yii::$app->getModule('user')]) ?>

<?= $this->render('/admin/_menu') ?>

<?php Pjax::begin() ?>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel'  => $searchModel,
    'layout'       => "{items}\n{pager}",
    'columns' => [
        [
            'label'=>'Branch',
            'attribute'=>'BRANCH_C',
            'value'=> function ($model){
                    return ($model->userinfo->BRANCH_C) ? $model->userinfo->branch->name : '';
            },
            'contentOptions'=>['style'=>'width:200px;'],
            'filter' => Select2::widget([
                            'model' => $searchModel,
                            'attribute' => 'BRANCH_C',
                            'data' => ArrayHelper::map($branches, 'id', 'name'),
                            'options' => ['placeholder' => '', 'multiple' => false],
                            'pluginOptions' => [
                                'allowClear' => true
                            ],
                        ]),
            'visible' => true,
        ],
        [
            'label'=>'School',
            'attribute'=>'SCHOOL_C',
            'value'=> function ($model){
                    return ($model->userinfo->SCHOOL_C) ? $model->userinfo->school ? $model->userinfo->school->name.' ('.$model->userinfo->school->location.')' : '' : '';
            },
            'contentOptions'=>['style'=>'width:350px;'],
            'filter' => Select2::widget([
                            'model' => $searchModel,
                            'attribute' => 'SCHOOL_C',
                            'data' => ArrayHelper::map($schools, 'id', 'name'),
                            'options' => ['placeholder' => '', 'multiple' => false],
                            'pluginOptions' => [
                                'allowClear' => true
                            ],
                        ]),
            'visible' => true,
        ],
        'fullName',
        'username',
        'email:email',
        [
            'attribute' => 'registration_ip',
            'value' => function ($model) {
                return $model->registration_ip == null
                    ? '<span class="not-set">' . Yii::t('user', '(not set)') . '</span>'
                    : $model->registration_ip;
            },
            'format' => 'html',
        ],
        [
            'attribute' => 'created_at',
            'value' => function ($model) {
                if (extension_loaded('intl')) {
                    return Yii::t('user', '{0, date, MMMM dd, YYYY HH:mm}', [$model->created_at]);
                } else {
                    return date('Y-m-d G:i:s', $model->created_at);
                }
            },
        ],

        [
          'attribute' => 'last_login_at',
          'value' => function ($model) {
            if (!$model->last_login_at || $model->last_login_at == 0) {
                return Yii::t('user', 'Never');
            } else if (extension_loaded('intl')) {
                return Yii::t('user', '{0, date, MMMM dd, YYYY HH:mm}', [$model->last_login_at]);
            } else {
                return date('Y-m-d G:i:s', $model->last_login_at);
            }
          },
        ],
        [
            'header' => Yii::t('user', 'Confirmation'),
            'value' => function ($model) {
                if ($model->isConfirmed) {
                    return '<div class="text-center">
                                <span class="text-success">' . Yii::t('user', 'Confirmed') . '</span>
                            </div>';
                } else {
                    return Html::a(Yii::t('user', 'Confirm'), ['confirm', 'id' => $model->id], [
                        'class' => 'btn btn-xs btn-success btn-block',
                        'data-method' => 'post',
                        'data-confirm' => Yii::t('user', 'Are you sure you want to confirm this user?'),
                    ]);
                }
            },
            'format' => 'raw',
            'visible' => Yii::$app->getModule('user')->enableConfirmation,
        ],
        [
            'header' => Yii::t('user', 'Block status'),
            'value' => function ($model) {
                if ($model->isBlocked) {
                    return Html::a(Yii::t('user', 'Unblock'), ['block', 'id' => $model->id], [
                        'class' => 'btn btn-xs btn-success btn-block',
                        'data-method' => 'post',
                        'data-confirm' => Yii::t('user', 'Are you sure you want to unblock this user?'),
                    ]);
                } else {
                    return Html::a(Yii::t('user', 'Block'), ['block', 'id' => $model->id], [
                        'class' => 'btn btn-xs btn-danger btn-block',
                        'data-method' => 'post',
                        'data-confirm' => Yii::t('user', 'Are you sure you want to block this user?'),
                    ]);
                }
            },
            'format' => 'raw',
        ],
        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{switch} {resend_password} {update} {delete}',
            'buttons' => [
                'resend_password' => function ($url, $model, $key) {
                    if (\Yii::$app->user->identity->isAdmin && !$model->isAdmin) {
                        return '
                    <a data-method="POST" data-confirm="' . Yii::t('user', 'Are you sure?') . '" href="' . Url::to(['resend-password', 'id' => $model->id]) . '">
                    <span title="' . Yii::t('user', 'Generate and send new password to user') . '" class="glyphicon glyphicon-envelope">
                    </span> </a>';
                    }
                },
                'switch' => function ($url, $model) {
                    if(\Yii::$app->user->identity->isAdmin && $model->id != Yii::$app->user->id && Yii::$app->getModule('user')->enableImpersonateUser) {
                        return Html::a('<span class="glyphicon glyphicon-user"></span>', ['/user/admin/switch', 'id' => $model->id], [
                            'title' => Yii::t('user', 'Become this user'),
                            'data-confirm' => Yii::t('user', 'Are you sure you want to switch to this user for the rest of this Session?'),
                            'data-method' => 'POST',
                        ]);
                    }
                }
            ]
        ],
    ],
]); ?>

<?php Pjax::end() ?>

