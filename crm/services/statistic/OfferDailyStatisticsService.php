<?php

namespace crm\services\statistic;

use common\models\LandingViews;
use common\models\offer\targets\advert\TargetAdvert;
use common\models\order\Order;
use common\models\order\OrderStatus;
use common\models\order\OrderView;
use common\models\statistics\Statistics;
use common\modules\user\models\tables\User;
use Yii;
use yii\helpers\ArrayHelper;

class OfferDailyStatisticsService extends Statistics
{
    public $offers;
    public $total;
    public $hourly;
    public $owner_id;
    public $permissions;
    
    /**
     * OfferDailyStatisticsService constructor.
     * @param array $filters
     */
    public function __construct($filters = [])
    {
        $this->owner_id = Yii::$app->user->identity->getOwnerId();
        $this->offers = $this->getStatistics($filters);
        $this->total = $this->getAllStatisticsTotalRow($this->offers);
        $this->hourly = $this->getHourlyStatistics($filters);
        parent::__construct();
    }

    private function statisticsQuery()
    {
        $approved_statuses = OrderStatus::sqlFormatFindByOrderStatuses([
            OrderStatus::WAITING_DELIVERY,
            OrderStatus::DELIVERY_IN_PROGRESS,
            OrderStatus::SUCCESS_DELIVERY,
            OrderStatus::CANCELED,
            OrderStatus::RETURNED,
            OrderStatus::NOT_PAID]);
    
        $wds_statuses = OrderStatus::sqlFormatFindByOrderStatuses([
            OrderStatus::WAITING_DELIVERY,
            OrderStatus::DELIVERY_IN_PROGRESS,
            OrderStatus::SUCCESS_DELIVERY]);
        
        $query = Order::find()
            ->select([
                "DATE_FORMAT(`order`.created_at, '%d.%m.%Y') as date",
                'SUM(if(`order`.order_status = ' . OrderStatus::PENDING . ', 1, 0 )) AS `pending`',
                'SUM(if(`order`.order_status = ' . OrderStatus::BACK_TO_PENDING . ', 1, 0 )) AS `back_to_pending`',
                'SUM(if(`order`.order_status = ' . OrderStatus::WAITING_DELIVERY . ', 1, 0 )) AS `waiting_for_delivery`',
                'SUM(if(`order`.order_status = ' . OrderStatus::DELIVERY_IN_PROGRESS . ', 1, 0 )) AS `delivery_in_progress`',
                'SUM(if(`order`.order_status = ' . OrderStatus::SUCCESS_DELIVERY . ', 1, 0 )) AS `success_delivery`',
                'SUM(if(`order`.order_status = ' . OrderStatus::CANCELED . ', 1,0 )) AS `canceled`',
                'SUM(if(`order`.order_status = ' . OrderStatus::REJECTED . ', 1,0 )) AS `rejected`',
                'SUM(if(`order`.order_status = ' . OrderStatus::NOT_VALID . ', 1,0 )) AS `not_valid`',
                'SUM(if(`order`.order_status = ' . OrderStatus::NOT_VALID_CHECKED . ', 1,0 )) AS `not_valid_checked`',
                'SUM(if(`order`.order_status = ' . OrderStatus::NOT_PAID . ', 1,0 )) AS `not_paid`',
                'SUM(if(`order`.order_status = ' . OrderStatus::RETURNED . ', 1,0 )) AS `returned`',
                'SUM(if(`order`.order_status in ' . $approved_statuses . ', 1, 0 )) AS `approved`',
                'SUM(if(`order`.order_status in ' . $wds_statuses . ' , 1, 0)) AS total_wds',
                'SUM(CASE WHEN `order`.order_status in ' . $wds_statuses . ' THEN `order`.total_amount ELSE 0 END) AS sum_pcs_wds',
                'SUM(if(`order`.total_amount > 1 and `order`.order_status in ' . $approved_statuses . ', 1, 0)) AS up_sale_pcs_total_approved',
                'SUM(if(`order`.total_amount > 1, 1, 0)) AS up_sale_pcs_total',
                'COUNT(*) as total'])
            ->join('LEFT JOIN', 'order_data', 'order_data.order_id = `order`.order_id')
            ->join('LEFT JOIN', 'target_wm', 'target_wm.target_wm_id = `order`.target_wm_id')
            ->join('LEFT JOIN', 'target_advert', '`order`.target_advert_id = target_advert.target_advert_id')
            ->join('LEFT JOIN', 'target_advert_group', 'target_advert_group.target_advert_group_id = target_advert.target_advert_group_id')
            ->join('LEFT JOIN', 'advert_offer_target', 'target_advert_group.advert_offer_target_id = advert_offer_target.advert_offer_target_id')
            ->join('LEFT JOIN', 'offer', 'advert_offer_target.offer_id = offer.offer_id')
            ->join('LEFT JOIN', 'order_stickers', 'order_stickers.order_id = `order`.order_id')
            ->where(['order.deleted' => 0]);

        if ($this->owner_id !== null) {
            $query->andWhere(['order_data.owner_id' => $this->owner_id]);
        }

        return $query;
    }

    /**
     * @param array $filters
     * @return array|LandingViews[]|TargetAdvert[]|\common\models\offer\targets\wm\TargetWm[]|OrderView[]|\yii\db\ActiveRecord[]
     */
    public function getStatistics($filters = [])
    {
        $vu_countries = [];
        $vu_offer = [];
        $vu_date = [];
        $vu_wm_id = null;

        $query = $this->statisticsQuery();

        if (isset($filters['advert_id'])) {
            $query->andWhere(['order_data.owner_id' => $filters['advert_id']['value']]);
            $vu_offer = $this->getAdvertOffers($filters['advert_id']['value']);
            if (!isset($filters['country_id'])) $vu_countries = $this->getAdvertGeo($filters['advert_id']);
        }

        if (isset($filters['offer_id'])) $query->andWhere(['order.offer_id' => $filters['offer_id']['value']]);
        if (isset($filters['flow_id'])) $query->andWhere(['order.flow_id' => $filters['flow_id']['value']]);
        if (isset($filters['advert_target_id'])) $query->andWhere(['advert_offer_target.advert_offer_target_status' => $filters['advert_target_id']['value']]);

        if (isset($filters['country_id'])) {
            $query->andWhere(['advert_offer_target.geo_id' => $filters['country_id']['value']]);
            $vu_countries = $filters['country_id']['value'];
        }

        if (isset($filters['webmaster'])) {
            if ($filters['webmaster']['value'] == User::getChepollyNo_WmId()->id) {
                $query->andWhere(['target_wm.wm_id' => null]);
                $query->orWhere(['target_wm.wm_id' => User::getChepollyNo_WmId()->id]);
                $vu_wm_id = null;
            } else {
                $query->andWhere(['target_wm.wm_id' => $filters['webmaster']['value']]);
                $vu_wm_id = $filters['webmaster']['value'];
            }

        }

        if (isset($filters['active'])) $query->andWhere(['target_advert.active' => $filters['active']['value']]);
        if (isset($filters['api'])) $query->andWhere(['order_stickers.sticker_id' => $filters['api']['value']]);

        if (isset($filters['date'])) {
            $start = new \DateTime($filters['date']['start']);
            $start->setTime(0, 0, 0);
            $start_date = $start->format('Y-m-d H:i:s');

            $end = new \DateTime($filters['date']['end']);
            $end->setTime(23, 59, 59);
            $end_date = $end->format('Y-m-d H:i:s');

            $query->andWhere(['>', '`order`.created_at', $start_date]);
            $query->andWhere(['<', '`order`.created_at', $end_date]);

            $vu_date = [
                'start' => $start_date,
                'end' => $end_date,
            ];
        }
    
        $cross_sale_pcs_count_by_date = (clone $query)
            ->select(["DATE_FORMAT(`order`.created_at, '%d.%m.%Y') as date", 'count(*) as `cross_sale_pcs`'])
            ->andWhere(['target_advert_sku.use_extended_rules' => 1])
            ->andWhere('order_sku.order_id = `order`.order_id')
            ->andWhere(['order.order_status' => [
                OrderStatus::WAITING_DELIVERY,
                OrderStatus::DELIVERY_IN_PROGRESS,
                OrderStatus::SUCCESS_DELIVERY]])
            ->leftJoin('target_advert_sku', 'target_advert_sku.target_advert_id = order.target_advert_id')
            ->leftJoin('order_sku', 'target_advert_sku.sku_id = order_sku.sku_id')
            ->groupBy('`date`')
            ->asArray()
            ->all();
    
        $cross_sale_pcs = [];
        foreach ($cross_sale_pcs_count_by_date as $row) {
            $cross_sale_pcs[$row['date']] = $row['cross_sale_pcs'];
        }
        
        $statistics = $query
            ->groupBy('`date`')
            ->orderBy(['`order`.created_at' => SORT_DESC])
            ->asArray()
            ->all();
    
        $landing_data = $this->getViewsUniques($vu_offer, $vu_wm_id, $vu_countries, $vu_date);
        $calculating = new Statistics();
        
        foreach ($statistics as $key => $offer) {
            $offer['cross_sale_pcs'] = $cross_sale_pcs[$offer['date']] ?? 0;
            $offer['up_sale_pcs'] = $offer['sum_pcs_wds'] - $offer['cross_sale_pcs'] - $offer['total_wds'];
    
            if (isset($landing_data[$offer['date']])) {
                $calculating->setAttributes(array_merge($landing_data[$offer['date']], $offer));
                $calculating->setCalculatedAttributes();
                $statistics[$key] = $offer + $calculating->getAttributes();
            } else {
                $calculating->setAttributes($offer);
                $calculating->setCalculatedAttributes();
                $statistics[$key] = $offer + $calculating->getAttributes();
            }
    
            //$statistics[$key]['cr'] = !empty($statistics[$key]['views']) ? ($offer['total'] / $statistics[$key]['views']) * 100 : 0;
            //$statistics[$key]['cs'] = !empty($statistics[$key]['views']) ? ($offer['success_delivery'] / $statistics[$key]['views']) * 100 : 0;
            //$statistics[$key]['sr'] = !is_null($offer['total']) ? ($offer['success_delivery'] / $offer['total']) * 100 : 0;
            //$statistics[$key]['pr'] = !is_null($offer['total']) ? (1 - ($offer['pending']) / $offer['total']) * 100 : 0;
            //$statistics[$key]['nr'] = !is_null($offer['total']) ? ($offer['not_valid'] / $offer['total']) * 100 : 0;
            //$statistics[$key]['ar'] = !is_null($offer['total']) ? ($offer['waiting_for_delivery'] + $offer['delivery_in_progress'] + $offer['not_paid']) / $offer['total'] * 100 : 0;
            
            foreach ($offer as $params => $values) {
                if ($params !== 'offer_name' && $params !== 'date') {
                    $statistics[$key][$params] = (int)$values;
                }
            }
        }
    
        //usort($statistics, function ($a, $b) {
        //    return (strtotime($a['date']) < strtotime($b['date']));
        //});

        return $statistics;
    }

    /**
     * @return array
     */
    public function hoursMatrix()
    {
        return [
            '00:00' => '00:00',
            '01:00' => '01:00',
            '02:00' => '02:00',
            '03:00' => '03:00',
            '04:00' => '04:00',
            '05:00' => '05:00',
            '06:00' => '06:00',
            '07:00' => '07:00',
            '08:00' => '08:00',
            '09:00' => '09:00',
            '10:00' => '10:00',
            '11:00' => '11:00',
            '12:00' => '12:00',
            '13:00' => '13:00',
            '14:00' => '14:00',
            '15:00' => '15:00',
            '16:00' => '16:00',
            '17:00' => '17:00',
            '18:00' => '18:00',
            '19:00' => '19:00',
            '20:00' => '20:00',
            '21:00' => '21:00',
            '22:00' => '22:00',
            '23:00' => '23:00',
        ];
    }

    /**
     * @param array $filters
     * @return array
     */
    public function getHourlyStatistics($filters = [])
    {
        $query = $this->hourlyQuery();

        if (!is_null($this->owner_id)) $query->where(['order_data.owner_id' => $this->owner_id]);
        $query->andWhere(['`order`.deleted' => 0]);

        $vu_date = [];
        if (isset($filters['date'])) {
            $start = new \DateTime($filters['date']['end']);
            $start->setTime(0, 0, 0);
            $start_date = $start->format('Y-m-d H:i:s');

            $end = new \DateTime($filters['date']['end']);
            $end->setTime(23, 59, 59);
            $end_date = $end->format('Y-m-d H:i:s');

            $query->andWhere(['>', '`order`.created_at', $start_date]);
            $query->andWhere(['<', '`order`.created_at', $end_date]);

            $vu_date = [
                'start' => $start_date,
                'end' => $end_date,
            ];
        }

        $statistics = $query
            ->groupBy('date')
            ->asArray()
            ->all();

        $statistics = ArrayHelper::index($statistics, 'date');

        $landing_data = $this->getHourlyViews($vu_date);
        $hours = $this->hoursMatrix();

        $total = [];
        $success_delivery = [];
        $views = [];
        $uniques = [];
        foreach ($hours as $h => $hour) {

            $total[$hour] = 0;
            $success_delivery[$hour] = 0;
            $views[$hour] = 0;
            $uniques[$hour] = 0;

            foreach ($statistics as $key => $order) {

                $total[$key] = 0;
                $views[$key] = 0;
                $uniques[$key] = 0;
                $success_delivery[$key] = 0;

                if (isset($statistics[$key])) {
                    $total[$key] = $statistics[$key]['total'];
                    $success_delivery[$key] = $statistics[$key]['success_delivery'];
                }

                if (isset($landing_data[$key])) {
                    $views[$key] = $landing_data[$key]['views'];
                    $uniques[$key] = $landing_data[$key]['uniques'];
                }
            }
        }

        ksort($total);
        ksort($success_delivery);
        ksort($views);
        ksort($uniques);

        return [
            'total' => array_values($total),
            'views' => array_values($views),
            'unique' => array_values($uniques),
            'success_delivery' => array_values($success_delivery),
        ];
    }

    /**
     * @param array $date_range
     * @return array
     */
    public function getHourlyViews($date_range = [])
    {
        $query = LandingViews::find()
            ->select([
                "DATE_FORMAT(date, \"%H:00\") AS index_date",
                "SUM(views) as views",
                "SUM(uniques) as uniques"
            ]);

        if (!empty($date_range)) {
            $query->andWhere(['>', 'landing_views.date', $date_range['start']]);
            $query->andWhere(['<', 'landing_views.date', $date_range['end']]);
        }

        $landing_data = $query->groupBy('`index_date`')->asArray()->all();

        return ArrayHelper::index($landing_data, 'index_date');
    }

    /**
     * @param $offer
     * @param null $wm_id
     * @param array $geo_id
     * @param array $date_range
     * @return array
     */
    protected function getViewsUniques($offer = [], $wm_id = null, $geo_id = [], $date_range = [])
    {
        $query = LandingViews::find()
            ->select([
                "DATE_FORMAT(landing_views.date, '%d.%m.%Y') as `day_date`",
                "SUM(views) as views",
                "SUM(uniques) as uniques"
            ])
            ->join('LEFT JOIN', 'flow', 'landing_views.flow_id = flow.flow_id');
//            ->where("DATE_FORMAT(landing_views.date, '%Y-%m-%d') = DATE_FORMAT('" . $day_date . "', '%Y-%m-%d')");

        if (!empty($offer)) $query->andWhere(['landing_views.offer_id' => $offer]);

        if (isset($wm_id)) $query->andWhere(['flow.wm_id' => $wm_id]);
        if (!empty($geo_id)) $query->andWhere(['landing_views.geo_id' => $geo_id]);

        if (!empty($date_range)) {
            $query->andWhere(['>', 'landing_views.date', $date_range['start']]);
            $query->andWhere(['<', 'landing_views.date', $date_range['end']]);
        }

        $landing_data = $query
            ->groupBy('`day_date`')
            ->asArray()
            ->all();

        return ArrayHelper::index($landing_data, 'day_date');
    }

    /**
     * @param $advert_id
     * @return array
     */
    protected function getAdvertGeo($advert_id)
    {
        $query = TargetAdvert::find()
            ->select('advert_offer_target.geo_id')
            ->join('LEFT JOIN', 'target_advert_group', 'target_advert_group.target_advert_group_id = target_advert.target_advert_group_id')
            ->join('LEFT JOIN', 'advert_offer_target', 'target_advert_group.advert_offer_target_id = advert_offer_target.advert_offer_target_id')
            ->where(['target_advert.advert_id' => $advert_id])
            ->asArray()
            ->all();

        return ArrayHelper::getColumn($query, 'geo_id');
    }

//    public function statisticsQuery()
//    {
//        $query = Order::find()
//            ->select([
//                "DATE_FORMAT(`order`.created_at, '%d.%m.%Y') as `date`",
//                "SUM(if(`order`.order_status = " . OrderStatus::PENDING . ", 1, 0 )) AS `pending`",
//                "SUM(if(`order`.order_status = " . OrderStatus::BACK_TO_PENDING . ", 1, 0 )) AS `back_to_pending`",
//                "SUM(if(`order`.order_status = " . OrderStatus::WAITING_DELIVERY . ", 1, 0 )) AS `waiting_for_delivery`",
//                "SUM(if(`order`.order_status = " . OrderStatus::DELIVERY_IN_PROGRESS . ", 1, 0 )) AS `delivery_in_progress`",
//                "SUM(if(`order`.order_status = " . OrderStatus::SUCCESS_DELIVERY . ", 1, 0 )) AS `success_delivery`",
//                "SUM(if(`order`.order_status = " . OrderStatus::CANCELED . ", 1,0 )) AS `canceled`",
//                "SUM(if(`order`.order_status = " . OrderStatus::REJECTED . ", 1,0 )) AS `rejected`",
//                "SUM(if(`order`.order_status = " . OrderStatus::NOT_VALID . ", 1,0 )) AS `not_valid`",
//                "SUM(if(`order`.order_status = " . OrderStatus::NOT_VALID_CHECKED . ", 1,0 )) AS `not_valid_checked`",
//                "SUM(if(`order`.order_status = " . OrderStatus::NOT_PAID . ", 1,0 )) AS `not_paid`",
//                "SUM(if(`order`.order_status = " . OrderStatus::RETURNED . ", 1,0 )) AS `returned`",
//                "COUNT(*) as total"
//            ])
//            ->join('LEFT JOIN', 'order_data', 'order_data.order_id = order.order_id')
//            ->join('LEFT JOIN', 'target_wm', 'target_wm.target_wm_id = `order`.target_wm_id')
//            ->join('LEFT JOIN', 'target_advert', '`order`.target_advert_id = target_advert.target_advert_id')
//            ->join('LEFT JOIN', 'target_advert_group', 'target_advert_group.target_advert_group_id = target_advert.target_advert_group_id')
//            ->join('LEFT JOIN', 'advert_offer_target', 'target_advert_group.advert_offer_target_id = advert_offer_target.advert_offer_target_id')
//            ->join('LEFT JOIN', 'offer', 'advert_offer_target.offer_id = offer.offer_id');
//
//        if (!is_null($this->owner_id)) $query->where(['order_data.owner_id' => $this->owner_id]);
//        $query->andWhere(['`order`.deleted' => 0]);
//
//        return $query;
//    }

    public function hourlyQuery()
    {
        $query = Order::find()
            ->select([
                "DATE_FORMAT(`order`.created_at, \"%H:00\") as date",
                "SUM(if(`order`.order_status = " . OrderStatus::SUCCESS_DELIVERY . ", 1, 0 )) AS `success_delivery`",
                "COUNT(`order`.order_id) as total"
            ])
            ->join('LEFT JOIN', 'order_data', 'order_data.order_id = order.order_id');

        return $query;
    }

    /**
     * @param $rows
     * @return array
     */
//    public function getStatisticsTotalRow($rows)
//    {
//        if (empty($rows)) return [];
//
//        $total = [];
//        $calculating = new Statistics();
//        foreach ($rows as $offer) {
//
//            unset($offer['date']);
//
//            foreach ($offer as $key => $row) {
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
////        $total['sr'] = $total['success_delivery'] / $total['total'] * 100;
////        $total['pr'] = (1 - ($total['pending']) / $total['total']) * 100;
////        $total['nr'] = ($total['not_valid'] + $total['not_valid_checked']) / $total['total'] * 100;
////        $total['ar'] = ($total['waiting_for_delivery'] + $total['delivery_in_progress'] + $total['success_delivery'] + $total['not_paid']) / $total['total'] * 100;
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

    /**
     * @param $advert_id
     * @return array
     */
    public function getAdvertOffers($advert_id)
    {
        $offers = TargetAdvert::find()
            ->select('advert_offer_target.offer_id')
            ->join('LEFT JOIN', 'target_advert_group', ' target_advert_group.target_advert_group_id = target_advert.target_advert_group_id')
            ->join('LEFT JOIN', 'advert_offer_target', 'target_advert_group.advert_offer_target_id = advert_offer_target.advert_offer_target_id')
            ->where(['target_advert.advert_id' => $advert_id])
            ->groupBy('advert_offer_target.offer_id')
            ->asArray()
            ->all();

        return ArrayHelper::getColumn($offers, 'offer_id');
    }
}