<?php

namespace crm\services\order\search\logic;

use common\models\order\Order;
use common\models\order\OrderSku;
use common\models\order\OrderStatus;
use common\models\order\StatusReason;
use crm\services\order\search\AbstractOrderSearch;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * Class Group
 * @package crm\services\order\search\logic
 */
class Group extends AbstractOrderSearch
{
    /**
     * @param null $filters
     * @param null $pagination
     * @param null $sortOrder
     * @param null $sortField
     * @return array
     * @throws \common\components\joinMap\JoinMapException
     */
    public function getOrders($filters = null, $pagination = null, $sortOrder = null, $sortField = null): array
    {
        $basic_orders = $this->orderQuery($filters, $pagination)->all();
        $orders_hashes = ArrayHelper::map($basic_orders, 'order_hash', 'order_hash');

        $orders_query = $this->orderViewQuery($orders_hashes)
            ->from('order_view, customer_view')
            ->andWhere('customer_view.customer_id = order_view.customer_id');

        if (isset($sortField)) {
            $orders_query->orderBy([$sortField => $sortOrder]);
        } else {
            $orders_query->orderBy(['order_view.created_at' => SORT_DESC]);
        }

        $orders = $orders_query
            ->asArray()
            ->all();

        $orders_without_pagination = $this->groupSearchOrderQuery()->andWhere(['O.order_hash' => $filters['order_hash']['value']])->asArray()->all();
        $hashes = array_unique($filters['order_hash']['value']);
        $found = [];
        foreach ($orders_without_pagination as $ord) {
            foreach ($hashes as $key => $hash) {
                if ($hash == $ord['order_hash']) {
                    $found[] = $hash;
                    unset($hashes[$key]);
                }
            }
        }

        $order_with_filter = $this->groupSearchOrderFilters($this->groupSearchOrderQuery(), $filters)->asArray()->all();

        $count_statuses = array_fill_keys(array_keys(OrderStatus::attributeLabels()), 0);
        $count_countries = [];
        $count_offers = [];
        foreach ($order_with_filter as $order) {
            $count_statuses[$order['order_status']]++;

            if (isset($count_countries[$order['country_id']])) {
                $count_countries[$order['country_id']]++;
            } else {
                $count_countries[$order['country_id']] = 1;
            }

            if (isset($count_offers[$order['offer_id']])) {
                $count_offers[$order['offer_id']]++;
            } else {
                $count_offers[$order['offer_id']] = 1;
            }

            $full_id_list[] = $order['order_id'];
        }

        foreach ($orders as &$order) {
            $order['order_sku'] = OrderSku::findListByOrderId($order['order_id']);

            if (OrderStatus::statusNeedReason($order['order_status']) === true) {
                $order['status_reason'] = StatusReason::getReason($order['order_status'], $order['status_reason']);
            }
        }

        return [
            'orders' => $orders,
            'not_found_orders' => array_values($hashes),
            'full_id_list' => empty($full_id_list) ? [] : $full_id_list,
            'count' => [
                'count_all' => $this->countOrdersForPagination($filters)->count(),
                'count_found' => \count($found),
                'count_not_found' => \count($hashes),
                'count_statuses' => $count_statuses,
                'count_offers' => $count_offers,
                'count_countries' => $count_countries,
            ],
        ];
    }

    /**
     * @return array
     */
    protected function selectFields(): array
    {
        return [
            'order_view.order_id',
            'order_view.order_hash',
            'order_view.order_status',
            'order_view.status_reason',
            'order_view.declaration',
            'order_view.delivery_date',
            'order_view.offer_id',
            'order_view.offer_name',
            'order_view.total_amount',
            'order_view.total_cost',
            'customer_view.customer_id',
            'customer_view.name',
            'customer_view.phone',
            'order_view.currency_id',
            'order_view.currency_name',
            'order_view.country_id',
            'order_view.country_name',
            'order_view.iso',
            'customer_view.address',
            'customer_view.region_id',
            'customer_view.region_name',
            'customer_view.city_id',
            'customer_view.city_name',
            'order_view.bitrix_flag',
        ];
    }

    /**
     * @return ActiveQuery
     */
    private function groupSearchOrderQuery(): ActiveQuery
    {
        $query = Order::find()
            ->select(['O.order_id', 'O.order_hash', 'O.order_status', 'OF.offer_id', 'AOT.geo_id as country_id'])
            ->from('order O')
            ->leftJoin('order_data OD', 'OD.order_id = O.order_id')
            ->leftJoin('offer OF', 'OF.offer_id = O.offer_id')
            ->leftJoin('target_advert TA', 'TA.target_advert_id = O.target_advert_id')
            ->leftJoin('target_advert_group TAG', 'TAG.target_advert_group_id = TA.target_advert_group_id')
            ->leftJoin('advert_offer_target AOT', 'AOT.advert_offer_target_id = TAG.advert_offer_target_id')
            ->where(['O.deleted' => 0]);

        if (!empty($owner_id = \Yii::$app->user->identity->getOwnerId())) {
            $query->andWhere(['OD.owner_id' => $owner_id]);
        }

        return $query;
    }

    /**
     * @param ActiveQuery $query
     * @param array $filters
     * @return ActiveQuery
     */
    private function groupSearchOrderFilters(ActiveQuery $query, array $filters = []): ActiveQuery
    {
        if (isset($filters['order_hash']['value'])) {
            $query->andWhere(['O.order_hash' => $filters['order_hash']['value']]);
        }
        if (isset($filters['offer_id']['value'])) {
            $query->andWhere(['O.offer_id' => $filters['offer_id']['value']]);
        }
        if (isset($filters['total_cost']['value'])) {
            $query->andWhere(['O.total_cost' => $filters['total_cost']['value']]);
        }
        if (isset($filters['order_status']['value'])) {
            $query->andWhere(['O.order_status' => $filters['order_status']['value']]);
        }
        if (isset($filters['country_id']['value'])) {
            $query->andWhere(['AOT.geo_id' => $filters['country_id']['value']]);
        }

        return $query;
    }
}