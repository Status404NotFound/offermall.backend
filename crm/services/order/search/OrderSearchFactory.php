<?php

namespace crm\services\order\search;

use crm\services\order\search\logic\Delivery;
use crm\services\order\search\logic\DIP;
use crm\services\order\search\logic\Group;
use crm\services\order\search\logic\Orders;
use crm\services\order\search\logic\WFD;

class OrderSearchFactory
{
    private static $_pages = [
        'delivery' => Delivery::class,
        'orders'   => Orders::class,
        'group'    => Group::class,
        'wfd'      => WFD::class,
        'dip'      => DIP::class
    ];

    public static function createSearch(string $page)
    {
        $class = self::$_pages[$page];
        if ( !$class) {
            throw new \InvalidArgumentException("Page type $page not found.");
        }
    
        return new $class;
    }
}