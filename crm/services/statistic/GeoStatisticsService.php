<?php

namespace crm\services\statistic;

use common\models\offer\Offer;
use common\models\offer\targets\advert\TargetAdvert;
use Yii;
use common\modules\user\models\tables\User;
use common\models\geo\Geo;
use common\models\order\OrderStatus;
use common\models\LandingViews;
use common\models\order\OrderView;
use common\models\statistics\Statistics;
use yii\helpers\ArrayHelper;

class GeoStatisticsService extends Statistics
{
    public $owner_id;
    public $geo;
    public $total;

    /**
     * GeoStatisticsService constructor.
     * @param array $filters
     * @param null $sort_field
     * @param null $sort_order
     */
    public function __construct($filters = [], $sort_field = null, $sort_order = null)
    {
        $this->owner_id = Yii::$app->user->identity->getOwnerId();
        $this->geo = $this->getStatistics($filters, $sort_field, $sort_order);
        $this->total = $this->getAllStatisticsTotalRow($this->geo);
        parent::__construct();
    }

    public function statisticsQuery()
    {
        $approved_statuses = "(" .
            OrderStatus::WAITING_DELIVERY . ", " .
            OrderStatus::DELIVERY_IN_PROGRESS . ", " .
            OrderStatus::SUCCESS_DELIVERY . ", " .
            OrderStatus::CANCELED . ", " .
            OrderStatus::RETURNED . ", " .
            OrderStatus::NOT_PAID . ")";
    
        $wds_statuses = "(" .
            OrderStatus::WAITING_DELIVERY . ", " .
            OrderStatus::DELIVERY_IN_PROGRESS . ", " .
            OrderStatus::SUCCESS_DELIVERY . ")";
        
        $query = OrderView::find()
            ->select([
                "if(`OV`.`country_id` is null or `OV`.`country_id` = '', 0, `OV`.`country_id`) as geo_id",
                "if(`OV`.`country_name` is null or `OV`.`country_name` = '', 'Not set', `OV`.`country_name`) as geo_name",
                "if(`OV`.`iso` is null or `OV`.`iso` = '', 0, `OV`.`iso`) as iso",
                'SUM(if(`OV`.order_status = ' . OrderStatus::PENDING . ', 1, 0 )) AS `pending`',
                'SUM(if(`OV`.order_status = ' . OrderStatus::BACK_TO_PENDING . ', 1, 0 )) AS `back_to_pending`',
                'SUM(if(`OV`.order_status = ' . OrderStatus::WAITING_DELIVERY . ', 1, 0 )) AS `waiting_for_delivery`',
                'SUM(if(`OV`.order_status = ' . OrderStatus::DELIVERY_IN_PROGRESS . ', 1, 0 )) AS `delivery_in_progress`',
                'SUM(if(`OV`.order_status = ' . OrderStatus::SUCCESS_DELIVERY . ', 1, 0 )) AS `success_delivery`',
                'SUM(if(`OV`.order_status = ' . OrderStatus::CANCELED . ', 1,0 )) AS `canceled`',
                'SUM(if(`OV`.order_status = ' . OrderStatus::REJECTED . ', 1,0 )) AS `rejected`',
                'SUM(if(`OV`.order_status = ' . OrderStatus::NOT_VALID . ', 1,0 )) AS `not_valid`',
                'SUM(if(`OV`.order_status = ' . OrderStatus::NOT_VALID_CHECKED . ', 1,0 )) AS `not_valid_checked`',
                'SUM(if(`OV`.order_status = ' . OrderStatus::NOT_PAID . ', 1,0 )) AS `not_paid`',
                'SUM(if(`OV`.order_status = ' . OrderStatus::RETURNED . ', 1,0 )) AS `returned`',
                'SUM(if(`OV`.order_status in ' . $approved_statuses . ', 1, 0 )) AS `approved`',
                'SUM(if(`OV`.order_status in ' . $wds_statuses . ' , 1, 0)) AS total_wds',
//                'SUM(CASE WHEN `OV`.order_status = ' . OrderStatus::DELIVERY_IN_PROGRESS . ' THEN `OV`.total_cost ELSE 0 END) AS `total_cost`',
                'SUM(CASE WHEN `OV`.order_status = ' . OrderStatus::SUCCESS_DELIVERY . ' THEN `OV`.total_cost ELSE 0 END) AS `total_cost`',
                'SUM(CASE WHEN `OV`.order_status in ' . $wds_statuses . ' THEN `OV`.total_amount ELSE 0 END) AS sum_pcs_wds',
                'SUM(if(`OV`.total_amount > 1 and `OV`.order_status in ' . $approved_statuses . ', 1, 0)) AS up_sale_pcs_total_approved',
                'SUM(if(`OV`.total_amount > 1, 1, 0)) AS up_sale_pcs_total',
                'COUNT(*) as total'])
            ->from(['order_view as OV']);

        $query->where(['`OV`.deleted' => 0]);

        return $query;
    }

    /**
     * @param array $filters
     * @param null $sort_field
     * @param null $sort_order
     * @return array|LandingViews[]|OrderView[]|\yii\db\ActiveRecord[]
     */
    public function getStatistics($filters = [], $sort_field = null, $sort_order = null)
    {
        $ids = null;
        $offer_id = null;
        $countries = [];
        $advert_id = null;
        $target = null;

        $query = $this->statisticsQuery();

        if (!is_null($this->owner_id)) $query->where(['OV.owner_id' => $this->owner_id]);
        $query->andWhere(['OV.deleted' => 0]);

        if (isset($filters['filters']['advert_id'])) {
            $query->andWhere(['OV.owner_id' => $filters['filters']['advert_id']['value']]);
            $advert_id = $filters['filters']['advert_id']['value'];
        }
        if (isset($filters['filters']['offer_id'])) {
            $query->andWhere(['OV.offer_id' => $filters['filters']['offer_id']['value']]);
            $offer_id = $filters['filters']['offer_id']['value'];
        }
        if (isset($filters['filters']['country_id'])) {
            $query->andWhere(['OV.country_id' => $filters['filters']['country_id']['value']]);
            $countries = $filters['filters']['country_id']['value'];
        }
        if (isset($filters['filters']['advert_target_id'])) {
            $query->andWhere(['OV.advert_offer_target_status' => $filters['filters']['advert_target_id']['value']]);
            $target = $filters['filters']['advert_target_id']['value'];
        }

        if (isset($filters['filters']['wm_id'])) {
            if ($filters['filters']['wm_id']['value'] == User::getChepollyNo_WmId()->id) {
                $ids = null;
            } else {
                $ids = $filters['filters']['wm_id']['value'];
            }

            $query->andWhere(['OV.wm_id' => $filters['filters']['wm_id']['value']]);

        }

        if (isset($filters['filters']['active'])) $query->andWhere(['target_advert.active' => $filters['filters']['active']['value']]);
        if (isset($filters['filters']['active'])) $query->andWhere(['OV.delivery_api_id' => $filters['filters']['active']['value']]);

        if (isset($filters['filters']['date'])) {
            $start = new \DateTime($filters['filters']['date']['start']);
            $start->setTime(0, 0, 0);
            $start_date = $start->format('Y-m-d H:i:s');

            $end = new \DateTime($filters['filters']['date']['end']);
            $end->setTime(23, 59, 59);
            $end_date = $end->format('Y-m-d H:i:s');

            $query->andWhere(['>', 'OV.created_at', $start_date]);
            $query->andWhere(['<', 'OV.created_at', $end_date]);

            $date_range = [
                'start' => $start_date,
                'end' => $end_date,
            ];
        } else {
            $start = new \DateTime();
            $start->setTime(0, 0, 0);
            $start->modify('-29 day');
            $start_date = $start->format('Y-m-d H:i:s');

            $end = new \DateTime();
            $end->setTime(23, 59, 59);
            $start->modify('now');
            $end_date = $end->format('Y-m-d H:i:s');

            $query->andWhere(['>', 'OV.created_at', $start_date]);
            $query->andWhere(['<', 'OV.created_at', $end_date]);

            $date_range = [
                'start' => $start_date,
                'end' => $end_date,
            ];
        }
    
        $cross_sale_pcs_count_by_date = (clone $query)
            ->select(['`OV`.`country_id`', 'count(*) as `cross_sale_pcs`'])
            ->andWhere(['target_advert_sku.use_extended_rules' => 1])
            ->andWhere('order_sku.order_id = `OV`.order_id')
            ->andWhere(['`OV`.order_status' => [
                OrderStatus::WAITING_DELIVERY,
                OrderStatus::DELIVERY_IN_PROGRESS,
                OrderStatus::SUCCESS_DELIVERY]])
            ->leftJoin('target_advert_sku', 'target_advert_sku.target_advert_id = `OV`.target_advert_id')
            ->leftJoin('order_sku', 'target_advert_sku.sku_id = order_sku.sku_id')
            ->groupBy('country_id')
            ->asArray()
            ->all();
    
        $cross_sale_pcs = [];
        foreach ($cross_sale_pcs_count_by_date as $row) {
            $cross_sale_pcs[$row['country_id']] = $row['cross_sale_pcs'];
        }

        $statistics = $query
            ->groupBy('`OV`.`country_id`')
            ->asArray()
            ->all();

        $statistics = ArrayHelper::index($statistics, 'geo_id');
        $zeroGeo = $this->normalizeGeo($offer_id, $countries, $advert_id, $target, $ids);
        $landing_data = $this->getViewsUniques($filters, $date_range, $ids);

        $calculating = new Statistics();
        foreach ($statistics as $key => $order) {

            $statistics[$key]['views'] = 0;
            $statistics[$key]['uniques'] = 0;
            $statistics[$key]['cr'] = 0;
            $statistics[$key]['cs'] = 0;
            $statistics[$key]['sr'] = 0;
            $statistics[$key]['pr'] = 0;
            $statistics[$key]['nr'] = 0;
            $statistics[$key]['ar'] = 0;
            $order['cross_sale_pcs'] = $cross_sale_pcs[$order['geo_id']] ?? 0;
            $order['up_sale_pcs'] = $order['sum_pcs_wds'] - $order['cross_sale_pcs'] - $order['total_wds'];
            
            if (isset($landing_data[$order['geo_name']])) {
                $calculating->setAttributes(array_merge($landing_data[$order['geo_name']], $order));
                $calculating->setCalculatedAttributes();
                $statistics[$key] = $order + $calculating->getAttributes();
            }

//            if (isset($landing_data[$index])) {
//                $statistics[$key]['views'] = $landing_data[$index]['views'];
//                $statistics[$key]['unique'] = $landing_data[$index]['uniques'];
//            }

//            $statistics[$key]['cr'] = !empty($statistics[$key]['views']) ? ($order['total'] / $statistics[$key]['views']) * 100 : 0;
//            $statistics[$key]['cs'] = !empty($statistics[$key]['views']) ? ($order['success_delivery'] / $statistics[$key]['views']) * 100 : 0;
//            $statistics[$key]['sr'] = !is_null($order['total']) ? ($order['success_delivery'] / $order['total']) * 100 : 0;
//            $statistics[$key]['pr'] = !is_null($order['total']) ? (1 - ($order['pending']) / $order['total']) * 100 : 0;
//            $statistics[$key]['nr'] = !is_null($order['total']) ? ($order['not_valid'] / $order['total']) * 100 : 0;
//            $statistics[$key]['ar'] = !is_null($order['total']) ? ($order['waiting_for_delivery'] + $order['delivery_in_progress'] + $order['not_paid']) / $order['total'] * 100 : 0;
            foreach ($order as $params => $values) {
                if ($params !== 'geo_name' && $params !== 'iso') {
                    $statistics[$key][$params] = (int)$values;
                }
            }
        }

        if ($filters['viewZeroGeo'] == true) {
            $result = empty($statistics) ? $zeroGeo : $statistics + $zeroGeo;
        } else {
            $result = $statistics;
        }

        $sort_order = ($sort_order == -1) ? SORT_DESC : SORT_ASC;

        if (isset($sort_field)) {
            ArrayHelper::multisort($result, $sort_field, $sort_order);
        } else {
            ArrayHelper::multisort($result, 'geo_id', SORT_ASC);
        }

        return $result;
    }

    /**
     * @param $filters
     * @param array $date_range
     * @param null $ids
     * @return array
     */
    public function getViewsUniques($filters, $date_range = [], $ids = null)
    {
        $query = LandingViews::find()
            ->select([
                "geo.geo_name",
                "SUM(views) as views",
                "SUM(uniques) as uniques"
            ])
            ->leftJoin('flow', 'landing_views.flow_id = flow.flow_id')
            ->leftJoin('geo', 'landing_views.geo_id = geo.geo_id');

        if ($ids) $query->where(['flow.wm_id' => $ids]);

        if (isset($filters['offer_id'])) $query->andWhere(['landing_views.offer_id' => $filters['offer_id']['value']]);
        if (isset($filters['country_id'])) $query->andWhere(['landing_views.geo_id' => $filters['country_id']['value']]);

        if (!empty($date_range)) {
            $query->andWhere(['>', '`landing_views`.date', $date_range['start']]);
            $query->andWhere(['<', '`landing_views`.date', $date_range['end']]);
        }

        $landing_data = $query
            ->groupBy('landing_views.geo_id')
            ->asArray()
            ->all();

        return ArrayHelper::index($landing_data, 'geo_name');
    }

//    /**
//     * @param $rows
//     * @return array
//     */
//    public function getTotalRow($rows)
//    {
//        if (empty($rows)) return [];
//
//        $total = [];
//        $calculating = new Statistics();
//        foreach ($rows as $order) {
//
//            unset($order['geo_id']);
//            unset($order['geo_name']);
//            unset($order['iso']);
//
//            foreach ($order as $key => $row) {
//                if (isset($total[$key])) {
//                    $total[$key] += $row;
//                } else {
//                    $total[$key] = $row;
//                }
//            }
//        }
//
//        $calculating->setAttributes($total);
//        $calculating->setCalculatedAttributes();
//        $total = $calculating->getAttributes();
//
////        $total['cr'] = !empty($total['views']) ? $total['total'] / $total['views'] * 100 : 0;
////        $total['cs'] = !empty($total['views']) ? $total['success_delivery'] / $total['views'] * 100 : 0;
////        $total['sr'] = !empty($total['total']) ? $total['success_delivery'] / $total['total'] * 100 : 0;
////        $total['pr'] = !empty($total['total']) ? (1 - ($total['pending']) / $total['total']) * 100 : 0;
////        $total['nr'] = !empty($total['total']) ? ($total['not_valid'] + $total['not_valid_checked']) / $total['total'] * 100 : 0;
////        $total['ar'] = !empty($total['total']) ? ($total['waiting_for_delivery'] + $total['delivery_in_progress'] + $total['success_delivery'] + $total['not_paid']) / $total['total'] * 100 : 0;
//
////        $count = count($rows);
////
////        $total['cr'] = $total['cr'] / $count;
////        $total['cs'] = $total['cs'] / $count;
////        $total['sr'] = $total['sr'] / $count;
////        $total['nr'] = $total['nr'] / $count;
//
//        return $total;
//    }

    public function normalizeGeo($offer_id = null, $geo_id = null, $advert_id = null, $target = null, $wm_id = null)
    {
        $statistics = new Statistics();

        $query = TargetAdvert::find()
            ->select([
                'geo.geo_id',
                'geo.geo_name',
                'geo.iso',
            ])
            ->join('LEFT JOIN', 'target_advert_group', 'target_advert_group.target_advert_group_id = target_advert.target_advert_group_id')
            ->join('LEFT JOIN', 'advert_offer_target', 'target_advert_group.advert_offer_target_id = advert_offer_target.advert_offer_target_id')
            ->join('LEFT JOIN', 'geo', 'geo.geo_id = advert_offer_target.geo_id')
            ->join('LEFT JOIN', 'wm_offer_target', 'wm_offer_target.geo_id = geo.geo_id')
            ->join('LEFT JOIN', 'target_wm_group', 'target_wm_group.wm_offer_target_id = wm_offer_target.wm_offer_target_id')
            ->join('RIGHT JOIN', 'target_wm', 'target_wm.target_wm_group_id = target_wm_group.target_wm_group_id');

        if (!empty($offer_id)) $query->andWhere(['advert_offer_target.offer_id' => $offer_id]);
        if (!empty($advert_id)) $query->andWhere(['target_advert.advert_id' => $advert_id]);
        if (!empty($geo_id)) $query->andWhere(['advert_offer_target.geo_id' => $geo_id]);
        if (!empty($target)) $query->andWhere(['advert_offer_target.advert_offer_target_status' => $target]);
        if (!empty($wm_id)) $query->andWhere(['target_wm.wm_id' => $wm_id]);

        $geo_list = $query
            ->groupBy('advert_offer_target.geo_id')
            ->asArray()
            ->all();

        $zero = [];
        foreach ($geo_list as $k => $value) {
            $zero[$value['geo_id']] = array_merge(
                [
                    'geo_id' => $value['geo_id'],
                    'geo_name' => $value['geo_name'],
                    'iso' => $value['iso']
                ],
                $statistics->getAttributes()
            );
        }

        return $zero;
    }
}