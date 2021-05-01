<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => [
        'log',
        'common\modules\user\Bootstrap'
    ],
    'controllerNamespace' => 'console\controllers',
    'components' => [
        'user' => [
            'class' => 'yii\web\User',
            'identityClass' => 'common\modules\user\models\tables\User',
            'enableAutoLogin' => true,
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
                ['class' => 'yii\log\FileTarget',
                    'categories' => ['partner_e'],
                    'levels' => ['error'],
                    'logFile' => '@regorder/log/partner_e.log',
                    'logVars' => []
                ],
                ['class' => 'yii\log\FileTarget',
                    'categories' => ['sended_to_partner'],
                    'levels' => ['info'],
                    'logFile' => '@regorder/log/sended_to_partner.log',
                    'logVars' => []
                ],
            ],
        ],
    ],
    'modules' => [
        'user' => [
            'class' => 'common\modules\user\Module',
        ],
    ],
    'params' => $params,
];
