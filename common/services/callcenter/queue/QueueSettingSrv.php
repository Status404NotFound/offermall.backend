<?php
namespace common\services\callcenter\queue;

use common\models\callcenter\AsteriskQueue;
use common\models\callcenter\CallQueue;
use yii\base\Exception;

class QueueSettingSrv
{
    public function save($data, $id = null)
    {
        if (!$callQueue = CallQueue::findOne($id))
        {
            $asteriskQueue = $this->createAsteriskQueue();
            $callQueue = new CallQueue();
            $callQueue->queue_asterisk_code = $asteriskQueue->name;
        }

        if (isset($data['queue_name'])) $callQueue->queue_name = $data['queue_name'];
        if (isset($data['offer'])) $callQueue->offer = json_encode($data['offer']);
        if (isset($data['geo'])) $callQueue->geo = json_encode($data['geo']);
        if (isset($data['language'])) $callQueue->language= json_encode($data['language']);
        if (isset($data['lead_status'])) $callQueue->lead_status = json_encode($data['lead_status']);
        if (isset($data['attempts_min']) && isset($data['attempts_max'])){
            $attempts = [
                'min' => $data['attempts_min'],
                'max' => $data['attempts_max'],
            ];

            $callQueue->attempts = json_encode($attempts);
        }
        return $callQueue->save();
    }

    private function createAsteriskQueue():AsteriskQueue
    {
        $asteriskQueue = new AsteriskQueue();
        $asteriskQueue->name = $this->generateAsteriskQueueName();
        $asteriskQueue->wrapuptime = 10;
        $asteriskQueue->joinempty = true;
        $asteriskQueue->strategy = 'random';
        $asteriskQueue->musiconhold = '1.avi';
        if ($asteriskQueue->save()) return $asteriskQueue;
        throw new Exception('Asterisk Queue was not created' . json_encode($asteriskQueue->errors));
    }

    private function generateAsteriskQueueName()
    {
        $lastName = AsteriskQueue::find()->orderBy(['name' => SORT_ASC])->one();
        if ($lastName) return strval(intval($lastName->name)-1);
        return '990';
    }


    public function get($id = null)
    {
        $query = CallQueue::find();
        if (!is_null($id)) $query->where(['queue_id' => $id]);
        $rows = $query->asArray()->all();
        $result = [];
        foreach ($rows as $row)
        {
            $result[] = $this->handleDataGet($row);
        }

        if (!is_null($id)) return $result[0];
        return $result;
    }

    private function handleDataGet($data)
    {
        foreach ($data as $key => $row)
        {
            $value = json_decode($row, true);
            if (!is_null($value)){
                $data[$key] = $value;
                if ($key == 'attempts')
                {
                    $data['attempts_min'] = $value['min'];
                    $data['attempts_max'] = $value['max'];
                }
            }
        }

        return $data;
    }

    public function operatorSetting($operator_id)
    {

    }
}