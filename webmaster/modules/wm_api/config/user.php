<?php

return [
    'class' => 'yii\rest\UrlRule',
    'controller' => 'wm_api/user',
    'pluralize' => false,
    'tokens' => [
        '{id}' => '<id:\d+>',
    ],
    'extraPatterns' => [
        'OPTIONS {id}' => 'options',

        'POST login' => 'login',
        'OPTIONS login' => 'options',

        'POST signup' => 'signup',
        'OPTIONS signup' => 'options',

        'POST confirm' => 'confirm',
        'OPTIONS confirm' => 'options',

        'GET permission' => 'permission',
        'OPTIONS permission' => 'options',

        'GET settings' => 'settings',
        'OPTIONS settings' => 'options',

        'POST password-reset-request' => 'password-reset-request',
        'OPTIONS password-reset-request' => 'options',

        'POST password-reset-token-verification' => 'password-reset-token-verification',
        'OPTIONS password-reset-token-verification' => 'options',

        'POST password-reset' => 'password-reset',
        'OPTIONS password-reset' => 'options',

        'POST reg-new' => 'reg-new',
        'OPTIONS reg-new' => 'options',
    ]
];