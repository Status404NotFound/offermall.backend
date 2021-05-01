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
        'user' => [
            'identityClass' => 'common\modules\user\models\tables\User',
            'enableAutoLogin' => true,
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
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => true,

            'rules' => [
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'angular_api/user',
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
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'angular_api/setting',
                    'pluralize' => false,
                    'tokens' => [
                        '{id}' => '<id:\d+>',
                    ],
                    'extraPatterns' => [
                        'GET public' => 'public',
                        'OPTIONS public' => 'options',
                    ]
                ],
                /**
                 * PRODUCT CONTROLLER
                 */
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'angular_api/product',
                    'pluralize' => false,
                    'tokens' => [
                        '{id}' => '<id:\d+>',
                        '{product_id}' => '<product_id:\d+>',
                    ],
                    'extraPatterns' => [
                        'OPTIONS {id}' => 'options',
                        'OPTIONS {product_id}' => 'options',
                        'OPTIONS index' => 'options',
                        'OPTIONS product-sku' => 'options',

                        'GET index' => 'index',
                        'GET {product_id}' => 'view',

                        'PUT {product_id}' => 'update',
//                        'PATCH {product_id}' => 'update',

                        'POST product-sku' => 'product-sku',

                        'POST create' => 'create',
                        'OPTIONS create' => 'options',
                    ],
                ],

                /**
                 * OFFER CONTROLLER
                 */
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'angular_api/offer',
                    'pluralize' => false,
                    'tokens' => [
                        '{id}' => '<id:\d+>',
                        '{offer_id}' => '<offer_id:\d+>',
                    ],
                    'extraPatterns' => [
                        'OPTIONS {id}' => 'options',
                        'OPTIONS index' => 'options',
                        'OPTIONS create' => 'options',
                        'OPTIONS product-list' => 'options',
                        'OPTIONS offer-list' => 'options',
                        'OPTIONS all-offers' => 'options',

                        'GET index' => 'index',
                        'GET offer-list' => 'offer-list',
                        'GET all-offers' => 'all-offers',
                        'GET {offer_id}' => 'view',

                        'POST product-list' => 'product-list',
                        'POST create' => 'create',

                        'PUT {offer_id}' => 'update',

                    ],
                ],


                /**
                 * OFFER-LANDING CONTROLLER
                 */
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'angular_api/offer-landing',
                    'pluralize' => false,
                    'tokens' => [
                        '{id}' => '<id:\d+>',
                        '{offer_id}' => '<offer_id:\d+>',
                    ],
                    'extraPatterns' => [
                        'OPTIONS {id}' => 'options',

                        'GET list{offer_id}' => 'list',
                        'OPTIONS list{offer_id}' => 'options',

                        'GET forms{offer_id}' => 'forms',
                        'OPTIONS forms{offer_id}' => 'options',
                    ],
                ],


                /**
                 * ADVERT-TARGET CONTROLLER
                 */
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'angular_api/advert-target',
                    'pluralize' => false,
                    'tokens' => [
                        '{id}' => '<id:\d+>',
                    ],
                    'extraPatterns' => [
                        'OPTIONS status-list' => 'options',
                        'OPTIONS geo-list' => 'options',
                        'OPTIONS advert-list' => 'options',
                        'OPTIONS currency-list' => 'options',
                        'OPTIONS create' => 'options',

                        'POST create' => 'create',
                        'POST status-list' => 'status-list',
                        'POST geo-list' => 'geo-list',
                        'POST advert-list' => 'advert-list',
                        'POST currency-list' => 'currency-list',
                    ],
                ],

                /**
                 * SKU CONTROLLER
                 */
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'angular_api/sku',
                    'pluralize' => false,
                    'tokens' => [
                        '{id}' => '<id:\d+>',
                        '{product_id}' => '<product_id:\d+>',
                        '{sku_id}' => '<sku_id:\d+>',
                    ],
                    'extraPatterns' => [
                        'OPTIONS {id}' => 'options',
                        'OPTIONS index' => 'options',
                        'OPTIONS sku-product-list' => 'options',
//                        'OPTIONS create' => 'options',

                        'GET index' => 'index',
                        'GET {product_id}' => 'index',
                        'GET sku-product-list' => 'index',
//                        'GET {product_id}' => 'view',
                        'GET {sku_id}' => 'view',
                        'POST create' => 'create',
                        'OPTIONS create' => 'options',
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
        'angular_api' => [
            'class' => 'crm\modules\angular_api\Module',
        ],
    ],
    'params' => $params,
];
