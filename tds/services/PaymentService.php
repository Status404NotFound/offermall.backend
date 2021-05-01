<?php

namespace tds\services;

use common\helpers\FishHelper;
use common\models\finance\Currency;
use common\models\log\orderInfo\OrderInfoInstrument;
use common\models\log\orderSku\OrderSkuInstrument;
use common\models\offer\targets\advert\sku\TargetAdvertSku;
use common\models\offer\targets\advert\sku\TargetAdvertSkuRules;
use common\models\onlinePayment\OnlinePayment;
use common\models\order\Order;
use common\services\order\OrderCommonService;
use common\services\ValidateException;

class PaymentService
{
    public function savePayment(Order $order, $request)
    {
        if ($payment = OnlinePayment::findOne(['order_id' => $order->order_id])) die;

        $payment = new OnlinePayment();
        $payment->setAttributes([
            'order_id' => $order->order_id,
            'order_hash' => $request['order_id'],
            'offer_id' => $order->offer_id,
            'amount' => $request['amount'],
            'currency_id' => Currency::findOne(['currency_name' => $request['currency']])->currency_id,
            'currency_name' => $request['currency'],
            'tracking_id' => (string)$request['tracking_id'],
            'bank_ref_no' => (string)$request['bank_ref_no'],
            'payment_name' => 'CcAvenue',
            'payment_status' => (string)$request['order_id'],
            'message' => $request['failure_message'],
            'serialized_data' => json_encode($request)
        ]);
        if (!$payment->save())
            FishHelper::debug($payment->errors);
//        throw new ValidateException($payment->errors);

        $order_sku = $this->getOrderSku($order, $request['amount']);

        $order->paid_online = 1;
        $order->instrument = OrderInfoInstrument::TDS_ONLINE_PAYMENT;
        (new OrderCommonService())->saveOrderSku($order, $order_sku, OrderSkuInstrument::TDS_ONLINE_PAYMENT);

        return true;
    }

    private function getOrderSku(Order $order, $amount)
    {
        $order_sku = [];

        if ($sku = TargetAdvertSku::find()->where([
            'target_advert_id' => $order->target_advert_id,
            'base_cost' => $amount
        ])->asArray()->one()) {
            $order_sku[] = [
                'sku_id' => $sku['sku_id'],
                'amount' => 1
            ];
        } else {
            $rules = TargetAdvertSkuRules::find()->where([
                'target_advert_id' => $order->target_advert_id,
                'cost' => $amount
            ])->asArray()->all();

            foreach ($rules as $key => $rule) {
                if ($key == 2) break;
                $order_sku[] = ['sku_id' => $rule['sku_id'], 'amount' => 1];
            }
        }
        return $order_sku;
    }
}