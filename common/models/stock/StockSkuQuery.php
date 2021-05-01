<?php

namespace common\models\stock;

/**
 * This is the ActiveQuery class for [[StockSku]].
 *
 * @see StockSku
 */
class StockSkuQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return StockSku[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return StockSku|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    public function rightJoinProductSku()
    {
        return $this->select('ss.stock_id, ss.sku_id as sku_id, ps.sku_id, count, sku_name, ss.updated_at, ss.updated_by')
            ->from('stock_sku as ss')
            ->rightJoin('product_sku as ps', 'ps.sku_id = ss.sku_id')
            ->where(['not', ['ss.sku_id' => null]]);
    }

}
