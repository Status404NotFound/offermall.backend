<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-webmaster',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'webmaster\controllers',
    'components' => [
        'request' => [
//            'csrfParam' => '_csrf-webmaster',
//            'baseUrl' => '',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ]
        ],
        'user' => [
            'identityClass' => 'common\modules\user\models\tables\User',
            'enableAutoLogin' => true,
        ],
        'session' => [
            // this is the name of the session cookie used for login on the webmaster
            'name' => 'advanced-webmaster',
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
        'errorHandler' => [
            'errorAction' => 'error/index',
        ],
//        'urlManager' => [
//            'enablePrettyUrl' => true,
//            'showScriptName' => false,
//            'rules' => [
//                '<controller:\w+>/<id:\d+>' => '<controller>/view',
//                '<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
//                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
//            ],
//        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => true,

            'rules' => [
                /** USER CONTROLLER **/
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'wm_api/user',
                    'pluralize' => false,
                    'tokens' => [
                        '{id}' => '<id:\d+>',
                    ],
                    'extraPatterns' => [
                        'OPTIONS {id}' => 'options',
                        'POST login' => 'login',
                        'OPTIONS login' => 'options',
                        'POST signup' => 'signup',
                        'OPTIONS signup' => 'options',
                        'POST confirm' => 'confirm',
                        'OPTIONS confirm' => 'options',
                        'POST password-reset-request' => 'password-reset-request',
                        'OPTIONS password-reset-request' => 'options',
                        'POST password-reset-token-verification' => 'password-reset-token-verification',
                        'OPTIONS password-reset-token-verification' => 'options',
                        'POST password-reset' => 'password-reset',
                        'OPTIONS password-reset' => 'options',
                    ]
                ],
                /** SETTING CONTROLLER **/
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'wm_api/setting',
                    'pluralize' => false,
                    'tokens' => [
                        '{id}' => '<id:\d+>',
                    ],
                    'extraPatterns' => [
                        'GET public' => 'public',
                        'OPTIONS public' => 'options',
                    ]
                ],
                /** FLOW CONTROLLER **/
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'wm_api/flow',
                    'pluralize' => false,
                    'tokens' => [
                        '{id}' => '<id:\d+>',
                        '{flow_id}' => '<flow_id:\d+>',
                        '{offer_id}' => '<offer_id:\d+>',
                    ],
                    'extraPatterns' => [
                        'OPTIONS {flow_id}' =>  'options',
                        'GET {flow_id}' => 'view',
                        'OPTIONS view' =>  'options',

//                        'POST flow-list' => 'flow-list',
//                        'OPTIONS flow-list' => 'options',
//
/// //                        'POST flow-list' => 'flow-list',
//                        'OPTIONS flow-list' => 'options',

                        'GET create-offers' => 'create-offers',
                        'OPTIONS create-offers' => 'options',

                        'GET offer-landings/{offer_id}' => 'offer-landings',
                        'OPTIONS offer-landings/{offer_id}' => 'options',

                        'GET target/{offer_id}' => 'target',
                        'OPTIONS target/{offer_id}' => 'options',

                        'GET flow-list' => 'flow-list',
                        'OPTIONS flow-list' => 'options',

//                        'OPTIONS {flow_id}' => 'options',
//                        'PUT {flow_id}' => 'update',
                        'OPTIONS edit/{flow_id}' => 'options',
                        'PUT edit/{flow_id}' => 'edit',

//                        'OPTIONS {flow_id}' => 'options',
//                        'DELETE {flow_id}' => 'delete',
                        'OPTIONS delete/{flow_id}' => 'options',
                        'DELETE delete/{flow_id}' => 'delete',

//                        'POST' => 'create',
//                        'OPTIONS' => 'options',
                        'POST create' => 'create',
                        'OPTIONS create' => 'options',
                    ],
                ],
                /** Statistics CONTROLLER **/
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'wm_api/statistics',
                    'pluralize' => false,
                    'tokens' => [
                        '{id}' => '<id:\d+>',
                        '{offer_id}' => '<offer_id:\d+>',
                        '{flow_id}' => '<flow_id:\d+>'
                    ],
                    'extraPatterns' => [
                        'OPTIONS {id}' =>  'options',

                        'GET {id}' => 'view',
                        'OPTIONS view' =>  'options',

                        'GET offers-select' => 'offers-select',
                        'OPTIONS offers-select' => 'options',

                        'GET sub/{flow_id}' => 'sub',
                        'OPTIONS sub/{flow_id}' => 'options',

                        'GET geo/{offer_id}' => 'geo',
                        'OPTIONS geo/{offer_id}' => 'options',

                        'GET geo' => 'geo',
                        'OPTIONS geo' => 'options',

                        'POST hourly' => 'hourly',
                        'OPTIONS hourly' => 'options',

                        'POST daily' => 'daily',
                        'OPTIONS daily' => 'options',

                        'POST flows' => 'flows',
                        'OPTIONS flows' => 'options',

                        'POST offers' => 'offers',
                        'OPTIONS offers' => 'options',

                        'GET flows-select/{offer_id}' => 'flows-select',
                        'OPTIONS flows-select/{offer_id}' => 'options',
                    ],
                ],
                /** Profile CONTROLLER **/
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'wm_api/profile',
                    'pluralize' => false,
                    'tokens' => [
                        '{id}' => '<id:\d+>',
                    ],
                    'extraPatterns' => [
                        'OPTIONS {id}' =>  'options',

                        'GET data' => 'data',
                        'OPTIONS data' =>  'options',

                        'OPTIONS update' => 'options',
                        'PUT update' => 'update',
                    ],
                ],
                /** Finance CONTROLLER **/
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'wm_api/finance',
                    'pluralize' => false,
                    'tokens' => [
                        '{id}' => '<id:\d+>',
                    ],
                    'extraPatterns' => [
                        'OPTIONS {id}' =>  'options',

                        'GET data' => 'data',
                        'OPTIONS data' =>  'options',

                        'OPTIONS payment' => 'options',
                        'POST payment' => 'payment',
                    ],
                ],
                /** Offers CONTROLLER **/
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'wm_api/offers',
                    'pluralize' => false,
                    'tokens' => [
                        '{id}' => '<id:\d+>',
                    ],
                    'extraPatterns' => [
                        'OPTIONS {id}' =>  'options',

                        'POST offer-list' => 'offer-list',
                        'OPTIONS offer-list' => 'options',

                        'POST my-offer-list' => 'my-offer-list',
                        'OPTIONS my-offer-list' => 'options',

                        'GET geo-select' => 'geo-select',
                        'OPTIONS geo-select' => 'options',

                        'GET targets-select' => 'targets-select',
                        'OPTIONS targets-select' => 'options',

                        'GET offers-select' => 'offers-select',
                        'OPTIONS offers-select' => 'options',

                        'GET info' => 'info',
                        'OPTIONS info' => 'options',
                    ],
                ],
            ]
        ],
        'response' => [
            'class' => 'yii\web\Response',
            'on beforeSend' => function ($event) {

                $response = $event->sender;
                if ($response->format == 'html') {
                    return $response;
                }

                $responseData = $response->data;

                if (is_string($responseData) && json_decode($responseData)) {
                    $responseData = json_decode($responseData, true);
                }

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
    ],
    'modules' => [
        'wm_api' => [
            'class' => 'webmaster\modules\wm_api\Module',
        ],
    ],
    'params' => $params,
];
