<?php

return [
    'class' => 'yii\rest\UrlRule',
    'controller' => 'angular_api/notify',
    'pluralize' => false,
    'tokens' => [
        '{offer_id}' => '<offer_id:\d+>',
    ],
    'extraPatterns' => [
        'GET {offer_id}' => 'view',
        'OPTIONS {offer_id}' => 'options',

        'POST save' => 'save',
        'OPTIONS save' => 'options',
    ],
];