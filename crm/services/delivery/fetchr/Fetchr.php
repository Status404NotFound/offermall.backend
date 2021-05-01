<?php
/**
 * Created by PhpStorm.
 * User: evild
 * Date: 7/11/18
 * Time: 11:35 AM
 */

namespace crm\services\delivery\fetchr;

use common\services\delivery\DeliveryException;
use crm\services\delivery\AbstractDeliveryApi;
use crm\services\delivery\DeliveryApiInterface;
use Exception;

abstract class Fetchr extends AbstractDeliveryApi implements DeliveryApiInterface
{
    //protected function getBulkOrderStatus(array $tracking_numbers)
    //{
    //    $headers = ['Authorization:' . self::$authorization[$credentials]];
    //    $body = json_encode(['tracking_numbers' => $tracking_numbers], JSON_HEX_QUOT);
    //
    //    return $this->request($this->api_url . 'order/status', [], $body, $headers);
    //}
    
    protected function prepareData($orders): array
    {
        $data_list = [];
        
        if ( !is_array($orders)) {
            $items = $this->setItems($orders);
    
            if (empty($items)) {
                throw new Exception('Items can not be empty!');
            }
    
            $data_list[] = $this->setDataList($orders, $items);
        } else {
            foreach ($orders as $order) {
                $items = $this->setItems($order);
        
                if (empty($items)) {
                    throw new Exception('Items can not be empty!');
                }
        
                $data_list[] = $this->setDataList($order, $items);
            }
        }
        
        if (empty($data_list)) {
            throw new Exception('Data list can not be empty!');
        }
        
        return $data_list;
    }
    
    protected function responseProcessing(array $response): array
    {
        if ($response['status'] == 'error') {
            $reason = $response['message'] ?? '';
            $error_code = $response['error_code'] ?? '';
            throw new DeliveryException("Failed to send. Reason: $reason. Error Code: $error_code");
        }
        
        if (isset($response['data'])) {
            foreach ($response['data'] as $one_order) {
            
                $reason = $one_order['message'] ?? '';
                $error_code = $one_order['error_code'] ?? '';
                $order_reference = $one_order['order_reference'] ?? '';
                $tracking_no = $one_order['tracking_no'] ?? '';
                $so_no = $one_order['so_no'] ?? '';
            
                if ($one_order['status'] == 'error') {
                    throw new DeliveryException("Failed to send Order #$order_reference. Reason: $reason. Error Code: $error_code");
                }
            
                if ($one_order['status'] == 'success') {
                    return [
                        'track_number'  => $tracking_no,
                        'shipment_no'   => $so_no,
                        'remote_status' => $one_order['status'],
                    ];
                }
            }
        } else {
            return false;
        }
        
        //$success = '';
        //$failed = '';
        //$response_log = [];
        
        //if (isset($response['data'])) {
        //    foreach ($response['data'] as $one_order) {
        //
        //        $reason = $one_order['message'] ?? '';
        //        $error_code = $one_order['error_code'] ?? '';
        //        $order_reference = $one_order['order_reference'] ?? '';
        //        $tracking_no = $one_order['tracking_no'] ?? '';
        //        $so_no = $one_order['so_no'] ?? '';
        //
        //        if ($one_order['status'] == 'error') {
        //            $failed .= !empty($failed) ? '. ' : '';
        //            $failed .= "Failed to send Order #$order_reference. Reason: $reason. Error Code: $error_code";
        //        }
        //
        //        if ($one_order['status'] == 'success') {
        //            $response_log[$order_reference] = [
        //                'track_number'  => $tracking_no,
        //                'shipment_no'   => $so_no,
        //                'remote_status' => $one_order['status'],
        //            ];
        //
        //            $success .= !empty($success) ? '. ' : '';
        //            $success .= "Success to send Order #$order_reference. Reason: $reason. Error Code: $error_code. Tracking no #$tracking_no. So no #$so_no";
        //        }
        //    }
        //    if ( !empty($failed)) {
        //        $response_log['log'] = ['success' => $success, 'failed'  => $failed];
        //    }
        //}
    
        //return $response_log;
    }
}