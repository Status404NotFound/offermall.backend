<?php

namespace crm\services\statistic;

use Yii;
use common\models\order\Order;
use common\models\customer\CustomerSystem;

/**
 * Class AdditionalStatisticsService
 * @package crm\services\statistic
 */
class AdditionalStatisticsService
{
    public $owner_id;

    /**
     * AdditionalStatisticsService constructor.
     */
    public function __construct()
    {
        $this->owner_id = Yii::$app->user->identity->getOwnerId();
    }

    /**
     * @return array
     */
    public function getBrowserStatistics(): array
    {
        $query = CustomerSystem::find()
            ->select([
                "SUM(if(browser like '%chrome%', 1, 0 )) AS `chrome`",
                "SUM(if(browser like '%safari%', 1, 0 )) AS `safari`",
                "SUM(if(browser like '%mozilla%', 1, 0 )) AS `mozilla`",
                "SUM(if(browser like '%msie%', 1, 0 )) AS `msie`",
                "SUM(if(browser like '%opera%', 1, 0 )) AS `opera`",
                "SUM(if(browser NOT LIKE '%chrome%' AND browser NOT LIKE '%safari%' 
                AND browser NOT LIKE '%mozilla%' AND browser NOT LIKE '%msie%' 
                AND browser NOT LIKE '%opera%', 1, 0 )) AS `other`",
                "COUNT(customer_system.customer_id) as total",
            ])
            ->join('LEFT JOIN', 'order', 'order.customer_id = customer_system.customer_id')
            ->join('LEFT JOIN', 'order_data', 'order_data.order_id = order.order_id');

        if (!is_null($this->owner_id)) $query->where(['order_data.owner_id' => $this->owner_id]);
        $query->andWhere(['`order`.deleted' => 0]);

        $result = $query
            ->asArray()
            ->one();

        $return = [];
        if (!empty($result['total'])) {
            $return = [
                'chrome' => round($result['chrome'] / $result['total'] * 100, 2),
                'safari' => round($result['safari'] / $result['total'] * 100, 2),
                'mozilla' => round($result['mozilla'] / $result['total'] * 100, 2),
                'msie' => round($result['msie'] / $result['total'] * 100, 2),
                'opera' => round($result['opera'] / $result['total'] * 100, 2),
                'other' => round($result['other'] / $result['total'] * 100, 2),
            ];
        }

        return $return;
    }

    /**
     * @return array
     */
    public function getOsStatistics(): array
    {
        $query = CustomerSystem::find()
            ->select([
                "SUM(if(os like '%windows%', 1, 0 )) AS `windows`",
                "SUM(if(os like '%mac%', 1, 0 )) AS `mac`",
                "SUM(if(os like '%android%', 1, 0 )) AS `android`",
                "SUM(if(os LIKE '%iphone%' OR os LIKE '%ipad%' OR os LIKE '%ipod%', 1, 0 )) AS `ios`",
                "SUM(if(os NOT LIKE '%windows%' AND os NOT LIKE '%mac%' 
                AND os NOT LIKE '%android%' AND os NOT LIKE '%iphone%' 
                AND os NOT LIKE '%ipad%' AND os NOT LIKE '%ipod%', 1, 0 )) AS `other`",
                "COUNT(customer_system.customer_id) as total",
            ])
            ->join('LEFT JOIN', 'order', 'order.customer_id = customer_system.customer_id')
            ->join('LEFT JOIN', 'order_data', 'order_data.order_id = order.order_id');

        if (!is_null($this->owner_id)) $query->where(['order_data.owner_id' => $this->owner_id]);
        $query->andWhere(['`order`.deleted' => 0]);

        $result = $query
            ->asArray()
            ->one();

        $return = [];
        if (!empty($result['total'])) {
            $return = [
                'other' => round($result['other'] / $result['total'] * 100, 2),
                'Android' => round($result['android'] / $result['total'] * 100, 2),
                'Windows' => round($result['windows'] / $result['total'] * 100, 2),
                'IOS' => round($result['ios'] / $result['total'] * 100, 2),
                'Mac' => round($result['mac'] / $result['total'] * 100, 2),
            ];
        }

        return $return;
    }

    /**
     * @return array
     */
    public function getCountriesStatistics(): array
    {
        $query = Order::find()
            ->select([
                'customer.country_id',
                'countries.country_code',
                'countries.country_name',
                'COUNT(*) AS orders_amount'
            ])
            ->join('LEFT JOIN', 'customer', 'customer.customer_id = `order`.customer_id')
            ->join('LEFT JOIN', 'order_data', 'order_data.order_id = `order`.order_id')
            ->join('LEFT JOIN', 'countries', 'countries.id = customer.country_id')
            ->where(['`order`.deleted' => 0]);

        if (!is_null($this->owner_id)) $query->andWhere(['order_data.owner_id' => $this->owner_id]);

        $count = clone $query;
        $total = $count->count();

        $statistics = $query
            ->groupBy('customer.country_id')
            ->asArray()
            ->all();

        foreach ($statistics as $key => $country) {
            $statistics[$key]['percentage'] = round($country['orders_amount'] / $total * 100, 1);
        }

        return $statistics;
    }

    /**
     * @param array $request
     * @return float|int
     */
    public function calculateAutoleads(array $request)
    {
        $start = new \DateTime($request['date']['start']);
        $start->setTime(0, 0, 0);
        $start_date = $start->format('Y-m-d H:i:s');

        $end = new \DateTime($request['date']['end']);
        $end->setTime(23, 59, 59);
        $end_date = $end->format('Y-m-d H:i:s');

        $query = Order::find()
            ->select([
                "DATE_FORMAT(`order`.created_at, '%d.%m.%Y') as `date`",
                "SUM(if(`order`.is_autolead = 1, 1, 0 )) AS `is_autolead`",
                "COUNT(`order`.order_id) as total",
            ])
            ->leftJoin('order_data', 'order_data.order_id = order.order_id')
            ->andWhere(['>', 'created_at', $start_date])
            ->andWhere(['<', 'created_at', $end_date]);

        if (!is_null($this->owner_id)) $query->where(['order_data.owner_id' => $this->owner_id]);
        $query->andWhere(['`order`.deleted' => 0]);

        $result = $query
            ->groupBy('date')
            ->asArray()
            ->all();

        foreach ($result as $row) {
            $result['is_autolead'] = $row['is_autolead'];
            $result['total'] = $row['total'];
        }

        $return = !empty($result['total']) ? round($result['is_autolead'] / $result['total'] * 100, 2) : 0;

        return $return;
    }
}