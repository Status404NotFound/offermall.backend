<?php

namespace crm\services\statistic;

use Yii;
use common\models\offer\targets\advert\TargetAdvert;
use common\models\order\Order;
use common\models\order\OrderStatus;
use common\models\statistics\Statistics;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

class OfferAdvertStatistics extends Statistics
{
    public $offers;
    public $total;
    public $owner_id;

    /**
     * OfferAdvertStatistics constructor.
     * @param array $filters
     */
    public function __construct($filters = [])
    {
        $this->owner_id = Yii::$app->user->identity->getOwnerId();
        $this->offers = $this->getStatistics($filters);
        $this->total = $this->getAllStatisticsTotalRow($this->offers);

        parent::__construct();
    }

    /**
     * @param array $filters
     * @return array|Order[]
     */
    protected function getStatistics($filters = [])
    {
        $vu_geo_id = null;
        $vu_offer_id = null;
        $vu_date = [];

        $query = $this->statisticsQuery();

        if (isset($filters['offer_id'])) {
            $query->andWhere(['offer.offer_id' => $filters['offer_id']['value']]);
            $vu_offer_id = $filters['offer_id']['value'];
        }

        if (isset($filters['advert_id'])) $query->andWhere(['target_advert.advert_id' => $filters['advert_id']['value']]);
        if (isset($filters['advert_target_id'])) $query->andWhere(['advert_offer_target.advert_offer_target_status' => $filters['advert_target_id']['value']]);

        if (isset($filters['country_id'])) {
            $query->andWhere(['advert_offer_target.geo_id' => $filters['country_id']['value']]);
            $vu_geo_id = $filters['country_id']['value'];
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
            ->select(['target_advert.advert_id', 'count(*) as `cross_sale_pcs`'])
            ->andWhere(['target_advert_sku.use_extended_rules' => 1])
            ->andWhere('order_sku.order_id = `order`.order_id')
            ->andWhere(['order.order_status' => [
                OrderStatus::WAITING_DELIVERY,
                OrderStatus::DELIVERY_IN_PROGRESS,
                OrderStatus::SUCCESS_DELIVERY]])
            ->leftJoin('target_advert_sku', 'target_advert_sku.target_advert_id = order.target_advert_id')
            ->leftJoin('order_sku', 'target_advert_sku.sku_id = order_sku.sku_id')
            ->groupBy('advert_id')
            ->asArray()
            ->all();
    
        $cross_sale_pcs = [];
        foreach ($cross_sale_pcs_count_by_date as $row) {
            $cross_sale_pcs[$row['advert_id']] = $row['cross_sale_pcs'];
        }

        $statistics = $query
            ->groupBy('`user`.id')
            ->asArray()
            ->all();

        $vu_data = $this->getViewsUniques($vu_offer_id, $vu_geo_id, $vu_date);

        $calculating = new Statistics();
        foreach ($statistics as $key => $offer) {
            $statistics[$key]['views'] = 0;
            $statistics[$key]['unique'] = 0;
            $offer['cross_sale_pcs'] = $cross_sale_pcs[$offer['advert_id']] ?? 0;
            $offer['up_sale_pcs'] = $offer['sum_pcs_wds'] - $offer['cross_sale_pcs'] - $offer['total_wds'];
            
            if (isset($vu_data[$offer['advert_id']])) {
                $calculating->setAttributes(array_merge($vu_data[$offer['advert_id']], $offer));
                $calculating->setCalculatedAttributes();
                $statistics[$key] = $offer + $calculating->getAttributes();
            }

//            if (isset($vu_data[$advert_id])) {
//                $statistics[$key]['views'] = $vu_data[$advert_id]['views'];
//                $statistics[$key]['unique'] = $vu_data[$advert_id]['uniques'];
//            }

//            $statistics[$key]['cr'] = !empty($statistics[$key]['views']) ? ($offer['total'] / $statistics[$key]['views']) * 100 : 0;
//            $statistics[$key]['cs'] = !empty($statistics[$key]['views']) ? ($offer['success_delivery'] / $statistics[$key]['views']) * 100 : 0;
//            $statistics[$key]['sr'] = !is_null($offer['total']) ? ($offer['success_delivery'] / $offer['total']) * 100 : 0;
//            $statistics[$key]['pr'] = !is_null($offer['total']) ? (1 - ($offer['pending']) / $offer['total']) * 100 : 0;
//            $statistics[$key]['nr'] = !is_null($offer['total']) ? ($offer['not_valid'] / $offer['total']) * 100 : 0;
//            $statistics[$key]['ar'] = !is_null($offer['total']) ? ($offer['waiting_for_delivery'] + $offer['delivery_in_progress'] + $offer['not_paid']) / $offer['total'] * 100 : 0;
            foreach ($offer as $params => $values) {
                if ($params !== 'advert_name' && $params !== 'date') {
                    $statistics[$key][$params] = (int)$values;
                }
            }
        }

        return $statistics;

    }

//    /**
//     * @param $rows
//     * @return array
//     */
//    public function getStatisticsTotalRow($rows)
//    {
//        if (empty($rows)) return [];
//
//        $total = [];
//        $calculating = new Statistics();
//        foreach ($rows as $offer) {
//
//            unset($offer['advert_id']);
//            unset($offer['advert_name']);
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

    private function statisticsQuery()
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
        
        $query = Order::find()
            ->select([
                'target_advert.advert_id',
                '`user`.username as advert_name',
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
//                'SUM(CASE WHEN `order`.order_status = ' . OrderStatus::DELIVERY_IN_PROGRESS . ' THEN `order`.total_cost ELSE 0 END) AS `total_cost`',
                'SUM(CASE WHEN `order`.order_status = ' . OrderStatus::SUCCESS_DELIVERY . ' THEN `order`.total_cost ELSE 0 END) AS `total_cost`',
                'SUM(CASE WHEN `order`.order_status in ' . $wds_statuses . ' THEN `order`.total_amount ELSE 0 END) AS sum_pcs_wds',
                'SUM(if(`order`.total_amount > 1 and `order`.order_status in ' . $approved_statuses . ', 1, 0)) AS up_sale_pcs_total_approved',
                'SUM(if(`order`.total_amount > 1, 1, 0)) AS up_sale_pcs_total',
                'COUNT(*) as total'])
            //->join('LEFT JOIN', 'target_wm', 'target_wm.target_wm_id = `order`.target_wm_id')
            ->join('LEFT JOIN', 'target_advert', '`order`.target_advert_id = target_advert.target_advert_id')
            ->join('LEFT JOIN', 'user', 'user.id = target_advert.advert_id')
            ->join('LEFT JOIN', 'target_advert_group', 'target_advert_group.target_advert_group_id = target_advert.target_advert_group_id')
            ->join('LEFT JOIN', 'advert_offer_target', 'target_advert_group.advert_offer_target_id = advert_offer_target.advert_offer_target_id')
            ->join('LEFT JOIN', 'offer', 'advert_offer_target.offer_id = offer.offer_id')
            ->join('LEFT JOIN', 'order_stickers', 'order_stickers.order_id = `order`.order_id')
            ->andWhere(['`order`.deleted' => 0]);
    
        if ($this->owner_id !== null) {
            $query->andWhere(['order_data.owner_id' => $this->owner_id]);
        }

        return $query;
    }

    /**
     * @param null $offer_id
     * @param null $geo_id
     * @param array $date_range
     * @return array
     */
    private function getViewsUniques($offer_id = null, $geo_id = null, $date_range = [])
    {
        $query = TargetAdvert::find()
            ->select([
                "`user`.id as advert_id",
                "sum(landing_views.views) as views",
                "sum(landing_views.uniques) as uniques"
            ])
            ->join('LEFT JOIN', 'user', '`user`.`id` = target_advert.advert_id')
            ->join('LEFT JOIN', 'target_advert_group', 'target_advert_group.target_advert_group_id = target_advert.target_advert_group_id')
            ->join('LEFT JOIN', 'advert_offer_target', 'advert_offer_target.advert_offer_target_id = target_advert_group.advert_offer_target_id')
            ->join('LEFT JOIN', 'landing_views', ' advert_offer_target.offer_id = landing_views.offer_id');

        if (isset($geo_id)) {
            $query->where(['advert_offer_target.geo_id' => $geo_id]);
            $query->andWhere(['landing_views.geo_id' => $geo_id]);
        } else $query->where('landing_views.geo_id = advert_offer_target.geo_id');

        if (!empty($offer_id)) {
            $query->andWhere(['landing_views.offer_id' => $offer_id]);
        }

        if (!empty($date_range)) {
            $query->andWhere(['>', 'landing_views.date', $date_range['start']]);
            $query->andWhere(['<', 'landing_views.date', $date_range['end']]);
        }

        $landing_data = $query
            ->groupBy('`user`.`id`')
            ->asArray()
            ->all();

        return ArrayHelper::index($landing_data, 'advert_id');
    }
}