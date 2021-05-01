<?php

return [
    'class' => 'yii\rest\UrlRule',
    'controller' => 'angular_api/offer',
    'pluralize' => false,
    'tokens' => [
        '{id}' => '<id:\d+>',
        '{offer_id}' => '<offer_id:\d+>',
    ],
    'extraPatterns' => [
        'POST' => 'create',
        'OPTIONS' => 'options',

//        'GET index' => 'index',
//        'OPTIONS index' => 'options',

        'POST offers' => 'offers',
        'OPTIONS offers' => 'options',

        'POST offers-info' => 'offers-info',
        'OPTIONS offers-info' => 'options',

        'GET {offer_id}' => 'view',
        'OPTIONS {offer_id}' => 'options',

        'GET offer/{offer_id}' => 'offer',
        'OPTIONS offer/{offer_id}' => 'options',

        'POST product-list' => 'product-list',
        'OPTIONS product-list' => 'options',

        'PUT {offer_id}' => 'update',

        'POST status' => 'status',
        'OPTIONS status' => 'options',
    ],
];