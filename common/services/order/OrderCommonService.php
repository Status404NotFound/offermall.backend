<?php

namespace common\services\order;

use common\helpers\FishHelper;
use common\models\log\orderInfo\OrderInfoInstrument;
use common\models\log\orderSku\OrderSkuInstrument;
use common\models\order\OrderSku;
use common\models\order\OrderStatus;
use common\models\order\Order;
use common\services\order\logic\commission\CommissionFactory;
use common\services\order\logic\order_sku\SaveOrderSku;
use common\services\order\logic\status\ChangeStatusException;
use common\services\order\logic\status\ChangeStatusFactory;
use common\services\order\logic\status\StatusNotFoundException;
use common\services\ValidateException;
use common\services\webmaster\postback\PostbackService;
use webmaster\models\finance\Finance;
use webmaster\modules\api\partners\PartnerCRM;
use Yii;
use yii\base\Exception;

/**
 * Class OrderCommonService
 * @package common\services\order
 */
class OrderCommonService
{
    private $errors = [];

    public function changeStatus(Order $order, $order_status, $params = [])
    {
        $tx = Yii::$app->db->beginTransaction();
        try {
            if (!OrderStatus::attributeLabels($order_status))
                throw new StatusNotFoundException('Order Status exists');
            $changeStatus = ChangeStatusFactory::create($order_status);

            if ($changeStatus->init($order, $params) !== true)
            {
                throw new ChangeStatusException('Failed to set Order #' . $order->order_hash . ' status ' . OrderStatus::attributeLabels($order_status));
            } else {
                $this->savePaymentStatus();
            }
            $tx->commit();
        } catch (ValidateException $e) {
            $tx->rollBack();
            throw $e;
        } catch (Exception $e) {
            $tx->rollBack();
            throw $e;
        }
    }

    private function savePaymentStatus(){
        $finances = Finance::find()->where(['payment_status' => 0])->orWhere(['payment_status' => 1])->all();
        foreach ($finances as $finance){
            if(Finance::getHoldPaymentOrders($finance->order_id) !== null){
                $finance->order_status = $finance->target_status;
                $finance->payment_status = 1;
                $finance->hold_time = date("Y-m-d H:i:s");
                $finance->save();
            }
            if(Finance::getRejectedOrders($finance->order_id) !== null){
                $finance->order_status = $finance->target_status;
                $finance->payment_status = 0;
                $finance->save();
            }
        }
    }

    public function saveOrderSku(Order $order, $order_sku, $instrument)
    {
        // Временно опускаем проверку $order->canBeChanged(), пока не будут созданы адекватные цели
//        if (!$order->canBeChanged()) return false;
        $command = new SaveOrderSku($order, $order_sku, $instrument);
        if ($command->execute() !== true) {
            $this->errors['SaveOrderSku'] = $command->getErrors();
            return false;
        }
        $order->total_amount = OrderSku::getOrderTotalAmount($order->order_id);
        $orderCommission = CommissionFactory::create($order, 'delivery');
        $order->total_cost = $orderCommission->getCommission();
        $order->usd_total_cost = $orderCommission->getUsdCommission();
        if (!$order->save()) throw new OrderException('Failed save Order');
        return true;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function saveComment(Order $order, $comment)
    {
        $order->comment = $this->getBaseComment($order) . ' ' . $comment . '.';
        if (!$order->save()) {
            FishHelper::debug($order->errors, 0, 0);
            //        throw new ValidateException($order->errors);
        }
        return true;
    }

    private function getBaseComment($order)
    {
        return '<span style="color: #BDBDBD">' . date('Y-m-d H:i:s') . ' | ' . OrderInfoInstrument::instruments($order->instrument) . ' | ' . $_SERVER['REMOTE_ADDR'] . ': ' . '</span>' . '<strong>' .PartnerCRM::getPartnerNameByAdvertId($order->targetAdvert->advert_id). '</strong>' . ' says Order ID: '.$order->order_id;
    }

}