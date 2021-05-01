<?php

return [
    'class' => 'yii\rest\UrlRule',
    'controller' => 'angular_api/requests',
    'pluralize' => false,
    'tokens' => [
        '{wm_offer_id}' => '<wm_offer_id:\d+>',
    ],
    'extraPatterns' => [
        'OPTIONS {wm_offer_id}' => 'options',

        'POST requests-list' => 'requests-list',
        'OPTIONS requests-list' => 'options',

        'GET info/{wm_offer_id}' => 'info',
        'OPTIONS info/{wm_offer_id}' => 'options',

        'PUT confirm' => 'confirm',
        'OPTIONS confirm' => 'options',

        'PUT reject' => 'reject',
        'OPTIONS reject' => 'options'
    ],
];