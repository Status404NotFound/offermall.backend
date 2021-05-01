<?php

namespace regorder\controllers;

use common\helpers\FishHelper;
use common\models\geo\Geo;
use common\models\offer\OfferGeoThankYouPage;
use common\models\order\Order;
use common\models\PartnerCrm;
use common\models\SendedToPartner;
use common\models\webmaster\postback\PostbackGlobal;
use common\models\webmaster\postback\PostbackIndividual;
use webmaster\models\finance\Finance;
use webmaster\modules\api\partners\strategy\MyLandCRM;
use webmaster\models\partners\PartnerOrders;
use common\services\partner\PartnerFactory;
use common\services\partner\PartnerService;
use common\services\webmaster\postback\PostbackService;
use common\services\ValidateException;
use regorder\services\customer\RegorderCustomerService;
use regorder\services\order\RegOrderService;
use yii\base\BaseObject;
use yii\base\Module;
use \yii\web\Response;
use Yii;
use Exception;

/**
 * RegOrder controller
 */
class OrderController extends BehaviorController
{
    /**
     * @var RegOrderService
     */
    private $regOrderService;
    /**
     * @var Response
     */
    private $response;

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

    /**
     * @throws \regorder\services\order\exceptions\RegOrderException
     * @throws \yii\base\Exception
     */
    public function actionIndex()
    {
        $attributes = Yii::$app->request->post();

        $attributes['userAgent'] = Yii::$app->request->userAgent;
        $attributes['userIP'] = Yii::$app->request->userIP;
//        Yii::trace($attributes, 'new_orders');
        if(isset($attributes['referrer'])){
            try {
                $order = $this->regOrderService->regOrder($attributes);
//            new PostbackService($order->order_id, 'url');

                $offerGeoPrice = $order->offer->getOfferGeoPrices()
                    ->where(['geo_id' => $this->regOrderService->geo_id])
                    ->one();

                $offerGeoThankYouPage = OfferGeoThankYouPage::find()
                    ->where([
                        'offer_id' => $order->offer_id,
                        'geo_id' => $this->regOrderService->geo_id
                    ])->one();

                $financeModel = new Finance();
                $financeModel->wm_id = $order->orderData->wm_id;
                $financeModel->order_id = $order->order_id;
                $financeModel->order_status = $order->order_status;
                $financeModel->target_status = $order->targetWm->targetWmGroup->wmOfferTarget->wm_offer_target_status;
                $financeModel->hold = $order->targetWm->targetWmGroup->hold;
                $financeModel->price = $order->targetWm->targetWmGroup->base_commission;
                $financeModel->payment_status = 0;
                $financeModel->save();


                new PostbackService($order->order_id, 'url');

                $this->saveToPartners($order);

                $this->response->data = [
//                'order_id' => $order->order_id,
                    'order_hash' => $order->order_hash,
                    'offer_geo_thank_you_page_url' => $offerGeoThankYouPage ? $offerGeoThankYouPage->url : null,

                    'currency' => (isset($order->targetAdvert) && isset($offerGeoPrice)) ? $offerGeoPrice->currency->currency_code : null,
//                'currency' => isset($order->targetAdvert) ? $order->targetAdvert->targetAdvertGroup->currency->currency_code : null,
                    'amount' => $order->getBaseItemCost(),

                    'customer_id' => $order->customer->customer_id,
                    'name' => $order->customer->name,
                    'phone' => (string)$order->customer->phone,

                    'pay_online' => isset($order->targetAdvert) ? $order->targetAdvert->pay_online : null,
                    'target_advert_id' => isset($order->targetAdvert) ? $order->target_advert_id : null,
                    'in_blacklist' => $this->regOrderService->in_blacklist,
                ];
            } catch (ValidateException $e) {
                $this->response->data = $e->getMessages();
            }
        }
        $this->response->send();
    }

    /**
     * @param Order $order
     * @param $geo_id
     */
    private function sendToPartnersCrm(Order $order, $geo_id)
    {
        $service = new PartnerService();
        $geo_iso = Geo::find()->where(['geo_id' => $geo_id])->one()->iso;
        $service->sendOrderToPartners($order, $geo_iso, true);
        $service->sendToMyLandCrm($order, $geo_iso);
    }

    private function saveToPartners(Order $order)
    {
        $offerHash = \webmaster\modules\api\partners\PartnerCRM::getOfferHash($order->offer_id);
        if(isset($offerHash))
        {
            $partnerOrder = new PartnerOrders();
            $partnerOrder->order_hash = $order->order_hash;
            $partnerOrder->order_id = $order->order_id;
            $partnerOrder->partner_id = isset($order->targetAdvert) ? $order->targetAdvert->advert_id : null;
            $partnerOrder->status = 0;
            $partnerOrder->method = 'form';
            $partnerOrder->save();
        }
    }

    /**
     * @param Order $order
     * @param $geo_id
     */
    private function savePartnerOrderToSend(Order $order, $geo_id)
    {
        $service = new PartnerService();
        $geo_iso = Geo::find()->where(['geo_id' => $geo_id])->one()->iso;
        $service->savePartnerOrderToSend($order, $geo_iso, true);
    }

    /**
     * @return array|bool
     */
    public function actionSaveEmail()
    {
        $request = Yii::$app->request;
        $regorderCustomerService = new RegorderCustomerService();
        return $regorderCustomerService->saveEmail($request->post('fields')['email'], $request->post('sid'));
    }
}
