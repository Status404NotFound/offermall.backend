<?php

namespace webmaster\models\statistics;

use Yii;
use common\traits\TotalStatisticTrait;
use common\models\order\Order;
use common\models\order\OrderStatus;
use common\models\LandingViews;

/**
 * Class Offer
 * @package webmaster\models\statistics
 */
class Offer extends Statistics
{
    use TotalStatisticTrait;

    public $offer_id = null;
    public $offer_name = null;
    public $commission_success = 0;
    public $commission_potential = 0;

    /**
     * @return array
     */
    public function rules()
    {
        return array_merge(array_merge(parent::rules(), [
            [['offer_id'], 'integer'],
            [['offer_name'], 'string'],
            [['commission_success', 'commission_potential'], 'number'],
        ]));
    }

    /**
     * @param $filters
     * @return array
     */
    public function searchOffer($filters)
    {
        $views = $this->viewsQuery($filters);
        $orders = $this->ordersQuery($filters);
        return $this->compareData($views, $orders);
    }

    /**
     * @param $views
     * @param $orders
     * @return array
     */
    private function compareData($views, $orders)
    {
        $result = [];
        foreach ($views as $view) {
            $isset_orders = false;
            foreach ($orders as $key => $order) {
                if ($view['offer_id'] == $order['offer_id']) {
                    $model = new self();
                    $model->setAttributes(array_merge($view, $order));
                    $model->setCalculatedAttributes();

                    $result[] = $model->getAttributes();
                    $isset_orders = true;
                    unset($orders[$key]);
                }
            }
            if ($isset_orders === false) {
                $model = new self();
                $model->setAttributes($view);
                $result[] = $model->getAttributes();
            }
        }

        if (!empty($orders)) {
            foreach ($orders as $order) {
                $model = new self();
                $model->setAttributes($order);
                $model->setCalculatedAttributes();

                $result[] = $model->getAttributes();
            }
        }
        return $result;
    }

    /**
     * @param array $filters
     * @return array|Order[]
     */
    private function ordersQuery($filters = [])
    {
        $pending = Statistics::pendingStatuses();
        $approved = Statistics::approvedStatuses();
        $rejected = Statistics::rejectedStatuses();
        $not_valid = Statistics::notValidStatuses();
    
        $query = Order::find()
            ->select([
                'offer.offer_id',
                'offer.offer_name',
                "SUM(if(`order`.order_status in " . $pending . ", 1, 0 )) AS `pending`",
                "SUM(if(`order`.order_status in " . $approved . ", 1, 0 )) AS `approved`",
                "SUM(if(`order`.order_status in " . $rejected . ", 1, 0 )) AS `rejected`",
                "SUM(if(`order`.order_status in " . $not_valid . ", 1, 0 )) AS `not_valid`",
                "SUM(if(`order`.order_status = " . OrderStatus::SUCCESS_DELIVERY . ", 1, 0 )) AS `success_delivery`",
//                "SUM(if(`order`.order_status >= wm_offer_target.wm_offer_target_status, `order`.wm_commission, 0)) AS `commission_success`",
//                "SUM(if(`order`.order_status not in " . self::not_valid . " and `order`.order_status >= wm_offer_target.wm_offer_target_status,`order`.wm_commission, 0)) AS `commission_success`",
//                "SUM(if(`order`.order_status not in " . self::not_valid . " and `order`.order_status >= wm_offer_target.wm_offer_target_status or `order`.order_status = 22,`order`.wm_commission, 0)) AS `commission_success`",
//                "SUM(if(`order`.order_status not in " . self::not_valid . " and `order`.order_status >= wm_offer_target.wm_offer_target_status or `order`.order_status IN (20, 22),`order`.wm_commission, 0)) AS `commission_success`",
                "SUM(CASE WHEN `order`.order_status not in " . self::not_valid . " and `order`.order_status >= wm_offer_target.wm_offer_target_status THEN `order`.wm_commission 
                WHEN wm_offer_target.wm_offer_target_status = ". OrderStatus::WAITING_DELIVERY ." and `order`.order_status IN ". self::approved ." THEN `order`.wm_commission
                ELSE 0 
                END) AS `commission_success`",
//                "SUM(if(`order`.order_status in " . $pending . " and `order`.order_status <= wm_offer_target.wm_offer_target_status, target_wm_group.base_commission, 0 )) AS `commission_potential`",
                "SUM(if(`order`.order_status < wm_offer_target.wm_offer_target_status and `order`.order_status in " . self::pending . ", target_wm_group.base_commission, 0 )) AS `commission_potential`",
                "SUM(if(`order`.total_amount > 1 and order.order_status in " . self::approved . ", 1, 0)) AS up_sale_rate",
                "COUNT(*) as total"
            ])
            ->leftJoin('target_wm', 'target_wm.target_wm_id = order.target_wm_id')
            ->leftJoin('target_wm_group', 'target_wm_group.target_wm_group_id = target_wm.target_wm_group_id')
            ->leftJoin('wm_offer_target', 'target_wm_group.wm_offer_target_id = wm_offer_target.wm_offer_target_id')
            ->leftJoin('flow', 'order.flow_id = flow.flow_id')
            ->leftJoin('offer', 'wm_offer_target.offer_id = offer.offer_id')
            ->where(['`order`.deleted' => 0]);

        if (isset($filters['wm_id']) && in_array($filters['wm_id'], Yii::$app->user->identity->getWmChild())) {
            $query->andWhere(['flow.wm_id' => $filters['wm_id']]);
        } else $query->andWhere(['flow.wm_id' => Yii::$app->user->identity->getWmChild()]);

        if (isset($filters['offer_id'])) $query->andWhere(['order.offer_id' => $filters['offer_id']]);
        if (isset($filters['geo_id'])) $query->andWhere(['wm_offer_target.geo_id' => $filters['geo_id']]);
        if (isset($filters['advert_offer_target_status'])) $query->andWhere(['wm_offer_target.advert_offer_target_status' => $filters['advert_offer_target_status']]);

        if (isset($filters['date'])) {
            $start = new \DateTime($filters['date']['start']);
            $start->setTime(0, 0, 0);
            $start_date = $start->format('Y-m-d H:i:s');

            $end = new \DateTime($filters['date']['end']);
            $end->setTime(23, 59, 59);
            $end_date = $end->format('Y-m-d H:i:s');

            $query->andWhere(['>', 'order.created_at', $start_date]);
            $query->andWhere(['<', 'order.created_at', $end_date]);
        }

        $statistics = $query
            ->groupBy('offer.offer_id')
            ->asArray()
            ->all();

        return $statistics;
    }

    /**
     * @param array $filters
     * @return array|\common\models\geo\Geo[]|LandingViews[]|\yii\db\ActiveRecord[]
     */
    private function viewsQuery($filters = [])
    {
        $query = LandingViews::find()
            ->select([
                'offer.offer_id',
                'offer.offer_name',
                "SUM(views) as views",
                "SUM(uniques) as uniques"
            ])
            ->leftJoin('offer', 'landing_views.offer_id = offer.offer_id')
            ->leftJoin('flow', 'landing_views.flow_id = flow.flow_id');

        if (isset($filters['wm_id']) && in_array($filters['wm_id'], Yii::$app->user->identity->getWmChild())) {
            $query->andWhere(['flow.wm_id' => $filters['wm_id']]);
        } else $query->andWhere(['flow.wm_id' => Yii::$app->user->identity->getWmChild()]);

        if (isset($filters['date'])) {
            $start = new \DateTime($filters['date']['start']);
            $start->setTime(0, 0, 0);
            $start_date = $start->format('Y-m-d H:i:s');

            $end = new \DateTime($filters['date']['end']);
            $end->setTime(23, 59, 59);
            $end_date = $end->format('Y-m-d H:i:s');

            $query->andWhere(['>', 'landing_views.date', $start_date]);
            $query->andWhere(['<', 'landing_views.date', $end_date]);
        }

        if (isset($filters['offer_id'])) $query->andWhere(['landing_views.offer_id' => $filters['offer_id']]);
        if (isset($filters['flow_id'])) $query->andWhere(['landing_views.flow_id' => $filters['flow_id']]);
        if (isset($filters['geo_id'])) $query->andWhere(['landing_views.geo_id' => $filters['geo_id']]);

        $views = $query
            ->groupBy('landing_views.offer_id')
            ->asArray()
            ->all();

        return $views;
    }
}