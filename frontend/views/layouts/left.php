<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use common\modules\accounting\models\Notification;
use frontend\assets\AppAsset;
/* @var $this \yii\web\View */
/* @var $content string */
$asset = AppAsset::register($this);
?>
<?php
    $notifications = 0;
    $badgeOptions = [];
    $badge = '';
    $roles = \Yii::$app->authManager->getRolesByUser(Yii::$app->user->id);
    if(empty($roles))
    {
        $user_role = ""; 
    }
    else
    {
       foreach($roles as $role)
        {
            $user_role = $role->name;
        }
    }

    if($user_role == 'TopManagement'){
        $notifications = Notification::find()->where(['branch_id' => null])->count();
    }else if($user_role == 'AreaManager'){
        $notifications = Notification::find()->where(['branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])->count();
    }else if($user_role == 'AccountingStaff'){
        $notifications = Notification::find()->where(['branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C])->count();
    }else if($user_role == 'EnrolmentStaff'){
        $notifications = Notification::find()->where(['branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C, 'model' => 'Transferee'])->count();
    }else if($user_role == 'SchoolBased'){
        $notifications = Notification::find()->where(['branch_id' => Yii::$app->user->identity->userinfo->BRANCH_C, 'model' => 'Transferee'])->count();
    }

    if($notifications > 0)
    {
        $badge = "!";
        $badgeOptions = ["class" => "label-danger"];
    }
?>
<div class="col-md-3 left_col">
    <div class="left_col">

        <div class="navbar nav_title menu_color_left" style="border: 0;">
            <a href="<?= Url::to(['/accounting/home']) ?>" class="site_title"><center><?= Html::img($asset->baseUrl.'/images/favicon.ico',['style' => 'height: 10%; width: 10%;']) ?> ACCOUNTING</center>
            </a>
        </div>
        <div class="clearfix"></div>

        <!-- menu prile quick info -->
        <div class="profile">
            <div class="profile_pic"> 
                <?= Html::img('http://gravatar.com/avatar/'.Yii::$app->user->identity->profile->gravatar_id.'?s=63',['class' => 'img-circle', 'alt' => 'User Image', 'style' => 'margin-left: 15px; margin-top: 20px;']); ?> 
            </div>
            <div class="profile_info">
                <h2><?= !Yii::$app->user->isGuest ? Yii::$app->user->identity->userinfo->fullName : ""; ?></h2>
                <span><i class="fa fa-circle text-success"></i> Online</span>
            </div>
        </div>
        <!-- /menu prile quick info -->

        <br />

        <!-- sidebar menu -->
        <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">

            <div class="menu_section">
                <h3>Main Navigation</h3>
                <?=
                \yiister\gentelella\widgets\Menu::widget(
                    [
                        "items" => [
                            ["label" => "Home", "url" => ["/accounting/home"], "icon" => "building", 'visible' => !Yii::$app->user->isGuest, 'badge' => $badge, 'badgeOptions' => $badgeOptions],
                            ["label" => "My Requests", "url" => ["/accounting/professional-request"], "icon" => "user", 'visible' => !Yii::$app->user->isGuest && ($user_role == 'Professional')],
                            ["label" => "Prof Requests", "url" => ["/accounting/professional-request"], "icon" => "user", 'visible' => !Yii::$app->user->isGuest && ($user_role == 'TopManagement')],
                            [
                                "label" => "Dashboard", 
                                "url" => "#", 
                                "icon" => "dashboard", 
                                'visible' => !Yii::$app->user->isGuest && ($user_role == 'TopManagement'),
                                "items" => [
                                     ["label" => "Physical", "url" => ["/accounting/dashboard/student-enrolment"], "icon" => "user"],
                                     ["label" => "Financial", "url" => ["/accounting/dashboard/finance"], "icon" => "dollar"],
                                ],
                            ],
                            ["label" => "Cost Estimation", "url" => ["/accounting/cost-estimation/"], "icon" => "folder-open-o", 'visible' => !Yii::$app->user->isGuest && ($user_role == 'TopManagement' ||  $user_role == 'AccountingStaff' || $user_role == 'AreaManager')],
                             [
                                "label" => "Enrolment",
                                "icon" => "folder-open-o",
                                "url" => "#",
                                'visible' => !Yii::$app->user->isGuest && ($user_role == 'EnrolmentStaff' || $user_role == 'SchoolBased' || $user_role == 'AccountingStaff' || $user_role == 'AreaManager'),
                                "items" => [
                                     ["label" => "Enrol New Student", "url" => ["/accounting/student/create"], "icon" => "plus", 'visible' => !Yii::$app->user->isGuest && ($user_role == 'EnrolmentStaff' || $user_role == 'SchoolBased' || $user_role == 'AccountingStaff' || $user_role == 'AreaManager')],
                                     ["label" => "Enrol Existing Student", "url" => ["/accounting/student/"], "icon" => "plus", 'visible' => !Yii::$app->user->isGuest && ($user_role == 'TopManagement' || $user_role == 'EnrolmentStaff' || $user_role == 'SchoolBased' || $user_role == 'AccountingStaff' || $user_role == 'AreaManager')],
                                     ["label" => "Student Records", "url" => ["/accounting/student/list"], "icon" => "users", 'visible' => !Yii::$app->user->isGuest && ($user_role == 'EnrolmentStaff' || $user_role == 'AccountingStaff' || $user_role == 'SchoolBased' || $user_role == 'AreaManager' || $user_role == 'TopManagement')],

                                    ["label" => "Transferees", "url" => ["/accounting/transferee/"], "icon" => "sign-out", 'visible' => !Yii::$app->user->isGuest && ($user_role == 'EnrolmentStaff' || $user_role == 'SchoolBased' || $user_role == 'AccountingStaff' || $user_role == 'AreaManager')],
                                    ["label" => "Dropouts", "url" => ["/accounting/dropout/"], "icon" => "sign-out", 'visible' => !Yii::$app->user->isGuest && ($user_role == 'EnrolmentStaff' || $user_role == 'SchoolBased' || $user_role == 'AccountingStaff' || $user_role == 'AreaManager')],
                                ],
                            ],
                            
                            [
                                "label" => "Daily Income",
                                "icon" => "table",
                                "url" => "#",
                                'visible' => !Yii::$app->user->isGuest && ($user_role == 'TopManagement' || $user_role == 'AccountingStaff' || $user_role == 'AreaManager'),
                                "items" => [
                                     ["label" => "Enrolments", "url" => ["/accounting/income-enrolment"], "icon" => "table"],
                                     ["label" => "Freebies and Icons", "url" => ["/accounting/freebie-and-icon"], "icon" => "table"],
                                     [
                                        "label" => "Budget Proposals",
                                        "icon" => "table",
                                        "url" => "#",
                                        "visible" => !Yii::$app->user->isGuest && ($user_role == 'AreaManager' || $user_role == 'AccountingStaff' || $user_role == 'TopManagement'),
                                        "items" => [
                                             ["label" => "Create Request", "url" => ["/accounting/budget-proposal/create"], "icon" => "plus", 'visible' => !Yii::$app->user->isGuest && ($user_role == 'AreaManager' || $user_role == 'AccountingStaff')],
                                             ["label" => "Requests", "url" => ["/accounting/budget-proposal"], "icon" => "file", 'visible' => !Yii::$app->user->isGuest && ($user_role == 'AreaManager' || $user_role == 'AccountingStaff' || $user_role == 'TopManagement')],
                                        ],
                                    ],
                                ],
                            ],
                            [
                                "label" => "Daily Expenses",
                                "icon" => "table",
                                "url" => "#",
                                'visible' => !Yii::$app->user->isGuest && ($user_role == 'TopManagement' || $user_role == 'AccountingStaff' || $user_role == 'AreaManager'),
                                "items" => [
                                     ["label" => "Petty Expenses", "url" => ["/accounting/petty-expense"], "icon" => "table"],
                                     ["label" => "Photocopy Expenses", "url" => ["/accounting/photocopy-expense"], "icon" => "table"],
                                     ["label" => "Operating Expenses", "url" => ["/accounting/operating-expense"], "icon" => "table"],
                                     ["label" => "Other Expenses", "url" => ["/accounting/other-expense"], "icon" => "table"],
                                     ["label" => "Bank Deposits", "url" => ["/accounting/bank-deposit"], "icon" => "table", 'visible' => !Yii::$app->user->isGuest && ($user_role == 'TopManagement' || $user_role == 'AreaManager' || $user_role == 'AccountingStaff')],
                                     ["label" => "Branch Transfers", "url" => ["/accounting/branch-transfer"], "icon" => "table"]
                                ],
                            ],

                            
                            ["label" => "Daily Audit", "url" => ["/accounting/audit/"], "icon" => "calculator", 'visible' => !Yii::$app->user->isGuest && ($user_role == 'TopManagement' || $user_role == 'AreaManager' || $user_role == 'AccountingStaff')],
                            [
                                "label" => "Reports",
                                "icon" => "file",
                                "url" => "#",
                                'visible' => !Yii::$app->user->isGuest && ($user_role == 'AreaManager' || $user_role == 'AccountingStaff' || $user_role == 'TopManagement'),
                                "items" => [
                                     ["label" => "Daily Income", "url" => ["/accounting/report/income-generation"], "icon" => "bar-chart"],
                                     ["label" => "Daily Expense", "url" => ["/accounting/report/expense-generation"], "icon" => "bar-chart"],
                                     ["label" => "Daily Audit Summary", "url" => ["/accounting/report/daily-audit"], "icon" => "bar-chart"],
                                     ["label" => "Monthly Summary", "url" => ["/accounting/report/monthly-summary"], "icon" => "bar-chart"],
                                     [
                                        "label" => "Cut-Off Summary",
                                        "icon" => "bar-chart",
                                        "url" =>  ["/accounting/audit/cut-off-summary"],
                                        /*'visible' => !Yii::$app->user->isGuest && ($user_role == 'AreaManager' || $user_role == 'AccountingStaff' || $user_role == 'TopManagement'),
                                        "items" => [
                                             ["label" => "Program", "url" => "icon" => "bar-chart"],
                                             ["label" => "Icons", "url" => ["/accounting/audit/cut-off-summary-icon"], "icon" => "bar-chart"],
                                        ],*/
                                    ],
                                ],
                            ],
                            ["label" => "Add OR", "url" => ["/accounting/season-or-list/"], "icon" => "plus",'visible' => !Yii::$app->user->isGuest && $user_role == 'TopManagement'],
                            [
                                "label" => "Libraries",
                                "icon" => "cog",
                                "url" => "#",
                                'visible' => !Yii::$app->user->isGuest && $user_role == 'TopManagement',
                                "items" => [
                                     ["label" => "Branches", "url" => ["/accounting/branch"], "icon" => "file"],
                                     ["label" => "Programs", "url" => ["/accounting/program"], "icon" => "file"],
                                     ["label" => "Branch - Program", "url" => ["/accounting/branch-program"], "icon" => "file"],
                                     ["label" => "Seasons", "url" => ["/accounting/season"], "icon" => "file"],
                                     ["label" => "Schools", "url" => ["/accounting/school"], "icon" => "file"],
                                     ["label" => "Packages", "url" => ["/accounting/package"], "icon" => "file"],
                                     ["label" => "Date Restriction", "url" => ["/accounting/date-restriction"], "icon" => "calendar"],
                                     //["label" => "Enhancement Fees", "url" => ["/accounting/branch-program-enhancement"], "icon" => "bar-chart"],
                                     //["label" => "Target Enrolee", "url" => ["/accounting/target-enrolee"], "icon" => "bar-chart"],
                                     //["label" => "Target Expense", "url" => ["/accounting/target-expense"], "icon" => "bar-chart"],
                                ],
                            ],
                            ["label" => "User Management", "url" => ["/user/admin"], "icon" => "users", 'visible' => !Yii::$app->user->isGuest && $user_role == 'TopManagement'],
                            ["label" => "Control Panel", "url" => ["/audit"], "icon" => "users", 'visible' => !Yii::$app->user->isGuest && $user_role == 'TechnicalStaff'],
                        ],

                    ]
                )
                ?>
            </div>

        </div>
    </div>
</div>
