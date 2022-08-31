<?php
return [
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
            'transport' => [
            'class' => 'Swift_SmtpTransport',
            'host' => 'smtp.gmail.com',
            'username' => 'donotreply@toprankreviewacademy.com',
            'password' => 't0pR57k@zFcS',
            'port' => '587',
            'encryption' => 'tls',
            ],
        ],
        'view' => [
         'theme' => [
             'pathMap' => [
                '@frontend/views' => '@common/modules/pos/views/ui'
             ],
         ],
    ],
    ],
    'modules' => [
        'accounting' => [
            'class' => 'common\modules\accounting\Accounting',
        ],
        'pos' => [
            'class' => 'common\modules\pos\Pos',
        ],
        'gridview' =>  [
            'class' => '\kartik\grid\Module'
        ],
        'audit' => [
            'class' => 'bedezign\yii2\audit\Audit',
            'accessRoles' => ['TopManagement'],
        ],
        'user' => [
            'class' => 'dektrium\user\Module',
            'admins' => ['markespiritu'],
            'enableRegistration' => false,
            'enableConfirmation' => false,
            'enablePasswordRecovery' => false,
            'controllerMap' => [
                'admin' => [
                    'class' => 'dektrium\user\controllers\AdminController',
                    'as access' => [
                        'class' => 'yii\filters\AccessControl',
                        'rules' => [
                            [
                                'allow' => true,
                                'roles' => ['TopManagement','TechnicalStaff'],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'rbac' => [
            'class' => 'dektrium\rbac\RbacWebModule',
        ]
    ],
];
