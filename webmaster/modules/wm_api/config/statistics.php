<?php

return [
    'class' => 'yii\rest\UrlRule',
    'controller' => 'wm_api/statistics',
    'pluralize' => false,
    'tokens' => [
        '{id}' => '<id:\d+>',
        '{offer_id}' => '<offer_id:\d+>',
        '{flow_id}' => '<flow_id:\d+>'
    ],
    'extraPatterns' => [
        'OPTIONS {id}' => 'options',

        'GET {id}' => 'view',
        'OPTIONS view' => 'options',

        'GET offers-select' => 'offers-select',
        'OPTIONS offers-select' => 'options',

        'GET sub-select' => 'sub-select',
        'OPTIONS sub-select' => 'options',

        'GET geo-select' => 'geo-select',
        'OPTIONS geo-select' => 'options',

        'POST hourly' => 'hourly',
        'OPTIONS hourly' => 'options',

        'POST daily' => 'daily',
        'OPTIONS daily' => 'options',

        'POST flows' => 'flows',
        'OPTIONS flows' => 'options',

        'POST offers' => 'offers',
        'OPTIONS offers' => 'options',

        'POST sub' => 'sub',
        'OPTIONS sub' => 'options',

        'POST delivery-sku' => 'delivery-sku',
        'OPTIONS delivery-sku' => 'options',

        'GET flows-select' => 'flows-select',
        'OPTIONS flows-select' => 'options',

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