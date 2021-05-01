<?php

namespace webmaster\models\statistics;

use Yii;
use common\traits\TotalStatisticTrait;
use common\models\order\Order;
use common\models\order\OrderStatus;
use common\models\LandingViews;

/**
 * Class Sub
 * @package webmaster\models\statistics
 */
class Sub extends Statistics
{
    use TotalStatisticTrait;

    public $sub_id_1 = null;
    public $sub_id_2 = null;
    public $sub_id_3 = null;
    public $sub_id_4 = null;
    public $sub_id_5 = null;
    public $commission_success = 0;
    public $commission_potential = 0;

    /**
     * @return array
     */
    public function rules()
    {
        return array_merge(array_merge(parent::rules(), [
            [['sub_id_1', 'sub_id_2', 'sub_id_3', 'sub_id_4', 'sub_id_5'], 'string'],
            [['commission_success', 'commission_potential'], 'number'],
        ]));
    }

    /**
     * @param $filters
     * @return array
     */
    public function searchSub($filters)
    {
        $views = $this->viewsQuery($filters);
        $orders = $this->ordersQuery($filters);
        $sub_id = $this->transformSubIds($filters['sub_id']);

        return $this->compareData($views, $orders, $sub_id);
    }

    /**
     * @param $views
     * @param $orders
     * @return array
     */
    private function compareData($views, $orders, $sub_id)
    {
        $result = [];
        foreach ($views as $view) {
            $isset_orders = false;
            foreach ($orders as $key => $order) {
                if ($view[$sub_id] == $order[$sub_id]) {
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
        $query = Order::find()
            ->select([
                "`order_data`.sub_id_1",
                "`order_data`.sub_id_2",
                "`order_data`.sub_id_3",
                "`order_data`.sub_id_4",
                "`order_data`.sub_id_5",
                "CONCAT(`order_data`.sub_id_1, '_', `order_data`.sub_id_2, '_', `order_data`.sub_id_3, '_', `order_data`.sub_id_4, '_', `order_data`.sub_id_5) AS sub",
                "SUM(if(`order`.order_status in " . self::pending . ", 1, 0 )) AS `pending`",
                "SUM(if(`order`.order_status in " . self::approved . ", 1, 0 )) AS `approved`",
                "SUM(if(`order`.order_status in " . self::rejected . ", 1, 0 )) AS `rejected`",
                "SUM(if(`order`.order_status in " . self::not_valid . ", 1, 0 )) AS `not_valid`",
                "SUM(if(`order`.order_status = " . OrderStatus::SUCCESS_DELIVERY . ", 1, 0 )) AS `success_delivery`",
                "SUM(if(`order`.order_status in " . self::rejected . ", 1, 0 )) AS `rejected`",
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
            ->leftJoin('order_data', 'order.order_id = order_data.order_id')
            ->where(['`order`.deleted' => 0]);

        if (isset($filters['wm_id']) && in_array($filters['wm_id'], Yii::$app->user->identity->getWmChild())) {
            $query->andWhere(['flow.wm_id' => $filters['wm_id']]);
        } else $query->andWhere(['flow.wm_id' => Yii::$app->user->identity->getWmChild()]);

        if (isset($filters['offer_id'])) $query->andWhere(['order.offer_id' => $filters['offer_id']]);
        if (isset($filters['flow_id'])) $query->andWhere(['flow.flow_id' => $filters['flow_id']]);
        if (isset($filters['geo_id'])) $query->andWhere(['wm_offer_target.geo_id' => $filters['geo_id']]);
        if (isset($filters['advert_offer_target_status'])) $query->andWhere(['wm_offer_target.advert_offer_target_status' => $filters['advert_offer_target_status']]);

        if (isset($filters['date'])) {
            $start = new \DateTime($filters['date'][0]);
            $start->setTime(0, 0, 0);
            $start_date = $start->format('Y-m-d H:i:s');

            $end = new \DateTime($filters['date'][1]);
            $end->setTime(23, 59, 59);
            $end_date = $end->format('Y-m-d H:i:s');

            $query->andWhere(['>', 'order.created_at', $start_date]);
            $query->andWhere(['<', 'order.created_at', $end_date]);
        }

        $sub_id = $filters['sub_id'];

        if ($sub_id == 1) $query->groupBy('`order_data`.sub_id_1');
        if ($sub_id == 2) $query->groupBy('`order_data`.sub_id_2');
        if ($sub_id == 3) $query->groupBy('`order_data`.sub_id_3');
        if ($sub_id == 4) $query->groupBy('`order_data`.sub_id_4');
        if ($sub_id == 5) $query->groupBy('`order_data`.sub_id_5');

        if ($sub_id == 0) $query->groupBy('sub');

        $statistics = $query
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
                "landing_views.sub_id_1",
                "landing_views.sub_id_2",
                "landing_views.sub_id_3",
                "landing_views.sub_id_4",
                "landing_views.sub_id_5",
                "CONCAT(`landing_views`.sub_id_1, '_', `landing_views`.sub_id_2, '_', `landing_views`.sub_id_3, '_', `landing_views`.sub_id_4, '_', `landing_views`.sub_id_5) AS sub",
                "SUM(landing_views.views) as views",
                "SUM(landing_views.uniques) as uniques"
            ])
            ->leftJoin('offer', 'landing_views.offer_id = offer.offer_id')
            ->leftJoin('flow', 'landing_views.flow_id = flow.flow_id');

        if (isset($filters['wm_id']) && in_array($filters['wm_id'], Yii::$app->user->identity->getWmChild())) {
            $query->andWhere(['flow.wm_id' => $filters['wm_id']]);
        } else $query->andWhere(['flow.wm_id' => Yii::$app->user->identity->getWmChild()]);

        if (isset($filters['date'])) {
            $start = new \DateTime($filters['date'][0]);
            $start->setTime(0, 0, 0);
            $start_date = $start->format('Y-m-d H:i:s');

            $end = new \DateTime($filters['date'][1]);
            $end->setTime(23, 59, 59);
            $end_date = $end->format('Y-m-d H:i:s');

            $query->andWhere(['>', 'landing_views.date', $start_date]);
            $query->andWhere(['<', 'landing_views.date', $end_date]);
        }

        if (isset($filters['offer_id'])) $query->andWhere(['landing_views.offer_id' => $filters['offer_id']]);
        if (isset($filters['flow_id'])) $query->andWhere(['landing_views.flow_id' => $filters['flow_id']]);
        if (isset($filters['geo_id'])) $query->andWhere(['landing_views.geo_id' => $filters['geo_id']]);

        $sub_id = $filters['sub_id'];

        if ($sub_id == 1) $query->groupBy('`landing_views`.sub_id_1');
        if ($sub_id == 2) $query->groupBy('`landing_views`.sub_id_2');
        if ($sub_id == 3) $query->groupBy('`landing_views`.sub_id_3');
        if ($sub_id == 4) $query->groupBy('`landing_views`.sub_id_4');
        if ($sub_id == 5) $query->groupBy('`landing_views`.sub_id_5');

        if ($sub_id == 0) $query->groupBy('sub');

        $views = $query
            ->asArray()
            ->all();

        return $views;
    }

    /**
     * @param $sub_id_array
     * @return string
     */
    private function transformSubIds($sub_id_array)
    {
        if ($sub_id_array == 1) $sub_id = 'sub_id_1';
        if ($sub_id_array == 2) $sub_id = 'sub_id_2';
        if ($sub_id_array == 3) $sub_id = 'sub_id_3';
        if ($sub_id_array == 4) $sub_id = 'sub_id_4';
        if ($sub_id_array == 5) $sub_id = 'sub_id_5';

        if ($sub_id_array == 0) $sub_id = 'sub';

        return $sub_id;
    }
}