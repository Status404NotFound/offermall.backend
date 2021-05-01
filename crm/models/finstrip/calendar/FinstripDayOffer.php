<?php

namespace crm\models\finstrip\calendar;

use crm\models\finstrip\Finstrip;
use common\models\order\Order;
use common\models\LandingViews;
use crm\models\finstrip\DayOfferGeoSubCost;

/**
 * Class FinstripDayOffer
 * @package crm\models\finstrip\calendar
 *
 * @property integer $geo_id
 * @property string $geo_name
 *
 */
class FinstripDayOffer extends Finstrip
{
    public $geo_id = null;
    public $geo_name = null;

    /**
     * @return array
     */
    public function rules()
    {
        return array_merge(array_merge(parent::rules(), [
            [['geo_id'], 'integer'],
            [['geo_name'], 'string'],
        ]));
    }

    /**
     * @param $filters
     * @return array
     */
    public function searchDayOffer($filters): array
    {
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
        $result = [];
        foreach ($views as $view) {
            $isset_orders = false;
            foreach ($orders as $key => $order) {
                if ($view['geo_name'] == $order['geo_name']) {
                    $model = new self();
                    $model->setAttributes(array_merge($view, $order));
                    $model->setCalculatedAttributes();

                    $result[] = $model->getAttributes(); // Setting calculated attributes
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

                $result[] = $model->getAttributes(); // Setting calculated attributes
            }
        }

        foreach ($subs as $key => $dayOfferGeoCost) {
            $isset_views = false;
            foreach ($result as &$dayOfferGeo) {
                if ($dayOfferGeoCost['geo_name'] == $dayOfferGeo['geo_name']) {
                    $model = new self();
                    $model->setAttributes(array_merge($dayOfferGeo, $dayOfferGeoCost));
                    $model->setCalculatedAttributes();
                    $dayOfferGeo = $model->getAttributes();
                    $isset_views = true;
                }
            }
            if ($isset_views === false) {
                $model = new self();
                $model->setAttributes($dayOfferGeoCost);
                $result[] = $model->getAttributes();
            }
        }

        return $result;
    }

    /**
     * @param $filters
     * @return array|\common\models\finance\KnownSubs[]|LandingViews[]|DayOfferGeoSubCost[]|\yii\db\ActiveRecord[]
     */
    private function viewsQuery($filters)
    {
        $query = LandingViews::find()
            ->select([
                'IF(countries.id IS NULL OR countries.id NOT IN (SELECT `advert_offer_target`.geo_id FROM `advert_offer_target`WHERE `advert_offer_target`.offer_id = ' . $filters['offer_id']['value'] . '), NULL, landing_views.geo_id) AS geo_id',
                'IF(countries.id IS NULL OR countries.id NOT IN (SELECT `advert_offer_target`.geo_id FROM `advert_offer_target`WHERE `advert_offer_target`.offer_id = ' . $filters['offer_id']['value'] . '), "Wrong Geo", countries.country_name) AS geo_name',
                'IFNULL(SUM(landing_views.views), 0) AS views',
                'IFNULL(SUM(landing_views.uniques), 0) AS unique_views'
            ])
            ->join('JOIN', 'offer', 'offer.offer_id = ' . $filters['offer_id']['value'] . '')
            ->join('LEFT JOIN', 'countries', 'countries.id = landing_views.geo_id')
            ->where('DATE_FORMAT(date, "%d.%m.%Y") = "' . $filters['day']['value'] . '"')
            ->andWhere(['landing_views.offer_id' => $filters['offer_id']['value']]);
        $this->filterViews($query, $filters);

        $result = $query
            ->groupBy(['geo_name'])
            ->orderBy(['geo_name' => SORT_DESC])
            ->asArray()
            ->all();

        return $result;
    }

    /**
     * @param $filters
     * @return array|Order[]
     */
    private function ordersQuery($filters)
    {
        $query = Order::find()
            ->select([
                'advert_offer_target.geo_id AS geo_id',
                'IF(geo.geo_id IS NULL OR geo.geo_id NOT IN (SELECT `advert_offer_target`.geo_id FROM `advert_offer_target`WHERE `advert_offer_target`.offer_id = ' . $filters['offer_id']['value'] . '), "Wrong Geo", geo.geo_name) AS geo_name',

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
            ->join('LEFT JOIN', 'customer', 'customer.customer_id = `order`.customer_id')
            ->join('LEFT JOIN', '`target_advert`', 'target_advert.target_advert_id = `order`.target_advert_id')
            ->join('LEFT JOIN', '`target_advert_group`', 'target_advert_group.target_advert_group_id = target_advert.target_advert_group_id')
            ->join('LEFT JOIN', '`advert_offer_target`', 'advert_offer_target.advert_offer_target_id = target_advert_group.advert_offer_target_id')
            ->join('LEFT JOIN', 'geo', 'geo.geo_id = advert_offer_target.geo_id')
            ->join('LEFT JOIN', '`target_wm`', 'target_wm.target_wm_id = `order`.target_wm_id')
            ->where('DATE_FORMAT(`order`.created_at, "%d.%m.%Y") = "' . $filters['day']['value'] . '"')
            ->andWhere(['`order`.offer_id' => $filters['offer_id']['value']])
            ->andWhere(['`order`.deleted' => 0]);

        if (isset($filters['advert_id'])) $query->andWhere(['target_advert.advert_id' => $filters['advert_id']['value']]);
        if (isset($filters['advert_offer_target_status'])) $query->andWhere(['advert_offer_target.advert_offer_target_status' => $filters['advert_offer_target_status']['value']]);
        if (isset($filters['wm_id'])) $query->andWhere(['target_wm.wm_id' => $filters['wm_id']['value']]);

        $result = $query
            ->groupBy(['geo.geo_id'])
            ->orderBy(['geo.geo_name' => SORT_DESC])
            ->asArray()
            ->all();

        return $result;
    }

    /**
     * @param $filters
     * @return array|\common\models\finance\KnownSubs[]|LandingViews[]|DayOfferGeoSubCost[]|\yii\db\ActiveRecord[]
     */
    private function subsQuery($filters)
    {
        return DayOfferGeoSubCost::find()
            ->select([
                'IF(countries.id IS NULL OR countries.id NOT IN (SELECT `advert_offer_target`.geo_id FROM `advert_offer_target`WHERE `advert_offer_target`.offer_id = ' . $filters['offer_id']['value'] . '), NULL, day_offer_geo_sub_cost.geo_id) AS geo_id',
                'IF(day_offer_geo_sub_cost.geo_id IS NULL OR day_offer_geo_sub_cost.geo_id NOT IN (SELECT `advert_offer_target`.geo_id FROM `advert_offer_target`WHERE `advert_offer_target`.offer_id = ' . $filters['offer_id']['value'] . '), "Wrong Geo", countries.country_name) AS geo_name',
                'IFNULL(sum(day_offer_geo_sub_cost.usd_sum), 0) AS total_traffic_cost'
            ])
            ->join('LEFT JOIN', 'countries', 'countries.id = day_offer_geo_sub_cost.geo_id')
            ->where('DATE_FORMAT(day_offer_geo_sub_cost.date, "%d.%m.%Y") = "' . $filters['day']['value'] . '"')
            ->andWhere(['day_offer_geo_sub_cost.offer_id' => $filters['offer_id']['value']])
            ->groupBy(['geo_name'])
            ->orderBy(['geo_name' => SORT_ASC])
            ->asArray()
            ->all();
    }
}