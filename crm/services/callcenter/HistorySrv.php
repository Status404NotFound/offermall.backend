<?php

namespace crm\services\callcenter;

use Yii;
use common\models\callcenter\CallRecords;
use common\models\order\OrderStatus;
use common\models\order\OrderSku;
use common\components\ccApi\requests\OrderRecords;
use common\models\callcenter\LeadCalls;
use linslin\yii2\curl\Curl;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

class HistorySrv
{
    public $count;
    public $list;

    private static $_lead_calls_dependencies = [
        'user' => false,
        'order' => false,
        'order_data' => false,
        'customer' => false,
        'countries' => false,
        'offer' => false,
        'order_sku' => false,
    ];

    public function __construct($params)
    {
        $this->list = $this->getHistory($params);
    }

    public function getHistory($params)
    {
        $history_query = LeadCalls::find()
            ->select([
                'lead_calls.call_id',
                'lead_calls.datetime',
                'lead_calls.duration',
                'order.order_status as order_status_id',
                'offer.offer_name',
                'user.username as operator_name',
                'order.order_hash',
                'order.order_id',
                'order_data.owner_id',
                'countries.country_code as country_iso',
                'countries.id as country_id',
                'countries.country_name',
            ]);
        $this->joinUser($history_query);
        $this->joinOrder($history_query);
        $this->joinOrderData($history_query);
        $this->joinCustomer($history_query);
        $this->joinCountries($history_query);
        $this->joinOffer($history_query);

        // filter for history_query
        $this->filterQuery($history_query, $params['filters']);
        foreach (self::$_lead_calls_dependencies as &$join) {
            $join = false;
        }

        // filter for history_count_query
        $history_count_query = LeadCalls::find();
        $this->filterQuery($history_count_query, $params['filters']);
        foreach (self::$_lead_calls_dependencies as &$join) {
            $join = false;
        }

        // filter for history_basic_query
        $history_basic_query = LeadCalls::find()
            ->select([
                'lead_calls.id',
            ])
            ->orderBy(['lead_calls.id' => SORT_DESC])
            ->offset($params['firstRow'])
            ->limit($params['rows']);
        $this->filterQuery($history_basic_query, $params['filters']);

        $lead_calls_ids = [];
        foreach ($history_basic_query->all() as $lead_calls_id) {
            $lead_calls_ids[] = $lead_calls_id->id;
        }

        $history = $history_query
            ->andWhere(['lead_calls.id' => $lead_calls_ids])
            ->orderBy(['lead_calls.id' => SORT_DESC])
            ->asArray()
            ->all();

        foreach ($history as &$row) {
            $row['order_status'] = OrderStatus::attributeLabels($row['order_status_id']);
            $row['sku_count'] = 0;
            $row['orderSku'] = OrderSku::findListByOrderId($row['order_id']);

            $all_amount = 0;
            if (!empty($row['orderSku'])){
                foreach ($row['orderSku'] as &$item) {
                    $all_amount += $item['amount'];
                }
                $row['sku_count'] = $all_amount;
            }
        }

        $this->count = $history_count_query->count();
        return $history;
    }

    private function filterQuery($query, $filters): void
    {
        $owner_id = Yii::$app->user->identity->getOwnerId();

        if ($owner_id !== null) {
            $this->joinOrderData($query);
            $query->andWhere(['order_data.owner_id' => $owner_id]);
        }
        if (isset($filters['order_status'])) {
            $this->joinOrder($query);
            $query->andWhere(['order.order_status' => $filters['order_status']['value']]);
        }
        if (isset($filters['call_id'])) {
            $query->andWhere(['lead_calls.call_id' => $filters['call_id']['value']]);
        }
        if (isset($filters['operator'])) {
            $query->andWhere(['lead_calls.operator_id' => $filters['operator']['value']]);
        }
        if (isset($filters['offer'])) {
            $this->joinOrder($query);
            $this->joinOffer($query);
            $query->andWhere(['order.offer_id' => $filters['offer']['value']]);
        }
        if (isset($filters['order_id'])) {
            $this->joinOrder($query);
            $query->andWhere(['order.order_hash' => $filters['order_id']['value']]);
        }
        if (isset($filters['country'])) {
            $this->joinOrder($query);
            $this->joinCustomer($query);
            $this->joinCountries($query);
            $query->andWhere(['countries.id' => $filters['country']['value']]);
        }
        if (isset($filters['sku_id'])) {
            $this->joinOrder($query);
            $this->joinOrderSku($query);
            $query->andWhere(['order_sku.sku_id' => $filters['sku_id']['value']]);
        }
        if (isset($filters['duration'])) {
            if ($filters['duration']['value'] == 0) {
                $query->andWhere(['<', 'duration', 60]);
            } else {
                $query->andWhere(['>', 'duration', $filters['duration']['value']]);
            }
        }
        if (isset($filters['sku_count'])) {
            $query->addSelect([
                '(SELECT SUM(amount) FROM order_sku LEFT JOIN `order` O ON order_sku.order_id = O.order_id WHERE order_sku.order_id = O.order_id AND O.order_id = lead_calls.order_id) as sku_count'
            ]);
            $query->andHaving(['=', 'sku_count', $filters['sku_count']['value']]);
        }
        if (isset($filters['date'])) {
            $start = new \DateTime($filters['date']['start']);
            $start->setTime(0, 0, 0);
            $start_date = $start->format('Y-m-d H:i:s');

            $end = new \DateTime($filters['date']['end']);
            $end->setTime(23, 59, 59);
            $end_date = $end->format('Y-m-d H:i:s');

            $query->andWhere(['>', 'datetime', $start_date]);
            $query->andWhere(['<', 'datetime', $end_date]);
        }
    }

    private function joinUser(ActiveQuery $query): void
    {
        if (!self::$_lead_calls_dependencies['user']) {
            $query->leftJoin('user', 'user.id = lead_calls.operator_id');
            self::$_lead_calls_dependencies['user'] = true;
        }
    }

    private function joinOrder(ActiveQuery $query): void
    {
        if (!self::$_lead_calls_dependencies['order']) {
            $query->leftJoin('order', 'order.order_id = lead_calls.order_id');
            self::$_lead_calls_dependencies['order'] = true;
        }
    }

    private function joinOrderData(ActiveQuery $query): void
    {
        if (!self::$_lead_calls_dependencies['order_data']) {
            $query->leftJoin('order_data', 'order_data.order_id = lead_calls.order_id');
            self::$_lead_calls_dependencies['order_data'] = true;
        }
    }

    private function joinCustomer(ActiveQuery $query): void
    {
        if (!self::$_lead_calls_dependencies['customer']) {
            $query->leftJoin('customer', 'customer.customer_id = order.customer_id');
            self::$_lead_calls_dependencies['customer'] = true;
        }
    }

    private function joinCountries(ActiveQuery $query): void
    {
        if (!self::$_lead_calls_dependencies['countries']) {
            $query->leftJoin('countries', 'customer.country_id = countries.id');
            self::$_lead_calls_dependencies['countries'] = true;
        }
    }

    private function joinOffer(ActiveQuery $query): void
    {
        if (!self::$_lead_calls_dependencies['offer']) {
            $query->leftJoin('offer', 'offer.offer_id = order.offer_id');
            self::$_lead_calls_dependencies['offer'] = true;
        }
    }

    private function joinOrderSku(ActiveQuery $query): void
    {
        if (!self::$_lead_calls_dependencies['order_sku']) {
            $query->leftJoin('order_sku', 'order_sku.order_id = `order`.order_id');
            self::$_lead_calls_dependencies['order_sku'] = true;
        }
    }

    public function refreshDuration($operator_id = null, $limit = 100)
    {
        $query = LeadCalls::find()
            ->select([
                'order_hash',
                'call_id',
            ])
            ->join('LEFT JOIN', 'order', 'order.order_id=lead_calls.order_id')
            ->where(['duration' => null])
            ->andWhere(['>', 'datetime', '2018-03-01 00:00:00'])
            ->andWhere(['<', 'datetime', '2018-04-01 00:00:00']);
//            ->andWhere(['call_checked' => false]);

        if (!is_null($operator_id)) $query->andWhere(['lead_calls.operator_id' => $operator_id]);

        $query->orderBy(['id' => SORT_DESC])
            ->limit($limit);


        $calls = $query->asArray()
            ->all();

//        var_dump($calls);exit;

        foreach ($calls as $call) {
            $oid = $call['order_hash'];
            $cid = $call['call_id'];

            $orderRecords = $this->getOrderRecords($oid);
            if (!is_null($orderRecords)) {
                $search = ArrayHelper::map($orderRecords, 'call_id', 'uniqueid');

                if (isset($search[$cid])) {
                    $curl = new Curl();

                    $filePath = null;
                    foreach ($orderRecords as $record) {
                        $filePath = str_replace('/data/', ' ', Yii::$app->params['callCenterRecords'] . $record['path']);

//                        $head = array_change_key_case(get_headers($srv . $path . $filename, TRUE)); $size = $head['content-length'];

                        $mp3 = $curl->get($filePath);
                        $tmpDirName = 'tmp-call';
                        $tmpDir = Yii::getAlias('@webroot') . '/' . $tmpDirName;

                        if (($isExist = !file_exists($tmpDir))) {
                            mkdir($tmpDir, 755, true);
                        }

                        file_put_contents($tmpDir . '/tmp.mp3', $mp3);
                        $mp3 = new MP3($tmpDirName . '/tmp.mp3');

                        $duration = $mp3->getDurationEstimate();

                        unlink($tmpDir . '/tmp.mp3');
                    }
                } else {
                    $duration = 0;
                }

//                if (isset($search[$cid])) {
//                    $uniqueid = $search[$cid];
//                    $file = $uniqueid . '.mp3';
//
//                    $filePath = str_replace('/data/', '/mp3/', Yii::$app->params['callCenterApi']) . $file;
//
//                    $curl = new Curl();
//                    $mp3 = $curl->get($filePath);
//                    $tmpDirName = 'tmp-call';
//                    $tmpDir = Yii::getAlias('@webroot') . '/' . $tmpDirName;
//                    if (($isExist = !file_exists($tmpDir))) {
//                        mkdir($tmpDir, 755, true);
//                    }
//                    file_put_contents($tmpDir . '/tmp.mp3', $mp3);
//                    $mp3 = new MP3($tmpDirName . '/tmp.mp3');
//
//                    $duration = $mp3->getDurationEstimate();
//                    var_dump($duration);
//
//                    unlink($tmpDir . '/tmp.mp3');
//                } else {
//                    $duration = 0;
//                }

                $modelUserToCall = LeadCalls::findOne(['call_id' => $cid]);
                if ($modelUserToCall && $duration != 0) {
                    //var_dump($duration);
                    $modelUserToCall->duration = $duration;
                }

                $modelUserToCall->call_checked = true;
                $modelUserToCall->save();
            }


//            try{
//                $orderRecords = $this->getOrderRecords($oid);
//                $search = ArrayHelper::map($orderRecords, 'call_id', 'uniqueid');
//
//                if (isset($search[$cid])) {
////                var_dump($cid);
//                    $uniqueid = $search[$cid];
//                    $file = $uniqueid . '.mp3';
//
//                    $filePath = str_replace('/api/', '/mp3/', Yii::$app->params['callCenterApi']) . $file;
//
//                    $curl = new Curl();
//                    $mp3 = $curl->get($filePath);
//                    $tmpDirName = 'tmp-call';
//                    $tmpDir = Yii::getAlias('@webroot') . '/' . $tmpDirName;
//                    if (($isExist = !file_exists($tmpDir))) {
//                        mkdir($tmpDir, 755, true);
//                    }
//                    file_put_contents($tmpDir . '/tmp.mp3', $mp3);
//                    $mp3 = new MP3($tmpDirName . '/tmp.mp3');
//
//                    $duration = $mp3->getDurationEstimate();
//                    var_dump($duration);
//
//                    unlink($tmpDir . '/tmp.mp3');
//                } else {
//                    $duration = 0;
//                }
//
//                $modelUserToCall = LeadCalls::findOne(['call_id' => $cid]);
//                if ($modelUserToCall && $duration != 0) {
//                    //var_dump($duration);
//                    $modelUserToCall->duration = $duration;
//                }
//
//                $modelUserToCall->call_checked = true;
//                $modelUserToCall->save();
//            }catch (Exception $e){
//                var_dump($e);
//            }

        }

        return 0;
    }

    /**
     * @param string $order_id
     * @return array|CallRecords[]|LeadCalls[]|\common\models\callcenter\OperatorConf[]|\common\models\Language[]|\yii\db\ActiveRecord[]
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

    public static function findListByOrderId($order_id)
    {
        $query = OrderSku::find()
            ->select([
                'COUNT(*) as total',
                'OS.sku_id',
                'OS.amount',
                'PS.sku_name'
            ])
            ->from('order_sku OS')
            ->join('LEFT JOIN', 'product_sku PS', 'PS.sku_id = OS.sku_id')
            ->where(['OS.order_id' => $order_id])
            ->asArray()
            ->all();

        return $query;
    }

//    public function getOrderRecords($order_id)
//    {
//        $model = new OrderRecords();
//        $records = json_decode($model->doRequest(['order_id' => $order_id]), true);
//        if (isset($records['response'])) {
//            $order_records = $records['response'];
//        } else {
//            $order_records = null;
//        }
//
//        return $order_records;
//    }

//    public function getOrderRecords($order_id)
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
}