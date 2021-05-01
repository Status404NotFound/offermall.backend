<?php

return [
            'class'         => 'yii\rest\UrlRule',
            'controller'    => 'v1/delivery',
            'pluralize'     => false,
            'tokens'        => [
                '{id}'             => '<id:\d+>',
            ],
            'extraPatterns' => [
                'OPTIONS {id}'      =>  'options',
                'GET index'       =>  'index',
                'OPTIONS index'       =>  'options',
                'POST list'       =>  'list',
                'OPTIONS list'       =>  'options',
            ],
        ];