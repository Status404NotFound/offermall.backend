<?php
/**
 * Created by PhpStorm.
 * User: evild
 * Date: 7/23/18
 * Time: 12:47 PM
 */

namespace crm\services\callcenter;

use Yii;
use common\models\callcenter\LeadCalls;
use common\models\callcenter\OperatorPcs;
use common\models\order\OrderStatus;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * Class StatisticsSrv
 *
 * @property $_owner_id
 * @property array $_list
 * @property array $_total
 * @property array $_pending_processing
 * @property integer $_count
 */

class StatisticsSrv
{
    private $_owner_id;
    public $_list;
    public $_total;
    public $_count;
    public $_pending_processing;
    
    private const _OPERATOR_PCS = 10;
    private const _LEAD_CALLS = 20;
    
    private static $_filters_type = [
        self::_OPERATOR_PCS => true,
        self::_LEAD_CALLS => true
    ];
    
    private static $_filters_dependencies = [
        'user' => false,
        'offer' => false,
        'order' => false,
        'target_advert' => false,
        'target_advert_group' => false,
        'advert_offer_target' => false
    ];
    
    public function __construct($filters)
    {
        $this->_owner_id = Yii::$app->user->identity->getOwnerId();
        $this->_list = $this->getOperatorsStatistics($filters);
        $this->_total = $this->totalRows($this->_list);
        $this->_count = \count($this->_list);
        $this->_pending_processing = $this->getLeadCalls($filters);
    }
    
    public function getOperatorsStatistics($filters)
    {
        $pcs = $this->getPcs($filters);
            foreach (self::$_filters_dependencies as &$tables) {
                $tables = false;
            }
        
        $calls = $this->getCalls($filters);
            foreach (self::$_filters_dependencies as &$tables) {
                $tables = false;
            }
            
        $statistics = [];
        foreach ($calls as $operator_id => $row) {
            $statistics[$operator_id] = [
                'operator_id' => $operator_id,
                'operator_name' => $row['operator_name'],
                'calls' => $row['call_count'],
                'up_sale' => $pcs[$operator_id] ?? 0,
            ];
            $filters['operator'] = $operator_id;
            $operator_order_statistics = $this->getOperatorOrderStatistics($filters);
            
            $statistics[$operator_id]['orders'] = $operator_order_statistics['total'];
            $statistics[$operator_id]['incorrect'] = $operator_order_statistics['incorrect'];
            $statistics[$operator_id]['successfully_processed'] = $operator_order_statistics['successfully_processed'];
            $statistics[$operator_id]['approved'] = $operator_order_statistics['approved'];
            $statistics[$operator_id]['approved_rate'] = $operator_order_statistics['approved_rate'];
            $statistics[$operator_id]['up_sale_rate'] = isset($statistics[$operator_id]['up_sale']) ? ($statistics[$operator_id]['up_sale'] / $operator_order_statistics['total']) * 100 : 0;
        }
        
        $sort_order = Yii::$app->request->getBodyParam('sortOrder') ? SORT_DESC : SORT_ASC;
        $sort_field = Yii::$app->request->getBodyParam('sortField');
    
        if ( !empty($sort_field)) {
            ArrayHelper::multisort($statistics, $sort_field, $sort_order);
        }
    
        return array_values($statistics);
    }
    
    public function getPcs($filters)
    {
        $operatorPcs = OperatorPcs::find()->select(['up_sale', 'operator_pcs.operator_id']);
        $this->filterQuery($operatorPcs, $filters, self::_OPERATOR_PCS);
        $pcs = [];
        foreach ($operatorPcs->groupBy(['operator_pcs.order_id'])->asArray()->all() as $val) {
            if (isset($pcs[$val['operator_id']])) {
                $pcs[$val['operator_id']] += $val['up_sale'];
            } else {
                $pcs[$val['operator_id']] = $val['up_sale'];
            }
        }
        
        return $pcs;
    }
    
    public function getCalls($filters)
    {
        $leadCalls = LeadCalls::find()->select(['COUNT(`lead_calls`.id) as call_count', '`lead_calls`.operator_id', 'username as operator_name']);
        $this->joinUser($leadCalls, self::_LEAD_CALLS);
        $this->filterQuery($leadCalls, $filters, self::_LEAD_CALLS);
    
        return ArrayHelper::index($leadCalls->groupBy('operator_id')->asArray()->all(), 'operator_id');
    }
    
    private function getOperatorOrderStatistics($filters)
    {
        $reject_incorrect_status_reason = '(22, 20, 18, 13, 10, 12, 4, 23)';
        $reject_success_processed_status_reason = '(0, 1, 2, 3, 5, 6, 7, 8, 9, 11, 14, 15, 16, 17, 19, 21, 24, null)';
        
        $approved_statuses = "(" .
             OrderStatus::WAITING_DELIVERY . ", " .
             OrderStatus::DELIVERY_IN_PROGRESS . ", " .
             OrderStatus::SUCCESS_DELIVERY . ", " .
             OrderStatus::CANCELED . ", " .
             OrderStatus::NOT_PAID . ", " .
             OrderStatus::RETURNED . ")";

        $leadCalls = LeadCalls::find()
            ->select([
                "lead_calls.operator_id",
                "SUM(if(order.order_status = " . OrderStatus::REJECTED . " and order.status_reason in " . $reject_success_processed_status_reason . ", 1, 0 )) AS reject_correct",
                "SUM(if(order.order_status = " . OrderStatus::REJECTED . " and order.status_reason in " . $reject_incorrect_status_reason . ", 1, 0 )) AS incorrect",
                "SUM(if(order.order_status in " . $approved_statuses . ", 1, 0 )) AS `approved`",
                "COUNT(order.order_id) as total"])
            ->leftJoin('order', 'lead_calls.order_id = order.order_id');
        self::$_filters_dependencies['order'] = true;
        $this->filterQuery($leadCalls, $filters, self::_LEAD_CALLS);
    
        foreach (self::$_filters_dependencies as &$tables) {
            $tables = false;
        }
    
        $orders = [];
        foreach ($leadCalls->groupBy('`order`.`order_id`')->asArray()->all() as $value) {
            
            $success_processed = $value['approved'] + $value['reject_correct'];
            $orders['successfully_processed'] = isset($orders['successfully_processed']) ? $orders['successfully_processed'] + $success_processed : $success_processed;
            $orders['incorrect'] = isset($orders['incorrect']) ? $orders['incorrect'] + $value['incorrect'] : $value['incorrect'];
            $orders['approved'] = isset($orders['approved']) ? $orders['approved'] + $value['approved'] : $value['approved'];
            //$orders['calls'] = isset($orders['total']) ? $orders['total']+$value['total'] : $value['total'];
            $orders['total'] = isset($orders['total']) ? $orders['total'] + 1 : 1;
            $orders['approved_rate'] = isset($orders['approved']) ? (($orders['approved'] + $value['approved']) / $orders['total']) * 100 : $value['approved'];
        }

        return $orders;
    }
    
    private function filterQuery(ActiveQuery $query, $filters, $filter_type)
    {
        if ( !isset(self::$_filters_type[$filter_type])) {
    
            return false;
        }

        if ( !is_null($this->_owner_id) || isset($filters['owner_id'])) {
            $this->joinOrder($query, $filter_type);
            $this->joinTargetAdvert($query);
            $query->where(['target_advert.advert_id' => $this->_owner_id ?: $filters['owner_id']]);
        }
    
        if (isset($filters['offer'])) {
            $this->joinOrder($query, $filter_type);
            $this->joinOffer($query);
            $query->andWhere(['order.offer_id' => $filters['offer']]);
        }
    
        if (isset($filters['operator'])) {
            $this->joinUser($query, $filter_type);
            switch ($filter_type) {
                case self::_OPERATOR_PCS:
                    $query->andWhere(['operator_pcs.operator_id' => $filters['operator']]);
                    break;
                case self::_LEAD_CALLS:
                    $query->andWhere(['lead_calls.operator_id' => $filters['operator']]);
                    break;
            }
        }
        
        if (isset($filters['geo'])) {
            $this->joinOrder($query, $filter_type);
            $this->joinTargetAdvert($query);
            $this->joinTargetAdvertGroup($query);
            $this->joinAdvertOfferTarget($query);
            $query->andWhere(['advert_offer_target.geo_id' => $filters['geo']]);
        }
    
        if (isset($filters['created_at'])) {
            $start = new \DateTime($filters['created_at']['start']);
            $start->setTime(0, 0, 0);
            $start_date = $start->format('Y-m-d H:i:s');
        
            $end = new \DateTime($filters['created_at']['end']);
            $end->setTime(23, 59, 59);
            $end_date = $end->format('Y-m-d H:i:s');
    
            switch ($filter_type) {
                case self::_OPERATOR_PCS:
                    $query->andWhere(['>', 'operator_pcs.created_at', $start_date]);
                    $query->andWhere(['<', 'operator_pcs.created_at', $end_date]);
                    break;
                case self::_LEAD_CALLS:
                    $query->andWhere(['>', 'lead_calls.datetime', $start_date]);
                    $query->andWhere(['<', 'lead_calls.datetime', $end_date]);
                    break;
            }
        }
    }
    
    private function joinUser(ActiveQuery $query, $filter_type): void
    {
        if ( !self::$_filters_dependencies['user']) {
            switch ($filter_type) {
                case self::_OPERATOR_PCS:
                    $query->leftJoin('user', 'user.id = operator_pcs.operator_id');
                    break;
                case self::_LEAD_CALLS:
                    $query->leftJoin('user', 'user.id = lead_calls.operator_id');
                    break;
            }
            self::$_filters_dependencies['user'] = true;
        }
    }
    
    private function joinOrder(ActiveQuery $query, $filter_type): void
    {
        if ( !self::$_filters_dependencies['order']) {
            switch ($filter_type) {
                case self::_OPERATOR_PCS:
                    $query->leftJoin('order', 'operator_pcs.order_id = order.order_id');
                    break;
                case self::_LEAD_CALLS:
                    $query->leftJoin('order', 'lead_calls.order_id = order.order_id');
                    break;
            }
            self::$_filters_dependencies['order'] = true;
        }
    }
    
    private function joinTargetAdvert(ActiveQuery $query): void
    {
        if ( !self::$_filters_dependencies['target_advert']) {
            $query->leftJoin('target_advert', 'order.target_advert_id = target_advert.target_advert_id');
            self::$_filters_dependencies['target_advert'] = true;
        }
    }
    
    private function joinTargetAdvertGroup(ActiveQuery $query): void
    {
        if ( !self::$_filters_dependencies['target_advert_group']) {
            $query->leftJoin('target_advert_group', 'target_advert.target_advert_group_id = target_advert_group.target_advert_group_id');
            self::$_filters_dependencies['target_advert_group'] = true;
        }
    }
    
    private function joinAdvertOfferTarget(ActiveQuery $query): void
    {
        if ( !self::$_filters_dependencies['advert_offer_target']) {
            $query->leftJoin('advert_offer_target', 'target_advert_group.advert_offer_target_id = advert_offer_target.advert_offer_target_id');
            self::$_filters_dependencies['advert_offer_target'] = true;
        }
    }
    
    private function joinOffer(ActiveQuery $query): void
    {
        if ( !self::$_filters_dependencies['offer']) {
            $query->leftJoin('offer', 'offer.offer_id = order.offer_id');
            self::$_filters_dependencies['offer'] = true;
        }
    }
    
    public function getLeadCalls($filters = [])
    {
        $leadCalls = LeadCalls::find()
            ->select([
                "`offer`.offer_name",
                "`lead_calls`.order_id",
                "COUNT(*) as total"
            ])
            ->join('join', 'order', 'order.order_id = lead_calls.order_id')
            ->join('join', 'offer', 'offer.offer_id = order.offer_id');
        self::$_filters_dependencies['order'] = true;
        self::$_filters_dependencies['offer'] = true;
        $this->filterQuery($leadCalls, $filters, self::_LEAD_CALLS);

        $res = [];
        foreach ($leadCalls->groupBy('order_id')->asArray()->all() as $key => $item) {
            $cnt = $item['total'];
            
            switch ($cnt) {
                case 1:
                    //$res['one'][] = $item;
                    $res[$item['offer_name']]['one'][] = $item;
                    break;
                case 2:
                    //                    $res['two'][] = $item;
                    $res[$item['offer_name']]['two'][] = $item;
                    break;
                case 3:
                    //                    $res['three'][] = $item;
                    $res[$item['offer_name']]['three'][] = $item;
                    break;
                case 4:
                    //                    $res['four'][] = $item;
                    $res[$item['offer_name']]['four'][] = $item;
                    break;
                case $cnt > 4:
                    //                    $res['more'][] = $item;
                    $res[$item['offer_name']]['more'][] = $item;
                    break;
            }
        }
        
        return $res;
    }
    
    private function totalRows($rows)
    {
        if (empty($rows)) return [];
        
        $total = [];
        foreach ($rows as $order) {
            
            unset($order['operator_id']);
            unset($order['operator_name']);
            
            foreach ($order as $key => $row) {
                if (isset($total[$key])) {
                    $total[$key] += $row;
                } else {
                    $total[$key] = $row;
                }
            }
        }
        
        $total['approved_rate'] = !empty($total['approved']) ? $total['approved'] / $total['orders'] * 100 : 0;
        $total['up_sale_rate'] = !empty($total['up_sale']) ? $total['up_sale'] / $total['orders'] * 100 : 0;
        
        return $total;
    }
}