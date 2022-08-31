<?php

/**
 * @var string $content
 * @var \yii\web\View $this
 */

use yii\widgets\Breadcrumbs;
use common\widgets\Alert;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yiister\gentelella\widgets\Panel;
use yii\jui\ProgressBar;
use yii\web\View;
use common\modules\accounting\models\AccessProgram;
use common\modules\accounting\models\BranchProgram;

if (Yii::$app->controller->action->id === 'login') { 
/**
 * Do not use this code in your template. Remove it. 
 * Instead, use the code  $this->layout = '//main-login'; in your controller.
 */
    echo $this->render(
        'main-login',
        ['content' => $content]
    );
} else {

    if (class_exists('frontend\assets\AppAsset')) {
        frontend\assets\AppAsset::register($this);
    } else {
        app\assets\AppAsset::register($this);
    }

$bundle = yiister\gentelella\assets\Asset::register($this);
\bedezign\yii2\audit\web\JSLoggingAsset::register($this);

$user_info = Yii::$app->user->identity->userinfo;
$roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
$rolenames =  ArrayHelper::map($roles, 'name','name');
$role = '';
$branch = Yii::$app->user->identity->userinfo->branch ? Yii::$app->user->identity->userinfo->branch->name : 'ALL BRANCHES';
$branchProgram = AccessProgram::findOne(['user_id' => Yii::$app->user->identity->userinfo->user_id]);

$program = '';
if($branchProgram)
{
    if($branchProgram->branch_program_id != '')
    {
        $bp = BranchProgram::findOne($branchProgram->branch_program_id);
        $program = $bp->branchProgramName;
        $branch = $bp->branchName;
    }else{
        $program = 'ALL PROGRAMS';
    }
}else{
    $program = 'ALL PROGRAMS';
}

if(in_array('TopManagement',$rolenames)){
    $role = 'TopManagement';  
}else if(in_array('AreaManager',$rolenames)){
    $role = 'AreaManager';
}else if(in_array('AccountingStaff',$rolenames)){
    $role = 'AccountingStaff';
}else if(in_array('EnrolmentStaff',$rolenames)){
    $role = 'EnrolmentStaff';
}else if(in_array('SchoolBased',$rolenames)){
    $role = 'SchoolBased';
}else if(in_array('Student',$rolenames)){
    $role = 'Student';
}else if(in_array('TechnicalStaff',$rolenames)){
    $role = 'TechnicalStaff';
}else if(in_array('Professional',$rolenames)){
    $role = 'Professional';
}
?>
<?php $this->beginPage(); ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta charset="<?= Yii::$app->charset ?>" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Lato" />
    <link rel="shortcut icon" href="<?php echo Yii::$app->request->baseUrl; ?>/images/favicon.ico" type="image/x-icon" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.23.0/moment.min.js"></script>
    <?php $this->head() ?>
</head>
<body class="nav-md" style="font-family: 'Lato'">
<?php $this->beginBody(); ?>
<div class="container body">
    <div class="main_container">
        <?php if(!Yii::$app->user->isGuest){ ?>
        <?= $this->render('left.php') ?>    
        <?php } ?>
        <!-- top navigation -->
        <?= $this->render('header.php') ?>
        <!-- /top navigation -->

        <!-- page content -->
        <div class="right_col" role="main">
            <div class="content-wrapper">
                <section class="content-header"> 
                    <br>
                    <br>
                    <?= Alert::widget() ?>
                    <span class="pull-right"><?= Breadcrumbs::widget(['links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : []]) ?></span>
                </section>
                <section class="content">
                <?= $content ?>
                </section>
            </div>
            <?php if (isset($this->params['h1'])): ?>
                <div class="page-title">
                    <div class="title_left">
                        <h1><?= $this->params['h1'] ?></h1>
                    </div>
                    <div class="title_right">
                        <div class="col-md-5 col-sm-5 col-xs-12 form-group pull-right top_search">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Search for...">
                                <span class="input-group-btn">
                                <button class="btn btn-default" type="button">Go!</button>
                            </span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <div class="clearfix"></div>
        </div>
        <!-- /page content -->
        <!-- footer content -->
        <footer>
            <div class="row">
                <div class="col-md-2">
                    <p>Logged in as: <br><?= $role ?></p>
                </div>
                <div class="col-md-2">
                    <p>Branch: <br><?= $branch ?></p>
                </div>
                <div class="col-md-2">
                    <p>Program: <br><?= $program ?></p>
                </div>
                <div class="col-md-2">
                    <div class='time-frame'>
                        <div id='date-part'></div>
                        <div id='time-part'></div>
                    </div>
                </div>
                <div class="col-md-3 pull-right">
                    <p>Toprank Integrated Systems | Accounting<br />&copy; <?= date('Y') ?> All RIghts Reserved.</p>
                </div>
            </div>
        </footer>
        <!-- /footer content -->
    </div>

</div>

<div id="custom_notifications" class="custom-notifications dsp_none">
    <ul class="list-unstyled notifications clearfix" data-tabbed_notifications="notif-group">
    </ul>
    <div class="clearfix"></div>
    <div id="notif-group" class="tabbed_notifications"></div>
</div>
<!-- /footer content -->
<?php
      $script = '
        $(document).ready(function() {
            var interval = setInterval(function() {
                var momentNow = moment();
                $("#date-part").html(momentNow.format("MMMM DD"+", "+"YYYY") + " "
                                    + momentNow.format("dddd")
                                     .substring(0,3).toUpperCase());
                $("#time-part").html(momentNow.format("hh:mm:ss A"));
            }, 100);
        });
      ';
      $this->registerJs($script, View::POS_END);
    ?>
};
<?php $this->endBody(); ?>
</body>
</html>
<?php $this->endPage(); ?>
<?php } ?>
<style>
    h1{ font-size: 30px; }
</style>

</div>