<?php

namespace common\services\product;

use common\helpers\FishHelper;
use common\models\offer\OfferProduct;
use common\models\product\Product;
use common\models\product\ProductQuery;
use common\models\product\ProductSku;
use common\models\product\SkuView;
use common\modules\user\models\tables\User;
use \Yii;
use yii\base\Component;
use common\models\product\Product as ProductModel;
use common\components\product\logic\ProductSave;
use yii\base\Exception;
use yii\data\Pagination;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

class ProductCommonService
{
    /**
     * @var
     */
    private $owner_id;
    /**
     * @var bool
     */
    private $category = false;

    /**
     * @var array
     */
    public $skus = [];

    /**
     * @param ProductModel $product
     * @return object
     */
    public function saveProduct(ProductModel $product)
    {
        return Yii::createObject(['class' => ProductSave::class, 'product' => $product]);
    }


    /**
     * @param array $filters
     * @param null $pagination
     * @return array|ProductModel[]
     * @throws Exception
     */
    public function findVisible($filters = [], $pagination = null)
    {
        $products = Product::find()
            ->visible()
            ->join('RIGHT JOIN', 'product_sku', 'product_sku.product_id = product.product_id');

        if (isset($filters['product_id'])) $products->andWhere(['product.product_id' => $filters['product_id']['value']]);
        if (isset($filters['sku_id'])) $products->andWhere(['product_sku.sku_id' => $filters['sku_id']['value']]);

        $products->with(['offer_products' => function (ActiveQuery $query) {
            $query->select('product_id, offer_product.offer_id, offer_name')
                ->join('JOIN', 'offer', 'offer.offer_id = offer_product.offer_id');
        }])->with(['product_sku' => function (ActiveQuery $query) {
            $query->select('product_id, sku_id, sku_name, sku_alias, product_sku.geo_id, geo.iso, advert_id,  user.username as advert_name, common_sku_name')
                ->join('JOIN', 'user', 'user.id = advert_id')
                ->join('JOIN', 'geo', 'geo.geo_id = product_sku.geo_id');
            if (Yii::$app->user->identity->role == User::ROLE_ADVERTISER) {
                $query->andWhere(['advert_id' => Yii::$app->user->identity->getId()]);
            } elseif (Yii::$app->user->identity->role !== User::ROLE_ADMIN) {
                throw new Exception('No permissions.');
            }
            $query->asArray();
        }]);

        if (Yii::$app->user->identity->role == User::ROLE_ADVERTISER) {
            $products->andWhere(['product_sku.advert_id' => Yii::$app->user->identity->getId()]);
        } elseif (Yii::$app->user->identity->role !== User::ROLE_ADMIN) {
            throw new Exception('No permissions.');
        }

        $count = clone $products;
        $count = $count->count();

        if (!isset($pagination)) {
            return $products->asArray()->all();
        }

        return [
            'products' => $products
                ->offset($pagination['first_row'])
                ->limit($pagination['rows'])
                ->orderBy(['product_id' => SORT_DESC])
                ->asArray()
                ->all(),
            'count' => $count
        ];
    }

    /**
     * @param bool $with_sku
     * @return array|ProductModel[]
     */
    public function getProducts($with_sku = false)
    {
        $products = Product::find()->select(['product_id', 'product_name'])->visible();
        if ($with_sku == true) {
            $products->with(['product_sku' => function (ActiveQuery $product_sku) {
                $product_sku->select('product_id, sku_id, sku_name')->asArray();
            }]);
        }
        return $products->asArray()->all();
    }


    /**
     * @param $product_id
     * @return array|ProductModel|null
     * @throws Exception
     */
    public function getProductById($product_id)
    {
        $product = Product::find()
            ->visible()
            ->where(['product.product_id' => $product_id])
            ->with(['product_sku' => function (ActiveQuery $query) {
                $query->select('product_id, sku_id, sku_name, common_sku_name, sku_alias, geo_id, advert_id, user.username as advert_name')
                    ->join('JOIN', 'user', 'user.id = advert_id');
                if (Yii::$app->user->identity->role == User::ROLE_ADVERTISER) {
                    $query->andWhere(['advert_id' => Yii::$app->user->identity->getId()]);
                } elseif (Yii::$app->user->identity->role !== User::ROLE_ADMIN) {
                    throw new Exception('No permissions.');
                }
                $query->asArray();
            }]);
        $product->join('RIGHT JOIN', 'product_sku', 'product_sku.product_id = product.product_id');
        if (Yii::$app->user->identity->role == User::ROLE_ADVERTISER) {
            $product->andWhere(['product_sku.advert_id' => Yii::$app->user->identity->getId()]);
        } elseif (Yii::$app->user->identity->role !== User::ROLE_ADMIN) {
            throw new Exception('No permissions.');
        }
        $result = $product->asArray()->one();
        return $result;
    }

    /**
     * @param $product_id
     * @return array
     */
    public function productSkuList($product_id)
    {
        $sku_list = ArrayHelper::map(ProductSku::find()->skuList($product_id), 'sku_id', 'sku_name');
        return $sku_list;
    }
}