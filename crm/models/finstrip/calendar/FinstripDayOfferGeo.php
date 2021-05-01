<?php

namespace crm\models\finstrip\calendar;

use common\models\finance\KnownSubs;
use crm\models\finstrip\Finstrip;
use common\models\order\Order;
use common\models\LandingViews;
use crm\models\finstrip\DayOfferGeoSubCost;

/**
 * Class FinstripDayOfferGeo
 * @package crm\models\finstrip\calendar
 *
 * @property string $sub_id_1
 * @property string $known_sub_id_1
 * @property integer $known_sub_id
 *
 */
class FinstripDayOfferGeo extends Finstrip
{
    private $knownSubs;
    public $sub_id_1 = null;
    public $known_sub_id_1 = null;
    public $known_sub_id = null;

    /**
     * @return array
     */
    public function rules()
    {
        return array_merge(array_merge(parent::rules(), [
            [['known_sub_id'], 'integer'],
            [['sub_id_1', 'known_sub_id_1'], 'string'],
        ]));
    }

    /**
     * @param $filters
     * @return array
     */
    public function searchDayOfferGeo($filters): array
    {
        $this->knownSubs = KnownSubs::find()->distinct('alias')->indexBy('alias')->asArray()->all();

        $views = $this->viewsQuery($filters);
        $orders = $this->ordersQuery($filters);
        $subs = $this->subsQuery($filters);

        return $this->compareData($views, $orders, $subs);
    }

    /**
     * @param $views
     * @param $orders
     * @param $subs
     * @return array
     */
    private function compareData($views, $orders, $subs): array
    {
        $offer = [];
        $unknown_sources = [];

        foreach ($views as $view) {
            $isset_orders = false;
            foreach ($orders as $key => $order) {
                if ($view['sub_id_1'] == $order['sub_id_1']) {
                    $model = new self();
                    $model->setAttributes(array_merge($view, $order));
                    $model->setCalculatedAttributes();
                    if (!in_array($model->sub_id_1, array_keys($this->knownSubs)) || $model->sub_id_1 == 'unknown_source') {
                        $unknown_sources[] = $model->getAttributes(); // Setting calculated attributes;
                    } else {
                        $model->known_sub_id = $this->knownSubs[$model->sub_id_1]['id'];
                        $offer[] = $model->getAttributes(); // Setting calculated attributes;
                    }
                    $isset_orders = true;
                    unset($orders[$key]);
                }
            }
            if ($isset_orders === false) {
                $model = new self();
                $model->setAttributes($view);
                if (!in_array($model->sub_id_1, array_keys($this->knownSubs)) || $model->sub_id_1 == 'unknown_source') {
                    $unknown_sources[] = $model->getAttributes();;
                } else {
                    $model->known_sub_id = $this->knownSubs[$model->sub_id_1]['id'];
                    $offer[] = $model->getAttributes();;
                }
            }
        }

        if (!empty($orders)) {
            foreach ($orders as $order) {
                $model = new self();
                $model->setAttributes($order);
                $model->setCalculatedAttributes();

                $offer[] = $model->getAttributes(); // Setting calculated attributes
            }
        }

        foreach ($subs as $key => $day_offer_sub) {
            $isset_views = false;
            foreach ($offer as &$view) {
                if ($day_offer_sub['sub_id_1'] == $view['sub_id_1']) {
                    $model = new self();
                    $model->setAttributes(array_merge($view, $day_offer_sub));
                    $model->setCalculatedAttributes();
                    $view = $model->getAttributes();
                    $isset_views = true;
                }
            }
            if ($isset_views === false) {
                $model = new self();
                $model->setAttributes($day_offer_sub);
                if (!in_array($day_offer_sub['sub_id_1'], array_keys($this->knownSubs)) || $day_offer_sub['sub_id_1'] == 'unknown_source') {
                    $unknown_sources[] = $model->getAttributes();
                } else {
                    $model->known_sub_id = $this->knownSubs[$model->sub_id_1]['id'];
                    $offer[] = $model->getAttributes();
                }
            }
        }

        if (!empty($unknown_sources)) {
            $model = new self();
            foreach ($unknown_sources as $source) {
                $model->sub_id_1 = 'unknown_source';
                $model->known_sub_id_1 = 'unknown_source';
                $model->known_sub_id = 1;// Hardcode (id in DB of 'unknown_source')

                $model->views += $source['views'];
                $model->unique_views += $source['unique_views'];

                $model->total_orders_amount += $source['total_orders_amount'];
                $model->sd_orders_amount += $source['sd_orders_amount'];
                $model->dip_orders_amount += $source['dip_orders_amount'];
                $model->nv_orders_amount += $source['nv_orders_amount'];
                $model->achived_orders_amount += $source['achived_orders_amount'];
                $model->total_sku_amount += $source['total_sku_amount'];
                $model->dip_sku_amount += $source['dip_sku_amount'];
                $model->sd_sku_amount += $source['sd_sku_amount'];
                $model->achived_sku_amount += $source['achived_sku_amount'];
                $model->total_advert_commission += $source['total_advert_commission'];
                $model->total_traffic_cost += $source['total_traffic_cost'] ?? 0;
            }

            $model->setCalculatedAttributes();
            $offer[] = $model->getAttributes();
        }
        return $offer;
    }

    /**
     * @param $filters
     * @return array|Order[]
     */
    private function ordersQuery($filters)
    {
        $query = Order::find()
            ->select([
                'order_data.sub_id_1',
                '`known_sub_id_1`.alias AS known_sub_id_1',
                'customer.country_id',

                'count(`order`.order_id) AS total_orders_amount',
                'count(CASE `order`.order_status WHEN 100 THEN 1 ELSE NULL END) AS sd_orders_amount',
                'count(CASE `order`.order_status WHEN 50 THEN 1 ELSE NULL END) AS dip_orders_amount',
                'count(CASE `order`.order_status WHEN 1 OR 0 THEN 1 ELSE NULL END) AS nv_orders_amount',
//                'count(CASE WHEN `order`.order_status >= advert_offer_target.advert_offer_target_status THEN 1 ELSE NULL END) AS achived_orders_amount',
                'count(CASE 
                WHEN `order`.order_status >= advert_offer_target.advert_offer_target_status THEN 1 
                WHEN advert_offer_target.advert_offer_target_status = 40 and `order`.order_status IN '. $this->approved_statuses .' THEN 1 
                ELSE NULL 
                END) AS achived_orders_amount',

                'sum(`order`.total_amount) AS total_sku_amount',
                'sum(CASE WHEN `order`.order_status = 50 THEN `order`.total_amount ELSE 0 END) AS dip_sku_amount',
                'sum(CASE WHEN `order`.order_status = 100 THEN `order`.total_amount ELSE 0 END) AS sd_sku_amount',
//                'sum(CASE WHEN `order`.order_status >= advert_offer_target.advert_offer_target_status THEN `order`.total_amount ELSE 0 END) AS achived_sku_amount',
                'sum(CASE WHEN `order`.order_status >= advert_offer_target.advert_offer_target_status THEN `order`.total_amount 
                WHEN advert_offer_target.advert_offer_target_status = 40 and `order`.order_status IN '. $this->approved_statuses .' THEN `order`.total_amount 
                ELSE 0 
                END) AS achived_sku_amount',
//                'sum(CASE WHEN `order`.order_status >= advert_offer_target.advert_offer_target_status THEN `order`.usd_advert_commission ELSE 0 END) AS total_advert_commission',
                'sum(CASE WHEN `order`.order_status >= advert_offer_target.advert_offer_target_status THEN `order`.usd_advert_commission 
                WHEN advert_offer_target.advert_offer_target_status = 40 and `order`.order_status IN '. $this->approved_statuses .' THEN `order`.usd_advert_commission 
                ELSE 0 
                END) AS total_advert_commission',
            ])
            ->join('JOIN', 'order_data', 'order_data.order_id = `order`.order_id')
            ->join('LEFT JOIN', 'customer', 'customer.customer_id = `order`.customer_id')
            ->join('LEFT JOIN', '`known_sub_id_1`', '`known_sub_id_1`.alias = order_data.sub_id_1')
            ->join('LEFT JOIN', '`target_advert`', 'target_advert.target_advert_id = `order`.target_advert_id')
            ->join('LEFT JOIN', '`target_advert_group`', 'target_advert_group.target_advert_group_id = target_advert.target_advert_group_id')
            ->join('LEFT JOIN', '`advert_offer_target`', 'advert_offer_target.advert_offer_target_id = target_advert_group.advert_offer_target_id')
            ->join('LEFT JOIN', '`target_wm`', 'target_wm.target_wm_id = `order`.target_wm_id')
            ->where('DATE_FORMAT(`order`.created_at, "%d.%m.%Y") = "' . $filters['day']['value'] . '"')

            ->andWhere(['`order`.deleted' => 0])
            ->andWhere(['`order`.offer_id' => $filters['offer_id']['value']]);

        if (isset($filters['advert_id'])) $query->andWhere(['target_advert.advert_id' => $filters['advert_id']['value']]);
        if (isset($filters['advert_offer_target_status'])) $query->andWhere(['advert_offer_target.advert_offer_target_status' => $filters['advert_offer_target_status']['value']]);
        if (isset($filters['wm_id'])) $query->andWhere(['target_wm.wm_id' => $filters['wm_id']['value']]);

        if ($filters['geo_id']['value'] == null) {
            $query->join('JOIN', 'order_data', 'order_data.order_id = `order`.order_id');
            $query->andWhere('`order_data`.owner_id IS NULL');
        } else {
            $query->andWhere(['`customer`.country_id' => $filters['geo_id']['value']]);
        }

        $result = $query
            ->groupBy(['order_data.sub_id_1'])
            ->orderBy(['order_data.sub_id_1' => SORT_ASC])
            ->asArray()
            ->all();

        return $result;
    }

    /**
     * @param $filters
     * @return array|KnownSubs[]|LandingViews[]|DayOfferGeoSubCost[]|\yii\db\ActiveRecord[]
     */
    private function viewsQuery($filters)
    {
        $query = LandingViews::find()
            ->select([
                'landing_views.sub_id_1',
                '`known_sub_id_1`.alias AS known_sub_id_1',
                'IFNULL(sum(landing_views.views), 0) AS views',
                'IFNULL(sum(landing_views.uniques), 0) AS unique_views',
            ])
            ->join('LEFT JOIN', '`known_sub_id_1`', '`known_sub_id_1`.alias = landing_views.sub_id_1')
            ->where('DATE_FORMAT(date, "%d.%m.%Y") = "' . $filters['day']['value'] . '"')
            ->andWhere(['landing_views.offer_id' => $filters['offer_id']['value']]);
        $this->filterViews($query, $filters);

        if ($filters['geo_id']['value'] == null) {
            $query->andWhere('landing_views.geo_id NOT IN (SELECT `advert_offer_target`.geo_id FROM `advert_offer_target`WHERE `advert_offer_target`.offer_id = ' . $filters['offer_id']['value'] . ') ');
        } else {
            $query->andWhere(['landing_views.geo_id' => $filters['geo_id']['value']]);
        }

        $result = $query
            ->groupBy(['sub_id_1'])
            ->orderBy(['sub_id_1' => SORT_ASC])
            ->asArray()
            ->all();

        return $result;
    }

    /**
     * @param $filters
     * @return array|KnownSubs[]|LandingViews[]|DayOfferGeoSubCost[]|\yii\db\ActiveRecord[]
     */
    private function subsQuery($filters)
    {
        $query = DayOfferGeoSubCost::find()
            ->select([
                'day_offer_geo_sub_cost.known_sub_id',
                'day_offer_geo_sub_cost.sub_id_1',
                '`known_sub_id_1`.alias AS known_sub_id_1',
                'IFNULL(SUM(day_offer_geo_sub_cost.sum), 0) AS sum',
                'IFNULL(SUM(day_offer_geo_sub_cost.usd_sum), 0) AS total_traffic_cost',
            ])
            ->join('LEFT JOIN', '`known_sub_id_1`', '`known_sub_id_1`.alias = day_offer_geo_sub_cost.sub_id_1')
            ->where('DATE_FORMAT(day_offer_geo_sub_cost.date, "%d.%m.%Y") = "' . $filters['day']['value'] . '"')
            ->andWhere(['day_offer_geo_sub_cost.offer_id' => $filters['offer_id']['value']]);

        if ($filters['geo_id']['value'] == null) {
            $query->andWhere('day_offer_geo_sub_cost.geo_id NOT IN (SELECT `advert_offer_target`.geo_id FROM `advert_offer_target`WHERE `advert_offer_target`.offer_id = ' . $filters['offer_id']['value'] . ') ');
        } else {
            $query->andWhere(['day_offer_geo_sub_cost.geo_id' => $filters['geo_id']['value']]);
        }

        $result = $query
            ->groupBy(['day_offer_geo_sub_cost.sub_id_1'])
            ->orderBy(['day_offer_geo_sub_cost.sub_id_1' => SORT_ASC])
            ->asArray()
            ->all();

        return $result;
    }
}