<?php

return [
    'class' => 'yii\rest\UrlRule',
    'controller' => 'api/api-script',
    'pluralize' => false,
    'tokens' => [
        '{id}' => '<id:\d+>',
    ],
    'extraPatterns' => [
        'OPTIONS {id}' => 'options',

        'POST new' => 'new',
        'OPTIONS new' => 'options',

        'GET offer-info' => 'offer-info',
        'OPTIONS offer-info' => 'options',
    ],
];