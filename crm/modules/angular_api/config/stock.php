<?php

return [
    'class' => 'yii\rest\UrlRule',
    'controller' => 'angular_api/stock',
    'pluralize' => false,
    'tokens' => [
        '{stock_id}' => '<stock_id:\d+>',
        '{stock_sku}' => '<stock_sku:\w+>',
    ],
    'extraPatterns' => [
        'POST stocks' => 'stocks',
        'OPTIONS stocks' => 'options',

        'GET {stock_id}/stock_sku/{stock_sku}' => 'view',
        'OPTIONS {stock_id}/stock_sku/{stock_sku}' => 'options',

        'POST' => 'create',
        'OPTIONS' => 'options',

        'POST add-sku' => 'add-sku',
        'OPTIONS add-sku' => 'options',

        'POST move-sku' => 'move-sku',
        'OPTIONS move-sku' => 'options',

        'DELETE {stock_id}' => 'delete',
        'OPTIONS {stock_id}' => 'options',

        'POST geo-list' => 'geo-list',
        'OPTIONS geo-list' => 'options',

        'GET list' => 'list',
        'OPTIONS list' => 'options',
    ],
];