<?php

return [
    'class' => 'yii\rest\UrlRule',
    'controller' => 'api/api-key',
    'pluralize' => false,
    'tokens' => [
        '{id}' => '<id:\d+>',
    ],
    'extraPatterns' => [
        'OPTIONS {id}' => 'options',

        'GET new' => 'new',
        'OPTIONS new' => 'options',

        'GET get-all-keys' => 'get-all-keys',
        'OPTIONS get-all-keys' => 'options',

        'POST change-status' => 'change-status',
        'OPTIONS change-status' => 'options',

        'POST delete-key' => 'delete-key',
        'OPTIONS delete-key' => 'options',
    ],
];