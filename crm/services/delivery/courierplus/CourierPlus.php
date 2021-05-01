<?php
/**
 * Created by PhpStorm.
 * User: evild
 * Date: 5/3/18
 * Time: 2:47 PM
 */

namespace crm\services\delivery\courierplus;

use common\models\order\Order;
use common\services\delivery\DeliveryException;
use common\services\order\OrderSkuCommonService;
use crm\services\delivery\AbstractDeliveryApi;
use crm\services\delivery\DeliveryApiInterface;

class CourierPlus extends AbstractDeliveryApi implements DeliveryApiInterface
{
    private $api_url = 'http://trackplus.courierpluslogistics.com/api/';
    private $api_private_key = '9U9M3ROE0UAYKRMSPPNMB7OK';
    private $api_registration_number = '729390';

    public function send($order, $credentials = null)
    {
        $response = json_decode($this->createShipment($order), true);

        if ($response['status'] == 'error') {
            $reason = $response['message'];
            throw new DeliveryException("Failed to send Order #$order->order_hash. Reason: $reason");
        }

        return [
            'track_number'  => $response['data']['waybill_number'],
            'remote_status' => $response['status'],
        ];
    }

    private function createShipment(Order $order)
    {
        $url = $this->api_url . 'parcel/create?';
        $get_fields = ['registration_number' => $this->api_registration_number, 'private_key' => $this->api_private_key];

        $request = json_encode($this->prepareData($order));
        $response = $this->request($url, $get_fields, $request);

        return $response;
    }

    private function prepareData(Order $order)
    {
        return [
            "shipment_order_number"       => (string)$order->order_hash ?: 'default',
            "shipment_customer_reference" => "0",
            "shipment_consignee_name"     => ucfirst($order->customer->name) ?: 'default',
            "shipment_consignee_address1" => (string)$order->customer->address ?: 'default',
            "shipment_consignee_address2" => "0",
            "shipment_consignee_city"     => (string)lcfirst($order->customer->city->city_name),
            "shipment_consignee_email"    => (string)$order->customer->email ?: 'customercare@crmka.net',
            "shipment_consignee_tel"      => '+' . (string)$order->customer->phone ? : '0',
            "shipment_weight"             => "1",
            "shipment_pieces"             => $order->total_amount ?: '0',
            "shipment_value"              => $order->paid_online == 0 ? (string)$order->total_cost : 0 ?: '0',
            "shipment_description_1"      => OrderSkuCommonService::getOrderAllSkuString($order->order_id) ?: 'default',
            "shipment_description_2"      => "default",
            "shipment_sender_name"        => "UCT",
            "shipment_sender_city"        => "dubai (dxb, 971)",
            "shipment_sender_address_1"   => "Business Bay, Dubai, UAE",
            "shipment_sender_address_2"   => "default",
            "is_cash_on_delivery"         => $order->paid_online == 0 ? 1 : 0,
            "cash_on_delivery_amount"     => $order->paid_online == 0 ? (string)$order->total_cost : 0,
        ];
    }
}


















