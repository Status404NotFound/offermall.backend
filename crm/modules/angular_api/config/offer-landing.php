<?php

return [
    'class' => 'yii\rest\UrlRule',
    'controller' => 'angular_api/offer-landing',
    'pluralize' => false,
    'tokens' => [
        '{offer_id}' => '<offer_id:\d+>',
    ],
    'extraPatterns' => [

        'GET list/{offer_id}' => 'landing-list',
        'OPTIONS list/{offer_id}' => 'options',

        'GET forms/{offer_id}' => 'forms',
        'OPTIONS forms/{offer_id}' => 'options',

        'POST save/landing' => 'save-landing',
        'OPTIONS save/landing' => 'options',

        'POST save/transit' => 'save-transit',
        'OPTIONS save/transit' => 'options',

        'GET geo/{offer_id}' => 'geo',
        'OPTIONS geo/{offer_id}' => 'options',

        'GET currency' => 'currency',
        'OPTIONS currency' => 'options',

        'POST save/geo-price' => 'save-geo-price',
        'OPTIONS save/geo-price' => 'options',
    ],
];

