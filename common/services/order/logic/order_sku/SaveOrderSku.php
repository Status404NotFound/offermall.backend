<?php

namespace common\services\order\logic\order_sku;

use common\helpers\FishHelper;
use common\models\Instrument;
use common\models\log\orderSku\OrderSkuInstrument;
use common\models\order\Order;
use common\models\order\OrderSku;
use common\services\log\LogServiceException;
use common\services\log\LogSrv;
use common\services\stock\StockService;
use common\services\stock\StockServiceException;
use common\services\ValidateException;
use crm\services\log\LogFactory;
use Yii;
use yii\base\Exception;
use yii\base\InvalidParamException;

class SaveOrderSku
{
    public $errors = [];
    public $order;
    public $order_sku;
    public $instrument;

    public function __construct(Order $order, $order_sku = [], $instrument)
    {
        $this->order = $order;
        $this->order_sku = $order_sku;
        $this->instrument = $instrument;
    }

    public function execute()
    {
        $result = true;
        $orderSku = OrderSku::findOrderSkuByOrderId($this->order->order_id);
        $stockService = new StockService();
        $rests = [];

        $tx = Yii::$app->db->beginTransaction();
        try {
            foreach ($this->order_sku as $sku) {
                $order_sku_id = $this->saveOrderSku($sku['sku_id'], $sku['amount']);
                /** BEGIN Replace StockSku to Order */
                if (isset($orderSku[$order_sku_id])) {
                    $sku_rest = $orderSku[$order_sku_id]->amount - $sku['amount'];
                } else {
                    $sku_rest = -$sku['amount'];
                }
                if ($sku_rest < 0) {
                    $stockService->moveSku($this->order->targetAdvert->stock_id, null, $sku['sku_id'], abs($sku_rest));
                } elseif ($sku_rest > 0) {
                    /** Put OrderSku rests into $rests array */
                    $rests[$order_sku_id] = [
                        'sku_id' => $sku['sku_id'],
                        'amount' => abs($sku_rest),
                        'is_new' => false
                    ];
                }
                /** END Replace StockSku to Order */
                unset($orderSku[$order_sku_id]);
            }
            $tx->commit();
        } catch (InvalidParamException $e) {
            $tx->rollBack();
            $result = false;
            throw $e;
        } catch (StockServiceException $e) {
            $tx->rollBack();
            $result = false;
            throw $e;
        } catch (ValidateException $e) {
            $tx->rollBack();
            $result = false;
            throw $e;
        } catch (Exception $e) {
            $tx->rollBack();
            $result = false;
            throw $e;
        }
        if ($result === true) {
            foreach ($orderSku as $sku) {
                /** @var OrderSku $sku */
                $rests[$sku->sku_id]['sku_id'] = $sku->sku_id;
                $rests[$sku->sku_id]['amount'] = $sku->amount;
                $rests[$sku->sku_id]['is_new'] = false;
                $sku->setInstrument($this->instrument);
                if (!$sku->delete()) FishHelper::debug($sku->errors, 0, 1);
            }
            /** Replace OrderSku $rests into TargetAdvert Stock */
            $stockService->addSku($this->order->targetAdvert->stock_id, null, $rests);
        }
        return $result;
    }

    public function saveOrderSku($sku_id, $amount)
    {
        $orderSku = OrderSku::findOne(['order_id' => $this->order->order_id, 'sku_id' => $sku_id]) ?? new OrderSku();
        if ($orderSku->amount != $amount) {
            if ($orderSku->isNewRecord) {
                $orderSku->order_id = $this->order->order_id;
                $orderSku->sku_id = $sku_id;
            }
            $orderSku->amount = $amount;
            $orderSku->setInstrument($this->instrument);
            if (!$orderSku->save()) {
                $this->errors['OrderSku'] = $orderSku->errors;
                return false;
            }
        }
        return $orderSku->order_sku_id;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}