<?php

namespace common\services\callcenter\call_list;

use common\models\callcenter\CallList;
use common\models\callcenter\CallListView;
use common\models\customer\Customer;
use common\models\DataList;
use common\models\Instrument;
use common\models\offer\targets\advert\sku\TargetAdvertSku;
use common\models\order\Order;
use common\models\order\OrderSku;
use common\models\order\OrderStatus;
use common\services\log\LogSrv;
use common\services\order\OrderCommonService;
use common\services\order\OrderNotFoundException;
use common\services\timezone\TimeZoneSrv;
use Yii;
use yii\base\Exception;
use yii\helpers\ArrayHelper;

class CallListService
{
    public function searchFormView($params = [])
    {
        $owner_id = !Yii::$app->user->isGuest ? Yii::$app->user->identity->getOwnerId() : null;

        $query = CallListView::find();

        if (!is_null($owner_id)) $query->where(['owner_id' => $owner_id]);

        if (isset($params['call_list_updated_at'])) $query->andWhere(['>', 'call_list_updated_at', $params['call_list_updated_at']]);

        return $query;
    }

    public function getCallList($params = []){

        if (!array_key_exists('operator_id', $params) && Yii::$app->operator->is_config_operator) $params['operator_id'] = Yii::$app->operator->id;
        else throw new Exception('You are not operator!');

        $available_geo = ArrayHelper::getColumn((new DataList())->getOffersGeo(), 'country_id');

        $tz = new TimeZoneSrv();
        $callList = $this->searchFormView($params)
            ->select([
                "call_list_view.*",
                "convert_tz(call_list_view.created_at, '+00:00', '" . $tz->time_zone_offset . "') as created_at",
            ])

            ->andWhere(['order_status' => OrderStatus::PENDING])
            ->orFilterWhere(['order_status' => OrderStatus::BACK_TO_PENDING])

            ->andWhere(['lead_state' => LeadStatus::STATE_FREE])

            ->andWhere(['is_enable_language' => true])
            ->andWhere(['language_operator_id' => $params['operator_id']])

            ->andWhere(['is_enable_offer' => true])
            ->andWhere(['offer_operator_id' => $params['operator_id']])

            ->andWhere(['paid_online' => false])

            ->andWhere(['country_id' => $available_geo])
            ->andWhere(['owner_id' => Yii::$app->user->identity->getOwnerId()])

            ->groupBy('order_id')
            ->orderBy(['attempts' => SORT_ASC])
            ->asArray()
            ->all();

//        var_dump($callList->prepare(Yii::$app->db->queryBuilder)->createCommand()->rawSql);exit();

        return $callList;
    }

    public function getPlanCalls($auto_mode = false, $operator_id = null){

        if ($operator_id === null) $operator_id = Yii::$app->operator->id;

        $tz = new TimeZoneSrv();
        $list = $this->searchFormView([]);
        $list->select([
            "call_list_view.*",
            "convert_tz(call_list_view.created_at, '+00:00', '" . $tz->time_zone_offset . "') as created_at",
        ])
            ->andWhere(['order_status' => OrderStatus::PENDING])
            ->orFilterWhere(['order_status' => OrderStatus::BACK_TO_PENDING]);

        $list->andWhere(['operator_id' => $operator_id]);
        $list->andWhere(['order_status' => OrderStatus::PENDING]);

        $list->andWhere(['lead_state' => LeadStatus::STATE_PLAN]);

        if ($auto_mode){
//            $list->andWhere('time_to_call < NOW()');
            $list->andWhere("time_to_call < convert_tz(NOW(), '+00:00', '" . $tz->time_zone_offset . "')");
            $list->groupBy('order_id');
            $list->orderBy(['time_to_call' => SORT_ASC]);
            $result = $list->asArray()->one();
            return $result;
        }

        $list->groupBy('order_id');
        $list->orderBy(['time_to_call' => SORT_ASC]);

        $auto_mode ? $list->limit(1) : $list->limit(100);

        $result = $list->asArray()
            ->all();

        return $result;
    }

    public function getToDoCalls($operator_id = null){

        if ($operator_id === null) $operator_id = Yii::$app->operator->id;

        $params = [
            'operator_id' => $operator_id,
            'lead_state' => LeadStatus::STATE_TODO,
        ];

        $list = $this->searchFormView($params)
            ->andWhere(['order_status' => OrderStatus::PENDING])
            ->orFilterWhere(['order_status' => OrderStatus::BACK_TO_PENDING]);

        $list->andWhere(['operator_id' => $operator_id]);
        $list->andWhere(['lead_state' => LeadStatus::STATE_TODO]);
        $list->andWhere(['order_status' => OrderStatus::PENDING]);
//        $list->groupBy(['order_id', 'is_enable_offer', 'offer_operator_id', 'language_operator_id', 'is_enable_language']);
        $list->groupBy('order_id');
        $list->orderBy(['created_at' => SORT_DESC]);

        $result = $list->asArray()
            ->all();

        return $result;
    }


    public function getOrder($order_id){

        $order = $this->searchFormView([])
            ->andWhere(['order_id' => $order_id])
            ->asArray()
            ->one();
    
        $order['area_id'] = Customer::find()->select('area_id')->where(['customer_id' => $order['customer_id']])->asArray()->one()['area_id'];
        $order['order_sku'] = OrderSku::findListByOrderId($order_id);
        $order['sku_list'] = TargetAdvertSku::findAdvertSku($order['target_advert_id']);
        
        return $order;
    }


    public function reserveLeadByOperator($order_id, $operator_id){

        $order = CallList::find()
            ->join('LEFT JOIN', 'order', 'order.order_id = call_list.order_id')
            ->where(['call_list.order_id' => $order_id])
            ->andWhere(['order.order_status' => [OrderStatus::PENDING, OrderStatus::BACK_TO_PENDING]])
            ->andWhere(['call_list.operator_id' => null])
            ->one();

        if (!empty($order)){

            if (
                (empty($order->operator_id) || $order->operator_id === $operator_id)
                && $order->lead_state != LeadStatus::STATE_TODO
                && $order->lead_state != LeadStatus::STATE_PLAN
            ){
                $order->operator_id = $operator_id;
                $order->lead_state = LeadStatus::STATE_TODO;
//                $order->attempts +=1;

                if ($order->update(false))
                {
                    if (!$order = Order::findOne(['order_id' => $order_id])) throw new OrderNotFoundException('OrderNotFound');
                    $order->instrument = Instrument::CALL_CENTER_TAKE_LEAD;
                    $comment = "<span style='color: #00bba7;'> Take Lead</span>";

                    try{
                        (new OrderCommonService())->saveComment($order, $comment);
                    } catch (\Exception $e)
                    {

                    }

                    return true;
                }
            }

        }

        return false;
    }



    public static function changePriority($order_id){
        $call = CallList::findOne($order_id);
        $call->lead_state = LeadStatus::STATUS_HIGH_PRIORITY;
        if($call->update(true, ['lead_state'])) return true;
        else return false;
    }

    public function checkPendingToDoLeads()
    {
        $pending_leads = CallList::find()
            ->select(["call_list.*"])
            ->where(['lead_state' => [LeadStatus::STATE_PENDING, LeadStatus::STATE_TODO]])
            ->andWhere('updated_at + INTERVAL 20 MINUTE < NOW()')
            ->all();

        foreach ($pending_leads as $pending_lead)
        {
            $pending_lead->lead_state = LeadStatus::STATE_FREE;
            $pending_lead->operator_id = null;
            $pending_lead->time_to_call = null;
            $pending_lead->update();
        }

        return true;
    }

    public function checkPendingPlanLeads()
    {
        $tz = new TimeZoneSrv();
        $pending_leads = CallList::find()
            ->select([
                "call_list.*",
            ])
            ->where(['lead_state' => LeadStatus::STATE_PLAN])
            ->andWhere("time_to_call + INTERVAL 140 MINUTE < convert_tz(NOW(), '+00:00', '" . $tz->time_zone_offset . "')")
            ->all();


        foreach ($pending_leads as $pending_lead)
        {
            $pending_lead->lead_state = LeadStatus::STATE_FREE;
            $pending_lead->operator_id = null;
            $pending_lead->time_to_call = null;
            $pending_lead->update();
        }

        return true;
    }

    public function getReservedLeads($params)
    {
        $callList = $this->searchFormView($params)
            ->select([
                "order_id",
            ])
            ->andWhere(['lead_state' => LeadStatus::STATE_TODO])
            ->andWhere(['lead_state' => LeadStatus::STATE_PLAN])
            ->groupBy('order_id')
            ->asArray()
            ->all();

//        var_dump($callList->prepare(Yii::$app->db->queryBuilder)->createCommand()->rawSql);exit();

        return $callList;
    }

//var_dump($pending_leads->prepare(Yii::$app->db->queryBuilder)->createCommand()->rawSql);exit;

}