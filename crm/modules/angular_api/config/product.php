<?php

return [
    'class' => 'yii\rest\UrlRule',
    'controller' => 'angular_api/product',
    'pluralize' => false,
    'tokens' => [
        '{product_id}' => '<product_id:\d+>',
    ],
    'extraPatterns' => [
        'GET index' => 'index',
        'OPTIONS index' => 'options',

        'OPTIONS' => 'options',
        'POST' => 'create',

        'POST products' => 'products',
        'OPTIONS products' => 'options',

        'POST product-list' => 'product-list',
        'OPTIONS product-list' => 'options',

        'GET {product_id}' => 'view',
        'OPTIONS {product_id}' => 'options',

        'PUT {product_id}' => 'update',
    ],
];