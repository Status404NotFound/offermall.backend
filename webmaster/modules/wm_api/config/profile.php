<?php

return [
    'class' => 'yii\rest\UrlRule',
    'controller' => 'wm_api/profile',
    'pluralize' => false,
    'tokens' => [
        '{id}' => '<id:\d+>',
    ],
    'extraPatterns' => [
        'OPTIONS {id}' => 'options',

        'GET data' => 'data',
        'OPTIONS data' => 'options',

        'PUT update' => 'update',
        'OPTIONS update' => 'options',

        'POST upload-avatar' => 'upload-avatar',
        'OPTIONS upload-avatar' => 'options',

        'POST change-password' => 'change-password',
        'OPTIONS change-password' => 'options'
    ],
];