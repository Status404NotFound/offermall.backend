<?php

namespace crm\services\statistic;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use common\models\order\Order;
use common\models\order\OrderStatus;
use common\models\order\StatusReason;
use common\models\statistics\Statistics;

/**
 * Class RejectStatisticsService
 * @package crm\services\statistic
 */
class RejectStatisticsService extends Statistics
{
    public $total = [];
    public $list;
    public $valid;
    public $total_valid;
    public $total_not_valid;
    public $filters;
    public $owner_id;

    /**
     * RejectStatisticsService constructor.
     * @param array $filters
     */
    public function __construct($filters = [])
    {
        $this->owner_id = Yii::$app->user->identity->getOwnerId();
        $this->filters = $filters;
        $this->list = $this->getRejectStatistics();
        $this->valid = $this->getValid();
        parent::__construct();
    }

    /**
     * @return array|null|ActiveRecord
     */
    public function getRejectStatistics()
    {
        $query = $this->totalQuery();
        $query = $this->applyFilters($query);
        $statistics = $query
            ->asArray()
            ->one();

        $statistics['total'] = !is_null($statistics['allcount']) ? $statistics['allcount'] - $statistics['nvc'] : 0;
        $statistics['rate_approved'] = isset($statistics['approved']) ? ($statistics['approved'] / $statistics['total']) * 100 : 0;
        $statistics['rate_valid_reject'] = isset($statistics['rejected']) ? ($statistics['valid_reject'] / $statistics['total']) * 100 : 0;
        $statistics['up_sale'] = isset($statistics['pcs']) ? $statistics['pcs'] : 0;
        $statistics['up_sale_rate'] = !empty($statistics['pcs']) ? ($statistics['pcs'] / $statistics['approved']) * 100 : 0;

        return $statistics;
    }

    /**
     * @return array
     */
    public function getValid()
    {
        $statistics_data = $this->applyFilters($this->reasonQuery())
            ->groupBy('status_reason')
            ->asArray()
            ->all();
        
        $valid = [];
        $not_valid = [];
        $total_valid_count = 0;
        $total_not_valid_count = 0;
        
        foreach ($statistics_data as $reason) {
            $info = [
                'status_reason' => $reason['status_reason'],
                'reason_name' => StatusReason::statusReasons()[OrderStatus::REJECTED][$reason['status_reason']],
                'order_count' => $reason['order_count'],
                'rate_covered' => round(!empty($this->list) ? ($reason['order_count'] / ($this->list['total'] - $this->list['pending'])) * 100 : 0, 2),
                'rate_rejected' => round(!empty($this->list) ? ($reason['order_count'] / $this->list['rejected']) * 100 : 0, 2),
            ];
    
            if ($reason['is_valid_reject'] == 1) {
                $valid[] = $info;
                $total_valid_count += $reason['order_count'];
            } else {
                $not_valid[] = $info;
                $total_not_valid_count += $reason['order_count'];
            }
        }
        
        $total = [
            'valid_reject' => [
                'total_valid' => $total_valid_count,
                'valid_rate_rejected' => !empty($this->list['rejected']) ? ($total_valid_count / $this->list['rejected']) * 100 : 0,
                'valid_rate_covered' => !empty($total_valid_count) ? ($total_valid_count / ($this->list['total'] - $this->list['pending'])) * 100 : 0,
            ],
            'not_valid_reject' => [
                'total_not_valid' => $total_not_valid_count,
                'not_valid_rate_rejected' => !empty($this->list['rejected']) ? round(($total_not_valid_count / $this->list['rejected']) * 100, 2) : 0,
                'not_valid_rate_covered' => !empty($total_not_valid_count) ? round(($total_not_valid_count / ($this->list['total'] - $this->list['pending'])) * 100, 2) : 0,
            ],
        ];

        return [
            'valid' => $valid,
            'not_valid' => $not_valid,
            'total' => $total,
        ];
    }


    /**
     * @return ActiveQuery
     */
    private function totalQuery(): ActiveQuery
    {
        $approved_statuses = "(" .
            OrderStatus::WAITING_DELIVERY . ", " .
            OrderStatus::DELIVERY_IN_PROGRESS . ", " .
            OrderStatus::SUCCESS_DELIVERY . ", " .
            OrderStatus::CANCELED . ", " .
            OrderStatus::RETURNED . ", " .
            OrderStatus::NOT_PAID . ")";
    
        $wds_statuses = "(" .
            OrderStatus::WAITING_DELIVERY . ", " .
            OrderStatus::DELIVERY_IN_PROGRESS . ", " .
            OrderStatus::SUCCESS_DELIVERY . ")";

        $query = Order::find()
            ->select([
                "SUM(if(`order`.order_status = " . OrderStatus::PENDING . ", 1, 0 )) AS `pending`",
                "SUM(if(`order`.order_status = " . OrderStatus::WAITING_DELIVERY . ", 1, 0 )) AS `waiting_for_delivery`",
                "SUM(if(`order`.order_status = " . OrderStatus::DELIVERY_IN_PROGRESS . ", 1, 0 )) AS `delivery_in_progress`",
                "SUM(if(`order`.order_status = " . OrderStatus::SUCCESS_DELIVERY . ", 1, 0 )) AS `success_delivery`",
                "SUM(if(`order`.order_status = " . OrderStatus::REJECTED . ", 1,0 )) AS `rejected`",
                "SUM(if(`order`.order_status = " . OrderStatus::NOT_PAID . ", 1,0 )) AS `not_paid`",
                "SUM(if(`order`.order_status = " . OrderStatus::CANCELED . ", 1,0 )) AS `canceled`",
                "SUM(if(`order`.order_status = " . OrderStatus::RETURNED . ", 1,0 )) AS `returned`",
                "SUM(if(`order`.order_status = " . OrderStatus::REJECTED . " and `order`.status_reason in " . self::VALID_REJECT . ", 1, 0 )) AS `valid_reject`",
                "SUM(if(`order`.order_status = " . OrderStatus::REJECTED . " and `order`.status_reason in " . self::NOT_VALID_REJECT . ", 1, 0 )) AS `not_valid_reject`",
                'SUM(if(`order`.order_status in ' . $approved_statuses . ', 1, 0 )) AS `approved`',
                'SUM(if(`order`.order_status in ' . $wds_statuses . ' , 1, 0)) AS total_wds',
                'SUM(CASE WHEN `order`.order_status in ' . $wds_statuses . ' THEN `order`.total_amount ELSE 0 END) AS sum_pcs_wds',
                'SUM(if(`order`.total_amount > 1 and `order`.order_status in ' . $approved_statuses . ', 1, 0)) AS pcs',
                'SUM(if(`order`.total_amount > 1, 1, 0)) AS up_sale_pcs_total',
                'SUM(if(`order`.order_status = ' . OrderStatus::NOT_VALID_CHECKED . ' and `order`.status_reason IN (2, 10), 1, 0)) AS nvc',
                'COUNT(*) AS allcount'
            ])
            ->join('LEFT JOIN', 'target_advert', 'target_advert.target_advert_id = `order`.target_advert_id')
            ->join('LEFT JOIN', 'target_advert_group', 'target_advert_group.target_advert_group_id = target_advert.target_advert_group_id')
            ->join('LEFT JOIN', 'advert_offer_target', 'target_advert_group.advert_offer_target_id = advert_offer_target.advert_offer_target_id')
            ->join('LEFT JOIN', 'offer', 'advert_offer_target.offer_id = offer.offer_id')
            ->andWhere(['`order`.deleted' => 0]);

        if (!is_null($this->owner_id)) $query->andWhere(['target_advert.advert_id' => $this->owner_id]);

        return $query;
    }

    /**
     * @return ActiveQuery
     */
    private function reasonQuery(): ActiveQuery
    {
        $query = Order::find()
            ->select([
                "if(order.status_reason in " . self::VALID_REJECT . ", 1, 0 ) AS `is_valid_reject`",
                'order.status_reason',
                'count(order.order_id) as order_count',
            ])
            ->join('LEFT JOIN', 'target_advert', 'target_advert.target_advert_id = `order`.target_advert_id')
            ->join('LEFT JOIN', 'target_advert_group', 'target_advert_group.target_advert_group_id = target_advert.target_advert_group_id')
            ->join('LEFT JOIN', 'advert_offer_target', 'target_advert_group.advert_offer_target_id = advert_offer_target.advert_offer_target_id')
            ->join('LEFT JOIN', 'offer', 'advert_offer_target.offer_id = offer.offer_id')
            ->where(['`order`.deleted' => 0])
            ->andWhere(['order.order_status' => OrderStatus::REJECTED]);

        if (!is_null($this->owner_id)) $query->andWhere(['target_advert.advert_id' => $this->owner_id]);

        return $query;
    }

    /**
     * @param ActiveQuery $query
     * @return ActiveQuery
     */
    private function applyFilters(ActiveQuery $query)
    {
        if (isset($this->filters['offer'])) $query->andWhere(['order.offer_id' => $this->filters['offer']]);
        if (isset($this->filters['geo_id'])) $query->andWhere(['advert_offer_target.geo_id' => $this->filters['geo_id']]);
        if (isset($this->filters['advert'])) $query->andWhere(['target_advert.advert_id' => $this->filters['advert']]);


        if (isset($this->filters['date'])) {
            $start = new \DateTime($this->filters['date']['start']);
            $start->setTime(0, 0, 0);
            $start_date = $start->format('Y-m-d H:i:s');

            $end = new \DateTime($this->filters['date']['end']);
            $end->setTime(23, 59, 59);
            $end_date = $end->format('Y-m-d H:i:s');

            $query->andWhere(['>', '`order`.created_at', $start_date]);
            $query->andWhere(['<', '`order`.created_at', $end_date]);
        }

        if (isset($this->filters['call_date'])) {

            $query->join('INNER JOIN', 'lead_calls', 'lead_calls.order_id = `order`.order_id');

            if (isset($this->filters['operator'])) $query->andWhere(['lead_calls.operator_id' => $this->filters['operator']]);

            $start = new \DateTime($this->filters['call_date']['start']);
            $start->setTime(0, 0, 0);
            $start_date = $start->format('Y-m-d H:i:s');

            $end = new \DateTime($this->filters['call_date']['end']);
            $end->setTime(23, 59, 59);
            $end_date = $end->format('Y-m-d H:i:s');

            $query->andWhere(['>', '`lead_calls`.datetime', $start_date]);
            $query->andWhere(['<', '`lead_calls`.datetime', $end_date]);
        }

        if (isset($this->filters['operator']) && !isset($this->filters['call_date'])) {
            $query->join('INNER JOIN', 'lead_calls', 'lead_calls.order_id = `order`.order_id');
            $query->andWhere(['lead_calls.operator_id' => $this->filters['operator']]);
        }

        return $query;
    }
}