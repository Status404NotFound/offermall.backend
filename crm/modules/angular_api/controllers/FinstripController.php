<?php

namespace crm\modules\angular_api\controllers;

use Yii;
use common\helpers\FishHelper;
use common\models\finance\Currency;
use common\models\finance\CurrencyRatePerDay;
use common\models\finance\KnownSubs;
use common\services\ValidateException;
use crm\models\finstrip\OfferDaySubCost;
use crm\services\finstrip\FinstripService;
use crm\services\finstrip\FinstripServiceExcepton;
use yii\base\Exception;
use yii\base\Module;
use common\models\SmsActivation;

/**
 * Class FinstripController
 * @package crm\modules\angular_api\controllers
 */
class FinstripController extends BehaviorController
{
    /**
     * @var string
     */
    public $modelClass = 'crm\models\finstrip\Finstrip';

    /**
     * @var FinstripService $fsService
     */
    private $fsService;

    /**
     * FinstripController constructor.
     * @param $id
     * @param Module $module
     * @param array $config
     */
    public function __construct($id, Module $module, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->fsService = new FinstripService();
    }

    /**
     * Calendar actions
     */
    public function actionCalendar()
    {
        $this->response->data = $this->fsService->getCalendar($this->filters);
        $this->response->send();
    }

    public function actionMonth()
    {
        $this->response->data = $this->fsService->getMonth($this->filters);
        $this->response->send();
    }

    public function actionDay()
    {
        $this->response->data = $this->fsService->getDay($this->filters);
        $this->response->send();
    }

    public function actionDayOffer()
    {
        $this->response->data = $this->fsService->getDayOffer($this->filters);
        $this->response->send();
    }

    public function actionDayOfferGeo()
    {
        $this->response->data = $this->fsService->getDayOfferGeo($this->filters);
        $this->response->send();
    }

    /**
     * Summary actions
     */

    public function actionSummaryMonth()
    {
        $this->response->data = $this->fsService->getSummaryMonth($this->filters);
        $this->response->send();
    }

    public function actionSummaryOffers()
    {
        $this->response->data = $this->fsService->getSummaryOffers($this->filters);
        $this->response->send();
    }

    public function actionSummaryOfferGeo()
    {
        $this->response->data = $this->fsService->getSummaryOffersGeo($this->filters);
        $this->response->send();
    }

    public function actionSummaryOfferDays()
    {
        $this->response->data = $this->fsService->getSummaryOfferDays($this->filters);
        $this->response->send();
    }

    public function actionSummaryOfferSub()
    {
        $this->response->data = $this->fsService->getSummaryOfferSub($this->filters);
        $this->response->send();
    }

    /**
     * Offer actions
     */
    public function actionOffers()
    {
        $this->response->data = $this->fsService->getOffers($this->filters);
        $this->response->send();
    }

    public function actionOffer()
    {
        $this->response->data = $this->fsService->getOffer($this->filters);
        $this->response->send();
    }

    public function actionOfferGeo()
    {
        $this->response->data = $this->fsService->getOfferGeo($this->filters);
        $this->response->send();
    }

    public function actionOfferMonth()
    {
        $this->response->data = $this->fsService->getOfferMonth($this->filters);
        $this->response->send();
    }

    public function actionOfferDay()
    {
        $this->response->data = $this->fsService->getOfferDay($this->filters);
        $this->response->send();
    }

    public function actionDayOfferGeoSubCost() // POST (create or update DayOfferGeoSubCost)
    {
        $this->fsService->saveDayOfferGeoSubCost(\Yii::$app->request->getBodyParam('sub'));
        $this->response->send();
    }

    public function actionGetKnownSubList()
    {
        $this->response->data = KnownSubs::find()->distinct('alias')->asArray()->all();
        $this->response->send();
    }

    public function actionFinancialPeriod()
    {
        $this->response->data = $this->fsService->getClosedPeriod();
        $this->response->send();
    }

    public function actionCloseFinancialPeriod()
    {
        $request = Yii::$app->request->post();
        $this->response->data = $this->fsService->closedPeriod($request);
        $this->response->send();
    }

    public function actionFinancialPeriodList()
    {
        $sort_field = Yii::$app->request->getBodyParam('sortField');
        $this->response->data = $this->fsService->listOfPeriods($this->filters,
            $this->getRequestPagination(),
            $sort_field,
            $this->getRequestSortOrder());
        $this->response->send();
    }

    public function actionFinancialPeriodCheck()
    {
        $this->response->data = $this->fsService->generateSmsPassword();
        $this->response->send();
    }

    public function actionFinancialPeriodVerify()
    {
        $request = Yii::$app->request->post();
        $sms_code = md5($request['code']);

        if (!SmsActivation::find()->where(['hash' => $sms_code])->andWhere(['user_id' => Yii::$app->user->identity->getId()])->exists()) {
            throw new FinstripServiceExcepton('Wrong verification code!');
        }
        $this->response->send();
    }


//    public function actionOffer()
//    {
//        $this->getRequestFilters();
//
//        $advert_id = isset($this->filters['advert_id']['value']) ? $this->filters['advert_id']['value'] : null;
//
//        $this->response->data = $this->finstripService->getOffer();
//        $this->response->send();
//    }
}