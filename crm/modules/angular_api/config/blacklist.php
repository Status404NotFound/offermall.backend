<?php

return [
    'class' => 'yii\rest\UrlRule',
    'controller' => 'angular_api/blacklist',
    'pluralize' => false,
    'tokens' => [
        '{id}' => '<id:\d+>',
    ],
    'extraPatterns' => [
        'POST list' => 'list',
        'OPTIONS list' => 'options',

        'POST add' => 'add',
        'OPTIONS add' => 'options',

        'POST status-reason' => 'status-reason',
        'OPTIONS status-reason' => 'options',

        'POST change-status' => 'change-status',
        'OPTIONS change-status' => 'options',

        'POST delete-customer' => 'delete-customer',
        'OPTIONS delete-customer' => 'options',
    ],
];