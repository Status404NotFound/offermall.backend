<?php

namespace crm\modules\angular_api\controllers;

use common\helpers\FishHelper;
use common\models\log\orderSku\OrderSkuInstrument;
use common\models\offer\targets\advert\sku\TargetAdvertSku;
use common\models\order\Order;
use common\models\order\OrderSku;
use common\services\order\OrderCommonService;
use common\services\order\OrderNotFoundException;
use common\services\order\OrderSkuCommonService;
use Yii;
use yii\base\Exception;

class OrderSkuController extends BehaviorController
{
    public $modelClass = 'common\models\order\OrderSku';

    private $orderSkuService;

    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->orderSkuService = new OrderSkuCommonService();
    }

    public function actionCreate()
    {
//        $this->actionFixCommission();  // Don't remove this method

        $order_id = Yii::$app->request->post('order_id');
        $order_sku = Yii::$app->request->post('order_sku');

        if (empty($order_sku))
            throw new Exception('Sku must be filled.');

        $order = Order::findOne(['order_id' => $order_id]);
        $order->instrument = $this->setInstrument();
        (new OrderCommonService())->saveOrderSku($order, $order_sku, self::setInstrument());

        $this->response->data['total_amount'] = $order->total_amount;
        $this->response->data['total_cost'] = number_format($order->total_cost, 2);
        $this->response->data['usd_total_cost'] = number_format($order->usd_total_cost, 2);
        $this->response->send();
    }

    public function actionPartnerSku()
    {
        $order_id = Yii::$app->request->get('order_id');
        if (!$order = Order::findOne(['order_id' => $order_id]))
            throw new OrderNotFoundException('Order ' . $order_id . ' not found.');
        $this->response->data = TargetAdvertSku::findAdvertSku($order->target_advert_id);
        $this->response->send();
    }

    private function setInstrument()
    {
        $page = $this->parseReferrer();
        $instrument = 0;
        switch ($page) {
            case '/order':
                $instrument = OrderSkuInstrument::CRM_ORDERS;
                break;
            case '/delivery/waiting-for-delivery':
                $instrument = OrderSkuInstrument::CRM_WFD;
                break;
            case '/delivery/group-search-order':
                $instrument = OrderSkuInstrument::CRM_GROUP_SEARCH;
                break;
            default:
                $instrument = 0;
                break;
        }
        return $instrument;
    }

    public function actionFixCommission()
    {
//        $order_id = Yii::$app->request->post('order_id');
//        $order_sku = Yii::$app->request->post('order_sku');

        $hashes = $this->getHashes();
//        FishHelper::debug(count($hashes), 0, 1);

        $orders = Order::find()
//            ->where(['offer_id' => 2])
            ->where(['order_hash' => $hashes])
            ->andWhere(['>', 'total_amount', 0])
            ->andWhere(['deleted' => 0])
//            ->andWhere(['is not', 'total_amount', null])
//            ->limit(10)
            ->all();

//        FishHelper::debug(count($orders), 0, 1);

        $success = [];
        foreach ($orders as $order) {
            $order = Order::findOne(['order_id' => $order->order_id]);

            $order_sku = OrderSku::find()->select('sku_id, amount')
                ->where(['order_id' => $order->order_id])->asArray()->all();
            $order->instrument = $this->setInstrument();
            (new OrderCommonService())->saveOrderSku($order, $order_sku, self::setInstrument());
            $success[] = $order->order_id;
        }
        FishHelper::debug(count($success), 0, 1);
//        FishHelper::debug($success, 0, 1);
        $this->response->data['total_amount'] = $order->total_amount;
        $this->response->data['total_cost'] = number_format($order->total_cost, 2);
        $this->response->data['usd_total_cost'] = number_format($order->usd_total_cost, 2);
        $this->response->send();
    }

    private function getHashes()
    {
        // Shpolski 82 items 12.12.2017
//        return [50151306040406, 50151304598906, 50151302115006, 50151302245506, 50151306971406, 50151301403806, 50151302850906, 50151301122106, 50151301086706, 50151301063506, 50151300755806, 50151300959706, 50151300947606, 50151300933406, 50151300705706, 50151300683206, 50151300632606, 50151300583906, 50151300419306, 50151300340406, 50151300427706, 50151300055706, 50151305249606, 50151304867006, 50151302937006, 50151307865506, 50151302644706, 50151307794706, 50151300942606, 50151307601406, 50151307278906, 50151307135106, 50151305613206, 50151302296006, 50151301529806, 50151303295206, 50151307129606, 50151307099406, 50151303458006, 50151307068106, 50151302485006, 50151305222806, 50151301157206, 50151306379306, 50151306823906, 50151306860506, 50151303426706, 50151306408106, 50151305280406, 50151305498906, 50151305754406, 50151301725106, 50151306220406, 50151302027406, 50151302760606, 50151302580106, 50151301349506, 50151303153106, 50151306493606, 50151301290606, 50151301896706, 50151305553506, 50151302496806, 50151303273206, 50151302120406, 50151301559206, 50151301896506, 50151302941706, 50151305006406, 50151303910806, 50151301731206, 50151306061106, 50151302118606, 50151301619206, 50151301932406, 50151301866606, 50151302716406, 50151305976806, 50151302364006, 50151303928506, 50151303311506, 50151302083806];
        // Shpolski 82 items 12.12.2017
        return [10151254486106, 10151242609406, 10151241294706, 10151245073606, 10151241419606, 10151247024306, 10151235332106, 10151240031906, 10151246744106, 10151246747106, 10151221028306, 10151232860606, 10151237449606, 10151234002706, 10151232964706, 10151238861306, 10151238978906, 10151238976006, 10151238936906, 10151238719706, 10151238799906, 10151231801206, 10151236824506, 10151236695306, 10151238217306, 10151232437606, 10151221662706, 10151222139206, 10151221848806, 10151221936806, 10151230204906, 10151229976406, 10151221848006, 10151229958106, 10151230133106, 10151230005206, 10151229435606, 10151229783106, 10151229376806, 10151221026606, 10151221385606, 10151203855106, 20151221150506, 10151220947406, 10151220460206, 10151220407006];
    }
}