<?php

return [
    'class' => 'yii\rest\UrlRule',
    'controller' => 'wm_api/setting',
    'pluralize' => false,
    'tokens' => [
        '{id}' => '<id:\d+>',
    ],
    'extraPatterns' => [
        'GET public' => 'public',
        'OPTIONS public' => 'options',
    ]
];