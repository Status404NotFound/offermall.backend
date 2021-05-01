<?php

namespace crm\services\delivery\mara;

use common\models\order\Order;
use common\services\delivery\DeliveryException;
use common\services\order\OrderSkuCommonService;
use crm\services\delivery\AbstractDeliveryApi;
use crm\services\delivery\DeliveryApiInterface;

class MaraExpress extends AbstractDeliveryApi implements DeliveryApiInterface
{
    private $api_url = 'https://mara.fareye.co/api/v1/process?';
    //private $api_url = 'http://api.maraxpress.com?';
    //private $api_url = 'https://merchant.maraxpress.com/api/v1/process?';
    private $api_key = '6WkkKJr0Z3usdd6pp62nDOUxRzEk4eha';
    //private $api_key = 'BKuCfUnV5vznPsAiTkzCBQJBLB6z0tYx';
    //private $api_key = '1e2fea7933d64b5793142877029290c4';

    public function send($order, $credentials = null)
    {
        $response = json_decode($this->createShipment($order), true);

        if ( !empty($response["failureList"])) {
            $reason = $response['failureList'][0]["failReason"];
            throw new DeliveryException("Failed to send Order #$order->order_hash. Reason: $reason");
        }

        return [
            'track_number'  => $response["successList"][0],
            'remote_status' => $response["successResponse"][0]["process"]["status"],
        ];
    }

    private function createShipment(Order $order)
    {
        $request = json_encode($this->prepareData($order));
        $get_fields = ['api_key' => $this->api_key];
        $response = $this->request($this->api_url, $get_fields, $request);

        return $response;
    }

    private function prepareData(Order $order)
    {
        return [
            [
                "merchantCode"          => "UGT",
                //"merchantCode"          => "demo_mara",
                //"merchantCode"          => "ugt_mara",
                "processDefinitionCode" => "logistic_cycle_new",
                "processData"           => [
                    "order_reference"        => (string)$order->order_hash,
                    "customer_name"          => ucfirst($order->customer->name),
                    "customer_contact_no"    => '+' . (string)$order->customer->phone,
                    "Apartment_Villa"        => (string)$order->customer->address,
                    "City_Emirate"           => ucfirst($order->customer->city->city_name),
                    "amount_to_be_collected" => $order->paid_online == 0 ? (string)$order->total_cost : 0,
                    "mode_of_payment"        => "CashOnDelivery",
                    "supplier_name"          => "UCT",
                    "product_description"    => OrderSkuCommonService::getOrderAllSkuString($order->order_id),
                    "vendor_address"         => "Business Bay, Dubai, UAE",
                    "supplier_contact_no"    => "+971557440815",
                    "email"                  => "customercare@crmka.net",
                    "type_of_delivery"       => "Forward",
                    "area"                   => "",
                    "comments"               => "",
                    "value_of_product"       => "String Content"
                ],
                //"processUserMappings"   => [
                //    [
                //        "flowCode"     => "regular_delivery",
                //        "cityCode"     => 'Sharjah',
                //        "cityCode"     => ucfirst($order->customer->city->city_name),
                //        "branchCode"   => "",     /*optional*/
                //        "employeeCode" => "",   /*optional*/
                //    ],
                //    [
                //        "flowCode"     => "reverse_pickup",
                //        "cityCode"     => 'Sharjah',
                //        "cityCode"     => ucfirst($order->customer->city->city_name),
                //        "branchCode"   => "",     /*optional*/
                //        "employeeCode" => "",   /*optional*/
                //    ]
                //]
            ]
        ];
    }
}
