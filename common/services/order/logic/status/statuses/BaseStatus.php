<?php

namespace common\services\order\logic\status\statuses;

use Yii;
use common\models\order\Order;
use common\models\order\OrderStatus;
use common\models\order\StatusReason;
use common\services\order\logic\commission\CommissionFactory;
use common\services\order\logic\status\ChangeStatusException;
use common\services\order\logic\status\OrderStatusReasonNotFoundException;
use common\services\webmaster\postback\PostbackService;
use common\services\finance\FinanceCommonService;
use common\models\offer\targets\advert\TargetAdvertView;

class BaseStatus
{
    protected $status;
    /**
     * @var Order $order
     */
    protected $order;

    /**
     * @return bool
     */
    protected function needReason()
    {
        return (in_array($this->status, StatusReason::getStatusesId())) ? true : false;
    }

    /**
     * @param $reason
     * @throws OrderStatusReasonNotFoundException
     */
    protected function checkReason($reason)
    {
        if (!isset($reason) || !StatusReason::getReason($this->status, $reason))
            throw new OrderStatusReasonNotFoundException('Order Status Reason exists');
    }

    /**
     * @param $status
     * @param $target_status
     * @return bool
     */
    protected function targetAchived($status, $target_status)
    {
        return ($status >= $target_status) ? true : false;
    }

    /**
     * @param $mode
     * @return bool|PostbackService
     */
    protected function wmPostback($mode)
    {
        if (!is_null($this->order->flow_id)){
            return new PostbackService($this->order->order_id, $mode);
        }
        return false;
    }

    /**
     * @param Order $order
     * @return bool|null
     */
    protected function sendSecondSms(Order $order)
    {
        $result = null;

        if ($order->order_status == OrderStatus::WAITING_DELIVERY) {

            $target = $order->targetAdvert->targetAdvertGroup->advert_offer_target_id ?? null;
            $customer_phone = !is_null($order->customer->phone) ? $order->customer->phone : null;

            if (!is_null($customer_phone)) {
                $query = TargetAdvertView::find()->select(['second_sms_text_customer', 'send_second_sms_customer']);

                $template_customer = $query
                    ->where([
                        'advert_offer_target_id' => $target,
                        'send_second_sms_customer' => 1
                    ])->one();

                if (
                    !is_null($template_customer)
                    && Yii::$app->turbosms->balance !== 0
                ) {
                    $result = Yii::$app->turbosms->send($template_customer->second_sms_text_customer, (string)$customer_phone);
                }
            }
        }

        return $result;
    }

    /**
     * @throws ChangeStatusException
     * @throws \common\services\ValidateException
     * @throws \crm\services\finance\FinanceServiceExcepton
     */
    protected function checkTargets()
    {
        if ($this->order->target_advert_id !== null) {
            if ($this->targetAchived($this->status, $this->order->getAdvertTargetStatus())) {
                $orderCommission = CommissionFactory::create($this->order, 'advert');
                $this->order->advert_commission = $orderCommission->getCommission();
                $this->order->usd_advert_commission = $orderCommission->getUsdCommission();
                if (!$balance = (new FinanceCommonService())->changeBalance(
                    $this->order->targetAdvert->advert_id,
                    $this->order->advert_commission,
                    $this->order->targetAdvert->targetAdvertGroup->currency_id)
                ) throw new ChangeStatusException('Failed to change ' . $this->order->targetAdvert->advert->username . ' balance.');
            }
        }
        if ($this->order->target_wm_id !== null) {
            if ($this->targetAchived($this->status, $this->order->getWmTargetStatus())) {
                $orderCommission = CommissionFactory::create($this->order, 'wm');
                $this->order->wm_commission = $orderCommission->getCommission();
                $this->order->usd_wm_commission = $orderCommission->getUsdCommission();
            }
        }
    }
}