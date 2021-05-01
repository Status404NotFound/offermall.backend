<?php

namespace crm\models\finstrip\calendar;

use Yii;
use crm\models\finstrip\Finstrip;
use common\models\order\Order;
use crm\services\export\ExportInterface;
use crm\services\export\ExportFactory;
use crm\models\finstrip\DayOfferGeoSubCost;
use yii\base\Exception;
use yii\db\Expression;

/**
 * Class FinstripExportModel
 * @package crm\models\finstrip\calendar
 */
class FinstripExportModel extends Finstrip implements ExportInterface
{
    public $day_month;
    public $offer_id = null;
    public $offer_name = null;
    public $geo_id = null;
    public $geo_name = null;

    /**
     * @return array
     */
    public function rules()
    {
        return array_merge(array_merge(parent::rules(), [
            [['offer_id', 'geo_id'], 'integer'],
            [['day_month', 'offer_name', 'geo_name'], 'string'],
        ]));
    }

    /**
     * @param null $filters
     * @return array
     * @throws Exception
     */
    public function compareData($filters = null): array
    {
        $start = new \DateTime($filters['date_range']['start']);
        $start->setTime(0, 0, 0);
        $start_date = $start->format('Y-m-d H:i:s');

        $end = new \DateTime($filters['date_range']['end']);
        $end->setTime(23, 59, 59);
        $end_date = $end->format('Y-m-d H:i:s');

        $offers_geo = $this->offersGeoSubCost($start_date, $end_date);
        $orders = $this->ordersData($start_date, $end_date);

        $dataSheet = ExportFactory::getDataSheet();
        $dataSheet['export_name'] = 'Finstrip Export';
        if (empty($rows = $this->readyToExport($offers_geo, $orders)))
            throw new Exception('Rows not found.');
        $dataSheet['titles'] = $this->getTitles($rows[0]);
        $dataSheet['data'] = $rows;
        return $dataSheet;
    }

    /**
     * @param $start_date
     * @param $end_date
     * @return array|Order[]
     */
    private function ordersData($start_date, $end_date)
    {
        return Order::find()
            ->select([
                'DATE_FORMAT(`order`.created_at, "%d.%m.%Y") AS day_month',

                'offer.offer_id',
                'offer.offer_name',

                'geo.geo_id',
                'geo.geo_name',

                'count(`order`.order_id) AS leads',
                'count(CASE `order`.order_status WHEN 100 THEN 1 ELSE NULL END) AS success_leads',
                'count(CASE `order`.order_status WHEN 50 THEN 1 ELSE NULL END) AS dip_orders',
                'count(CASE `order`.order_status WHEN 1 OR 0 THEN 1 ELSE NULL END) AS not_valid_orders',

                'sum(CASE WHEN `order`.order_status = 50 THEN `order`.total_amount ELSE 0 END) AS dip_sku_count',
                'sum(CASE WHEN `order`.order_status = 100 THEN `order`.total_amount ELSE 0 END) AS success_count',

                'sum(CASE WHEN `order`.order_status >= advert_offer_target.advert_offer_target_status THEN `order`.usd_advert_commission ELSE 0 END) AS total',
            ], new Expression('STRAIGHT_JOIN'))
            ->leftJoin('target_advert', 'target_advert.target_advert_id = `order`.target_advert_id')
            ->leftJoin('target_advert_group', 'target_advert_group.target_advert_group_id = target_advert.target_advert_group_id')
            ->leftJoin('advert_offer_target', 'advert_offer_target.advert_offer_target_id = target_advert_group.advert_offer_target_id')
            ->leftJoin('offer', 'offer.offer_id = `advert_offer_target`.offer_id')
            ->leftJoin('geo', 'geo.geo_id = advert_offer_target.geo_id')
            ->where(['`order`.deleted' => 0])
            ->andWhere(['offer.offer_status' => 1])
            ->andWhere(['>=', '`order`.created_at', $start_date])
            ->andwhere(['<=', '`order`.created_at', $end_date])
            ->groupBy(['day_month', 'order.offer_id', 'advert_offer_target.geo_id'])
            ->orderBy(['day_month' => SORT_ASC])
            ->asArray()->all();
    }

    /**
     * @param $start_date
     * @param $end_date
     * @return array|\common\models\finance\KnownSubs[]|\common\models\LandingViews[]|DayOfferGeoSubCost[]|\yii\db\ActiveRecord[]
     */
    private function offersGeoSubCost($start_date, $end_date)
    {
        return DayOfferGeoSubCost::find()
            ->from('day_offer_geo_sub_cost DOG')
            ->select([
                'offer.offer_id',
                'offer.offer_name',
                'geo.geo_id',
                'geo.geo_name',
                'DATE_FORMAT(DOG.date, "%d.%m.%Y") as date',
                'DOG.sum'
            ])
            ->join('LEFT JOIN', 'offer', 'offer.offer_id = DOG.offer_id')
            ->join('LEFT JOIN', 'geo', 'geo.geo_id = DOG.geo_id')
            ->where(['>=', 'DOG.date', $start_date])
            ->andwhere(['<=', 'DOG.date', $end_date])
            ->orderBy(['date' => SORT_ASC])
            ->asArray()->all();
    }

    /**
     * @param $offers_geo
     * @param $orders
     * @return array
     */
    private function readyToExport($offers_geo, $orders)
    {
        $result = [];
        foreach ($offers_geo as $offer_geo) {
            $isset_orders = false;
            foreach ($orders as $order) {
                if ($order['offer_id'] == $offer_geo['offer_id'] && $order['geo_id'] == $offer_geo['geo_id']
                    && $order['day_month'] == $offer_geo['date']) {

                    $result[] = [
                        'date' => $offer_geo['date'],
                        'offer_id' => $offer_geo['offer_id'],
                        'offer_name' => $offer_geo['offer_name'],
                        'geo_id' => $offer_geo['geo_id'],
                        'geo_name' => $offer_geo['geo_name'],
                        'leads' => $order['leads'],
                        'dip_orders' => $order['dip_orders'],
                        'success_leads' => $order['success_leads'],
                        'not_valid_orders' => $order['not_valid_orders'],
                        'total' => $order['total'],
                        'sum' => $offer_geo['sum'],
                    ];
                    $isset_orders = true;
                }
            }
            if ($isset_orders === false) {
                $result[] = [
                    'date' => $offer_geo['date'],
                    'offer_id' => $offer_geo['offer_id'],
                    'offer_name' => $offer_geo['offer_name'],
                    'geo_id' => $offer_geo['geo_id'],
                    'geo_name' => $offer_geo['geo_name'],
                    'leads' => 0,
                    'dip_orders' => 0,
                    'success_leads' => 0,
                    'not_valid_orders' => 0,
                    'total' => 0,
                    'sum' => $offer_geo['sum'],
                ];
            }
        }

        return $result;
    }

    /**
     * @param $order
     * @return array [
     * 'Order hash',
     * 'Created at',
     * 'Delivery date',
     * 'Name',
     * 'Phone',
     * 'Address',
     * 'Declaration',
     * 'Offer',
     * 'Pcs',
     * 'Sku count',
     * 'Color',
     * 'Size',
     * 'Emirate',
     * 'Caller',
     * 'Time',
     * 'Status'
     * ]
     **/
    private function getTitles($order)
    {
        return explode(',',
            str_replace('_', ' ',
                ucwords(
                    implode(',',
                        array_keys($order)
                    ), ','
                )
            )
        );
    }
}