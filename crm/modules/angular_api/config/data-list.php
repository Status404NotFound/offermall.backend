<?php

return [
    'class' => 'yii\rest\UrlRule',
    // AdvertTargetController
    'controller' => 'angular_api/data-list',
    'pluralize' => false,
    'tokens' => [
//        '{order_id}' => '<order_id:\d+>',
        '{geo_id}' => '<geo_id:\d+>',
        '{offer_id}' => '<offer_id:\d+>',
    ],
    'extraPatterns' => [
        'GET geo' => 'geo',
        'OPTIONS geo' => 'options',

        'GET countries' => 'countries',
        'OPTIONS countries' => 'options',

        'GET geo-cities' => 'geo-cities',
        'OPTIONS geo-cities' => 'options',

        'GET geo-iso' => 'geo-iso',
        'OPTIONS geo-iso' => 'options',

        'GET status' => 'status',
        'OPTIONS status' => 'options',

        'GET partners' => 'adverts',
        'OPTIONS partners' => 'options',

        'GET role' => 'role',
        'OPTIONS role' => 'options',

        'GET web-partners' => 'webmasters',
        'OPTIONS web-partners' => 'options',

        'GET notification-audio' => 'notification-audio',
        'OPTIONS notification-audio' => 'options',

        'GET offers/geo' => 'offers-geo',
        'OPTIONS offers/geo' => 'options',

        'GET geo/offers/{geo_id}' => 'geo-offers',
        'OPTIONS geo/offers/{geo_id}' => 'options',

        'GET offers' => 'offers',
        'OPTIONS offers' => 'options',

        'GET flows' => 'flows',
        'OPTIONS flows' => 'options',

        'GET currency' => 'currency',
        'OPTIONS currency' => 'options',

        'GET time-zone' => 'time-zone',
        'OPTIONS time-zone' => 'options',

        'GET parent' => 'parent',
        'OPTIONS parent' => 'options',

        'GET status-reasons' => 'status-reasons',
        'OPTIONS status-reasons' => 'options',

        'GET checkout-status' => 'checkout-status',
        'OPTIONS checkout-status' => 'options',

        'GET geo-regions/{geo_id}' => 'geo-regions',
        'OPTIONS geo-regions/{geo_id}' => 'options',

        'POST available-geo' => 'available-geo',
        'OPTIONS available-geo' => 'options',

        'GET order-stickers' => 'order-stickers',
        'OPTIONS order-stickers' => 'options',

        'GET delivery/api-list' => 'delivery-api-list',
        'OPTIONS delivery/api-list' => 'options',
    ],
];