<?php

namespace common\services\stock;

use common\helpers\FishHelper;
use Yii;
use common\models\offer\targets\advert\AdvertOfferTarget;
use common\models\stock\Stock;
use common\models\stock\StockSku;


class StockCommonService
{
    public function saveStock($request)
    {
        $stock = new Stock();

        $stock->setAttributes([
            'stock_name' => $request['stock_name'],
            'owner_id' => Yii::$app->user->identity->getId(),
            'location' => (integer)$request['location'],
            'status' => Stock::STATUS_ACTIVE,
        ]);
        return $stock->validate() ? $stock->save() : $stock->errors;
    }

    /**
     * @param AdvertOfferTarget $stock
     * @return bool
     */
    public function updateStock(AdvertOfferTarget $stock)
    {
        return $stock->save();
    }

    /**
     * @param StockSku $stockSku
     * @return bool
     */
    public function createSku(StockSku $stockSku)
    {
        if ($model = StockSku::findOne(['sku_id' => $stockSku->sku_id, 'stock_id' => $stockSku->stock_id,])) {
            $model->setAttribute('count', $stockSku->count);

            return $this->updateSku($model);
        }
        return $stockSku->save();
    }

    /**
     * @param StockSku $stockSku
     * @return bool
     */
    public function updateSku(StockSku $stockSku)
    {
        return $stockSku->save();
    }
}