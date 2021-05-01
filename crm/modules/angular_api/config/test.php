<?php

return [
    'class' => 'yii\rest\UrlRule',
    'controller' => 'angular_api/test',
    'pluralize' => false,
    'tokens' => [
        '{phone}' => '<phone:\d+>',
    ],
    'extraPatterns' => [

        'GET test' => 'test',
        'OPTIONS test' => 'options',
    ],
];