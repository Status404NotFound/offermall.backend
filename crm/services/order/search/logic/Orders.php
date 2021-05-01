<?php

namespace crm\services\order\search\logic;

use common\models\offer\targets\advert\TargetAdvertGroupRules;
use common\models\offer\targets\wm\TargetWmGroupRules;
use common\models\onlinePayment\OnlinePayment;
use common\models\order\Order;
use common\models\order\OrderSku;
use common\models\order\OrderStatus;
use common\models\order\StatusReason;
use common\models\SendedToPartner;
use common\services\webmaster\ArrayHelper;
use crm\models\delivery\OrderStickers;
use crm\services\order\search\AbstractOrderSearch;
use yii\db\Exception;

/**
 * Class Orders
 * @package crm\services\order\search\logic
 */
class Orders extends AbstractOrderSearch
{
    /**
     * @param null $filters
     * @param null $pagination
     * @param null $sortOrder
     * @param null $sortField
     * @return array
     * @throws \common\components\joinMap\JoinMapException
     * @throws \yii\db\Exception
     */
    public function getOrders($filters = null, $pagination = null, $sortOrder = null, $sortField = null): array
    {
        $count_orders_by_status_query = Order::find()
            ->select('SUM(`paid_online` LIKE 1) AS `paid_online`,
                      SUM(`order_status` LIKE ' . OrderStatus::PENDING . ') AS `pending`,
                      SUM(`order_status` LIKE ' . OrderStatus::NOT_VALID . ') AS `not_valid`,
                      SUM(`order_status` LIKE ' . OrderStatus::BACK_TO_PENDING . ') AS `back_to_pending`,
                      SUM(`order_status` LIKE ' . OrderStatus::REJECTED . ' AND `status_reason` = 20) AS `wrong_geo`,
                      SUM(`order_status` LIKE ' . OrderStatus::WAITING_DELIVERY . ' AND `delivery_date` < now()) AS `missed_delivery`')
            ->andWhere(['order.deleted' => 0]);

        if (!isset($filters['owner_id']['value']) && !empty($owner_id = \Yii::$app->user->identity->getOwnerId())) {
            $filters['owner_id']['value'] = $owner_id;

            $count_orders_by_status_query->leftJoin('target_advert', 'order.target_advert_id = target_advert.target_advert_id');
            $count_orders_by_status_query->andWhere(['target_advert.advert_id' => $owner_id]);
        }

        $basic_orders_query = $this->orderQuery($filters, $pagination);

        if (isset($sortField)) {
            $basic_orders_query->orderBy(["order.$sortField" => $sortOrder]);
        } else {
            $basic_orders_query->orderBy(['`order`.created_at' => SORT_DESC]);
        }

        $basic_orders = $basic_orders_query->all();

        $orders_hashes = ArrayHelper::map($basic_orders, 'order_hash', 'order_hash');

        $orders_query = $this->orderViewQuery($orders_hashes)
            ->join('JOIN', 'customer_view', 'customer_view.customer_id = order_view.customer_id')
            ->join('LEFT JOIN', 'countries', 'countries.id = customer_view.country_id');

        if (isset($sortField)) {
            $orders_query->orderBy([$sortField => $sortOrder]);
        } else {
            $orders_query->orderBy(['order_view.created_at' => SORT_DESC]);
        }

        $orders = $orders_query
            ->asArray()
            ->all();

        $count_orders_by_status = $count_orders_by_status_query->asArray()->one();
        $result = [
            'orders' => $orders,
            'count' => [
                'count_all' => $this->countOrdersForPagination($filters)->count(),
                'count_paid_online' => $count_orders_by_status['paid_online'],
                'count_pending' => $count_orders_by_status['pending'],
                'count_not_valid' => $count_orders_by_status['not_valid'],
                'count_back_to_pending' => $count_orders_by_status['back_to_pending'],
                'count_wrong_geo' => $count_orders_by_status['wrong_geo'],
                'count_missed_delivery' => $count_orders_by_status['missed_delivery'],
            ],
        ];

        $orders_count_history = ArrayHelper::map($result['orders'], 'order_id', function ($order) {
            return str_replace('9710', '971', $order['phone']);
        });
        $count_history = $this->countHistory($orders_count_history);

        foreach ($result['orders'] as &$order) {
            $phone_formatted = str_replace('9710', '971', $order['phone']);
            $order['history'] = $count_history[$phone_formatted] ?? 0;
            $order['orderStickers'] = OrderStickers::findOrderStickersByOrderId($order['order_id']) ?? [];

            if (OrderStatus::statusNeedReason($order['order_status']) === true) {
                $order['reason'] = StatusReason::getReason($order['order_status'], $order['status_reason']);
            }
            $order['order_sku'] = OrderSku::findListByOrderId($order['order_id']);
            $order['wm_commission'] = $order['target_wm_id'] !== null ? $this->getWmCommission($order['order_id']) : false;
            $order['advert_commission'] = $order['target_advert_id'] !== null ? $this->getAdvertCommission($order['order_id']) : false;
            $order['payment'] = $order['paid_online'] == true ? OnlinePayment::OrderPayment($order['order_id']) : null;
        }

        return $result;
    }

    /**
     * @param $order_id
     * @return array
     */
    private function getAdvertCommission($order_id): array
    {
        $order = Order::findOne($order_id);
        $rules = [
            'base_commission' => $order->targetAdvert->targetAdvertGroup->base_commission ?? null,
            'exceeded_commission' => $order->targetAdvert->targetAdvertGroup->exceeded_commission ?? null,
            'use_commission_rules' => $order->targetAdvert->targetAdvertGroup->use_commission_rules,
            'rules_by_pcs' => TargetAdvertGroupRules::getAdvertGroupRulesByGroupId($order->targetAdvert->target_advert_group_id)
        ];

        return $rules;
    }

    /**
     * @param $order_id
     * @return array
     */
    private function getWmCommission($order_id): array
    {
        $order = Order::findOne($order_id);
        $rules = [
            'base_commission' => $order->targetWm->targetWmGroup->base_commission ?? null,
            'exceeded_commission' => $order->targetWm->targetWmGroup->exceeded_commission ?? null,
            'use_commission_rules' => $order->targetWm->targetWmGroup->use_commission_rules,
            'rules_by_pcs' => TargetWmGroupRules::getWmGroupRulesByGroupId($order->targetWm->target_wm_group_id),
        ];

        return $rules;
    }

    /**
     * @return array
     */
    protected function selectFields(): array
    {
        return [
            'order_view.order_id',
            'order_view.target_wm_id',
            'order_view.target_advert_id',
            'order_view.order_hash',
            'order_view.offer_id',
            'order_view.offer_name',
            'order_view.created_at',
            'order_view.delivery_date',
            'order_view.declaration',
            'order_view.order_status',
            'order_view.status_reason',
            'order_view.status_reason as reason_id',
            'order_view.advert_commission',
            'order_view.wm_commission',
            'order_view.advert_offer_target_status',
            'order_view.country_id',
            'order_view.iso',
            'order_view.country_name',
            'countries.country_code AS customer_iso',

            'order_view.advert_currency_id',
            'order_view.advert_currency_name',
            'order_view.currency_id',
            'order_view.currency_name',

            'customer_view.address',
            'customer_view.region_id',
            'customer_view.region_name',
            'customer_view.city_id',
            'customer_view.city_name',
            'order_view.total_amount',
            'order_view.total_cost',
            'order_view.owner_id',
            'order_view.owner_name',
            'order_view.wm_id',
            'order_view.wm_name',
            'customer_view.customer_id',
            'customer_view.name',
            'customer_view.phone',
            'customer_view.email',
            'concat(customer_view.ip, " - ", customer_view.country_name) as ip',
            'customer_view.os',
            'customer_view.sid',
            'order_view.sub_id_1',
            'order_view.sub_id_2',
            'order_view.sub_id_3',
            'order_view.sub_id_4',
            'order_view.sub_id_5',
            'order_view.view_time',
            'customer_view.view_hash',
            'order_view.referrer',
            'customer_view.browser',
            'customer_view.cookie',
            'order_view.paid_online',
            'order_view.information',
            'customer_view.pin',
            //'( SELECT COUNT( OV.order_id ) FROM order_view OV, customer_view CV WHERE OV.order_id != order_view.order_id
            // AND CV.phone LIKE customer_view.phone AND OV.customer_id = CV.customer_id) AS history'
        ];
    }
}
