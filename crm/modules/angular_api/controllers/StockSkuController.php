<?php

namespace crm\modules\angular_api\controllers;

use common\services\stock\StockService;

class StockSkuController extends BehaviorController
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
        ]);
        return $behaviors;
    }

    public function actionIndex()
    {
//        $this->response->data = Stock::find()->where(['status' => 1])->all();
//        $this->response->send();
    }

    public function actionCreate()
    {
//        $model = new StockSku();
//        if ($model->load(Yii::$app->request->post())) {
//            $model->setAttribute('stock_id', $stock_id);
//
//            $this->stockService->createSku($model);
//            return $this->redirect(['stock/view', 'id' => $stock_id]);
//        } else {
//            return $this->render('create', [
//                'model' => $model,
//                'stock_id' => $stock_id,
//                'skuList' => $this->stockService->getSkuList($stock_id),
//            ]);
//        }


//        $request = Yii::$app->request->post();
//        $this->response->data = $this->stockService->saveStock($request);
//        $this->response->send();
    }
}