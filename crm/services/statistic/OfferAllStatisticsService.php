<?php

namespace crm\services\statistic;

use Yii;
use common\models\LandingViews;
use common\models\offer\targets\advert\TargetAdvert;
use common\models\order\Order;
use common\models\order\OrderStatus;
use common\models\order\OrderView;
use common\models\statistics\Statistics;
use common\modules\user\models\tables\User;
use yii\helpers\ArrayHelper;

/**
 * Class OfferAllStatisticsService
 * @package crm\services\statistic;
 */
class OfferAllStatisticsService extends Statistics
{
    public $owner_id;
    public $offers;
    public $total;

    /**
     * OfferAllStatisticsService constructor.
     * @param array $filters
     * @param null $sort_field
     * @param null $sort_order
     */
    public function __construct($filters = [], $sort_field = null, $sort_order = null)
    {
        $this->owner_id = Yii::$app->user->identity->getOwnerId();
        $this->offers = $this->getAllStatistics($filters, $sort_field, $sort_order);
        $this->total = $this->getAllStatisticsTotalRow($this->offers);
        parent::__construct();
    }

    /**
     * @param array $filters
     * @param null $sort_field
     * @param null $sort_order
     * @return array|LandingViews[]|\common\models\offer\OfferProduct[]|OrderView[]|\yii\db\ActiveRecord[]
     */
    public function getAllStatistics($filters = [], $sort_field = null, $sort_order = null)
    {
        $offer_id = null;
        $vu_countries = [];
        $advert_id = null;
        $vu_wm_id = null;
        $target = null;

        $query = $this->statisticsQuery();
        if (isset($filters['filters']['advert_id'])) {
            $query->andWhere(['target_advert.advert_id' => $filters['filters']['advert_id']['value']]);
            $advert_id = $filters['filters']['advert_id']['value'];
        }
        if (isset($filters['filters']['offer_id'])) {
            $query->andWhere(['order.offer_id' => $filters['filters']['offer_id']['value']]);
            $offer_id = $filters['filters']['offer_id']['value'];
        }

        if (isset($filters['filters']['country_id'])) {
//            var_dump(count($filters['filters']['country_id']));exit;
//            if (count($filters['filters']['country_id']['value']) == 1){
//                $query->addSelect([
//                    "currency.currency_code"
//                ]);
//                $query->join('LEFT JOIN', 'currency', 'currency.country_id = advert_offer_target.geo_id');
//            }
            $query->andWhere(['advert_offer_target.geo_id' => $filters['filters']['country_id']['value']]);
            $vu_countries = $filters['filters']['country_id']['value'];
        }

        if (isset($filters['filters']['advert_target_id'])) {
//            $query->andWhere(['advert_offer_target.geo_id' => $filters['country_id']['value']]);
            $query->andWhere(['advert_offer_target.advert_offer_target_status' => $filters['filters']['advert_target_id']['value']]);
            $target = $filters['filters']['advert_target_id']['value'];
        }

        if (isset($filters['filters']['wm_id'])) {
            if ($filters['filters']['wm_id']['value'] == User::getChepollyNo_WmId()->id) {
                $vu_wm_id = null;
            } else {
                $vu_wm_id = $filters['filters']['wm_id']['value'];
            }

            $query->andWhere(['target_wm.wm_id' => $filters['filters']['wm_id']['value']]);

        }

        if (isset($filters['filters']['active'])) $query->andWhere(['target_advert.active' => $filters['filters']['active']['value']]);
        if (isset($filters['api'])) $query->andWhere(['order_stickers.sticker_id' => $filters['api']['value']]);

        if (isset($filters['filters']['date'])) {
            $start = new \DateTime($filters['filters']['date']['start']);
            $start->setTime(0, 0, 0);
            $start_date = $start->format('Y-m-d H:i:s');

            $end = new \DateTime($filters['filters']['date']['end']);
            $end->setTime(23, 59, 59);
            $end_date = $end->format('Y-m-d H:i:s');

            $query->andWhere(['>', 'order.created_at', $start_date]);
            $query->andWhere(['<', 'order.created_at', $end_date]);

            $vu_date = [
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

            $query->andWhere(['>', 'order.created_at', $start_date]);
            $query->andWhere(['<', 'order.created_at', $end_date]);

            $vu_date = [
                'start' => $start_date,
                'end' => $end_date,
            ];
        }
    
        $cross_sale_pcs_count_by_date = (clone $query)
            ->select(['offer.offer_id', 'count(*) as `cross_sale_pcs`'])
            ->andWhere(['target_advert_sku.use_extended_rules' => 1])
            ->andWhere('order_sku.order_id = `order`.order_id')
            ->andWhere(['order.order_status' => [
                OrderStatus::WAITING_DELIVERY,
                OrderStatus::DELIVERY_IN_PROGRESS,
                OrderStatus::SUCCESS_DELIVERY]])
            ->leftJoin('target_advert_sku', 'target_advert_sku.target_advert_id = order.target_advert_id')
            ->leftJoin('order_sku', 'target_advert_sku.sku_id = order_sku.sku_id')
            ->groupBy('offer_id')
            ->asArray()
            ->all();
    
        $cross_sale_pcs = [];
        foreach ($cross_sale_pcs_count_by_date as $row) {
            $cross_sale_pcs[$row['offer_id']] = $row['cross_sale_pcs'];
        }
        
        $statistics = $query
            ->groupBy('order.offer_id')
            ->asArray()
            ->all();

//        var_dump($statistics->createCommand()->rawSql);die;

        $statistics = ArrayHelper::index($statistics, 'offer_id');
        $zeroOffers = $this->normalizeOffers($offer_id, $vu_countries, $advert_id, $target, $vu_wm_id);
        $landing_data = $this->getViewsUniques($vu_countries, $vu_date, $vu_wm_id);

        $calculating = new Statistics();
        foreach ($statistics as $key => $offer) {
            $statistics[$key]['views'] = 0;
            $statistics[$key]['uniques'] = 0;
            $statistics[$key]['cr'] = 0;
            $statistics[$key]['cs'] = 0;
            $statistics[$key]['sr'] = 0;
            $statistics[$key]['pr'] = 0;
            $statistics[$key]['nr'] = 0;
            $statistics[$key]['ar'] = 0;
            $offer['cross_sale_pcs'] = $cross_sale_pcs[$offer['offer_id']] ?? 0;
            $offer['up_sale_pcs'] = $offer['sum_pcs_wds'] - $offer['cross_sale_pcs'] - $offer['total_wds'];
    
            if (isset($landing_data[$offer['offer_name']])) {
                $calculating->setAttributes(array_merge($landing_data[$offer['offer_name']], $offer));
                $calculating->setCalculatedAttributes();
                $statistics[$key] = $offer + $calculating->getAttributes();
            }
            
            foreach ($offer as $params => $values) {
                if ($params !== 'offer_name' && $params !== 'date') {
                    $statistics[$key][$params] = (int)$values;
                }
            }
        }

        if ($filters['viewZeroOffers'] == true) {
            $result = empty($statistics) ? $zeroOffers : $statistics + $zeroOffers;
        } else {
            $result = $statistics;
        }

        $sort_order = ($sort_order == -1) ? SORT_DESC : SORT_ASC;

        if (isset($sort_field)) {
            ArrayHelper::multisort($result, $sort_field, $sort_order);
        } else {
            ArrayHelper::multisort($result, 'offer_id', SORT_ASC);
        }

        return $result;
    }


//    /**
//     * @param $rows
//     * @return array
//     */
//    public function getAllStatisticsTotalRow($rows)
//    {
//        if (empty($rows)) return [];
//
//        $total = [];
//        $calculating = new Statistics();
//        foreach ($rows as $offer) {
//
//            unset($offer['offer_id']);
//            unset($offer['offer_name']);
//            unset($offer['currency_code']);
//
//            foreach ($offer as $key => $row) {
//                if (isset($total[$key])) {
//                    $total[$key] += $row;
//                } else {
//                    $total[$key] = $row;
//                }
//            }
//
//        }
//
//        $calculating->setAttributes($total);
//        $calculating->setCalculatedAttributes();
//        $total = $calculating->getAttributes();
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
                '`order`.`offer_id`',
                '`offer`.`offer_name`',
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
     * @param array $geo_id
     * @param array $date
     * @param null $wm_id
     * @return array
     */
    private function getViewsUniques($geo_id = [], $date = [], $wm_id = null)
    {
        $query = LandingViews::find()
            ->select([
                "`offer`.`offer_name`",
                "SUM(views) as views",
                "SUM(uniques) as uniques"
            ])
            ->join('LEFT JOIN', 'offer', 'landing_views.offer_id = offer.offer_id')
            ->join('LEFT JOIN', 'flow', 'landing_views.flow_id = flow.flow_id');

        if (isset($wm_id)) $query->andWhere(['flow.wm_id' => $wm_id]);
        if (!empty($geo_id)) $query->andWhere(['landing_views.geo_id' => $geo_id]);
        if (!empty($date)) {
            $query->andWhere(['>', '`landing_views`.date', $date['start']]);
            $query->andWhere(['<', '`landing_views`.date', $date['end']]);
        }

        $landing_data = $query
            ->groupBy('`offer_name`')
            ->asArray()
            ->all();

        return ArrayHelper::index($landing_data, 'offer_name');
    }

    public function normalizeOffers($offer_id = null, $geo_id = null, $advert_id = null, $target = null, $wm_id = null)
    {
        $statistics = new Statistics();

        $query = TargetAdvert::find()
            ->select([
                'offer.offer_id',
                'offer.offer_name',
            ])
            ->join('LEFT JOIN', 'target_advert_group', 'target_advert_group.target_advert_group_id = target_advert.target_advert_group_id')
            ->join('LEFT JOIN', 'advert_offer_target', 'target_advert_group.advert_offer_target_id = advert_offer_target.advert_offer_target_id')
            ->join('LEFT JOIN', 'offer', 'offer.offer_id = advert_offer_target.offer_id')
            ->join('LEFT JOIN', 'wm_offer_target', 'wm_offer_target.offer_id = offer.offer_id')
            ->join('LEFT JOIN', 'target_wm_group', 'target_wm_group.wm_offer_target_id = wm_offer_target.wm_offer_target_id')
            ->join('RIGHT JOIN', 'target_wm', 'target_wm.target_wm_group_id = target_wm_group.target_wm_group_id');

        if (!empty($offer_id)) $query->andWhere(['advert_offer_target.offer_id' => $offer_id]);
        if (!empty($advert_id)) $query->andWhere(['target_advert.advert_id' => $advert_id]);
        if (!empty($geo_id)) $query->andWhere(['advert_offer_target.geo_id' => $geo_id]);
        if (!empty($target)) $query->andWhere(['advert_offer_target.advert_offer_target_status' => $target]);
        if (!empty($wm_id)) $query->andWhere(['target_wm.wm_id' => $wm_id]);

        $offer_list = $query
            ->groupBy('offer.offer_id')
            ->asArray()
            ->all();

        $zero = [];
        foreach ($offer_list as $k => $value) {
            $zero[$value['offer_id']] = array_merge(
                [
                    'offer_id' => $value['offer_id'],
                    'offer_name' => $value['offer_name']
                ],
                $statistics->getAttributes()
            );
        }

        return $zero;
    }

}