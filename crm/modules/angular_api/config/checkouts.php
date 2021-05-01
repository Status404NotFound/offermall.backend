<?php

return [
    'class' => 'yii\rest\UrlRule',
    'controller' => 'angular_api/checkouts',
    'pluralize' => false,
    'tokens' => [
        '{id}' => '<id:\d+>',
    ],
    'extraPatterns' => [
        'OPTIONS {id}' => 'options',

        'POST wm-checkouts-list' => 'wm-checkouts-list',
        'OPTIONS wm-checkouts-list' => 'options',

        'POST wm-checkouts-history' => 'wm-checkouts-history',
        'OPTIONS wm-checkouts-history'  => 'options',

        'PUT wm-checkouts-confirm/{id}' => 'wm-checkouts-confirm',
        'OPTIONS wm-checkouts-confirm/{id}' => 'options',

        'PUT wm-checkouts-reject/{id}' => 'wm-checkouts-reject',
        'OPTIONS wm-checkouts-reject/{id}' => 'options'
    ],
];