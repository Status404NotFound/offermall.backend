<?php
return [
    'class' => 'yii\rest\UrlRule',
    'controller' => 'api/orders',
    'pluralize' => false,
    'tokens' => [
        '{id}' => '<id:\d+>',
    ],
    'extraPatterns' => [
        'OPTIONS {id}' => 'options',

        'POST create-order' => 'create-order',
        'OPTIONS create-order' => 'options',

        'GET payment' => 'payment',
        'OPTIONS payment' => 'options',

        'GET done-payment' => 'done-payment',
        'OPTIONS done-payment' => 'options',

        'POST change-payment-status' => 'change-payment-status',
        'OPTIONS change-payment-status' => 'options',
    ],
];