<?php

return [
    'class' => 'yii\rest\UrlRule',
    'controller' => 'wm_api/flow',
    'pluralize' => false,
    'tokens' => [
        '{id}' => '<id:\d+>',
        '{flow_id}' => '<flow_id:\d+>',
        '{offer_id}' => '<offer_id:\d+>',
    ],
    'extraPatterns' => [
        'OPTIONS {flow_id}' => 'options',
        'GET {flow_id}' => 'view',
        'OPTIONS view' => 'options',

        'POST flow-list' => 'flow-list',
        'OPTIONS flow-list' => 'options',

        'GET create-offers' => 'create-offers',
        'OPTIONS create-offers' => 'options',

        'GET landings-list' => 'landings-list',
        'OPTIONS landings-list' => 'options',

        'GET offer-landings/{offer_id}' => 'offer-landings',
        'OPTIONS offer-landings/{offer_id}' => 'options',

        'GET offer-transits/{offer_id}' => 'offer-transits',
        'OPTIONS offer-transits/{offer_id}' => 'options',

        'POST target' => 'target',
        'OPTIONS target' => 'options',

        'GET flow-url/{flow_id}' => 'flow-url',
        'OPTIONS flow-url/{flow_id}' => 'options',

        'OPTIONS edit/{flow_id}' => 'options',
        'PUT edit/{flow_id}' => 'edit',

        'OPTIONS delete/{flow_id}' => 'options',
        'DELETE delete/{flow_id}' => 'delete',

        'POST create' => 'create',
        'OPTIONS create' => 'options',
    ],
];