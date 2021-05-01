<?php

namespace common\services\sku;

use common\helpers\FishHelper;
use common\models\product\Product;
use common\models\product\ProductSku;


class SkuCommonService
{
    private $owner_id;
    private $category = false;

    public $skus = [];

//    public function createSku(ProductSku $sku)
//    {
//        return Yii::createObject(['class' => ProductSave::class, 'product' => $product]);
//    }

    public function findAllSku()
    {
        return ProductSku::findAll(['visible' => 1]);

//        $isWebMaster = Yii::$app->user->identity->role == 6;
//        if (!Yii::$app->user->can(Rbac::VIEW_ALL_PRODUCTS) && !$isWebMaster) {
//            $query->andWhere([self::tableName() . '.id' => Product::findProducts()]);
//        }
//
//        return $query;
    }

    public function productList()
    {
        $productList = ArrayHelper::map(Product::find()->productList(), 'product_id', 'product_name');
//        FishHelper::debug($productList);
        return $productList;
    }
}