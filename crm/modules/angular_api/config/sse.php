<?php

return [
    'class' => 'yii\rest\UrlRule',
    'controller' => 'angular_api/sse',
    'pluralize' => false,
    'tokens' => [
        '{id}' => '<id:\d+>',
    ],
    'extraPatterns' => [
        'OPTIONS {id}' => 'options',

        'GET counter' => 'counter',
        'OPTIONS counter' => 'options',

        'GET sse' =>  'sse',
        'OPTIONS sse' =>  'options',
    ],
];