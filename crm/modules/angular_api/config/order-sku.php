<?php

return [
    'class' => 'yii\rest\UrlRule',
    'controller' => 'angular_api/order-sku',
    'pluralize' => false,
    'tokens' => [
        '{order_id}' => '<order_id:\d+>',
    ],
    'extraPatterns' => [
        'POST' => 'create',
        'OPTIONS' => 'options',

        'GET {order_id}' => 'view',
        'OPTIONS {order_id}' => 'options',

        'GET partner-sku/{order_id}' => 'partner-sku',
        'OPTIONS partner-sku/{order_id}' => 'options',
    ],
];