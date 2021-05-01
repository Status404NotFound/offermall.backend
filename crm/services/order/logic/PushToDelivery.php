<?php

namespace crm\services\order\logic;

use common\helpers\FishHelper;
use common\models\delivery\DeliveryApi;
use common\models\delivery\OrderDelivery;
use common\models\delivery\UserDeliveryApi;
use common\models\order\Order;
use common\models\order\OrderStatus;
use common\services\delivery\DeliveryException;
use common\services\order\OrderCommonService;
use common\services\ValidateException;
use crm\services\delivery\DeliveryApiInterface;
use yii\base\Exception;
use yii\base\InvalidParamException;
use Yii;

class PushToDelivery
{
    private $order;
    private $deliveryApi;

    /**
     * PushToDelivery constructor.
     * @param Order $order
     * @param DeliveryApiInterface $deliveryApi
     */
    public function __construct(Order $order, DeliveryApiInterface $deliveryApi)
    {
        $this->order = $order;
        $this->deliveryApi = $deliveryApi;
    }

    public function execute($credentials)
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if ($response = $this->deliveryApi->send($this->order, $credentials)) {
                (new OrderCommonService())->changeStatus($this->order, OrderStatus::DELIVERY_IN_PROGRESS, [
                    'declaration' => $response['track_number']
                ]);
                $response['credentials'] = $credentials;
                if (!$this->saveOrderDelivery($response))
                    throw new Exception("Failed to save OrderDeliveryData. order_hash = " . $this->order->order_hash);
            }
            $transaction->commit();
        } catch (InvalidParamException $e) {
            $transaction->rollBack();
            throw $e;
        } catch (Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
        return true;
    }

    private function saveOrderDelivery($response)
    {
        $deliveryApi = DeliveryApi::find()->where([
            'api_name' => $this->deliveryApi->getClassName(),
        ])->one();
        $userApi = UserDeliveryApi::find()->where([
            'delivery_api_id' => $deliveryApi->getPrimaryKey(),
            'advert_id' => Yii::$app->user->getId(),
            'country_id' => $this->order->targetAdvert->targetAdvertGroup->advertOfferTarget->geo_id,
        ])->one();

        if ($orderDelivery = OrderDelivery::findOne(['order_id' => $this->order->order_id]))
            throw new DeliveryException('This Order was already sended. Api name: '
                . $orderDelivery->delivery_api_name
                . '. Tracking no: ' . $orderDelivery->tracking_no
                . '. Shipment no: ' . $orderDelivery->shipment_no
                . '. Remote status: ' . $orderDelivery->remote_status);

        $orderDelivery = new OrderDelivery();
        $orderDelivery->setAttributes([
            'order_id' => $this->order->order_id,
            'order_hash' => $this->order->order_hash,
            'offer_id' => $this->order->offer_id,
            'sent_by' => Yii::$app->user->getId(),
            'delivery_api_id' => $deliveryApi->getPrimaryKey(),
            'delivery_api_name' => DeliveryApi::getNameById($deliveryApi->getPrimaryKey()),
            'user_api_id' => $userApi->getPrimaryKey(),
            'tracking_no' => isset($response['track_number']) ? $response['track_number'] : null,
            'shipment_no' => isset($response['shipment_no']) ? $response['shipment_no'] : null,
            'remote_status' => isset($response['remote_status']) ? $response['remote_status'] : null,
            'shipment_data' => isset($response['shipment_data']) ? $response['shipment_data'] : null,
            'currency_id' => Order::getOrderCurrencyId($this->order->order_id),
        ]);
        if (!$orderDelivery->save()) {
//            FishHelper::debug($orderDelivery->errors, 0, 1);
            throw new ValidateException($orderDelivery->errors);
        }
        return true;
    }
}