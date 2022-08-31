<?php if(!Yii::$app->user->isGuest){ ?>

<aside class="main-sidebar">

    <section class="sidebar">

        <!-- Sidebar user panel -->
        <div class="user-panel">
            <div class="pull-left image">
                <img src="http://gravatar.com/avatar/<?= Yii::$app->user->identity->profile->gravatar_id ?>?s=160" class="img-circle"  alt="User Image"/>
            </div>
            <div class="pull-left info">
                <p><?= Yii::$app->user->identity->fullName ?></p>

                <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
            </div>
        </div>

        <?= dmstr\widgets\Menu::widget(
            [
                'options' => ['class' => 'sidebar-menu tree', 'data-widget'=> 'tree'],
                'items' => [
                    ['label' => 'Main Navigation', 'options' => ['class' => 'header']],
                    ['label' => 'Dashboard', 'icon' => 'line-chart', 'url' => ['/debug']],
                    ['label' => 'Daily Operations', 'options' => ['class' => 'header']],
                    ['label' => 'Schools', 'icon' => 'building', 'url' => ['/pos/pos-school']],
                    ['label' => 'Customers', 'icon' => 'users', 'url' => ['/pos/pos-customer']],
                    ['label' => 'Enrolment', 'icon' => 'user', 'url' => ['/pos/pos-enrolment']],
                    ['label' => 'Income', 'icon' => 'credit-card', 'url' => ['/pos/pos-income']],
                    ['label' => 'Expense', 'icon' => 'shopping-cart', 'url' => ['/pos/pos-expense']],
                    ['label' => 'Audit', 'icon' => 'server', 'url' => ['/pos/pos-audit']],
                    [
                        'label' => 'Reports',
                        'icon' => 'file',
                        'url' => '#',
                        'items' => [
                            ['label' => 'Enrolment Report', 'icon' => 'file', 'url' => ['/pos/pos-report/enrolment']],
                            ['label' => 'Income Report', 'icon' => 'file', 'url' => ['/pos/pos-report/income']],
                            ['label' => 'Expense Report', 'icon' => 'file', 'url' => ['/pos/pos-report/expense']],
                            ['label' => 'Audit Report', 'icon' => 'file', 'url' => ['/pos/pos-report/audit']],
                            /*['label' => 'Monthly Report', 'icon' => 'file', 'url' => ['/pos/pos-report/monthly']],
                            ['label' => 'Cutoff Report', 'icon' => 'file', 'url' => ['/pos/pos-report/cutoff']],
                            ['label' => 'Financial Statement', 'icon' => 'file', 'url' => ['/pos/pos-report/financial-statement']],*/
                        ],
                    ],
                    ['label' => 'Archive', 'icon' => 'archive', 'url' => ['/pos/pos-season']],

                    ['label' => 'Admin Tools', 'options' => ['class' => 'header']],
                    ['label' => 'Official Receipts', 'icon' => 'file-text-o', 'url' => ['/pos/pos-official-receipt']],
                    ['label' => 'Backtrack', 'icon' => 'calendar', 'url' => ['/pos/pos-backtrack']],
                    [
                        'label' => 'Libraries',
                        'icon' => 'gear',
                        'url' => '#',
                        'items' => [
                            ['label' => 'Branches', 'icon' => 'circle-o', 'url' => ['/pos/pos-branch'],],
                            ['label' => 'Programs', 'icon' => 'circle-o', 'url' => ['/pos/pos-program'],],
                            ['label' => 'Branch - Programs', 'icon' => 'circle-o', 'url' => ['/pos/pos-branch-program'],],
                            ['label' => 'Seasons', 'icon' => 'circle-o', 'url' => ['/pos/pos-season'],],
                            ['label' => 'Accounts', 'icon' => 'circle-o', 'url' => ['/pos/pos-account'],],
                            ['label' => 'Products', 'icon' => 'circle-o', 'url' => ['/pos/pos-product'],],
                            ['label' => 'Items', 'icon' => 'circle-o', 'url' => ['/pos/pos-item'],],
                            ['label' => 'Vendors', 'icon' => 'circle-o', 'url' => ['/pos/pos-vendor'],],
                            ['label' => 'Income Types', 'icon' => 'circle-o', 'url' => ['/pos/pos-income-type'],],
                            ['label' => 'Expense Types', 'icon' => 'circle-o', 'url' => ['/pos/pos-expense-type'],],
                            ['label' => 'Discount Types', 'icon' => 'circle-o', 'url' => ['/pos/pos-discount-type'],],
                            ['label' => 'Product Types', 'icon' => 'circle-o', 'url' => ['/pos/pos-product-type'],],
                            ['label' => 'Enrolment Types', 'icon' => 'circle-o', 'url' => ['/pos/pos-enrolment-type'],],
                            ['label' => 'Payment Methods', 'icon' => 'circle-o', 'url' => ['/pos/pos-amount-type'],],
                        ],
                    ],
                    ['label' => 'User Accounts', 'icon' => 'user-plus', 'url' => ['/user/admin']],
                ],
            ]
        ) ?>

    </section>

</aside>

<?php } ?>