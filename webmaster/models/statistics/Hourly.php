<?php

namespace webmaster\models\statistics;

use Yii;
use common\traits\TotalStatisticTrait;
use common\models\order\Order;
use common\models\order\OrderStatus;
use common\models\LandingViews;

/**
 * Class Hourly
 * @package webmaster\models\statistics
 */
class Hourly extends Statistics
{
    use TotalStatisticTrait;

    /**
     * Hours intervals
     * @var null
     */
    public $index_date = null;

    /**
     * @return array
     */
    public function rules()
    {
        return array_merge(array_merge(parent::rules(), [
            [['index_date'], 'integer'],
        ]));
    }

    /**
     * @param $filters
     * @return array
     */
    public function searchHourly($filters)
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
        $matrix = $this->hoursMatrix();

        $result = [];
        foreach ($views as $view) {
            $isset_orders = false;
            foreach ($orders as $order) {
                if ($view['index_date'] == $order['index_date']) {
                    $model = new self();
                    $model->setAttributes(array_merge($view, $order));
                    $model->setCalculatedAttributes();

                    $result[] = $model->getAttributes();
                    $isset_orders = true;
                }
            }
            if ($isset_orders === false) {
                $model = new self();
                $model->setAttributes($view);
                $result[] = $model->getAttributes();
            }
        }

        foreach ($matrix as $hour) {
            $isset_views = false;
            foreach ($result as &$order) {
                if ($hour['index_date'] == $order['index_date']) {
                    $model = new self();
                    $model->setAttributes(array_merge($hour, $order));
                    $model->setCalculatedAttributes();
                    $order = $model->getAttributes();
                    $isset_views = true;
                }
            }
            if ($isset_views === false) {
                $model = new self();
                $model->setAttributes($hour);
                $result[] = $model->getAttributes();
            }
        }

        usort($result, function ($a, $b) {
            return (strtotime($a['index_date']) > strtotime($b['index_date']));
        });

        return $result;
    }

    /**
     * @param array $filters
     * @return array|Order[]
     */
    private function ordersQuery($filters = [])
    {
        $approved = "(" .
            OrderStatus::WAITING_DELIVERY . ", " .
            OrderStatus::DELIVERY_IN_PROGRESS . ", " .
            OrderStatus::SUCCESS_DELIVERY . ", " .
            OrderStatus::NOT_PAID . ")";

        $query = Order::find()
            ->select([
                "CONCAT(DATE_FORMAT(`order`.created_at, \"%H\"), ':00-', DATE_FORMAT(`order`.created_at, \"%H\"), ':59') AS index_date",
                "SUM(if(`order`.order_status = " . OrderStatus::PENDING . ", 1, 0 )) AS `pending`",
                "SUM(if(`order`.order_status in " . $approved . ", 1, 0 )) AS `approved`",
                "COUNT(*) as total",
            ])
            ->leftJoin('target_wm', 'target_wm.target_wm_id = order.target_wm_id')
            ->leftJoin('flow', '`order`.flow_id = flow.flow_id')
            ->leftJoin('target_wm_group', 'target_wm_group.target_wm_group_id = target_wm.target_wm_group_id')
            ->leftJoin('wm_offer_target', 'target_wm_group.wm_offer_target_id = wm_offer_target.wm_offer_target_id')
            ->leftJoin('offer', 'wm_offer_target.offer_id = offer.offer_id')
            ->where(['`order`.deleted' => 0]);

        if (isset($filters['wm_id']) && in_array($filters['wm_id'], Yii::$app->user->identity->getWmChild())) {
            $query->andWhere(['flow.wm_id' => $filters['wm_id']]);
        } else $query->andWhere(['flow.wm_id' => Yii::$app->user->identity->getWmChild()]);

        if (isset($filters['offer_id'])) $query->andWhere(['wm_offer_target.offer_id' => $filters['offer_id']]);
        if (isset($filters['geo_id'])) $query->andWhere(['wm_offer_target.geo_id' => $filters['geo_id']]);
        if (isset($filters['advert_id'])) $query->andWhere(['target_advert.advert_id' => $filters['advert_id']]);
        if (isset($filters['flow_id'])) $query->andWhere(['flow.flow_id' => $filters['flow_id']]);
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
            ->groupBy('index_date')
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
                "CONCAT(DATE_FORMAT(date, \"%H\"), ':00-', DATE_FORMAT(date, \"%H\"), ':59') AS index_date",
                "SUM(views) as views",
                "SUM(uniques) as uniques"
            ])
            ->leftJoin('flow', 'landing_views.flow_id = flow.flow_id')
            ->leftJoin('offer', 'landing_views.offer_id = offer.offer_id');

        if (isset($filters['wm_id']) && in_array($filters['wm_id'], Yii::$app->user->identity->getWmChild())) {
            $query->andWhere(['flow.wm_id' => $filters['wm_id']]);
        } else $query->andWhere(['flow.wm_id' => Yii::$app->user->identity->getWmChild()]);

        if (isset($filters['offer_id'])) $query->andWhere(['landing_views.offer_id' => $filters['offer_id']]);
        if (isset($filters['flow_id'])) $query->andWhere(['landing_views.flow_id' => $filters['flow_id']]);
        if (isset($filters['geo_id'])) $query->andWhere(['landing_views.geo_id' => $filters['geo_id']]);

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

        $views = $query
            ->groupBy('index_date')
            ->asArray()
            ->all();

        return $views;
    }

    /**
     * @return array
     */
    private function hoursMatrix()
    {
        $matrix = [
            '00:00-00:59' => '00:00-00:59',
            '01:00-01:59' => '01:00-01:59',
            '02:00-02:59' => '02:00-02:59',
            '03:00-03:59' => '03:00-03:59',
            '04:00-04:59' => '04:00-04:59',
            '05:00-05:59' => '05:00-05:59',
            '06:00-06:59' => '06:00-06:59',
            '07:00-07:59' => '07:00-07:59',
            '08:00-08:59' => '08:00-08:59',
            '09:00-09:59' => '09:00-09:59',
            '10:00-10:59' => '10:00-10:59',
            '11:00-11:59' => '11:00-11:59',
            '12:00-12:59' => '12:00-12:59',
            '13:00-13:59' => '13:00-13:59',
            '14:00-14:59' => '14:00-14:59',
            '15:00-15:59' => '15:00-15:59',
            '16:00-16:59' => '16:00-16:59',
            '17:00-17:59' => '17:00-17:59',
            '18:00-18:59' => '18:00-18:59',
            '19:00-19:59' => '19:00-19:59',
            '20:00-20:59' => '20:00-20:59',
            '21:00-21:59' => '21:00-21:59',
            '22:00-22:59' => '22:00-22:59',
            '23:00-23:59' => '23:00-23:59',
        ];

        $intervals = [];
        foreach ($matrix as $hour) {
            $intervals[] = [
                'index_date' => $hour
            ];
        }

        return $intervals;
    }
}