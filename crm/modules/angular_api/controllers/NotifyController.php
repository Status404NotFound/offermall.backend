<?php
namespace crm\modules\angular_api\controllers;

use Yii;
use crm\services\targets\logic\AdvertTargetDataProvider;
use crm\services\targets\AdvertTargetService;
use common\services\notify\AdvertNotifyService;

class NotifyController extends BehaviorController
{
    /**
     * @var string
     */
    public $modelClass = 'common\models\offer\targets\advert\TargetAdvertGroup';

    /**
     * @var AdvertNotifyService
     */
    private $advertNotifyService;

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
     * NotifyController constructor.
     * @param $id
     * @param $module
     * @param array $config
     */
    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->advertNotifyService = new AdvertNotifyService();
    }

    public function actionSave()
    {
        $request = Yii::$app->request->post();
        $result = $this->advertNotifyService->saveAdvertNotify($request['offer_id'], $request);
        if ($result !== true) {
            $this->response->statusCode = 500;
            $this->response->data = $this->advertNotifyService->getErrors();
        }
        $this->response->send();
    }

    public function actionView()
    {
        $offer_id = Yii::$app->request->get('offer_id');
        $this->response->data = (new AdvertTargetService())->getAdvertTargetData($offer_id, AdvertTargetDataProvider::NOTIFY_TAB);
        $this->response->send();
    }
}