<?php

return [
    'class' => 'yii\rest\UrlRule',
    'controller' => 'angular_api/order',
    'pluralize' => false,
    'tokens' => [
        '{order_id}' => '<order_id:\d+>',
        '{offer_id}' => '<offer_id:\d+>',
        '{message}' => '<message:\d+>',
    ],
    'extraPatterns' => [
        'POST send-to-partner-crm' => 'send-to-partner-crm',
        'OPTIONS send-to-partner-crm' => 'options',

        'OPTIONS' => 'options',
        'OPTIONS {order_id}' => 'options',
        'DELETE {order_id}' => 'delete',

        'POST orders' => 'orders',
        'OPTIONS orders' => 'options',

        'GET order-user' => 'order-user',
        'OPTIONS order-user' => 'options',

        'GET order-offer' => 'order-offer',
        'OPTIONS order-offer' => 'options',

        'GET comment/{order_id}' => 'get-comment',
        'OPTIONS comment/{order_id}' => 'options',

        'POST comment' => 'set-comment',
        'OPTIONS comment' => 'options',

        'POST bitrix-flag' => 'bitrix-flag',
        'OPTIONS bitrix-flag' => 'options',

        'GET change-geo/{offer_id}' => 'change-geo',
        'OPTIONS change-geo/{offer_id}' => 'options',

        'POST change-geo' => 'save-change-geo',
        'OPTIONS change-geo' => 'options',

        'POST declaration' => 'declaration',
        'OPTIONS declaration' => 'options',

        'POST payment-data' => 'payment-data',
        'OPTIONS payment-data' => 'options',

        'POST information' => 'information',
        'OPTIONS information' => 'options',

        'POST create' => 'create',
        'OPTIONS create' => 'options',

        'GET status-reason' => 'status-reason',
        'OPTIONS status-reason' => 'options',
    ],
];
