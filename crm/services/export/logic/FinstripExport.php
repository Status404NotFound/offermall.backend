<?php

namespace crm\services\export\logic;

use Yii;
use common\services\timezone\TimeZoneSrv;
use crm\models\finstrip\DayOfferGeoSubCost;
use crm\services\export\ExportFactory;
use crm\services\export\ExportInterface;
use yii\base\Exception;

class FinstripExport implements ExportInterface
{
    /**
     * @param null $filters
     * @return array
     * @throws Exception
     */
    public function compareData($filters = null): array
    {
        $dataSheet = ExportFactory::getDataSheet();
        $dataSheet['export_name'] = 'Finstrip Export';
        if (empty($rows = $this->findRows($filters['date_range'])))
            throw new Exception('Rows not found.');
        $dataSheet['titles'] = $this->getTitles($rows[0]);
        $dataSheet['data'] = $rows;
        return $dataSheet;
    }

    private function findRows($date_range)
    {
        $start = new \DateTime($date_range['start']);
        $start->setTime(0, 0, 0);
        $start_date = $start->format('Y-m-d H:i:s');

        $end = new \DateTime($date_range['end']);
        $end->setTime(23, 59, 59);
        $end_date = $end->format('Y-m-d H:i:s');

//        $tz = new TimeZoneSrv();
        $rows = DayOfferGeoSubCost::find()
            ->from('day_offer_geo_sub_cost DOG')
            ->select([
                'offer.offer_name',
                'geo.geo_name',
//                "convert_tz(DATE_FORMAT(DOG.date, '%d.%m.%Y'), '+00:00', '" . $tz->time_zone_offset . "') as date",
                'DATE_FORMAT(DOG.date, "%d.%m.%Y") as date',
                'DOG.sum'
            ])
            ->join('LEFT JOIN', 'offer', 'offer.offer_id = DOG.offer_id')
            ->join('LEFT JOIN', 'geo', 'geo.geo_id = DOG.geo_id')
            ->where(['>=', 'DOG.date', $start_date])
            ->andwhere(['<=', 'DOG.date', $end_date])
            ->orderBy(['date' => SORT_ASC])
            ->asArray()->all();

        return $rows;
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