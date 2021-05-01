<?php

namespace crm\modules\angular_api\controllers;

use common\helpers\FishHelper;
use common\models\geo\Geo;
use common\models\order\OrderStatus;
use common\modules\user\models\tables\User;
use crm\services\targets\logic\AdvertTargetDataProvider;
use crm\services\targets\AdvertTargetService;
use crm\services\targets\WmTargetService;
use Yii;

class WmTargetController extends BehaviorController
{
    public $modelClass = 'crm\modules\angular_api\targets\WmOfferTarget';

    private $wmTargetService;

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs']['actions'] = array_merge($behaviors['verbs']['actions'], [
            'status-list' => ['post'],
            'advert-target-status-list' => ['get'],
            'target-geo-list' => ['post'],
            'wm-list' => ['get'],
        ]);

        return $behaviors;
    }

    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->wmTargetService = new WmTargetService();
    }

    public function actionCreate()
    {
        $request = Yii::$app->request->post();
        $result = $this->wmTargetService->saveWmTargets($request['offer_id'], $request['targets']);
        if ($result !== true) {
            $this->response->statusCode = 500;
            $this->response->data = $this->wmTargetService->getErrors();
        }
        $this->response->send();
    }

    public function actionView()
    {
        $offer_id = (integer)Yii::$app->request->get('offer_id');
        $this->response->data = [
            'advert_offer_targets' => (new AdvertTargetService())->getAdvertTargetData($offer_id, AdvertTargetDataProvider::WM_TAB),
            'wm_offer_targets' => $this->wmTargetService->getResponse($offer_id),
        ];
        $this->response->send();
    }

    public function actionStatusList()
    {
        $this->response->data = OrderStatus::getStatuses();
        $this->response->send();
    }

    public function actionTargetGeoList()
    {
        $this->response->data = Geo::list();
        $this->response->send();
    }

    public function actionWmList()
    {

        $this->response->data = $this->response->data = User::find()
            ->select(['id', 'username'])
            ->where(['role' => User::ROLE_WEBMASTER])
            ->asArray()->all();
        $this->response->send();
    }
}