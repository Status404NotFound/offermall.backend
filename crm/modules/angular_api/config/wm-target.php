<?php

return [
    'class' => 'yii\rest\UrlRule',
    'controller' => 'angular_api/wm-target',
    'pluralize' => false,
    'tokens' => [
        '{offer_id}' => '<offer_id:\d+>',
    ],
    'extraPatterns' => [
        'GET {offer_id}' => 'view',
        'OPTIONS {offer_id}' => 'options',

        'POST' => 'create',
        'OPTIONS' => 'options',

        'POST advert-target-status-list' => 'advert-target-status-list',
        'OPTIONS advert-target-status-list' => 'options',

        'POST status-list' => 'status-list',
        'OPTIONS status-list' => 'options',

        'POST target-geo-list' => 'target-geo-list',
        'OPTIONS target-geo-list' => 'options',

        'GET wm-list' => 'wm-list',
        'OPTIONS wm-list' => 'options',
    ],
];