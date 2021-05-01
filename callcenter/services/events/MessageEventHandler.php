<?php

namespace callcenter\services\events;

use common\models\callcenter\CallList;
use common\models\callcenter\OperatorConf;
use common\models\Instrument;
use common\models\log\CallListLog;
use common\services\callcenter\call_list\CallListService;
use common\services\callcenter\call_list\LeadStatus;
use common\services\callcenter\OperatorSettingsSrv;
use odannyc\Yii2SSE\SSEBase;
use Yii;

class MessageEventHandler extends SSEBase
{
    public $last_send_date;

    public function __construct()
    {
        date_default_timezone_set('UTC');
        $this->last_send_date = date('Y-m-d H:i:s');
    }

    public function check()
    {
        return true;
    }

    public function update()
    {
        sleep(30);

//        $push_data = $this->getPushData();
//        $delete_data = $this->getDeleteData();

//        $send_data = [
//            'push' => $push_data,
//            'delete' => $delete_data,
//            'auto_mode' =>[
//                'open_card' => $this->getAutoCallOrderId(),
//            ],
//        ];

        $send_data = [
            'push' => [],
            'delete' => [],
            'auto_mode' =>[
                'open_card' => [],
            ],
        ];

//        if (!empty($push_data)) $send_data['push'] = $push_data;
//
//        if (!empty($delete_data)) $send_data['delete'] = $delete_data;

        $this->last_send_date = date('Y-m-d H:i:s');

        return json_encode($send_data);
    }


    private function getPushData()
    {
        $callListSrv = new CallListService();

//        $callListSrv->checkPendingPlanLeads();
//        $callListSrv->checkPendingToDoLeads();

        $params = [
            'call_list_updated_at'=>$this->last_send_date,
        ];
        $result = $callListSrv->getCallList($params);
        return $result  ;
    }

    private function getDeleteData()
    {
        $callListLog = CallList::find()
            ->select('order_id')
            ->where(['>', 'updated_at', $this->last_send_date])
            ->andWhere(['lead_state' => [LeadStatus::STATE_PLAN, LeadStatus::STATE_TODO]])
            ->asArray()
            ->all();

        return $callListLog;
    }

    private function getAutoCallOrderId()
    {
        $orders = OperatorConf::find()
            ->select([
                'call_list.order_id'
            ])
            ->join('LEFT JOIN', 'call_list', 'operator_conf.operator_id=call_list.operator_id')
            ->andWhere(['operator_conf.operator_id' => Yii::$app->user->id])
            ->andWhere(['operator_conf.status' => OperatorSettingsSrv::STATUS_ONLINE])
            ->andWhere(['operator_conf.call_mode' => OperatorSettingsSrv::AUTO_MODE])
            ->orderBy(['call_list.updated_at' => SORT_DESC])
            ->asArray()
            ->all();

        if (count($orders) < 1) return null;
        else return $orders[0]['order_id'];
    }
}