<?php
namespace common\services\callcenter\auto;

use common\models\callcenter\CallList;
use common\models\callcenter\CallQueue;
use common\models\order\Order;
use common\models\order\OrderStatus;
use common\modules\user\models\tables\User;
use common\services\callcenter\call_list\CallListService;
use common\services\callcenter\call_list\LeadStatus;
use common\services\timezone\TimeZoneSrv;
use Yii;

class AutoModeSrv extends CallListService
{
    public $generated_order;

    public function __construct()
    {
        $this->generated_order = !Yii::$app->user->isGuest && Yii::$app->user->identity->role == User::ROLE_OPERATOR ? $this->getGeneratedOrderId() : [];
    }


    public function setOrderQueue(Order $order)
    {
        $queues = CallQueue::find()
            ->where(['advert_id' => $order->targetAdvert->advert_id])
            ->asArray()
            ->all();

        $values = [
            'geo_id' => $order->customer->country_id,
            'attempts' => $order->callList->attempts,
            'offer_id' => $order->offer_id,
            'language' => $order->callList->language_id,
            'lead_status' => $order->callList->lead_status,
        ];

        $available_queues = [];

        foreach ($queues as $queue)
        {
            if (!isset($available_queues[$queue['queue_id']])) $available_queues[$queue['queue_id']] = 0;

            if (in_array($values['geo_id'], json_decode($queue['geo']))) $available_queues[$queue['queue_id']] +=1;
            if (in_array($values['offer_id'], json_decode($queue['offer']))) $available_queues[$queue['queue_id']] +=1;
            if (in_array($values['language'], json_decode($queue['language']))) $available_queues[$queue['queue_id']] +=1;
            if ($values['attempts'] >= json_decode($queue['attempts'], true)['min'] && $values['attempts'] <= json_decode($queue['attempts'],true)['max']) $available_queues[$queue['queue_id']] +=1;
            if (in_array($values['lead_status'], json_decode($queue['lead_status']))) $available_queues[$queue['queue_id']] +=1;
        }

        arsort($available_queues);
        $queues = array_keys($available_queues);
        $needle_queue = array_shift($queues);
        if ($needle_queue)
        {
            $order->callList->queue_id = $needle_queue;
            $order->callList->save();
        }

    }


    public function getGeneratedOrderId()
    {

        if ($order = $this->getPlanLead()) return $order;
        if ($order = $this->getFreshLead()) return $order;
        if ($order = $this->getMorningLead()) return $order;
        if ($order = $this->getYesterdayEveningLead()) return $order;
        if ($order = $this->getNightLead()) return $order;
        if ($order = $this->getFreshGroupLead()) return $order;
        if ($order = $this->getColdGroupLead()) return $order;

        return null;
    }


    private function getPlanLead()
    {
        $planed = $this->getPlanCalls(true);
        if (isset($planed)) return $planed;
        return false;
    }


    private function getMorningLead()
    {
        $tz = new TimeZoneSrv();

        $order_query = $this->searchFormView()
            ->select([
                "call_list_view.*",
                "convert_tz(call_list_view.created_at, '+00:00', '" . $tz->time_zone_offset . "') as created_at",
            ])
            ->andWhere(['order_status' => OrderStatus::PENDING])
            ->orFilterWhere(['order_status' => OrderStatus::BACK_TO_PENDING])

            ->andWhere(['lead_state' => LeadStatus::STATE_FREE])

            ->andWhere(['is_enable_language' => true])
            ->andWhere(['language_operator_id' => \Yii::$app->operator->id])

            ->andWhere(['is_enable_offer' => true])
            ->andWhere(['offer_operator_id' => \Yii::$app->operator->id])

            ->andWhere(['<', 'attempts', 1])

            ->andWhere("convert_tz(call_list_view.created_at, '+00:00', '" . $tz->time_zone_offset . "') > CURDATE() + INTERVAL 6 HOUR")
            ->andWhere("convert_tz(call_list_view.created_at, '+00:00', '" . $tz->time_zone_offset . "') < CURDATE() + INTERVAL 8 HOUR");

        $order_query->groupBy('call_list_view.order_id');
        $order = $order_query->orderBy(['created_at' => SORT_DESC])->asArray()->one();

        return $order;
    }


    private function getNightLead()
    {
        $tz = new TimeZoneSrv();

        $order_query = $this->searchFormView()
            ->select([
                "call_list_view.*",
                "convert_tz(call_list_view.created_at, '+00:00', '" . $tz->time_zone_offset . "') as created_at",
            ])
            ->andWhere(['order_status' => OrderStatus::PENDING])
            ->orFilterWhere(['order_status' => OrderStatus::BACK_TO_PENDING])

            ->andWhere(['lead_state' => LeadStatus::STATE_FREE])

            ->andWhere(['is_enable_language' => true])
            ->andWhere(['language_operator_id' => \Yii::$app->operator->id])

            ->andWhere(['is_enable_offer' => true])
            ->andWhere(['offer_operator_id' => \Yii::$app->operator->id])

            ->andWhere(['<', 'attempts', 1])

            ->andWhere("convert_tz(call_list_view.created_at, '+00:00', '" . $tz->time_zone_offset . "') > CURDATE() - INTERVAL 1 HOUR")
            ->andWhere("convert_tz(call_list_view.created_at, '+00:00', '" . $tz->time_zone_offset . "') < CURDATE() + INTERVAL 6 HOUR");

        $order_query->groupBy('call_list_view.order_id');
        $order = $order_query->orderBy(['created_at' => SORT_DESC])->asArray()->one();

        return $order;
    }

    private function getYesterdayEveningLead()
    {
        $tz = new TimeZoneSrv();

        $order_query = $this->searchFormView()
            ->select([
                "call_list_view.*",
                "convert_tz(call_list_view.created_at, '+00:00', '" . $tz->time_zone_offset . "') as created_at",
            ])
            ->andWhere(['order_status' => OrderStatus::PENDING])
            ->orFilterWhere(['order_status' => OrderStatus::BACK_TO_PENDING])

            ->andWhere(['lead_state' => LeadStatus::STATE_FREE])

            ->andWhere(['is_enable_language' => true])
            ->andWhere(['language_operator_id' => \Yii::$app->operator->id])

            ->andWhere(['is_enable_offer' => true])
            ->andWhere(['offer_operator_id' => \Yii::$app->operator->id])

            ->andWhere(['<', 'attempts', 1])

            ->andWhere("convert_tz(call_list_view.created_at, '+00:00', '" . $tz->time_zone_offset . "') > CURDATE() - INTERVAL 5 HOUR")
            ->andWhere("convert_tz(call_list_view.created_at, '+00:00', '" . $tz->time_zone_offset . "') < CURDATE() - INTERVAL 1 HOUR");

        $order_query->groupBy('call_list_view.order_id');
        $order = $order_query->orderBy(['created_at' => SORT_DESC])->asArray()->one();

        return $order;
    }

    private function getFreshLead()
    {
        $tz = new TimeZoneSrv();

        $order_query = $this->searchFormView()
            ->select([
                "call_list_view.*",
                "convert_tz(call_list_view.created_at, '+00:00', '" . $tz->time_zone_offset . "') as created_at",
            ])
            ->andWhere(['order_status' => OrderStatus::PENDING])
            ->orFilterWhere(['order_status' => OrderStatus::BACK_TO_PENDING])

            ->andWhere(['lead_state' => LeadStatus::STATE_FREE])

            ->andWhere(['is_enable_language' => true])
            ->andWhere(['language_operator_id' => \Yii::$app->operator->id])

            ->andWhere(['is_enable_offer' => true])
            ->andWhere(['offer_operator_id' => \Yii::$app->operator->id])

            ->andWhere(['attempts' => null])

            ->andWhere("call_list_view.created_at < NOW() - INTERVAL 5 MINUTE");

        $order_query->groupBy('call_list_view.order_id');
        $order = $order_query->orderBy(['created_at' => SORT_DESC])->asArray()->one();

        return $order;
    }

    private function getFreshGroupLead()
    {
        $tz = new TimeZoneSrv();

        $order_query = $this->searchFormView()
            ->select([
                "call_list_view.*",
                "convert_tz(call_list_view.created_at, '+00:00', '" . $tz->time_zone_offset . "') as created_at",
            ])
            ->andWhere(['order_status' => OrderStatus::PENDING])
            ->orFilterWhere(['order_status' => OrderStatus::BACK_TO_PENDING])

            ->andWhere(['lead_state' => LeadStatus::STATE_FREE])

            ->andWhere(['is_enable_language' => true])
            ->andWhere(['language_operator_id' => \Yii::$app->operator->id])

            ->andWhere(['is_enable_offer' => true])
            ->andWhere(['offer_operator_id' => \Yii::$app->operator->id])

            ->andWhere(['>=', 'attempts', 1])
            ->andWhere(['<', 'attempts', 4])

            ->andWhere('call_list_updated_at < NOW() - INTERVAL 4 HOUR');

        $order_query->orderBy(['attempts' => SORT_ASC]);
        $order_query->groupBy('call_list_view.order_id');
        $order = $order_query->asArray()->one();

        return $order;
    }

    private function getColdGroupLead()
    {
        $tz = new TimeZoneSrv();

        $order_query = $this->searchFormView()
            ->select([
                "call_list_view.*",
                "convert_tz(call_list_view.created_at, '+00:00', '" . $tz->time_zone_offset . "') as created_at",
            ])
            ->andWhere(['order_status' => OrderStatus::PENDING])
            ->orFilterWhere(['order_status' => OrderStatus::BACK_TO_PENDING])

            ->andWhere(['lead_state' => LeadStatus::STATE_FREE])

            ->andWhere(['is_enable_language' => true])
            ->andWhere(['language_operator_id' => \Yii::$app->operator->id])

            ->andWhere(['is_enable_offer' => true])
            ->andWhere(['offer_operator_id' => \Yii::$app->operator->id])

            ->andWhere(['>=', 'attempts', 4])
            ->andWhere(['<', 'attempts', 11])

            ->andWhere('call_list_updated_at < NOW() - INTERVAL 12 HOUR');

        $order_query->orderBy(['attempts' => SORT_ASC]);
        $order_query->groupBy('call_list_view.order_id');
        $order = $order_query->asArray()->one();

        return $order;
    }

//    private function getLeadByCallsCount()
//    {
//        $tz = new TimeZoneSrv();
//
//        $order_query = $this->searchFormView()
//            ->select([
//                "call_list_view.*",
//                "convert_tz(call_list_view.created_at, '+00:00', '" . $tz->time_zone_offset . "') as created_at",
//            ])
//            ->andWhere(['lead_state' => LeadStatus::STATE_FREE])
//
//            ->andWhere(['is_enable_language' => true])
//            ->andWhere(['language_operator_id' => \Yii::$app->operator->id])
//
//            ->andWhere(['is_enable_offer' => true])
//            ->andWhere(['offer_operator_id' => \Yii::$app->operator->id])
//            ->andWhere('call_list_updated_at < NOW() - INTERVAL 4 HOUR');
//
//        $order_query->orderBy(['attempts' => SORT_ASC]);
//        $order_query->groupBy('call_list_view.order_id');
//        $order = $order_query->asArray()->one();
//
//        return $order;
//    }
}