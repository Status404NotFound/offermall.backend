<?php

return [
    'class' => 'yii\rest\UrlRule',
    'controller' => 'angular_api/load',
    'pluralize' => false,
    'tokens' => [
        '{site_id}' => '<id:\d+>',
    ],
    'extraPatterns' => [
        'OPTIONS {id}' => 'options',

        'POST read-steal-log' => 'read-steal-log',
        'OPTIONS read-steal-log' => 'options',

        'POST read-form-log' => 'read-form-log',
        'OPTIONS read-form-log' => 'options',

        'POST status' => 'status',
        'OPTIONS status' => 'options',
    ],
];