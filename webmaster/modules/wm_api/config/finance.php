<?php

return [
    'class' => 'yii\rest\UrlRule',
    'controller' => 'wm_api/finance',
    'pluralize' => false,
    'tokens' => [
        '{id}' => '<id:\d+>',
    ],
    'extraPatterns' => [
        'OPTIONS {id}' => 'options',

        'GET finance-data' => 'finance-data',
        'OPTIONS finance-data' => 'options',

        'GET balance' => 'balance',
        'OPTIONS balance' => 'options',

        'GET check' => 'check',
        'OPTIONS check' => 'options',

        'GET translate' => 'translate',
        'OPTIONS translate' => 'options',

        'POST history-data' => 'history-data',
        'OPTIONS history-data' => 'options',

        'POST leads-in-hold' => 'hold',
        'OPTIONS leads-in-hold' => 'options',

        'POST payment' => 'payment',
        'OPTIONS payment' => 'options',

        'GET payment-order' => 'payment-order',
        'OPTIONS payment-order' => 'options',
    ],
];