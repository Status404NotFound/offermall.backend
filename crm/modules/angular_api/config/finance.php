<?php

return [
    'class' => 'yii\rest\UrlRule',
    'controller' => 'angular_api/finance',
    'pluralize' => false,
    'tokens' => [
        '{advert_id}' => '<advert_id:\d+>',
    ],
    'extraPatterns' => [
        'OPTIONS checks' => 'options',
        'POST checks' => 'checks',

        'OPTIONS funds' => 'options',
        'POST funds' => 'funds',

        'OPTIONS month-balance' => 'options',
        'POST month-balance' => 'month-balance',

        'OPTIONS send-sms' => 'options',
        'POST send-sms' => 'send-sms',

        'OPTIONS verify-sms' => 'options',
        'POST verify-sms' => 'verify-sms',

        'OPTIONS change-balance' => 'options',
        'POST change-balance' => 'change-balance',

        'GET turbo-sms-balance' => 'turbo-sms-balance',
        'OPTIONS turbo-sms-balance' => 'options',

        'OPTIONS currency-rate' => 'options',
        'OPTIONS get-currency-rate' => 'options',
        'POST get-currency-rate' => 'get-currency-rate',
        'POST currency-rate' => 'currency-rate',
    ],
];