<?php

return [
    'class' => 'yii\rest\UrlRule',
    'controller' => 'wm_api/instruments',
    'pluralize' => false,
    'tokens' => [
        '{id}' => '<id:\d+>',
        '{flow_id}' => '<flow_id:\d+>',
        '{domain_id}' => '<domain_id:\d+>',
        '{postback_individual_id}' => '<postback_individual_id:\d+>',
    ],
    'extraPatterns' => [
        'OPTIONS {id}' => 'options',

        'GET parking-list' => 'parking-list',
        'OPTIONS parking-list' => 'options',

        'OPTIONS parking-add' => 'options',
        'POST parking-add' => 'parking-add',

        'GET parking-url/{domain_id}' => 'parking-url',
        'OPTIONS parking-url/{domain_id}' => 'options',

        'OPTIONS parking-update/{domain_id}' => 'options',
        'PUT parking-update/{domain_id}' => 'parking-update',

        'GET parking-view/{domain_id}' => 'parking-view',
        'OPTIONS parking-view/{domain_id}' => 'options',

        'OPTIONS parking-delete/{domain_id}' => 'options',
        'DELETE parking-delete/{domain_id}' => 'parking-delete',

        'POST postback/list' => 'individual-postback-list',
        'OPTIONS postback/list' => 'options',

        'GET postback/global' => 'global-postback',
        'OPTIONS postback/global' => 'options',

        'GET postback/individual/{flow_id}' => 'individual-postback',
        'OPTIONS postback/individual/{flow_id}' => 'options',

        'PUT postback/global/save' => 'global-postback-save',
        'OPTIONS postback/global/save' => 'options',

        'PUT postback/individual/save' => 'individual-postback-save',
        'OPTIONS postback/individual/save' => 'options',

        'DELETE postback/individual/delete/{postback_individual_id}' => 'individual-postback-delete',
        'OPTIONS postback/individual/delete/{postback_individual_id}' => 'options',
    ],
];