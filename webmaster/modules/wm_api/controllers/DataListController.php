<?php

namespace webmaster\modules\wm_api\controllers;

use Yii;
use common\models\geo\Geo;
use webmaster\models\DataList;
use common\models\order\OrderStatus;
use yii\base\Module;

class DataListController extends BehaviorController
{
    /**
     * @var string
     */
    public $modelClass = 'webmaster\models\DataList';

    /**
     * @var DataList
     */
    public $dataList;

    /**
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs']['actions'] = array_merge($behaviors['verbs']['actions'], [
            'flow-geo' => ['get'],
        ]);
        return $behaviors;
    }

    /**
     * DataListController constructor.
     * @param string $id
     * @param Module $module
     * @param array $config
     */
    public function __construct($id, Module $module, array $config = [])
    {
        $this->dataList = new DataList();
        parent::__construct($id, $module, $config);
    }

    public function actionWebmasterList()
    {
        $this->response->data = $this->dataList->getWebmasterList();
        $this->response->send();
    }

    public function actionGeo()
    {
//        $this->response->data = $this->dataList->getWebmasterGeo();
        $this->response->data = $this->dataList->getWebmasterGeoWithParent();
        $this->response->send();
    }

    public function actionGeoList()
    {
        $this->response->data = Geo::list();
        $this->response->send();
    }

    public function actionFlowGeo()
    {
        $flow_id = Yii::$app->request->get('flow_id');
        $this->response->data = $this->dataList->getWebmasterFlowGeo($flow_id);
        $this->response->send();
    }

    public function actionOffers()
    {
//        $this->response->data = $this->dataList->offerList();
        $this->response->data = $this->dataList->getWmOffersWithParent();
        $this->response->send();
    }

    public function actionAdverts()
    {
        $this->response->data = $this->dataList->getOfferAdverts();
        $this->response->send();
    }

    public function actionMyOffers()
    {
        $this->response->data = $this->dataList->getWmOffers();
        $this->response->send();
    }

    public function actionOffersList()
    {
        $wm_id = Yii::$app->request->get('wm_id');
        $this->response->data = $this->dataList->getWmOffers($wm_id);
        $this->response->send();
    }

    public function actionFlows()
    {
        $this->response->data = $this->dataList->getFlows(Yii::$app->user->identity->getId());
        $this->response->send();
    }

    public function actionWmOfferFlows()
    {
        $request = Yii::$app->request->getBodyParams();
        $this->response->data = $this->dataList->getWmOfferFlows($request);
        $this->response->send();
    }

    public function actionTargets()
    {
        $this->response->data = $this->dataList->getWmStatuses();
        $this->response->send();
    }

    public function actionStatusList()
    {
        $this->response->data = OrderStatus::getWmFilterStatuses();
        $this->response->send();
    }

    public function actionTimezoneList()
    {
        $this->response->data = $this->dataList->timeZoneList();
        $this->response->send();
    }

    public function actionAvatar()
    {
        $this->response->data = $this->dataList->getUserAvatar();
        $this->response->send();
    }
}