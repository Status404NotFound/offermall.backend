<?php

namespace crm\services\product;

use common\models\product\Product;
use common\services\product\ProductCommonService;
use common\services\ServiceException;
use crm\services\sku\SkuService;
use yii\base\InvalidParamException;
use yii\db\Exception;

/**
 * Class ProductService
 * @package crm\services\product
 */
class ProductService extends ProductCommonService
{
    /**
     * @var array
     */
    public $errors = [];

    /**
     * @param $request
     * @return array|bool
     * @throws Exception
     */
    public function createProduct($request)
    {
        $product = new Product();
        $product->setAttributes(
            [
                'product_name' => $request['product_name'],
                'category' => $request['category'],
                'description' => $request['description'],
            ]
        );
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $product->validate() ? $product->save() : $this->errors['product_sku'] = $product->errors;
            if (isset($request['product_sku']) && is_array($request['product_sku'])) {
                $skuService = new SkuService();
                foreach ($request['product_sku'] as $sku) {
                    $product_sku = $skuService->saveSku(array_merge(['product_id' => $product->product_id], $sku));
                    if ($product_sku != true || is_array($product_sku)) {
                        $this->errors['product_sku'] = $product_sku;
                    }
                }
            }
            $transaction->commit();
        } catch (InvalidParamException $e) {
            $this->errors['InvalidParamException'] = $e;
            $transaction->rollBack();
        } catch (Exception $e) {
            $this->errors['Exception'] = $e;
            $transaction->rollBack();
        }
        return !empty($this->errors) ? $this->errors : true;
    }

    /**
     * @param $request
     * @return bool
     * @throws Exception
     * @throws ServiceException
     */
    public function updateProduct($request)
    {
        $product = Product::findOne(['product_id' => $request['product_id']]);
        $product->setAttributes(
            [
                'product_name' => $request['product_name'],
                'category' => $request['category'],
                'description' => $request['description'],
            ]
        );
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $product->validate() ? $product->save() : $this->errors['product_sku'] = $product->errors;
            if (isset($request['product_sku']) && is_array($request['product_sku'])) {
                $skuService = new SkuService();
                foreach ($request['product_sku'] as $sku) {
                    $product_sku = $skuService->saveSku(array_merge(['product_id' => $product->product_id], $sku));
                    if ($product_sku != true || is_array($product_sku)) {
                        $this->errors['product_sku'] = $product_sku;
                    }
                }
            }
            $transaction->commit();
        } catch (InvalidParamException $e) {
            $this->errors['InvalidParamException'] = $e;
            $transaction->rollBack();
        } catch (Exception $e) {
            $this->errors['Exception'] = $e;
            $transaction->rollBack();
        }

        if (!empty($this->errors)) {
            throw new ServiceException('Error!');
        } else {
            return true;
        }
    }
}