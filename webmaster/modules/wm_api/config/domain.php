<?php
return [
    'class' => 'yii\rest\UrlRule',
    'controller' => 'wm_api/domain',
    'pluralize' => false,
    'tokens' => [
        '{id}' => '<id:\d+>',
    ],
    'extraPatterns' => [
        'OPTIONS {id}' => 'options',

        'POST parking' => 'parking',
        'OPTIONS parking' => 'options',

        'GET list' => 'list',
        'OPTIONS list' => 'options',

        'GET exist-domains' => 'exist-domains',
        'OPTIONS exist-domains' => 'options'
    ],
];