<?php
/**
 * Created by PhpStorm.
 * User: evild
 * Date: 8/30/18
 * Time: 1:23 PM
 */

namespace crm\services\delivery\mara;

use common\models\offer\Offer;
use common\models\order\Order;
use common\services\order\OrderSkuCommonService;
use crm\services\delivery\AbstractDeliveryApi;
use common\services\delivery\DeliveryException;

/**
 * This is the model class for table "delivery_api".
 *
 * @property string $_api_url
 * @property string $_script_id
 * @property string $_deploy_id
 * @property string $_consumer_key
 * @property string $_consumer_secret
 * @property string $_token_id
 * @property string $_token_secret
 * @property string $_signature_method
 * @property string $_oauth_version
 * @property string $_realm_shipa_delivery_id
 * @property string $_client_id_on_shipa_delivery
 */

class ShipaDelivery extends AbstractDeliveryApi
{
    // Testing Environment
    //private $_api_url = 'https://rest.eu1.netsuite.com/app/site/hosting/restlet.nl';
    //private $_script_id = '127';
    //private $_deploy_id = '1';
    //private $_consumer_key = '83ebe31387694d42ce7564d481b527581ee804a43faadd8a77eec0fce3510de4';
    //private $_consumer_secret = '669a5a72a771619d3092a57d31114b2dbee79988dc0af309895e1940a63f96fd';
    //private $_token_id = '3ec8b878bf77abc860ae22df0512b8322703226789b317843da57163727bc77d';
    //private $_token_secret = '4a4a83ff4c9adb49b42fdb578538451d18a36ba027a2b3e10fcb49a7ae624ef8';
    //private $_signature_method = 'HMAC-SHA1';
    //private $_oauth_version = '1.0';
    //private $_realm_shipa_delivery_id = '4344065_SB1';
    //private $_client_id_on_shipa_delivery = '198215';

    // Production Environment
    private $_api_url = 'https://rest.eu2.netsuite.com/app/site/hosting/restlet.nl';
    private $_script_id = '127';
    private $_deploy_id = '1';
    private $_consumer_key = 'dd06f959ce72688e139176ae9944a86dc514bb5062df0c445f434346039aa9b2';
    private $_consumer_secret = 'a192b515feb92e4dc486d88cf591bbf7aa44614cde804ed9821dff3f871bc658';
    private $_token_id = '779889909ac226ce08247a6a1a2bf57d19851f6bdb7140e1a7ecc1f7e6655bd6';
    private $_token_secret = '5c192258d1b940d13cb6248d4c02ed6342b792bb8075587334d5702bdf5ad45b';
    private $_signature_method = 'HMAC-SHA1';
    private $_oauth_version = '1.0';
    private $_realm_shipa_delivery_id = '4344065';
    private $_client_id_on_shipa_delivery = '866226';

    public function send($order, $credentials = null)
    {
        $response = json_decode($this->createShipment($order, $credentials), true);

        if ( !empty($response['error'] && !empty($response['code']))) {
            throw new DeliveryException("Failed to send Order #$order->order_hash. Reason: " . $response['error'] . ' Code: ' . $response['code'] . '.');
        }

        return [
            'track_number'  => $response['carryid'],
            'remote_status' => $response['status'],
        ];
    }

    private function createShipment($order)
    {
        $oauth_nonce = md5(mt_rand());
        $oauth_timestamp = time();
        $base_string =
            "POST&" . urlencode($this->_api_url) . "&" .
            urlencode(
                "deploy="               . $this->_deploy_id
                . "&oauth_consumer_key="     . $this->_consumer_key
                . "&oauth_nonce="            . $oauth_nonce
                . "&oauth_signature_method=" . $this->_signature_method
                . "&oauth_timestamp="        . $oauth_timestamp
                . "&oauth_token="            . $this->_token_id
                . "&oauth_version="          . $this->_oauth_version
                . "&realm="                  . $this->_realm_shipa_delivery_id
                . "&script="                 . $this->_script_id
            );

        $sig_string = urlencode($this->_consumer_secret) . '&' . urlencode($this->_token_secret);
        $signature = base64_encode(hash_hmac("sha1", $base_string, $sig_string, true));
        $auth_header = "OAuth "
            . 'oauth_signature="'        . rawurlencode($signature) . '", '
            . 'oauth_version="'          . rawurlencode($this->_oauth_version) . '", '
            . 'oauth_nonce="'            . rawurlencode($oauth_nonce) . '", '
            . 'oauth_signature_method="' . rawurlencode($this->_signature_method) . '", '
            . 'oauth_consumer_key="'     . rawurlencode($this->_consumer_key) . '", '
            . 'oauth_token="'            . rawurlencode($this->_token_id) . '", '
            . 'oauth_timestamp="'        . rawurlencode($oauth_timestamp) . '", '
            . 'realm="'                  . rawurlencode($this->_realm_shipa_delivery_id) .'"';

        $data = json_encode($this->prepareData($order));
        $headers = [
            'Authorization: ' . $auth_header,
            'Content-Length: ' . \strlen($data)
        ];

        $get_fields = [
            'script' => $this->_script_id,
            'deploy' => $this->_deploy_id,
            'realm'  => $this->_realm_shipa_delivery_id,
        ];

        return $this->request($this->_api_url . '?', $get_fields, $data, $headers);
    }

    private function prepareData(Order $order): array
    {
        return [
            'userid'     => $this->_client_id_on_shipa_delivery,
            'action'     => 'AddOrder',     //AddOrder TrackOrder
            'externalid' => (string)$order->order_hash,
            'data'       => [
                'receiver_location'         => '0',
                'receiver_address'          => (string)$order->customer->address,
                'receiver_contact_name'     => ucfirst($order->customer->name),
                'receiver_contact_number'   => (string)$order->customer->phone,
                'dispatcher_location'       => '0',
                'dispatcher_address'        => 'Business Bay, Dubai, UAE',
                'dispatcher_contact_number' => '971557440815',
                'package_name'              => (string)Offer::findOne(['offer_id' => $order->offer_id])->description . '. ' . $order->information,
                'package_description'       => (string)OrderSkuCommonService::getOrderAllSkuString($order->order_id),
                'pickup_time'               => (string)date('d/m/Y h:i:s a'),
                'dropoff_time'              => (string)date('d/m/Y h:i:s a', strtotime($order->delivery_date)),
                'amount_to_collect'         => $order->paid_online == 0 ? (string)$order->total_cost : 0,
                'vehicle_type'              => 1,
                'handling_type'             => '[]',
            ],
        ];
    }
}