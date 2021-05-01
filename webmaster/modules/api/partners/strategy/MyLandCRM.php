<?php

namespace webmaster\modules\api\partners\strategy;

use common\models\log\orderInfo\OrderInfoInstrument;
use common\models\order\Order;
use common\models\order\OrderStatus;
use common\services\order\OrderCommonService;
use webmaster\models\partners\PartnerOffers;
use webmaster\models\partners\PartnerOrders;
use webmaster\modules\api\partners\PartnerCRM;
use webmaster\modules\api\partners\Strategy;
use Yii;

/**
 * Конкретные Стратегии реализуют алгоритм, следуя базовому интерфейсу
 * Стратегии. Этот интерфейс делает их взаимозаменяемыми в Контексте.
 */
class MyLandCRM implements Strategy
{
    private const URL = 'https://www.mylandcrm.com/api/v1/lead';
    private const ADVERT_ID = 268;

    private const STATUS_MAP = [
        'Pending' => OrderStatus::PENDING,
        'Rejected' => OrderStatus::REJECTED,
        'Rejected-Checked' => OrderStatus::REJECTED,
        'Not-Valid' => OrderStatus::NOT_VALID,
        'Waiting-For-Delivery' => OrderStatus::WAITING_DELIVERY,
        'Approved' => OrderStatus::WAITING_DELIVERY,
        'Not-Valid-Checked' => OrderStatus::NOT_VALID_CHECKED,
        'Cancelled' => OrderStatus::CANCELED,
        'Back-To-Pending' => OrderStatus::BACK_TO_PENDING,
        'Delivery-In-Progress' => OrderStatus::DELIVERY_IN_PROGRESS,
        'Success-Delivery' => OrderStatus::SUCCESS_DELIVERY,
        'Not-Paid' => OrderStatus::NOT_PAID,
        'Returned' => OrderStatus::RETURNED,
    ];

    public function send(array $orders): void
    {
        foreach ($orders as $order) {
            $data = $this->setRequestData($order);
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, self::URL);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/plain'));

            $response = curl_exec($ch);
            curl_close($ch);

            file_put_contents(Yii::getAlias('@webmaster').'/temp/log.txt', $response, FILE_APPEND);

            $this->compareResponse($response, $data["orderID"]);

            sleep(1);
        }
    }

    private function setRequestData($order): array
    {
        $reqData = [
            "offerHash" => PartnerCRM::getOfferHash($order),
            "affiliateHash" => 'c226405da7',
            "source" => "facebook.com",
            "name" => $order->customer->name,
            "email" => $order->customer->email,
            "phone" => $order->customer->phone,
            "address" => $order->customer->address,
            "postbacklink" => "http://r.crmka.net/order/my-land-postback",
            "subid1" => $order->orderData->sub_id_1,
            "subid2" => $order->orderData->sub_id_2,
            "subid3" => $order->orderData->sub_id_3,
            "affiliatereference" => $order->order_hash,
            "orderID" => $order->order_id
        ];
        return $reqData;
    }

    private function compareResponse($out, $orderID): void
    {
        $out_array = json_decode($out, true);
        if (isset($out_array['success']) && (int)$out_array['success'] === 1) {
            $targetOrder = PartnerOrders::find()->where(['order_id' => $orderID])->one();
            $targetOrder->status = PartnerOrders::ORDER_ACCEPT_STATUS;
            $crm_resp = $out_array['leadHash'];
            $targetOrder->crm_resp = $crm_resp;
            $targetOrder->save();
            $status = OrderStatus::PENDING;
        } else {
            $targetOrder = PartnerOrders::find()->where(['order_id' => $orderID])->one();
            $targetOrder->status = PartnerOrders::ORDER_REJECT_STATUS;
            $crm_resp = $out;
            $targetOrder->crm_resp = $crm_resp;
            $targetOrder->save();
            $status = OrderStatus::NOT_VALID;
        }

        $this->applyPartnerStatus($orderID, $crm_resp, $status);
    }

    private function applyPartnerStatus($orderID, $crm_resp, $status)
    {
        $order = Order::find()->where(['order_id' => $orderID])->one();
        $order->instrument = OrderInfoInstrument::PARTNER_CRM;
        $comment = $crm_resp;
        $order->information = $comment;
        $order->save();
        (new OrderCommonService())->saveComment($order, $comment);
        if ($status === OrderStatus::NOT_VALID) {
            (new OrderCommonService())->changeStatus($order, $status, ['reason_id' => 30]);
        } else {
            (new OrderCommonService())->changeStatus($order, $status);
        }

    }
}