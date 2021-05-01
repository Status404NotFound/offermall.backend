<?php

namespace crm\modules\angular_api\controllers;

use crm\services\statistic\LiveSrv;
use Yii;
use common\services\statistics\HourlyStatisticsService;
use crm\services\statistic\OfferAllStatisticsService;
use crm\services\statistic\OfferDailyStatisticsService;
use crm\services\statistic\OfferWmStatistics;
use crm\services\statistic\OfferAdvertStatistics;
use crm\services\statistic\AdditionalStatisticsService;
use crm\services\statistic\GeoStatisticsService;
use crm\services\statistic\RejectStatisticsService;
use crm\services\statistic\OperatorRating;
use crm\services\statistic\DeliverySkuStatisticsService;

class StatisticsController extends BehaviorController
{
    /**
     * @var string
     */
    public $modelClass = 'common\models\order\Order';

    /**
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs']['actions'] = array_merge($behaviors['verbs']['actions'], []);
        return $behaviors;
    }

    public function actionOffer()
    {
        $request = Yii::$app->request->post();
        $sort_field = Yii::$app->request->getBodyParam('sortField');
        $sort_order = Yii::$app->request->getBodyParam('sortOrder');
        $statistics = new OfferAllStatisticsService($request, $sort_field, $sort_order);
        $this->response->data = [
            'statistics' => $statistics->offers,
            'total' => $statistics->total,
        ];
        $this->response->send();
    }

    public function actionHourly()
    {
        $request = Yii::$app->request->post();
        $statistics = new HourlyStatisticsService($request['filters']);
        $this->response->data = [
            'statistics' => $statistics->hours,
            'total' => $statistics->total,
        ];
        $this->response->send();
    }

    public function actionDaily()
    {
        $request = Yii::$app->request->post();
        $statistics = new OfferDailyStatisticsService(isset($request['filters']) ? $request['filters'] : []);
        $this->response->data = [
            'statistics' => $statistics->offers,
            'total' => $statistics->total,
            'hourly_rate' => $statistics->hourly,
        ];
        $this->response->send();
    }

    public function actionGeo()
    {
        $request = Yii::$app->request->post();
        $sort_field = Yii::$app->request->getBodyParam('sortField');
        $sort_order = Yii::$app->request->getBodyParam('sortOrder');
        $statistics = new GeoStatisticsService($request, $sort_field, $sort_order);
        $this->response->data = [
            'statistics' => $statistics->geo,
            'total' => $statistics->total
        ];
        $this->response->send();
    }

    public function actionWm()
    {
        $request = Yii::$app->request->post();
        $statistics = new OfferWmStatistics($request['filters']);
        $this->response->data = [
            'statistics' => $statistics->offers,
            'total' => $statistics->total,
        ];
        $this->response->send();
    }

    public function actionAdvert()
    {
        $request = Yii::$app->request->post();
        $statistics = new OfferAdvertStatistics($request['filters']);
        $this->response->data = [
            'statistics' => $statistics->offers,
            'total' => $statistics->total,
        ];
        $this->response->send();
    }

    public function actionReject()
    {
        $request = Yii::$app->request->post();
        $list = new RejectStatisticsService($request['filters']);

        $this->response->data = [
            'list' => array_values([$list->list]),
            'valid_reject' => $list->valid['valid'],
            'not_valid_reject' => $list->valid['not_valid'],
            'total_valid' => array_values([$list->valid['total']['valid_reject']]),
            'total_not_valid' => array_values([$list->valid['total']['not_valid_reject']]),
            'operators' => (new OperatorRating($request['filters']))->getOperatorsTodayStatistics(),
        ];
        $this->response->send();
    }

    public function actionBrowser()
    {
        $statistics = new AdditionalStatisticsService();
        $this->response->data = $statistics->getBrowserStatistics();
        $this->response->send();
    }

    public function actionOs()
    {
        $statistics = new AdditionalStatisticsService();
        $this->response->data = $statistics->getOsStatistics();
        $this->response->send();
    }

    public function actionCountries()
    {
        $statistics = new AdditionalStatisticsService();
        $this->response->data = $statistics->getCountriesStatistics();
        $this->response->send();
    }

    public function actionAutolead()
    {
        $request = Yii::$app->request->post();
        $statistics = new AdditionalStatisticsService();
        $this->response->data = $statistics->calculateAutoleads($request);
        $this->response->send();
    }

    public function actionDeliverySku()
    {
        $statistics = new DeliverySkuStatisticsService();
        $delivery_sku = $statistics->deliverySku($this->getRequestFilters(),
            $this->getRequestPagination(),
            $this->getRequestSortOrder());
        $this->response->data = $delivery_sku['result'];
        $this->setPaginationHeaders($delivery_sku['count']['count_all']);
        $this->response->send();
    }

    public function actionLiveOffer()
    {
        $data = Yii::$app->request->post();
        $srv = new LiveSrv($data);
        $this->response->data = $srv->offer();
        $this->response->send();
    }

    public function actionLiveSuperWm()
    {
        $data = Yii::$app->request->post();
        $srv = new LiveSrv($data);
        $this->response->data = $srv->superWm();
        $this->response->send();
    }

    public function actionLiveAdvert()
    {
        $data = Yii::$app->request->post();
        $srv = new LiveSrv($data);
        $this->response->data = $srv->advert();
        $this->response->send();
    }

    public function actionLiveGeo()
    {
        $data = Yii::$app->request->post();
        $srv = new LiveSrv($data);
        $this->response->data = $srv->geo();
        $this->response->send();
    }
}