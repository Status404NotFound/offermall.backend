<?php

namespace crm\modules\angular_api\controllers;

use Yii;
use common\helpers\FishHelper;
use common\models\log\orderInfo\OrderInfoInstrument;
use common\models\order\Order;
use common\models\order\StatusReason;
use common\services\order\logic\status\ChangeStatusException;
use common\services\order\OrderCommonService;
use common\services\order\OrderNotFoundException;
use common\services\ValidateException;
use crm\services\delivery\DeliveryStickersService;
use crm\models\delivery\DeliveryStickers;
use yii\base\Exception;

class OrderStatusController extends BehaviorController
{
    /**
     * @var string
     */
    public $modelClass = 'common\models\order\OrderStatus';

    /**
     * @var OrderCommonService
     */
    private $orderService;

    /**
     * @var DeliveryStickersService
     */
    private $deliveryStickerService;

    /**
     * OrderStatusController constructor.
     * @param $id
     * @param $module
     * @param array $config
     */
    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->orderService = new OrderCommonService();
        $this->deliveryStickerService = new DeliveryStickersService();
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs']['actions'] = array_merge($behaviors['verbs']['actions'], [
            'change' => ['put'],
            'reject-reason' => ['get'],
        ]);
        return $behaviors;
    }

    public function actionChange()
    {
        $params = Yii::$app->request->getBodyParams();
        $order_status = $params['order_status'];
//        $order_status = 50;
//        $order_hash_array = $params['order_hash'];
        $order_id_array = $params['order_id'];
//        $order_hash_array = $this->getOrders();

        unset($params['order_status']);
//        unset($params['order_hash']);
        unset($params['order_id']);

        $this->response->data['success'] = [];
        $this->response->data['failed'] = [];
        foreach ($order_id_array as $key => $order_id) {
            try {
//                if (!$order = Order::findOne(['order_hash' => $hash, 'deleted' => 0]))
                if (!$order = Order::findOne(['order_id' => $order_id, 'deleted' => 0]))
                    throw new OrderNotFoundException('Order is not found.');
                $order->instrument = $this->setInstrument();
                $this->orderService->changeStatus($order, $order_status, $params);

                if (!empty($params['fetcher_id'])) {
                    $sticker = DeliveryStickers::findOne(['sticker_id' => $params['fetcher_id']]);
                    $this->deliveryStickerService->saveOrderStickers($order->order_id, [$sticker->sticker_id]);
                }
            } catch (ChangeStatusException $e) {
                $this->response->data['failed'][$order->order_hash] = $e->getMessage();
                continue;
            } catch (ValidateException $e) {
                $this->response->data['failed'][$order->order_hash] = $e->getMessages();
                continue;
            } catch (Exception $e) {
                $this->response->data['failed'][$order->order_hash] = $e->getMessage();
                continue;
            }
            $this->response->data['success'][] = $order->order_hash;
        }
        $this->response->send();
    }

    public function actionRejectReason()
    {
        $order_status = Yii::$app->request->get('status_id');
        $this->response->data = StatusReason::getIndexedReasons($order_status);
        $this->response->send();
    }

    public function actionRejectReasons()
    {
        $arr = [];
        foreach (StatusReason::statusReasons() as $status => $reason_array) {
            $arr[$status] = array_values($reason_array);
        }
        $this->response->data = $arr;
        $this->response->send();
    }

    public function getOrders()
    {
        /** DON'T DELETE COMMENTED ROWS. It's manual status changes. */
        /** 26.10.2017 From Shpolskiy. 42 orders to DIP */
//        $order_hash = $shpolskiy_group_search_26_10_2017 = ['10150875082301', '10150877571001', '10150878248801', '10150878519101', '10150879049801', '10150879515401', '10150880275501', '10150881214201', '10150882938401', '10150883372301', '10150883488501', '10150884129701', '10150884201801', '10150884265901', '10150884285401', '10150884327301', '10150884401101', '10150884441501', '10150884515901', '10150884552201', '10150884720201', '10150884986701', '10150885366601', '10150885445501', '10150885780601', '10150885943701', '10150886788901', '10150887707201', '10150888262501', '10150888995301', '10150889885501', '10150889886601', '10150889967501', '10150890030201', '10150890350801', '10150890789601', '10150891352001', '10150891522801', '10150891722801', '10150891807501', '10150891888101', '10150891890001'];
//        $order_hash = $shpolskiy_group_search_26_10_2017_second_party = ['10150790131401', '10150875104101', '10150877932901', '10150880733401', '10150880845601', '10150881581501', '10150882246401', '10150883715501', '10150883727501', '10150884189401', '10150887081101', '10150890201501', '10150893203401', '10150893207301', '10150893649701', '10150893673501', '10150893841901', '10150893872401', '10150894228301', '10150894254301', '10150894873001', '10150895295601', '10150895553201', '10150895861601', '10150895886901', '10150895921101', '10150896040101', '10150896048401', '10150896331301', '10150896432601', '10150896707501', '10150897011801', '10150897060001', '10150898377801', '10150898555501', '10150898563001', '10150898731401', '10150898875301', '10150898992601', '10150899043201', '10150899070901', '10150899101601', '10150899097801', '10150899267601', '10150900692801', '10150901493801', '10150901576201'];
        /** 29.10.2017 From Vetaska. 65 orders to DIP */
//        $order_hash = $vetaska_29_10_2017 = [10150878247901, 10150883862701, 10150893999601, 10150895099001, 10150896720501, 10150902419601, 10150902450201, 10150902512101, 10150902618901, 10150902762001, 10150902782301, 10150902786301, 10150902819401, 10150902934401, 10150903232501, 10150903223701, 10150904556901, 10150904801801, 10150905404301, 10150908259601, 10150910875601, 10150911058201, 10150911181001, 10150911198701, 10150911344601, 10150911350701, 10150911580701, 10150913119001, 10150913363601, 10150914583201, 10150917020601, 10150918784101, 10150918792901, 10150918825401, 10150918872601, 10150918993001, 10150919296001, 10150919600901, 10150919664201, 10150919674901, 10150920181101, 10150920351201, 10150920563101, 10150920637701, 10150921087201, 10150921554001, 10150921908001, 10150922017001, 10150922519201, 10150922601001, 10150923179301, 10150923225201, 10150924287901, 10150924449101, 10150924659401, 10150925010901, 10150925066601, 10150925106201, 10150925164901, 10150925531701, 10150925636801, 10150926323001, 10150926813201, 10150927184701, 10150927252901,];
        /** 30.10.2017 From Vetaska. 41 orders to DIP */
//        $order_hash = $vetaska_30_10_2017 = [10150877053401, 10150881577501, 10150908882801, 10150916577201, 10150920901701, 10150921224201, 10150921386801, 10150921453901, 10150921748901, 10150922183301, 10150924563801, 10150924691601, 10150924706101, 10150925392301, 10150927469701, 10150927561401, 10150927648201, 10150927842401, 10150928159301, 10150928309401, 10150928422701, 10150928614701, 10150928847701, 10150928858201, 10150929406001, 10150929855301, 10150929962201, 10150930483101, 10150930596601, 10150930750801, 10150931585001, 10150932256001, 10150933425401, 10150933507801, 10150933615601, 10150934285501, 10150935425201, 10150935576001, 10150935798801, 10150936110501, 10150936255401];
//        return $order_hash;

        /** DON'T DELETE COMMENTED ROWS. It's manual status changes. */
//        /* FROM Not paid to Success Delivery */
//        $order_hash = $vetaska_18_06_2018 = [28015176760342];
//        /* FROM Success Delivery to Returned */
//        $order_hash = $vetaska_18_06_2018 = [3101522100804, 201521618388, 3401520918361, 3401520250616, 3401519623051, 3401520265699, 11101522464710, 2601519466631, 1401522271444,
//        ];

//        /** 19.06.2018 From Vetaska. 1 order from Not paid to Success Delivery */
//        $order_hash = $vetaska_19_06_2018 = [2701521829383];
        /** 19.06.2018 From Vetaska. 3 order from Delivery in progress to Success Delivery */
//        $order_hash = $vetaska_19_06_2018 = [7001525109977, 7001525058176, 401524934544];

        /** 20.06.2018 From Vetaska */
//        $order_hash = $vetaska_20_06_2018 = [101524922570, 101525012253]; // From Delivery in progress to Success Delivery

        /** 22.06.2018 From Vetaska */
//        $order_hash = $vetaska_22_06_2018 = [501521215179, 3101519828929, 2701521797236, 3101524839565, 3901524665056, 2801523106050, 2801522866486, 401525041632, 501524715585, 501524674385, 1001524322108, 9501524226066, 401524967425, 501524663499, 901524822351];
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
            case '/delivery/deliveries':
                $instrument = OrderInfoInstrument::CRM_DELIVERY;
                break;
            default:
                $instrument = 0;
                break;
        }
        return $instrument;
    }
}