<?php
namespace webmaster\modules\wm_api\controllers;

use Yii;
use webmaster\services\order\OrdersService;

class OrdersController extends BehaviorController
{
    /**
     * @var string
     */
    public $modelClass = 'common\models\order\Order';

    /**
     * @var OrdersService
     */
    private $orderService;

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
     * OrdersController constructor.
     * @param string $id
     * @param \yii\base\Module $module
     * @param array $config
     */
    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->orderService = new OrdersService();
    }

    public function actionMyOrders()
    {
        $sort_field = Yii::$app->request->getBodyParam('sortField');
        $orders = $this->orderService->getMyOrders($this->getRequestFilters(), $this->getRequestPagination(), $this->getRequestSortOrder(), $sort_field);
        $this->response->data = [
            'orders' => $orders['orders']
        ];
        $this->setPaginationHeaders($orders['count']['count_all']);
        $this->response->send();
    }
}