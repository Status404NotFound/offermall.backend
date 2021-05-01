<?php

return [
    'class' => 'yii\rest\UrlRule',
    'controller' => 'wm_api/orders',
    'pluralize' => false,
    'tokens' => [
        '{id}' => '<id:\d+>',
    ],
    'extraPatterns' => [
        'OPTIONS {id}' => 'options',

        'POST my-orders' => 'my-orders',
        'OPTIONS my-orders' => 'options',
    ],
];