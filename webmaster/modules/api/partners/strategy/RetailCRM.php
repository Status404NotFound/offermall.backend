<?php


namespace webmaster\modules\api\partners\strategy;


use common\models\order\Order;
use common\models\order\OrderStatus;
use webmaster\modules\api\partners\Strategy;
use webmaster\models\partners\PartnerOffers;
use webmaster\models\partners\PartnerOrders;
use Yii;

class RetailCRM implements Strategy
{

    private const URL = 'https://superdeal.retailcrm.ru/api/v5/orders/create?apiKey='.self::API_KEY;
    private const ADVERT_ID = 320;
    private const API_KEY = 'OD6AIuG3IUm3PIRAwRaLj9UjWgc5V0O0';

    public function send(array $orders): void
    {
        foreach ($orders as $order)
        {
            $data = $this->setRequestData($order);

            foreach ($data as $reqData){
                $ch = curl_init();

                curl_setopt($ch, CURLOPT_URL, self::URL);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
                curl_setopt($ch, CURLOPT_POST,           1 );
                curl_setopt($ch, CURLOPT_POSTFIELDS,     json_encode($reqData) );
                curl_setopt($ch, CURLOPT_HTTPHEADER,     array('Content-Type: application/json'));

                $response = curl_exec($ch);
                curl_close($ch);

                $filePath = Yii::getAlias('@webmaster');

                file_put_contents($filePath."/test-resp-retail.txt", $response, FILE_APPEND);

                sleep(1);
            }
        }
    }

    private function setRequestData($order): array
    {
        $reqData = [
            $order["source"]["source"] => "facebook",
            $order["source"]["campaign"] => $order->orderData->wm_id,
            "quantity" => 1,
            "firstName" => $order->name,
            "phone" => $order->phone,
            "orderID" => $order->order_id,
        ];

        return $reqData;
    }
}