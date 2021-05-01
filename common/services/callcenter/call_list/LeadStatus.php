<?php
/**
 * Created by PhpStorm.
 * User: ihor
 * Date: 18.08.17
 * Time: 12:08
 */

namespace common\services\callcenter\call_list;


use common\models\callcenter\CallList;
use common\models\Instrument;
use common\models\order\Order;
use common\services\log\LogSrv;

class LeadStatus
{
    const STATUS_NEW = 1;
    const STATUS_DAY_OLD =2;
    const STATUS_PRE_SLEEPER = 3;
    const STATUS_SLEEPER = 4;

    const STATUS_HIGH_PRIORITY = 5;

    const STATUS_NEW_MORNING = 6;
    const STATUS_NEW_DAY = 7;
    const STATUS_NEW_EVENING = 8;
    const STATUS_NEW_NIGHT = 9;

    const STATE_FREE = 1;
    const STATE_TODO = 2;
    const STATE_PLAN = 3;
    const STATE_PENDING = 4;

    public static function getIndexedStatuses()
    {
        $statuses = [];
        foreach (self::getStatuses() as $status_id => $status_name)
        {
            $statuses[] = ['status_id'=>$status_id, 'status_name' => $status_name];
        }
        return $statuses;
    }

    public static function getIndexedStates()
    {
        $states = [];
        foreach (self::getStates() as $state_id => $state_name){
            $states[] = ['state_id' => $state_id, 'state_name' => $state_name];
        }
        return $states;
    }

    public static function getStatuses(){
        return [
            self::STATUS_NEW => 'New Lead',
            self::STATUS_DAY_OLD => 'Day old',
            self::STATUS_PRE_SLEEPER => 'Pre sleeper',
            self::STATUS_SLEEPER => 'Sleeper',
            self::STATUS_NEW_MORNING => 'New morning lead',
            self::STATUS_NEW_DAY => 'New day lead',
            self::STATUS_NEW_EVENING => 'New evening lead',
            self::STATUS_NEW_NIGHT => 'New night lead',
            self::STATUS_HIGH_PRIORITY => 'High priority',
        ];
    }

    public static function getStates(){
        return[
            self::STATE_FREE => 'Free lead',
            self::STATE_TODO => 'Reserved lead',
            self::STATE_PLAN => 'Planed lead',
            self::STATE_PENDING => 'Pending lead',
        ];
    }

    public function change($order_id, $lead_status)
    {
        $lead = CallList::findOne(['order_id' => $order_id]);
        if ($lead && isset(self::getStatuses()[$lead_status])){
            $lead->lead_status = $lead_status;
            $log = new LogSrv($lead, Instrument::LMC_CALL_CENTER);
            if ($lead->update()){
                $log->add();
                return true;
            }
        }

        return false;
    }
}