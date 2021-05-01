<?php

namespace crm\modules\angular_api\controllers;

use Yii;
use common\models\order\Order;
use common\models\order\OrderStatus;
use common\models\offer\Offer;
use common\services\delivery\DeliveryCommonService;
use common\services\delivery\DeliveryException;
use common\services\ValidateException;
use common\services\delivery\BlockDeliveryDateService;
use common\services\delivery\UserRequisitesService;
use crm\services\targets\AdvertTargetService;
use crm\services\targets\logic\AdvertTargetDataProvider;
use crm\services\delivery\DeliveryStickersService;
use crm\services\export\logic\SuccessDeliveryExport;
use crm\services\sku\SkuService;
use crm\services\delivery\DeliveryFactory;
use crm\services\delivery\DeliveryService;
use yii\base\Exception;

class DeliveryController extends BehaviorController
{
    /**
     * @var string
     */
    public $modelClass = 'common\models\delivery\Delivery';

    /**
     * @var DeliveryCommonService
     */
    private $deliveryService;

    /**
     * @var BlockDeliveryDateService
     */
    private $deliveryDateService;

    /**
     * @var DeliveryStickersService
     */
    private $deliveryStickersService;

    /**
     * DeliveryController constructor.
     * @param string $id
     * @param \yii\base\Module $module
     * @param array $config
     */
    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->deliveryService = new DeliveryCommonService();
        $this->deliveryDateService = new BlockDeliveryDateService();
        $this->deliveryStickersService = new DeliveryStickersService();
    }

    public function actionSend()
    {
        $delivery_type = Yii::$app->request->post('delivery_type');
        $credentials = Yii::$app->request->post('credentials');
        $orders_id_array = Yii::$app->request->post('orders'); // []
        //$delivery_type = 'fulfillment';

        $delivery = DeliveryFactory::createDelivery($delivery_type);
        //$multiple_delivery = DeliveryFactory::checkMultipleDelivery($delivery_type);
        $deliveryService = new DeliveryService($delivery);

        $dip = [];
        $wfd = [];
        //$orders = [];
        
        //TODO multiple delivery
        
        //if ($multiple_delivery == true) {
        //    foreach ($orders_id_array as $order_id) {
        //        if (!$order = Order::findOne(['order_id' => $order_id])) {
        //            $wfd[$order_id] = 'OrderNotFound';
        //            continue;
        //        }
        //        $orders[] = $order;
        //    }
        //    $deliveryService->executeAll($orders, $credentials);
        //} else {
            foreach ($orders_id_array as $order_id) {
                try {
                    if (!$order = Order::findOne(['order_id' => $order_id])) {
                        $wfd[$order_id] = 'OrderNotFound';
                        continue;
                    }
                    //$this->deliveryService->pushToDelivery($order, $delivery_type, $credentials);
                    $deliveryService->execute($order, $credentials);
                    $dip[] = ['order_id' => $order->order_hash];
                } catch (DeliveryException $e) {
                    $wfd[] = ['order_id' => $order->order_hash, 'message' => $e->getMessage()];
                } catch (ValidateException $e) {
                    $wfd[] = ['order_id' => $order->order_hash, 'message' => $e->getMessages()];
                } catch (Exception $e) {
                    $wfd[] = ['order_id' => $order->order_hash, 'message' => $e->getMessage()];
                }
            }
        //}

        $this->response->data['dip'] = $dip;
        $this->response->data['wfd'] = $wfd;
        $this->response->send();
    }

    public function actionDeliveryCountries()
    {
        $this->response->data = $this->deliveryService->getDeliveryCountries();
        $this->response->send();
    }

    public function actionDeliveryButtons()
    {
        $this->response->data = $this->deliveryService->getDeliveryButtons();
        $this->response->send();
    }

    public function actionPartnerDeliveryDate()
    {
        $sort_field = Yii::$app->request->getBodyParam('sortField');
        $result = $this->deliveryDateService->viewDeliveryDate($this->getRequestFilters(), $this->getRequestPagination(),
            $this->getRequestSortOrder(), $sort_field);
        $this->response->data = $result['result'];
        $this->setPaginationHeaders($result['count']['count_all']);
        $this->response->send();
    }

    public function actionPartnerDeliveryDateView()
    {
        $delivery_date_id = Yii::$app->request->get('delivery_date_id');
        $this->response->data = $this->deliveryDateService->view($delivery_date_id);
        $this->response->send();
    }

    public function actionPartnerDeliveryDateSave()
    {
        $request = Yii::$app->request->getBodyParams();
        $this->response->data = $this->deliveryDateService->saveDeliveryDate($request);
        $this->response->send();
    }

    public function actionPartnerDeliveryDateDelete()
    {
        $request = Yii::$app->request->getBodyParams();
        $this->response->data = $this->deliveryDateService->delete($request);
        $this->response->send();
    }

    public function actionPartnerDeliveryDateDeletePastDays()
    {
        $this->response->data = $this->deliveryDateService->deletePastDays();
        $this->response->send();
    }

    public function actionDeliveryDate()
    {
        $order_hash_array = Yii::$app->request->post('order_hash');
        $delivery_date = Yii::$app->request->post('delivery_date');
        $success = [];
        $failed = [];
        foreach ($order_hash_array as $key => $hash) {
            if (!$order = Order::find()->where([['order_hash' => $hash]])
                ->andWhere(['order_status' => [OrderStatus::PENDING, OrderStatus::WAITING_DELIVERY]])
                ->one()) {
                $failed[] = [$hash => 'Order #' . $hash . ' Not Found or Not in status Pending or WFD.'];
            }
            $order->delivery_date = $delivery_date;
            if (!$order->save()) throw new ValidateException($order->errors);
            $success[] = $hash;
        }
        $this->response->data['success'] = $success;
        $this->response->data['failed'] = $failed;
        $this->response->send();
    }

    public function actionCountSuccessDelivery()
    {
        $successDeliveryExport = new SuccessDeliveryExport();
        $this->response->data = $successDeliveryExport->countOrderByFilters($this->getRequestFilters());
        $this->response->send();
    }

    public function actionCompanyInfo()
    {
        $advert_id = Yii::$app->request->get('requisite_id');
        $requisites = new UserRequisitesService();
        if ($advert_id) {
            $this->response->data = $requisites->getList($advert_id);
        } else {
            $this->response->data = $requisites->getList();
        }
        $this->response->send();
    }

    public function actionSaveCompanyInfo()
    {
        $request = Yii::$app->request->getBodyParams();
        $requisites = new UserRequisitesService();
        $this->response->data = $requisites->save($request);
        $this->response->send();
    }

    public function actionDeleteCompanyInfo()
    {
        $request = Yii::$app->request->get();
        $requisites = new UserRequisitesService();
        $this->response->data = $requisites->delete($request);
        $this->response->send();
    }

    public function actionBlockSku()
    {
        $offer_id = Yii::$app->request->get('offer_id');
        $product_id_array = Offer::findProductIds($offer_id);
        $targets = (new AdvertTargetService())->getAdvertTargetData($offer_id, AdvertTargetDataProvider::BLOCK_SKU_TAB);
        $sku_list = empty($targets) ? [] : (new SkuService())->getSkuList($product_id_array);
        $this->response->data = [
            'targets' => $targets,
            'sku_list' => $sku_list,
        ];
        $this->response->send();
    }

    public function actionAddStickers()
    {
        $orders_array = Yii::$app->request->post('order_id');
        $stickers_array = Yii::$app->request->post('sticker_id');

        $orders = Order::find()->where(['in', 'order_id', $orders_array])->all();
        foreach ($orders as $order){
            $this->deliveryStickersService->saveOrderStickers($order->order_id, $stickers_array);
        }
    }

    public function actionDeleteStickers()
    {
        $orders_array = Yii::$app->request->post('order_id');
        $stickers_array = Yii::$app->request->post('sticker_id');
        $this->response->data = $this->deliveryStickersService->deleteOrderStickers($orders_array, $stickers_array);
        $this->response->send();
    }
}