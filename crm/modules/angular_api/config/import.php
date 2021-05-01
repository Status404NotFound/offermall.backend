<?php

return [
    'class' => 'yii\rest\UrlRule',
    'controller' => 'angular_api/import',
    'pluralize' => false,
    'extraPatterns' => [
        'OPTIONS fetcher' => 'options',
        'POST fetcher' => 'fetcher',

        'OPTIONS reports' => 'options',
        'GET reports' => 'reports',

        'OPTIONS report' => 'options',
        'POST report' => 'report',

        'OPTIONS sku-name-1c' => 'options',
        'POST sku-name-1c' => 'sku-name1c'
    ],
];