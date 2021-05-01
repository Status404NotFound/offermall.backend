<?php

return [
    'class' => 'yii\rest\UrlRule',
    'controller' => 'angular_api/contact',
    'pluralize' => false,
    'tokens' => [
        '{phone}' => '<phone:\d+>',
    ],
    'extraPatterns' => [

        'POST search' => 'search',
        'OPTIONS search' => 'options',
    ],
];