<?php

return [
    'class' => 'yii\rest\UrlRule',
    'controller' => 'angular_api/statistics',
    'pluralize' => false,
    'tokens' => [
        '{id}' => '<id:\d+>',
    ],
    'extraPatterns' => [
        'OPTIONS {id}' => 'options',

        'POST offer' => 'offer',
        'OPTIONS offer' => 'options',

        'POST hourly' => 'hourly',
        'OPTIONS hourly' => 'options',

        'POST daily' => 'daily',
        'OPTIONS daily' => 'options',

        'POST advert' => 'advert',
        'OPTIONS advert' => 'options',

        'POST geo' => 'geo',
        'OPTIONS geo' => 'options',

        'POST reject' => 'reject',
        'OPTIONS reject' => 'options',

        'POST wm' => 'wm',
        'OPTIONS wm' => 'options',

        'POST autolead' => 'autolead',
        'OPTIONS autolead' => 'options',

        'POST delivery-sku' => 'delivery-sku',
        'OPTIONS delivery-sku' => 'options',

        'GET browser' => 'browser',
        'OPTIONS browser' => 'options',

        'GET os' => 'os',
        'OPTIONS os'  => 'options',

        'GET countries' => 'countries',
        'OPTIONS countries' => 'options',

        'POST live/offer' => 'live-offer',
        'OPTIONS live/offer' => 'options',

        'POST live/super-wm' => 'live-super-wm',
        'OPTIONS live/super-wm' => 'options',

        'POST live/advert' => 'live-advert',
        'OPTIONS live/advert' => 'options',

        'POST live/geo' => 'live-geo',
        'OPTIONS live/geo' => 'options',
    ],
];