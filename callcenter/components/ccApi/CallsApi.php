<?php
namespace callcenter\components\ccApi;

use callcenter\components\ccApi\requests\OrderHistory;
use callcenter\components\ccApi\requests\Siplist;
use callcenter\components\ccApi\requests\MakeCall;
use callcenter\components\ccApi\requests\OperatorHistory;
use callcenter\components\ccApi\requests\CallHistory;
use callcenter\components\ccApi\requests\RecordList;
use callcenter\components\ccApi\requests\OrderRecords;

use common\models\callcenter\LeadCalls;
use common\models\customer\Customer;
use common\models\order\Order;
use yii\base\Component;

use Yii;
/**
 * Class Calls
 * @package app\models\ccApi
 */
class CallsApi extends Component
{
    public $record_list;
    public $order_records;
    public $call_history;
    public $order_history;
    public $operatorHistory;

    public $params;
    public $info;

    public function makeCall(Order $order){
        /**
         * phone must be without symbol `+`
         */
        $call_id = null;
        $makeCallModel = new MakeCall();
        $customer = Customer::findOne($order->customer_id);

        $date =  new \DateTime();
        $response = $makeCallModel->doRequest([
            'sip' => Yii::$app->operator->sip,
            'phone' => Yii::$app->operator->channel . $customer->phone,
            'order_id' => $order->order_hash,
            'external_key' => $order->order_id . $date->getTimestamp(),
        ]);

        $responseData = json_decode($response, true);

        if($responseData['status'] == 'ok')
        {
            $call_id = $responseData['response']['call_id'];
            $this->saveCall($order->order_id, $call_id);

            return $responseData;
        }

        return false;
    }


    public function checkOperatorAsteriskStatus(){

    }


    public function getCallState($call_id = null){
        $model = new CallHistory();
        $records = json_decode($model->doRequest($call_id != null ? ['call_id' => 	$call_id] : null), true);
        if ($records['status_code'] == 200) $call = $records['response'];
        else $call = null;
        return $call;
    }

    public function getOperatorState(){
        $model = new OperatorHistory();
        $records = json_decode($model->doRequest(['sip' => 	Yii::$app->operator->sip]), true);
        if ($records['status_code'] == 200) $operator_state = $records['response'][0]['state'];
        else $operator_state = null;
        return $operator_state;
    }

    public function getOrderHistory($order_id){
        $model = new OrderHistory();
        $records = json_decode($model->doRequest(['order_id' => $order_id]), true);

        if ($records['status_code'] == 200) $orderHistory = $records['response'];
        else $orderHistory = null;

        return $orderHistory;
    }


    public function saveCall($order_id, $call_id)
    {
        $leadCallsModel = new LeadCalls();
        $leadCallsModel->order_id = $order_id;
        $leadCallsModel->operator_id = Yii::$app->operator->id;
        $leadCallsModel->datetime = date('Y-m-d H:i:s');
        $leadCallsModel->call_id = $call_id;

        if ($leadCallsModel->save()) return true;
        else return false;
    }

//
//
//    public function getRecordList()
//    {
//        $model = new RecordList();
//        $records = json_decode($model->doRequest($this->params), true);
//        $records_list = $records['response']['data'];
//        foreach($records_list as $key => $record)
//        {
//            $conversion = Conversion::findOne(['id' => $record['order_id']]);
//            if(!empty($conversion))
//            {
//                $offer_id = $conversion->offer_id;
//                $offer_name = Offer::findOne(['id' => $offer_id])->offer_name;
//                $records_list[$key]['offer_name'] = $offer_name;
//            }else{
//                continue;
//            }
//        }
//
//        $this->record_list = $records_list;
//        unset($records['response']['data']);
//        $this->info = $records['response'];
//        return $this->record_list;
//    }
//
//    /**
//     * @return mixed
//     */
    public function getOrderRecords()
    {
        $model = new OrderRecords();
        $records = json_decode($model->doRequest($this->params), true);
        if (isset($records['response'])){
            $this->order_records = $records['response'];
        } else {
            $this->order_records = null;
        }

        return $this->order_records;
    }
//
//    /**
//     * @return mixed
//     */
//    public function getCallHistory()
//    {
//        $model = new CallHistory();
//        $records = json_decode($model->doRequest($this->params), true);
//        $this->call_history = $records['response'][0]['states'];
//        unset($records['response'][0]['states']);
//        $this->info = $records['response'][0];
//        return $this->call_history;
//    }
//
//    /**
//     * @param $calls
//     * @return null
//     */
//    public function getOperatorsList($calls)
//    {
//        $model = new UserToCall(['calls' => $calls]);
//
//        return $model->user_list;
//    }
//
//    /**
//     * @return \yii\data\ActiveDataProvider
//     */
//    public function getUserToCall($params)
//    {
//        $modelSearch = new UserToCallSearch();
//        $this->user_to_call = $modelSearch->search($params);
//
//        return $this->user_to_call;
//    }
//
////    public function getOrderHistory(){
////        $model = new OrderHistory();
////        $records = json_decode($model->doRequest($this->params),true);
////        $this->order_history = $records['response'];
////        return $this->order_records;
////    }
}