<?php
/**
 * Created by PhpStorm.
 * User: evild
 * Date: 3/16/17
 * Time: 11:14 PM
 */

namespace crm\services\delivery\fetchr;

use common\models\order\Order;
use crm\services\delivery\DeliveryApiInterface;

class Fulfillment extends Fetchr implements DeliveryApiInterface
{
    private $api_url = 'https://api.order.fetchr.us/';

    public $merchant_name = 'UCT';
    public $merchant_phone = '+971557440815';
    public $merchant_address = '3409, Burlington Tower, Business Bay, Dubai, UAE';

    public $warehouse_address;

    private static $warehouse = [
        'sb_uae'     => 'UAE_DXB_WHS_001',
        'sb_ksa'     => 'KSA_RUH_WHS_001',
        'sb_bahrain' => 'BHR_AMH_WHS_001',
        'sb_jordan'  => 'JOR_AMN_WHS_001',
        'sb_egypt'   => 'EGY_CAI_WHS_001',
    ];

    private static $authorization = [
        'sb_uae'     => 'Bearer eyJhbGciOiJIUzI1NiIsImV4cCI6MTY3NDgyNjY1NywiaWF0IjoxNTE5MzA2NjU3fQ.eyJYLUNsaWVudC1OYW1lIjoiVUNUIiwic2FuZGJveCI6ZmFsc2UsInByaXZpbGVnZXMiOnsiY3JlZGVudGlhbHMiOiJjcnVkIiwidHJhY2tpbmciOiJjcnVkIiwib3JkZXJzIjoiY3J1ZCIsIm5vdGlmaWNhdGlvbnMiOiJjcnVkIn0sIlgtQ2xpZW50LUlEIjozODIwNDk3fQ.0HGDw93HPaPUqmCPzUGy3b7SZGsNuwzvWqtcrJydG_g',
        'sb_ksa'     => 'Bearer eyJhbGciOiJIUzI1NiIsImV4cCI6MTY3NDgyNjc0NywiaWF0IjoxNTE5MzA2NzQ3fQ.eyJYLUNsaWVudC1OYW1lIjoidWN0X2tzYWZ4ZiIsInNhbmRib3giOmZhbHNlLCJwcml2aWxlZ2VzIjp7ImNyZWRlbnRpYWxzIjoiY3J1ZCIsInRyYWNraW5nIjoiY3J1ZCIsIm9yZGVycyI6ImNydWQiLCJub3RpZmljYXRpb25zIjoiY3J1ZCJ9LCJYLUNsaWVudC1JRCI6ODIwNTc1M30.CAca5UnICJ0f66kMCZMf1ByOyDU85mqqSafImXnmof0',
        'sb_bahrain' => 'Bearer eyJhbGciOiJIUzI1NiIsImV4cCI6MTY3NDgyNjcyMywiaWF0IjoxNTE5MzA2NzIzfQ.eyJYLUNsaWVudC1OYW1lIjoidWN0X2JhaHJhaW4iLCJzYW5kYm94IjpmYWxzZSwicHJpdmlsZWdlcyI6eyJjcmVkZW50aWFscyI6ImNydWQiLCJ0cmFja2luZyI6ImNydWQiLCJvcmRlcnMiOiJjcnVkIiwibm90aWZpY2F0aW9ucyI6ImNydWQifSwiWC1DbGllbnQtSUQiOjcyMTUxMzZ9.ygMjLkTLg7YapGpucON_UujH8E0-1v2Wr5hmvHrhyjE',
        'sb_jordan'  => 'Bearer eyJhbGciOiJIUzI1NiIsImV4cCI6MTY3NDgyNjY5OSwiaWF0IjoxNTE5MzA2Njk5fQ.eyJYLUNsaWVudC1OYW1lIjoidWN0X2Z4Zl9qb3JkYW4iLCJzYW5kYm94IjpmYWxzZSwicHJpdmlsZWdlcyI6eyJjcmVkZW50aWFscyI6ImNydWQiLCJ0cmFja2luZyI6ImNydWQiLCJvcmRlcnMiOiJjcnVkIiwibm90aWZpY2F0aW9ucyI6ImNydWQifSwiWC1DbGllbnQtSUQiOjE2MDU1NDA0fQ.FtBJeOrSgF-3oFq2BfMaUO4M31FjfNke1H5BE2n7Vmw',
        'sb_egypt'   => 'Bearer 265cda32-f65f-469e-9a61-0a0f342b76e6',
    ];

    //private static $credentials = [
    //    'sb_uae'     => ['username' => 'uct_llc', 'password' => 'uct_llc'],
    //    'sb_bahrain' => ['username' => 'uct_bahrain', 'password' => 'uct_bahrain'],
    //    'sb_ksa'     => ['username' => 'uct_ksafxf', 'password' => 'uct_ksafxf'],
    //    'sb_jordan'  => ['username' => 'uct_fxf_jordan', 'password' => 'advert34'],
    //    'sb_egypt'   => ['username' => 'uct_fxf_egy', 'password' => 'uct_fxf_egy'],
    //];

    public function send($orders, $credentials = null)
    {
        $response = json_decode($this->createShipment($orders, $credentials), true);

        if (is_null($response)) {
            return false;
        }
        //$response = json_decode($this->getBulkOrderStatus([]), true);

        return $this->responseProcessing($response);
    }

    //public function getOrderStatus($orders)
    //{
    //    $response = json_decode($this->getBulkOrderStatus(), true);
    //
    //    //$response = json_decode($this->getBulkOrderStatus([]), true);
    //
    //    return $this->responseProcessing($response);
    //}

    private function createShipment($orders, $credentials)
    {
        $this->warehouse_address = self::$warehouse[$credentials];
        $headers = ['Authorization:' . self::$authorization[$credentials]];
        $body = json_encode(['data' => $this->prepareData($orders, $credentials)], JSON_HEX_QUOT);

        return $this->request($this->api_url . 'fulfillment', [], $body, $headers);
    }

    protected function setItems(Order $order)
    {
        $items = [];
        foreach ($order->orderSku as $order_sku) {
            $item = [
                "name"           => (string)$order->offer->offer_name,
                "sku"            => (string)$order_sku->sku->sku_name,
                "quantity"       => (string)$order_sku->amount,
                "processing_fee" => (int)0,
                "price_per_unit" => (int)$order->paid_online == 0 ? number_format($order->total_cost / $order->total_amount, 2) : 0,
            ];
            $items[] = $item;
        }

        return $items;
    }

    protected function setDataList(Order $order, $items)
    {
        return [
            "items"              => $items,
            "warehouse_location" => [
                "id" => (string)$this->warehouse_address,
            ],
            "details"            => [
                "discount"           => (int)0,
                "extra_fee"          => (int)0,
                "payment_type"       => (string)'cod',
                "order_reference"    => (string)$order->order_hash,
                "customer_name"      => (string)ucfirst($order->customer->name),
                "customer_phone"     => (string)$order->customer->phone,
                "customer_email"     => (string)$order->customer->email ?: 'customercare@crmka.net',
                "customer_address"   => (string)$order->customer->address,
                "customer_latitude"  => (int)'-',
                "customer_longitude" => (int)'-',
                "customer_country"   => (string)$order->customer->country->country_name,
                "customer_city"      => (string)$order->customer->city->city_name,
                "comments"           => (string)'-'
            ]
        ];
    }
}
