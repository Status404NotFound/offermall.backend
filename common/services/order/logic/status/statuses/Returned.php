<?php

namespace common\services\order\logic\status\statuses;

use common\models\order\Order;
use common\models\order\OrderStatus;
use common\services\order\logic\status\ChangeStatusInterface;
use common\services\ValidateException;

class Returned extends BaseStatus implements ChangeStatusInterface
{
    public function init(Order $order, $params = [])
    {
        $this->status = OrderStatus::RETURNED;
        $this->order = $order;

        $this->checkTargets();
        $this->changeStatus();
        $this->wmPostback('url_cancelled');
        return true;
    }

    private function changeStatus()
    {
        $this->order->order_status = $this->status;
        if (!$this->order->save()) throw new ValidateException($this->order->errors);
    }
}