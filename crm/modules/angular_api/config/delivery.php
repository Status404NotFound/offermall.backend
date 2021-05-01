<?php

return [
    'class' => 'yii\rest\UrlRule',
    // AdvertTargetController
    'controller' => 'angular_api/delivery',
    'pluralize' => false,
    'tokens' => [
        '{order_id}' => '<order_id:\d+>',
        '{delivery_date_id}' => '<delivery_date_id:\d+>',
        '{requisite_id}' => '<requisite_id:\d+>',
        '{offer_id}' => '<offer_id:\d+>',
    ],
    'extraPatterns' => [
        'POST' => 'send',
        'OPTIONS' => 'options',

        'GET delivery-countries' => 'delivery-countries',
        'OPTIONS delivery-countries' => 'options',

        'GET delivery-buttons' => 'delivery-buttons',
        'OPTIONS delivery-buttons' => 'options',

        'GET company-info' => 'company-info',
        'OPTIONS company-info' => 'options',

        'GET company-info/{requisite_id}' => 'company-info',
        'OPTIONS company-info/{requisite_id}' => 'options',

        'GET block-sku/{offer_id}' => 'block-sku',
        'OPTIONS block-sku/{offer_id}' => 'options',

        'POST company-info/save' => 'save-company-info',
        'OPTIONS company-info/save' => 'options',

        'DELETE company-info/delete/{requisite_id}' => 'delete-company-info',
        'OPTIONS company-info/delete/{requisite_id}' => 'options',

        'POST delivery-date' => 'delivery-date',
        'OPTIONS delivery-date' => 'options',

        'POST count-success-delivery' => 'count-success-delivery',
        'OPTIONS count-success-delivery' => 'options',

        'GET partner-delivery-date/view/{delivery_date_id}' => 'partner-delivery-date-view',
        'OPTIONS partner-delivery-date/view/{delivery_date_id}' => 'options',

        'POST partner-delivery-date' => 'partner-delivery-date',
        'OPTIONS partner-delivery-date' => 'options',

        'POST partner-delivery-date/save' => 'partner-delivery-date-save',
        'OPTIONS partner-delivery-date/save' => 'options',

        'POST partner-delivery-date/delete' => 'partner-delivery-date-delete',
        'OPTIONS partner-delivery-date/delete' => 'options',

        'POST stickers/add' => 'add-stickers',
        'OPTIONS stickers/add' => 'options',

        'POST stickers/save' => 'save-stickers',
        'OPTIONS stickers/save' => 'options',

        'POST stickers/delete' => 'delete-stickers',
        'OPTIONS stickers/delete' => 'options',

        'DELETE partner-delivery-date/delete/past-days' => 'partner-delivery-date-delete-past-days',
        'OPTIONS partner-delivery-date/delete/past-days' => 'options',
    ],
];