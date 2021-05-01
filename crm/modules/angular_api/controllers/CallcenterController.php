<?php
/**
 * Created by PhpStorm.
 * User: ihor
 * Date: 21.08.17
 * Time: 16:00
 */

namespace crm\modules\angular_api\controllers;

use callcenter\services\operator_activity\OperatorActivityService;
use common\components\ccApi\CallsApi;
use common\models\callcenter\CallList;
use common\models\callcenter\CallRecords;
use common\models\callcenter\OperatorConf;
use common\models\callcenter\OperatorFine;
use common\models\DataList;
use common\models\Instrument;
use common\models\order\Order;
use common\modules\user\models\tables\User;
use common\services\callcenter\call_list\CallListService;
use common\services\callcenter\call_list\LeadStatus;
use common\services\callcenter\call_list\OrderCallcenterService;
use common\services\callcenter\OperatorSettingsSrv;
use common\services\callcenter\queue\QueueSettingSrv;
use common\services\ListService;
use common\services\log\LogSrv;
use crm\services\callcenter\CallListCrmSrv;
use crm\services\callcenter\FineSrv;
use crm\services\callcenter\HistorySrv;
use common\services\callcenter\OfferNotesSrv;
use crm\services\callcenter\OperatorActivitySrv;
use crm\services\callcenter\PcsSrv;
use crm\services\callcenter\StatisticsSrv;
use crm\services\callcenter\StatisticsSrv2;
use crm\services\statistic\OperatorRating;
use crm\services\statistic\RejectStatisticsService;
use linslin\yii2\curl\Curl;
use phpDocumentor\Reflection\Types\Null_;
use phpDocumentor\Reflection\Types\Nullable;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

class CallcenterController extends BehaviorController
{
    public $modelClass = 'common\models\callcenter\OperatorConf';

    public function actionChange()
    {
        $request = Yii::$app->request->get();
        $operatorStnSrv = new OperatorSettingsSrv();
        $this->response->data = $operatorStnSrv->getSetting($request['operator_id']);
        $this->response->send();
    }

    public function actionSave()
    {
        $request = Yii::$app->request->post();
        $operatorConf = OperatorConf::find()->where(['operator_id' => $request['operator_id']])->one();
//        $operatorConf->call_mode = $request['call_mode'];
//        $operatorConf->sip = $request['sip'];
//        $operatorConf->channel = $request['channel'];
        $operatorConf->setAttributes($request);
        $operatorConf->save();
//        if (!) var_dump($operatorConf->errors);exit;
        $operStnSrv = new OperatorSettingsSrv();
        $operStnSrv->saveOperatorLanguages($request['operator_id'], $request['languages']);
        $operStnSrv->saveOperatorOffers($request['operator_id'], $request['offers']);
        $operStnSrv->saveOperatorQueues($request['operator_id'], $request['queues']);

        $this->response->data = $operatorConf->errors;
        $this->response->send();
    }

    public function actionGetOffers()
    {
        $userService = new ListService();
        $this->response->data = $userService->offers;
        $this->response->send();
    }

    public function actionGetLanguages()
    {
        $this->response->data = OperatorSettingsSrv::getLanguages();
        $this->response->send();
    }

    public function actionSettings()
    {
        $request = Yii::$app->request->getBodyParams();
        $operatorSettingsSrv = new OperatorSettingsSrv();
        $this->response->data = $operatorSettingsSrv->getSettings($request);
        $this->response->send();
    }

    public function actionDelete()                                      // TODO DELETE
    {
        $request = Yii::$app->request->getBodyParams();
        $statisticsSrv = new StatisticsSrv($request);
        $this->response->data = [
            'list' => $statisticsSrv->list,
            'total' => $statisticsSrv->total,
        ];
        $this->response->headers->add('Access-Control-Expose-Headers', 'X-Pagination-Total-Count');
        $this->response->headers->add('X-Pagination-Total-Count', $statisticsSrv->count);
        $this->response->send();
    }

    public function actionCallList()
    {
        $request = Yii::$app->request->post();
        $callListSrv = new CallListCrmSrv();
        $call_list = $callListSrv->generalCallList($request);
        $this->response->data = $call_list['call_list'];
        $this->setPaginationHeaders($call_list['count']);
        $this->response->send();
    }

    public function actionCallListSettings()
    {
        $request = Yii::$app->request->post();
        $callListSrv = new CallListCrmSrv();
        $this->response->data = $callListSrv->setGroupSettings($request['orders'], $request['settings']);
        $this->response->send();
    }

    public function actionCallListHighPriorityChange()
    {
        $request = Yii::$app->request->post();
        $leadStatus = new LeadStatus();
        $action_status = false;

        if ($request['high_priority'] == true) {
            $order = Order::find()->where(['order_hash' => $request['order_id']])->one();
            $lead = CallList::findOne($order->order_id);
            $lead->lead_status = LeadStatus::STATUS_HIGH_PRIORITY;
            $lead->lead_state = LeadStatus::STATE_FREE;
            $lead->operator_id = null;

            $log = new LogSrv($lead, Instrument::LMC_CALL_CENTER);
            if ($lead->update()) {
                $action_status = true;
                $log->add();
            }

//            if ($leadStatus->change($request['order_id'], LeadStatus::STATUS_HIGH_PRIORITY)) $action_status = true;
        } else {
            $order = Order::find()->where(['order_hash' => $request['order_id']])->one();
            $lead = CallList::findOne($order->order_id);
            $lead->lead_status = LeadStatus::STATUS_NEW;
            $lead->lead_state = LeadStatus::STATE_FREE;
            $lead->operator_id = null;

            $log = new LogSrv($lead, Instrument::LMC_CALL_CENTER);
            if ($lead->update()) {
                $action_status = true;
                $log->add();
            }
        }
        $this->response->data = $action_status;
        $this->response->send();
    }

    public function actionFines()
    {
        $fineSrv = new FineSrv();
        $this->response->data = $fineSrv->fines();
        $this->response->send();
    }

    public function actionFineStatus()
    {
        $request = Yii::$app->request->post();
        $fineSrv = new FineSrv();
        $this->response->data = $fineSrv->changeFineStatus($request['operator_fine_id'], $request['status_id']);
        $this->response->send();
    }

    public function actionPieces()
    {
        $request = Yii::$app->request->post();
        $pcsSrv = new PcsSrv($request);
        $this->response->data = [
            'pcs' => $pcsSrv->pcs,
            'pcs_total' => $pcsSrv->pcs_total,
        ];
        $this->setPaginationHeaders($pcsSrv->count);
        $this->response->send();
    }

    public function actionHistory()
    {
        $request = Yii::$app->request->post();
        $historySrv = new HistorySrv($request);
//        (new OrderCallcenterService())->refreshDuration(null, 1000);
//        $historySrv->refreshDuration(null, 10000);
        $this->response->data = $historySrv->list;
        $this->setPaginationHeaders($historySrv->count);
        $this->response->send();
    }

    public function actionStatistics()
    {
        $statisticsSrv = new StatisticsSrv($this->filters);
        $this->response->data = [
            'list' => $statisticsSrv->_list,
            'total' => $statisticsSrv->_total,
        ];
        $this->response->headers->add('Access-Control-Expose-Headers', 'X-Pagination-Total-Count');
        $this->response->headers->add('X-Pagination-Total-Count', $statisticsSrv->_count);
        $this->response->send();
    }

    public function actionActivity()
    {
        $request = Yii::$app->request->post();
        $statisticsSrv = new OperatorActivitySrv($request);
        $this->response->data = $statisticsSrv->list;
        $this->response->send();
    }

    public function actionActivityApprove()
    {
        $activity_id = Yii::$app->request->get('activity_id');
        $activitySrv = new OperatorActivitySrv([]);
        $this->response->data = $activitySrv->changeApprove($activity_id, true);
        $this->response->send();
    }

    public function actionActivityReject()
    {
        $activity_id = Yii::$app->request->get('activity_id');
        $activitySrv = new OperatorActivitySrv([]);
        $this->response->data = $activitySrv->changeApprove($activity_id, false);
        $this->response->send();
    }

    public function actionOperators()
    {
        $dataList = new DataList();
        $this->response->data = $dataList->getUsers(User::ROLE_OPERATOR);
        $this->response->send();
    }

    public function actionLeadStatus()
    {
        $this->response->data = LeadStatus::getIndexedStatuses();
        $this->response->send();
    }

    public function actionLeadState()
    {
        $this->response->data = LeadStatus::getIndexedStates();
        $this->response->send();
    }

    public function actionCallRecord()
    {
        $request = Yii::$app->request->post();
        $record = null;
        $call_records = CallRecords::getCallRecord($request['order_id'], $request['call_id']);
        if (!empty($call_records)) {
            $record = str_replace('/data/', '/', Yii::$app->params['callCenterRecords'] . $call_records);
        }
        $this->response->data = $record;
        $this->response->send();
    }

    public function actionRecordSave()
    {
        $request = Yii::$app->request->post();
        $filePath = null;

        $call_records = CallRecords::getCallRecord($request['order_id'], $request['call_id']);

        if (!empty($call_records)) {
            $filePath = $call_records;
        }

        $this->response->headers->add('Content-Disposition', 'filename="music.mp3"');
        $this->response->headers->add('Content-Type', 'audio/x-mpeg-3');
        $this->response->sendFile($filePath);
        $this->response->send();
    }

    public function actionOrder()
    {
        $request = Yii::$app->request->get();

//        $callListSrv = new CallListService();
//        $this->response->data = $callListSrv->getOrder($request['order_id']);

        $callListSrv = new CallListCrmSrv();
        $this->response->data = $callListSrv->orderCard($request['order_id']);
        $this->response->send();
    }

    public function actionPending()
    {
        $statisticsSrv = new StatisticsSrv($this->filters);
        $this->response->data = [
            'list' => $statisticsSrv->_pending_processing,
            //'total' => $statisticsSrv->_total,
        ];
        $this->response->send();
    }

    public function actionScriptNotes()
    {
        $offerNotesSrv = new OfferNotesSrv();
        $this->response->data = $offerNotesSrv->get();
        $this->response->send();
    }


    public function actionScriptSave()
    {
        $request = Yii::$app->request->post();
        $offerNotesSrv = new OfferNotesSrv();
        $this->response->data = $offerNotesSrv->save($request['offer_id'], $request['notes']);
        $this->response->send();
    }

    public function actionStatisticsRatingToday()
    {
        $this->response->data = [
            'operators' => (new OperatorRating())->getOperatorsTodayStatistics(),
        ];
        $this->response->send();
    }

    public function actionQueueList()
    {
        $queueSettingSrv = new QueueSettingSrv();
        $this->response->data = $queueSettingSrv->get();
        $this->response->send();
    }

    public function actionQueueUpdate($queue_id)
    {
        $queueSettingSrv = new QueueSettingSrv();
        if ($this->request->isPost) {
            $data = $queueSettingSrv->save($this->request->post(), $queue_id);
        } elseif ($this->request->isGet) {
            $data = $queueSettingSrv->get($queue_id);
        } else {
            $data = [];
        }

        $this->response->data = $data;
        $this->response->send();
    }

    public function actionQueueCreate()
    {
        $queueSettingSrv = new QueueSettingSrv();
        $this->response->data = $queueSettingSrv->save($this->request->post());
        $this->response->send();
    }

}