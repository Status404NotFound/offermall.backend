<?php
return [
    'class' => 'yii\rest\UrlRule',
    'controller' => 'angular_api/delivery-sticker',
    'pluralize' => false,
    'tokens' => [
        '{sticker_id}' => '<sticker_id:\d+>',
    ],
    'extraPatterns' => [
        'OPTIONS {sticker_id}' => 'options',

        'GET list' => 'list',
        'OPTIONS list' => 'options',

        'POST create' => 'create',
        'OPTIONS create' => 'options',

        'POST change-status' => 'change-status',
        'OPTIONS change-status' => 'options',

        'PUT update' => 'update',
        'OPTIONS update' => 'options',

        'DELETE delete/{sticker_id}' => 'delete',
        'OPTIONS delete/{sticker_id}' => 'options',
    ],
];