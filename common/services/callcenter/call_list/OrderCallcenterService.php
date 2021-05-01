<?php
/**
 * Created by PhpStorm.
 * User: ihor
 * Date: 23.06.17
 * Time: 12:39
 */

namespace common\services\callcenter\call_list;

use Yii;
use common\models\callcenter\CallRecords;
use common\models\callcenter\LeadCalls;
use common\models\customer\Customer;
use common\models\Instrument;
use common\models\callcenter\CallList;
use common\models\log\CallListLog;
use common\models\log\CustomerLog;
use common\models\log\OrderLog;
use common\models\order\Order;
use common\models\order\OrderStatus;
use common\services\order\OrderCommonService;
use common\services\order\OrderNotFoundException;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use linslin\yii2\curl\Curl;

/**
 * Class OrderCallcenterService
 * @package common\services\callcenter\call_list
 */
class OrderCallcenterService extends OrderCommonService
{
    /**
     * @param $params
     * @param null $operator_id
     * @return bool|Exception
     * @throws Exception
     * @throws OrderNotFoundException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function planCall($params, $operator_id = null)
    {
        if ($operator_id === null && Yii::$app->operator->is_config_operator) $operator_id = Yii::$app->operator->id;
        else return new Exception('This user is not operator or has not any configuration!!! Please check user role and configuration!');

        if (isset($params['order_id'])) $order_id = $params['order_id'];
        else throw new Exception('No order id!');

        if (isset($params['date'])) {

            $order = CallList::findOne($order_id);
            $order->operator_id = $operator_id;
            $order->lead_state = LeadStatus::STATE_PLAN;
            $order->time_to_call = date('Y-m-d H:i:s', strtotime($params['date']));
//            $order->time_to_call = $params['date'];

//            $log = new LogSrv(clone $order, Instrument::CALL_CENTER_PLAN_CALL, 'plan call on ' . $order->time_to_call);

            if ($order->update(true, ['operator_id', 'lead_state', 'time_to_call'])) {
                if (!$order = Order::findOne(['order_id' => $order_id])) throw new OrderNotFoundException('OrderNotFound');
                $order->instrument = Instrument::CALL_CENTER_TAKE_LEAD;
                $comment = "<span style='color: #ffa080;'> Plan call on time " . date('Y-m-d H:i:s', strtotime($params['date'])) . "</span>";

                try {
                    (new OrderCommonService())->saveComment($order, $comment);
                } catch (\Exception $e) {

                }
//                $log->add();
                return true;
            }
        }
        return false;
    }

    /**
     * @param $order_id
     * @return array|bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function detachLead($order_id)
    {
        $order = CallList::findOne($order_id);

//        $date = new \DateTime();
//        $date->modify('+20 minutes');

        $order->operator_id = null;
        $order->lead_state = LeadStatus::STATE_PENDING;
        $order->time_to_call = null;

        if (!$order->update(true, ['operator_id', 'lead_state', 'time_to_call'])) return $order->errors;

        return true;
    }

    /**
     * @param $order_id
     * @param $delivery_date
     * @param $customer_address
     * @param $declaration
     * @return bool
     * @throws Exception
     */
    public function planDelivery($order_id, $delivery_date, $customer_address, $declaration)
    {
        $params = [
            'delivery_date' => $delivery_date,
        ];
        if (isset($customer_address)) $params['address'] = $customer_address;

        $order = Order::findOne($order_id);
//        $order_call = CallList::findOne($order_id);

        try {
            $this->changeStatus($order, OrderStatus::WAITING_DELIVERY, $params);
            if (!$order = Order::findOne(['order_id' => $order_id])) throw new OrderNotFoundException('OrderNotFound');
            $order->instrument = Instrument::CALL_CENTER_TAKE_LEAD;
            $comment = "<span style='color: #bb0087;'> Plan delivery on time " . $delivery_date . "</span>";

            try {
                (new OrderCommonService())->saveComment($order, $comment);
            } catch (\Exception $e) {

            }
        } catch (Exception $e) {
            throw $e;
        }

//        $order_call->delete();

        return true;
    }


    /**
     * @param $params
     * @return bool
     * @throws Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function changeOrderAddress($params)
    {
        if (isset($params['customer_id'])) $customer_id = $params['customer_id'];
        else throw new Exception('No customer id!');

        if (isset($params['address'])) $order_address = !empty($params['address']) ? $params['address'] : null;
        else throw new Exception('No address!');

        if (!empty($order_address)) {

            $customer = \common\models\customer\Customer::findOne($customer_id);
            $customer->address = $order_address;

            if (!$customer->update()) var_dump($customer->errors);
            exit;
        }

        return true;
    }

    /**
     * @param $params
     * @param null $operator_id
     * @return Order|null|Exception
     * @throws Exception
     */
    public function RejectOrder($params, $operator_id = null)
    {
        if ($operator_id === null && Yii::$app->operator->is_config_operator) $operator_id = Yii::$app->operator->id;
        else return new Exception('This user is not operator or has not any configuration!!! Please check user role and configuration!');

        if (isset($params['order_id'])) $order_id = $params['order_id'];
        else throw new Exception('No order id!');

        if (isset($params['message'])) $message = !empty($params['message']) ? $params['message'] : null;
        else throw new Exception('No message added!');

        $order = Order::findOne($order_id);

        try {
            $order->setInstrument(Instrument::CALL_CENTER_CARD_REJECT);
            $this->changeStatus($order, OrderStatus::REJECTED, ['reason_id' => $message]);
        } catch (Exception $e) {
            throw $e;
        }

        return $order;
    }

    /**
     * @param $order_id
     * @param $language_id
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function changeOrderLanguage($order_id, $language_id)
    {
        $call = CallList::findOne($order_id);
        if ($call) {
            $call->language_id = $language_id;
            $call->lead_status = LeadStatus::STATUS_HIGH_PRIORITY;
            $call->lead_state = LeadStatus::STATE_FREE;
            $call->operator_id = null;

            if ($call->update(['language_id', 'lead_status', 'operator_id', 'lead_state'])) return true;
        }

        return false;
    }

    /**
     * @param $order_id
     * @return array
     */
    public function getComments($order_id)
    {
        $order = Order::findOne($order_id);
        $instruments = [
            Instrument::CALL_CENTER,
            Instrument::CALL_CENTER_COMMENT,
            Instrument::CALL_CENTER_TAKE_LEAD,
            Instrument::CALL_CENTER_PLAN_CALL,
            Instrument::CALL_CENTER_MAKE_CALL,
            Instrument::CALL_CENTER_SET_SKU,
            Instrument::CALL_CENTER_SET_EMIRATE,
        ];

        $call_list_logs = CallListLog::find()
            ->select('call_list_log.*, user.username')
            ->join('LEFT JOIN', 'user', 'user.id = call_list_log.user_id')
            ->where([
                'row_id' => $order_id,
                'instrument' => $instruments,
            ])
//            ->groupBy('id')
            ->asArray()
            ->all();

        $order_logs = OrderLog::find()
            ->select('order_log.*, user.username')
            ->join('LEFT JOIN', 'user', 'user.id = order_log.user_id')
            ->where([
                'row_id' => $order_id,
                'instrument' => $instruments,
            ])
            ->asArray()
            ->all();

        $customer_log = CustomerLog::find()->where(['row_id' => $order->customer_id, 'instrument' => $instruments])->asArray()->all();

        $logs = array_merge($call_list_logs, $order_logs, $customer_log);
        usort($logs, function ($a, $b) {
            return ($a['datetime'] < $b['datetime']) ? -1 : 1;
        });

        $comments = [];
        foreach ($logs as $log) {
            $comments[] =
                [
                    'comment' => $log['datetime'] . ": User " . ucfirst($log['username']) . Instrument::getAction($log['instrument']) . $log['comment'],
                    'color' => isset(Instrument::instrumentCommentColor()[$log['instrument']]) ? Instrument::instrumentCommentColor()[$log['instrument']] : '#000000',
                ];
        }

        return $comments;
    }

    /**
     * @param $order_id
     * @return \yii\db\ActiveQuery
     */
    public function getOrderSku($order_id)
    {
        $order = Order::findOne(['order_id' => $order_id]);
        $sku = $order->getOrderSku();

        return $sku;
    }

    /**
     * @param $params
     * @return array|CallRecords[]|LeadCalls[]|\common\models\callcenter\OperatorConf[]|Customer[]|\common\models\Language[]|CallListLog[]|CustomerLog[]|OrderLog[]|\yii\db\ActiveRecord[]
     */
    public function getCustomerHistory($params)
    {
        $query = Customer::find()
            ->select([
                'order.order_id',
                'order.order_hash',
                'order.order_status as order_status_id',
                'order_data.offer_id',
                'order.created_at',
                'offer.offer_name',
                'customer.phone',
            ])
            ->join('LEFT JOIN', 'order', 'order.customer_id = customer.customer_id')
            ->join('LEFT JOIN', 'order_data', 'order.order_id = order_data.order_id')
            ->join('LEFT JOIN', 'offer', 'order.offer_id = offer.offer_id')
            ->where(['deleted' => 0])
            ->andWhere(['owner_id' => \Yii::$app->user->identity->getOwnerId()]);


        if (isset($params['phone'])) {
            $query->andWhere(['like', 'customer.phone', str_replace(array('+', '-', ' '), '', $params['phone'])]);
        }

        $result = $query->asArray()->all();

        foreach ($result as $key => $order) {
            $result[$key]['order_status'] = OrderStatus::attributeLabels($order['order_status_id']);
        }

        return $result;
    }

    /**
     * @param null $operator_id
     * @param int $limit
     * @return int
     */
    public function refreshDuration($operator_id = null, $limit = 100)
    {
        $query = LeadCalls::find()
            ->select([
                'order_hash',
                'call_id',
            ])
            ->join('LEFT JOIN', 'order', 'order.order_id=lead_calls.order_id')
            ->where(['duration' => null])
            ->andWhere(['order.deleted' => 0]);

        if (!is_null($operator_id)) $query->andWhere(['lead_calls.operator_id' => $operator_id]);

        $query->orderBy(['id' => SORT_DESC])
            ->limit($limit);

        $calls = $query
            ->asArray()
            ->all();

        foreach ($calls as $call) {
            $oid = $call['order_hash'];
            $cid = $call['call_id'];

            $orderRecords = $this->getOrderRecords($oid);
            $search = ArrayHelper::map($orderRecords, 'call_id', 'uniqueid');

            if (isset($search[$cid])) {

                $filePath = null;

                // TODO check working
                foreach ($orderRecords as $record) {

                    $mp3 = $record['path'];
                    $mp3 = new MP3($mp3);

                    $duration = $mp3->getDurationEstimate();

                }
            } else {
                $duration = 0;
            }

            $modelUserToCall = LeadCalls::findOne(['call_id' => $cid]);
            if ($modelUserToCall && $duration != 0) {
                var_dump($duration);
                $modelUserToCall->duration = $duration;
            }
            $modelUserToCall->call_checked = true;
            $modelUserToCall->save();
        }

        return 0;
    }

//    public function refreshDuration($operator_id = null, $limit = 100){
//
//        $query = LeadCalls::find()
//            ->select([
//                'order_hash',
//                'call_id',
//            ])
//            ->join('LEFT JOIN', 'order', 'order.order_id=lead_calls.order_id')
//            ->where(['duration' => null])
//            ->andWhere(['order.deleted' => 0]);
//
//        if (!is_null($operator_id)) $query->andWhere(['lead_calls.operator_id' => $operator_id]);
//
//        $query->orderBy(['id' => SORT_DESC])
//            ->limit($limit);
//
//
//        $calls = $query->asArray()
//            ->all();
//
//        foreach ($calls as $call) {
//            $oid = $call['order_hash'];
//            $cid = $call['call_id'];
//
//
//            $orderRecords = $this->getOrderRecords($oid);
//            $search = ArrayHelper::map($orderRecords, 'call_id', 'uniqueid');
////            if (!is_null($orderRecords)){
////
//////                $search = ArrayHelper::map($orderRecords, 'call_id', 'uniqueid');
////            }
////            else $search = [];
//
//            if (isset($search[$cid])) {
//                //var_dump($cid);
//                $uniqueid = $search[$cid];
//                $file = $uniqueid . '.mp3';
//
//                $filePath = str_replace('/api/', '/mp3/', Yii::$app->params['callCenterApi']) . $file;
//
//                $curl = new Curl();
//                $mp3 = $curl->get($filePath);
//                $tmpDirName = 'tmp-call';
//                $tmpDir = Yii::getAlias('@webroot') . '/' . $tmpDirName;
//                if (($isExist = !file_exists($tmpDir))) {
//                    mkdir($tmpDir, 755, true);
//                }
//                file_put_contents($tmpDir . '/tmp.mp3', $mp3);
//                $mp3 = new MP3($tmpDirName . '/tmp.mp3');
//
//                $duration = $mp3->getDurationEstimate();
//
//                unlink($tmpDir . '/tmp.mp3');
//            } else {
//                $duration = 0;
//            }
//
//            $modelUserToCall = LeadCalls::findOne(['call_id' => $cid]);
//            if ($modelUserToCall && $duration != 0) {
//                var_dump($duration);
//                $modelUserToCall->duration = $duration;
//            }
//            $modelUserToCall->call_checked = true;
//            $modelUserToCall->save();
//        }
//
//        return 0;
//    }

    /**
     * @param string $order_id
     * @return array|CallRecords[]|LeadCalls[]|\common\models\callcenter\OperatorConf[]|Customer[]|\common\models\Language[]|CallListLog[]|CustomerLog[]|OrderLog[]|\yii\db\ActiveRecord[]
     */
    private function getOrderRecords(string $order_id)
    {
        $order_records = [];
        $records = CallRecords::find()
            ->where(['order_id' => $order_id])
            ->asArray()
            ->all();

        if (!empty($records)) {
            $order_records = $records;
        }

        return $order_records;
    }

//    private function getOrderRecords($order_id)
//    {
//        $model = new OrderRecords();
//        $records = json_decode($model->doRequest( ['order_id' => $order_id]), true);
//        if (isset($records['response'])){
//            $order_records = $records['response'];
//        } else {
//            $order_records = null;
//        }
//
//        return $order_records;
//    }

    /**
     * @param $order
     */
    public function addCall($order)
    {

    }
}