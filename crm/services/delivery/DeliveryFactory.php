<?php

namespace crm\services\delivery;

use crm\services\delivery\courierplus\CourierPlus;
use crm\services\delivery\fetchr\Dropship;
use crm\services\delivery\fetchr\Fulfillment;
use crm\services\delivery\mara\ShipaDelivery;
use crm\services\delivery\mara\MaraExpress;

class DeliveryFactory
{
    private static $deliveries = [
        'dropship'      => Dropship::class,
        'fulfillment'   => Fulfillment::class,
        'maraexpress'   => MaraExpress::class,
        'courierplus'   => CourierPlus::class,
        'shipadelivery' => ShipaDelivery::class
    ];

    public static function createDelivery($type)
    {
        $class = self::$deliveries[$type];
        if ( !$class) {
            throw new \InvalidArgumentException("Delivery type $type not found.");
        }
        return new $class;
    }
}

