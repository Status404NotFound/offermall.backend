<?php

return [
    'class' => 'yii\rest\UrlRule',
    'controller' => 'angular_api/order-status',
    'pluralize' => false,
    'tokens' => [
        '{status_id}' => '<status_id:\d+>',
    ],
    'extraPatterns' => [
        'PUT change' => 'change',
        'OPTIONS change' => 'options',

        'GET reject-reason/{status_id}' => 'reject-reason',
        'OPTIONS reject-reason/{status_id}' => 'options',

        'GET reject-reasons' => 'reject-reasons',
        'OPTIONS reject-reasons' => 'options',
    ],
];