<?php

return [
    'class' => 'yii\rest\UrlRule',
    'controller' => 'angular_api/callcenter',
    'pluralize' => false,
    'tokens' => [
        '{operator_id}' => '<operator_id:\d+>',
        '{order_id}' => '<order_id:\d+>',
        '{activity_id}' => '<activity_id:\d+>',
        '{queue_id}' => '<queue_id:\d+>',
    ],
    'extraPatterns' => [
        'OPTIONS' => 'options',

        'GET settings/update/{operator_id}' => 'change',
        'OPTIONS settings/update/{operator_id}' => 'options',

        'GET order/{order_id}' => 'order',
        'OPTIONS order/{order_id}' => 'options',

        'POST settings/save' => 'save',
        'OPTIONS settings/save' => 'options',

        'GET offers' => 'get-offers',
        'OPTIONS offers' => 'options',

        'GET languages' => 'get-languages',
        'OPTIONS languages' => 'options',

        'GET settings' => 'settings',
        'OPTIONS settings' => 'options',

        'POST call-list' => 'call-list',
        'OPTIONS call-list' => 'options',

        'POST call-list/settings' => 'call-list-settings',
        'OPTIONS call-list/settings' => 'options',

        'POST call-list/high-priority/change' => 'call-list-high-priority-change',
        'OPTIONS call-list/high-priority/change' => 'options',

        'GET fines' => 'fines',
        'OPTIONS fines' => 'options',

        'POST fine/status' => 'fine-status',
        'OPTIONS fine/status' => 'options',

        'POST pieces' => 'pieces',
        'OPTIONS pieces' => 'options',

        'POST history' => 'history',
        'OPTIONS history' => 'options',

        'POST activity' => 'activity',
        'OPTIONS activity' => 'options',

        'GET activity/approve/{activity_id}' => 'activity-approve',
        'OPTIONS activity/approve/{activity_id}' => 'options',

        'GET activity/reject/{activity_id}' => 'activity-reject',
        'OPTIONS activity/reject/{activity_id}' => 'options',

        'POST statistics' => 'statistics',
        'OPTIONS statistics' => 'options',

        'GET operators' => 'operators',
        'OPTIONS operators' => 'options',

        'GET lead-status' => 'lead-status',
        'OPTIONS lead-status' => 'options',

        'GET lead-state' => 'lead-state',
        'OPTIONS lead-state' => 'options',

        'POST history/record' => 'call-record',
        'OPTIONS history/record' => 'options',

        'POST history/record-save' => 'record-save',
        'OPTIONS history/record-save' => 'options',

        'POST pending' => 'pending',
        'OPTIONS pending' => 'options',

        'GET script' => 'script-notes',
        'OPTIONS script' => 'options',

        'POST script/save' => 'script-save',
        'OPTIONS script/save' => 'options',

        'GET statistics/rating-today' => 'statistics-rating-today',
        'OPTIONS statistics/rating-today' => 'options',

        'POST queue/list' => 'queue-list',
        'OPTIONS queue/list' => 'options',

        'GET queue/update/{queue_id}' => 'queue-update',
        'OPTIONS queue/update/{queue_id}' => 'options',
        'POST queue/update/{queue_id}' => 'queue-update',

        'POST queue/create' => 'queue-create',
        'OPTIONS queue/create' => 'options',
    ],
];