<?php
/**
 * Created by PhpStorm.
 * User: ihor
 * Date: 20.04.17
 * Time: 16:04
 */

namespace callcenter\modules\v1\controllers;

use callcenter\components\operator_config\OperatorConfig;
use callcenter\services\operator_activity\SkuCallcenterService;
use common\models\callcenter\CallList;
use common\models\callcenter\OperatorConf;
use common\models\customer\Customer;
use common\models\Instrument;
use common\models\Language;
use common\models\offer\Offer;
use common\models\order\Order;
use common\models\order\OrderSku;
use common\models\order\OrderStatus;
use common\models\order\OrderView;
use common\models\order\StatusReason;
use common\models\product\SkuView;
use common\services\callcenter\call_list\CallListService;
use common\services\callcenter\call_list\LeadCallsService;
use common\services\callcenter\call_list\OrderCallcenterService;
use common\services\callcenter\OfferNotesSrv;
use common\services\log\LogSrv;
use common\services\order\logic\status\OrderStatusReasonNotFoundException;
use common\services\order\OrderCommentService;
use common\services\order\OrderCommonService;
use common\services\order\OrderNotFoundException;
use common\services\order\RegOrderCustom;
use common\services\delivery\BlockDeliveryDateService;
use yii\base\Exception;
use yii\rest\ActiveController;
use yii\filters\auth\CompositeAuth;
use callcenter\filters\auth\HttpBearerAuth;
use Yii;


class OrderController extends ActiveController
{
    public $modelClass = 'common\models\order\Order';

    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);

    }

    public function actions()
    {
        return [];
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();


        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
            'authMethods' => [
                HttpBearerAuth::className(),
            ],

        ];

        // remove authentication filter
        $auth = $behaviors['authenticator'];
        unset($behaviors['authenticator']);

        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::className(),
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
            ],
        ];

        // re-add authentication filter
        $behaviors['authenticator'] = $auth;
        // avoid authentication on CORS-pre-flight requests (HTTP OPTIONS method)
        $behaviors['authenticator']['except'] = ['options', 'auto-mode-card'];


        return $behaviors;
    }

    public function actionIndex()
    {

    }


    public function actionAutoModeCard()
    {
        $request = Yii::$app->request;
        $order_id = $request->get('order_id');
        $sip = $request->get('sip');

        if ($order = Order::findOne(['order_hash' => $order_id]))
        {
            if ($operator = OperatorConf::findOne(['sip' => $sip])) (new OperatorConfig($operator->operator_id))->takeLead($order->order_id);
        }

        return false;
    }


    public function actionCreate()
    {
        $request = Yii::$app->request->post();
        $response = Yii::$app->response;
        $request['advert_id'] = Yii::$app->user->identity->getOwnerId()[0];
        $regOrderCustomSrv = new RegOrderCustom();
        $regOrderCustomSrv->init($request);
        $response->data = $regOrderCustomSrv->result;
        $response->send();
    }

    public function actionHistory()
    {
        $request = Yii::$app->request->post();
        $response = Yii::$app->response;
        $orderSrv = new OrderCallcenterService();
        $response->data = $orderSrv->getCustomerHistory(['phone' => $request['phone']]);
        $response->send();
    }
    
    public function actionHistoryLeadCalls()
    {
        $request = Yii::$app->request->post();
        $response = Yii::$app->response;
    
        $call_service = new CallListService();
        $list = $call_service->getCallList($request);
        
        $leadCallsService = new LeadCallsService();
        $response->data = $leadCallsService->getOperatorHistory();
        $response->send();
    }

    public function actionPlanDelivery()
    {
        $request = Yii::$app->request->post();
        $response = Yii::$app->response;
        $orderCallcenterSrv = new OrderCallcenterService();
        $declaration = isset($request['declaration']) ? $request['declaration'] : null;
        $isDelivered = $orderCallcenterSrv->planDelivery(
            $request['order_id'],
            $request['delivery_date'],
            $request['customer_address'],
            $declaration
        );
        $response->data = $isDelivered;
        $response->send();
    }

    public function actionDeliveryDate()
    {
        $response = Yii::$app->response;
        $request = Yii::$app->request->getBodyParams();
        $delivery_date = new BlockDeliveryDateService();
        $response->data = $delivery_date->getDeliveryDates($request);
        $response->send();
    }

    public function actionCall()
    {
        $request = Yii::$app->request->get();
        $response = Yii::$app->response;

        $order = Order::findOne($request['order_id']);

        if ($call_info = Yii::$app->cc_api->makeCall($order)) {

            try {
                (new OrderCallcenterService())->refreshDuration(Yii::$app->user->id, 4);
            } catch (\Exception $e) {

            }
        }

        $response->data = $call_info;
        $response->send();

    }

    public function actionStatus()
    {
        $response = Yii::$app->response;
        $response->data = OrderStatus::getStatuses();
        $response->send();
    }

    public function actionDetach()
    {
        $request = Yii::$app->request->get();
        $response = Yii::$app->response;

        $orderService = new OrderCallcenterService();
        $response->data = $orderService->detachLead($request['order_id']);
        $response->send();
    }

    public function actionComment()
    {
        $response = Yii::$app->response;
        $order_id = Yii::$app->request->get('order_id');
        $order = Order::findOne(['order_id' => $order_id]);
        $response->data = (new OrderCommentService())->getComments($order);
        $response->send();

//        $request = Yii::$app->request->get();
//        $response = Yii::$app->response;
//        $order_id = $request['order_id'];
//        $orderSrv = new OrderCallcenterService();
//        $response->data = $orderSrv->getComments($order_id);
//        $response->send();
    }

    public function actionAddComment()
    {
        $response = Yii::$app->response;
        $order_id = Yii::$app->request->post('order_id');
        $comment = Yii::$app->request->post('message');
        if (!$order = Order::findOne(['order_id' => $order_id])) throw new OrderNotFoundException('OrderNotFound');
        $order->setInstrument(Instrument::CALL_CENTER_COMMENT);
        if ((new OrderCommonService())->saveComment($order, $comment))
            $response->data = $order->comment;
        $response->send();

//        $request = Yii::$app->request->post();
//        $response = Yii::$app->response;
//        $order_id = $request['order_id'];
//        $message = $request['message'];
//
//
//        $log = new LogSrv(Order::findOne(['order_id' => $order_id]), Instrument::CALL_CENTER_COMMENT, $message);
//        $log->column = 'custom_comment';
//        if ($log->add()) $response->data = $log->getComment();
//        else $response->data = 'Fail';
//
//
//        $response->send();

    }

    public function actionCard()
    {
        $request = Yii::$app->request;
    
        if ( !$request->isPost) {
            throw new Exception('Not valid method!');
        }

        $call_service = new CallListService();

        $order = $call_service->getOrder($request->post());

        //TODO: Add log component

        return $order;
    }

    public function actionSkuSave()
    {
        $response = Yii::$app->response;

        $order_id = Yii::$app->request->post('order_id');
        $order_sku = Yii::$app->request->post('order_sku');

        $order = Order::findOne($order_id);
        $old_pcs = $order->total_amount;

        $orderService = new OrderCommonService();
        $orderService->saveOrderSku($order, $order_sku, Instrument::CALL_CENTER_SET_SKU);

//        $comment = "";
//        foreach ($order_sku as $sku)
//        {
//            $comment .= OrderSku::findOne($sku['sku_id'])->sku->sku_name . " to " . $sku['amount'] . ", ";
//        }
//
//        $logSrv = new LogSrv($order, Instrument::CALL_CENTER_SET_SKU,
//            $comment
//            );
//        $logSrv->column='custom_comment';
//        $logSrv->add();


        $new_pcs = $order->total_amount;

        $skuCallCenterSrv = new SkuCallcenterService();
        $skuCallCenterSrv->operatorUpsalesSave($order_id, $old_pcs, $new_pcs);

        $response_data = [
            'total_cost' => $order->total_cost,
            'currency' => $order->customer->country->currency->currency_name
        ];
        $response->data = $response_data;

        $response->send();

    }


    public function actionPlanCall()
    {

        if (Yii::$app->request->isPost) $request = Yii::$app->request->post();
        else throw new Exception('Not valid method!');

        $response = Yii::$app->response;

        $orderService = new OrderCallcenterService();

        if ($orderService->planCall($request)) $response->data = 'OK';
        else $response->data = 'Fail';

        //TODO: Add log component

        $response->send();
    }


    public function actionChangeOrderAddress()
    {

        if (Yii::$app->request->isPost) $request = Yii::$app->request->post();
        else throw new Exception('Not valid method!');

        $response = Yii::$app->response;

        $orderService = new OrderCallcenterService();

        $response->data = $orderService->changeOrderAddress($request);
        $response->send();

    }


    public function actionSse()
    {

        if (Yii::$app->request->isPost) $request = Yii::$app->request->post();
        else throw new Exception('Not valid method!');

        $response = Yii::$app->response;
        $call_service = new CallListService();

        $list = $call_service->getCallList($request);
        $response->data = $list;
        $response->send();
    }

    public function actionChangeLanguage()
    {
        $request = Yii::$app->request->post();
        $response = Yii::$app->response;
        $orderSrv = new OrderCallcenterService();
        $response->data = $orderSrv->changeOrderLanguage($request['order_id'], $request['language_id']);
        $response->send();

    }

    public function actionLanguages()
    {
        $response = Yii::$app->response;
        $response->data = Language::find()->select(['language_id', 'name as language_name'])->asArray()->all();
        $response->send();
    }

    /**
     * REJECT ORDER PART
     */


    public function actionReject()
    {


        if (Yii::$app->request->isPost) $request = Yii::$app->request->post();
        else throw new Exception('Not valid method!');

        $response = Yii::$app->response;

        $orderService = new OrderCallcenterService();
        $orderService->RejectOrder($request);

        $response->data = 'OK';
//        else $response->data = 'Fail';

        //TODO: Add log component

        $response->send();

    }

    public function actionRejectReasons()
    {
        $response = Yii::$app->response;
        $reasons = StatusReason::statusReasons()[OrderStatus::REJECTED];
        $data = [];
        unset($reasons[20]);
        unset($reasons[0]);
        foreach ($reasons as $reason_id => $reason_name) {
            $data[] = ['reason_id' => $reason_id, 'reason_name' => $reason_name];
        }
//        $data[] = ['reason_id' => 100, 'reason_name' => 'Other reason'];
        $response->data = $data;
        $response->send();
    }

    public function actionSkuRules()
    {
        $order_id = Yii::$app->request->get('order_id');
        $response = Yii::$app->response;
        $rules = Order::getOrderSkuRules($order_id);
        $response->data = array_values($rules);
        $response->send();
    }

    public function actionNotValidReasons()
    {
        $response = Yii::$app->response;
        $reasons = StatusReason::statusReasons()[OrderStatus::NOT_VALID];
        $data = [];
        unset($reasons[0]);
        foreach ($reasons as $reason_id => $reason_name) {
            $data[] = ['reason_id' => $reason_id, 'reason_name' => $reason_name];
        }
//        $data[] = ['reason_id' => 100, 'reason_name' => 'Other reason'];
        $response->data = $data;
        $response->send();
    }

    public function actionNotValid()
    {
        $response = Yii::$app->response;

        $order_id = Yii::$app->request->post('order_id');
        $message = Yii::$app->request->post('message');


        $order = Order::findOne($order_id);
        try {
            $order->setInstrument(Instrument::CALL_CENTER_CARD_NOT_VALID);
            $orderCommonSrv = new OrderCommonService();

            $orderCommonSrv->changeStatus($order, OrderStatus::NOT_VALID, ['reason_id' => $message]);
        } catch (Exception $e) {
            throw $e;
        }

        $response->send();
    }

    public function actionOfferNotes()
    {
        $offer_id = Yii::$app->request->post('offer_id');
        $geo_id = Yii::$app->request->post('geo_id');

        $offerNotesSrv = new OfferNotesSrv();
        $response = Yii::$app->response;
        $response->data = $offerNotesSrv->getByOfferGeo($offer_id, $geo_id);
        $response->send();
    }

    /**
     * END
     */


    public function actionOptions($id = null)
    {
        return "ok";
    }

//    public function actionSaveCustomerInfo(){
//        if (Yii::$app->request->isPost) $request = Yii::$app->request->post();
//        else throw new Exception('Not valid method!');
//
//        if (!empty($request)){
//
//        }
//    }
}