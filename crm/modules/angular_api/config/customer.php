<?php

return [
    'class' => 'yii\rest\UrlRule',
    'controller' => 'angular_api/customer',
    'pluralize' => false,
    'tokens' => [
        '{customer_id}' => '<customer_id:\d+>',
    ],
    'extraPatterns' => [
        'PUT' => 'update',
        'OPTIONS' => 'options',

        'POST history' => 'history',
        'OPTIONS history' => 'options',

        'POST test' => 'test',
        'OPTIONS test' => 'options',
    ],
];