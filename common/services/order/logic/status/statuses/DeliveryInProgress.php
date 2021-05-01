<?php

namespace common\services\order\logic\status\statuses;

use common\models\order\Order;
use common\models\order\OrderData;
use common\models\order\OrderStatus;
use common\services\order\logic\status\ChangeStatusException;
use common\services\order\logic\status\ChangeStatusInterface;
use common\services\ValidateException;

class DeliveryInProgress extends BaseStatus implements ChangeStatusInterface
{
    private $declaration = null;

    /**
     * @param Order $order
     * @param array $params
     * @return bool
     * @throws ChangeStatusException
     */
    public function init(Order $order, $params = [])
    {
        $this->declaration = (isset($params['declaration'])) ? (string)$params['declaration'] : (string)$order->order_hash;
        $this->status = OrderStatus::DELIVERY_IN_PROGRESS;
        $this->order = $order;

        /** Vetaska said that commented checking below don't need */
//        if ($this->order->order_status !== OrderStatus::WAITING_DELIVERY)
//            throw new ChangeStatusException('Order can\'t change status to DELIVERY IN PROGRESS. Previous status is not WAITING FOR DELIVERY');

        $this->checkTargets();
        $this->changeStatus();
        $this->wmPostback('url_approved');
        return true;
    }

    /**
     * @throws ValidateException
     */
    private
    function changeStatus()
    {
        $this->order->order_status = $this->status;
        if (!$this->order->save())
            throw new ValidateException($this->order->errors);

        $this->order->orderData->declaration = $this->declaration;
        if (!$this->order->orderData->save())
            throw new ValidateException($this->order->orderData->errors);
    }
}