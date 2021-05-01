<?php

return [
    'class' => 'yii\rest\UrlRule',
    'controller' => 'api/partners',
    'pluralize' => false,
    'tokens' => [
        '{id}' => '<id:\d+>',
    ],
    'extraPatterns' => [
        'OPTIONS {id}' => 'options',

        'GET orders' => 'orders',
        'OPTIONS orders' => 'options',

        'GET offers' => 'offers',
        'OPTIONS offers' => 'options',

        'GET save-comment' => 'save-comment',
        'OPTIONS save-comment' => 'options',

        'POST save-slug' => 'save-slug',
        'OPTIONS save-slug' => 'options',

        'POST send' => 'send',
        'OPTIONS send' => 'options',
    ],
];