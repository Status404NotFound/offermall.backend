<?php

namespace common\services\order\logic\status\statuses;

use common\models\order\Order;
use common\models\order\OrderStatus;
use common\services\order\logic\status\ChangeStatusException;
use common\services\order\logic\status\ChangeStatusInterface;
use common\services\ValidateException;

class Canceled extends BaseStatus implements ChangeStatusInterface
{
    private $reason = null;

    public function init(Order $order, $params = [])
    {
        $this->status = OrderStatus::CANCELED;
        $this->reason = (isset($params['reason_id'])) ? $params['reason_id'] : 0;
        $this->order = $order;
        if ($this->reason === null) throw new ChangeStatusException('Missed params for saving Order Status ' . __CLASS__);

        $this->checkReason($this->reason);
        $this->checkTargets();
        $this->changeStatus();
        $this->wmPostback('url_cancelled');
        return true;
    }

    private function changeStatus()
    {
        $this->order->order_status = $this->status;
        $this->order->status_reason = $this->reason;
        if (!$this->order->save()) throw new ValidateException($this->order->errors);
    }
}
