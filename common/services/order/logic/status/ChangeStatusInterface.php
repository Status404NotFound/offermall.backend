<?php

namespace common\services\order\logic\status;

use common\models\order\Order;

/**
 * Interface ChangeStatusInterface
 * @package common\services\order\logic\status\statuses
 */
interface ChangeStatusInterface
{
    /**
     * @param Order $order
     * @param array $params
     * @return mixed
     */
    public function init(Order $order, $params = []);
}