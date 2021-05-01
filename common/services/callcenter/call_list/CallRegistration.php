<?php
/**
 * Created by PhpStorm.
 * User: ihor
 * Date: 21.03.17
 * Time: 11:55
 */

namespace common\services\callcenter\call_list;


use common\models\callcenter\CallList;
use common\models\order\Order;
use common\services\callcenter\auto\AutoModeSrv;
use phpDocumentor\Reflection\Types\Null_;
use yii\base\Exception;

class CallRegistration
{

    public $order_id;
    public $registered_call;


    public function __construct($order_id, $config)
    {

        if(!empty($order_id)){

            $this->order_id = $order_id;

            $this->registered_call = CallList::find()->where(['order_id' => $this->order_id])->one();

            if (empty($this->registered_call)){

                $current_dateTime = date('Y-m-d H:i:s');
                $order = Order::findOne($this->order_id);
                $country_id = $order->customer->country_id;

                $new_call = new CallList();
                $new_call->order_id = $this->order_id;
                $new_call->lead_status = $this->getLeadGroupByTimeRegistration($current_dateTime);
                $new_call->lead_state = LeadStatus::STATE_FREE;

                if ($country_id == 190 || $country_id == 111) $new_call->language_id = 2;
                else $new_call->language_id = 1;

                if (isset($config['operator_id']))
                {
                    $new_call->operator_id = $config['operator_id'];
                    $new_call->lead_state = LeadStatus::STATE_TODO;
                }

                if ($new_call->save())
                {
                    $autoModeSrv = new AutoModeSrv();
                    try{ $autoModeSrv->setOrderQueue($order); }catch (Exception $e){}
                    return true;
                }
                var_dump($new_call->errors);

//                $this->checkPhoneNumber();
            }

        }else{
            throw new Exception('No order Id for registration to call list!');
        }

    }

    public function checkPhoneNumber(){

    }

    public function getLeadGroupByTimeRegistration($dateTime){

        $group_time = [
            LeadStatus::STATUS_NEW_MORNING => [
                'from' => date('H:i:s', strtotime('06:00:00')),
                'to' => date('H:i:s', strtotime('08:00:00')),
            ],
            LeadStatus::STATUS_NEW_DAY => [
                'from' => date('H:i:s', strtotime('08:00:00')),
                'to' => date('H:i:s', strtotime('19:00:00')),
            ],
            LeadStatus::STATUS_NEW_EVENING => [
                'from' => date('H:i:s', strtotime('19:00:00')),
                'to' => date('H:i:s', strtotime('23:00:00')),
            ],
            LeadStatus::STATUS_NEW_NIGHT =>[
                'from' => date('H:i:s', strtotime('23:00:00')),
                'to' => date('H:i:s', strtotime('06:00:00')),
            ],
        ];

        foreach ($group_time as $key => $time){

            if ($dateTime > $time['from'] && $dateTime < $time['to']) $lead_group = $key;

        }

        return $lead_group;

    }

}