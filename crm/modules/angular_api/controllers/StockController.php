<?php

namespace crm\modules\angular_api\controllers;

use common\models\geo\Geo;
use common\models\stock\Stock;
use common\services\stock\StockService;
use Yii;

class StockController extends BehaviorController
{
    public $modelClass = 'common\models\stock\Stock';
    private $stockService;

    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->stockService = new StockService();
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs']['actions'] = array_merge($behaviors['verbs']['actions'], [
            'geo-list' => ['post'],
            'stock-list' => ['post'],
            'add-sku' => ['post'],
            'move-sku' => ['post'],
            'stocks' => ['post'],
        ]);
        return $behaviors;
    }

    public function actionIndex()
    {

    }

    public function actionStocks()
    {
        $pagination = [];
        $pagination['first_row'] = Yii::$app->request->getBodyParam('first_row');
        $pagination['rows'] = Yii::$app->request->getBodyParam('rows');
        $stocks = $this->stockService->findActive(!empty($pagination) ? $pagination : null);
        $this->response->data = $stocks['stocks'];
        $this->response->headers->add('Access-Control-Expose-Headers', 'X-Pagination-Total-Count');
        $this->response->headers->add('X-Pagination-Total-Count', $stocks['count']);
        $this->response->send();
    }

    public function actionCreate()
    {
        $request = Yii::$app->request->post();
        $this->response->data = $this->stockService->saveStock($request);
        $this->response->send();
    }

    public function actionView()
    {
        $stock_id = Yii::$app->request->get('stock_id');
        $stock_sku = Yii::$app->request->get('stock_sku');
        $this->response->data = $this->stockService->getStockById($stock_id, $stock_sku);
        $this->response->send();
    }

    public function actionAddSku()
    {
        $request = Yii::$app->request->getBodyParams();
        $result = $this->stockService->addSku($request['stock_id'], null, $request['stock_sku']);
        if ($result !== true) {
            $this->response->statusCode = 500;
            $this->response->data = $this->stockService->getErrors();
        }
        $this->response->send();
    }

    public function actionMoveSku()
    {
        $request = Yii::$app->request->getBodyParams();
        $result = $this->stockService->moveSku($request['stock_id'], $request['stock_id_to'], $request['sku_id'], abs($request['amount']));
        if ($result !== true) {
            $this->response->statusCode = 500;
            $this->response->data = $this->stockService->getErrors();
        }
        $this->response->send();
    }

    public function actionDelete()
    {
        $stock_id = Yii::$app->request->get('stock_id');
        // TODO:  if !empty => иди нахуй
        $this->response->data = $this->stockService->saveStatus($stock_id, Stock::STATUS_DELETED);
        $this->response->send();
    }

    public function actionGeoList()
    {
        $this->response->data = Geo::list();
        $this->response->send();
    }

    public function actionList()
    {
        $this->response->data = Stock::find()->list();
        $this->response->send();
    }

}