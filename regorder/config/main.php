<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-regorder',
    'name' => 'Advert Fish Order Registration',
    'version' => '2.0',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'regorder\controllers',
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-regorder',
        ],
        'session' => [
            // this is the name of the session cookie used for login on the regorder
            'name' => 'regorder',
        ],
        'turbosms' => [
//            'class' => 'avator\turbosms\Turbosms',
            'class' => 'common\services\sms\Turbosms',
            'sender' => 'Advert Fish',
            'login' => 'AdvertCRM',
            'password' => 'advertsender',
            'debug' => false, //in debug mode sms not send only add to db table.
        ],
        'log' => [
            'flushInterval' => 1,
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'exportInterval' => 1,
                    'categories' => [
                        'debug',
                    ],
                    'levels' => ['info', 'trace'],
                    'logFile' => '@regorder/log/debug/debug.txt',
                    'logVars' => []
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'exportInterval' => 1,
                    'categories' => [
                        'error',
                        'yii\db\*',
                        'yii\web\HttpException:*',
                    ],
                    'except' => [
                        'yii\web\HttpException:404',
                    ],
                    'levels' => ['error', 'warning'],
                    'logFile' => '@regorder/log/errors/error.txt',
                    'logVars' => []
                ],
                ['class' => 'yii\log\FileTarget',
                    'exportInterval' => 1,
                    'categories' => [
                        'user_activity',
                    ],
                    'prefix' => function ($message) {
                        $user = Yii::$app->has('user', true) ? Yii::$app->get('user') : null;
                        $userID = $user ? $user->getId(false) : '-';
                        return "user---[$userID]";
                    },
                    'levels' => ['info'],
                    'logFile' => '@regorder/log/user_activity/user_activity.txt',
                    'logVars' => [],  // hide logVars is ON
                ],
                ['class' => 'yii\log\FileTarget',
                    'categories' => ['my_land_crm_e'],
                    'levels' => ['error'],
                    'logFile' => '@regorder/log/my_land_crm_e.log',
                    'logVars' => []
                ],
                ['class' => 'yii\log\FileTarget',
                    'categories' => ['my_land_crm_postback_e'],
                    'levels' => ['error'],
                    'logFile' => '@regorder/log/my_land_crm_postback_e.log',
                    'logVars' => []
                ],
                ['class' => 'yii\log\FileTarget',
                    'categories' => ['partner_e'],
                    'levels' => ['error'],
                    'logFile' => '@regorder/log/partner_e.log',
                    'logVars' => []
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'error/index',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                '<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
            ],
        ],
    ],
    'params' => $params,
];