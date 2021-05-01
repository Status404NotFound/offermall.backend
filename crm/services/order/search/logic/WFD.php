<?php

namespace crm\services\order\search\logic;

use common\models\offer\targets\advert\sku\TargetAdvertSku;
use common\models\order\OrderSku;
use common\models\order\OrderStatus;
use common\services\webmaster\ArrayHelper;
use crm\services\order\search\AbstractOrderSearch;

/**
 * Class WFD
 * @package crm\services\order\search\logic
 */
class WFD extends AbstractOrderSearch
{
    /**
     * @param null $filters
     * @param null $pagination
     * @param null $sortOrder
     * @param null $sorField
     * @return array
     * @throws \common\components\joinMap\JoinMapException
     * @throws \common\services\offer\exceptions\AdvertServiceException
     * @throws \yii\db\Exception
     */
    public function getOrders($filters = null, $pagination = null, $sortOrder = null, $sortField = null): array
    {
        $basic_orders_query = $this->orderQuery($filters, $pagination)
            ->andWhere(['order.order_status' => OrderStatus::WAITING_DELIVERY]);

        if (isset($sortField)) {
            $basic_orders_query->orderBy(["order.$sortField" => $sortOrder]);
        } else {
            $basic_orders_query->orderBy(['order.delivery_date' => SORT_ASC]);
        }

        $basic_orders = $basic_orders_query->all();
    
        $orders_hashes = ArrayHelper::map($basic_orders, 'order_hash', 'order_hash');
        $orders_query = $this->orderViewQuery($orders_hashes)
            ->from('order_view, customer_view')
            ->andWhere('customer_view.customer_id = order_view.customer_id')
            ->andWhere(['order_view.order_status' => OrderStatus::WAITING_DELIVERY]);

        if (isset($sortField)) {
            $orders_query->orderBy([$sortField => $sortOrder]);
        } else {
            $orders_query->orderBy(['order_view.delivery_date' => SORT_ASC]);
        }

        $orders = $orders_query
            ->asArray()
            ->all();

        $mapped_array = ArrayHelper::map($orders, 'order_id', 'phone');
        $count_history = $this->countHistory($mapped_array);
        $stickers = $this->getDeliveryStickers($mapped_array);

        foreach ($orders as &$order) {
            $order['history'] = $count_history[$order['phone']] ?? 0;
            $order['orderStickers'] = [];
            $order['order_sku'] = OrderSku::findListByOrderId($order['order_id']);
            $order['sku_list'] = TargetAdvertSku::findAdvertSku($order['target_advert_id']);
            
            if (empty($order['sku_list'])) {
                $order['sku_list'] = TargetAdvertSku::findOfferSkuList($order['offer_id']);
            }

            foreach ($stickers as $sticker) {
                if ($sticker['order_id'] == $order['order_id']) {
                    $order['orderStickers'][] = $sticker;
                }
            }
        }

        return [
            'orders' => $orders,
            'count' => [
                'count_all' => $this->countOrdersForPagination($filters, $pagination)->andWhere(['order.order_status' => OrderStatus::WAITING_DELIVERY])->count()
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
            'order_view.offer_id',
            'order_view.offer_name',
            'order_view.target_advert_id',
            'order_view.delivery_date',
            'order_view.created_at',
            'order_view.total_cost',
            'order_view.total_amount',
            'customer_view.customer_id',
            'customer_view.phone',
            'customer_view.name',
            'customer_view.address',
            'customer_view.email',
            'order_view.currency_id',
            'order_view.currency_name',
            'order_view.country_id',
            'order_view.iso',
            'order_view.country_name',
            'customer_view.city_id',
            'customer_view.city_name',
            "CONCAT('stock', ' default') as source_stock",
            //'CV.region_id', 'CV.region_name',
        ];
    }
}