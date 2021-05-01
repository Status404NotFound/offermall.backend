<?php

return [
            'class'         => 'yii\rest\UrlRule',
            'controller'    => 'v1/sse',
            'pluralize'     => false,
            'tokens'        => [
                '{id}'             => '<id:\d+>',
            ],
            'extraPatterns' => [
                'OPTIONS {id}' =>  'options',

                'GET index' =>  'index',
                'OPTIONS index' =>  'options',

                'OPTIONS message' =>  'options',
                'GET message' =>  'message',

            ],
        ];