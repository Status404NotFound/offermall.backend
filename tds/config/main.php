<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-tds',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'tds\controllers',
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-tds',
        ],
        'user' => [
            'identityClass' => 'common\modules\User\models\tables\User',
//            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-tds', 'httpOnly' => true],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the tds
            'name' => 'advanced-tds',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler'=>[
            'errorAction'=>'site/error',
        ],

        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                '<controller:\w+>/<id:\d+>' => '<controller>/view',
                '<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
            ],
        ],
    ],
    'modules' => [
        'genform' => [
            'class' => 'tds\modules\genform\GenForm',
        ],
    ],
    'params' => $params,
];
