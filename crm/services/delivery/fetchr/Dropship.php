<?php
/**
 * Created by PhpStorm.
 * User: evild
 * Date: 3/16/17
 * Time: 12:11 PM
 */

namespace crm\services\delivery\fetchr;

use common\models\order\Order;
use common\services\delivery\DeliveryException;
use common\services\order\OrderSkuCommonService;
use crm\services\delivery\AbstractDeliveryApi;
use crm\services\delivery\DeliveryApiInterface;

class Dropship extends Fetchr implements DeliveryApiInterface
{
//    private $username = 'UCT_dropship';
    private $username = 'ecommerce_dom_ae';
//    private $password = '987654321';
    private $password = 'ecommerce_dom_ae';
//    private $api_url = 'https://spawn.stag.fetchr.us/order';
    private $api_url = 'https://xapi.fetchr.us/order';
//    private $client_address_id = 'ADDR3820497_4771';            //TODO: implement real address (should be set at personal page at Fetchr site)
    private $client_address_id = 'ADDRAC00042012737_31851';            //TODO: implement real address (should be set at personal page at Fetchr site)
//    private $authorization = 'Bearer eyJhbGciOiJIUzI1NiIsImV4cCI6MTY1ODIyMDI0OCwiaWF0IjoxNTAyNzAwMjQ4fQ.eyJYLUNsaWVudC1OYW1lIjoiZHVtbXkiLCJzYW5kYm94Ijp0cnVlLCJwcml2aWxlZ2VzIjp7ImNyZWRlbnRpYWxzIjoiY3J1ZCIsInRyYWNraW5nIjoiY3J1ZCIsIm9yZGVycyI6ImNydWQiLCJub3RpZmljYXRpb25zIjoiY3J1ZCJ9LCJYLUNsaWVudC1JRCI6MTIxODJ9.oOTC-XIHzp7wSqHYMjFFhwgkLIUp7NEFPdWBzDXibrU';
    private $authorization = 'Bearer 4556ba36-e273-4de3-a51c-007d86337e3b';

    public function send($orders, $credentials = null)
    {
        $response = json_decode($this->createShipment($orders), true);

        return $this->responseProcessing($response);
    }

    private function createShipment($orders)
    {
        $headers = ['Authorization:' . $this->authorization];
        $fields = $this->prepareData($orders);
        $request = [
            'username'          => $this->username,
            'password'          => $this->password,
            'client_address_id' => $this->client_address_id,
            'data'              => $fields
        ];

        $body = json_encode($request, JSON_HEX_QUOT);

        return $this->request($this->api_url, [], $body, $headers);
    }

    protected function setItems(Order $order)
    {
        $items = [];
        foreach ($order->orderSku as $order_sku) {
            $item = [
                "description"    => (string)OrderSkuCommonService::getOrderAllSkuString($order->order_id) ?: '-',
                "sku"            => (string)$order_sku->sku->sku_name,
                "hs_code"        => (string)'',
                "quantity"       => (int)1,
            ];
            $items[] = $item;
        }

        return $items;
    }

    protected function setDataList(Order $order, $items)
    {
        return [
            "order_reference"    => (string)$order->order_hash,
            "name"               => (string)ucfirst($order->customer->name),
            "email"              => (string)$order->customer->email ?: 'customercare@shovo.net',
            "phone_number"       => (string)$order->customer->phone,
            "alternate_phone"    => (string)'-',
            "address"            => (string)$order->customer->address,
            "receiver_country"   => (string)$order->customer->country->country_name,
            "receiver_city"      => (string)$order->customer->city->city_name,
            "area"               => (string)'-',
            "payment_type"       => (string)'cod',
            "bag_count"          => (int)'1',
            "weight"             => (int)'0',
            "description"        => (string)OrderSkuCommonService::getOrderAllSkuString($order->order_id) ?: '-',
            "comments"           => (string)'-',
            "order_package_type" => (string)'-',
            "total_amount"       => (string)$order->total_cost ?: '0.0011',
            //"extra_data"         => [],
            "items"              => $items,
            //"scheduling"         => [
            //    "scheduling_token" => (string)'',
            //    "latitude"         => (int)'',
            //    "longitude"        => (int)'',
            //    "timeslot_start"   => (string)'',
            //    "timeslot_end"     => (string)'',
            //    "schedule_date"    => (string)'',
            //    "comments"         => (string)'',
            //]
        ];
    }
}
