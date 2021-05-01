<?php

namespace crm\services\callcenter;

use common\models\callcenter\CallList;
use common\models\offer\targets\advert\sku\TargetAdvertSku;
use common\models\order\Order;
use common\models\order\OrderSku;
use common\models\order\OrderStatus;
use common\services\callcenter\call_list\LeadStatus;

class CallListCrmSrv
{
    private static $_callList_dependencies = [
        'order' => false,
        'target_advert' => false,
        'customer' => false,
        'offer' => false,
        'geo' => false,
        'language' => false,
        'user' => false,
    ];
    
    public function setGroupSettings($orders, $settings)
    {
        foreach ($orders as $key => $order_hash)
        {
            $order = Order::find()->where(['order_id' => $order_hash])->one();
            $callListOrderModel = CallList::findOne(['order_id' => $order->order_id]);
            if (isset($settings['language'])) $callListOrderModel->language_id = $settings['language'];
            if (isset($settings['operator'])) $callListOrderModel->operator_id = $settings['operator'];
            if (isset($settings['state']))
            {
                $state = $settings['state'];
                if ($state == LeadStatus::STATE_FREE)
                {
                    $callListOrderModel->operator_id = null;
                    $callListOrderModel->lead_state = LeadStatus::STATE_FREE;
                }

                if (($state == LeadStatus::STATE_TODO || $state == LeadStatus::STATE_PLAN) && !isset($settings['operator'])) throw new \yii\base\Exception('Not legal operation!');

                $callListOrderModel->lead_state = $state;
            }
            if (isset($settings['priority']) && $settings['priority'] == true) $callListOrderModel->lead_status = LeadStatus::STATUS_HIGH_PRIORITY;

            if (!$callListOrderModel->update()) throw new \yii\base\Exception('Settings are not applied!');

        }

        return true;
    }

    public function generalCallList($params)
    {
        $callList = CallList::find()
            ->select([
                'order.order_hash',
                'order.order_id',
                'customer.name',
                'offer.offer_name',
                'geo.geo_name as country_name',
                'language.name as language_name',
                'customer.phone',
                'lead_status',
                'lead_state',
                'if(lead_state = ' . LeadStatus::STATE_PLAN . ', time_to_call, null) as time_to_call',
                'order.created_at',
                'user.username as operator_name',
                'attempts'
            ])
            ->leftJoin('order', 'call_list.order_id = order.order_id')
            ->leftJoin('target_advert', 'order.target_advert_id = target_advert.target_advert_id')
            ->leftJoin('customer', 'customer.customer_id = order.customer_id')
            ->leftJoin('offer', 'offer.offer_id = order.offer_id')
            ->leftJoin('geo', 'geo.geo_id = customer.country_id')
            ->leftJoin('language', 'language.language_id = call_list.language_id')
            ->leftJoin('user', 'user.id = call_list.operator_id')
    
            ->where(['order_status' => OrderStatus::PENDING])
            ->orFilterWhere(['order_status' => OrderStatus::BACK_TO_PENDING]);
        $this->filterQuery($callList, $params['filters']);
    
        $callList_count = CallList::find();
        $this->joinOrder($callList_count);
        $callList_count->where(['order_status' => OrderStatus::PENDING])->orFilterWhere(['order_status' => OrderStatus::BACK_TO_PENDING]);
        $this->filterCountQuery($callList_count, $params['filters']);
        
        if ( !empty($owner_id = \Yii::$app->user->identity->getOwnerId())) {
            $callList->andWhere(['target_advert.advert_id' => $owner_id]);
            
            $this->joinTargetAdvert($callList_count);
            $callList_count->andWhere(['target_advert.advert_id' => $owner_id]);
        }
        
        if (isset($params['rows'])) {
            $callList->limit($params['rows']);
        }
    
        $result = [
            'call_list' => $callList->orderBy(['created_at'=>SORT_DESC])->offset($params['firstRow'])->groupBy('order_id')->asArray()->all(),
            'count' => $callList_count->count(),
        ];

        foreach ($result['call_list'] as &$lead){
            $lead['lead_status'] = LeadStatus::getStatuses()[$lead['lead_status']];
            $lead['lead_state'] = LeadStatus::getStates()[$lead['lead_state']];
        
            if ($lead['lead_status'] == LeadStatus::STATUS_HIGH_PRIORITY) {
                $lead['high_priority'] = 1;
            } else {
                $lead['high_priority'] = 0;
            }
        }
    
        return $result;
    }
    
    private function filterQuery($callList, $filters)
    {
        if (isset($filters['call_list_updated_at'])) {
            $callList->andWhere(['>', 'updated_at', $filters['updated_at']]);
        }
        if (isset($filters['order_id'])) {
            $callList->andWhere(['order.order_hash' => $filters['order_id']['value']]);
        }
        if (isset($filters['customer_name'])) {
            $callList->andWhere(['customer.customer_name' => $filters['customer_name']['value']]);
        }
        if (isset($filters['phone'])) {
            $callList->andWhere(['like', 'customer.phone', str_replace(['+', '-', ' '], '', $filters['phone']['value'])]);
        }
        if (isset($filters['offer'])) {
            $callList->andWhere(['offer.offer_id' => $filters['offer']['value']]);
        }
        if (isset($filters['country'])) {
            $callList->andWhere(['geo.geo_id' => $filters['country']['value']]);
        }
        if (isset($filters['language'])) {
            $callList->andWhere(['language.language_id' => $filters['language']['value']]);
        }
        if (isset($filters['operator'])) {
            $callList->andWhere(['user.id' => $filters['operator']['value']]);
        }
        if (isset($filters['status'])) {
            $callList->andWhere(['lead_status' => $filters['status']['value']]);
        }
        if (isset($filters['state'])) {
            $callList->andWhere(['lead_state' => $filters['state']['value']]);
        }
        if (isset($filters['attempts'])) {
            $callList->andWhere(['>=', 'attempts', $filters['attempts']['value']]);
        }
        if (isset($filters['high_priority'])) {
            $callList->andWhere(['lead_status' => LeadStatus::STATUS_HIGH_PRIORITY]);
        }
        if (isset($filters['date'])) {
            $start = new \DateTime($filters['date']['start']);
            $start->setTime(0, 0, 0);
            $start_date = $start->format('Y-m-d H:i:s');
        
            $end = new \DateTime($filters['date']['end']);
            $end->setTime(23, 59, 59);
            $end_date = $end->format('Y-m-d H:i:s');
    
            $callList->andWhere(['>', 'order.created_at', $start_date]);
            $callList->andWhere(['<', 'order.created_at', $end_date]);
        }
        if (isset($filters['time_to_call'])) {
            $start = new \DateTime($filters['time_to_call']['start']);
            $start->setTime(0, 0, 0);
            $start_date = $start->format('Y-m-d H:i:s');
        
            $end = new \DateTime($filters['time_to_call']['end']);
            $end->setTime(23, 59, 59);
            $end_date = $end->format('Y-m-d H:i:s');
    
            $callList->andWhere(['>', 'time_to_call', $start_date]);
            $callList->andWhere(['<', 'time_to_call', $end_date]);
            $callList->andWhere(['lead_state' => LeadStatus::STATE_PLAN]);
        }
    }
    
    private function filterCountQuery($callList, $filters)
    {
        if (isset($filters['call_list_updated_at'])) {
            $callList->andWhere(['>', 'updated_at', $filters['updated_at']]);
        }
        if (isset($filters['order_id'])) {
            $this->joinOrder($callList);
            $callList->andWhere(['order.order_hash' => $filters['order_id']['value']]);
        }
        if (isset($filters['customer_name'])) {
            $this->joinCustomer($callList);
            $callList->andWhere(['customer.customer_name' => $filters['customer_name']['value']]);
        }
        if (isset($filters['phone'])) {
            $this->joinCustomer($callList);
            $callList->andWhere(['like', 'customer.phone', str_replace(['+', '-', ' '], '', $filters['phone']['value'])]);
        }
        if (isset($filters['offer'])) {
            $this->joinOffer($callList);
            $callList->andWhere(['offer.offer_id' => $filters['offer']['value']]);
        }
        if (isset($filters['country'])) {
            $this->joinCustomer($callList);
            $this->joinGeo($callList);
            $callList->andWhere(['geo.geo_id' => $filters['country']['value']]);
        }
        if (isset($filters['language'])) {
            $this->joinLanguage($callList);
            $callList->andWhere(['language.language_id' => $filters['language']['value']]);
        }
        if (isset($filters['operator'])) {
            $this->joinUser($callList);
            $callList->andWhere(['user.id' => $filters['operator']['value']]);
        }
        if (isset($filters['status'])) {
            $callList->andWhere(['lead_status' => $filters['status']['value']]);
        }
        if (isset($filters['state'])) {
            $callList->andWhere(['lead_state' => $filters['state']['value']]);
        }
        if (isset($filters['attempts'])) {
            $callList->andWhere(['>=', 'attempts', $filters['attempts']['value']]);
        }
        if (isset($filters['high_priority'])) {
            $callList->andWhere(['lead_status' => LeadStatus::STATUS_HIGH_PRIORITY]);
        }
        if (isset($filters['date'])) {
            $this->joinOrder($callList);
    
            $start = new \DateTime($filters['date']['start']);
            $start->setTime(0, 0, 0);
            $start_date = $start->format('Y-m-d H:i:s');
        
            $end = new \DateTime($filters['date']['end']);
            $end->setTime(23, 59, 59);
            $end_date = $end->format('Y-m-d H:i:s');
    
            $callList->andWhere(['>', 'order.created_at', $start_date]);
            $callList->andWhere(['<', 'order.created_at', $end_date]);
        }
        if (isset($filters['time_to_call'])) {
            $start = new \DateTime($filters['time_to_call']['start']);
            $start->setTime(0, 0, 0);
            $start_date = $start->format('Y-m-d H:i:s');
        
            $end = new \DateTime($filters['time_to_call']['end']);
            $end->setTime(23, 59, 59);
            $end_date = $end->format('Y-m-d H:i:s');
    
            $callList->andWhere(['>', 'time_to_call', $start_date]);
            $callList->andWhere(['<', 'time_to_call', $end_date]);
            $callList->andWhere(['lead_state' => LeadStatus::STATE_PLAN]);
        }
    }
    
    private function joinOrder($callList): void
    {
        if ( !self::$_callList_dependencies['order']) {
            $callList->leftJoin('order', 'call_list.order_id = order.order_id');
            self::$_callList_dependencies['order'] = true;
        }
    }
    
    private function joinTargetAdvert($callList): void
    {
        if ( !self::$_callList_dependencies['target_advert']) {
            $callList->leftJoin('target_advert', 'order.target_advert_id = target_advert.target_advert_id');
            self::$_callList_dependencies['target_advert'] = true;
        }
    }
    
    private function joinCustomer($callList): void
    {
        if ( !self::$_callList_dependencies['customer']) {
            $callList->leftJoin('customer', 'order.customer_id = customer.customer_id');
            self::$_callList_dependencies['customer'] = true;
        }
    }
    
    private function joinOffer($callList): void
    {
        if ( !self::$_callList_dependencies['offer']) {
            $callList->leftJoin('offer', 'order.offer_id = offer.offer_id');
            self::$_callList_dependencies['offer'] = true;
        }
    }
    
    private function joinGeo($callList): void
    {
        if ( !self::$_callList_dependencies['geo']) {
            $callList->leftJoin('geo', 'geo.geo_id = customer.country_id');
            self::$_callList_dependencies['geo'] = true;
        }
    }
    
    private function joinLanguage($callList): void
    {
        if ( !self::$_callList_dependencies['language']) {
            $callList->leftJoin('language', 'language.language_id = call_list.language_id');
            self::$_callList_dependencies['language'] = true;
        }
    }
    
    private function joinUser($callList): void
    {
        if ( !self::$_callList_dependencies['user']) {
            $callList->leftJoin('user', 'user.id = call_list.operator_id');
            self::$_callList_dependencies['user'] = true;
        }
    }

    public function orderCard($order_id)
    {
        $order = Order::find()
            ->select([
                'order.order_id',
                'order.target_advert_id',
                'order_hash',
                'customer.name as customer_name',
                'address',
                'phone',
                'email',
                'country_name',
                'country_code as iso',
                'language.name as language',
                'offer_name',
            ])
            ->join('LEFT JOIN', 'offer', 'offer.offer_id = order.offer_id')
            ->join('LEFT JOIN', 'customer', 'order.customer_id = customer.customer_id')
            ->join('LEFT JOIN', 'call_list', 'call_list.order_id = order.order_id')
            ->join('LEFT JOIN', 'language', 'language.language_id = call_list.language_id')
            ->join('LEFT JOIN', 'target_advert', 'target_advert.target_advert_id = order.target_advert_id')
            ->join('LEFT JOIN', 'target_advert_group', 'target_advert_group.target_advert_group_id = target_advert.target_advert_group_id')
            ->join('LEFT JOIN', 'advert_offer_target', 'target_advert_group.advert_offer_target_id = advert_offer_target.advert_offer_target_id')
            ->join('LEFT JOIN', 'countries', 'advert_offer_target.geo_id = countries.id')
            ->where(['order.deleted' => false])
            ->andWhere(['order.order_id' => $order_id])
            ->groupBy('order.order_id')
            ->asArray()
            ->one();

        $order['order_sku']  = OrderSku::findListByOrderId($order_id);
        $order['sku_list'] = TargetAdvertSku::findAdvertSku($order['target_advert_id']);

        return $order;
    }
}