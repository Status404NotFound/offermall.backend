<?php

namespace crm\services\delivery;

use Yii;
use common\helpers\FishHelper;
use common\models\delivery\DeliveryApi;
use common\models\delivery\OrderDelivery;
use common\models\delivery\UserDeliveryApi;
use common\models\order\Order;
use common\models\order\OrderStatus;
use crm\models\delivery\DeliveryStickers;
use common\services\delivery\DeliveryException;
use common\services\order\OrderCommonService;
use common\services\ValidateException;
use yii\base\Exception;
use common\models\Instrument;

class DeliveryService
{
    /**
     * @var DeliveryApiInterface
     */
    private $delivery;

    /**
     * @var OrderCommonService
     */
    private $orderService;

    /**
     * @var DeliveryStickersService
     */
    private $deliveryStickerService;

    /**
     * DeliveryService constructor.
     * @param DeliveryApiInterface $delivery
     */
    public function __construct(DeliveryApiInterface $delivery)
    {
        $this->orderService = new OrderCommonService();
        $this->deliveryStickerService = new DeliveryStickersService();
        $this->delivery = $delivery;
    }

    /**
     * @param Order $order
     * @param $credentials
     * @return bool
     * @throws DeliveryException
     * @throws Exception
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function execute(Order $order, $credentials)
    {
        $transaction = \Yii::$app->db->beginTransaction();

        if ($orderDelivery = OrderDelivery::findOne(['order_id' => $order->order_id]))
            throw new DeliveryException('This Order was already sent. Api name: '
                . $orderDelivery->delivery_api_name
                . '. Tracking no: ' . $orderDelivery->tracking_no
                . '. Shipment no: ' . $orderDelivery->shipment_no
                . '. Remote status: ' . $orderDelivery->remote_status);

        try {
            if ($response = $this->delivery->send($order, $credentials)) {
                $order->setInstrument(Instrument::LMC_WFD_TO_DIP);
                $this->orderService->changeStatus($order, OrderStatus::DELIVERY_IN_PROGRESS, [
                    'declaration' => $response['track_number']
                ]);
            }

            if (!$this->saveOrderDelivery($response, $order))
                throw new DeliveryException("Failed to save delivery data for Order #$order->order_hash");

            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
        return true;
    }

    ///**
    // * @param $orders
    // * @param $credentials
    // *
    // * @return bool
    // * @throws DeliveryException
    // * @throws Exception
    // * @throws \yii\db\Exception
    // */
    //public function executeAll($orders, $credentials)
    //{
    //    $already_sent = [];
    //    foreach ($orders as $order) {
    //        if ($orderDelivery = OrderDelivery::findOne(['order_id' => $order->order_id])) {
    //
    //            $already_sent[] = 'This Order was already sent. Api name: '
    //              . $orderDelivery->delivery_api_name
    //              . '. Tracking no: ' . $orderDelivery->tracking_no
    //              . '. Shipment no: ' . $orderDelivery->shipment_no
    //              . '. Remote status: ' . $orderDelivery->remote_status
    //              . '. Order hash: ' . $orderDelivery->order_hash;
    //        }
    //    }
    //
    //    if ( !empty($already_sent)) {
    //        throw new DeliveryException(implode('. ', $already_sent));
    //    }
    //
    //    if ($response_log = $this->delivery->send($orders, $credentials)) {
    //
    //        $failed_orders = [];
    //        foreach ($response_log as $order_hash => $order_log) {
    //
    //            if ($order_hash != 'log') {
    //                $transaction = \Yii::$app->db->beginTransaction();
    //
    //                $order = Order::findOne(['order_hash' => $order_hash]);
    //                $this->orderService->changeStatus($order, OrderStatus::DELIVERY_IN_PROGRESS, [
    //                    'declaration' => $order_log['track_number']
    //                ]);
    //
    //                if ( !$this->saveOrderDelivery($order_log, $order)) {
    //                    $failed_orders[] = "Failed to save delivery data for Order #$order_hash";
    //                    $transaction->rollBack();
    //                } else {
    //                    $transaction->commit();
    //                }
    //            } else {
    //                if ( !empty($order_log['failed'])) {
    //                    $failed_orders[] = $order_log['failed'];
    //                }
    //            }
    //        }
    //    }
    //
    //    if ( !empty($failed_orders)) {
    //        throw new DeliveryException(implode('. ', $failed_orders));
    //    }
    //
    //    return true;
    //}

    /**
     * @param $response
     * @param Order $order
     * @return bool
     * @throws DeliveryException
     * @throws ValidateException
     * @throws \Throwable
     */
    private function saveOrderDelivery($response, Order $order)
    {
        $deliveryApi = DeliveryApi::find()->where([
            'api_name' => $this->delivery->getClassName()
        ])->one();

        $userApi = UserDeliveryApi::find()->where([
            'delivery_api_id' => $deliveryApi->getPrimaryKey(),
            'country_id' => $order->customer->country_id
        ])->one();

        if ($orderDelivery = OrderDelivery::findOne(['order_id' => $order->order_id]))
            throw new DeliveryException('This Order was already sended. Api name: '
                . $orderDelivery->delivery_api_name
                . '. Tracking no: ' . $orderDelivery->tracking_no
                . '. Shipment no: ' . $orderDelivery->shipment_no
                . '. Remote status: ' . $orderDelivery->remote_status);

        $orderDelivery = new OrderDelivery();
        $orderDelivery->setAttributes([
            'order_id' => $order->order_id,
            'order_hash' => $order->order_hash,
            'offer_id' => $order->offer_id,
            'sent_by' => Yii::$app->user->getId(),
            'delivery_api_id' => $deliveryApi->getPrimaryKey(),
            'delivery_api_name' => DeliveryApi::getNameById($deliveryApi->getPrimaryKey()),
            'user_api_id' => $userApi->getPrimaryKey(),
            'tracking_no' => $response['track_number'] ?? null,
            'shipment_no' => $response['shipment_no'] ?? null,
            'remote_status' => $response['remote_status'] ?? null,
            'shipment_data' => $response['shipment_data'] ?? null,
            'currency_id' => Order::getOrderCurrencyId($order->order_id),
        ]);

        try {
//            $color = substr(str_shuffle('AABBCCDDEEFF00112233445566778899AABBCCDDEEFF00112233445566778899AABBCCDDEEFF00112233445566778899'), 0, 6);
            $sticker = DeliveryStickers::findOne(['sticker_name' => $userApi->api_name]);
            $this->deliveryStickerService->saveOrderStickers($order->order_id, [$sticker->sticker_id]);
        } catch (\Exception $e) {

        }

//        FishHelper::debug($orderDelivery->validate());
        if (!$orderDelivery->save()) {
//            FishHelper::debug($orderDelivery->errors, 0, 1);
            throw new ValidateException($orderDelivery->errors);
        }
        return true;
    }
}
