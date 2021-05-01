<?php

namespace crm\modules\angular_api\controllers;

use common\models\geo\Geo;
use common\models\PartnerCrm;
use common\services\partner\LpCrm;
use common\services\partner\PartnerService;
use Yii;
use common\models\log\orderInfo\OrderInfoInstrument;
use common\models\offer\Offer;
use common\models\onlinePayment\OnlinePayment;
use common\models\order\Order;
use common\models\order\OrderData;
use common\models\order\OrderStatus;
use common\models\order\StatusReason;
use common\modules\user\models\tables\User;
use common\services\order\OrderCommentService;
use common\services\order\OrderCommonService;
use common\services\order\OrderNotFoundException;
use common\services\order\RegOrderCustom;
use common\services\ValidateException;
//use crm\services\order\OrderService;
use crm\services\order\search\OrderSearchFactory;
use yii\base\Exception;

/**
 * Class OrderController
 * @package crm\modules\angular_api\controllers
 */
class OrderController extends BehaviorController
{
    /**
     * @var string
     */
    public $modelClass = Order::class;

    ///**
    //* @var OrderService
    //*/
    //private $orderService;

    ///**
    //* OrderController constructor.
    //* @param $id
    //* @param $module
    //* @param array $config
    //*/
    //public function __construct($id, $module, $config = [])
    //{
    //parent::__construct($id, $module, $config);
    //$this->orderService = new OrderService();
    //}

    public function actionOrders(): void
    {
        $page = Yii::$app->request->getBodyParam('page');
        $sort_field = Yii::$app->request->getBodyParam('sortField');
        $searchService = (new OrderSearchFactory())->createSearch($page);
        $orders = $searchService->getOrders(
            $this->getRequestFilters(),
            $this->getRequestPagination(),
            $this->getRequestSortOrder(),
            $sort_field
        );

        $this->response->data['orders'] = $orders['orders'];
        $this->response->data['count'] = $orders['count'];

        if ($page == 'group') {
            $this->response->data['full_id_list'] = $orders['full_id_list'];
        }

        if (isset($orders['not_found_orders'])) {
            $this->response->data['not_found_orders'] = $orders['not_found_orders'];
        }

        $this->setPaginationHeaders($orders['count']['count_all']);
        $this->response->send();
    }

    public function actionOrderUser()
    {
        $users = User::find()->select('id, username')->asArray()->all();
        $this->response->data = $users;
        $this->response->send();
    }

    public function actionOrderOffer()
    {
        $offers = Offer::find()->select('offer_id, offer_name')->asArray()->all();
        $this->response->data = $offers;
        $this->response->send();
    }

    public function actionSetComment()
    {
        $order_id = Yii::$app->request->post('order_id');
        $comment = Yii::$app->request->post('comment');
        if (!$order = Order::findOne(['order_id' => $order_id])) throw new OrderNotFoundException('OrderNotFound');
        $order->instrument = $this->setInstrument();
        if ((new OrderCommonService())->saveComment($order, $comment))
            $this->response->data = $order->comment;
        $this->response->send();
    }

    public function actionGetComment()
    {
        $order_id = Yii::$app->request->get('order_id');
        $order = Order::findOne(['order_id' => $order_id]);
        $this->response->data = (new OrderCommentService())->getComments($order);
        $this->response->send();
    }

    /**
     * @throws Exception
     * @throws ValidateException
     */
    public function actionBitrixFlag()
    {
        $orders_id_array = Yii::$app->request->getBodyParam('orders_id_array');
        $bitrix_flag = Yii::$app->request->getBodyParam('bitrix_flag');
        $orders = Order::findAll(['order_id' => $orders_id_array]);
        if (!empty($orders)) {
            try {
                foreach ($orders as $order) {
                    $order->instrument = $this->setInstrument();
                    $order->bitrix_flag = (int)$bitrix_flag;
                    if (!$order->save()) throw new ValidateException($order->errors);
                }
            } catch (ValidateException $e) {
//                $this->response->data['message'] = $e->getMessages();
                throw $e;
            } catch (Exception $e) {
//                $this->response->data['message'] = $e->getMessage();
                throw $e;
            }
        }
        $this->response->send();
    }

    public function actionPaymentData()
    {
        $order_id = Yii::$app->request->getBodyParam('order_id');
        if (!$onlinePayment = OnlinePayment::find()->where(['order_id' => $order_id])->asArray()->one())
            throw new Exception('Payment Not Found');
        $this->response->data = json_decode($onlinePayment['serialized_data'], true);
//        $this->response->data = [
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
        $this->response->send();
    }

    public function actionDeclaration()
    {
        $order_id = Yii::$app->request->getBodyParam('order_id');
        $declaration = Yii::$app->request->getBodyParam('declaration');
        if (!$order_data = OrderData::findOne(['order_id' => $order_id]))
            throw new OrderNotFoundException($order_id);
        $order_data->declaration = $declaration;
        if (!$order_data->save())
            throw new ValidateException($order_data->errors);
        $this->response->send();
    }

    public function actionChangeGeo()
    {
        $offer_id = Yii::$app->request->get()['offer_id'];
        $regOrderCustomSrv = new RegOrderCustom();
        $this->response->data = $regOrderCustomSrv->getChangeGeoData($offer_id);
        $this->response->send();
    }

    public function actionSaveChangeGeo()
    {
        $request = Yii::$app->request->post();
        $regOrderCustomSrv = new RegOrderCustom();
        $this->response->data = $regOrderCustomSrv->initChangeGeo($request);
        $this->response->send();
    }

    public function actionDelete()
    {
        $order_id = Yii::$app->request->get('order_id');
//        throw new OrderNotFoundException('It\'s not possible to remove the order, try tomorrow.');
        if (!$order = Order::findOne(['order_id' => $order_id]))
            throw new OrderNotFoundException('Order not found');
        $order->deleted = 1;
        if (!$order->save())
            throw new ValidateException($order->errors);
        $this->response->send();
    }

    public function actionInformation()
    {
        $order_id = Yii::$app->request->getBodyParam('order_id');
        $information = Yii::$app->request->getBodyParam('information');
        if (!$order = Order::findOne(['order_id' => $order_id]))
            throw new OrderNotFoundException('Order not found');
        $order->information = $information;
        if (!$order->save())
            throw new ValidateException($order->errors);
        $this->response->send();
    }

    public function actionCreate()
    {
        $request = Yii::$app->request->post();
        $request['advert_id'] = Yii::$app->user->identity->getOwnerId();
        $regOrderCustomSrv = new RegOrderCustom();
        $regOrderCustomSrv->init($request);
        $this->response->data = $regOrderCustomSrv->result;
        $this->response->send();

    }

    public function actionStatusReason()
    {
        if (OrderStatus::statusNeedReason(OrderStatus::NOT_VALID_CHECKED) === true) {
            $reason = StatusReason::getIndexedReasons(OrderStatus::NOT_VALID_CHECKED);
        } else {
            $reason = [];
        }
        $this->response->data = $reason;
        $this->response->send();
    }

    public function actionSendToPartnerCrm()
    {
        $order_id = Yii::$app->request->getBodyParam('orderId');
        $partner_id = Yii::$app->request->getBodyParam('partnerId');

        $order = Order::find()->where(['order_id' => $order_id])->one();
        $partner = PartnerCrm::find()->where(['id' => $partner_id])->one();

        $this->response->data = ['success' => true];
        if ($order && $partner) {
            if (!$order->orderSku) {
                throw new Exception('You need to change at list one SKU in Callcenter!');
            }
            $service = new PartnerService();
            $service->sendOrderToPartner($order, $partner, $order->targetAdvert->targetAdvertGroup->advertOfferTarget->geo->iso);
        } else {
//            throw new OrderNotFoundException('Order ' . $order_id . ' or partner ' . $partner_id . ' not found.');
            throw new OrderNotFoundException('Order or partner not found.');
        }
        $this->response->send();
    }

    private function setInstrument()
    {
        $page = $this->parseReferrer();
        $instrument = 0;
        switch ($page) {
            case '/order':
                $instrument = OrderInfoInstrument::CRM_ORDERS;
                break;
            case '/delivery/waiting-for-delivery':
                $instrument = OrderInfoInstrument::CRM_WFD;
                break;
            case '/delivery/group-search-order':
                $instrument = OrderInfoInstrument::CRM_GROUP_SEARCH;
                break;
            case '/delivery/deliveries':
                $instrument = OrderInfoInstrument::CRM_DELIVERY;
                break;
            default:
                $instrument = 0;
                break;
        }
        return $instrument;
    }
}
