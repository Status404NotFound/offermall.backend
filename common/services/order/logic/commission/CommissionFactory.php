<?php

namespace common\services\order\logic\commission;

use common\models\order\Order;
use common\services\order\logic\commission\advert\OrderAdvertCommission;
use common\services\order\logic\commission\delivery\OrderDeliveryCommission;
use common\services\order\logic\commission\wm\OrderWmCommission;

/**
 * Class CommissionFactory
 * @package common\services\order\logic\commission
 */
class CommissionFactory
{
    /**
     * @var array
     */
    private static $commissionMap = [
        'advert' => OrderAdvertCommission::class,
        'delivery' => OrderDeliveryCommission::class,
        'wm' => OrderWmCommission::class,
    ];

    /**
     * @param Order $order
     * @param $type
     * @return OrderCommissionInterface
     */
    public static function create(Order $order, $type)
    {
        $class = self::$commissionMap[$type];
        if (!$class)
            throw new \InvalidArgumentException("Commission type $type not found.");
        return new $class($order);
    }
}