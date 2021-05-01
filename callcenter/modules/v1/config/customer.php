<?php

return [
            'class'         => 'yii\rest\UrlRule',
            'controller'    => 'v1/customer',
            'pluralize'     => false,
            'tokens'        => [
                '{customer_id}'             => '<customer_id:\d+>',
            ],
            'extraPatterns' => [
                'OPTIONS {customer_id}'      =>  'options',

                'POST save'       =>  'save',
                'OPTIONS save'       =>  'options',

                'POST save-address'       =>  'save-address',
                'OPTIONS save-address'       =>  'options',

                'GET get-cities/{customer_id}' => 'get-cities',
                'OPTIONS get-cities/{customer_id}' => 'options',
                
                'POST get-areas' => 'get-areas',
                'OPTIONS get-areas' => 'options',

                'POST history' => 'history',
                'OPTIONS history' => 'options',
            ],
        ];