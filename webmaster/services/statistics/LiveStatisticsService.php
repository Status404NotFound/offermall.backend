<?php

namespace webmaster\services\statistics;

use Yii;
use common\models\order\Order;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * Class LiveStatisticsService
 * @package webmaster\services\statistics
 */
class LiveStatisticsService
{
    protected $filters;
    protected $children;
    private $query;

    const DAYS = [
        [
            'field' => 'today',
            'number' => 0,
        ],
        [
            'field' => 'yesterday',
            'number' => 1,
        ],
        [
            'field' => 'day3',
            'number' => 2,
        ],
        [
            'field' => 'day4',
            'number' => 3,
        ],
        [
            'field' => 'day5',
            'number' => 4,
        ],
        [
            'field' => 'day6',
            'number' => 5,
        ],
        [
            'field' => 'day7',
            'number' => 6,
        ],
        [
            'field' => 'day8',
            'number' => 7,
        ],

    ];

    /**
     * LiveSrv constructor.
     * @param $data
     */
    public function __construct($data)
    {
        $this->filters = $data['filters'] ?? [];
        $this->children = Yii::$app->user->identity->getWmChild();
        $this->query = $this->query();
    }

    /**
     * @return ActiveQuery
     */
    private function query(): ActiveQuery
    {
        $query = Order::find()
            ->select([
                "count(`order`.order_id) as orders_count",
            ], new Expression('STRAIGHT_JOIN'))
            ->join('LEFT JOIN', 'flow', 'flow.flow_id = `order`.flow_id')
            ->join('LEFT JOIN', 'target_advert', '`order`.target_advert_id = target_advert.target_advert_id')
            ->join('LEFT JOIN', 'target_advert_group', 'target_advert_group.target_advert_group_id = target_advert.target_advert_group_id')
            ->join('LEFT JOIN', 'advert_offer_target', 'target_advert_group.advert_offer_target_id = advert_offer_target.advert_offer_target_id')
            ->join('LEFT JOIN', 'offer', 'advert_offer_target.offer_id = offer.offer_id')
            ->where(['flow.wm_id' => $this->children])
            ->andWhere(['`order`.deleted' => 0]);

        if (isset($this->filters['offer_id'])) $query->andWhere(['offer.offer_id' => $this->filters['offer_id']]);
        if (isset($this->filters['advert_id'])) $query->andWhere(['target_advert.advert_id' => $this->filters['advert_id']]);
        if (isset($this->filters['geo_id'])) $query->andWhere(['advert_offer_target.geo_id' => $this->filters['geo_id']]);
        if (isset($this->filters['wm_id'])) $query->andWhere(['flow.wm_id' => $this->filters['wm_id']]);
        if (isset($this->filters['advert_offer_target_status'])) $query->andWhere(['advert_offer_target.advert_offer_target_status' => $this->filters['advert_offer_target_status']]);

        return $query;
    }

    /**
     * @return array
     */
    public function advert()
    {
        $this->query->addSelect([
            'AU.username as title',
            'AU.id as identifier',
        ]);
        $this->query->join('LEFT JOIN', 'user AU', 'AU.id = target_advert.advert_id');
        $group_field = 'target_advert.advert_id';
        $result = $this->getRate($this->days($group_field), $this->actualYesterday($group_field));
        return $result;
    }


    /**
     * @return array
     */
    public function offer()
    {
        $this->query->addSelect([
            'offer.offer_name as title',
            'offer.offer_id as identifier',
        ]);
        $group_field = 'offer.offer_id';
        $result = $this->getRate($this->days($group_field), $this->actualYesterday($group_field));
        return $result;
    }

    /**
     * @return array
     */
    public function superWm()
    {
        $this->query->addSelect([
            'WU.username as title',
            'WU.id as identifier',
        ]);
        $this->query->join('LEFT JOIN', 'user WU', 'WU.id = flow.wm_id');
        $group_field = 'WU.id';
        $result = $this->getRate($this->days($group_field), $this->actualYesterday($group_field));
        return $result;
    }

    /**
     * @return array
     */
    public function geo()
    {
        $this->query->addSelect([
            'countries.country_name as title',
            'advert_offer_target.geo_id as identifier',
        ]);
        $this->query->join('LEFT JOIN', 'countries', 'countries.id = advert_offer_target.geo_id');
        $group_field = 'advert_offer_target.geo_id';
        $result = $this->getRate($this->days($group_field), $this->actualYesterday($group_field));
        return $result;
    }

    /**
     * @param string $group_by
     * @return array
     */
    private function days(string $group_by)
    {
        $days = self::DAYS;
        $result = [];
        foreach ($days as $value) {
            $day = $this->dayQuery($value['number'])->groupBy($group_by)->asArray()->all();
            $result[] = ArrayHelper::index($day, 'identifier') + ['day' => $value['field']];
        }

        return $result;

    }

    /**
     * @param $day_number
     * @return ActiveQuery
     */
    private function dayQuery($day_number) : ActiveQuery
    {
        $query = clone $this->query;
        $query->andWhere("DATE(order.created_at) = DATE(NOW()) - INTERVAL $day_number DAY");
        return $query;
    }

    /**
     * @param $days
     * @param $actual_yesterday
     * @return array
     */
    private function getRate($days, $actual_yesterday)
    {
        $result = [];
        $total = [];

        $actual_yesterday_total = 0;

        foreach ($days as $key => $day) {

            foreach ($day as $identifier => $row) {
                if ($identifier != 'day') {
                    if (!isset($result[$identifier])) $result[$identifier] = [];

                    $result[$identifier][$day['day']] = $row['orders_count'];
                    if (!isset($result[$identifier]['title'])) $result[$identifier]['title'] = $row['title'];

                    if ($day['day'] . '_perc' != 'today' . '_perc') {
                        if (isset($days[$key + 1][$identifier]['orders_count'])) $result[$identifier][$day['day'] . '_perc'] = $this->countRate($days[$key + 1][$identifier]['orders_count'], $row['orders_count']);
                    } else {
                        $actual_yesterday_orders_count = $actual_yesterday[$identifier]['orders_count'] ?? 0;
                        $result[$identifier][$day['day'] . '_perc'] = $this->countRate($actual_yesterday_orders_count, $row['orders_count']);
                        $actual_yesterday_total += $actual_yesterday_orders_count;
                    }

                    if (!isset($total[$day['day']])) $total[$day['day']] = $row['orders_count'];
                    else $total[$day['day']] += $row['orders_count'];
                }
            }

        }

        if (isset($total['today'])) $total['today_perc'] = $this->countRate($actual_yesterday_total, $total['today']) . '%';
        if (isset($total['yesterday']) && isset($total['day3'])) $total['yesterday_perc'] = $this->countRate($total['day3'], $total['yesterday']) . '%';
        if (isset($total['day3']) && isset($total['day4'])) $total['day3_perc'] = $this->countRate($total['day4'], $total['day3']) . '%';
        if (isset($total['day4']) && isset($total['day5'])) $total['day4_perc'] = $this->countRate($total['day5'], $total['day4']) . '%';
        if (isset($total['day5']) && isset($total['day6'])) $total['day5_perc'] = $this->countRate($total['day6'], $total['day5']) . '%';
        if (isset($total['day6']) && isset($total['day7'])) $total['day6_perc'] = $this->countRate($total['day7'], $total['day6']) . '%';
        if (isset($total['day7']) && isset($total['day8'])) $total['day7_perc'] = $this->countRate($total['day8'], $total['day7']) . '%';


        return [
            'statistics' => array_values($result),
            'total' => $total,
        ];
    }

    /**
     * @param $yesterday
     * @param $today
     * @return float
     */
    private function countRate($yesterday, $today)
    {
        $rate = $yesterday != 0 ? (($today - $yesterday) / $yesterday) * 100 : 0;
        return round($rate, 2);
    }

    /**
     * @param string $group_by
     * @return array
     */
    private function actualYesterday(string $group_by)
    {
        $query = clone $this->query;
        $query->andWhere("DATE(order.created_at) = DATE(NOW()) - INTERVAL 1 DAY");
        $query->andWhere("TIME(`order`.created_at) < TIME(NOW())");

        $day = $query->groupBy($group_by)->asArray()->all();
        return ArrayHelper::index($day, 'identifier');
    }
}