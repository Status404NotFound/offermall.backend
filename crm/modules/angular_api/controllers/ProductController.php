<?php

namespace crm\modules\angular_api\controllers;

use crm\services\product\ProductService;
use Yii;

/**
 * Class ProductController
 * @package crm\modules\angular_api\controllers
 */
class ProductController extends BehaviorController
{
    /**
     * @var string
     */
    public $modelClass = 'common\models\product\Product';

    /**
     * @var ProductService
     */
    private $productService;

    /**
     * ProductController constructor.
     * @param string $id
     * @param \yii\base\Module $module
     * @param array $config
     */
    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->productService = new ProductService();
    }

    public function actionCreate()
    {
        $request = Yii::$app->request->post();
        $this->response->data = $this->productService->createProduct($request);
        $this->response->send();
    }

    public function actionView()
    {
        $product_id = Yii::$app->request->get('product_id');
        $this->response->data = $this->productService->getProductById($product_id);;
        $this->response->send();
    }

    public function actionUpdate()
    {
        $request = Yii::$app->request->getBodyParams();
        $this->response->data = $this->productService->updateProduct($request);
        $this->response->send();
    }

    public function actionProductList()
    {
        $with_sku = Yii::$app->request->getBodyParam('product_sku');
        $this->response->data = $this->productService->getProducts($with_sku);
        $this->response->send();
    }

    public function actionProducts()
    {
        $products = $this->productService->findVisible($this->getRequestFilters(), $this->getRequestPagination());
        $this->response->data = $products['products'];
        $this->setPaginationHeaders($products['count']);
        $this->response->send();
    }


    public function actionProductSku()
    {
        $product_id = Yii::$app->request->getBodyParams();
        $this->response->data = $this->productService->productSkuList($product_id);
        $this->response->send();
    }

}