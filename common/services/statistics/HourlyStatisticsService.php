<?php

namespace common\services\statistics;

use Yii;
use common\models\order\Order;
use common\models\order\OrderStatus;
use common\models\LandingViews;
use yii\helpers\ArrayHelper;

class HourlyStatisticsService extends Statistics
{
    public $hours;
    public $total;

    /**
     * HourlyStatisticsService constructor.
     * @param $post
     */
    public function __construct($post)
    {
        $this->hours = $this->getStatistics($post);
        $this->total = (new \common\models\statistics\Statistics())->getAllStatisticsTotalRow($this->hours);
    }

    public function statisticsQuery()
    {
        $approved = "(" .
            OrderStatus::WAITING_DELIVERY . ", " .
            OrderStatus::DELIVERY_IN_PROGRESS . ", " .
            OrderStatus::SUCCESS_DELIVERY . ", " .
            OrderStatus::NOT_PAID . ")";

        $query = Order::find()
            ->select([
                "CONCAT(DATE_FORMAT(`order`.created_at, \"%H\"), ':00-', DATE_FORMAT(`order`.created_at, \"%H\"), ':59') AS date",
                "SUM(if(`order`.order_status = " . OrderStatus::PENDING . ", 1, 0 )) AS `pending`",
                "SUM(if(`order`.order_status in " . $approved . ", 1, 0 )) AS `approved`",
                "COUNT(*) as total",
            ])
            ->leftJoin('target_wm', 'target_wm.target_wm_id = order.target_wm_id')
            ->leftJoin('target_advert', 'order.target_advert_id = target_advert.target_advert_id')
            ->leftJoin('target_advert_group', 'target_advert_group.target_advert_group_id = target_advert.target_advert_group_id')
            ->leftJoin('advert_offer_target', 'target_advert_group.advert_offer_target_id = advert_offer_target.advert_offer_target_id')
            ->leftJoin('flow', 'order.flow_id = flow.flow_id')
            ->leftJoin('offer', 'advert_offer_target.offer_id = offer.offer_id');

        return $query;
    }

    /**
     * @param $post
     * @return array|Order[]
     */
    public function getStatistics($post)
    {
        $ids = null;
        $date_range = [];

        $query = $this->statisticsQuery();

        if (isset($post['offer_id'])) $query->andWhere(['advert_offer_target.offer_id' => $post['offer_id']]);
        if (isset($post['geo_id'])) $query->andWhere(['advert_offer_target.geo_id' => $post['geo_id']]);
        if (isset($post['advert_id'])) $query->andWhere(['target_advert.advert_id' => $post['advert_id']]);
        if (isset($post['flow_id'])) $query->andWhere(['flow.flow_id' => $post['flow_id']]);

        if (!isset($post['wm'])) {
            $query->andWhere(['flow.wm_id' => Yii::$app->user->identity->getId()]);
            $ids = Yii::$app->user->identity->getId();
        }

        if (!empty($post['wm'])) {
            $query->andWhere(['flow.wm_id' => $post['wm']]);
            $ids = $post['wm'];
        }

        if (isset($post['date'])) {
            $start = new \DateTime($post['date']['start']);
            $start->setTime(0, 0, 0);
            $start_date = $start->format('Y-m-d H:i:s');

            $end = new \DateTime($post['date']['end']);
            $end->setTime(23, 59, 59);
            $end_date = $end->format('Y-m-d H:i:s');

            $query->andWhere(['>', 'order.created_at', $start_date]);
            $query->andWhere(['<', 'order.created_at', $end_date]);

            $date_range = [
                'start' => $start_date,
                'end' => $end_date,
            ];
        }

        $statistics = $query
            ->groupBy('date')
            ->asArray()
            ->all();

        $statistics = ArrayHelper::index($statistics, 'date');

        $landing_data = $this->getViews($post, $date_range, $ids);

        $hours = $this->hoursMatrix();

        $intervals = [];
        foreach ($hours as $h => $hour) {

            $intervals[$hour] = [
                'date' => $hour,
                'total' => 0,
                'views' => 0,
                'uniques' => 0,
                'cr' => 0,
                'pr' => 0,
                'ar' => 0,
            ];

            $calculating = new \common\models\statistics\Statistics();
            foreach ($statistics as $key => $order) {
                $index = $order['date'];

                $statistics[$key]['views'] = 0;
                $statistics[$key]['uniques'] = 0;

                if (isset($landing_data[$index])) {
                    $calculating->setAttributes(array_merge($landing_data[$index], $order));
                    $calculating->setCalculatedAttributes();
                    $statistics[$key] = $order + $calculating->getAttributes();
                }

//                if (isset($landing_data[$index])) {
//                    $statistics[$key]['views'] = $landing_data[$index]['views'];
//                    $statistics[$key]['uniques'] = $landing_data[$index]['uniques'];
//                }

//                $statistics[$key]['cr_t'] = !empty($statistics[$key]['views']) ? ($order['orders'] / $statistics[$key]['views']) * 100 : 0;
//                $statistics[$key]['pr'] = !is_null($order['orders']) ? ($order['pending'] / $order['orders']) * 100 : 0;
//                $statistics[$key]['ar'] = !is_null($order['orders']) ? ($order['approved'] / $order['orders']) * 100 : 0;
            }
        }

        $statistics = array_merge($intervals, $statistics);

        usort($statistics, function ($a, $b) {
            return (strtotime($a['date']) > strtotime($b['date']));
        });

        return $statistics;
    }

    /**
     * @param $post
     * @param $date_range
     * @param null $ids
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getViews($post, $date_range, $ids = null)
    {
        $query = LandingViews::find()
            ->select([
                "CONCAT(DATE_FORMAT(date, \"%H\"), ':00-', DATE_FORMAT(date, \"%H\"), ':59') AS index_date",
                "SUM(views) as views",
                "SUM(uniques) as uniques"
            ])
            ->leftJoin('flow', 'landing_views.flow_id = flow.flow_id')
            ->leftJoin('offer', 'landing_views.offer_id = offer.offer_id');

        if ($ids) $query->where(['flow.wm_id' => $ids]);

        if (!empty($date_range)) {
            $query->andWhere(['>', 'landing_views.date', $date_range['start']]);
            $query->andWhere(['<', 'landing_views.date', $date_range['end']]);
        }

        if (isset($post['offer_id'])) $query->andWhere(['landing_views.offer_id' => $post['offer_id']]);
        if (isset($post['flow_id'])) $query->andWhere(['landing_views.flow_id' => $post['flow_id']]);
        if (isset($post['geo_id'])) $query->andWhere(['landing_views.geo_id' => $post['geo_id']]);

        $data = $query
            ->groupBy('index_date')
            ->asArray()
            ->all();

        return ArrayHelper::index($data, 'index_date');
    }

    /**
     * @param $rows
     * @return array
     */
    public function getTotalRow($rows)
    {
        if (empty($rows)) return [];

        $total = [];
        foreach ($rows as $order) {

            unset($order['date']);
            unset($order['index_date']);

            foreach ($order as $key => $row) {
                if (isset($total[$key])) {
                    $total[$key] += $row;
                } else {
                    $total[$key] = $row;
                }
            }
        }

        $total['cr'] = !empty($total['views']) ? ($total['total'] / $total['views']) * 100 : 0;
        $total['pr'] = !empty($total['pending']) ? (1 - ($total['pending']) / $total['total']) * 100 : 0;
        $total['ar'] = !empty($total['approved']) ? (1 - ($total['approved']) / $total['total']) * 100 : 0;
//        $count = count($rows);
//
//        $total['cr_t'] = $total['cr_t'] / $count;

        return $total;
    }
}