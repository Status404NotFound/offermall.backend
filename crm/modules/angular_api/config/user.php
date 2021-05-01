<?php

return [
    'class' => 'yii\rest\UrlRule',
    'controller' => 'angular_api/user',
    'pluralize' => false,
    'tokens' => [
        '{id}' => '<id:\d+>',
    ],
    'extraPatterns' => [
        'OPTIONS {id}' => 'options',

        'GET {id}' => 'view',
        'OPTIONS view' => 'options',

        'GET avatar' => 'avatar',
        'OPTIONS avatar' => 'options',

        'POST login' => 'login',
        'OPTIONS login' => 'options',

        'POST create' => 'create',
        'OPTIONS create' => 'options',

        'PUT update' => 'update',
        'OPTIONS update' => 'options',

        'POST permissions' => 'permissions',
        'OPTIONS permissions' => 'options',

        'GET roles' => 'roles',
        'OPTIONS roles' => 'options',

        'POST update-permissions' => 'update-permissions',
        'OPTIONS update-permissions' => 'options',

        'DELETE delete/{id}' => 'delete',
        'OPTIONS delete/{id}' => 'options',

        'POST block' => 'block',
        'OPTIONS block' => 'options',

        'GET profile' => 'profile',
        'OPTIONS profile' => 'options',

        'POST profile-save' => 'profile-save',
        'OPTIONS profile-save' => 'options',

        'POST signup' => 'signup',
        'OPTIONS signup' => 'options',

        'POST confirm' => 'confirm',
        'OPTIONS confirm' => 'options',

        'POST password-reset-request' => 'password-reset-request',
        'OPTIONS password-reset-request' => 'options',

        'POST password-reset-token-verification' => 'password-reset-token-verification',
        'OPTIONS password-reset-token-verification' => 'options',

        'POST password-reset' => 'password-reset',
        'OPTIONS password-reset' => 'options',

        'GET advert-list' => 'advert-list',
        'OPTIONS advert-list' => 'options',

        'GET wm-list' => 'wm-list',
        'OPTIONS wm-list' => 'options',

        'GET permission' => 'permission',
        'OPTIONS permission' => 'options',

        'POST list' => 'list',
        'OPTIONS list' => 'options',

        'GET test' => 'test',
        'OPTIONS test' => 'options',

        'GET settings' => 'settings',
        'OPTIONS settings' => 'options',
    ]
];