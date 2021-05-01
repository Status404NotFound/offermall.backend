<?php

return [
    'class' => 'yii\rest\UrlRule',
    'controller' => 'wm_api/offers',
    'pluralize' => false,
    'tokens' => [
        '{id}' => '<id:\d+>',
    ],
    'extraPatterns' => [
        'OPTIONS {id}' => 'options',

        'POST offer-list' => 'offer-list',
        'OPTIONS offer-list' => 'options',

        'GET geo-select' => 'geo-select',
        'OPTIONS geo-select' => 'options',

        'GET targets-select' => 'targets-select',
        'OPTIONS targets-select' => 'options',

        'GET offers-select' => 'offers-select',
        'OPTIONS offers-select' => 'options',

        'GET offer-info/{id}' => 'offer-info',
        'OPTIONS offer-info/{id}' => 'options',

        'POST take-offer/{id}' => 'take-offer',
        'OPTIONS take-offer/{id}' => 'options',
    ],
];