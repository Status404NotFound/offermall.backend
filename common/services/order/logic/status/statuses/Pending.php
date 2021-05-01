<?php

namespace common\services\order\logic\status\statuses;

use common\models\order\Order;
use common\models\order\OrderStatus;
use common\services\order\logic\status\ChangeStatusInterface;
use common\services\ValidateException;

class Pending extends BaseStatus implements ChangeStatusInterface
{
    public function init(Order $order, $params = [])
    {
        $this->status = OrderStatus::PENDING;
        $this->order = $order;

        $this->checkTargets();
        $this->changeStatus();
        $this->wmPostback('url_approved');
        return true;
    }

    private function changeStatus()
    {
        $this->order->order_status = $this->status;
        if (!$this->order->save()) throw new ValidateException($this->order->errors);
    }
}