<?php

namespace crm\modules\angular_api\controllers;

use Yii;
use common\models\DataList;
use common\models\geo\Countries;
use common\models\geo\Geo;
use common\models\order\OrderStatus;
use common\models\webmaster\WmCheckout;
use common\modules\user\models\tables\User;
use crm\services\notify\NotifyService;
use yii\helpers\ArrayHelper;

class DataListController extends BehaviorController
{
    public $modelClass = 'common\models\DataList';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs']['actions'] = array_merge($behaviors['verbs']['actions'], [
            'geo' => ['get'],
            'geo-cities' => ['get'],
            'status' => ['get'],
        ]);
        return $behaviors;
    }

    public function actionGeo()
    {
        $this->response->data = Geo::list();
        $this->response->send();
    }

    public function actionGeoIso()
    {
        $query = Geo::find()
            ->select('geo_id, iso')
            ->asArray()
            ->all();

        $this->response->data = ArrayHelper::map($query, 'geo_id', 'iso');
        $this->response->send();
    }

    public function actionGeoCities()
    {
        $this->response->data = Geo::find()
            ->with([
                'geoRegions' => function ($query) {
                    $query->orderBy(['region_name' => SORT_ASC]);
                },
                'geoCities' => function ($query) {
                    $query->orderBy(['city_name' => SORT_ASC]);
                },
            ])
            ->asArray()
            ->all();
        $this->response->send();
    }

    public function actionCountries()
    {
        $this->response->data = Countries::find()
            ->asArray()
            ->all();
        $this->response->send();
    }

    public function actionStatus()
    {
        $this->response->data = OrderStatus::getStatuses();
        $this->response->send();
    }

    public function actionAdverts()
    {
        $this->response->data = (new DataList())->getUsers(User::ROLE_ADVERTISER);
        $this->response->send();
    }

    public function actionWebmasters()
    {
        $this->response->data = (new DataList())->getUsers([User::ROLE_WEBMASTER, User::ROLE_SUPER_WM]);
        $this->response->send();
    }

    public function actionOffersGeo()
    {
        $this->response->data = (new DataList())->getOffersGeo();
        $this->response->send();
    }

    public function actionOffers()
    {
        $this->response->data = (new DataList())->getOffers();
        $this->response->send();
    }

    public function actionGeoOffers()
    {
        $geo_id = Yii::$app->request->get('geo_id');
        $this->response->data = (new DataList())->getGeoOffers($geo_id);
        $this->response->send();
    }

    public function actionFlows()
    {
        $this->response->data = (new DataList())->getFlows();
        $this->response->send();
    }

    public function actionCurrency()
    {
        $this->response->data = (new DataList())->getCurrency();
        $this->response->send();
    }

    public function actionRole()
    {
        $this->response->data = (new DataList())->roles();
        $this->response->send();
    }

    public function actionCurrencyList()
    {
        $this->response->data = (new DataList())->getCurrencyList();
        $this->response->send();
    }

    public function actionTimeZone()
    {
        $this->response->data = (new DataList())->timeZone();
        $this->response->send();
    }

    public function actionParent()
    {
        $this->response->data = (new DataList())->getUserList();
        $this->response->send();
    }

    public function actionGeoRegions()
    {
        $geo_id = Yii::$app->request->get('geo_id');
        $this->response->data = (new DataList())->getCountryRegions($geo_id);
        $this->response->send();
    }

    public function actionAvailableGeo()
    {
        $offer_id = Yii::$app->request->post('offer_id');
        $this->response->data = (new DataList())->getOfferGeo($offer_id);
        $this->response->send();
    }

    public function actionNotificationAudio()
    {
        $this->response->data = (new NotifyService())->getListOfNotificationAudio();
        $this->response->send();
    }

    public function actionCheckoutStatus()
    {
        $this->response->data = WmCheckout::statusList();
        $this->response->send();
    }

    public function actionStatusReasons()
    {
        $this->response->data = (new DataList())->getOrderStatusReasons();
        $this->response->send();
    }

    public function actionOrderStickers()
    {
        $this->response->data = (new DataList())->getUserOrderStickers();
        $this->response->send();
    }

    public function actionDeliveryApiList()
    {
        $this->response->data = (new DataList())->getDeliveryApi();
        $this->response->send();
    }
}