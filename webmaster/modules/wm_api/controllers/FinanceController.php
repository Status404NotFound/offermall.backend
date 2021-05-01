<?php

namespace webmaster\modules\wm_api\controllers;

use webmaster\models\finance\Finance;
use Yii;
use webmaster\services\finance\FinanceService;
use webmaster\services\finance\FinanceSearchFactory;
use webmaster\services\finance\FinanceServiceException;
use webmaster\services\profile\ProfileService;
use webmaster\services\notification\TelegramNotification;

/**
 * Class FinanceController
 * @package webmaster\modules\wm_api\controllers
 */
class FinanceController extends BehaviorController
{
    /**
     * @var string
     */
    public $modelClass = 'common\models\order\Order';

    /**
     * @var FinanceService
     */
    private $financeService;

    /**
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs']['actions'] = array_merge($behaviors['verbs']['actions'], [
            'translate' => ['get']
        ]);
        $behaviors['authenticator']['except'] = array_merge($behaviors['authenticator']['except'], [
            'payment-order'
        ]);
        return $behaviors;
    }

    /**
     * FinanceController constructor.
     * @param $id
     * @param $module
     * @param array $config
     */
    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->financeService = new FinanceService();
    }

    public function actionFinanceData()
    {
        $this->response->data = $this->financeService->getFinanceData();
        $this->response->send();
    }

    public function actionHistoryData()
    {
        $sort_field = Yii::$app->request->getBodyParam('sortField');
        $orders = (new FinanceSearchFactory())->createFinanceSearch('finance');
        $finance = $orders->searchLeads($this->getRequestFilters(), $this->getRequestPagination(), $this->getRequestSortOrder(), $sort_field);
        $total = $this->financeService->getTotalRow($finance['totals'], ['date', 'wm_commission', 'offer_name', 'flow_id', 'target_wm_id', 'hold', 'commission']);

        if (!empty($total)) {
            $result = [
                'table_data' => $finance['finance_list'],
                'total' => [
                    'pcs' => $total['pcs'],
                    'leads' => $total['leads'],
                    'money' => $total['total'] . ' $',
                ]
            ];
        } else {
            $result = [
                'table_data' => $finance['finance_list'],
                'total' => []
            ];
        }

        $this->response->data = $result;
        $this->setPaginationHeaders($finance['count']['count_all']);
        $this->response->send();
    }

    public function actionHold()
    {
        $sort_field = Yii::$app->request->getBodyParam('sortField');
        $orders = (new FinanceSearchFactory())->createFinanceSearch('hold');
        $hold = $orders->searchLeads($this->getRequestFilters(), $this->getRequestPagination(), $this->getRequestSortOrder(), $sort_field);
        $this->response->data = $hold['hold'];
        $this->setPaginationHeaders($hold['count']['count_all']);
        $this->response->send();
    }

    public function actionTranslate()
    {
        $response = Yii::$app->response;

        $response->data = Finance::getHoldBalance();
        $response->send();
    }

    public function actionBalance()
    {
        $balance = Finance::getCurrentBalance();
        $hold = Finance::getHoldBalance();

        $this->response->data = [
            'balance' => $balance,
            'hold_balance' => $hold
        ];
        $this->response->send();
    }

    public function actionCheck()
    {
        $result = (new ProfileService())->getWebmasterProfileData();
        if (!$result['card']) {
            throw new FinanceServiceException('Oops, You don\'t entered requisites in a profile! Please go to profile and enter requisites!');
        }
        $this->response->data = $result;
        $this->response->send();
    }

    public function actionPayment()
    {
        $request = Yii::$app->request->post();
        $this->financeService->requestPay($request);

        $telegram = new TelegramNotification();
        $telegram->sendNewPaymentOrder($request);

        $this->response->data = $request;
        $this->response->send();
    }

    public function actionPaymentOrder()
    {
        $paymentOrders = $this->financeService->getAllProcessingOrders();
        $this->response->data = $paymentOrders;
        $this->response->send();
    }
}