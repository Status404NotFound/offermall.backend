<?php
return [
    'class' => 'yii\rest\UrlRule',
    'controller' => 'wm_api/form',
    'pluralize' => false,
    'tokens' => [
        '{id}' => '<id:\d+>',
    ],
    'extraPatterns' => [
        'OPTIONS {id}' => 'options',

        'POST generate-form' => 'generate-form',
        'OPTIONS generate-form' => 'options',

        'GET list' => 'list',
        'OPTIONS list' => 'options',

        'POST delete-form' => 'delete-form',
        'OPTIONS delete-form' => 'options'
    ],
];