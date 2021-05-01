<?php

namespace crm\services\sku;

use Yii;
use common\helpers\FishHelper;
use common\models\product\ProductSku;
use common\services\sku\SkuCommonService;
use common\services\ValidateException;
use common\models\product\SkuView;

/**
 * Class SkuService
 * @package crm\services\sku
 */
class SkuService extends SkuCommonService
{
    /**
     * @param $request
     * @return array|bool
     */
    public function saveSku($request)
    {
        if (!isset($request['product_id'])) {
            return false;
        }

        if (!isset($request['sku_id'])) {
            $sku = new ProductSku();
        } else {
            $sku = ProductSku::findOne([
                'product_id' => $request['product_id'],
                'sku_id' => $request['sku_id']
            ]);
        }

        $sku->setAttributes(
            [
                'product_id' => $request['product_id'],
                'sku_name' => $request['sku_name'],
                'common_sku_name' => $request['common_sku_name'],
                'sku_alias' => $request['sku_alias'],

                'geo_id' => $request['geo_id'],
                'advert_id' => ($request['advert_id'] == null) ? \Yii::$app->user->identity->getId() : $request['advert_id'],

                'description' => isset($request['description']) ? $request['description'] : '',
                'color' => isset($request['color']) ? $request['color'] : '',
                'visible' => isset($request['visible']) ? $request['visible'] : 1,
            ]
        );

        return $sku->validate() ? $sku->save() : $sku->errors;
    }


    /**
     * @param null $product_id
     * @return array|ProductSku[]
     */
    public function getVisibleSku($product_id = null)
    {
        $sku_list = ProductSku::find()->where(['visible' => 1]);
        $product_id ? $sku_list->andWhere(['product_id' => $product_id]) : null;
        return $sku_list->all();
    }

    /**
     * @param array $product_id_array
     * @return array|\common\models\offer\OfferProduct[]|SkuView[]|\yii\db\ActiveRecord[]
     */
    public function getSkuList(array $product_id_array)
    {
        $query = SkuView::find()
//            ->select(['sku_id', 'sku_name', 'geo_id', 'advert_id', 'common_sku_name'])
            ->select(['sku_id', 'sku_name', 'geo_id', 'advert_id'])
            ->where(['product_id' => $product_id_array]);

        if (!is_null(Yii::$app->user->identity->getOwnerId())) $query->andWhere(['advert_id' => Yii::$app->user->identity->getOwnerId()]);

        $sku_list = $query
            ->asArray()
            ->all();

        return $sku_list;
    }
}