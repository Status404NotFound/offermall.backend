<?php

return [
            'class'         => 'yii\rest\UrlRule',
            'controller'    => 'v1/order',
            'pluralize'     => false,
            'tokens'        => [
                '{order_id}'             => '<order_id:\d+>',
                '{offer_id}'             => '<offer_id:\d+>',
            ],
            'extraPatterns' => [
                'OPTIONS {order_id}' =>  'options',

                'GET index' =>  'index',
                'OPTIONS index' =>  'options',

                'POST create' => 'create',
                'OPTIONS create' => 'options',

                'POST card' =>  'card',
                'OPTIONS card' =>  'options',

                'OPTIONS sse' =>  'options',
                'POST sse' =>  'sse',

                'OPTIONS reject' =>  'options',
                'POST reject' =>  'reject',

                'POST plan-call' =>  'plan-call',
                'OPTIONS plan-call' =>  'options',

                'POST change-order-address' =>  'change-order-address',
                'OPTIONS change-order-address' =>  'options',

                'GET reject-reasons' => 'reject-reasons',
                'OPTIONS reject-reasons' => 'options',

                'GET languages' => 'languages',
                'OPTIONS languages' => 'options',

                'GET status' => 'status',
                'OPTIONS status' => 'options',

                'POST change-language' =>  'change-language',
                'OPTIONS change-language' =>  'options',

                'POST comment/add' =>'add-comment',
                'OPTIONS comment/add' => 'options',

                'GET comment/{order_id}' => 'comment',
                'OPTIONS comment/{order_id}' => 'options',

                'POST sku/save' => 'sku-save',
                'OPTIONS sku/save' => 'options',

                'GET sku-rules/{order_id}' => 'sku-rules',
                'OPTIONS sku-rules/{order_id}' => 'options',

                'GET call/{order_id}' => 'call',
                'OPTIONS call/{order_id}' => 'options',

                'POST plan-delivery' => 'plan-delivery',
                'OPTIONS plan-delivery' => 'options',

                'POST history' => 'history',
                'OPTIONS history' => 'options',

                'POST history-lead-calls' => 'history-lead-calls',
                'OPTIONS history-lead-calls' => 'options',

                'GET detach/{order_id}' => 'detach',
                'OPTIONS detach/{order_id}' => 'options',

                'POST call/record' => 'call-record',
                'OPTIONS call/record' => 'options',

                'POST not-valid' => 'not-valid',
                'OPTIONS not-valid' => 'options',

                'GET not-valid-reasons' => 'not-valid-reasons',
                'OPTIONS not-valid-reasons' => 'options',

                'POST offer/notes' => 'offer-notes',
                'OPTIONS offer/notes' => 'options',

                'POST delivery-date' => 'delivery-date',
                'OPTIONS delivery-date' => 'options',

                'GET auto-mode-card' => 'auto-mode-card',
                'OPTIONS auto-mode-card' => 'options',
            ],
        ];