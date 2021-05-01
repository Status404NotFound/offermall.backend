<?php

namespace crm\services\import;

use common\helpers\FishHelper;
use common\models\delivery\OrderDelivery;
use common\models\order\Order;
use common\models\order\OrderStatus;
use common\services\order\OrderNotFoundException;
use common\services\ValidateException;
use Dompdf\Exception;

class ImportService
{
    public function makeImport($base64_string)
    {
        $result = [];
        $report = ImportFactory::createDelivery('fetcher_cod_report');

        $file_path = $report->saveFile($base64_string);
        $report_array = $report->parseReport($file_path);
        $order_deliveries = $report->getOrderDeliveries($report_array['hashes']);

        $orders_failed = [];
        $success = [];

        $od_success = [];
        $od_failed = [];
        foreach ($order_deliveries as $order_delivery) {
            /** @var OrderDelivery $order_delivery */
            try {
                $report->saveOrderDelivery($report_array, $order_delivery);
                $od_success[$order_delivery->order_hash] = [
                    'order_hash' => $order_delivery->order_hash,
                    'remote_status' => $order_delivery->remote_status
                ];
            } catch (ValidateException $e) {
                $od_failed[$order_delivery->order_hash] = $e->getMessages();
                continue;
            } catch (ImportServiceException $e) {
                $od_failed[$order_delivery->order_hash] = $e->getMessage();
                continue;
            } catch (Exception $e) {
                $od_failed[$order_delivery->order_hash] = $e->getMessage();
                continue;
            }
        }

        $orders = Order::find()->where(['order_hash' => array_keys($od_success)])->all();
        foreach ($orders as $order) {
            try {
                $report->saveOrder($od_success, $order);
                $success[] = [
                    'order_hash' => $order->order_hash,
                    'order_status' => OrderStatus::attributeLabels($order->order_status)
                ];
            } catch (ImportServiceException $e) {
                $orders_failed[$order->order_hash] = $e->getMessage();
                continue;
            } catch (ValidateException $e) {
                $orders_failed[$order->order_hash] = $e->getMessages();
                continue;
            } catch (Exception $e) {
                $orders_failed[$order->order_hash] = $e->getMessage();
                continue;
            }
        }

        $result['failed'] = array_merge($od_failed, $orders_failed);
        $result['success'] = $success;
        return $result;
    }


    public function Import_1c_sku($base64_string)
    {
        $result = [];
        $report = ImportFactory::createDelivery('sku_names');

        $file_path = $report->saveFile($base64_string);
        $report_array = $report->parseReport($file_path);

        return $report_array;
    }


    public function setBitrix($base64_string)
    {
        $result = [];
        $report = ImportFactory::createDelivery('bitrix_flag');

        $file_path = $report->saveFile($base64_string);
        $order_hashes = $report->parseReport($file_path);
        $order_hashes_str = implode(', ', $order_hashes);
        $orders_failed = [];

        try {
            $report->saveOrders($order_hashes_str);
//            $report->saveOldConversions($order_hashes_str);
        } catch (ImportServiceException $e) {
            $result['failed'][] = $e->getMessage();
        } catch (Exception $e) {
            $result['failed'][] = $e->getMessage();
        }

        $result['failed'] = $orders_failed;
        $result['hashes_count'] = count($order_hashes);
        return $result;
    }


}