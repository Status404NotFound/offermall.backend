<?php
/**
 * Created by PhpStorm.
 * User: ihor-fish
 * Date: 11.04.18
 * Time: 16:45
 */

namespace crm\services\statistic;


use common\models\callcenter\OperatorPcs;
use common\models\order\Order;
use common\models\order\OrderStatus;
use common\models\statistics\Statistics;
use crm\services\callcenter\StatisticsSrv;
use Yii;
use yii\db\ActiveQuery;

class OperatorRating
{
    public $owner_id;
    public $filters;

    const APPROVED_STATUSES = "(" .
    OrderStatus::WAITING_DELIVERY . ", " .
    OrderStatus::DELIVERY_IN_PROGRESS . ", " .
    OrderStatus::SUCCESS_DELIVERY . ", " .
    OrderStatus::CANCELED . ", " .
    OrderStatus::RETURNED . ", " .
    OrderStatus::NOT_PAID . ")";

    public function __construct($filters = [])
    {
        $this->owner_id = Yii::$app->user->identity->getOwnerId();
        $this->filters = $filters;
    }


    public function getOperatorsTodayStatistics()
    {
        $pcs = $this->userPcs();

        $query = Order::find()
            ->select([
                "SUM(if(`order`.order_status = " . OrderStatus::WAITING_DELIVERY . ", 1, 0 )) AS `waiting_for_delivery`",
                "SUM(if(`order`.order_status = " . OrderStatus::REJECTED . ", 1,0 )) AS `rejected`",
                "SUM(if(`order`.order_status = " . OrderStatus::REJECTED . " and order.status_reason in " . Statistics::VALID_REJECT . ", 1, 0 )) AS `valid_reject`",
                "SUM(if(`order`.order_status = " . OrderStatus::REJECTED . " and order.status_reason in " . Statistics::NOT_VALID_REJECT . ", 1, 0 )) AS `not_valid_reject`",
                "SUM(if(`order`.order_status in " . self::APPROVED_STATUSES . ", 1, 0 )) AS `approved`",
                "SUM(if(`order`.total_amount > 1 and order.order_status in " . self::APPROVED_STATUSES . ", 1, 0)) AS up_sale",
//                "SUM(CASE WHEN `order`.total_amount > 1 THEN 1 ELSE 0 END) AS pcs",
                "COUNT(*) as total_calls",
                "user.username as user_name",
                "lead_calls.operator_id",
                "base_profile.avatar",
            ])
            ->join('LEFT JOIN', 'target_advert', 'target_advert.target_advert_id = `order`.target_advert_id')
            ->join('LEFT JOIN', 'target_advert_group', 'target_advert_group.target_advert_group_id = target_advert.target_advert_group_id')
            ->join('LEFT JOIN', 'advert_offer_target', 'target_advert_group.advert_offer_target_id = advert_offer_target.advert_offer_target_id')
            ->join('LEFT JOIN', 'offer', 'advert_offer_target.offer_id = offer.offer_id')
            ->join('INNER JOIN', 'lead_calls', 'lead_calls.order_id = `order`.order_id')
            ->join('LEFT JOIN', 'user', 'user.id = lead_calls.operator_id')
            ->join('LEFT JOIN', 'base_profile', 'user.id = base_profile.user_id')
            ->where(['`order`.deleted' => 0]);

        $this->applyFilters($query);
        if (!isset($this->filters['date'])) $query->andWhere("DATE(lead_calls.datetime) = DATE(NOW())");
        if (!is_null($this->owner_id)) $query->andWhere(['target_advert.advert_id' => $this->owner_id]);
        $query->andWhere('lead_calls.operator_id not in (14, 13, 20, 24, 27)');
        $data = $query->groupBy('lead_calls.operator_id')->indexBy('operator_id')->asArray()->all();

        foreach ($data as $operator_id => $info)
        {
            if (isset($pcs[$operator_id])) $data[$operator_id]['up_sale'] = $pcs[$operator_id]['up_sale'];
        }

        return array_values($data);
    }

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

        if (isset($this->filters['operator']) && !isset($this->filters['call_date'])){
            $query->andWhere(['lead_calls.operator_id' => $this->filters['operator']]);
        }

        return $query;
    }

    private function userPcs()
    {
        $query = OperatorPcs::find();
        $query->select([
            'SUM(up_sale) AS up_sale',
            'operator_id',
        ]);
        $query->join('LEFT JOIN', 'order', 'operator_pcs.order_id=order.order_id');
        $query->andWhere('order.order_status in ' . self::APPROVED_STATUSES);
        $query->andWhere("DATE(operator_pcs.updated_at) = DATE(NOW())");
        $result = $query->groupBy('operator_id')->indexBy('operator_id')->asArray()->all();
        return $result;
    }
}