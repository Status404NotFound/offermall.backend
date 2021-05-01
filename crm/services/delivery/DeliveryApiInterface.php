<?php

namespace crm\services\delivery;

use common\models\order\Order;

/**
 * Interface DeliveryApiInterface
 * @package crm\services\delivery
 */
interface DeliveryApiInterface
{

    /**
     * @return mixed
     */
    public function getClassName();
    
    /**
     * @param $url
     * @param $get_fields
     * @param $post_fields
     * @param $headers
     *
     * @return array
     */
    public function request($url, $get_fields, $post_fields, $headers);
    
    /**
     * @param Order $order
     * @param $credentials
     * @return array
     */
    public function send($order, $credentials);
}