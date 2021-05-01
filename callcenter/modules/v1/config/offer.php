<?php

return [
            'class'         => 'yii\rest\UrlRule',
            'controller'    => 'v1/offer',
            'pluralize'     => false,
            'tokens'        => [
                '{offer_id}'             => '<offer_id:\d+>',
            ],
            'extraPatterns' => [
                'OPTIONS {offer_id}'      =>  'options',

                'GET index'       =>  'index',
                'OPTIONS index'       =>  'options',

                'GET list'       =>  'list',
                'OPTIONS list'       =>  'options',

                'GET country/list'       =>  'country-list',
                'OPTIONS country/list'       =>  'options',

                'GET geo/{offer_id}' => 'geo',
                'OPTIONS geo/{offer_id}' => 'options',
            ],
        ];