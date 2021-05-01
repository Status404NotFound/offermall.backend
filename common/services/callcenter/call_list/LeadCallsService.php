<?php
/**
 * Created by PhpStorm.
 * User: evild
 * Date: 4/26/18
 * Time: 4:01 PM
 */

namespace common\services\callcenter\call_list;

use common\models\callcenter\LeadCalls;
use Yii;

class LeadCallsService
{
    public function getOperatorHistory()
    {
        $start = new \DateTime('-1 day');
        $start->setTime(0, 0, 0);
        $start_date = $start->format('Y-m-d H:i:s');
    
        $end = new \DateTime('now');
        $end->setTime(23, 59, 59);
        $end_date = $end->format('Y-m-d H:i:s');
        
        $query = LeadCalls::find()
            ->select(['lead_calls.*',
                      'user.username as operator_name',
                      'order.order_hash',
                      'order.total_amount',
                      'order.order_status',
                      'order.customer_id',
                      'order.offer_id',
                      'operator_pcs.operator_pcs_id',
                      'customer.name',
                      'customer.phone',
                      'countries.id',
                      'countries.country_name',
                      'offer.offer_name'])
            ->join('LEFT JOIN', 'user', 'user.id = lead_calls.operator_id')
            ->join('LEFT JOIN', 'order', 'order.order_id = lead_calls.order_id')
            ->join('LEFT JOIN', 'operator_pcs', 'operator_pcs.order_id = lead_calls.order_id')
            ->join('LEFT JOIN', 'customer', 'customer.customer_id = order.customer_id')
            ->join('LEFT JOIN', 'countries', 'customer.country_id = countries.id')
            ->join('LEFT JOIN', 'offer', 'offer.offer_id = order.offer_id')
            ->where(['between', 'lead_calls.datetime', $start_date, $end_date ])
            ->andWhere(['lead_calls.operator_id' => Yii::$app->user->getId()])
            ->asArray()
            ->all();
        
        return $query;
    }
}