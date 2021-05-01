<?php

namespace crm\modules\angular_api\controllers;

use common\models\finance\Currency;
use common\models\geo\Geo;
use common\models\offer\Offer;
use common\models\offer\targets\advert\TargetAdvert;
use common\modules\user\models\tables\User;
use common\services\offer\OfferNotFoundException;
use crm\services\targets\logic\AdvertTargetDataProvider;
use crm\services\targets\AdvertTargetService;
use Yii;

class PartnerTargetController extends BehaviorController
{
    public $modelClass = 'common\models\offer\targets\advert\AdvertOfferTarget';

    private $advertTargetService;

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs']['actions'] = array_merge($behaviors['verbs']['actions'], [
            'geo-list' => ['get'],
            'advert-list' => ['get'],
            'advert-rules' => ['get'],
        ]);

        return $behaviors;
    }

    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->advertTargetService = new AdvertTargetService();
    }

    public function actionCreate()
    {
        $request = Yii::$app->request->post();
        $result = $this->advertTargetService->saveAdvertTargets($request['offer_id'], $request['advert_targets']);
        if ($result !== true) {
            $this->response->statusCode = 500;
            $this->response->data = $this->advertTargetService->getErrors();
        }
        $this->response->send();
    }


    public function actionView()
    {
        $offer_id = (integer)Yii::$app->request->get('offer_id');
        if (!Offer::find()
            ->where(['offer_id' => $offer_id])
            ->count()) throw new OfferNotFoundException('Offer not found');
        $this->response->data = ['targets' => (new AdvertTargetService())->getAdvertTargetData($offer_id, AdvertTargetDataProvider::ADVERT_TAB)];
        $this->response->send();
    }

    public function actionGeoList()
    {
        $this->response->data = Geo::list();
        $this->response->send();
    }

    public function actionCurrencyList()
    {
        $this->response->data = Currency::list();
        $this->response->send();
    }

    public function actionAdvertRules()
    {
        $target_advert_id = Yii::$app->request->post('target_advert_id');
        $advert_id = Yii::$app->request->post('advert_id');
        $rules = TargetAdvert::find()->select(['base_commission', 'exceeded_commission'])->asArray()->one();
        $this->response->data = [
            'base_commission' => $rules['base_commission'],
            'exceeded_commission' => $rules['exceeded_commission'],
            'rules' => $this->advertTargetService->getAdvertRules($target_advert_id, $advert_id)];
        $this->response->send();
    }

    public function actionAdvertList()
    {
        $this->response->data = User::find()
            ->select(['id', 'username'])
            ->where(['role' => User::ROLE_ADVERTISER])
            ->asArray()->all();
        $this->response->send();
    }
}