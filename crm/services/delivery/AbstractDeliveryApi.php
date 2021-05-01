<?php
/**
 * Created by PhpStorm.
 * User: evild
 * Date: 5/21/18
 * Time: 3:07 PM
 */

namespace crm\services\delivery;

use linslin\yii2\curl\Curl;

abstract class AbstractDeliveryApi implements DeliveryApiInterface
{
    public function getClassName()
    {
        return (new \ReflectionClass($this))->getShortName();
    }
    
    public function request($url = '', $get_fields = [], $post_fields = [], $headers = [])
    {
        $basic_headers = ['Content-Type: application/json'];
        
        $curl = new Curl();
        $curl->setOptions([
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => $post_fields,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => !empty($headers) ? array_merge($headers, $basic_headers) : $basic_headers
        ]);
        $curl->post($url . http_build_query($get_fields));

        return $curl->response;
    }
    
    abstract public function send($orders, $credentials = null);
    
    //abstract public function getOrderStatus($orders);
}