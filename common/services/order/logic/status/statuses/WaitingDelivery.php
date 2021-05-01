<?php

namespace common\services\order\logic\status\statuses;

use common\helpers\FishHelper;
use common\models\customer\Customer;
use common\models\order\Order;
use common\models\order\OrderStatus;
use common\services\order\logic\status\ChangeStatusException;
use common\services\order\logic\status\ChangeStatusInterface;
use common\services\ValidateException;

class WaitingDelivery extends BaseStatus implements ChangeStatusInterface
{
    /**
     * @var Customer $customer
     */
    private $customer;
    private $delivery_date;
    private $customer_address;

    /**
     * @param Order $order
     * @param array $params
     * @return bool
     * @throws ChangeStatusException
     */
    public function init(Order $order, $params = [])
    {
        if (empty($params) || empty($params['delivery_date']))
            throw new ChangeStatusException('Delivery date is not set or empty params.');
        if (isset($params['page']) && $params['page'] == 'group') {
            $setAddress = false;
        } elseif (isset($params['address']) && !empty($params['address'])) {
            $setAddress = true;
            $this->customer_address = (string)$params['address'];
        } else {
            throw new ChangeStatusException('Address is not set.');
        }

        $this->status = OrderStatus::WAITING_DELIVERY;
        $this->order = $order;
        $this->customer = $order->customer;
        $this->delivery_date = (string)$params['delivery_date'];
        if (!$this->customer instanceof Customer || !$this->delivery_date)
            throw new ChangeStatusException('Missed params for saving Order Status ' . __CLASS__);

        $this->checkTargets();
        /** If $page === group - there is NO address in params array and must not be set. */
        $this->changeStatus($setAddress);
        $this->wmPostback('url_approved');

        try {
            $this->sendSecondSms($order);
        }
        catch (Exception $e) {
            /*
             * TODO: If SMS is not sent - write to the log.
             */
        }

        return true;
    }

    /**
     * @param bool $setAddress
     * @throws ValidateException
     */
    private function changeStatus($setAddress = true)
    {
        if ($setAddress === true) {
            $this->customer->address = $this->customer_address;
            if (!$this->customer->save())
                throw new ValidateException($this->customer->errors);
        }
        $this->order->delivery_date = $this->delivery_date;
        $this->order->order_status = $this->status;
        if (!$this->order->save())
            throw new ValidateException($this->order->errors);
    }
}