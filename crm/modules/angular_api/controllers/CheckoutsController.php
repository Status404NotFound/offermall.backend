<?php

namespace crm\modules\angular_api\controllers;

use Yii;
use yii\base\Module;
use crm\services\webmaster\WmCheckoutService;
use common\models\webmaster\WmCheckout;

class CheckoutsController extends BehaviorController
{
    /**
     * @var string
     */
    public $modelClass = 'common\models\webmaster\WmCheckout';

    /**
     * @var WmCheckoutService
     */
    private $wmCheckoutsService;

    /**
     * CheckoutsController constructor.
     * @param string $id
     * @param Module $module
     * @param array $config
     */
    public function __construct($id, Module $module, array $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->wmCheckoutsService = new WmCheckoutService();
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

    public function actionWmCheckoutsList()
    {
        $sort_field = Yii::$app->request->getBodyParam('sortField');
        $sort_order = Yii::$app->request->getBodyParam('sortOrder');
        $list = $this->wmCheckoutsService->getWmCheckoutsList($this->filters, $this->getRequestPagination(), $sort_field, $sort_order);
        $this->response->data = $list['checkouts'];
        $this->setPaginationHeaders($list['count']['count_all']);
        $this->response->send();
    }

    public function actionWmCheckoutsHistory()
    {
        $sort_field = Yii::$app->request->getBodyParam('sortField');
        $sort_order = Yii::$app->request->getBodyParam('sortOrder');
        $history = $this->wmCheckoutsService->getWmCheckoutsHistory($this->filters, $this->getRequestPagination(), $sort_field, $sort_order);
        $this->response->data = $history['checkouts_history'];
        $this->setPaginationHeaders($history['count']['count_all']);
        $this->response->send();
    }

    public function actionWmCheckoutsConfirm($id)
    {
        $status = WmCheckout::PAID_OUT;
        $this->response->data = $this->wmCheckoutsService->changeStatus($id, $status);
        $this->response->send();
    }

    public function actionWmCheckoutsReject($id)
    {
        $request = Yii::$app->request->getBodyParams();
        $status = WmCheckout::REJECTED;
        $this->response->data = $this->wmCheckoutsService->changeStatus($id, $status, $request);
        $this->response->send();
    }
}