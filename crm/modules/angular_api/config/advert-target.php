<?php

return [
    'class' => 'yii\rest\UrlRule',
    // AdvertTargetController
    'controller' => 'angular_api/partner-target',
    'pluralize' => false,
    'tokens' => [
        '{offer_id}' => '<offer_id:\d+>',
    ],
    'extraPatterns' => [
        'GET {offer_id}' => 'view',
        'OPTIONS {offer_id}' => 'options',

        'POST' => 'create',
        'OPTIONS' => 'options',

        'GET geo-list' => 'geo-list',
        'OPTIONS geo-list' => 'options',

        'GET partner-list' => 'advert-list',
        'OPTIONS partner-list' => 'options',

        'GET currency-list' => 'currency-list',
        'OPTIONS currency-list' => 'options',
    ],
];