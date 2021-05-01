<?php

namespace crm\services\order\search;

use common\components\joinMap\JoinMap;
use common\models\customer\Customer;
use common\models\customer\CustomerSystem;
use common\models\offer\targets\advert\AdvertOfferTarget;
use common\models\offer\targets\advert\TargetAdvert;
use common\models\offer\targets\advert\TargetAdvertGroup;
use common\models\onlinePayment\OnlinePayment;
use common\models\order\Order;
use common\models\order\OrderData;
use common\models\order\OrderStatus;
use common\models\order\OrderView;
use common\models\SendedToPartner;
use crm\models\delivery\DeliveryStickers;
use crm\models\delivery\OrderStickers;
use Yii;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * Class AbstractOrderSearch
 * @package crm\services\order\search
 */
abstract class AbstractOrderSearch implements OrderSearchInterface
{
    /**
     * @param null $filters
     * @param null $pagination
     * @param int $sortOrder
     * @param int $sortField
     * @return array
     */
    abstract public function getOrders($filters = null, $pagination = null, $sortOrder = null, $sortField = null): array;

    /**
     * @return array
     */
    abstract protected function selectFields(): array;

    /**
     * @param null $filters
     * @return ActiveQuery
     * @throws \common\components\joinMap\JoinMapException
     */
    protected function countOrdersForPagination($filters = null): ActiveQuery
    {
        $query = Order::find()->andWhere(['order.deleted' => 0]);
        $this->joinTablesAndFilterQuery($query, $filters);

        return $query;
    }

    /**
     * @param null $filters
     * @param null $pagination
     * @return ActiveQuery
     * @throws \common\components\joinMap\JoinMapException
     */
    protected function orderQuery($filters = null, $pagination = null): ActiveQuery
    {
        $query = Order::find()
            ->select(['order.order_hash'])
            ->andWhere(['order.deleted' => 0]);
        $this->joinTablesAndFilterQuery($query, $filters, $pagination);

        return $query;
    }

    // Извлекать поля только у этого запроса
    /**
     * @param array $orders_hashes
     * @return ActiveQuery
     */
    protected function orderViewQuery(array $orders_hashes): ActiveQuery
    {
        return OrderView::find()
            ->select($this->selectFields(), new Expression('STRAIGHT_JOIN'))
            ->andWhere(['order_hash' => $orders_hashes]);
    }

    /**
     * @param ActiveQuery $orders
     * @param null $filters
     * @param null $pagination
     * @throws \common\components\joinMap\JoinMapException
     */
    private function joinTablesAndFilterQuery(ActiveQuery $orders, $filters = null, $pagination = null): void
    {
        $joinMap = new JoinMap($orders, [
            [JoinMap::LEFT, TargetAdvert::tableName(), 'target_advert_id', Order::tableName(), 'target_advert_id'],
            [JoinMap::LEFT, TargetAdvertGroup::tableName(), 'target_advert_group_id', TargetAdvert::tableName(), 'target_advert_group_id'],
            [JoinMap::LEFT, AdvertOfferTarget::tableName(), 'advert_offer_target_id', TargetAdvertGroup::tableName(), 'advert_offer_target_id'],
            [JoinMap::LEFT, OrderStickers::tableName(), 'order_id', Order::tableName(), 'order_id'],
            [JoinMap::LEFT, OnlinePayment::tableName(), 'order_id', Order::tableName(), 'order_id'],
            [JoinMap::LEFT, OrderData::tableName(), 'order_id', Order::tableName(), 'order_id'],
            [JoinMap::LEFT, Customer::tableName(), 'customer_id', Order::tableName(), 'customer_id'],
            [JoinMap::LEFT, CustomerSystem::tableName(), 'customer_id', Order::tableName(), 'customer_id'],
        ]);

        if (!empty($owner_id = \Yii::$app->user->identity->getOwnerId())) {
            $joinMap->join($orders, OrderData::tableName());
            $orders->andWhere(['order_data.owner_id' => $owner_id]);
        }
        if (isset($filters['order_hash']['value'])) {
            //$orders->andWhere(['LIKE', 'order.order_hash', $filters['order_hash']['value']]);
            $orders->andWhere(['order.order_hash' => $filters['order_hash']['value']]);
        }
        if (isset($filters['offer_id']['value'])) {
            $orders->andWhere(['order.offer_id' => $filters['offer_id']['value']]);
        }
        if (isset($filters['offer']['value'])) {
            $orders->andWhere(['order.offer_id' => $filters['offer']['value']]);
        }
        if (isset($filters['owner_id']['value'])) {
            if ($filters['owner_id']['value'] == 0) {
                $filters['owner_id']['value'] = null;
            }
            $joinMap->join($orders, TargetAdvert::tableName());
            $joinMap->join($orders, TargetAdvertGroup::tableName());
            $joinMap->join($orders, AdvertOfferTarget::tableName());
            $orders->andWhere(['target_advert.advert_id' => $filters['owner_id']['value']]);
        }
        if (isset($filters['cost']['value'])) {
            $orders->andWhere(['order.total_cost' => $filters['cost']['value']]);
        }
        if (isset($filters['advert_commission']['value'])) {
            $orders->andWhere(['order.advert_commission' => $filters['advert_commission']['value']]);
        }
        if (isset($filters['wm_commission']['value'])) {
            $orders->andWhere(['LIKE', 'order.wm_commission', $filters['wm_commission']['value']]);
        }
        if (isset($filters['total_cost']['value'])) {
            $orders->andWhere(['order.total_cost' => $filters['total_cost']['value']]);
        }
        if (isset($filters['paid_online']['value'])) {
            $orders->andWhere(['order.paid_online' => $filters['paid_online']['value']]);
        }
        if (isset($filters['status_reason']['value'])) {
            $orders->andWhere(['order.status_reason' => $filters['status_reason']['value']]);
        }
        if (isset($filters['payment_status']['value'])) {
            $joinMap->join($orders, OnlinePayment::tableName());
            $orders->andWhere(['online_payment.payment_status' => $filters['payment_status']['value']]);
        }
        if (isset($filters['webmaster']['value'])) {
            $joinMap->join($orders, OrderData::tableName());
            $orders->andWhere(['order_data.wm_id' => $filters['webmaster']['value']]);
        }
        if (isset($filters['order_status']['value'])) {
            $orders->andWhere(['order.order_status' => $filters['order_status']['value']]);
        }
        if (isset($filters['status']['value']) && $filters['status']['value'] != 'missed_delivery') {
            $orders->andWhere(['order.order_status' => $filters['status']['value']]);
        }
        if (isset($filters['status']['value']) && $filters['status']['value'] == 'missed_delivery') {
            $orders->andWhere('order.delivery_date < now()');
            $orders->andWhere(['order.order_status' => OrderStatus::WAITING_DELIVERY]);
        }
        if (isset($filters['status_reason']['value'])) {
            $orders->andWhere(['order.status_reason' => $filters['status_reason']['value']]);
        }
        if (isset($filters['phone']['value'])) {
            $joinMap->join($orders, Customer::tableName());
            $orders->andWhere(['LIKE', 'customer.phone', $filters['phone']['value']]);
        }
        if (isset($filters['name']['value'])) {
            $joinMap->join($orders, Customer::tableName());
            $orders->andWhere(['LIKE', 'customer.name', $filters['name']['value']]);
        }
        if (isset($filters['address']['value'])) {
            $joinMap->join($orders, Customer::tableName());
            $orders->andWhere(['LIKE', 'customer.address', $filters['address']['value']]);
        }
        if (isset($filters['email']['value'])) {
            $joinMap->join($orders, Customer::tableName());
            $orders->andWhere(['LIKE', 'customer.email', $filters['email']['value']]);
        }
        if (isset($filters['region']['value'])) {
            $joinMap->join($orders, Customer::tableName());
            $orders->andWhere(['LIKE', 'customer.region_name', $filters['region']['value']]);
        }
        //if (isset($filters['country_id']['value'])) {
        //    $this->joinCustomer($orders);
        //    $orders->andWhere(['customer.country_id' => $filters['country_id']['value']]);
        //}
        if (isset($filters['total_amount']['value'])) {
            $orders->andWhere(['order.total_amount' => $filters['total_amount']['value']]);
        }
        if (isset($filters['owner_id'])) {
            $joinMap->join($orders, TargetAdvert::tableName());
            $orders->andWhere(['target_advert.advert_id' => $filters['owner_id']['value']]);
        }
        if (isset($filters['advert_target_id']['value'])) {
            $joinMap->join($orders, TargetAdvert::tableName());
            $joinMap->join($orders, TargetAdvertGroup::tableName());
            $joinMap->join($orders, AdvertOfferTarget::tableName());
            $orders->andWhere(['advert_offer_target.advert_offer_target_status' => $filters['advert_target_id']['value']]);
        }
        if (isset($filters['customer_country_id'])) {
            $joinMap->join($orders, Customer::tableName());
            $orders->andWhere(['customer.country_id' => $filters['customer_country_id']['value']]);
        }
        if (isset($filters['country_id'])) {
            $joinMap->join($orders, TargetAdvert::tableName());
            $joinMap->join($orders, TargetAdvertGroup::tableName());
            $joinMap->join($orders, AdvertOfferTarget::tableName());
            $orders->andWhere(['advert_offer_target.geo_id' => $filters['country_id']['value']]);
        }
        if (isset($filters['browser'])) {
            $joinMap->join($orders, CustomerSystem::tableName());
            $orders->andWhere(['LIKE', 'customer_system.browser', $filters['browser']['value']]);
        }
        if (isset($filters['cookie'])) {
            $joinMap->join($orders, CustomerSystem::tableName());
            $orders->andWhere(['LIKE', 'customer_system.cookie', $filters['cookie']['value']]);
        }
        if (isset($filters['ip'])) {
            $joinMap->join($orders, CustomerSystem::tableName());
            $orders->andWhere(['LIKE', 'customer_system.ip', $filters['ip']['value']]);
        }
        if (isset($filters['os'])) {
            $joinMap->join($orders, CustomerSystem::tableName());
            $orders->andWhere(['LIKE', 'customer_system.os', $filters['os']['value']]);
        }
        //if (isset($filters['referrer'])) {
        //    $orders->andWhere(['LIKE', 'customer_view.referrer', $filters['referrer']['value']]);
        //}
        if (isset($filters['sid'])) {
            $joinMap->join($orders, CustomerSystem::tableName());
            $orders->andWhere(['LIKE', 'customer_system.sid', $filters['sid']['value']]);
        }
        if (isset($filters['view_hash'])) {
            $joinMap->join($orders, CustomerSystem::tableName());
            $orders->andWhere(['LIKE', 'customer_system.view_hash', $filters['view_hash']['value']]);
        }
        if (isset($filters['active'])) {
            $joinMap->join($orders, TargetAdvert::tableName());
            $orders->andWhere(['target_advert.active' => $filters['active']['value']]);
        }
        if (isset($filters['stickers']['value'])) {
            $joinMap->join($orders, OrderStickers::tableName());
            $orders->andWhere(['order_stickers.sticker_id' => $filters['stickers']['value']]);
            $orders->andWhere('order_stickers.order_id = `order`.order_id');
        }
        if (isset($filters['sticker_id'])) {
            $joinMap->join($orders, OrderStickers::tableName());
            $orders->andWhere(['order_stickers.sticker_id' => $filters['sticker_id']['value']]);
        }
        if (isset($filters['tab_name']['value'])) {
            if ($filters['tab_name']['value'] == 'present') {
                $orders->andWhere(['>=', 'DATE(`order`.delivery_date)', date('Y-m-d')]);
            } elseif ($filters['tab_name']['value'] == 'history') {
                $orders->andWhere(['<', 'DATE(`order`.delivery_date)', date('Y-m-d')]);
            }
        }
        if (isset($filters['created_at'])) {
            $start = new \DateTime($filters['created_at']['start']);
            $start->setTime(0, 0, 0);
            $start_date = $start->format('Y-m-d H:i:s');

            $end = new \DateTime($filters['created_at']['end']);
            $end->setTime(23, 59, 59);
            $end_date = $end->format('Y-m-d H:i:s');

            $orders->andWhere(['>', 'order.created_at', $start_date]);
            $orders->andWhere(['<', 'order.created_at', $end_date]);
        }
        if ($pagination !== null) {
            $orders->offset($pagination['first_row'])->limit($pagination['rows']);
        }
    }

    /**
     * @param array $order_id
     * @return array|\common\models\offer\targets\advert\sku\TargetAdvertSku[]|OrderStickers[]|\yii\db\ActiveRecord[]
     */
    protected function getDeliveryStickers(array $order_id)
    {
        return OrderStickers::find()
            ->select(['OS.order_id', 'OS.sticker_id', 'DS.sticker_id', 'DS.sticker_name', 'DS.sticker_color'])
            ->from('order_stickers OS')
            ->leftJoin('delivery_stickers DS', 'DS.sticker_id = OS.sticker_id')
            ->where(['OS.order_id' => array_keys($order_id)])
            ->andWhere(['DS.is_active' => DeliveryStickers::IS_ACTIVE])
            ->asArray()
            ->all();
    }

    /**
     * @param array $orders
     * @return array
     * @throws \yii\db\Exception
     */
    protected function countHistory(array $orders): array
    {
        if (empty($orders)) {
            return [];
        }

        $has_duplicate = array_unique(array_diff_assoc($orders, array_unique($orders)));
        if ($has_duplicate) {
            foreach ($orders as $order_id => $phone) {
                if (isset($has_duplicate[$order_id])) {
                    unset($orders[$order_id]);
                }
            }
        }

        $sql = 'SELECT
                  SUM(cnt) as cnt,
                  phone
                FROM (
                       (SELECT
                          COUNT(*) as cnt,
                          phone
                        FROM customer
                          LEFT JOIN `order` ON `order`.customer_id = customer.customer_id
                        WHERE (`customer`.`phone` IN ("' . implode('", "', $orders) . '") AND
                               (NOT (`order`.`order_id` IN (' . implode(', ', array_keys($orders)) . '))))
                        GROUP BY `phone`)
                       UNION ALL
                       (SELECT
                          COUNT(*) as cnt,
                          phone
                        FROM old_history
                        WHERE `phone` IN ("' . implode('", "', $orders) . '")
                        GROUP BY `phone`)
                     ) `customer`
                WHERE `phone` IS NOT NULL
                GROUP BY `phone`';

        $command = Yii::$app->db->createCommand($sql);
        $result = $command->queryAll();

        return ArrayHelper::map($result, 'phone', 'cnt');
    }
}
