<?php

return [
    'class' => 'yii\rest\UrlRule',
    'controller' => 'wm_api/data-list',
    'pluralize' => false,
    'tokens' => [
        '{geo_id}' => '<geo_id:\d+>',
        '{offer_id}' => '<offer_id:\d+>',
        '{flow_id}' => '<flow_id:\d+>',
        '{wm_id}' => '<wm_id:\d+>',
    ],
    'extraPatterns' => [

        'GET webmaster-list' => 'webmaster-list',
        'OPTIONS webmaster-list' => 'options',

        'GET geo' => 'geo',
        'OPTIONS geo' => 'options',

        'GET flow-geo/{flow_id}' => 'flow-geo',
        'OPTIONS flow-geo/{flow_id}' => 'options',

        'GET geo-list' => 'geo-list',
        'OPTIONS geo-list' => 'options',

        'GET offers' => 'offers',
        'OPTIONS offers' => 'options',

        'GET my-offers' => 'my-offers',
        'OPTIONS my-offers' => 'options',

        'GET offers-list/{wm_id}' => 'offers-list',
        'OPTIONS offers-list/{wm_id}' => 'options',

        'GET flows' => 'flows',
        'OPTIONS flows' => 'options',

        'POST wm-offer-flows' => 'wm-offer-flows',
        'OPTIONS wm-offer-flows' => 'options',

        'GET targets' => 'targets',
        'OPTIONS targets' => 'options',

        'GET timezone-list' => 'timezone-list',
        'OPTIONS timezone-list' => 'options',

        'GET status-list' => 'status-list',
        'OPTIONS status-list' => 'options',

        'GET avatar' => 'avatar',
        'OPTIONS avatar' => 'options',

        'GET partners' => 'adverts',
        'OPTIONS partners' => 'options',
    ],
];