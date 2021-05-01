<?php

namespace crm\modules\angular_api\controllers;

use common\helpers\FishHelper;
use common\models\finance\Currency;
use common\services\webmaster\Helper;
use crm\services\offer\OfferLandingService;
use yii\base\Exception;
use Yii;

class OfferLandingController extends BehaviorController
{
    public $modelClass = 'common\models\offer\OfferLanding';

    public function actionLandingList($offer_id)
    {
        $offerLandingService = new OfferLandingService($offer_id);
        $this->response->data['landings'] = $offerLandingService->getLandings();
        $this->response->data['transits'] = $offerLandingService->getTransits();
        $this->response->data['geo_price'] = $offerLandingService->getOfferGeoPrice();

        $this->response->send();
    }

    public function actionForms($offer_id)
    {
        $offerLandingService = new OfferLandingService($offer_id);
        $this->response->data = $offerLandingService->getFormList();
        $this->response->send();
    }


    public function actionSaveTransit()
    {
        $request = Yii::$app->request;
        $offer_id = $request->post('offer_id');
        $transits = $request->post('transits');
        $offerLandingService = new OfferLandingService($offer_id);
        $this->response->data = $offerLandingService->saveTransits($transits);
        $this->response->send();
    }

    public function actionSaveGeoPrice()
    {
        $request = Yii::$app->request;

        $offer_id = $request->post('offer_id');
        $offer_geo_price = $request->post('geo_price');

        $offerLandingService = new OfferLandingService($offer_id);

        $this->response->data = $offerLandingService->saveOfferGeoPrice($offer_geo_price);
        $this->response->send();
    }


    public function actionSaveLanding()
    {
        $request = Yii::$app->request;

        $landings = $request->post('landings');
        $offer_id = $request->post('offer_id');

        if (is_null($offer_id)) throw new Exception('Offer Id is NULL !!!');

        $offerLandingService = new OfferLandingService($offer_id);
        $this->response->data = $offerLandingService->saveLandings($landings);
        $this->response->send();
    }

    public function actionCurrency()
    {
        $query = Currency::find()
            ->select('currency_id, currency_name');
        $this->response->data = $query->asArray()->all();
        $this->response->send();
    }


    public function actionGeo()
    {
        $request = Yii::$app->request;

        $offer_id = $request->get('offer_id');

        if (is_null($offer_id)) throw new Exception('Offer Id is NULL !!!');

        $offerLandingService = new OfferLandingService($offer_id);

        $this->response->data = $offerLandingService->getGeo();

        $this->response->send();
    }

}
