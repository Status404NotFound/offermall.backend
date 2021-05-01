<?php

namespace common\services\order\logic\status;

use common\models\order\OrderStatus;
use common\services\order\logic\status\statuses\BackToPending;
use common\services\order\logic\status\statuses\Canceled;
use common\services\order\logic\status\statuses\DeliveryInProgress;
use common\services\order\logic\status\statuses\MissedDelivery;
use common\services\order\logic\status\statuses\NotPaid;
use common\services\order\logic\status\statuses\NotValid;
use common\services\order\logic\status\statuses\NotValidChecked;
use common\services\order\logic\status\statuses\Pending;
use common\services\order\logic\status\statuses\Rejected;
use common\services\order\logic\status\statuses\Returned;
use common\services\order\logic\status\statuses\SuccessDelivery;
use common\services\order\logic\status\statuses\WaitingDelivery;

/**
 * Class ChangeStatusFactory
 * @package common\services\order\logic\status
 */
class ChangeStatusFactory
{
    /**
     * @var array
     */
    private static $changeStatusMap = [
        OrderStatus::PENDING => Pending::class,
        OrderStatus::BACK_TO_PENDING => BackToPending::class,
        OrderStatus::WAITING_DELIVERY => WaitingDelivery::class,
        OrderStatus::DELIVERY_IN_PROGRESS => DeliveryInProgress::class,
        OrderStatus::SUCCESS_DELIVERY => SuccessDelivery::class,
        OrderStatus::REJECTED => Rejected::class,
        OrderStatus::CANCELED => Canceled::class,
        OrderStatus::NOT_VALID_CHECKED => NotValidChecked::class,
        OrderStatus::NOT_VALID => NotValid::class,
        OrderStatus::NOT_PAID => NotPaid::class,
        OrderStatus::RETURNED => Returned::class,
    ];


    /**
     * @param $order_status
     * @return mixed
     */
    public static function create($order_status)
    {
        $class = self::$changeStatusMap[$order_status];
        if (!$class) throw new \InvalidArgumentException("OrderStatus $order_status not found.");
        return new $class();
    }
}