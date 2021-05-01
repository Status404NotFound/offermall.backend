<?php

namespace crm\modules\angular_api\controllers;

use Yii;
use yii\base\Module;
use common\models\order\StatusReason;
use common\models\customer\CustomerBlackList;
use common\services\customer\CustomerBlackListService;
use common\models\order\OrderStatus;

class BlacklistController extends BehaviorController
{
    /**
     * @var string
     */
    public $modelClass = 'common\models\customer\CustomerBlackList';

    /**
     * @var CustomerBlackList
     */
    public $blackListService;

    /**
     * BlacklistController constructor.
     * @param $id
     * @param Module $module
     * @param array $config
     */
    public function __construct($id, Module $module, array $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->blackListService = new CustomerBlackListService();
    }

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

    public function actionList()
    {
        $sort_field = Yii::$app->request->getBodyParam('sortField');
        $result = $this->blackListService->getBlackList($this->getRequestFilters(), $this->getRequestPagination(),
            $this->getRequestSortOrder(), $sort_field);
        $this->response->data = $result['result'];
        $this->setPaginationHeaders($result['count']['count_all']);
        $this->response->send();
    }

    public function actionAdd()
    {
        $request = Yii::$app->request->post();
        $this->response->data = $this->blackListService->saveBlackListData($request);
        $this->response->send();
    }

    public function actionStatusReason()
    {
        $status_id = Yii::$app->request->post('status_id');
        if (OrderStatus::statusNeedReason($status_id) === true) {
            $reason = StatusReason::getIndexedReasons($status_id);
        } else {
            $reason = [];
        }
        $this->response->data = $reason;
        $this->response->send();
    }

    public function actionChangeStatus()
    {
        $blacklist_id = Yii::$app->request->post('id');
        $this->response->data = $this->blackListService->changeStatus($blacklist_id);
        $this->response->send();
    }

    public function actionDeleteCustomer()
    {
        $blacklist_id = Yii::$app->request->post('id');
        $this->response->data = $this->blackListService->delete($blacklist_id);
        $this->response->send();
    }
}