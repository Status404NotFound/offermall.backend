<?php

namespace webmaster\modules\api\partners;
use common\models\order\Order;
use webmaster\models\partners\Partner;
use webmaster\models\partners\PartnerOffers;
use webmaster\models\partners\PartnerOrders;
use Yii;

/**
 * Контекст определяет интерфейс, представляющий интерес для клиентов.
 */
class PartnerCRM //Context Class
{
    /**
     * @var Strategy Контекст хранит ссылку на один из объектов Стратегии.
     * Контекст не знает конкретного класса стратегии. Он должен работать со
     * всеми стратегиями через интерфейс Стратегии.
     */
    private $strategy;

    /**
     * Обычно Контекст принимает стратегию через конструктор, а также
     * предоставляет сеттер для её изменения во время выполнения.
     * @param Strategy $strategy
     */
    public function __construct(Strategy $strategy)
    {
        $this->strategy = $strategy;
    }

    /**
     * Обычно Контекст позволяет заменить объект Стратегии во время выполнения.
     * @param Strategy $strategy
     */
    public function setStrategy(Strategy $strategy)
    {
        $this->strategy = $strategy;
    }

    /**
     * Вместо того, чтобы самостоятельно реализовывать множественные версии
     * алгоритма, Контекст делегирует некоторую работу объекту Стратегии.
     */
    public function sendToPartner($partnerId): void
    {
        $pOrders = PartnerOrders::getPendingOrdersByPartnerId($partnerId);
        $orders = [];
        $filePath = Yii::getAlias('@webmaster');

        file_put_contents($filePath."/test-orders-myLand.txt", $partnerId, FILE_APPEND);
        foreach ($pOrders as $pOrder){
            array_push($orders, Order::find()->where(['order_id' => $pOrder->order_id])->one());
        }
        $this->strategy->send($orders);
    }

    public static function getOfferHash($offer_id)
    {
        $offerObj = PartnerOffers::find()->where(['offer_id' => $offer_id])->one();
        if (isset($offerObj)) {
            return $offerObj->partner_offer_hash;
        }
        return null;
    }

    public static function getPartnerNameByAdvertId($advertId): string
    {
        return Partner::find()->where(['advert_id' => $advertId])->one()->name;
    }

    public function sendOrderById($orderId): void
    {
        $order = Order::find()->where(['order_id' => $orderId])->one();
    }
}