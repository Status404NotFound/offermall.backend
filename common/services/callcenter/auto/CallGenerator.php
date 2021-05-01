<?php
namespace common\services\callcenter;


use common\models\callcenter\OperatorConf;
use common\services\webmaster\ArrayHelper;

class CallGenerator
{
    private function operators():array
    {
        $query = OperatorConf::find()
            ->select([
                'operator_setting.operator_id',
                'user_child.parent as owner_id'
            ])
            ->join('LEFT JOIN', 'user_child', 'operator_setting.operator_id=user_child.child')
            ->andWhere(['operator_setting.status' => OperatorSettingsSrv::STATUS_ONLINE])
            ->andWhere(['operator_setting.call_mode' => OperatorSettingsSrv::AUTO_MODE]);
        $result = $query
            ->groupBy('operator_id')
            ->asArray()
            ->all();

        return $result;
    }

    private function queues(array $operators):array
    {
        $queues = [];
        foreach ($operators as $operator)
        {
            $operator_queues = (new OperatorSettingsSrv())->getOperatorQueues($operator['operator_id']);
            foreach ($operator_queues as $queue)
            {
                if (isset($queues[$queue])) $queues[$queue] += 1;
                else $queues[$queue] = 1;
            }
        }

        asort($queues);

        return $queues;
    }

    public function call()
    {
        $queue_weight = 0.7;
        $operators = $this->operators();
        $queues = $this->queues($operators);

        $order_calls = [];
        do{
            foreach ($queues as $queue)
            {

            }
        }while(!$this->checkSum($operators, $order_calls, $queue_weight));
    }

    private function checkSum($operators, $orders_call, $queue_weight)
    {
        $result = false;
        if (array_sum($orders_call) * $queue_weight < count($operators)) $result = true;
        return $result;
    }
}