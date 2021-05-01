<?php

namespace crm\services\order\search\logic;

use crm\services\order\search\AbstractOrderSearch;
use common\models\order\OrderSku;
use yii\helpers\ArrayHelper;

/**
 * Class Delivery
 * @package crm\services\order\search\logic
 */
class Delivery extends AbstractOrderSearch
{
    /**
     * @param null $filters
     * @param null $pagination
     * @param null $sortOrder
     * @param null $sorField
     * @return array
     * @throws \common\components\joinMap\JoinMapException
     * @throws \yii\db\Exception
     */
    public function getOrders($filters = null, $pagination = null, $sortOrder = null, $sortField = null): array
    {
        $basic_orders_query = $this->orderQuery($filters, $pagination)
            ->andWhere(['IS NOT', 'order.delivery_date', null]);

        if (isset($sortField)) {
            $basic_orders_query->orderBy(["order.$sortField" => $sortOrder]);
        } else {
            $basic_orders_query->orderBy(['order.delivery_date' => SORT_DESC]);
        }

        $basic_orders = $basic_orders_query->all();

        $orders_hashes = ArrayHelper::map($basic_orders, 'order_hash', 'order_hash');
        $orders_query = $this->orderViewQuery($orders_hashes)
            ->from('order_view, customer_view')
            ->andWhere('customer_view.customer_id = order_view.customer_id')
            ->andWhere(['IS NOT', 'order_view.delivery_date', null]);

        if (isset($sortField)) {
            $orders_query->orderBy([$sortField => $sortOrder]);
        } else {
            $orders_query->orderBy(['order_view.delivery_date' => SORT_DESC]);
        }

        $orders = $orders_query
            ->asArray()
            ->all();

        $mapped_array = ArrayHelper::map($orders, 'order_id', 'phone');
        $count_history = $this->countHistory($mapped_array);

        foreach ($orders as &$order) {
            $order['order_sku'] = OrderSku::findListByOrderId($order['order_id']);
            $order['history'] = $count_history[$order['phone']] ?? 0;
        }
    
        return [
            'orders' => $orders,
            'count'  => [
                'count_all' => $this->countOrdersForPagination($filters, $pagination)->andWhere(['IS NOT', '`order`.delivery_date', null])->count()
            ]
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
            'order_view.declaration',
            'order_view.order_status',
            'order_view.offer_id',
            'order_view.offer_name',
            'order_view.delivery_date',
            'order_view.created_at',
            'order_view.total_amount',
            'order_view.customer_id',
            'customer_view.phone',
            'customer_view.name',
            'customer_view.address',
            'customer_view.country_id',
            'order_view.iso',
            'customer_view.country_name',
            'customer_view.city_id',
            'customer_view.city_name',
            'order_view.bitrix_flag',
            'order_view.delivery_api_id',
            'order_view.user_api_id',
            'order_view.delivery_api_name',
            'order_view.sent_by',
            'order_view.tracking_no',
            'order_view.shipment_no',
            'order_view.remote_status',
            'order_view.report_no',
            'order_view.delivery_date_in_fact',
            'order_view.money_in_fact'
        ];
    }
}