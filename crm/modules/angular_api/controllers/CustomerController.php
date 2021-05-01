<?php

namespace crm\modules\angular_api\controllers;

use common\helpers\FishHelper;
use common\models\customer\Customer;
use common\models\log\orderInfo\OrderInfoInstrument;
use common\services\callcenter\call_list\CustomerService;
use common\services\customer\CustomerCommonService;
use common\services\customer\CustomerException;
use common\services\customer\CustomerNotFoundException;
use Yii;

class CustomerController extends BehaviorController
{
    public $modelClass = 'common\models\customer';
    private $customerService;

    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->customerService = new CustomerCommonService();
    }

    public function actionUpdate()
    {
        $customer_id = Yii::$app->request->getBodyParam('customer_id');
        $attributes = Yii::$app->request->getBodyParam('attributes');
        if (!$customer = Customer::findOne(['customer_id' => $customer_id]))
            throw new CustomerNotFoundException('Customer Not Found.');
        $customer->instrument = $this->setInstrument();
        $this->response->data = $this->customerService->saveCustomer($customer, $attributes);
        $this->response->send();
    }

    public function actionHistory()
    {
        $request = Yii::$app->request->post();
        $customerSrv = new CustomerService($request['customer_id']);
        $this->response->data = $customerSrv->getHistory($request['order_id']);
        $this->response->send();
    }

    private function setInstrument()
    {
        $page = $this->parseReferrer();
        $instrument = 0;
        switch ($page) {
            case '/order':
                $instrument = OrderInfoInstrument::CRM_ORDERS;
                break;
            case '/delivery/waiting-for-delivery':
                $instrument = OrderInfoInstrument::CRM_WFD;
                break;
            case '/delivery/group-search-order':
                $instrument = OrderInfoInstrument::CRM_GROUP_SEARCH;
                break;
            default:
                $instrument = 0;
                break;
        }
        return $instrument;
    }
}