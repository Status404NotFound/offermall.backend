<?php

namespace regorder\controllers;

use common\helpers\FishHelper;
use common\models\order\Order;

//use regorder\forms\orderOrderForm;
//use regorder\services\order\OrderService;
use common\services\callcenter\call_list\CallRegistration;
use regorder\services\order\RegOrderCustom;
use regorder\services\order\RegOrderRequest;
use regorder\services\order\RegOrderSrv;
use Yii;
use yii\base\Module;
use yii\base\Response;
use yii\helpers\Json;
use yii\web\Controller;

use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * RegOrder controller
 */
class OrderController__ extends Controller
{
//    private $orderService;

//    public function __construct($id, Module $module, $config = [])
//    {
//        parent::__construct($id, $module, $config);
//        $this->orderService = new OrderService();
//    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::className(),
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['POST'],
                'Access-Control-Allow-Credentials' => true,
                'Access-Control-Max-Age' => 3600,                 // Cache (seconds)
            ],
        ];

        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::className(),
            'actions' => [
                'register' => ['post'],
            ],
        ];

        return $behaviors;
    }


    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
//        $data = '{"order_hash":"1220263354","offer_id":"122","id":"122","order_status":"0","view_hash":"WVbD58vhHdGKWJqaE2yCFoDB","sid":"596cbacc4a89b","ip":"858227887","cookie":"15002979783133","browser":"chrome android webkit version_59.0.3071.125android_version_6.0;","os":"Android","phone":"0505234327","address":"\u0627\u0644\u062c\u0628\u064a\u0644 \u0627\u0644\u0628\u0644\u062f \u0634\u0627\u0631\u0639 \u062d\u0627\u0626\u0644 \u062a\u0642\u0627\u0637\u0639 \u0639\u064a\u0646\u064a\u0646 \u0633\u0648\u0642 \u0627\u0644\u0630\u0647\u0628","is_autolead":null,"name":"\u0639\u0628\u062f\u0647 \u0639\u0628\u062f\u0627\u0644\u0644\u0647 \u0639\u0644\u064a \u0635\u0627\u0644\u062d","referrer":"sa.baellerryitaly.uaeby.net"}';
        $data = Yii::$app->request->post('data');
        $reg = new RegOrderCustom(json_decode($data, true));


        $response = new \yii\web\Response();
//        $response->data = $reg;
        $response->data = [
            'order_id' => $reg->order->order_id,
            'status' => $reg->order->order_status,
        ];
        $response->format = \yii\web\Response::FORMAT_JSON;
        $response->send();


//        $old_crm_order = [
//            'order_hash' => 1520242852,
//            'offer_id' => null,
//            'order_status' => 0,
//            'created_at' => 1499117064,
//            'view_hash' => 'yfVYxr09ku6nbW1CkFpDhAaC',
//            'country_id' => 1,
//            'city_id' => 1,
//            'total_cost' => 65,
//            'ip' => '1580958231',
//            'cookie' => '14991170639699',
//            'browser' => 'safari iphone ios mobile safari_mobile mac webkit version_602.3.12',
//            'os'=> 'iPhone',
//            'phone' => '+91003',
//            'address' => 'Al shmkha',
//            'sid' => '595ab5e40bc10',
//            'is_autolead' => true,
//        ];

//        new RegOrderCustom($old_crm_order);

    }


    public function actionRequest(){
        $request = Yii::$app->request;
        $reg = new RegOrderRequest($request);

        $response = new \yii\web\Response();
//        $response->data = $reg;
        $response->data = [
            'order_id' => $reg->order->order_id,
            'status' => $reg->order->order_status,
            'customer_id' => $reg->customer->customer_id,
            'name' => $reg->customer->name,
            'phone' => $reg->customer->phone,
        ];
        $response->format = \yii\web\Response::FORMAT_JSON;
        $response->send();
    }

    public function actionRegCall($order_id){
        new CallRegistration($order_id, []);
    }



















//    public function actionRegister()
//    {
//        Yii::$app->response->data = 'werqweqwe';
//        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
////        return 123;
//
//
////        Yii::info(Yii::$app->request->get(), 'debug');
//
////        $request = Yii::$app->request->get();
//
//
//        $request = $this->getRequestArray();
//        Yii::info(Yii::$app->request->get(), 'debug');
//
//        $model = new Order();
//
////        FishHelper::debug($request);
//
//        if ($model->load($request)) {
//            $this->orderService->saveOrder($model);
//        }
//
//        Yii::info($request, 'conversionData');
//        Yii::$app->response->format = Response::FORMAT_JSON;
//
//        if ($model->validate()) {
//            $conversion = $model->registerConversion();
//            ApiController::registerConversion($model->sid); //push notification
//
//            $conv_hash_id = $conversion['hash_id'];
//
//            if ($conversion['autolead'] == 1) {
//                LogHelper::writeLog(LogHelper::TRAFARET_LOG, $conversion['hash_id'] . ' AUTOLEAD ');
//            } elseif ($conversion['autolead'] == 0) {
//                $sendToApiName = new SendToTrafaret();
//                $sendToApiName->sendConversion($conv_hash_id);
//            }
//
//            return $conversion ? 1 : 0;
//        } else {
//            return ['response' => $model->getErrors('fields_data')];
//        }
//        //}
//
//
//        //        Yii::$app->response->data = Yii::$app->request->get();
////        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
////        Yii::$app->response->send();
//
//
////        return $this->redirect(Yii::$app->request->referrer);
////        return $this->render('index');
////        die;
////        return $this->render('index');
//    }

    public function getRequestArray()
    {
        return [
            'hash' => '148612891265183',
            'referral' => '',
            'name' => '3123',
            'phone' => '+971 3333333333',

            'ip' => '127.0.0.1',
            'cookie' => '',


            'view_hash' => 'NZaSzfwCZKqMUqizTbz2fhCU',
            'trustorder-formid' => 'HairStraightenerForm',
            'sid' => '58d19f37eb984',
            'fields' => [
                'address' => 'wewe',
                'zip' => '',
                'surname' => '',
            ],
            'view_time' => '4098',
            'autolead' => '0',
        ];
    }

}