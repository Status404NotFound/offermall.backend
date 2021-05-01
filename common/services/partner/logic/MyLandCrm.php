<?php

namespace common\services\partner\logic;

use common\models\log\orderInfo\OrderInfoInstrument;
use common\models\order\OrderStatus;
use common\models\SendedToPartner;
use common\services\order\OrderCommonService;
use Yii;
use common\models\order\Order;
use common\services\partner\PartnerInterface;
use common\services\partner\PartnerService;
use yii\db\Exception;

class MyLandCrm extends PartnerService implements PartnerInterface
{
    private const URL = 'https://www.mylandcrm.com/api/v1/lead';

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

    private $geo_iso;

    public function setGeo(string $geo_iso)
    {
        $this->geo_iso = $geo_iso;
    }

    public function send(Order $order): array
    {
        $data = $this->setRequestData($order);

        $curl = curl_init();
        curl_setopt_array($curl, array
        (
            CURLOPT_URL => self::URL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array
            (
                "content-type: application/json",
            )
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        $resp = $this->compareResponse($response);
        return $resp ?? [];
    }

    private function compareResponse($out)
    {
        $out_array = json_decode($out, true);
        $response = [];
        if (isset($out_array['success']) && (int)$out_array['success'] === 1) {
            $response['status'] = 'success';
            if (isset($out_array['leadHash'])) {
//                if (!$order = Order::findOne(['order_hash' => $out_array['leadHash']])) {
//                if (!$order = Order::findOne(['order_hash' => $out_array['affiliatereference']])) {
////                    throw new Exception('Order with hash ' . $out_array['leadHash'] . ' not found.');
//                    throw new Exception('Order with hash ' . $out_array['affiliatereference'] . ' not found.');
//                }
//                $response['order_id'] = $order->order_id;
//                $response['order_id'] = $order_id;
                $response['order_id'] = $out_array['leadHash'];
            }
        } else {
            Yii::error($out_array, 'my_land_crm_e');
            return $out_array;
        }
        return $response;
    }

    private function setRequestData(Order $order)
    {
        return [
            "offerHash" => Yii::$app->params['my_land_crm_offers_config'][$order->offer_id]['offerHash'],
            "affiliateHash" => Yii::$app->params['my_land_crm_offers_config']['affiliateHash'],
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
        ];
    }

    public function updateOrder(Order $order, $new_status)
    {
        if (!in_array($new_status, self::STATUS_MAP)) {
            Yii::error('Remote status have no candidate in status map.');
        }
        $order->instrument = OrderInfoInstrument::MY_LAND_CRM_POSTBACK;
        $service = new OrderCommonService();
        $service->changeStatus($order, self::STATUS_MAP[$new_status], [
            'reason_id' => 0,
            'address' => !empty($order->customer->address) ? $order->customer->address : "No Address.",
            'delivery_date' => (new \DateTime())->add(new \DateInterval('P5D'))->format('Y-m-d H:i:s')
        ]);
    }
}
