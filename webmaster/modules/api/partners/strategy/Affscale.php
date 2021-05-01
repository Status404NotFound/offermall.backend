<?php


namespace webmaster\modules\api\partners\strategy;


use common\models\log\orderInfo\OrderInfoInstrument;
use common\models\order\Order;
use common\models\order\OrderStatus;
use common\services\order\OrderCommonService;
use webmaster\models\partners\PartnerOrders;
use webmaster\modules\api\partners\Strategy;
use webmaster\models\partners\PartnerOffers;
use Yii;

class Affscale implements Strategy
{
    private const URL = 'https://tracking85020.com/api/v2/affiliate/leads?api-key='.self::API_KEY;
    private const ADVERT_ID = 321;
    private const API_KEY = '7e2f44c45c6b77ffc32523955547c5eb6e61f716';

    public function send(array $orders): void
    {
        foreach ($orders as $order){
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

                file_put_contents($filePath."/test-orders.txt", $response, FILE_APPEND);

                sleep(1);
            }
        }
    }

    private function setRequestData($order): array
    {
        $reqData = [
            "goal_id" => self::getOfferHash($order),
            "lastname" => $order->customer->name,
            "email" => $order->customer->email,
            "phone" => $order->customer->phone,
            "address" => $order->customer->address,
            "subid1" => $order->orderData->sub_id_1,
            "subid2" => $order->orderData->sub_id_2,
            "subid3" => $order->orderData->sub_id_3,
            "aff_click_id" => $order->order_hash,
            "orderID" => $order->order_id
        ];

        return $reqData;
    }

    private function applyPartnerStatus($orderID, $crm_resp, $status)
    {
        $order = Order::find()->where(['order_id' => $orderID])->one();
        $order->instrument = OrderInfoInstrument::PARTNER_CRM;
        $comment = $crm_resp;
        $order->information = $comment;
        $order->save();
        (new OrderCommonService())->saveComment($order, $comment);
        if($status === OrderStatus::NOT_VALID){
            (new OrderCommonService())->changeStatus($order, $status, ['reason_id' => 30]);
        } else {
            (new OrderCommonService())->changeStatus($order, $status);
        }

    }
}