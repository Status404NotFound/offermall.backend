<?php

namespace webmaster\modules\wm_api\controllers;

use Yii;
use webmaster\models\statistics\Daily;
use webmaster\models\statistics\Hourly;
use webmaster\models\statistics\Offer;
use webmaster\models\statistics\Flow;
use webmaster\models\statistics\Sub;
use webmaster\services\statistics\DeliverySkuService;
use webmaster\services\statistics\LiveStatisticsService;

/**
 * Class StatisticsController
 * @package webmaster\modules\wm_api\controllers
 */
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
        $behaviors['verbs']['actions'] = array_merge($behaviors['verbs']['actions'], [
        ]);
        return $behaviors;
    }

    public function actionHourly()
    {
        $post = Yii::$app->request->post();
        $statistics = new Hourly();
        $hourly = $statistics->searchHourly($post['filters']);
        $this->response->data = [
            'statistics' => $hourly,
            'total' => $statistics->getTotalRow($hourly, ['index_date']),
        ];
        $this->response->send();
    }

    public function actionDaily()
    {
        $post = Yii::$app->request->post();
        $statistics = new Daily();
        $daily = $statistics->searchDaily($post['filters']);
        $this->response->data = [
            'statistics' => $daily,
            'total' => $statistics->getTotalRow($daily, ['index_date']),
        ];
        $this->response->send();
    }

    public function actionFlows()
    {
        $post = Yii::$app->request->post();
        $statistics = new Flow();
        $flow = $statistics->searchFlow($post['filters']);
        $this->response->data = [
            'statistics' => $flow,
            'total' => $statistics->getTotalRow($flow, ['flow_id', 'flow_name']),
        ];
        $this->response->send();
    }

    public function actionOffers()
    {
        $post = Yii::$app->request->post();
        $statistics = new Offer();
        $offers = $statistics->searchOffer($post['filters']);
        $this->response->data = [
            'statistics' => $offers,
            'total' => $statistics->getTotalRow($offers, ['offer_id', 'offer_name']),
        ];
        $this->response->send();
    }

    public function actionSub()
    {
        $post = Yii::$app->request->post();
        $statistics = new Sub();
        $sub = $statistics->searchSub($post['filter']);
        $this->response->data = [
            'statistics' => $sub,
            'total' => $statistics->getTotalRow($sub, ['sub_id_1', 'sub_id_2', 'sub_id_3', 'sub_id_4', 'sub_id_5']),
        ];
        $this->response->send();
    }

    public function actionDeliverySku()
    {
        $statistics = new DeliverySkuService();
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
        $srv = new LiveStatisticsService($data);
        $this->response->data = $srv->offer();
        $this->response->send();
    }

    public function actionLiveSuperWm()
    {
        $data = Yii::$app->request->post();
        $srv = new LiveStatisticsService($data);
        $this->response->data = $srv->superWm();
        $this->response->send();
    }

    public function actionLiveAdvert()
    {
        $data = Yii::$app->request->post();
        $srv = new LiveStatisticsService($data);
        $this->response->data = $srv->advert();
        $this->response->send();
    }

    public function actionLiveGeo()
    {
        $data = Yii::$app->request->post();
        $srv = new LiveStatisticsService($data);
        $this->response->data = $srv->geo();
        $this->response->send();
    }
}