<?php

namespace crm\services\import\logic;

use common\helpers\FishHelper;
use common\models\delivery\OrderDelivery;
use common\models\order\Order;
use common\models\order\OrderStatus;
use common\services\order\OrderCommonService;
use common\services\ValidateException;
use crm\services\import\ImportServiceException;
use Yii;

class FetcherCODReport
{
    public function saveFile($base64_string)
    {
        $time = date('Y-m-d H:i:s');
        $file_path = Yii::$app->basePath . DIRECTORY_SEPARATOR . 'services' . DIRECTORY_SEPARATOR . 'import' . DIRECTORY_SEPARATOR . 'fetcher_cod_reports' . DIRECTORY_SEPARATOR . $time . '.xls';
        $base_to_php = explode(',', $base64_string);

        $data = base64_decode($base_to_php[1]);
        if (!file_put_contents($file_path, $data))
            throw new ImportServiceException('Failed to CREATE report file');

        chmod($file_path, 0777);
        return $file_path;
    }

    public function parseReport($file_path)
    {
        $file = \PHPExcel_IOFactory::createReader('Excel2007')
            ->load($file_path)
            ->getActiveSheet();

        $maxRow = $file->getHighestRow();

        $report_array = [];
        if ($file->getCell('B2')->getValue() == 'Account Invoice Cod Report') {
            $report_array['report_no'] = $file->getCell('A8')->getValue();
            for ($row = 11; $row < $maxRow; $row++) {
                $order_hash = (int)$file->getCell('D' . $row)->getValue();
                $report_array['hashes'][] = (int)$order_hash;

                if (strlen($order_hash) > 10) {
                    $report_array[$order_hash] = [
                        'order_hash' => $order_hash,
                        'shipment_no' => $file->getCell('B' . $row)->getValue(),
                        'remote_status' => $file->getCell('F' . $row)->getValue(),
                        'delivery_cost' => $file->getCell('J' . $row)->getValue(),
                        'money_in_fact' => (float)$file->getCell('L' . $row)->getValue(),
                        'delivery_date_in_fact' => $file->getCell('E' . $row)->getValue(),
                    ];
                }
            }
        }
//        FishHelper::debug($report_array, 0, 1);

        return $report_array;
    }

    public function getOrderDeliveries($hashes)
    {
        return OrderDelivery::find()->where(['order_hash' => $hashes])->all();
    }

    public function saveOrderDelivery($report_array, OrderDelivery $order_delivery)
    {
        if (($report_array[$order_delivery->order_hash]['remote_status'] == 'Delivered'
                && $report_array[$order_delivery->order_hash]['delivery_cost'] == $report_array[$order_delivery->order_hash]['money_in_fact']
            )
            || $report_array[$order_delivery->order_hash]['remote_status'] == 'Returned'
        ) {
            $order_delivery->delivery_date_in_fact = $report_array[$order_delivery->order_hash]['delivery_date_in_fact'];
            $order_delivery->money_in_fact = $report_array[$order_delivery->order_hash]['money_in_fact'];
            $order_delivery->remote_status = $report_array[$order_delivery->order_hash]['remote_status'];
            $order_delivery->shipment_no = $report_array[$order_delivery->order_hash]['shipment_no'];
            $order_delivery->currency_id = Order::getOrderCurrencyId($order_delivery->order_id);
            $order_delivery->report_no = $report_array['report_no'];

            if (!$order_delivery->save()) {
                throw new ValidateException($order_delivery->errors);
//                FishHelper::debug($order_delivery->errors, 0, 0);
            }
        } elseif ($report_array[$order_delivery->order_hash]['delivery_cost']
            != $report_array[$order_delivery->order_hash]['money_in_fact']) {
            throw new ImportServiceException('Money in fact not equals to delivery_cost. Delivery cost: ' . $report_array[$order_delivery->order_hash]['delivery_cost'] . '. Money in fact: ' . $report_array[$order_delivery->order_hash]['money_in_fact'] . '.');
        }
    }

    public function saveOrder($od_success, Order $order)
    {
        if ($od_success[$order->order_hash]['remote_status'] == 'Delivered') {
            (new OrderCommonService())->changeStatus($order, OrderStatus::SUCCESS_DELIVERY);
        } elseif ($od_success[$order->order_hash]['remote_status'] == 'Returned') {
            (new OrderCommonService())->changeStatus($order, OrderStatus::RETURNED);
        } else {
            throw new ImportServiceException($order->order_hash . ' remote_status is less than order target. remote_status: "' . $od_success[$order->order_hash]['remote_status'] . '".');
        }
    }
}