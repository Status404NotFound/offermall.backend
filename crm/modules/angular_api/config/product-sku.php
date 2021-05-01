<?php

return [
    'class' => 'yii\rest\UrlRule',
    'controller' => 'angular_api/product-sku',
    'pluralize' => false,
    'tokens' => [
        '{id}' => '<id:\d+>',
        '{product_id}' => '<product_id:\d+>',
        '{sku_id}' => '<sku_id:\d+>',
    ],
    'extraPatterns' => [
        'OPTIONS {sku_id}' => 'options',
        'OPTIONS index' => 'options',
        'OPTIONS sku-product-list' => 'options',

        'GET index' => 'index',
        'GET sku-product-list' => 'index',

        'GET sku-list' => 'sku-list',
        'OPTIONS sku-list' => 'options',

        'POST create' => 'save',
        'OPTIONS create' => 'options',
    ],
];