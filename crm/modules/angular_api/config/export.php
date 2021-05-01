<?php

return [
    'class' => 'yii\rest\UrlRule',
    'controller' => 'angular_api/export',
    'pluralize' => false,

    'extraPatterns' => [
        'OPTIONS' => 'options',

        'OPTIONS orders' => 'options',
        'POST orders' => 'orders',

        'OPTIONS declaration' => 'options',
        'POST declaration' => 'declaration',

        'OPTIONS tax-invoice' => 'options',
        'POST tax-invoice' => 'tax-invoice',

        'OPTIONS finstrip' => 'options',
        'POST finstrip' => 'finstrip',

        'OPTIONS finstrip-summary' => 'options',
        'POST finstrip-summary' => 'finstrip-summary',

        'OPTIONS group-search' => 'options',
        'POST group-search' => 'group-search',
    ],
];