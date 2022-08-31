<?php
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this \yii\web\View */
/* @var $content string */
$branch = Yii::$app->user->identity->userinfo->branch ? '('.Yii::$app->user->identity->userinfo->branch->name.')' : ''; 
?>
<!-- top navigation -->

<div class="top_nav">
    <div class="nav_menu menu_color">
        <nav class="" role="navigation">
            <div class="nav toggle">
                <a id="menu_toggle"><i class="fa fa-bars menu_bar_color"></i></a>
            </div>
            <div>
            </div>
            <ul class="nav navbar-nav navbar-right">
                <li class="menu_account">
                    <a href="javascript:;" class="user-profile dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                       <?= !Yii::$app->user->isGuest ? Html::img('http://gravatar.com/avatar/'.Yii::$app->user->identity->profile->gravatar_id.'?s=63',['class' => 'img-circle', 'alt' => 'User Image']). Yii::$app->user->identity->userinfo->fullName.''.$branch : "" ?>
                        <span class=" fa fa-angle-down"></span>
                    </a>
                    <?php if(!Yii::$app->user->isGuest){ ?>
                    <ul class="dropdown-menu dropdown-usermenu pull-right ">
                        <li>
                        <?= Html::a(
                            '<i class="fa fa-gear pull-right"></i> Account Settings',
                            ['/user/settings/account'],
                            ['data-method' => 'post']
                        ) ?>
                        </li>
                        <li>
                        <?= Html::a(
                            '<i class="fa fa-sign-out pull-right"></i> Sign out',
                            ['/user/security/logout'],
                            ['data-method' => 'post']
                        ) ?>
                        </li>
                    </ul>
                    <?php } ?>
                </li>
	         </ul>
        </nav>
    </div>
</div>
<!-- /top navigation -->
