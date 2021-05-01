<?php


namespace webmaster\modules\api\controllers;


use common\models\log\orderInfo\OrderInfoInstrument;
use common\models\offer\Offer;
use common\models\offer\OfferView;
use common\models\order\Order;
use common\services\order\OrderCommonService;
use common\services\order\OrderNotFoundException;
use webmaster\models\partners\Partner;
use webmaster\models\partners\PartnerOrders;
use webmaster\models\partners\PartnerOffers;
use webmaster\modules\api\partners\PartnerCRM;
use webmaster\modules\api\partners\strategy\MyLandCRM;
use Yii;
use yii\base\Exception;

class PartnersController extends BehaviorController
{
    public $modelClass = 'webmaster\models\partners\PartnerOrders';

    public function behaviors(): ?array
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs']['actions'] = array_merge($behaviors['verbs']['actions'], [
            'orders' => ['get'],
            'offers' => ['get'],
            'save-slug' => ['post'],
            'send' => ['post'],
            'save-comment' => ['get']
        ]);
        return $behaviors;
    }

    public function actionOrders()
    {
        $response = Yii::$app->response;
        $response->data = array_reverse(PartnerOrders::getAllOrders());
        $response->send();
    }

    public function actionOffers()
    {
        $response = Yii::$app->response;
        $offers = OfferView::find()->select([
            'offer_id',
            'offer_name',
            'offer_status',
            'description',
            'img',
            '(SELECT SUM(IF(ta_active = 1, 1, 0)) AS temp ) as active',
        ])
            ->andWhere(['offer_view.advert_id' => Yii::$app->request->get('advertID')])
            ->groupBy('offer_id')
            ->asArray()
            ->all();
        foreach ($offers as &$offer) {
            $offer_geo = OfferView::getOfferGeoByOfferId($offer['offer_id']);
            foreach ($offer_geo as $key => $geo) {
                if ($geo['geo_id'] == null) unset($offer_geo[$key]);
            }
            $offer['geo'] = $offer_geo;
            $offer_adverts = OfferView::getOfferAdvertsByOfferId($offer['offer_id']);
            foreach ($offer_adverts as $key => $advert) {
                if ($advert['advert_id'] == null) unset($offer_adverts[$key]);
            }
            $pOfferModel = PartnerOffers::find()->where(['offer_id' => $offer['offer_id']])->one();
            if (isset($pOfferModel)) {
                $offer['partner_offer_hash'] = $pOfferModel['partner_offer_hash'];
            } else {
                $offer['partner_offer_hash'] = '';
            }

            $offer['adverts'] = $offer_adverts;
        }
        $response->data = $offers;
        $response->send();
    }

    public function actionSaveSlug()
    {
        $data = Yii::$app->request->post();
        $response = Yii::$app->response;
        if (isset($data['slug'])) {
            if ($model = PartnerOffers::find()->where(['offer_id' => $data['offer_id']])->one()) {
                $model->offer_obj_to_send = json_encode($data);
                $model->offer_status = $data['offer_status'];
                $model->partner_offer_hash = $data['slug'];
                $model->save();
            } else {
                $model = new PartnerOffers();
                $model->advert_id = 268;
                $model->offer_id = $data['offer_id'];
                $model->offer_name = $data['offer_name'];
                $model->offer_obj_to_send = json_encode($data);
                $model->partner_offer_hash = $data['slug'];
                $model->offer_status = $data['offer_status'];
                $model->save();
            }
        } else {
            foreach ($data as $offer) {
                if (isset($offer['slug'])) {
                    if ($model = PartnerOffers::find()->where(['offer_id' => $offer['offer_id']])->one()) {
                        $model->offer_obj_to_send = json_encode($offer);
                        $model->offer_status = $offer['offer_status'];
                        $model->partner_offer_hash = $offer['slug'];
                        $model->save();
                    } else {
                        $model = new PartnerOffers();
                        $model->advert_id = 268;
                        $model->offer_id = $offer['offer_id'];
                        $model->offer_name = $offer['offer_name'];
                        $model->partner_offer_hash = $offer['slug'];
                        $model->offer_obj_to_send = json_encode($offer);
                        $model->offer_status = $offer['offer_status'];
                        $model->save();
                    }
                } else {
                    throw new Exception('slug is not founds!', 500);
                }
            }
        }
        $response->data = $data;
        $response->send();
    }

    public function actionSend()
    {
        if (Yii::$app->request->post() === 'all') {
            $partners = Partner::getAllActivePartners();
            foreach ($partners as $partner){
                $partnerCRM = new PartnerCRM(new $partner->class_name());
                $partnerCRM->sendToPartner();
            }
        }
    }

    public function actionSaveComment()
    {
        $pOrders = PartnerOrders::find()
            ->where(['status' => PartnerOrders::ORDER_REJECT_STATUS])
            ->orWhere(['status' => PartnerOrders::ORDER_REJECT_STATUS])
            ->all();
        foreach ($pOrders as $pOrder) {
            $order = Order::find()->where(['order_id' => $pOrder->order_id])->one();
            $order->instrument = OrderInfoInstrument::PARTNER_CRM;
            $comment = $pOrder->crm_resp;
            (new OrderCommonService())->saveComment($order, $comment);
            (new OrderCommonService())->changeStatus($order, 0, ['reason_id' => 30]);
        }
    }
}