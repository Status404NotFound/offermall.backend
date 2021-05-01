<?php

namespace crm\models\finstrip\calendar;

use crm\models\finstrip\Finstrip;
use common\models\order\Order;
use common\models\LandingViews;

/**
 * Class FisntripMonth
 * @package crm\models\finstrip\calendar
 *
 * @property string $day_month
 *
 */
class FisntripMonth extends Finstrip
{
    public $day_month;

    /**
     * @return array
     */
    public function rules()
    {
        return array_merge(array_merge(parent::rules(), [
            [['day_month'], 'string'],
        ]));
    }

    /**
     * @param $filters
     * @return array
     */
    public function searchMonth($filters): array
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
    private function compareData($views, $orders): array
    {
        $result = [];
        foreach ($views as $view) {
            $isset_orders = false;
            foreach ($orders as $key => $order) {
                if ($order['day_month'] == $view['day_month']) {
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

    private function viewsQuery($filters)
    {
        $query = LandingViews::find()
            ->select([
                'DATE_FORMAT(date, "%d.%m.%Y") AS day_month',
                'sum(views) AS views',
                'sum(uniques) AS unique_views'
            ])
            ->where('DATE_FORMAT(date,"%m.%Y") = "' . $filters['month']['value'] . '"');
        $this->filterViews($query, $filters);

        $result = $query
            ->groupBy(['day_month'])
            ->orderBy(['day_month' => SORT_DESC])
            ->asArray()
            ->all();

        return $result;
    }

    private function ordersQuery($filters)
    {
        $query = Order::find()
            ->select([
                'DATE_FORMAT(`order`.created_at, "%d.%m.%Y") AS day_month',
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
            ->join('JOIN', 'customer', 'customer.customer_id = `order`.customer_id')
            ->join('LEFT JOIN', '`target_advert`', 'target_advert.target_advert_id = `order`.target_advert_id')
            ->join('LEFT JOIN', '`target_advert_group`', 'target_advert_group.target_advert_group_id = target_advert.target_advert_group_id')
            ->join('LEFT JOIN', '`advert_offer_target`', 'advert_offer_target.advert_offer_target_id = target_advert_group.advert_offer_target_id')
            ->join('LEFT JOIN', '`target_wm`', 'target_wm.target_wm_id = `order`.target_wm_id')
            ->where('DATE_FORMAT(`order`.created_at,"%m.%Y") = "' . $filters['month']['value'] . '"')
            ->andWhere(['`order`.deleted' => 0]);

        if (isset($filters['advert_id'])) {
            $query->addSelect(['IFNULL((SELECT sum(day_offer_geo_sub_cost.usd_sum) FROM day_offer_geo_sub_cost WHERE DATE_FORMAT(day_offer_geo_sub_cost.date, "%d.%m.%Y") = DATE_FORMAT(`order`.created_at, "%d.%m.%Y") AND day_offer_geo_sub_cost.offer_id IN ('.implode(',', $this->getAdvertOffers($filters['advert_id']['value'])).') AND day_offer_geo_sub_cost.geo_id IN ('.implode(',', $this->getAdvertGeo($filters['advert_id']['value'])).')), 0) AS total_traffic_cost']);
            $query->andWhere(['target_advert.advert_id' => $filters['advert_id']['value']]);
        } else {
            $query->addSelect(['IFNULL((SELECT sum(day_offer_geo_sub_cost.usd_sum) FROM day_offer_geo_sub_cost WHERE DATE_FORMAT(day_offer_geo_sub_cost.date, "%d.%m.%Y") = DATE_FORMAT(`order`.created_at, "%d.%m.%Y")), 0) AS total_traffic_cost']);
        }

        if (isset($filters['advert_offer_target_status'])) $query->andWhere(['advert_offer_target.advert_offer_target_status' => $filters['advert_offer_target_status']['value']]);
        if (isset($filters['wm_id'])) $query->andWhere(['target_wm.wm_id' => $filters['wm_id']['value']]);

        $result = $query
            ->groupBy(['day_month'])
            ->orderBy(['day_month' => SORT_DESC])
            ->asArray()
            ->all();

        return $result;
    }
}