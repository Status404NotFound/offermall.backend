<?php

namespace webmaster\services\order;

use Yii;
use common\components\joinMap\JoinMap;
use common\models\customer\Customer;
use common\models\customer\CustomerSystem;
use common\models\offer\targets\advert\AdvertOfferTarget;
use common\models\offer\targets\advert\TargetAdvert;
use common\models\offer\targets\advert\TargetAdvertGroup;
use common\models\order\Order;
use common\models\order\OrderData;
use common\models\order\OrderStatus;
use common\models\order\OrderView;
use common\models\order\StatusReason;
use common\services\webmaster\Helper;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * Class OrdersService
 * @package webmaster\services\order
 *
 * @property array $_orders_dependencies
 */
class OrdersService
{

    /**
     * @param array $filters
     * @param null $pagination
     * @param null $sort_order
     * @param null $sort_field
     * @return array
     * @throws \common\components\joinMap\JoinMapException
     * @throws \yii\base\InvalidConfigException
     */
    public function getMyOrders(array $filters = [], $pagination = null, $sort_order = null, $sort_field = null): array
    {
        $count_all_query = Order::find()
            ->where(['order.deleted' => 0])
            ->leftJoin('flow', 'order.flow_id = flow.flow_id')
            ->andWhere(['flow.wm_id' => Yii::$app->user->identity->getWmChild()]);
        $this->filterQuery($count_all_query, $filters, $pagination);

        $orders_basic_query = Order::find()
            ->select(['order.order_hash'])
            ->where(['order.deleted' => 0])
            ->leftJoin('flow', 'order.flow_id = flow.flow_id')
            ->andWhere(['flow.wm_id' => Yii::$app->user->identity->getWmChild()])
            ->orderBy(['order.created_at' => $sort_order]);
    
        if ($sort_field !== null) {
            switch ($sort_field) {
                case 'created_at':
                    $orders_basic_query->orderBy(['order.created_at' => $sort_order]);
                    break;
                    
                case 'offer_name':
                    $orders_basic_query->leftJoin('offer', 'order.offer_id = offer.offer_id');
                    $orders_basic_query->orderBy(['offer.offer_name' => $sort_order]);
                    break;
                    
                case 'wm_commmission_usd':
                    $orders_basic_query->orderBy(['order.wm_commission' => $sort_order]);
                    break;
            }
        }
        $this->filterQuery($orders_basic_query, $filters, $pagination);
        $orders_hashes = ArrayHelper::map($orders_basic_query->all(), 'order_hash', 'order_hash');
    
        $orders = OrderView::find()
            ->select($this->selectFields(), new Expression('STRAIGHT_JOIN'))
            ->innerJoin('customer_view', 'customer_view.customer_id = order_view.customer_id')
            ->leftJoin('countries', 'countries.id = customer_view.country_id')
            ->leftJoin('flow', 'order_view.flow_id = flow.flow_id')
            ->leftJoin('target_wm', 'target_wm.target_wm_id= order_view.target_wm_id')
            ->leftJoin('target_wm_group', 'target_wm_group.target_wm_group_id = target_wm.target_wm_group_id')
            ->leftJoin('partner_orders', 'partner_orders.order_id = order_view.order_id')
            ->leftJoin('wm_offer_target', 'wm_offer_target.wm_offer_target_id = target_wm_group.wm_offer_target_id')
            ->andWhere(['order_hash' => $orders_hashes]);

        $sort_field !== null
            ? $orders->orderBy([$sort_field => $sort_order])
            : $orders->orderBy(['created_at' => $sort_order]);
        
        $result = [
            'orders' => $orders->asArray()->all(),
            'count' => [
                'count_all' => (int)$count_all_query->count(),
            ],
        ];
    
        foreach ($result['orders'] as &$order) {
        
            if ($order['order_hold'] !== null
                && $order['hold_end'] < $order['date']
                && $order['hold_end'] > Yii::$app->formatter->asDate('now', 'php:d.m.Y H:i:s')) {
                $order['hold'] = $order['order_hold'] . ' day';
            } else {
                $order['hold'] = null;
            }
    
            $order['wm_commission'] !== null ? $order['success'] = true : $order['success'] = false;
            unset($order['wm_commission']);
        
            $order['name'] = Helper::hideFields(['name' => $order['name']]);
            $order['phone'] = Helper::hideFields(['phone' => $order['phone']]);
            $order['address'] = Helper::hideFields(['address' => $order['address']]);
            $order['email'] = Helper::hideFields(['email' => $order['email']]);
            $order['status'] = OrderStatus::attributeLabels($order['order_status']);
        
            if (OrderStatus::statusNeedReason($order['order_status']) === true) {
                $order['reason'] = StatusReason::getReason($order['order_status'], $order['reason']);
            } else {
                unset($order['reason']);
            }
        }
    
        return $result;
    }

    /**
     * @param $orders
     * @param $filters
     * @param $pagination
     * @throws \common\components\joinMap\JoinMapException
     */
    private function filterQuery($orders, $filters, $pagination): void
    {
        $joinMap = new JoinMap($orders, [
            [JoinMap::LEFT, TargetAdvert::tableName(), 'target_advert_id', Order::tableName(), 'target_advert_id'],
            [JoinMap::LEFT, TargetAdvertGroup::tableName(), 'target_advert_group_id', TargetAdvert::tableName(), 'target_advert_group_id'],
            [JoinMap::LEFT, AdvertOfferTarget::tableName(), 'advert_offer_target_id', TargetAdvertGroup::tableName(), 'advert_offer_target_id'],
            [JoinMap::LEFT, OrderData::tableName(), 'order_id', Order::tableName(), 'order_id'],
            [JoinMap::LEFT, Customer::tableName(), 'customer_id', Order::tableName(), 'customer_id'],
            [JoinMap::LEFT, CustomerSystem::tableName(), 'customer_id', Order::tableName(), 'customer_id'],
        ]);
        
        if (isset($filters['order_hash'])) {
            $orders->andWhere(['order.order_hash' => $filters['order_hash']['value']]);
        }
        if (isset($filters['offer'])) {
            $orders->andWhere(['order.offer_id' => $filters['offer']['value']]);
        }
        if (isset($filters['geo'])) {
            $joinMap->join($orders, TargetAdvert::tableName());
            $joinMap->join($orders, TargetAdvertGroup::tableName());
            $joinMap->join($orders, AdvertOfferTarget::tableName());
            $orders->andWhere(['advert_offer_target.geo_id' => $filters['geo']['value']]);
        }
        if (isset($filters['target'])) {
            $joinMap->join($orders, TargetAdvert::tableName());
            $joinMap->join($orders, TargetAdvertGroup::tableName());
            $joinMap->join($orders, AdvertOfferTarget::tableName());
            $orders->andWhere(['advert_offer_target.advert_offer_target_status' => $filters['target']['value']]);
        }
        if (isset($filters['wm_commmission_usd']['value'])) {
            $orders->andWhere(['order.wm_commission' => $filters['wm_commmission_usd']['value']]);
        }
        if (isset($filters['order_status']['value'])) {
            $orders->andWhere(['in', 'order.order_status', $filters['order_status']['value']]);
        }
        if (isset($filters['customer_info']['customer_name'])) {
            $joinMap->join($orders, Customer::tableName());
            $orders->andWhere(['LIKE', 'customer.name', $filters['customer_info']['customer_name']]);
        }
        if (isset($filters['customer_info']['phone'])) {
            $joinMap->join($orders, Customer::tableName());
            $orders->andWhere(['LIKE', 'customer.phone', $filters['customer_info']['phone']]);
        }
        if (isset($filters['sub_id']['sub_id_1'])) {
            $joinMap->join($orders, OrderData::tableName());
            $orders->andWhere(['LIKE', 'order_data.sub_id_1', $filters['sub_id']['sub_id_1']]);
        }
        if (isset($filters['sub_id']['sub_id_2'])) {
            $joinMap->join($orders, OrderData::tableName());
            $orders->andWhere(['LIKE', 'order_data.sub_id_2', $filters['sub_id']['sub_id_2']]);
        }
        if (isset($filters['sub_id']['sub_id_3'])) {
            $joinMap->join($orders, OrderData::tableName());
            $orders->andWhere(['LIKE', 'order_data.sub_id_3', $filters['sub_id']['sub_id_3']]);
        }
        if (isset($filters['sub_id']['sub_id_4'])) {
            $joinMap->join($orders, OrderData::tableName());
            $orders->andWhere(['LIKE', 'order_data.sub_id_4', $filters['sub_id']['sub_id_4']]);
        }
        if (isset($filters['sub_id']['sub_id_5'])) {
            $joinMap->join($orders, OrderData::tableName());
            $orders->andWhere(['LIKE', 'order_data.sub_id_5', $filters['sub_id']['sub_id_5']]);
        }
        if (isset($filters['lead_info']['view_time'])) {
            $joinMap->join($orders, OrderData::tableName());
            $orders->andWhere(['LIKE', 'order_data.view_time', $filters['lead_info']['view_time']]);
        }
        if (isset($filters['lead_info']['browser'])) {
            $joinMap->join($orders, CustomerSystem::tableName());
            $orders->andWhere(['LIKE', 'customer_system.browser', $filters['lead_info']['browser']]);
        }
        if (isset($filters['lead_info']['os'])) {
            $joinMap->join($orders, CustomerSystem::tableName());
            $orders->andWhere(['LIKE', 'customer_system.os', $filters['lead_info']['os']]);
        }
        if (isset($filters['lead_info']['flow_name'])) {
            $joinMap->join($orders, CustomerSystem::tableName());
            $orders->andWhere(['LIKE', 'customer_system.os', $filters['lead_info']['flow_name']]);
        }
        if (isset($filters['time'])) {
            $start = new \DateTime($filters['time']['start']);
            $start->setTime(0, 0, 0);
            $start_date = $start->format('Y-m-d H:i:s');
        
            $end = new \DateTime($filters['time']['end']);
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
     * @return array
     */
    private function selectFields(): array
    {
        return [
            'order_view.order_hash',
            'order_view.offer_id',
            'order_view.offer_name',
            'order_view.created_at',
            'order_view.order_status',
            'order_view.status_reason as reason',
            'order_view.advert_offer_target_status',
            'partner_orders.crm_resp',
            'order_view.country_id',
            'order_view.iso',
            'order_view.country_name',
            'order_view.wm_commission',
            'order_view.referrer',
            "IFNULL(CONCAT( order_view.wm_commission, ' ', '$' ), CONCAT( target_wm_group.base_commission, ' ', '$' )) AS wm_commmission_usd",
            'customer_view.country_code',
            'customer_view.address',
            'customer_view.region_id',
            'customer_view.region_name',
            'customer_view.city_id',
            'customer_view.city_name',
            'order_view.total_cost',
            'order_view.wm_id',
            'customer_view.customer_id',
            'customer_view.name',
            'customer_view.phone',
            'customer_view.email',
            'order_view.sub_id_1',
            'order_view.sub_id_2',
            'order_view.sub_id_3',
            'order_view.sub_id_4',
            'order_view.sub_id_5',
            'order_view.view_time',
            'customer_view.browser',
            'customer_view.os',
            'target_wm_group.hold',
            'target_wm_group.use_commission_rules',
            'flow.flow_name',
            "DATE_FORMAT(`order_view`.created_at, '%d.%m.%Y %H:%i:%s') AS date",
            "DATE_FORMAT(DATE_ADD(`order_view`.created_at, INTERVAL `target_wm_group`.hold DAY), '%d.%m.%Y %H:%i:%s') as hold_end",
            'IF(`order_view`.order_status >= wm_offer_target.wm_offer_target_status, `target_wm_group`.hold, NULL) as order_hold',
            'IF(`target_wm_group`.use_commission_rules = 1, `order_view`.total_amount, 0) as pcs'
        ];
    }
}