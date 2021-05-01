<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);
return [
    'id' => 'app-crm',
    'name' => 'Advert Fish CRM 2.0',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'crm\controllers',
    'controllerMap' => [
        'export' => 'phpnt\exportFile\controllers\ExportController'
    ],
    'components' => [
        'request' => [
//            'csrfParam' => '_csrf-crm',
            'baseUrl' => '',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ]
        ],
        'session' => [
            // this is the name of the session cookie used for login on the frontend
            'name' => 'advanced-crm',
        ],
        'sse' => [
            'class' => \odannyc\Yii2SSE\LibSSE::class,
        ],
        'user' => [
            'identityClass' => 'common\modules\user\models\tables\User',
            'enableAutoLogin' => true,
        ],
        'log' => [
            'flushInterval' => 1,
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                ['class' => 'yii\log\FileTarget',
                    'exportInterval' => 1,
                    'categories' => [
                        'debug',
                    ],
                    'levels' => ['info', 'trace'],
                    'logFile' => '@crm/log/debug/debug.txt',
                    'logVars' => []
                ],
                ['class' => 'yii\log\FileTarget',
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
                    'logFile' => '@crm/log/errors/error.txt',
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
                    'logFile' => '@crm/log/user_activity/user_activity.txt',
                    'logVars' => [],  // hide logVars is ON
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => true,

            'rules' => array_merge(
                require(__DIR__ . '/../modules/angular_api/config/main.php')
            ),
        ],
        'response' => [
            'class' => 'yii\web\Response',
            'on beforeSend' => function ($event) {
                $response = $event->sender;

//                \common\helpers\FishHelper::debug($response->format, 0, 0);
//                \common\helpers\FishHelper::debug('=================================================' . PHP_EOL . PHP_EOL, 0, 0);

                if ($response->format == 'html') {
                    return $response;
                }
                if ($response->format == 'pdf') {  // For printing declarations ->>> yii2-reportico || https://github.com/chrmorandi/yii2-jasper
                    \common\helpers\FishHelper::debug('=================================================' . PHP_EOL . PHP_EOL, 0, 0);

                    return $response;
                }
//                if ($response->format == 'raw') {
////                    \common\helpers\FishHelper::debug('=================================================' . PHP_EOL . PHP_EOL, 0, 0);
//                    return $response;
//                }

                $responseData = $response->data;
                if (is_string($responseData) && json_decode($responseData)) {
                    $responseData = json_decode($responseData, true);
                }
//                if ($responseData instanceof common\services\ValidateException) {
//                    $response->data = [
//                        'success' => true,
//                        'status' => 200,
//                        'data' => ['warning_message' => $responseData->getMessages()],
//                    ];
//                } else
                if ($response->statusCode >= 200 && $response->statusCode <= 299) {
                    $response->data = [
                        'success' => true,
                        'status' => $response->statusCode,
                        'data' => $responseData,
                    ];
                } else {
                    $response->data = [
                        'success' => false,
                        'status' => $response->statusCode,
                        'data' => $responseData,
                    ];

                }
                return $response;
            },
        ],
        'mail' => [
            'class' => 'yii\swiftmailer\Mailer',
            'useFileTransport' => true,
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtp.yandex.ru',  // e.g. smtp.mandrillapp.com or smtp.gmail.com
                'username' => $params['smtpEmail'],
                'password' => 'B23r32r32ffd',
                'port' => '587',
                'encryption' => 'tls',
            ],
        ],
    ],
    'modules' => [
        'angular_api' => [
            'class' => 'crm\modules\angular_api\Module',
        ],
    ],
    'params' => $params,
];