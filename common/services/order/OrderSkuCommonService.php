<?php

namespace common\services\order;

use common\helpers\FishHelper;
use common\models\order\Order;
use common\models\order\OrderSku;

class OrderSkuCommonService
{
    public $errors = [];

    public function getErrors()
    {
        return $this->errors;
    }

    public static function getOrderAllSkuString($order_id)
    {
        $string = '';
        $order_sku = OrderSku::find()
            ->select(['product_sku.sku_name', 'order_sku.sku_id', 'order_sku.amount'])
            ->join('JOIN', 'product_sku', 'product_sku.sku_id = order_sku.sku_id')
            ->where(['order_sku.order_id' => $order_id])
            ->asArray()->all();
        foreach ($order_sku as $sku) {
            $string .= $sku['sku_name'] . ': ' . $sku['amount'] . '; ';
        }
        return $string;
    }
}