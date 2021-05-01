<?php

namespace common\services\partner\logic;

use common\models\geo\Geo;
use common\models\order\Order;
use common\services\partner\PartnerInterface;
use common\services\partner\PartnerService;
use common\services\webmaster\Helper;

class LpCrm extends PartnerService implements PartnerInterface
{
    private const URL = 'http://ecomgroup.lp-crm.biz/api/addNewOrder.html';
    /**
     * amazon.de - Так сказал Виталик.
     * Еще Виталик предлагал shovo.net
     *
     * На функциональность поле не влияет, но ОБЯЗАТЕЛЬНО для заполнения в API LP CRM, хоть и не указано в документации по API.
     */

    private $site = 'amazon.de';
    private $geo_iso;
    private $comment_string = '';

    public function setGeo(string $geo_iso)
    {
        $this->geo_iso = $geo_iso;
    }

    public function send(Order $order): array
    {
        $products = $this->setProducts($order->orderSku);
        $sender = urlencode(serialize($_SERVER));

        $data = $this->setRequestData($order, $products, $sender);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, self::URL);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        $out = curl_exec($curl);
        curl_close($curl);

        return $this->compareResponse($out);
    }

    private function compareResponse($out)
    {
        $response = json_decode($out, true);

        if (isset($response['status']) && $response['status'] === 'ok') {
            $response['status'] = 'success';

            if (isset($response['data'])
                && isset($response['data'][0])
                && isset($response['data'][0]['order_id'])
                && !empty($response['data'][0]['order_id'])) {
                $response['order_id'] = $response['data'][0]['order_id'];
            }
        }
        return $response;
    }

    private function setRequestData(Order $order, $products, $sender)
    {
        return [
            'key' => \Yii::$app->params['LP_CRM_API_KEY'], //Ваш секретный токен
//            'order_id' => number_format(round(microtime(true) * 10), 0, '.', ''), //идентификатор (код) заказа (*автоматически*)
            'order_id' => $order->order_hash,
            'country' => $this->geo_iso,                         // Географическое направление заказа
            'office' => '1',                          // Офис (id в CRM)
            'products' => $products,                    // массив с товарами в заказе
            'bayer_name' => $order->customer->name,            // покупатель (Ф.И.О)
            'phone' => $order->customer->phone,           // телефон
            'email' => $order->customer->email,                   // электронка
            'comment' => $this->comment_string,    // комментарий
            'delivery' => '',        // способ доставки (id в CRM)
            'delivery_adress' => $order->customer->address, // адрес доставки
            'payment' => '',                           // вариант оплаты (id в CRM)
            'sender' => $sender,
            'utm_source' => '',  // utm_source
            'utm_medium' => '',  // utm_medium
            'utm_term' => '',    // utm_term
            'utm_content' => '', // utm_content
            'utm_campaign' => '',// utm_campaign
            'additional_1' => '',                               // Дополнительное поле 1
            'additional_2' => '',                               // Дополнительное поле 2
            'additional_3' => '',                               // Дополнительное поле 3
            'additional_4' => '',                              // Дополнительное поле 4

            'site' => $this->site,
        ];
    }

    private function setProducts($orderSku)
    {
        $products_list = [];
        foreach ($orderSku as $sku) {
            $this->comment_string .= $sku->sku->sku_alias . '; ';
            $products_list[] = [
                'product_id' => $sku->sku_id,    //код товара (из каталога CRM)
                'price' => $sku->cost, //цена товара 1
                'count' => $sku->amount,
                'subs' => []
            ];
        }
        return urlencode(serialize($products_list));
    }
}
