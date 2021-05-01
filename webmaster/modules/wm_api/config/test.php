<?php

return [
    'class' => 'yii\rest\UrlRule',
    'controller' => 'wm_api/test',
    'pluralize' => false,
    'tokens' => [
        '{id}' => '<id:\d+>',
    ],
    'extraPatterns' => [
        'OPTIONS {id}' => 'options',

        'POST generate-form' => 'generate-form',
        'OPTIONS generate-form' => 'options',
    ],
];