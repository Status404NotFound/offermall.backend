<?php

namespace crm\models\finstrip\calendar;

use crm\models\finstrip\Finstrip;
use common\models\order\Order;
use common\models\LandingViews;

/**
 * Class FisntripDay
 * @package crm\services\finstrip\calendar
 *
 * @property integer $offer_id
 * @property string $offer_name
 *
 */
class FisntripDay extends Finstrip
{
    public $offer_name = null;
    public $offer_id = null;

    /**
     * @return array
     */
    public function rules()
    {
        return array_merge(array_merge(parent::rules(), [
            [['offer_id'], 'integer'],
            [['offer_name'], 'string'],
        ]));
    }

    /**
     * @param $filters
     * @return array
     */
    public function searchDay($filters): array
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
                if ($order['offer_name'] == $view['offer_name']) {
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
                'offer.offer_id',
                'offer.offer_name',
                'SUM(views) AS views',
                'SUM(uniques) AS unique_views'
            ])
            ->join('JOIN', 'offer', 'offer.offer_id = landing_views.offer_id')
            ->where('DATE_FORMAT(date, "%d.%m.%Y") = "' . $filters['day']['value'] . '"');
        $this->filterViews($query, $filters);

        $result = $query
            ->groupBy(['offer_name'])
            ->orderBy(['offer_name' => SORT_ASC])
            ->asArray()
            ->all();

        return $result;
    }

    private function ordersQuery($filters)
    {
        $query = Order::find()
            ->select([
                'offer.offer_id',
                'offer.offer_name',
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
            ->join('JOIN', 'offer', 'offer.offer_id = `order`.offer_id')
            ->join('LEFT JOIN', '`target_advert`', 'target_advert.target_advert_id = `order`.target_advert_id')
            ->join('LEFT JOIN', '`target_advert_group`', 'target_advert_group.target_advert_group_id = target_advert.target_advert_group_id')
            ->join('LEFT JOIN', '`advert_offer_target`', 'advert_offer_target.advert_offer_target_id = target_advert_group.advert_offer_target_id')
            ->join('LEFT JOIN', '`target_wm`', 'target_wm.target_wm_id = `order`.target_wm_id')
            ->where('DATE_FORMAT(`order`.created_at, "%d.%m.%Y") = "' . $filters['day']['value'] . '"')
            ->andWhere(['`order`.deleted' => 0]);

        if (isset($filters['advert_id'])) {
            $query->addSelect([
                'IFNULL((SELECT sum(day_offer_geo_sub_cost.usd_sum) FROM day_offer_geo_sub_cost 
WHERE day_offer_geo_sub_cost.offer_id = offer.offer_id
AND DATE_FORMAT(day_offer_geo_sub_cost.date, "%d.%m.%Y") = "' . $filters['day']['value'] . '" AND day_offer_geo_sub_cost.offer_id IN ('.implode(',', $this->getAdvertOffers($filters['advert_id']['value'])).')), 0) AS total_traffic_cost'
            ]);
            $query->andWhere(['target_advert.advert_id' => $filters['advert_id']['value']]);
        } else {
            $query->addSelect([
                'IFNULL((SELECT sum(day_offer_geo_sub_cost.usd_sum) FROM day_offer_geo_sub_cost 
WHERE day_offer_geo_sub_cost.offer_id = offer.offer_id
AND DATE_FORMAT(day_offer_geo_sub_cost.date, "%d.%m.%Y") = "' . $filters['day']['value'] . '"), 0) AS total_traffic_cost'
            ]);
        }

        if (isset($filters['advert_offer_target_status'])) $query->andWhere(['advert_offer_target.advert_offer_target_status' => $filters['advert_offer_target_status']['value']]);
        if (isset($filters['wm_id'])) $query->andWhere(['target_wm.wm_id' => $filters['wm_id']['value']]);

        $result = $query
            ->groupBy(['offer.offer_name'])
            ->orderBy(['offer.offer_name' => SORT_ASC])
            ->asArray()
            ->all();

        return $result;
    }
}