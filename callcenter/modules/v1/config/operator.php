<?php

return [
            'class'         => 'yii\rest\UrlRule',
            'controller'    => 'v1/operator',
            'pluralize'     => false,
            'tokens'        => [
                '{id}'             => '<id:\d+>',
            ],
            'extraPatterns' => [
                'OPTIONS {id}' =>  'options',

                'GET index' =>  'index',
                'OPTIONS index' =>  'options',

                'POST take-lead' =>  'take-lead',
                'OPTIONS take-lead' =>  'options',

                'GET generate-lead' =>  'generate-lead',
                'OPTIONS generate-lead' =>  'options',

                'POST detach-lead' =>  'detach-lead',
                'OPTIONS detach-lead' =>  'options',

                'OPTIONS plan-list' =>  'options',
                'GET plan-list' =>  'plan-list',

                'OPTIONS to-do-list' =>  'options',
                'GET to-do-list' =>  'to-do-list',

                'GET call-list' =>  'call-list',
                'OPTIONS call-list' =>  'options',

                'GET permission' => 'permission',
                'OPTIONS permission' => 'options',

                'POST change-status' => 'change-status',
                'OPTIONS change-status' => 'options',

                'POST time-check' => 'time-check',
                'OPTIONS time-check' => 'options',

                'GET set-fine' => 'set-fine',
                'OPTIONS set-fine' => 'options',

                'GET statuses' => 'statuses',
                'OPTIONS statuses' => 'options',

                'GET status' => 'status',
                'OPTIONS status' => 'options',
            ],
        ];