<?php

namespace crm\models\finstrip\offer;

use crm\models\finstrip\Finstrip;
use common\models\order\Order;
use common\models\LandingViews;

/**
 * Class FisntripOfferGeo
 * @package crm\services\finstrip\offer
 *
 * @property string $month_year
 *
 */
class FisntripOfferGeo extends Finstrip
{
    public $month_year = null;
    private $viewsGeoFilter = '';

    /**
     * @return array
     */
    public function rules()
    {
        return array_merge(array_merge(parent::rules(), [
            [['month_year'], 'string'],
        ]));
    }

    /**
     * @param $filters
     * @return array
     */
    public function searchOfferGeo($filters): array
    {
        if ($filters['geo_id']['value'] == null) {
            $this->viewsGeoFilter .= 'geo_id NOT IN (SELECT `advert_offer_target`.geo_id FROM `advert_offer_target`WHERE `advert_offer_target`.offer_id = ' . $filters['offer_id']['value'] . ') ';
        } else {
            $this->viewsGeoFilter .= 'geo_id = ' . $filters['geo_id']['value'] . ' ';
        }

        $views = $this->viewsQuery($filters);
        $orders = $this->ordersQuery($filters);

        return $this->compareData($views, $orders);
    }

    /**
     * @param $views
     * @param $orders
     * @return array
     */
    private function compareData($views, $orders): array
    {
        $result = [];
        foreach ($views as $view) {
            $isset_orders = false;
            foreach ($orders as $key => $order) {
                if ($order['month_year'] == $view['month_year']) {
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

        return $result;
    }

    /**
     * @param $filters
     * @return array|LandingViews[]|\yii\db\ActiveRecord[]
     */
    private function viewsQuery($filters)
    {
        $query = LandingViews::find()
            ->select([
                'DATE_FORMAT(date, "%m.%Y") AS month_year',
                'SUM(landing_views.views) AS views',
                'SUM(landing_views.uniques) AS unique_views'
            ])
        ->where('landing_views.offer_id = ' . $filters['offer_id']['value'] . '');
        $this->filterViews($query, $filters);

        if ($filters['geo_id']['value'] == null) {
            $query->andWhere('landing_views.geo_id NOT IN (SELECT `advert_offer_target`.geo_id FROM `advert_offer_target`WHERE `advert_offer_target`.offer_id = ' . $filters['offer_id']['value'] . ') ');
        } else {
            $query->andWhere(['landing_views.geo_id' => $filters['geo_id']['value']]);
        }

        $result = $query
            ->groupBy(['month_year'])
            ->orderBy(['month_year' => SORT_DESC])
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
        /** It's NOT an error in joining order_data (' . $this->joinOrderData . ' ) */
        $query = Order::find()
            ->select([
                'DATE_FORMAT(`order`.created_at, "%m.%Y") AS month_year',
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

                'IFNULL((SELECT sum(day_offer_geo_sub_cost.usd_sum) 
FROM day_offer_geo_sub_cost 
WHERE DATE_FORMAT(day_offer_geo_sub_cost.date, "%m.%Y") = DATE_FORMAT(`order`.created_at, "%m.%Y")
AND day_offer_geo_sub_cost.' . $this->viewsGeoFilter . '
AND day_offer_geo_sub_cost.offer_id = ' . $filters['offer_id']['value'] . '
), 0) AS total_traffic_cost'
            ])
            ->join('LEFT JOIN', '`customer`', 'customer.customer_id = `order`.customer_id')
            ->join('LEFT JOIN', '`target_advert`', 'target_advert.target_advert_id = `order`.target_advert_id')
            ->join('LEFT JOIN', '`target_advert_group`', 'target_advert_group.target_advert_group_id = target_advert.target_advert_group_id')
            ->join('LEFT JOIN', '`advert_offer_target`', 'advert_offer_target.advert_offer_target_id = target_advert_group.advert_offer_target_id')
            ->join('LEFT JOIN', '`target_wm`', 'target_wm.target_wm_id = `order`.target_wm_id')
            ->where(['`order`.offer_id' => $filters['offer_id']['value']])
            ->andWhere(['`order`.deleted' => 0]);

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
            ->groupBy('month_year')
            ->orderBy(['month_year' => SORT_DESC])
            ->asArray()
            ->all();

        return $result;

        /**
         * For subquery
         *
         * IFNULL((SELECT sum(day_offer_geo_sub_cost.usd_sum)
         * FROM day_offer_geo_sub_cost
         * WHERE DATE_FORMAT(day_offer_geo_sub_cost.date, "%m.%Y") = DATE_FORMAT(O.created_at, "%m.%Y")
         * AND day_offer_geo_sub_cost.' . $this->viewsGeoFilter . '
         * AND day_offer_geo_sub_cost.offer_id = ' . $filters['offer_id']['value'] . '
         * ), 0) AS total_traffic_cost
         *
         * AND day_offer_geo_sub_cost.geo_id' . $this->geoFilter . '
         *
         */

        /**
         * For filter by wrong geo
         *
         * WHERE O.deleted = 0
         * AND O.offer_id = ' . $filters['offer_id']['value'] . '
         * AND ' . $this->ordersGeoFilter . '
         *
         * AND C.country_id' . $this->geoFilter . '
         *
         */
    }
}