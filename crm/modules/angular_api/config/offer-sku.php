<?php

return [
    'class' => 'yii\rest\UrlRule',
    'controller' => 'angular_api/offer-sku',
    'pluralize' => false,
    'tokens' => [
        '{offer_id}' => '<offer_id:\d+>',
    ],
    'extraPatterns' => [
        'OPTIONS' => 'options',
        'OPTIONS {offer_id}' => 'options',

        'GET {offer_id}' => 'view',
        'POST' => 'create',
    ],
];