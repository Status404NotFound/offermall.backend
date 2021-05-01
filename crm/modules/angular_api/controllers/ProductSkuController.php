<?php

namespace crm\modules\angular_api\controllers;

use common\helpers\FishHelper;
use common\models\product\Product;
use common\models\product\ProductSku;
use crm\services\sku\SkuService;
use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\Request;
use yii\web\Response;

class ProductSkuController extends BehaviorController
{
    public $modelClass = 'common\models\product\ProductSku';

    private $skuService;

    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->skuService = new SkuService();
    }

    /**
     * Lists all Product models.
     * @return mixed
     */
    public function actionIndex()
    {
        $product_id = Yii::$app->request->get('product_id');
        $this->response->data = ['sku_list' => $this->skuService->getVisibleSku($product_id)];
        $this->response->send();
    }

    public function actionSave()
    {
        $this->response->data = $this->skuService->saveSku(Yii::$app->request->post());
        $this->response->send();
    }

    public function actionView()
    {
//        $product_id = Yii::$app->request->get('product_id');
        $sku_id = Yii::$app->request->get('sku_id');


//        $product = Product::findOne(['product_id' => $product_id]);
        $sku = ProductSku::findOne(['sku_id' => $sku_id]);

//        $sku_list = ProductSku::findAll(['product_id' => $product_id]);
        $this->response->data = [
//            'sku' => $sku,
//            'product' => $product,
//            'sku_list' => $sku_list,
//            'product_id' => $product_id,
        ];
        $this->response->send();
    }

    public function actionSkuList()
    {
        $this->response->data = ProductSku::find()->asArray()->all();
        $this->response->send();
    }

}