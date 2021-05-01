<?php

namespace webmaster\modules\wm_api\controllers;

use Yii;
use common\models\landing\Landing;
use common\models\offer\OfferTransit;
use webmaster\services\flow\FlowDataProvider;
use webmaster\services\flow\FlowDataSave;
use webmaster\models\DataList;

class FlowController extends BehaviorController
{
    /**
     * @var string
     */
    public $modelClass = 'common\models\flow\Flow';

    /**
     * @var FlowDataProvider
     */
    private $flowDataProvider;

    /**
     * @var FlowDataSave
     */
    private $flowDataSave;

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

    /**
     * FlowController constructor.
     * @param string $id
     * @param \yii\base\Module $module
     * @param array $config
     */
    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->flowDataProvider = new FlowDataProvider();
        $this->flowDataSave = new FlowDataSave();

    }

    public function actionFlowList()
    {
        $sort_field = Yii::$app->request->getBodyParam('sortField');
        $flows = $this->flowDataProvider->getWebmasterFlowList($this->getRequestFilters(), $this->getRequestPagination(),
            $this->getRequestSortOrder(), $sort_field);
        $this->response->data = $flows['flows'];
        $this->setPaginationHeaders($flows['count']['count_all']);
        $this->response->send();
    }

    public function actionCreate()
    {
        $request = Yii::$app->request->post();
        $this->response->data = $this->flowDataSave->createFlow($request);
        $this->response->send();
    }

    public function actionCreateOffers()
    {
        $this->response->data = $this->flowDataProvider->getOfferWmList();
        $this->response->send();
    }

    public function actionOfferLandings()
    {
        $offer_id = Yii::$app->request->get('offer_id');
        $this->response->data = Landing::getOfferLandings($offer_id);
        $this->response->send();
    }

    public function actionOfferTransits()
    {
        $offer_id = Yii::$app->request->get('offer_id');
        $this->response->data = OfferTransit::getOfferTransits($offer_id);
        $this->response->send();
    }

    public function actionTarget()
    {
        $offer_id = Yii::$app->request->post('offer_id');
        $wm_id = Yii::$app->request->post('wm_id');
        $this->response->data = $this->flowDataProvider->getOfferTargets($offer_id, $wm_id);
        $this->response->send();
    }

    public function actionView()
    {
        $flow_id = Yii::$app->request->get('flow_id');
        $this->response->data = $this->flowDataProvider->getFlow($flow_id);
        $this->response->send();
    }

    public function actionEdit()
    {
        $request = Yii::$app->request->getBodyParams();
        $this->response->data = $this->flowDataSave->update($request);
        $this->response->send();
    }

    public function actionFlowUrl()
    {
        $flow_id = Yii::$app->request->get('flow_id');
        $link = $this->flowDataProvider->getLink($flow_id);
        $this->response->data = [
            'offer_name' => $link['offer_name'],
            'flow_name' => $link['flow_name'],
            'flow_key' => $link['flow_key'],
            'url' => $link['url'],
        ];
        $this->response->send();
    }

    public function actionLandingsList()
    {
        $this->response->data = DataList::getWmLandings();
        $this->response->send();
    }

    public function actionDelete()
    {
        $flow_id = Yii::$app->request->get('flow_id');
        $this->response->data = $this->flowDataSave->delete($flow_id);
        $this->response->send();
    }
}