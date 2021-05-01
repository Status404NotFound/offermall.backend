<?php
return [
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'timeZone' => 'Europe/Kiev',
    'components' => [
        'cc_api' => [
            'class' => 'common\components\ccApi\CallsApi',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],

        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => 'localhost',
            'port' => 6379,
            'database' => 0,
        ],

        'view' => [
            'theme' => [
                'pathMap' => [
                    '@app/views' => '@common/theme/inspinia/views'
                ],
            ],
        ],
        'authManager' => [
            'class' => 'common\modules\user\components\DbManager'
        ],
        'ip2location' => [
            'class' => 'application.extensions.ip2location.Geolocation',
            'database' => 'vendor/ip2location/ip2location-php/databases/IPV6-COUNTRY-SAMPLE.BIN',
            'mode' => 'FILE_IO',
        ],
        'turbosms' => [
//            'class' => 'avator\turbosms\Turbosms',
            'class' => 'common\services\sms\Turbosms',
            'sender' => '',
            'login' => '',
            'password' => '',
            'debug' => false, //in debug mode sms not send only add to db table.
        ],
        'serviceSms' => [
//            'class' => 'avator\turbosms\Turbosms',
            'class' => 'common\services\sms\Turbosms',
            'sender' => '',
            'login' => 'cmka',
            'password' => '',
            'debug' => false, //in debug mode sms not send only add to db table.
        ],
    ],
    'as access' => [
        'class' => 'common\modules\user\filters\AccessControl',
        'allowActions' => [
            'site/*',
            'debug/*',
            'genform/*',
            'user/admin/switch',
//            'admin/*',
//            'some-controller/some-action',
            // The actions listed here will be allowed to everyone including guests.
            // So, 'admin/*' should not appear here in the production, of course.
            // But in the earlier stages of your development, you may probably want to
            // add a lot of actions here until you finally completed setting up rbac,
            // otherwise you may not even take a first step.
        ]
    ],
    'bootstrap' => [
        'log',
        'common\modules\user\Bootstrap',
    ],
    'modules' => [
        'user' => [
            'class' => 'common\modules\user\Module',
        ],
    ],
];
