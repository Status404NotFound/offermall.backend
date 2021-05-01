<?php

namespace crm\services\statistic;

use common\models\LandingViews;
use common\models\offer\targets\advert\TargetAdvert;
use common\models\order\Order;
use common\models\order\OrderStatus;
use yii\helpers\ArrayHelper;

class OfferTodayStatisticsService
{
    public $offers;
    public $total;

    public function __construct($filters = [])
    {
        $this->offers = $this->getAllStatistics($filters);
        $this->total = $this->getAllStatisticsTotalRow($this->offers);
    }

    public function getAllStatistics($filters = [])
    {
        $vu_countries = [];
        $vu_wm_id = null;


        $query = $this->statisticsQuery();

        if (isset($filters['advert_id'])) $query->andWhere(['target_advert.advert_id' => $filters['advert_id']['value']]);
        if (isset($filters['offer_id'])) $query->andWhere(['advert_offer_target.offer_id' => $filters['offer_id']['value']]);

        if (isset($filters['country_id'])) {
            $query->andWhere(['advert_offer_target.geo_id' => $filters['country_id']['value']]);
            $vu_countries[] = $filters['country_id']['value'];
        }


        if (isset($filters['wm_id'])) {
            $query->andWhere(['target_wm.wm_id' => $filters['wm_id']['value']]);
            $vu_wm_id = $filters['wm_id']['value'];
        }

        $statistics = $query
            ->groupBy('`order`.offer_id')
            ->asArray()
            ->all();


        foreach ($statistics as $key => $offer) {
            if (!isset($filters['country_id']) && isset($filters['advert_id'])) {
                $vu_countries = $this->getAdvertGeo($filters['advert_id'], $offer['offer_id']);
            }

            $landing_data = $this->getViewsUniques($offer['offer_id'], $vu_wm_id, $vu_countries);

            $statistics[$key]['views'] = $landing_data['views'];
            $statistics[$key]['unique'] = $landing_data['uniques'];

            $statistics[$key]['cr'] = !is_null($landing_data['views']) ? ($offer['total'] / $landing_data['views']) * 100 : 0;

            $statistics[$key]['cs'] = !is_null($landing_data['views']) ? ($offer['success_delivery'] / $landing_data['views']) * 100 : 0;

            $statistics[$key]['sr'] = !is_null($offer['total']) ? ($offer['success_delivery'] / $offer['total']) * 100 : 0;

            $statistics[$key]['nr'] = !is_null($offer['total']) ? ($offer['not_valid'] / $offer['total']) * 100 : 0;
        }

        return $statistics;
    }

    public function getAllStatisticsTotalRow($rows)
    {

        if (empty($rows)) return [];

        $total = array();
        foreach ($rows as $offer) {

            unset($offer['offer_id']);
            unset($offer['offer_name']);

            foreach ($offer as $key => $row) {
                if (isset($total[$key])) {
                    $total[$key] += $row;
                } else {
                    $total[$key] = $row;
                }
            }
        }

        $total['cr'] = $total['cr'] / count($rows);
        $total['cs'] = $total['cs'] / count($rows);
        $total['sr'] = $total['sr'] / count($rows);
        $total['nr'] = $total['nr'] / count($rows);

        return $total;
    }

    private function statisticsQuery()
    {
        $query = Order::find()
            ->select([
                "`advert_offer_target`.`offer_id`",
                "`offer`.`offer_name`",
                "SUM(if(`order`.order_status = " . OrderStatus::PENDING . ", 1, 0 )) AS `pending`",
                "SUM(if(`order`.order_status = " . OrderStatus::BACK_TO_PENDING . ", 1, 0 )) AS `back_to_pending`",
                "SUM(if(`order`.order_status = " . OrderStatus::WAITING_DELIVERY . ", 1, 0 )) AS `waiting_for_delivery`",
                "SUM(if(`order`.order_status = " . OrderStatus::DELIVERY_IN_PROGRESS . ", 1, 0 )) AS `delivery_in_progress`",
                "SUM(if(`order`.order_status = " . OrderStatus::SUCCESS_DELIVERY . ", 1, 0 )) AS `success_delivery`",
                "SUM(if(`order`.order_status = " . OrderStatus::CANCELED . ", 1,0 )) AS `canceled`",
                "SUM(if(`order`.order_status = " . OrderStatus::REJECTED . ", 1,0 )) AS `rejected`",
                "SUM(if(`order`.order_status = " . OrderStatus::NOT_VALID . ", 1,0 )) AS `not_valid`",
                "SUM(if(`order`.order_status = " . OrderStatus::NOT_VALID_CHECKED . ", 1,0 )) AS `not_valid_checked`",
                "SUM(if(`order`.order_status = " . OrderStatus::NOT_PAID . ", 1,0 )) AS `not_paid`",
                "SUM(if(`order`.order_status = " . OrderStatus::RETURNED . ", 1,0 )) AS `returned`",
                "COUNT(*) as total"
            ])
            ->join('LEFT JOIN', 'target_wm', 'target_wm.target_wm_id = `order`.target_wm_id')
            ->join('LEFT JOIN', 'target_advert', '`order`.target_advert_id = target_advert.target_advert_id')
            ->join('LEFT JOIN', 'target_advert_group', 'target_advert_group.target_advert_group_id = target_advert.target_advert_group_id')
            ->join('LEFT JOIN', 'advert_offer_target', 'target_advert_group.advert_offer_target_id = advert_offer_target.advert_offer_target_id')
            ->join('LEFT JOIN', 'offer', 'advert_offer_target.offer_id = offer.offer_id')
            ->where('DATE(`order`.created_at) = CURDATE()');
        $query->andWhere(['`order`.deleted' => 0]);
        return $query;
    }

    /**
     * @param $offer_id
     * @param null $wm_id
     * @param array $geo_id
     * @return array
     */
    private function getViewsUniques($offer_id, $wm_id = null, $geo_id = [])
    {
        $query = LandingViews::find()
            ->select("
                sum(views) as views,
                sum(uniques) as uniques
            ")
            ->join('LEFT JOIN', 'flow', 'landing_views.flow_id = flow.flow_id')
            ->where(['landing_views.offer_id' => $offer_id])
            ->andWhere('DATE(landing_views.date) = CURDATE()');

        if (isset($wm_id)) $query->andWhere(['flow.wm_id' => $wm_id]);
        if (!empty($geo_id)) $query->andWhere(['landing_views.geo_id' => $geo_id]);


        $landing_data = $query->one();

        return [
            'views' => $landing_data->views ?? 1,
            'uniques' => $landing_data->uniques ?? 1,
        ];

    }

    private function getAdvertGeo($advert_id, $offer_id)
    {
        $query = TargetAdvert::find()
            ->select('advert_offer_target.geo_id')
            ->join('LEFT JOIN', 'target_advert_group', 'target_advert_group.target_advert_group_id = target_advert.target_advert_group_id')
            ->join('LEFT JOIN', 'advert_offer_target', 'target_advert_group.advert_offer_target_id = advert_offer_target.advert_offer_target_id')
            ->where(['target_advert.advert_id' => $advert_id])
            ->andWhere(['advert_offer_target.offer_id' => $offer_id])
            ->asArray()
            ->all();

        return ArrayHelper::getColumn($query, 'geo_id');
    }
}