<?php

return [
    'class' => 'yii\rest\UrlRule',
    'controller' => 'angular_api/partner-money',
    'pluralize' => false,
    'tokens' => [
        '{advert_id}' => '<advert_id:\d+>',
    ],
    'extraPatterns' => [
        'POST change-balance' => 'change-balance',
        'OPTIONS change-balance' => 'options',

        'GET partner-balance {advert_id}' => 'partner-balance',
        'OPTIONS partner-balance {advert_id}' => 'options',
    ],
];