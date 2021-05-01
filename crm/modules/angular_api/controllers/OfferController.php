<?php

namespace crm\modules\angular_api\controllers;

use Yii;
use common\helpers\FishHelper;
use common\models\offer\Offer;
use common\models\offer\OfferView;
use common\models\offer\targets\advert\TargetAdvertView;
use common\models\product\Product;
use common\services\offer\OfferNotFoundException;
use crm\services\offer\OfferService;

class OfferController extends BehaviorController
{
    public $modelClass = 'common\models\offer\Offer';

    /**
     * @var OfferService
     */
    private $offerService;

    /**
     * OfferController constructor.
     * @param $id
     * @param $module
     * @param array $config
     */
    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->offerService = new OfferService();
    }

    public function actionOffers()
    {
//        $sort_field = Yii::$app->request->getBodyParam('sortField');
        $result = $this->offerService->findOffers($this->filters, $this->getRequestPagination(), null, $this->getRequestSortOrder());
        $offers = $result['offers'];
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

            $offer['adverts'] = $offer_adverts;
        }
        $this->response->data = $offers;
        $this->setPaginationHeaders($result['count']);
        $this->response->send();
    }

    public function actionOffer()
    {
        $this->response->data = $this->offerService->findOffer(Yii::$app->request->get('offer_id'));
        $this->response->send();
    }

    public function actionView()
    {
        $offer_id = Yii::$app->request->get('offer_id');
        $offer = Offer::find()->where(['offer_id' => $offer_id])->asArray()->one();
        $offer['product_id'] = Offer::findProductIds($offer_id);
        $this->response->data = $offer;
        $this->response->send();
    }

    public function actionProductList()
    {
        $this->response->data = Product::find()->productList();
        $this->response->send();
    }

    public function actionCreate()
    {
        $request = Yii::$app->request->post();
        $offer = new Offer();
        $this->offerService->saveOffer($offer, $request);
        $this->response->data = ['offer_id' => $offer->offer_id];
        $this->response->send();
    }

    public function actionUpdate()
    {
        $request = Yii::$app->request->getBodyParams();
        if (!$offer = Offer::findOne(['offer_id' => $request['offer_id']]))
            throw new OfferNotFoundException();
        $this->offerService->saveOffer($offer, $request);
        $this->offerService->sendWebmasterNotify($request);
        $this->response->send();
    }

    public function actionStatus()
    {
        $request = Yii::$app->request->getBodyParams();
        if (!$offer = Offer::findOne(['offer_id' => $request['offer_id']]))
            throw new OfferNotFoundException();

        $offer->offer_status = $request['offer_status'];
        if (!$offer->save())
            $this->response->data = $offer->errors;

        $this->response->send();
    }
}