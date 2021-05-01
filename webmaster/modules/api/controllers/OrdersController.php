<?php


namespace webmaster\modules\api\controllers;

use Codeception\Lib\Di;
use common\helpers\FishHelper;
use common\models\customer\Customer;
use common\models\customer\CustomerSystem;
use common\models\flow\Flow;
use common\models\geo\Geo;
use common\models\offer\Offer;
use common\models\offer\OfferGeoThankYouPage;
use common\models\order\Order;
use common\models\order\OrderData;
use common\models\webmaster\postback\PostbackGlobal;
use common\models\webmaster\postback\PostbackIndividual;
use common\models\webmaster\WmCheckout;
use common\services\GeoService;
use common\services\partner\PartnerService;
use common\services\ValidateException;
use common\services\webmaster\postback\PostbackService;
use regorder\services\order\OrderAdvertService;
use regorder\services\order\OrderWmService;
use webmaster\models\api\UserApi;
use webmaster\models\finance\Finance;
use webmaster\models\partners\PartnerOrders;
use webmaster\models\partners\PartnerOffers;
use webmaster\modules\api\partners\PartnerCRM;
use webmaster\modules\api\partners\strategy\MyLandCRM;
use webmaster\services\finance\FinanceService;
use yii\base\BaseObject;
use yii\filters\Cors;
use yii\web\Response;
use regorder\services\order\RegOrderService;
use yii\base\Module;
use Yii;

class OrdersController extends BehaviorController
{
    public $modelClass = 'webmaster\models\api\OrderApi';

    /**
     * @var RegOrderService
     */
    private $regOrderService;

    /**
     * OrderController constructor.
     * @param $id
     * @param Module $module
     * @param array $config
     */
    public function __construct($id, Module $module, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->regOrderService = new RegOrderService();
        $this->response = new Response();
        $this->response->format = Response::FORMAT_JSON;
    }


    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs']['actions'] = array_merge($behaviors['verbs']['actions'], [
            'create-order' => ['post'],
            'payment' => ['get'],
            'done-payment' => ['get'],
            'change-payment-status' => ['post']
        ]);
        $behaviors['authenticator']['except'] = array_merge($behaviors['authenticator']['except'], ['create-order', 'payment', 'change-payment-status']);
        return $behaviors;
    }

    public function actionCreateOrder(){
        $attributes = Yii::$app->request->post();

        $api_user = UserApi::getUserApiByApiKey($attributes['api-key']);
        if(!isset($api_user)) {
            $this->response->data = [
                'status' => 400,
                'error' => 'Api Key not found'
            ];
        }

        $wm_flows = Flow::getFlowsByWmId($api_user->user_id);

        $flow = null;

        foreach ($wm_flows as $wm_flow){
            if($wm_flow['flow_key'] == $attributes['flow-key']) {
                $flow = $wm_flow;
            }
        }
        if(!isset($flow)){
            $this->response->data = [
                'status' => 400,
                'error' => 'Flow incorrect'
            ];
        }
        $geo = Geo::getGeoByIso($attributes['geoiso']);
        if(!isset($geo)){
            $this->response->data = [
                'status' => 400,
                'error' => 'Geo not found'
            ];
        }

        $customerModel = new Customer();

        $customerModel->name = $attributes['name'];
        $customerModel->phone = $attributes['phone'];
        $customerModel->phone_country_code = $geo['phone_code'];
        $customerModel->phone_string = $customerModel->phone_country_code . ' ' . $customerModel->phone;
        $customerModel->country_id = $geo['geo_id'];
        $customerModel->city_id = isset($attributes['city_id']) ? $attributes['city_id'] : null;
        $customerModel->address = isset($attributes['address']) ? $attributes['address'] : null;
        $customerModel->email = isset($attributes['email']) ? $attributes['email'] : null;

        $customerModel->save();

        $this->saveCustomerSystem($customerModel->customer_id, $attributes, $geo['geo_id']);

        $orderModel = new Order();

        $orderModel->offer_id = $flow['offer_id'];
        $orderModel->customer_id = $customerModel->customer_id;
        $orderModel->flow_id = $flow['flow_id'];
        $orderModel->order_hash = $orderModel->offer_id . 0 . time();

        $advert_offer_target_status = $flow['advert_offer_target_status'];
        $wm_geo = Offer::getWmGeo($flow['offer_id']);

        $target_wm_id = (new OrderWmService($orderModel, $geo['geo_id']))->getTargetWmId($advert_offer_target_status, $api_user->user_id);
        $target_advert_id = in_array($geo['geo_id'], $wm_geo)
            ? (new OrderAdvertService($orderModel, $geo['geo_id'], $advert_offer_target_status))->getTargetAdvertId()
            : null;

        $orderModel->target_wm_id = $target_wm_id;
        $orderModel->target_advert_id = $target_advert_id;

        $orderModel->save();

        if ($orderModel->getOrderId()){
            $orderDataModel = new OrderData();

            if (isset($attributes['subid1'])) $orderDataModel->sub_id_1 = $attributes['subid1'];
            if (isset($attributes['subid2'])) $orderDataModel->sub_id_2 = $attributes['subid2'];
            if (isset($attributes['subid3'])) $orderDataModel->sub_id_3 = $attributes['subid3'];
            if (isset($attributes['subid4'])) $orderDataModel->sub_id_4 = $attributes['subid4'];
            if (isset($attributes['subid5'])) $orderDataModel->sub_id_5 = $attributes['subid5'];

            $orderDataModel->order_id = $orderModel->getOrderId();
            $orderDataModel->order_hash = $orderModel->order_hash;
            $orderDataModel->owner_id = ($orderModel->target_advert_id !== null) ? $orderModel->targetAdvert->advert_id : null;
            $orderDataModel->wm_id = $api_user->user_id;
            $orderDataModel->offer_id = $flow['offer_id'];
            $orderDataModel->referrer = 'api '.$api_user->api_key;
    
            $orderDataModel->save();
        }

        new PostbackService($orderModel->getOrderId(), 'url');

        $financeModel = new Finance();
        $financeModel->wm_id = $orderDataModel->wm_id;
        $financeModel->order_id = $orderModel->order_id;
        $financeModel->order_status = $orderModel->order_status;
        $financeModel->target_status = $orderModel->targetWm->targetWmGroup->wmOfferTarget->wm_offer_target_status;
        $financeModel->hold = $orderModel->targetAdvert->targetAdvertGroup->daily_limit;
        $financeModel->price = $orderModel->wm_commission;
        $financeModel->payment_status = 0;
        $financeModel->save();

        $offerHash = MyLandCRM::getOfferHash($flow['offer_id']);
        if(isset($offerHash))
        {
            $partnerOrder = new PartnerOrders();
            $partnerOrder->order_id = $orderModel->getOrderId();
            $partnerOrder->partner_id = $orderDataModel->owner_id;
            $partnerOrder->status = 0;
            $partnerOrder->method = 'api';
            $partnerOrder->save();

            $partnerCRM = new PartnerCRM(new MyLandCRM());
            $partnerCRM->sendToPartner();
        }

        file_put_contents('/var/www/crmka.net/crmka.net-prod-back/tests.txt', json_encode($attributes, JSON_PRETTY_PRINT));

        $this->response->data = ['message' => 'OK'];
        $this->response->send();
    }

    public function actionPayment()
    {
        $financeSrv = new FinanceService();
        $paymentOrders = $financeSrv->getAllProcessingOrders();
        $response = Yii::$app->response;
        $response->data = $paymentOrders;
        $response->send();
    }

    private function saveCustomerSystem($customer_id, $data, $geo_id)
    {
        $customerSystem = CustomerSystem::findOne(['customer_id' => $customer_id]) ?? new CustomerSystem();
        $customerSystem->setAttributes([
            'customer_id' => $customer_id,
            'ip' => $data['userIP'],
            'country_id' => $geo_id,
            'os' => $this->getOS($data['user_agent']),
            'browser' => $this->getBrowser($data['user_agent']),
            'cookie' => isset($data['cookie']) ? $data['cookie'] : null,
            'sid' => isset($data['sid']) ? $data['sid'] : null,
            'view_hash' => isset($data['view_hash']) ? $data['view_hash'] : null,
        ]);
        if (!$customerSystem->save()) throw new ValidateException($customerSystem->errors);
        return true;
    }

    private function getBrowser($user_agent) :string
    {
        $browser = [
            'opera' => function ($user_agent) {
                return (preg_match('/opera/i', $user_agent)) ? true : false;
            },
            'msie' => function ($user_agent) {
                return (preg_match('/msie/i', $user_agent) && !preg_match('/opera/i', $user_agent)) ? true : false;
            },
            'msie6' => function ($user_agent) {
                return (preg_match('/msie 6/i', $user_agent) && !preg_match('/opera/i', $user_agent)) ? true : false;
            },
            'msie7' => function ($user_agent) {
                return (preg_match('/msie 7/i', $user_agent) && !preg_match('/opera/i', $user_agent)) ? true : false;
            },
            'msie8' => function ($user_agent) {
                return (preg_match('/msie 8/i', $user_agent) && !preg_match('/opera/i', $user_agent)) ? true : false;
            },
            'msie9' => function ($user_agent) {
                return (preg_match('/msie 9/i', $user_agent) && !preg_match('/opera/i', $user_agent)) ? true : false;
            }, 'msie10' => function ($user_agent) {
                return (preg_match('/msie 10/i', $user_agent) && !preg_match('/opera/i', $user_agent)) ? true : false;
            },
            'msie11' => function ($user_agent) {
                return (preg_match('/msie 11/i', $user_agent) && !preg_match('/opera/i', $user_agent)) ? true : false;
            },
            'mozilla' => function ($user_agent) {
                return (preg_match('/firefox/i', $user_agent)) ? true : false;
            },
            'chrome' => function ($user_agent) {
                return (preg_match('/chrome/i', $user_agent)) ? true : false;
            },
            'safari' => function ($user_agent) {
                return (!preg_match('/chrome/i', $user_agent) && preg_match('/webkit|safari|khtml/i', $user_agent)) ? true : false;
            },
            'iphone' => function ($user_agent) {
                return (preg_match('/iphone/i', $user_agent)) ? true : false;
            },
            'ipod' => function ($user_agent) {
                return (preg_match('/ipod/i', $user_agent)) ? true : false;
            },
            'iphone4' => function ($user_agent) {
                return (preg_match('/iphone.*OS 4/i', $user_agent)) ? true : false;
            },
            'ipod4' => function ($user_agent) {
                return (preg_match('/ipod.*OS 4/i', $user_agent)) ? true : false;
            },
            'ipad' => function ($user_agent) {
                return (preg_match('/ipad/i', $user_agent)) ? true : false;
            },
            'ios' => function ($user_agent) {
                return (preg_match('/ipad|ipod|iphone/i', $user_agent)) ? true : false;
            },
            'android' => function ($user_agent) {
                return (preg_match('/android/i', $user_agent)) ? true : false;
            },
            'bada' => function ($user_agent) {
                return (preg_match('/bada/i', $user_agent)) ? true : false;
            },
            'mobile' => function ($user_agent) {
                return (preg_match('/iphone|ipod|ipad|opera mini|opera mobi|iemobile/i', $user_agent)) ? true : false;
            },
            'msie_mobile' => function ($user_agent) {
                return (preg_match('/iemobile/i', $user_agent)) ? true : false;
            },
            'safari_mobile' => function ($user_agent) {
                return (preg_match('/iphone|ipod|ipad/i', $user_agent)) ? true : false;
            },
            'opera_mobile' => function ($user_agent) {
                return (preg_match('/opera mini|opera mobi/i', $user_agent)) ? true : false;
            },
            'opera_mini' => function ($user_agent) {
                return (preg_match('/opera mini/i', $user_agent)) ? true : false;
            },
            'mac' => function ($user_agent) {
                return (preg_match('/mac/i', $user_agent)) ? true : false;
            },
            'webkit' => function ($user_agent) {
                return (preg_match('/webkit/i', $user_agent)) ? true : false;
            },
            'version' => function ($user_agent) {
                return (preg_match('/.+(?:me|ox|on|rv|it|era|ie)[\/: ]([\d.]+)/', $user_agent, $matches)) ? $matches[1] : 0;
            },
            'android_version' => function ($user_agent) {
                $start = strpos($user_agent, "Android") + 8;
                $end = strpos(substr($user_agent, $start), ' ');
                return $start !== 8 ? substr($user_agent, $start, $end) : 0;
            },
        ];

        $results = '';
        foreach ($browser as $key => $value) {
            if (($val = $value($user_agent))) {
                if ($key == 'version' || $key == 'android_version') {
                    $results .= $key . '_' . $val;
                } else {
                    $results .= $key . ' ';
                }
            }
        }
        return $results;
    }

    private function getOS($userAgent) :string
    {

        $os_platform = "Unknown OS Platform";
        $os_array = [
            '/windows nt 10/i' => 'Windows 10',
            '/windows nt 6.3/i' => 'Windows 8.1',
            '/windows nt 6.2/i' => 'Windows 8',
            '/windows nt 6.1/i' => 'Windows 7',
            '/windows nt 6.0/i' => 'Windows Vista',
            '/windows nt 5.2/i' => 'Windows Server 2003/XP x64',
            '/windows nt 5.1/i' => 'Windows XP',
            '/windows xp/i' => 'Windows XP',
            '/windows nt 5.0/i' => 'Windows 2000',
            '/windows me/i' => 'Windows ME',
            '/win98/i' => 'Windows 98',
            '/win95/i' => 'Windows 95',
            '/win16/i' => 'Windows 3.11',
            '/macintosh|mac os x/i' => 'Mac OS X',
            '/mac_powerpc/i' => 'Mac OS 9',
            '/linux/i' => 'Linux',
            '/ubuntu/i' => 'Ubuntu',
            '/iphone/i' => 'iPhone',
            '/ipod/i' => 'iPod',
            '/ipad/i' => 'iPad',
            '/android/i' => 'Android',
            '/blackberry/i' => 'BlackBerry',
            '/webos/i' => 'Mobile'
        ];

        foreach ($os_array as $regex => $value) {
            if (preg_match($regex, $userAgent)) {
                $os_platform = $value;
            }
        }
        return $os_platform;
    }

    public function actionDonePayment()
    {
        $financeSrv = new FinanceService();
        $paymentOrders = $financeSrv->getAllCompletedOrders();
        $response = Yii::$app->response;
        $response->data = $paymentOrders;
        $response->send();
    }

    public function actionChangePaymentStatus()
    {
        $request = Yii::$app->request->post();
        $wmCheckout = WmCheckout::getById($request['id']);
        $wmCheckout->setAttribute('status', $request['newStatus']);
        $wmCheckout->setAttribute('updated_at', $request['newStatus']);
        $wmCheckout->save();
        $financeSrv = new FinanceService();
        $paymentOrders = $financeSrv->getAllProcessingOrders();
        $response = Yii::$app->response;
        $response->data = $paymentOrders;
        $response->send();
    }
}